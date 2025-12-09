
let currentStep = 1;
const totalSteps = 4;

const formSteps = document.querySelectorAll('.form-step');
const progressSteps = document.querySelectorAll('.progress-step');
const progressFill = document.getElementById('progressFill');

const nextBtn = document.getElementById('nextBtn');
const prevBtn = document.getElementById('prevBtn');
const errorMessage = document.getElementById('errorMessage');
const form = document.getElementById('multiStepForm');
const successMessage = document.getElementById('successMessage');

// Inputs
const typeCours = document.getElementById("type_cours");
const centreWrapper = document.getElementById("centreWrapper");
const centreSelect = document.getElementById("centre");
const niveauSelect = document.getElementById("niveau");
const dateInput = document.getElementById("date_start");

// ✅ REQUIRED FIX
const horairePrefereInput = document.getElementById("horaire_prefere");

// Hide the centre dropdown until "presentiel"
centreWrapper.style.display = "none";
centreSelect.removeAttribute("required");

// Static levels
const NIVEAUX = ["A1", "A2", "B1", "B2"];
function loadStaticLevels() {
    niveauSelect.innerHTML = '<option value="">Sélectionner un niveau</option>';
    NIVEAUX.forEach(level => {
        niveauSelect.innerHTML += `<option value="${level}">${level}</option>`;
    });
}
loadStaticLevels();

let flatpickrInstance = null;

/* ======================================================
   LOAD CENTERS
====================================================== */
function loadCenters() {
    centreSelect.innerHTML = "<option>Chargement...</option>";

    fetch("/api/centers")
        .then(res => res.json())
        .then(data => {
            centreSelect.innerHTML = '<option value="">Sélectionner un centre</option>';
            data.forEach(c =>
                centreSelect.innerHTML += `<option value="${c.id}">${c.name} (${c.city})</option>`
            );
        })
        .catch(() => centreSelect.innerHTML = "<option>Erreur</option>");
}

/* ======================================================
   LOAD AVAILABLE DATES FROM API
====================================================== */
function loadAvailableDates() {
    const siteId = centreSelect.value;
    const level = niveauSelect.value;

    if (!siteId || !level) return;

    dateInput.value = "";
    dateInput.placeholder = "Chargement...";

    fetch(`/api/groups/dates/${siteId}/${level}`)
        .then(res => res.json())
        .then(availableDates => {
            if (!availableDates.length) {
                dateInput.placeholder = "Aucune date disponible";
                return;
            }

            if (flatpickrInstance) {
                flatpickrInstance.destroy();
            }

            flatpickrInstance = flatpickr("#date_start", {
                dateFormat: "Y-m-d",
                disable: [
                    d => !availableDates.includes(d.toISOString().split("T")[0])
                ],
                onDayCreate: function(_, __, ___, dayElem) {
                    const date = dayElem.dateObj.toISOString().split("T")[0];
                    if (availableDates.includes(date)) {
                        dayElem.classList.add("available-date");
                    }
                }
            });

            dateInput.placeholder = "Sélectionner une date";
        });
}

/* ======================================================
   LOAD TIME RANGE (HORRAIRE)
====================================================== */
function loadTimeRange() {
    const siteId = centreSelect.value;
    const level = niveauSelect.value;

    if (!siteId || !level) return;

    fetch(`/api/groups/time/${siteId}/${level}`)
        .then(res => res.json())
        .then(data => {
            horairePrefereInput.value = data.time_range
                ? data.time_range
                : "Aucun horaire trouvé";
        });
}

/* ======================================================
   SHOW/HIDE CENTER BASED ON COURSE TYPE
====================================================== */
typeCours.addEventListener("change", () => {
    if (typeCours.value === "presentiel") {
        centreWrapper.style.display = "block";
        centreSelect.setAttribute("required", "required");
        loadCenters();
    } else {
        centreWrapper.style.display = "none";
        centreSelect.removeAttribute("required");
        centreSelect.innerHTML = "";
        dateInput.value = "";
        horairePrefereInput.value = "";
    }
});

// Reload dates & time when centre or level changes
centreSelect.addEventListener("change", loadAvailableDates);
niveauSelect.addEventListener("change", loadAvailableDates);

centreSelect.addEventListener("change", loadTimeRange);
niveauSelect.addEventListener("change", loadTimeRange);

/* ======================================================
   STEP LOGIC
====================================================== */
function updateProgress() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressFill.style.width = progress + '%';

    progressSteps.forEach((step, i) => {
        step.classList.remove("active", "completed");
        if (i + 1 < currentStep) step.classList.add("completed");
        if (i + 1 === currentStep) step.classList.add("active");
    });

    formSteps.forEach(step => {
        step.classList.toggle("active", step.dataset.step == currentStep);
    });

    prevBtn.style.display = currentStep === 1 ? "none" : "block";
    nextBtn.textContent = currentStep === totalSteps ? "Envoyer" : "Continuer";
}

function validateStep() {
    const currentEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredInputs = currentEl.querySelectorAll("[required]");

    for (let input of requiredInputs) {
        if (!input.value.trim()) {
            errorMessage.textContent = "Veuillez remplir les champs obligatoires.";
            errorMessage.classList.add("active");
            input.focus();
            return false;
        }
    }
    return true;
}

/* ======================================================
   NEXT BUTTON
====================================================== */
nextBtn.addEventListener("click", () => {
    if (!validateStep()) return;

    if (currentStep === totalSteps) {
        const formData = new FormData(form);

        fetch("{{ route('gls.inscription') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                form.style.display = "none";
                document.querySelector(".progress-container").style.display = "none";
                document.querySelector(".button-group").style.display = "none";
                document.querySelector(".form-header").style.display = "none";
                successMessage.classList.add("active");
            }
        });

        return;
    }

    currentStep++;
    updateProgress();
});

/* ======================================================
   PREVIOUS BUTTON
====================================================== */
prevBtn.addEventListener("click", () => {
    if (currentStep > 1) currentStep--;
    updateProgress();
});

updateProgress();
