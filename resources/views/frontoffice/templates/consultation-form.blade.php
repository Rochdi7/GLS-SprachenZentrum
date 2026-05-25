<!-- CONSULTATION POPUP -->
<div class="modal fade" id="consultationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">

            <div class="container" id="consultationRoot">
                <div class="form-card">

                    <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

                    <div class="decorative-element"></div>

                    <div class="form-content">

                        <div class="form-header" id="consultationFormHeader">
                            <h2 class="form-title">{{ __('templates/consultation-form.header.title') }}</h2>
                            <p class="form-subtitle">
                                {{ __('templates/consultation-form.header.subtitle') }}
                            </p>
                        </div>

                        <div class="error-message" id="consultationErrorMessage"></div>

                        <form id="consultationForm" method="POST" action="{{ route('front.consultation.store') }}">
                            @csrf

                            <div class="form-step active" data-step="1">

                                <div class="form-group">
                                    <label
                                        for="consultationName">{{ __('templates/consultation-form.fields.name.label') }}
                                        <span class="required">*</span></label>
                                    <input type="text" id="consultationName" name="name"
                                        placeholder="{{ __('templates/consultation-form.fields.name.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="consultationCity">{{ __('templates/consultation-form.fields.city.label') }}
                                        <span class="required">*</span></label>
                                    <input type="text" id="consultationCity" name="city"
                                        placeholder="{{ __('templates/consultation-form.fields.city.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="consultationPhone">{{ __('templates/consultation-form.fields.phone.label') }}
                                        <span class="required">*</span></label>
                                    <input type="tel" id="consultationPhone" name="phone"
                                        placeholder="{{ __('templates/consultation-form.fields.phone.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="consultationEmail">{{ __('templates/consultation-form.fields.email.label') }}
                                        <span class="required">*</span></label>
                                    <input type="email" id="consultationEmail" name="email"
                                        placeholder="{{ __('templates/consultation-form.fields.email.placeholder') }}"
                                        required>
                                </div>

                            </div>

                            <div class="button-group">
                                <button type="submit" class="button" id="consultationSubmitBtn">
                                    {{ __('templates/consultation-form.buttons.submit') }}
                                </button>
                            </div>
                        </form>

                        <!-- SUCCESS -->
                        <div class="success-message" id="consultationSuccessMessage">
                            <div class="success-icon"></div>
                            <h3>{{ __('templates/consultation-form.messages.success_title') }}</h3>
                            <p>
                                {{ __('templates/consultation-form.messages.success_text') }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .error-message.error {
        color: #d93025;
        margin-bottom: 20px;
    }
</style>


<script src="{{ asset('assets/js/consultation-form.js') }}"></script>
