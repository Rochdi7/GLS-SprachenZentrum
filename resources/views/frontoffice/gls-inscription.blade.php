{{-- resources/views/frontoffice/gls-inscription.blade.php --}}
@extends('frontoffice.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/gls-form-page.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('head')
    <meta name="api-centers-url" content="{{ url('/api/centers') }}">
    <meta name="api-groups-url-template" content="{{ url('/api/groups') }}/__SITE__">
    <meta name="api-dates-url-template" content="{{ url('/api/groups/dates') }}/__GROUP__">
    <meta name="gls-store-url" content="{{ LaravelLocalization::localizeUrl(route('gls.inscription')) }}">
@endpush

@section('content')
    @php
        $tr = function (string $key, string $fallback) {
            $val = __($key);
            return $val === $key ? $fallback : $val;
        };
    @endphp

    <main class="gls-inscription-page gls-inscription-scope">
        <section class="gls-inscription-section section py-5">
            <div class="container reveal delay-1">
                <div class="row justify-content-center">
                    <div class="col-lg-9">

                        <div class="form-card reveal delay-2">
                            <div class="decorative-element" aria-hidden="true"></div>

                            <div class="form-content">

                                <div class="form-header">
                                    <h1 class="form-title mb-2">
                                        {{ $tr('templates/gls-form.header.title', 'Inscription GLS') }}
                                    </h1>
                                    <p class="form-subtitle mb-0">
                                        {{ $tr('templates/gls-form.header.subtitle', 'Completez le formulaire pour envoyer votre demande') }}
                                    </p>
                                </div>

                                <div class="error-message" id="glsPageErrorMessage">
                                    <span id="glsPageErrorText"></span>
                                </div>

                                <form id="glsPageForm" data-label-submit="{{ __('templates/gls-form.buttons.submit') }}"
                                    data-label-sending="{{ __('templates/gls-form.buttons.sending') }}"
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
                                    @csrf

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step1', 'Informations') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsPageNom">
                                            {{ $tr('templates/gls-form.fields.nom.label', 'Nom') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsPageNom" name="nom"
                                            placeholder="{{ $tr('templates/gls-form.fields.nom.placeholder', 'Votre nom') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPagePrenom">
                                            {{ $tr('templates/gls-form.fields.prenom.label', 'Prénom') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsPagePrenom" name="prenom"
                                            placeholder="{{ $tr('templates/gls-form.fields.prenom.placeholder', 'Votre prénom') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPageEmail">
                                            {{ $tr('templates/gls-form.fields.email.label', 'Email') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="email" id="glsPageEmail" name="email"
                                            placeholder="{{ $tr('templates/gls-form.fields.email.placeholder', 'email@example.com') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPagePhone">
                                            {{ $tr('templates/gls-form.fields.phone.label', 'Telephone') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="tel" id="glsPagePhone" name="phone"
                                            placeholder="{{ $tr('templates/gls-form.fields.phone.placeholder', '+212 6XX-XXXXXX') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPageAdresse">
                                            {{ $tr('templates/gls-form.fields.adresse.label', 'Adresse') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsPageAdresse" name="adresse"
                                            placeholder="{{ $tr('templates/gls-form.fields.adresse.placeholder', 'Votre adresse complete') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step2', 'Centre GLS') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsPageTypeCours">
                                            {{ $tr('templates/gls-form.fields.type_cours.label', 'Type de cours') }}
                                            <span class="required">*</span>
                                        </label>
                                        <div class="att-select" data-att-select>
                                            <select id="glsPageTypeCours" name="type_cours" class="att-select__native" required>
                                                <option value="">
                                                    {{ $tr('templates/gls-form.fields.type_cours.placeholder', 'Choisissez un type') }}
                                                </option>
                                                <option value="presentiel">
                                                    {{ $tr('templates/gls-form.fields.type_cours_options.presentiel', 'Cours presentiel') }}
                                                </option>
                                                <option value="en_ligne">
                                                    {{ $tr('templates/gls-form.fields.type_cours_options.en_ligne', 'Cours en ligne') }}
                                                </option>
                                            </select>
                                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="att-select__value att-select__value--placeholder">{{ $tr('templates/gls-form.fields.type_cours.placeholder', 'Choisissez un type') }}</span>
                                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </button>
                                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group" id="glsPageCentreWrapper" style="display: none;">
                                        <label for="glsPageCentre">
                                            {{ $tr('templates/gls-form.fields.centre.label', 'Centre GLS prefere') }}
                                            <span class="required">*</span>
                                        </label>
                                        <div class="att-select" data-att-select>
                                            <select id="glsPageCentre" name="centre" class="att-select__native">
                                                <option value="">
                                                    {{ $tr('templates/gls-form.fields.centre.placeholder', 'Selectionner un centre') }}
                                                </option>
                                            </select>
                                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="att-select__value att-select__value--placeholder">{{ $tr('templates/gls-form.fields.centre.placeholder', 'Selectionner un centre') }}</span>
                                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </button>
                                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step3', 'Groupe') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsPageGroupId">
                                            {{ $tr('templates/gls-form.fields.group_id.label', 'Groupe') }}
                                            <span class="required">*</span>
                                        </label>
                                        <div class="att-select" data-att-select>
                                            <select id="glsPageGroupId" name="group_id" class="att-select__native" required>
                                                <option value="">
                                                    {{ $tr('templates/gls-form.fields.group_id.placeholder', 'Selectionner un groupe') }}
                                                </option>
                                                <!-- Groups will be populated dynamically based on selected center or type -->
                                                <option value="25" id="onlineGroupOption" style="display: none;">Groupe
                                                    Nuit
                                                    20:00 – 22:00</option>
                                            </select>
                                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="att-select__value att-select__value--placeholder">{{ $tr('templates/gls-form.fields.group_id.placeholder', 'Selectionner un groupe') }}</span>
                                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </button>
                                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>


                                    <div class="form-group">
                                        <label for="glsPageNiveau">
                                            {{ $tr('templates/gls-form.fields.niveau.label', "Niveau d'Allemand") }}
                                            <span class="required">*</span>
                                        </label>
                                        <div class="att-select" data-att-select>
                                            <select id="glsPageNiveau" name="niveau" class="att-select__native" required>
                                                <option value="">
                                                    {{ $tr('templates/gls-form.fields.niveau.placeholder', 'Selectionner un niveau') }}
                                                </option>
                                                <option value="A0">A0</option>
                                                <option value="A1">A1</option>
                                                <option value="A2">A2</option>
                                                <option value="B1">B1</option>
                                                <option value="B2">B2</option>
                                            </select>
                                            <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="att-select__value att-select__value--placeholder">{{ $tr('templates/gls-form.fields.niveau.placeholder', 'Selectionner un niveau') }}</span>
                                                <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </button>
                                            <ul class="att-select__menu" role="listbox" tabindex="-1" hidden></ul>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step4', 'Preferences') }}
                                    </h4>

                                    {{-- Hidden for now - static groups don't need preferences --}}
                                    {{--
                                    <div class="form-group">
                                        <label for="glsPageHorairePrefere">
                                            {{ $tr('templates/gls-form.fields.horaire_prefere.label', 'Horaire de cours') }}
                                        </label>
                                        <input type="text" id="glsPageHorairePrefere" name="horaire_prefere" readonly
                                            placeholder="{{ $tr('templates/gls-form.fields.horaire_prefere.placeholder', 'Auto rempli') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPageDateStart">
                                            {{ $tr('templates/gls-form.fields.date_start.label', 'A partir de...') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsPageDateStart" name="date_start"
                                            class="date-picker" required
                                            placeholder="{{ $tr('templates/gls-form.fields.date_start.placeholder', 'Selectionner une date') }}">
                                    </div>
                                    --}}

                                    <div class="form-group">
                                        <label class="checkbox-row" for="glsPageAcceptTerms">
                                            <input type="checkbox" id="glsPageAcceptTerms" name="accept_terms"
                                                value="1" required>
                                            <span class="checkbox-text">
                                                {{ $tr('templates/gls-form.fields.accept_terms.label', "J'accepte les") }}
                                                <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}"
                                                    target="_blank" class="link">
                                                    {{ $tr('templates/gls-form.fields.accept_terms.link', 'conditions generales') }}
                                                </a>
                                                <span class="required">*</span>
                                            </span>
                                        </label>
                                        <div class="invalid-feedback d-block"></div>
                                    </div>

                                    <div class="button-group">
                                        <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}"
                                            class="button button-outline">
                                            &larr; {{ $tr('templates/gls-form.buttons.cancel', 'Annuler') }}
                                        </a>

                                        <button type="submit" class="button" id="glsPageSubmitBtn">
                                            {{ $tr('templates/gls-form.buttons.submit', 'Envoyer') }}
                                        </button>
                                    </div>
                                </form>

                                <div class="success-message" id="glsPageSuccessMessage">
                                    <h3 class="mb-2">
                                        {{ $tr('templates/gls-form.messages.success_title', 'Demande envoyee !') }}</h3>
                                    <p class="mb-4">
                                        {{ $tr('templates/gls-form.messages.success_text', 'Merci. Nous vous contacterons tres vite.') }}
                                    </p>
                                    <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="button">
                                        {{ $tr('templates/gls-form.buttons.back_home', "Retour a l'accueil") }}
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/js/gls-form-page.js') }}"></script>
@endpush
