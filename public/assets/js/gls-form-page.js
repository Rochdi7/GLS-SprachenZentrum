/**
 * GLS Inscription Form - Standalone Page Version
 * File: public/assets/js/gls-form-page.js
 *
 * Uses unique IDs (glsPage*) to avoid conflicts with modal form (gls*)
 */

(function() {
  'use strict';

  // Find the page form (unique ID to avoid modal conflict)
  const form = document.getElementById("glsPageForm");
  if (!form) {
    // Not on the inscription page, exit silently
    return;
  }

  console.log('[GLS Page Form] Initializing standalone form...');

  // Get API URLs from meta tags
  const apiCentersUrl = document.querySelector('meta[name="api-centers-url"]')?.content || '/api/centers';
  const apiGroupsUrlTemplate = document.querySelector('meta[name="api-groups-url-template"]')?.content || '/api/groups/__SITE__';
  const apiDatesUrlTemplate = document.querySelector('meta[name="api-dates-url-template"]')?.content || '/api/groups/dates/__GROUP__';
  const glsStoreUrl = document.querySelector('meta[name="gls-store-url"]')?.content || '/gls-inscription';

  console.log('[GLS Page Form] API URLs:', { centers: apiCentersUrl, groups: apiGroupsUrlTemplate, dates: apiDatesUrlTemplate });

  // Translated labels from data attributes on form element
  const t = {
    submit: form.dataset.labelSubmit || "Envoyer",
    sending: form.dataset.labelSending || "Envoi en cours...",
    errRequired: form.dataset.errorRequired || "Veuillez remplir tous les champs obligatoires.",
    errDuplicate: form.dataset.errorDuplicate || "Vous avez déjà fait une demande pour ce groupe.",
    errConnection: form.dataset.errorConnection || "Erreur de connexion. Veuillez réessayer.",
    errGeneric: form.dataset.errorGeneric || "Une erreur est survenue.",
    errServer: form.dataset.errorServer || "Erreur serveur. Veuillez réessayer.",
    loading: form.dataset.jsLoading || "Chargement...",
    errLoading: form.dataset.jsErrorLoading || "Erreur de chargement",
    selectLevel: form.dataset.jsSelectLevel || "Sélectionner un niveau",
    selectCenter: form.dataset.jsSelectCenter || "Sélectionner un centre",
    selectGroup: form.dataset.jsSelectGroup || "Sélectionner un groupe",
    selectDate: form.dataset.jsSelectDate || "Sélectionner une date",
    groupLabel: form.dataset.jsGroupLabel || "Groupe",
    groupNight: form.dataset.jsGroupNight || "Groupe Nuit",
  };

  // Get form elements with unique glsPage* IDs
  const errorWrap = document.getElementById("glsPageErrorMessage");
  const errorText = document.getElementById("glsPageErrorText");
  const successMessage = document.getElementById("glsPageSuccessMessage");

  const typeCours = document.getElementById("glsPageTypeCours");
  const centreWrapper = document.getElementById("glsPageCentreWrapper");
  const centreSelect = document.getElementById("glsPageCentre");

  const groupSelect = document.getElementById("glsPageGroupId");
  const niveauSelect = document.getElementById("glsPageNiveau");

  const dateInput = document.getElementById("glsPageDateStart");
  const horairePrefereInput = document.getElementById("glsPageHorairePrefere");

  const submitBtn = document.getElementById("glsPageSubmitBtn");

  // Log element detection
  console.log('[GLS Page Form] Elements found:', {
    form: !!form,
    typeCours: !!typeCours,
    centreWrapper: !!centreWrapper,
    centreSelect: !!centreSelect,
    groupSelect: !!groupSelect,
    niveauSelect: !!niveauSelect,
    dateInput: !!dateInput,
    horairePrefereInput: !!horairePrefereInput,
  });

  // Utils
  function showError(message) {
    if (errorWrap && errorText) {
      errorText.textContent = message;
      errorWrap.classList.remove("active"); // Reset animation
      void errorWrap.offsetWidth; // Trigger reflow
      errorWrap.classList.add("active");
      
      // Scroll to error message
      errorWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function clearError() {
    if (errorWrap && errorText) {
      errorText.textContent = "";
      errorWrap.classList.remove("active");
    }
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
  }

  function disable(el, state = true) {
    if (el) el.disabled = !!state;
  }

  // Static levels with French and English descriptions
  const NIVEAUX_DATA = [
    { code: "A0", fr: "A0 – Aucune connaissance préalable", en: "A0 – No prior knowledge" },
    { code: "A1", fr: "A1 – Débutant", en: "A1 – Beginner" },
    { code: "A2", fr: "A2 – Élémentaire", en: "A2 – Elementary" },
    { code: "B1", fr: "B1 – Intermédiaire", en: "B1 – Intermediate" },
    { code: "B2", fr: "B2 – Intermédiaire Supérieur", en: "B2 – Upper Intermediate" },
    { code: "C1", fr: "C1 – Avancé", en: "C1 – Advanced" }
  ];

  function loadStaticNiveaux() {
    if (!niveauSelect) return;
    
    // Detect current language (fr or en)
    const locale = document.documentElement.lang || 'fr';
    const isFrench = locale.startsWith('fr');
    
    niveauSelect.innerHTML = `<option value="">${t.selectLevel}</option>`;
    NIVEAUX_DATA.forEach(level => {
      const text = isFrench ? level.fr : level.en;
      niveauSelect.innerHTML += `<option value="${level.code}">${text}</option>`;
    });
  }

  // Load niveau options on page load
  loadStaticNiveaux();

  // Initial state - hide centre wrapper
  if (centreWrapper) centreWrapper.style.display = "none";
  if (centreSelect) centreSelect.removeAttribute("required");

  // Disable group and niveau until a course type is selected
  if (groupSelect) disable(groupSelect, true);
  if (niveauSelect) disable(niveauSelect, true);

  // Flatpickr instance
  let flatpickrInstance = null;

  /* ============================== LOAD CENTERS ============================== */
  function loadCenters() {
    console.log('[GLS Page Form] Loading centers from:', apiCentersUrl);
    
    if (!centreSelect) {
      console.error('[GLS Page Form] centreSelect not found!');
      return;
    }

    centreSelect.innerHTML = `<option value="">${t.loading}</option>`;
    disable(centreSelect, true);

    fetch(apiCentersUrl)
      .then((res) => {
        console.log('[GLS Page Form] Response status:', res.status);
        if (!res.ok) throw new Error('HTTP error: ' + res.status);
        return res.json();
      })
      .then((data) => {
        console.log('[GLS Page Form] Centers received:', data);
        
        centreSelect.innerHTML = `<option value="">${t.selectCenter}</option>`;

        if (Array.isArray(data)) {
          data.forEach((c) => {
            const opt = document.createElement("option");
            opt.value = c.id;
            opt.textContent = c.name;
            centreSelect.appendChild(opt);
          });
          console.log('[GLS Page Form] Added', data.length, 'centers to dropdown');
        }
        
        disable(centreSelect, false);
      })
      .catch((err) => {
        console.error('[GLS Page Form] Error loading centers:', err);
        centreSelect.innerHTML = `<option value="">${t.errLoading}</option>`;
        disable(centreSelect, false);
      });
  }

  /* ============================== LOAD GROUPS ============================== */
  // COMMENTED OUT - Using static groups for now, will implement dynamic loading later
  /*
  function loadGroups() {
    if (!centreSelect || !groupSelect) return;
    
    const siteId = centreSelect.value;
    console.log('[GLS Page Form] Loading groups for site:', siteId);

    groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
    if (horairePrefereInput) horairePrefereInput.value = "";
    if (dateInput) dateInput.value = "";

    if (!siteId) {
      disable(groupSelect, true);
      return;
    }

    const groupsUrl = apiGroupsUrlTemplate.replace('__SITE__', siteId);
    console.log('[GLS Page Form] Fetching groups from:', groupsUrl);
    
    groupSelect.innerHTML = '<option value="">Chargement...</option>';
    disable(groupSelect, true);

    fetch(groupsUrl)
      .then((res) => {
        if (!res.ok) throw new Error('HTTP error: ' + res.status);
        return res.json();
      })
      .then((groups) => {
        console.log('[GLS Page Form] Groups received:', groups);
        
        groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
        
        if (Array.isArray(groups)) {
          groups.forEach((g) => {
            const opt = document.createElement("option");
            opt.value = g.id;
            opt.textContent = g.display_name || g.name_fr || g.name || ('Groupe ' + g.id);
            opt.setAttribute("data-level", g.level || "");
            opt.setAttribute("data-time", g.time_range || "");
            groupSelect.appendChild(opt);
          });
        }
        
        disable(groupSelect, false);
      })
      .catch((err) => {
        console.error('[GLS Page Form] Error loading groups:', err);
        groupSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        disable(groupSelect, false);
      });
  }
  */

  /* ============================== LOAD DATES ============================== */
  function loadDatesForGroup(groupId) {
    if (!dateInput) return;

    dateInput.value = "";
    
    // Destroy existing flatpickr instance
    if (flatpickrInstance) flatpickrInstance.destroy();

    // Initialize flatpickr with no date restrictions (static date picker)
    flatpickrInstance = flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      minDate: "today", // Minimum date is today
      placeholder: t.selectDate
    });

    console.log('[GLS Page Form] Date picker initialized for group:', groupId);
  }

  /* ============================== UPDATE GROUP TIMES BASED ON CENTER ============================== */
  function updateGroupTimes() {
    if (!centreSelect || !groupSelect) return;

    const selectedCenter = centreSelect.options[centreSelect.selectedIndex];
    if (!selectedCenter.value) return;

    // Get center name/city to determine duration
    const centerText = selectedCenter.textContent.toLowerCase();
    
    // Define groups for each center (using translated group label)
    const gl = t.groupLabel;
    const groupsByCenter = {
      rabat: [
        { id: 1, name: gl + ' 10:00 – 12:00' },
        { id: 2, name: gl + ' 15:00 – 17:00' },
        { id: 3, name: gl + ' 17:00 – 19:00' },
        { id: 4, name: gl + ' 19:00 – 21:00' }
      ],
      casablanca: [
        { id: 5, name: gl + ' 10:00 – 12:00' },
        { id: 6, name: gl + ' 15:00 – 17:00' },
        { id: 7, name: gl + ' 17:00 – 19:00' },
        { id: 8, name: gl + ' 19:00 – 21:00' }
      ],
      casa: [
        { id: 5, name: gl + ' 10:00 – 12:00' },
        { id: 6, name: gl + ' 15:00 – 17:00' },
        { id: 7, name: gl + ' 17:00 – 19:00' },
        { id: 8, name: gl + ' 19:00 – 21:00' }
      ],
      marrakech: [
        { id: 9, name: gl + ' 10:00 – 12:30' },
        { id: 10, name: gl + ' 16:00 – 18:30' },
        { id: 11, name: gl + ' 18:30 – 21:00' }
      ],
      sale: [
        { id: 13, name: gl + ' 10:00 – 12:00' },
        { id: 14, name: gl + ' 15:00 – 17:00' },
        { id: 15, name: gl + ' 17:00 – 19:00' },
        { id: 16, name: gl + ' 19:00 – 21:00' }
      ],
      kenitra: [
        { id: 17, name: gl + ' 10:00 – 12:30' },
        { id: 18, name: gl + ' 16:00 – 18:30' },
        { id: 19, name: gl + ' 18:30 – 21:00' }
      ],
      agadir: [
        { id: 21, name: gl + ' 10:00 – 12:30' },
        { id: 22, name: gl + ' 16:00 – 18:30' },
        { id: 23, name: gl + ' 19:00 – 21:30' }
      ],
      online: [
        { id: 25, name: t.groupNight + ' 20:00 – 22:00' }
      ]
    };

    // Find matching center
    let groups = [];
    for (const [city, cityGroups] of Object.entries(groupsByCenter)) {
      if (centerText.includes(city)) {
        groups = cityGroups;
        break;
      }
    }

    // Update group select
    groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
    groups.forEach(group => {
      groupSelect.innerHTML += `<option value="${group.id}">${group.name}</option>`;
    });

    console.log('[GLS Page Form] Updated groups for center:', centerText, 'Groups count:', groups.length);
  }

  /* ============================== EVENTS ============================== */
  
  // Type de cours change event
  if (typeCours) {
    console.log('[GLS Page Form] Attaching change event to typeCours');
    
    typeCours.addEventListener("change", function() {
      const value = this.value;
      console.log('[GLS Page Form] Type cours changed to:', value);

      if (value === "presentiel") {
        console.log('[GLS Page Form] Showing centre wrapper and loading centers...');
        if (centreWrapper) centreWrapper.style.display = "block";
        if (centreSelect) centreSelect.setAttribute("required", "required");
        loadCenters();
        // Enable niveau but keep group disabled until a centre is selected
        if (niveauSelect) disable(niveauSelect, false);
        if (groupSelect) {
          groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
          disable(groupSelect, true);
        }
      } else if (value === "en_ligne") {
        console.log('[GLS Page Form] Showing online course group...');
        if (centreWrapper) centreWrapper.style.display = "none";
        if (centreSelect) {
          centreSelect.removeAttribute("required");
          centreSelect.innerHTML = `<option value="">${t.selectCenter}</option>`;
        }
        // For online courses, show only the static night group
        if (groupSelect) {
          groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
          groupSelect.innerHTML += `<option value="25">${t.groupNight} 20:00 – 22:00</option>`;
          disable(groupSelect, false);
        }
        if (niveauSelect) disable(niveauSelect, false);
        if (dateInput) dateInput.value = "";
        if (horairePrefereInput) horairePrefereInput.value = "20:00 – 22:00";
      } else {
        console.log('[GLS Page Form] Hiding centre wrapper');
        if (centreWrapper) centreWrapper.style.display = "none";
        if (centreSelect) {
          centreSelect.removeAttribute("required");
          centreSelect.innerHTML = `<option value="">${t.selectCenter}</option>`;
        }
        if (groupSelect) {
          groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
          disable(groupSelect, true);
        }
        if (niveauSelect) disable(niveauSelect, true);
        if (dateInput) dateInput.value = "";
        if (horairePrefereInput) horairePrefereInput.value = "";
      }
    });
  }

  // Centre select change event
  if (centreSelect) {
    centreSelect.addEventListener("change", function() {
      console.log('[GLS Page Form] Centre changed to:', this.value);
      // Update group times based on selected center
      updateGroupTimes();
      // Enable group select once a centre is chosen
      if (groupSelect) disable(groupSelect, !this.value);
    });
  }

  // Group select change event
  if (groupSelect) {
    groupSelect.addEventListener("change", function() {
      const selected = this.options[this.selectedIndex];
      if (!selected || !selected.value) return;

      // Get group text to extract time (format: "Groupe HH:MM – HH:MM")
      const groupText = selected.textContent;
      console.log('[GLS Page Form] Group selected:', groupText);

      // Extract time from group text using regex (e.g., "10:00 – 12:00" or "10:00 – 12:30")
      const timeMatch = groupText.match(/(\d{1,2}:\d{2}\s*–\s*\d{1,2}:\d{2})/);
      const groupTime = timeMatch ? timeMatch[1] : '';

      // Set horaire_prefere input value (readonly field)
      if (horairePrefereInput) {
        horairePrefereInput.value = groupTime;
        console.log('[GLS Page Form] Set horaire_prefere to:', groupTime);
      }

      loadDatesForGroup(selected.value);
    });
  }

  /* ============================== FORM SUBMIT ============================== */
  form.addEventListener("submit", function(e) {
    e.preventDefault();
    clearError();

    console.log('[GLS Page Form] Form submitted');

    // Basic validation
    const requiredInputs = form.querySelectorAll("[required]");
    for (let input of requiredInputs) {
      const value = input.value.trim();
      
      // For select elements, value must be non-empty (exclude empty placeholder options)
      if (input.tagName === 'SELECT') {
        if (!value) {
          showError(t.errRequired);
          input.focus();
          return;
        }
      }
      // For other inputs, check they're not empty
      else if (!value) {
        showError(t.errRequired);
        input.focus();
        return;
      }
    }

    // Collect form data
    const formData = new FormData(form);
    
    // Remove centre field for en_ligne courses (online courses don't need a center)
    if (typeCours && typeCours.value === "en_ligne") {
      formData.delete("centre");
    }
    
    formData.append('form_source', 'page');

    // Disable submit button
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = t.sending;
    }

    fetch(glsStoreUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "Accept": "application/json",
      },
      body: formData,
    })
      .then((res) => {
        // Handle validation errors (422) and other errors
        if (!res.ok && res.status === 422) {
          return res.json().then(data => {
            throw { type: 'validation', data: data };
          });
        }
        if (!res.ok && res.status === 409) {
          return res.json().then(data => {
            throw { type: 'duplicate', data: data };
          });
        }
        if (!res.ok) {
          throw { type: 'server', message: t.errServer + ' (' + res.status + ')' };
        }
        return res.json();
      })
      .then((data) => {
        console.log('[GLS Page Form] Submit response:', data);

        if (data.success && data.redirect_url) {
          // Track conversion before redirect
          if (window.gtag) {
            gtag('event', 'form_submit', {
              event_category: 'GLS Inscription',
              event_label: 'Page Form',
              event_callback: function() {
                window.location.href = data.redirect_url;
              }
            });
            // Fallback redirect if gtag callback doesn't fire
            setTimeout(function() {
              window.location.href = data.redirect_url;
            }, 1000);
          } else {
            // No gtag, redirect immediately
            window.location.href = data.redirect_url;
          }
        } else if (data.success) {
          // Fallback: show success message if no redirect URL
          form.style.display = "none";
          if (successMessage) successMessage.style.display = "block";
        } else {
          showError(data.message || t.errGeneric);
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = t.submit;
          }
        }
      })
      .catch((err) => {
        console.error('[GLS Page Form] Submit error:', err);
        
        if (err.type === 'validation' && err.data.errors) {
          // Show validation errors
          const errors = err.data.errors;
          const firstError = Object.values(errors)[0];
          showError(Array.isArray(firstError) ? firstError[0] : firstError);
        } else if (err.type === 'duplicate') {
          showError(err.data.message || t.errDuplicate);
        } else if (err.type === 'server') {
          showError(err.message);
        } else {
          showError(t.errConnection);
        }

        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = t.submit;
        }
      });
  });

  console.log('[GLS Page Form] Initialization complete!');

})();
