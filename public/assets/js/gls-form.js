/**
 * GLS Inscription Multi-Step Form
 * Scoped to #glsInscriptionRoot to prevent conflicts with other modals
 *
 * FIXES:
 * - Prevent "Unexpected token '<' ... not valid JSON" by:
 *   1) Sending Accept: application/json + X-Requested-With
 *   2) Safely parsing response (JSON if possible, else text) and logging HTML errors
 * - Uses current locale prefix from <html lang="fr"> to post to /{locale}/gls-inscription
 * - Better error messages for 419/422/500/404
 */

(function () {
  // Get root container - all queries scoped within this element
  const root = document.getElementById("glsInscriptionRoot");
  if (!root) return;

  // Local state
  let currentStep = 1;
  const totalSteps = 3;

  // Tracking / submission state. Declared here (before submitForm is defined) so the
  // submit guard is never read in its temporal dead zone.
  let submitting = false;
  let submitted = false;

  // Translated labels from data attributes
  const t = {
    next: root.dataset.labelNext || "Continuer",
    submit: root.dataset.labelSubmit || "Envoyer",
    errRequired: root.dataset.errorRequired || "Veuillez remplir les champs obligatoires.",
    errDuplicate: root.dataset.errorDuplicate || "Vous êtes déjà inscrit.",
    errConnection: root.dataset.errorConnection || "Impossible d'envoyer votre inscription.",
    errGeneric: root.dataset.errorGeneric || "Une erreur est survenue.",
    errServer: root.dataset.errorServer || "Erreur serveur. Veuillez réessayer.",
    errSession: root.dataset.errorSession || "Session expirée. Veuillez recharger la page.",
    errCheck: root.dataset.errorCheck || "Veuillez vérifier les champs du formulaire.",
    loading: root.dataset.jsLoading || "Chargement...",
    errLoading: root.dataset.jsErrorLoading || "Erreur",
    selectLevel: root.dataset.jsSelectLevel || "Sélectionner un niveau",
    selectCenter: root.dataset.jsSelectCenter || "Sélectionner un centre",
    selectGroup: root.dataset.jsSelectGroup || "Sélectionner un groupe",
    selectDate: root.dataset.jsSelectDate || "Sélectionner une date",
    groupLabel: root.dataset.jsGroupLabel || "Groupe",
    groupNight: root.dataset.jsGroupNight || "Groupe Nuit",
  };

  // Scoped DOM queries (all within root)
  const formSteps = root.querySelectorAll(".form-step");
  const progressSteps = root.querySelectorAll(".progress-step");
  const progressFill = root.querySelector("#glsProgressFill");

  const nextBtn = root.querySelector("#glsNextBtn");
  const prevBtn = root.querySelector("#glsPrevBtn");
  const errorMessage = root.querySelector("#glsErrorMessage");
  const form = root.querySelector("#glsMultiStepForm");
  const successMessage = root.querySelector("#glsSuccessMessage");
  const progressContainer = root.querySelector("#glsProgressContainer");
  const formHeader = root.querySelector("#glsFormHeader");
  const buttonGroup = root.querySelector(".button-group");

  // Form inputs - all scoped within root
  const typeCours = root.querySelector("#glsTypeCours");
  const centreWrapper = root.querySelector("#glsCentreWrapper");
  const centreSelect = root.querySelector("#glsCentre");
  const groupSelect = root.querySelector("#glsGroupId");
  const niveauSelect = root.querySelector("#glsNiveau");
  const dateInput = root.querySelector("#glsDateStart");
  const horairePrefereInput = root.querySelector("#glsHorairePrefere");

  // ===== Helpers =====
  function getLocalePrefix() {
    const lang = (document.documentElement.lang || "fr").toLowerCase();
    // If your site uses "fr", "en", etc.
    const locale = lang.split("-")[0] || "fr";
    return `/${locale}`;
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
  }

  function showError(msg) {
    errorMessage.textContent = msg;
    errorMessage.classList.add("active");
  }

  function clearError() {
    errorMessage.textContent = "";
    errorMessage.classList.remove("active");
  }

  async function parseResponse(res) {
    const contentType = (res.headers.get("content-type") || "").toLowerCase();

    // Try JSON if server says it's JSON
    if (contentType.includes("application/json")) {
      const data = await res.json();
      return { ok: res.ok, status: res.status, data, rawText: null };
    }

    // Otherwise read as text (often HTML error page)
    const text = await res.text();
    return { ok: res.ok, status: res.status, data: null, rawText: text };
  }

  function mapHttpErrorToMessage(status) {
    if (status === 419) return t.errSession + " (419)";
    if (status === 422) return t.errCheck + " (422)";
    if (status === 404) return t.errServer + " (404)";
    if (status === 500) return t.errServer + " (500)";
    return t.errGeneric + ` (HTTP ${status})`;
  }

  // ===== Init: centre wrapper visibility =====
  centreWrapper.style.display = "none";
  centreSelect.removeAttribute("required");

  // Static levels with French and English descriptions
  const NIVEAUX_DATA = [
    { code: "A0", fr: "A0 – Aucune connaissance préalable", en: "A0 – No prior knowledge" },
    { code: "A1", fr: "A1 – Débutant", en: "A1 – Beginner" },
    { code: "A2", fr: "A2 – Élémentaire", en: "A2 – Elementary" },
    { code: "B1", fr: "B1 – Intermédiaire", en: "B1 – Intermediate" },
    { code: "B2", fr: "B2 – Intermédiaire Supérieur", en: "B2 – Upper Intermediate" },
    { code: "C1", fr: "C1 – Avancé", en: "C1 – Advanced" }
  ];

  function loadStaticLevels() {
    const locale = document.documentElement.lang || "fr";
    const isFrench = locale.toLowerCase().startsWith("fr");

    niveauSelect.innerHTML = `<option value="">${t.selectLevel}</option>`;
    NIVEAUX_DATA.forEach((level) => {
      const text = isFrench ? level.fr : level.en;
      niveauSelect.innerHTML += `<option value="${level.code}">${text}</option>`;
    });
  }
  loadStaticLevels();

  let flatpickrInstance = null;

  /* ============================== LOAD CENTERS ============================== */
  function loadCenters() {
    centreSelect.disabled = true;
    centreSelect.innerHTML = `<option value="" disabled selected>${t.loading}</option>`;

    fetch("/api/centers", {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    })
      .then((res) => res.json())
      .then((data) => {
        centreSelect.innerHTML = `<option value="">${t.selectCenter}</option>`;
        data.forEach((c) => {
          centreSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });
        centreSelect.disabled = false;
      })
      .catch(() => {
        centreSelect.innerHTML = `<option value="" disabled selected>${t.errLoading}</option>`;
        centreSelect.disabled = false;
      });
  }

  /* ============================== LOAD DATES ============================== */
  function loadDatesForGroup(groupId) {
    dateInput.value = "";

    // Destroy existing flatpickr instance
    if (flatpickrInstance) flatpickrInstance.destroy();

    // IMPORTANT: keep selector scoped? flatpickr needs actual input element
    flatpickrInstance = flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      minDate: "today",
      placeholder: t.selectDate
    });

    console.log("[GLS Form] Date picker initialized for group:", groupId);
  }

  /* ============================== GROUP SELECT EVENTS ============================== */
  groupSelect.addEventListener("change", () => {
    const selected = groupSelect.options[groupSelect.selectedIndex];
    if (!selected || !selected.value) return;

    const groupText = selected.textContent || "";
    console.log("[GLS Form] Group selected:", groupText);

    const timeMatch = groupText.match(/(\d{1,2}:\d{2}\s*–\s*\d{1,2}:\d{2})/);
    const groupTime = timeMatch ? timeMatch[1] : "";

    horairePrefereInput.value = groupTime;
    console.log("[GLS Form] Set horaire_prefere to:", groupTime);

    loadDatesForGroup(selected.value);
  });

  /* ============================== UPDATE GROUP TIMES BASED ON CENTER ============================== */
  function updateGroupTimes() {
    const selectedCenter = centreSelect.options[centreSelect.selectedIndex];
    if (!selectedCenter || !selectedCenter.value) return;

    const centerText = (selectedCenter.textContent || "").toLowerCase();

    const gl = t.groupLabel;
    const groupsByCenter = {
      rabat: [
        { id: 1, name: gl + " 10:00 – 12:00" },
        { id: 2, name: gl + " 15:00 – 17:00" },
        { id: 3, name: gl + " 17:00 – 19:00" },
        { id: 4, name: gl + " 19:00 – 21:00" }
      ],
      casablanca: [
        { id: 5, name: gl + " 10:00 – 12:00" },
        { id: 6, name: gl + " 15:00 – 17:00" },
        { id: 7, name: gl + " 17:00 – 19:00" },
        { id: 8, name: gl + " 19:00 – 21:00" }
      ],
      casa: [
        { id: 5, name: gl + " 10:00 – 12:00" },
        { id: 6, name: gl + " 15:00 – 17:00" },
        { id: 7, name: gl + " 17:00 – 19:00" },
        { id: 8, name: gl + " 19:00 – 21:00" }
      ],
      marrakech: [
        { id: 9, name: gl + " 10:00 – 12:30" },
        { id: 10, name: gl + " 16:00 – 18:30" },
        { id: 11, name: gl + " 18:30 – 21:00" }
      ],
      sale: [
        { id: 13, name: gl + " 10:00 – 12:00" },
        { id: 14, name: gl + " 15:00 – 17:00" },
        { id: 15, name: gl + " 17:00 – 19:00" },
        { id: 16, name: gl + " 19:00 – 21:00" }
      ],
      kenitra: [
        { id: 17, name: gl + " 10:00 – 12:30" },
        { id: 18, name: gl + " 16:00 – 18:30" },
        { id: 19, name: gl + " 18:30 – 21:00" }
      ],
      agadir: [
        { id: 21, name: gl + " 10:00 – 12:30" },
        { id: 22, name: gl + " 16:00 – 18:30" },
        { id: 23, name: gl + " 19:00 – 21:30" }
      ],
      online: [{ id: 25, name: t.groupNight + " 20:00 – 22:00" }]
    };

    let groups = [];
    for (const [city, cityGroups] of Object.entries(groupsByCenter)) {
      if (centerText.includes(city)) {
        groups = cityGroups;
        break;
      }
    }

    groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
    groups.forEach((group) => {
      groupSelect.innerHTML += `<option value="${group.id}">${group.name}</option>`;
    });

    // Reset dependent fields when center changes
    dateInput.value = "";
    horairePrefereInput.value = "";
    if (flatpickrInstance) {
      flatpickrInstance.destroy();
      flatpickrInstance = null;
    }
  }

  /* ============================== TYPE COURS EVENTS ============================== */
  typeCours.addEventListener("change", () => {
    clearError();

    if (typeCours.value === "presentiel") {
      centreWrapper.style.display = "block";
      centreSelect.setAttribute("required", "required");
      loadCenters();

      // Reset group/date/time when switching type
      groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
      dateInput.value = "";
      horairePrefereInput.value = "";
    } else if (typeCours.value === "en_ligne") {
      centreWrapper.style.display = "none";
      centreSelect.removeAttribute("required");
      centreSelect.innerHTML = "";

      groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
      groupSelect.innerHTML += `<option value="25">${t.groupNight} 20:00 – 22:00</option>`;

      dateInput.value = "";
      horairePrefereInput.value = "20:00 – 22:00";

      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }
      // Optional: init date picker immediately for online group
      loadDatesForGroup(25);
    } else {
      centreWrapper.style.display = "none";
      centreSelect.removeAttribute("required");
      centreSelect.innerHTML = "";

      groupSelect.innerHTML = `<option value="">${t.selectGroup}</option>`;
      dateInput.value = "";
      horairePrefereInput.value = "";

      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }
    }
  });

  centreSelect.addEventListener("change", function () {
    clearError();
    updateGroupTimes();
  });

  /* ============================== PROGRESS SYSTEM ============================== */
  function updateProgress() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressFill.style.width = progress + "%";

    progressSteps.forEach((step, i) => {
      step.classList.remove("active", "completed");
      if (i + 1 < currentStep) step.classList.add("completed");
      if (i + 1 === currentStep) step.classList.add("active");
    });

    formSteps.forEach((step) => {
      step.classList.toggle("active", step.dataset.step == currentStep);
    });

    prevBtn.style.display = currentStep === 1 ? "none" : "block";
    nextBtn.textContent = currentStep === totalSteps ? t.submit : t.next;
  }

  function validateStep() {
    clearError();

    const currentEl = root.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredInputs = currentEl.querySelectorAll("[required]");

    for (let input of requiredInputs) {
      // A disabled control (e.g. a <select> still loading its options) can't
      // hold a real user choice yet — block the step instead of trusting its value.
      if (input.disabled) {
        showError(t.errRequired);
        return false;
      }

      const value = (input.value || "").trim();
      if (!value) {
        showError(t.errRequired);
        input.focus();
        return false;
      }

      if (input.tagName === "SELECT") {
        const selectedOption = input.options[input.selectedIndex];
        if (!selectedOption || selectedOption.disabled) {
          showError(t.errRequired);
          input.focus();
          return false;
        }
      }
    }
    return true;
  }

  /* ============================== STEP 1 PARTIAL SAVE ============================== */
  // Fires the moment step 1 validates on "Continuer", so we keep the lead even if the
  // visitor never finishes steps 2-3. Fire-and-forget: never blocks the step transition
  // or shows an error to the visitor if it fails (this is a background capture, not
  // part of the required submission flow).
  const step1SaveUrl = root.dataset.step1SaveUrl;
  let step1Saved = false;

  function saveStep1Data() {
    if (step1Saved || !step1SaveUrl) return;

    const csrf = getCsrfToken();
    if (!csrf) return;

    const payload = new FormData();
    payload.append("nom", root.querySelector("#glsNom").value.trim());
    payload.append("prenom", root.querySelector("#glsPrenom").value.trim());
    payload.append("email", root.querySelector("#glsEmail").value.trim());
    payload.append("phone", root.querySelector("#glsPhone").value.trim());
    payload.append("adresse", root.querySelector("#glsAdresse").value.trim());
    payload.append("form_source", "modal");

    step1Saved = true;

    fetch(step1SaveUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: payload
    }).catch((err) => {
      console.error("[GLS Form] Step 1 partial save failed:", err);
      // Allow a retry if the visitor edits step 1 fields and clicks Continuer again.
      step1Saved = false;
    });
  }

  /* ============================== SUBMIT (SAFE JSON) ============================== */
  async function submitForm() {
    // Guard against double-submits (double-click / rapid retry). Without this both the
    // network request AND the Send_Clicked event fire more than once per real attempt.
    if (submitting) return;

    const csrf = getCsrfToken();
    if (!csrf) {
      showError(t.errSession);
      return;
    }

    submitting = true;
    if (nextBtn) nextBtn.disabled = true;

    if (window.glsTrack) {
      window.glsTrack("Inscription_Modal_Send_Clicked", { event_category: "GLS Inscription", event_label: "Modal" });
    }

    const formData = new FormData(form);
    
    // Remove centre field for en_ligne courses (online courses don't need a center)
    if (typeCours.value === "en_ligne") {
      formData.delete("centre");
    }
    
    formData.append("form_source", "modal");

    // Use current locale instead of hardcoding /fr
    const endpoint = `${getLocalePrefix()}/gls-inscription`;

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
      });

      const parsed = await parseResponse(res);

      if (!parsed.ok) {
        // If HTML returned, log it (first chars) to find the real error (419/500/redirect page...)
        if (parsed.rawText) {
          console.error("[GLS Form] Non-JSON response:", parsed.status, parsed.rawText.slice(0, 500));
        } else {
          console.error("[GLS Form] JSON error:", parsed.status, parsed.data);
        }

        // If validation errors from Laravel (422 JSON)
        if (parsed.status === 422 && parsed.data && parsed.data.errors) {
          const firstKey = Object.keys(parsed.data.errors)[0];
          const firstMsg = firstKey ? parsed.data.errors[firstKey][0] : null;
          showError(firstMsg || mapHttpErrorToMessage(parsed.status));
          return;
        }

        showError(mapHttpErrorToMessage(parsed.status));
        return;
      }

      // OK
      const data = parsed.data || {};

      if (data.status === "success") {
        submitted = true;
        if (window.glsTrack) {
          window.glsTrack("Inscription_Modal_Submitted", {
            event_category: "GLS Inscription",
            form_source: "modal"
          });
        }

        form.style.display = "none";
        progressContainer.style.display = "none";
        buttonGroup.style.display = "none";
        formHeader.style.display = "none";
        successMessage.classList.add("active");
        return;
      }

      if (data.status === "duplicate") {
        showError(data.message || t.errDuplicate);
        return;
      }

      showError(data.message || t.errGeneric);
    } catch (err) {
      console.error(err);
      showError(t.errConnection);
    } finally {
      // Always release the submit lock so the visitor can retry after a failure.
      // On success the form is hidden behind the success message, so re-enabling
      // the button is harmless and cannot produce a second submission.
      submitting = false;
      if (nextBtn && !submitted) nextBtn.disabled = false;
    }
  }

  /* ============================== NEXT BUTTON ============================== */
  // false = visitor has not yet completed a step and clicked Continuer, so no
  // abandonment event fires if they just open and immediately close the modal.
  // (submitting/submitted are declared at the top of the IIFE, with the other state.)
  let hasEngaged = false;

  // Fires once per step, the moment the visitor completes THAT step's fields and
  // clicks Continuer (i.e. "Step N completed" — separate events per step number).
  const STEP_COMPLETED_EVENTS = {
    1: "Inscription_Modal_Step1_Completed",
    2: "Inscription_Modal_Step2_Completed"
  };

  nextBtn.addEventListener("click", () => {
    if (!validateStep()) return;

    const completedStep = currentStep;

    if (completedStep === 1) {
      saveStep1Data();
    }

    if (currentStep === totalSteps) {
      submitForm();
      return;
    }

    currentStep++;
    updateProgress();

    hasEngaged = true;

    const stepEvent = STEP_COMPLETED_EVENTS[completedStep];
    if (stepEvent && window.glsTrack) {
      window.glsTrack(stepEvent, {
        event_category: "GLS Inscription",
        event_label: "Modal - Step " + completedStep + " Completed"
      });
    }
  });

  /* ============================== PREV BUTTON ============================== */
  prevBtn.addEventListener("click", () => {
    if (currentStep > 1) {
      currentStep--;
      updateProgress();
    }
  });

  // Initial progress update
  updateProgress();

  // Track form impression when modal opens.
  // NOTE: this must fire ONLY on show.bs.modal. The modal markup lives in the global
  // layout, so firing on script init would report an impression on every page view.
  const modal = document.getElementById("glsEnrollModal");
  if (modal) {
    modal.addEventListener("show.bs.modal", function () {
      if (window.glsTrack) {
        window.glsTrack("Inscription_Modal_Viewed", {
          event_category: "GLS Inscription",
          form_source: "modal"
        });
      }
    });

    // Listen for modal close to reset state
    modal.addEventListener("hidden.bs.modal", function () {
      // Visitor closed the modal without submitting, AFTER genuinely engaging (i.e. they
      // completed at least step 1 and advanced at least once — opening and closing with
      // no interaction does not count). We report the step they were ACTUALLY ON when
      // they left (currentStep), not the furthest step ever reached, so a visitor who
      // advances then navigates Back and quits is attributed to the step they quit on.
      if (!submitted && hasEngaged && window.glsTrack) {
        window.glsTrack("Inscription_Modal_Abandoned_At_Step_" + currentStep, {
          event_category: "GLS Inscription",
          event_label: "Modal - Abandoned at step " + currentStep
        });
      }
      hasEngaged = false;
      submitted = false;
      step1Saved = false;

      // Clear the submit lock too: closing the modal mid-request would otherwise leave
      // the next/submit button permanently disabled the next time it is reopened.
      submitting = false;
      if (nextBtn) nextBtn.disabled = false;

      currentStep = 1;
      form.reset();
      clearError();
      successMessage.classList.remove("active");

      form.style.display = "";
      progressContainer.style.display = "";
      buttonGroup.style.display = "";
      formHeader.style.display = "";

      // Reset date picker
      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }

      updateProgress();
    });
  }
})();