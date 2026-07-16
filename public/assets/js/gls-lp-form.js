/**
 * GLS Inscription Form - Ad Landing Page Version (Meta / Google)
 * File: public/assets/js/gls-lp-form.js
 *
 * Uses unique IDs (lp*) to avoid conflicts with the modal & standalone forms.
 * Reads form_source from the form's `data-form-source` attribute so the same
 * file powers both /lp/meta and /lp/google landing pages.
 */

(function () {
  'use strict';

  const form = document.getElementById('lpForm');
  if (!form) return;

  const formSource = form.dataset.formSource || 'landing';
  console.log('[GLS LP Form] Initializing, source=' + formSource);

  const apiCentersUrl = document.querySelector('meta[name="api-centers-url"]')?.content || '/api/centers';
  const glsStoreUrl = document.querySelector('meta[name="gls-store-url"]')?.content || '/gls-inscription';

  const t = {
    submit: form.dataset.labelSubmit || 'Envoyer',
    sending: form.dataset.labelSending || 'Envoi...',
    errRequired: form.dataset.errorRequired || 'Veuillez remplir tous les champs obligatoires.',
    errDuplicate: form.dataset.errorDuplicate || 'Vous avez déjà fait une demande.',
    errConnection: form.dataset.errorConnection || 'Erreur de connexion.',
    errGeneric: form.dataset.errorGeneric || 'Une erreur est survenue.',
    errServer: form.dataset.errorServer || 'Erreur serveur.',
    loading: form.dataset.jsLoading || 'Chargement...',
    errLoading: form.dataset.jsErrorLoading || 'Erreur de chargement',
    selectLevel: form.dataset.jsSelectLevel || 'Sélectionner un niveau',
    selectCenter: form.dataset.jsSelectCenter || 'Sélectionner un centre',
    selectGroup: form.dataset.jsSelectGroup || 'Sélectionner un groupe',
    groupLabel: form.dataset.jsGroupLabel || 'Groupe',
    groupNight: form.dataset.jsGroupNight || 'Groupe Nuit',
  };

  const errorWrap = document.getElementById('lpErrorMessage');
  const errorText = document.getElementById('lpErrorText');
  const successMessage = document.getElementById('lpSuccessMessage');

  const typeCours = document.getElementById('lpTypeCours');
  const centreWrapper = document.getElementById('lpCentreWrapper');
  const centreSelect = document.getElementById('lpCentre');
  const groupSelect = document.getElementById('lpGroupId');
  const niveauSelect = document.getElementById('lpNiveau');
  const horairePrefereInput = document.getElementById('lpHorairePrefere');
  const submitBtn = document.getElementById('lpSubmitBtn');

  function showError(msg) {
    if (errorWrap && errorText) {
      errorText.textContent = msg;
      errorWrap.classList.remove('active');
      void errorWrap.offsetWidth;
      errorWrap.classList.add('active');
      errorWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function clearError() {
    if (errorWrap && errorText) {
      errorText.textContent = '';
      errorWrap.classList.remove('active');
    }
  }

  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function disable(el, state = true) {
    if (el) el.disabled = !!state;
  }

  const NIVEAUX_DATA = [
    { code: 'A0', fr: 'A0 – Aucune connaissance préalable', en: 'A0 – No prior knowledge' },
    { code: 'A1', fr: 'A1 – Débutant', en: 'A1 – Beginner' },
    { code: 'A2', fr: 'A2 – Élémentaire', en: 'A2 – Elementary' },
    { code: 'B1', fr: 'B1 – Intermédiaire', en: 'B1 – Intermediate' },
    { code: 'B2', fr: 'B2 – Intermédiaire Supérieur', en: 'B2 – Upper Intermediate' },
    { code: 'C1', fr: 'C1 – Avancé', en: 'C1 – Advanced' },
  ];

  function loadStaticNiveaux() {
    if (!niveauSelect) return;
    const locale = document.documentElement.lang || 'fr';
    const isFrench = locale.startsWith('fr');
    niveauSelect.innerHTML = '<option value="">' + t.selectLevel + '</option>';
    NIVEAUX_DATA.forEach((level) => {
      const text = isFrench ? level.fr : level.en;
      niveauSelect.innerHTML += '<option value="' + level.code + '">' + text + '</option>';
    });
  }

  loadStaticNiveaux();

  if (centreWrapper) centreWrapper.style.display = 'none';
  if (centreSelect) centreSelect.removeAttribute('required');
  if (groupSelect) disable(groupSelect, true);
  if (niveauSelect) disable(niveauSelect, true);

  /* ============================== LOAD CENTERS ============================== */
  function loadCenters() {
    if (!centreSelect) return;
    centreSelect.innerHTML = '<option value="">' + t.loading + '</option>';
    disable(centreSelect, true);

    fetch(apiCentersUrl)
      .then((res) => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then((data) => {
        centreSelect.innerHTML = '<option value="">' + t.selectCenter + '</option>';
        if (Array.isArray(data)) {
          data.forEach((c) => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            centreSelect.appendChild(opt);
          });
        }
        disable(centreSelect, false);
      })
      .catch((err) => {
        console.error('[GLS LP Form] centers load error', err);
        centreSelect.innerHTML = '<option value="">' + t.errLoading + '</option>';
        disable(centreSelect, false);
      });
  }

  /* ============================== UPDATE GROUPS PER CENTER ============================== */
  function updateGroupTimes() {
    if (!centreSelect || !groupSelect) return;
    const selected = centreSelect.options[centreSelect.selectedIndex];
    if (!selected || !selected.value) return;

    const centerText = selected.textContent.toLowerCase();
    const gl = t.groupLabel;
    const groupsByCenter = {
      rabat: [
        { id: 1, name: gl + ' 10:00 – 12:00' },
        { id: 2, name: gl + ' 15:00 – 17:00' },
        { id: 3, name: gl + ' 17:00 – 19:00' },
        { id: 4, name: gl + ' 19:00 – 21:00' },
      ],
      casablanca: [
        { id: 5, name: gl + ' 10:00 – 12:00' },
        { id: 6, name: gl + ' 15:00 – 17:00' },
        { id: 7, name: gl + ' 17:00 – 19:00' },
        { id: 8, name: gl + ' 19:00 – 21:00' },
      ],
      casa: [
        { id: 5, name: gl + ' 10:00 – 12:00' },
        { id: 6, name: gl + ' 15:00 – 17:00' },
        { id: 7, name: gl + ' 17:00 – 19:00' },
        { id: 8, name: gl + ' 19:00 – 21:00' },
      ],
      marrakech: [
        { id: 9, name: gl + ' 10:00 – 12:30' },
        { id: 10, name: gl + ' 16:00 – 18:30' },
        { id: 11, name: gl + ' 18:30 – 21:00' },
      ],
      sale: [
        { id: 13, name: gl + ' 10:00 – 12:00' },
        { id: 14, name: gl + ' 15:00 – 17:00' },
        { id: 15, name: gl + ' 17:00 – 19:00' },
        { id: 16, name: gl + ' 19:00 – 21:00' },
      ],
      kenitra: [
        { id: 17, name: gl + ' 10:00 – 12:30' },
        { id: 18, name: gl + ' 16:00 – 18:30' },
        { id: 19, name: gl + ' 18:30 – 21:00' },
      ],
      agadir: [
        { id: 21, name: gl + ' 10:00 – 12:30' },
        { id: 22, name: gl + ' 16:00 – 18:30' },
        { id: 23, name: gl + ' 19:00 – 21:30' },
      ],
      online: [{ id: 25, name: t.groupNight + ' 20:00 – 22:00' }],
    };

    let groups = [];
    for (const [city, cityGroups] of Object.entries(groupsByCenter)) {
      if (centerText.includes(city)) {
        groups = cityGroups;
        break;
      }
    }

    groupSelect.innerHTML = '<option value="">' + t.selectGroup + '</option>';
    groups.forEach((g) => {
      groupSelect.innerHTML += '<option value="' + g.id + '">' + g.name + '</option>';
    });
  }

  /* ============================== EVENTS ============================== */
  if (typeCours) {
    typeCours.addEventListener('change', function () {
      const value = this.value;
      if (value === 'presentiel') {
        if (centreWrapper) centreWrapper.style.display = '';
        if (centreSelect) centreSelect.setAttribute('required', 'required');
        loadCenters();
        if (niveauSelect) disable(niveauSelect, false);
        if (groupSelect) {
          groupSelect.innerHTML = '<option value="">' + t.selectGroup + '</option>';
          disable(groupSelect, true);
        }
      } else if (value === 'en_ligne') {
        if (centreWrapper) centreWrapper.style.display = 'none';
        if (centreSelect) {
          centreSelect.removeAttribute('required');
          centreSelect.innerHTML = '<option value="">' + t.selectCenter + '</option>';
        }
        if (groupSelect) {
          groupSelect.innerHTML = '<option value="">' + t.selectGroup + '</option>';
          groupSelect.innerHTML += '<option value="25">' + t.groupNight + ' 20:00 – 22:00</option>';
          disable(groupSelect, false);
        }
        if (niveauSelect) disable(niveauSelect, false);
        if (horairePrefereInput) horairePrefereInput.value = '20:00 – 22:00';
      } else {
        if (centreWrapper) centreWrapper.style.display = 'none';
        if (centreSelect) {
          centreSelect.removeAttribute('required');
          centreSelect.innerHTML = '<option value="">' + t.selectCenter + '</option>';
        }
        if (groupSelect) {
          groupSelect.innerHTML = '<option value="">' + t.selectGroup + '</option>';
          disable(groupSelect, true);
        }
        if (niveauSelect) disable(niveauSelect, true);
        if (horairePrefereInput) horairePrefereInput.value = '';
      }
    });
  }

  if (centreSelect) {
    centreSelect.addEventListener('change', function () {
      updateGroupTimes();
      if (groupSelect) disable(groupSelect, !this.value);
    });
  }

  if (groupSelect) {
    groupSelect.addEventListener('change', function () {
      const selected = this.options[this.selectedIndex];
      if (!selected || !selected.value) return;
      const groupText = selected.textContent;
      const timeMatch = groupText.match(/(\d{1,2}:\d{2}\s*–\s*\d{1,2}:\d{2})/);
      const groupTime = timeMatch ? timeMatch[1] : '';
      if (horairePrefereInput) horairePrefereInput.value = groupTime;
    });
  }

  /* ============================== SUBMIT ============================== */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearError();

    const requiredInputs = form.querySelectorAll('[required]');
    for (let input of requiredInputs) {
      const value = (input.value || '').trim();
      if (!value) {
        showError(t.errRequired);
        input.focus();
        return;
      }
    }

    const formData = new FormData(form);
    if (typeCours && typeCours.value === 'en_ligne') formData.delete('centre');
    formData.append('form_source', formSource);

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.originalText = submitBtn.textContent;
      submitBtn.textContent = t.sending;
    }

    fetch(glsStoreUrl, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrfToken(), Accept: 'application/json' },
      body: formData,
    })
      .then((res) => {
        if (!res.ok && res.status === 422) {
          return res.json().then((d) => { throw { type: 'validation', data: d }; });
        }
        if (!res.ok && res.status === 409) {
          return res.json().then((d) => { throw { type: 'duplicate', data: d }; });
        }
        if (!res.ok) throw { type: 'server', message: t.errServer + ' (' + res.status + ')' };
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          // Fire ad-platform conversion events
          try {
            if (formSource === 'meta_ads' && typeof fbq === 'function') {
              fbq('track', 'Lead', { content_name: 'GLS Inscription', source: 'meta_ads' });
            }
            if (formSource === 'google_ads' && typeof gtag === 'function') {
              gtag('event', 'conversion', { send_to: 'GLS_GOOGLE_ADS_LEAD', event_label: 'google_ads' });
            }
            if (typeof gtag === 'function') {
              gtag('event', 'form_submit', { event_category: 'GLS Inscription', event_label: formSource });
            }
          } catch (err) { /* tracking optional */ }

          if (data.redirect_url) {
            setTimeout(function () { window.location.href = data.redirect_url; }, 400);
          } else {
            form.style.display = 'none';
            if (successMessage) successMessage.style.display = 'block';
          }
        } else {
          showError(data.message || t.errGeneric);
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || t.submit;
          }
        }
      })
      .catch((err) => {
        if (err && err.type === 'validation' && err.data && err.data.errors) {
          const firstError = Object.values(err.data.errors)[0];
          showError(Array.isArray(firstError) ? firstError[0] : firstError);
        } else if (err && err.type === 'duplicate') {
          showError((err.data && err.data.message) || t.errDuplicate);
        } else if (err && err.type === 'server') {
          showError(err.message);
        } else {
          showError(t.errConnection);
        }
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = submitBtn.dataset.originalText || t.submit;
        }
      });
  });
})();
