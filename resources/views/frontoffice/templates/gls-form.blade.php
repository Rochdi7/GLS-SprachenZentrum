<div class="container">
    <div class="form-card">
        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">
            ✕
        </button>
        <div class="decorative-element"></div>
        <div class="form-content">

            <div class="form-header">
                <h1 class="form-title">Inscription GLS</h1>
                <p class="form-subtitle">Complétez les étapes pour envoyer votre demande</p>
            </div>

            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-steps">
                    <span class="progress-step active" data-step="1" data-number="1">Informations</span>
                    <span class="progress-step" data-step="2" data-number="2">Niveau</span>
                    <span class="progress-step" data-step="3" data-number="3">Préférences</span>
                    <span class="progress-step" data-step="4" data-number="4">Centre GLS</span>
                </div>
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="multiStepForm">

                <!-- STEP 1 – About you -->
                <div class="form-step active" data-step="1">

                    <div class="form-group">
                        <label for="name">Nom complet <span class="required">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Votre nom complet" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="+212 650-123456" required>
                    </div>

                    <div class="form-group">
                        <label for="adresse">Adresse <span class="required">*</span></label>
                        <input type="text" id="adresse" name="adresse" placeholder="Votre adresse complète"
                            required>
                    </div>
                </div>

                <!-- STEP 2 – Language level -->
                <div class="form-step" data-step="2">

                    <div class="form-group">
                        <label for="niveau">Niveau d’Allemand <span class="required">*</span></label>
                        <select id="niveau" name="niveau" required>
                            <option value="">Choisissez votre niveau</option>
                            <option value="A1">A1 – Débutant</option>
                            <option value="A2">A2 – Élémentaire</option>
                            <option value="B1">B1 – Intermédiaire</option>
                            <option value="B2">B2 – Avancé</option>
                            <option value="C1">C1 – Autonome</option>
                        </select>
                    </div>

                </div>

                <!-- STEP 3 – Preferences -->
                <div class="form-step" data-step="3">

                    <div class="form-group">
                        <label for="type_cours">Type de cours</label>
                        <select id="type_cours" name="type_cours">
                            <option value="">Choisissez un type</option>
                            <option value="presentiel">Cours présentiel</option>
                            <option value="en_ligne">Cours en ligne</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="horaire_prefere">Horaire préféré</label>
                        <input type="time" id="horaire_prefere" name="horaire_prefere">
                    </div>

                    <div class="form-group">
                        <label for="date_start">À partir de...</label>
                        <input type="date" id="date_start" name="date_start">
                    </div>
                </div>

                <!-- STEP 4 – Center selection -->
                <div class="form-step" data-step="4">

                    <div class="form-group">
                        <label for="centre">Centre GLS préféré <span class="required">*</span></label>
                        <select id="centre" name="centre" required>
                            <option value="">Choisissez un centre</option>
                            <option>GLS Agdal, Rabat</option>
                            <option>GLS Salé</option>
                            <option>GLS Kénitra</option>
                            <option>GLS Casablanca</option>
                            <option>GLS Marrakech</option>
                            <option>GLS Agadir</option>
                        </select>
                    </div>

                </div>

                <div class="button-group">
                    <button type="button" class="button" id="prevBtn">Retour</button>
                    <button type="button" class="button" id="nextBtn">Continuer</button>
                </div>
            </form>

            <div class="success-message" id="successMessage">
                <div class="success-icon"></div>
                <h3>Merci !</h3>
                <p>Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.</p>
            </div>
        </div>
    </div>
</div>
<style>
    :root {
        --color-surface: #fffee8;
        --primary-1: #211e1d;
        --primary-2: #3e3832;
    }

    .form-card {
        background: var(--color-surface);
        border-radius: 28px;
        padding: 56px;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(33, 30, 29, 0.08);
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
        pointer-events: none;
    }

    .decorative-element {
        position: absolute;
        width: 200px;
        height: 200px;
        border-radius: 50%;

        /* updated with GLS tones */
        background: linear-gradient(135deg, var(--light--off-black), var(--dark--off-black));

        opacity: 0.05;
        top: -100px;
        right: -100px;
        z-index: 0;
    }

    .form-content {
        position: relative;
        z-index: 1;
    }

    .form-header {
        text-align: center;
        margin-bottom: 48px;
    }

    .form-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 36px;
        font-weight: 700;

        background: linear-gradient(135deg, var(--off-black), var(--light--off-black));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .form-subtitle {
        font-size: 17px;
        color: var(--light--off-black);
    }

    .progress-container {
        margin-bottom: 48px;
    }

    .progress-bar {
        height: 8px;
        background: #f1f5f9;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 28px;
        position: relative;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
                transparent 0%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 100%);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-1), var(--primary-2));
        border-radius: 10px;
        transition: width 0.5s;
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        gap: 16px;
    }

    .progress-step {
        flex: 1;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--light--off-black);
        position: relative;
        padding-top: 32px;
    }

    .progress-step::before {
        content: attr(data-number);
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--light--off-black);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
    }

    .progress-step.active {
        color: var(--primary-1);
    }

    .progress-step.active::before {
        background: linear-gradient(135deg, var(--primary-1), var(--primary-2));
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .progress-step.completed {
        color: #0caa6a;
    }

    .progress-step.completed::before {
        content: '✓';
        background: #0caa6a;
        color: white;
    }

    .form-step {
        display: none;
        animation: fadeSlideIn 0.5s ease;
    }

    .form-step.active {
        display: block;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-group {
        margin-bottom: 28px;
    }

    label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--dark--off-black);
        margin-bottom: 10px;
    }

    .required {
        color: #ef4444;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 16px 18px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background: white;
        color: var(--off-black);
    }

    input:hover,
    select:hover,
    textarea:hover {
        border-color: var(--light--off-black);
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary-1);
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    input::placeholder,
    textarea::placeholder {
        color: #94a3b8;
    }

    textarea {
        resize: vertical;
        min-height: 130px;
    }

    .button-group {
        display: flex;
        gap: 14px;
        margin-top: 40px;
    }

    .success-message h3 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 32px;
        background: linear-gradient(135deg, var(--primary-1), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .button {
        background-color: var(--off-black);
        color: var(--color-surface);
        border-radius: 8rem;
        padding: .75rem 2rem;
        font-weight: 700;
        transition: .25s ease;
        display: inline-block !important;
        width: auto !important;
        max-width: fit-content !important;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        letter-spacing: -0.01em;
    }

    .button:hover {
        background-color: var(--light--off-black);
        transform: translateY(-2px);
    }

    .button:active {
        background-color: var(--dark--off-black);
        transform: translateY(0);
    }

    .button:disabled,
    .button.disabled {
        opacity: .4;
        cursor: not-allowed;
        transform: none;
    }

    /* 📌 MOBILE FIX — max-width 575px */
    @media (max-width: 575px) {

        .form-card {
            padding: 24px !important;
            /* Avant 56px */
            border-radius: 18px;
        }

        .decorative-element {
            width: 120px !important;
            height: 120px !important;
            top: -60px !important;
            right: -60px !important;
        }

        .form-title {
            font-size: 26px !important;
            line-height: 1.2;
        }

        .form-subtitle {
            font-size: 14px !important;
            padding: 0 10px;
        }

        .progress-container {
            margin-bottom: 32px !important;
        }

        .progress-steps {
            gap: 6px !important;
        }

        .progress-step {
            font-size: 10px !important;
            padding-top: 26px !important;
        }

        .progress-step::before {
            width: 26px !important;
            height: 26px !important;
            font-size: 11px !important;
        }

        input,
        select,
        textarea {
            padding: 14px !important;
            font-size: 14px !important;
        }

        label {
            font-size: 13px !important;
        }

        .button-group {
            display: flex;
            flex-direction: row !important;
            justify-content: space-between;
            gap: 10px !important;
            margin-top: 30px !important;
        }

        .button-group .button {
            width: 50% !important;
            text-align: center;

            font-size: 15px !important;
        }
    }

    /* Close button */
    .close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 50%;
        background: var(--off-black);
        color: var(--color-surface);
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: .25s ease;
    }

    .close-btn:hover {
        background: var(--light--off-black);
        transform: scale(1.05);
    }

    .close-btn:active {
        transform: scale(0.95);
    }

    /* Mobile size */
    @media (max-width: 575px) {
        .close-btn {
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            font-size: 16px;
        }
    }

    .success-message {
        display: none;
        text-align: center;
        margin-top: 20px;
    }

    .success-message.active {
        display: block;
    }

    #errorMessage {
        color: #e11d48;
        font-size: 14px;
        margin-bottom: 12px;
        display: none;
    }

    #errorMessage.active {
        display: block;
    }
</style>

<script>
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

    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressFill.style.width = progress + '%';

        // Steps UI
        progressSteps.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');

            if (stepNum < currentStep) step.classList.add('completed');
            if (stepNum === currentStep) step.classList.add('active');
        });

        // Show correct form-step
        formSteps.forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
        });

        // Buttons
        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        nextBtn.textContent = currentStep === totalSteps ? 'Envoyer' : 'Continuer';

        errorMessage.classList.remove('active');
    }

    function validateStep() {
        const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const requiredInputs = currentStepEl.querySelectorAll('[required]');

        for (let input of requiredInputs) {
            // Required empty
            if (!input.value.trim()) {
                errorMessage.textContent = 'Veuillez remplir les champs obligatoires.';
                errorMessage.classList.add('active');
                input.focus();
                return false;
            }

            // Email validation
            if (input.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    errorMessage.textContent = 'Veuillez saisir une adresse email valide.';
                    errorMessage.classList.add('active');
                    input.focus();
                    return false;
                }
            }
        }

        return true;
    }

    nextBtn.addEventListener('click', () => {
        if (!validateStep()) return;

        // If it's the last step → SEND to Laravel
        if (currentStep === totalSteps) {

            const formData = new FormData(form);

            fetch("{{ route('gls.inscription') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: formData
                })
                .then(async (res) => {
                    const data = await res.json();

                    if (res.status === 409 && data.status === "duplicate") {
                        // MESSAGE DOUBLON
                        errorMessage.textContent = data.message;
                        errorMessage.classList.add('active');
                        return;
                    }

                    if (data.status === "success") {
                        form.style.display = 'none';
                        document.querySelector('.progress-container').style.display = 'none';
                        document.querySelector('.button-group').style.display = 'none';
                        document.querySelector('.form-header').style.display = 'none';
                        document.querySelector('.decorative-element').style.display = 'none';

                        successMessage.classList.add('active');
                    }
                })
                .catch(err => {
                    console.error(err);
                    errorMessage.textContent = "Une erreur est survenue. Réessayez.";
                    errorMessage.classList.add('active');
                });


            return;
        }

        currentStep++;
        updateProgress();
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateProgress();
        }
    });

    // Prevent Enter key from skipping steps
    form.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            nextBtn.click();
        }
    });

    // Init
    updateProgress();
</script>
