<div class="container" id="glsInscriptionRoot" data-label-next="{{ __('templates/gls-form.buttons.next') }}"
    data-label-submit="{{ __('templates/gls-form.buttons.submit') }}"
    data-error-required="{{ __('templates/gls-form.errors.required_fields') }}"
    data-error-duplicate="{{ __('templates/gls-form.errors.duplicate') }}"
    data-error-connection="{{ __('templates/gls-form.errors.connection_error') }}"
    data-error-generic="{{ __('templates/gls-form.errors.generic') }}"
    data-error-server="{{ __('templates/gls-form.errors.server_error') }}"
    data-error-session="{{ __('templates/gls-form.errors.session_expired') }}"
    data-error-check="{{ __('templates/gls-form.errors.check_fields') }}"
    data-js-loading="{{ __('templates/gls-form.js.loading') }}"
    data-js-error-loading="{{ __('templates/gls-form.js.error_loading') }}"
    data-js-select-level="{{ __('templates/gls-form.js.select_level') }}"
    data-js-select-center="{{ __('templates/gls-form.js.select_center') }}"
    data-js-select-group="{{ __('templates/gls-form.js.select_group') }}"
    data-js-select-date="{{ __('templates/gls-form.js.select_date') }}"
    data-js-group-label="{{ __('templates/gls-form.js.group_label') }}"
    data-js-group-night="{{ __('templates/gls-form.js.group_night_label') }}">
    <div class="form-card">

        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

        <div class="decorative-element"></div>

        <div class="form-content">

            <div class="form-header" id="glsFormHeader">
                <h1 class="form-title">{{ __('templates/gls-form.header.title') }}</h1>
                <p class="form-subtitle">{{ __('templates/gls-form.header.subtitle') }}</p>
            </div>

            <!-- Progress -->
            <div class="progress-container" id="glsProgressContainer">
                <div class="progress-bar">
                    <div class="progress-fill" id="glsProgressFill"></div>
                </div>
                <div class="progress-steps" id="glsProgressSteps">
                    <span class="progress-step active" data-step="1"
                        data-number="1">{{ __('templates/gls-form.progress.steps.step1') }}</span>
                    <span class="progress-step" data-step="2"
                        data-number="2">{{ __('templates/gls-form.progress.steps.step2') }}</span>
                    <span class="progress-step" data-step="3"
                        data-number="3">{{ __('templates/gls-form.progress.steps.step3') }}</span>
                    {{-- <span class="progress-step" data-step="4"
                        data-number="4">{{ __('templates/gls-form.progress.steps.step4') }}</span> --}}
                </div>
            </div>

            <div class="error-message" id="glsErrorMessage"></div>

            <form id="glsMultiStepForm">

                <!-- STEP 1 — INFORMATIONS -->
                <div class="form-step active" data-step="1">

                    <div class="form-group">
                        <label for="glsNom">{{ __('templates/gls-form.fields.nom.label') }} <span
                                class="required">*</span></label>
                        <input type="text" id="glsNom" name="nom"
                            placeholder="{{ __('templates/gls-form.fields.nom.placeholder') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="glsPrenom">{{ __('templates/gls-form.fields.prenom.label') }} <span
                                class="required">*</span></label>
                        <input type="text" id="glsPrenom" name="prenom"
                            placeholder="{{ __('templates/gls-form.fields.prenom.placeholder') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="glsEmail">{{ __('templates/gls-form.fields.email.label') }} <span
                                class="required">*</span></label>
                        <input type="email" id="glsEmail" name="email"
                            placeholder="{{ __('templates/gls-form.fields.email.placeholder') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="glsPhone">{{ __('templates/gls-form.fields.phone.label') }} <span
                                class="required">*</span></label>
                        <input type="tel" id="glsPhone" name="phone"
                            placeholder="{{ __('templates/gls-form.fields.phone.placeholder') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="glsAdresse">{{ __('templates/gls-form.fields.adresse.label') }} <span
                                class="required">*</span></label>
                        <input type="text" id="glsAdresse" name="adresse"
                            placeholder="{{ __('templates/gls-form.fields.adresse.placeholder') }}" required>
                    </div>
                </div>

                <!-- STEP 2 — TYPE + CENTRE -->
                <div class="form-step" data-step="2">

                    <div class="form-group">
                        <label for="glsTypeCours">{{ __('templates/gls-form.fields.type_cours.label') }} <span
                                class="required">*</span></label>
                        <div class="att-select" data-att-select>
                            <select id="glsTypeCours" name="type_cours" class="att-select__native" required>
                                <option value="">{{ __('templates/gls-form.fields.type_cours.placeholder') }}
                                </option>
                                <option value="presentiel">
                                    {{ __('templates/gls-form.fields.type_cours_options.presentiel') }}</option>
                                <option value="en_ligne">{{ __('templates/gls-form.fields.type_cours_options.en_ligne') }}
                                </option>
                            </select>
                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                <span class="att-select__value att-select__value--placeholder">{{ __('templates/gls-form.fields.type_cours.placeholder') }}</span>
                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                        </div>
                    </div>

                    <div class="form-group" id="glsCentreWrapper">
                        <label for="glsCentre">{{ __('templates/gls-form.fields.centre.label') }} <span
                                class="required">*</span></label>
                        <div class="att-select" data-att-select>
                            <select id="glsCentre" name="centre" class="att-select__native">
                                <option value="">{{ __('templates/gls-form.fields.centre.placeholder') }}</option>
                            </select>
                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                <span class="att-select__value att-select__value--placeholder">{{ __('templates/gls-form.fields.centre.placeholder') }}</span>
                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 — GROUP + NIVEAU -->
                <div class="form-step" data-step="3">

                    <div class="form-group">
                        <label for="glsGroupId">{{ __('templates/gls-form.fields.group_id.label') }} <span
                                class="required">*</span></label>
                        <div class="att-select" data-att-select>
                            <select id="glsGroupId" name="group_id" class="att-select__native" required>
                                <option value="">{{ __('templates/gls-form.fields.group_id.placeholder') }}</option>
                                <!-- Groups will be populated dynamically based on selected center or type -->
                                <option value="25" id="onlineGroupOption" style="display: none;">Groupe Nuit 20:00 –
                                    22:00</option>
                            </select>
                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                <span class="att-select__value att-select__value--placeholder">{{ __('templates/gls-form.fields.group_id.placeholder') }}</span>
                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="glsNiveau">{{ __('templates/gls-form.fields.niveau.label') }} <span
                                class="required">*</span></label>
                        <div class="att-select" data-att-select>
                            <select id="glsNiveau" name="niveau" class="att-select__native" required>
                                <option value="">{{ __('templates/gls-form.fields.niveau.placeholder') }}</option>
                            </select>
                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                <span class="att-select__value att-select__value--placeholder">{{ __('templates/gls-form.fields.niveau.placeholder') }}</span>
                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                        </div>
                    </div>

                </div>

                <!-- STEP 4 — PREFERENCES -->
                {{-- <div class="form-step" data-step="4">

                    <div class="form-group">
                        <label
                            for="glsHorairePrefere">{{ __('templates/gls-form.fields.horaire_prefere.label') }}</label>
                        <input type="text" id="glsHorairePrefere" name="horaire_prefere" readonly
                            placeholder="{{ __('templates/gls-form.fields.horaire_prefere.placeholder') }}">
                    </div>


                    <div class="form-group">
                        <label for="glsDateStart">{{ __('templates/gls-form.fields.date_start.label') }}</label>
                        <input type="text" id="glsDateStart" name="date_start" required
                            placeholder="{{ __('templates/gls-form.fields.date_start.placeholder') }}">

                    </div>
                </div> --}}

                <!-- BUTTONS -->
                <div class="button-group">
                    <button type="button" class="button"
                        id="glsPrevBtn">{{ __('templates/gls-form.buttons.prev') }}</button>
                    <button type="button" class="button"
                        id="glsNextBtn">{{ __('templates/gls-form.buttons.next') }}</button>
                </div>
            </form>

            <!-- SUCCESS MESSAGE -->
            <div class="success-message" id="glsSuccessMessage">
                <div class="success-icon"></div>
                <h3>{{ __('templates/gls-form.messages.success_title') }}</h3>
                <p>{{ __('templates/gls-form.messages.success_text') }}</p>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
