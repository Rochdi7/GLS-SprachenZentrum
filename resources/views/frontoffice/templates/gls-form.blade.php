<div class="container">
    <div class="form-card">

        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

        <div class="decorative-element"></div>

        <div class="form-content">

            <div class="form-header">
                <h1 class="form-title">Inscription GLS</h1>
                <p class="form-subtitle">Complétez les étapes pour envoyer votre demande</p>
            </div>

            <!-- Progress -->
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-steps">
                    <span class="progress-step active" data-step="1" data-number="1">Informations</span>
                    <span class="progress-step" data-step="2" data-number="2">Centre GLS</span>
                    <span class="progress-step" data-step="3" data-number="3">Niveau</span>
                    <span class="progress-step" data-step="4" data-number="4">Préférences</span>
                </div>
            </div>

            <div class="error-message" id="errorMessage"></div>

            <form id="multiStepForm">

                <!-- STEP 1 — INFORMATIONS -->
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

                <!-- STEP 2 — TYPE + CENTRE -->
                <div class="form-step" data-step="2">

                    <div class="form-group">
                        <label for="type_cours">Type de cours <span class="required">*</span></label>
                        <select id="type_cours" name="type_cours" required>
                            <option value="">Choisissez un type</option>
                            <option value="presentiel">Cours présentiel</option>
                            <option value="en_ligne">Cours en ligne</option>
                        </select>
                    </div>

                    <div class="form-group" id="centreWrapper">
                        <label for="centre">Centre GLS préféré <span class="required">*</span></label>
                        <select id="centre" name="centre">
                            <option value="">Sélectionner un centre</option>
                        </select>
                    </div>
                </div>

                <!-- STEP 3 — NIVEAU -->
                <div class="form-step" data-step="3">

                    <div class="form-group">
                        <label for="niveau">Niveau d’Allemand <span class="required">*</span></label>
                        <select id="niveau" name="niveau" required>
                            <option value="">Sélectionner un niveau</option>
                        </select>
                    </div>

                </div>

                <!-- STEP 4 — PREFERENCES -->
                <div class="form-step" data-step="4">

                    <div class="form-group">
                        <label for="horaire_prefere">Horaire de cours</label>
                        <input type="text" id="horaire_prefere" name="horaire_prefere" readonly
                            placeholder="Auto rempli">
                    </div>


                    <div class="form-group">
                        <label for="date_start">À partir de...</label>
                        <input type="text" id="date_start" name="date_start" placeholder="Sélectionner une date">

                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="button-group">
                    <button type="button" class="button" id="prevBtn">Retour</button>
                    <button type="button" class="button" id="nextBtn">Continuer</button>
                </div>
            </form>

            <!-- SUCCESS MESSAGE -->
            <div class="success-message" id="successMessage">
                <div class="success-icon"></div>
                <h3>Merci !</h3>
                <p>Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.</p>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
