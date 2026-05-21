{{-- resources/views/frontoffice/landing/google.blade.php --}}
@extends('frontoffice.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/lp-google.css') }}">
@endpush

@push('head')
    <meta name="api-centers-url" content="{{ url('/api/centers') }}">
    <meta name="gls-store-url" content="{{ LaravelLocalization::localizeUrl(route('gls.inscription')) }}">
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://player.vimeo.com">
    <link rel="preconnect" href="https://i.vimeocdn.com">
    <link rel="preconnect" href="https://f.vimeocdn.com">
    <link rel="preconnect" href="https://basemaps.cartocdn.com" crossorigin>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
    @php
        $tr = function (string $key, string $fallback) {
            $val = __($key);
            return $val === $key ? $fallback : $val;
        };
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'glssprachenzentrum.ma';

        // Vimeo IDs taken from existing home-page partials
        $featuredVideo = '1173823269'; // GLS Témoignage PLAN C (strong sales pitch)
        $galleryVideos = [
            ['id' => '1172183039', 'title' => 'Yassine Safine', 'tag' => 'Etudes en Allemagne', 'dot' => 'blue'],
            ['id' => '1172183086', 'title' => 'Mohamed Amine', 'tag' => 'Reussite GLS', 'dot' => 'yellow'],
            ['id' => '1172182987', 'title' => 'Oumaima', 'tag' => 'Parcours etudiant', 'dot' => 'green'],
            ['id' => '1172182943', 'title' => 'Wiam', 'tag' => 'Reussite GLS', 'dot' => 'purple'],
        ];
    @endphp

    <main class="lp-google-scope">
        <div class="lp-container">

            {{-- =================== Search-result style ribbon =================== --}}
            {{-- <div class="lp-ribbon">
                <span class="lp-ribbon-ad">Ad</span>
                <span class="lp-ribbon-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <b>{{ $host }}</b> &middot; {{ $tr('lp.google.ribbon', 'Cours d\'Allemand - Inscription Gratuite') }}
                </span>
            </div> --}}

            {{-- =================== HERO: text left / video right =================== --}}
            <div class="lp-grid">

                {{-- LEFT HERO --}}
                <div class="lp-hero">
                    <div class="lp-source-badge" data-reveal="left">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M12 11v2h5.51c-.2 1.27-1.52 3.72-5.51 3.72-3.31 0-6.01-2.74-6.01-6.12s2.7-6.12 6.01-6.12c1.88 0 3.14.8 3.86 1.49l2.63-2.53C16.91 1.99 14.74 1 12 1 5.92 1 1 5.92 1 12s4.92 11 11 11c6.35 0 10.55-4.46 10.55-10.74 0-.72-.08-1.27-.18-1.82H12z" />
                        </svg>
                        {{ $tr('lp.google.badge', 'Offre Speciale Google Ads') }}
                    </div>

                    <h1 class="lp-title" data-reveal style="--reveal-delay:.05s">
                        {{ $tr('lp.google.title.l1', 'Maitrisez') }}
                        <span class="accent">{{ $tr('lp.google.title.l2', "l'Allemand") }}</span><br>
                        {{ $tr('lp.google.title.l3', 'en un temps record') }}
                    </h1>

                    <p class="lp-subtitle" data-reveal style="--reveal-delay:.15s">
                        {{ $tr('lp.google.subtitle', "Methode certifiee, profs natifs, 7 villes au Maroc. Plus de 5000 etudiants nous font confiance. Inscrivez-vous gratuitement aujourd'hui.") }}
                    </p>

                    <div class="lp-features">
                        <div class="lp-feature" data-reveal style="--reveal-delay:.20s">
                            <div class="lp-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M16 12l-4-4-4 4M12 16V8" />
                                </svg>
                            </div>
                            <div class="lp-feature-text">
                                <strong>{{ $tr('lp.google.f1.title', 'De A0 a B2 en quelques mois') }}</strong>
                                <span>{{ $tr('lp.google.f1.text', 'Progression rapide grace a notre methode intensive prouvee.') }}</span>
                            </div>
                        </div>
                        <div class="lp-feature" data-reveal style="--reveal-delay:.28s">
                            <div class="lp-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </div>
                            <div class="lp-feature-text">
                                <strong>{{ $tr('lp.google.f2.title', 'Certificats GLS, Goethe & OSD') }}</strong>
                                <span>{{ $tr('lp.google.f2.text', 'Reconnus par toutes les universites allemandes.') }}</span>
                            </div>
                        </div>
                        <div class="lp-feature" data-reveal style="--reveal-delay:.36s">
                            <div class="lp-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <div class="lp-feature-text">
                                <strong>{{ $tr('lp.google.f3.title', 'Petits groupes, profs natifs') }}</strong>
                                <span>{{ $tr('lp.google.f3.text', '15 - 25 etudiants max par classe pour un suivi personnalise.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lp-proof" data-reveal="fade" style="--reveal-delay:.45s">
                        <div class="lp-stars" aria-label="5 etoiles">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <div class="lp-proof-text">
                            <b>4.9/5</b> &middot;
                            {{ $tr('lp.google.proof', '+5000 etudiants formes, 98% taux de reussite') }}
                        </div>
                    </div>
                </div>

                {{-- RIGHT: form card (first thing ad visitors should see) --}}
                <div class="lp-card lp-card--hero" data-reveal="right" style="--reveal-delay:.10s">
                    <div class="lp-card-top">
                        <h2>{{ $tr('lp.google.form.title', 'Reservez votre place gratuitement') }}</h2>
                        <p>{{ $tr('lp.google.form.subtitle', 'On vous rappelle sous 24h.') }}</p>
                    </div>

                    <div class="lp-form">
                        <div class="lp-error" id="lpErrorMessage">
                            <span id="lpErrorText"></span>
                        </div>

                        <form id="lpForm" data-form-source="google_ads"
                            data-label-submit="{{ __('templates/gls-form.buttons.submit') }}"
                            data-label-sending="{{ __('templates/gls-form.buttons.sending') }}"
                            data-error-required="{{ __('templates/gls-form.errors.required_fields') }}"
                            data-error-duplicate="{{ __('templates/gls-form.errors.duplicate') }}"
                            data-error-connection="{{ __('templates/gls-form.errors.connection_error') }}"
                            data-error-generic="{{ __('templates/gls-form.errors.generic') }}"
                            data-error-server="{{ __('templates/gls-form.errors.server_error') }}"
                            data-js-loading="{{ __('templates/gls-form.js.loading') }}"
                            data-js-error-loading="{{ __('templates/gls-form.js.error_loading') }}"
                            data-js-select-level="{{ __('templates/gls-form.js.select_level') }}"
                            data-js-select-center="{{ __('templates/gls-form.js.select_center') }}"
                            data-js-select-group="{{ __('templates/gls-form.js.select_group') }}"
                            data-js-group-label="{{ __('templates/gls-form.js.group_label') }}"
                            data-js-group-night="{{ __('templates/gls-form.js.group_night_label') }}">
                            @csrf

                            <input type="hidden" id="lpHorairePrefere" name="horaire_prefere" value="">

                            <div class="lp-section">
                                <h4 class="lp-section-title">
                                    {{ $tr('templates/gls-form.progress.steps.step1', 'Vos informations') }}</h4>

                                <div class="lp-field-grid">
                                    <div class="lp-field">
                                        <label for="lpNom">
                                            {{ $tr('templates/gls-form.fields.nom.label', 'Nom') }} <span
                                                class="required">*</span>
                                        </label>
                                        <input type="text" id="lpNom" name="nom"
                                            placeholder="{{ $tr('templates/gls-form.fields.nom.placeholder', 'Votre nom') }}"
                                            required>
                                    </div>
                                    <div class="lp-field">
                                        <label for="lpPrenom">
                                            {{ $tr('templates/gls-form.fields.prenom.label', 'Prenom') }} <span
                                                class="required">*</span>
                                        </label>
                                        <input type="text" id="lpPrenom" name="prenom"
                                            placeholder="{{ $tr('templates/gls-form.fields.prenom.placeholder', 'Votre prenom') }}"
                                            required>
                                    </div>
                                </div>

                                <div class="lp-field">
                                    <label for="lpEmail">
                                        {{ $tr('templates/gls-form.fields.email.label', 'Email') }} <span
                                            class="required">*</span>
                                    </label>
                                    <input type="email" id="lpEmail" name="email"
                                        placeholder="{{ $tr('templates/gls-form.fields.email.placeholder', 'email@example.com') }}"
                                        required>
                                </div>

                                <div class="lp-field-grid">
                                    <div class="lp-field">
                                        <label for="lpPhone">
                                            {{ $tr('templates/gls-form.fields.phone.label', 'Telephone') }} <span
                                                class="required">*</span>
                                        </label>
                                        <input type="tel" id="lpPhone" name="phone"
                                            placeholder="{{ $tr('templates/gls-form.fields.phone.placeholder', '+212 6XX-XXXXXX') }}"
                                            required>
                                    </div>
                                    <div class="lp-field">
                                        <label for="lpAdresse">
                                            {{ $tr('templates/gls-form.fields.adresse.label', 'Adresse') }} <span
                                                class="required">*</span>
                                        </label>
                                        <input type="text" id="lpAdresse" name="adresse"
                                            placeholder="{{ $tr('templates/gls-form.fields.adresse.placeholder', 'Votre adresse') }}"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="lp-section">
                                <h4 class="lp-section-title">
                                    {{ $tr('templates/gls-form.progress.steps.step2', 'Centre GLS') }}</h4>

                                <div class="lp-field">
                                    <label for="lpTypeCours">
                                        {{ $tr('templates/gls-form.fields.type_cours.label', 'Type de cours') }} <span
                                            class="required">*</span>
                                    </label>
                                    <select id="lpTypeCours" name="type_cours" required>
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
                                </div>

                                <div class="lp-field" id="lpCentreWrapper" style="display: none;">
                                    <label for="lpCentre">
                                        {{ $tr('templates/gls-form.fields.centre.label', 'Centre prefere') }} <span
                                            class="required">*</span>
                                    </label>
                                    <select id="lpCentre" name="centre">
                                        <option value="">
                                            {{ $tr('templates/gls-form.fields.centre.placeholder', 'Selectionner un centre') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="lp-section">
                                <h4 class="lp-section-title">
                                    {{ $tr('templates/gls-form.progress.steps.step3', 'Groupe & Niveau') }}</h4>

                                <div class="lp-field-grid">
                                    <div class="lp-field">
                                        <label for="lpGroupId">
                                            {{ $tr('templates/gls-form.fields.group_id.label', 'Groupe') }} <span
                                                class="required">*</span>
                                        </label>
                                        <select id="lpGroupId" name="group_id" required>
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.group_id.placeholder', 'Selectionner un groupe') }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="lp-field">
                                        <label for="lpNiveau">
                                            {{ $tr('templates/gls-form.fields.niveau.label', "Niveau d'Allemand") }} <span
                                                class="required">*</span>
                                        </label>
                                        <select id="lpNiveau" name="niveau" required>
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.niveau.placeholder', 'Selectionner un niveau') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <label class="lp-checkbox" for="lpAcceptTerms">
                                    <input type="checkbox" id="lpAcceptTerms" name="accept_terms" value="1" checked
                                        required>
                                    <span>
                                        {{ $tr('templates/gls-form.fields.accept_terms.label', "J'accepte les") }}
                                        <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}"
                                            target="_blank">
                                            {{ $tr('templates/gls-form.fields.accept_terms.link', 'conditions generales') }}
                                        </a>
                                        <span class="required">*</span>
                                    </span>
                                </label>
                            </div>

                            <button type="submit" class="lp-submit" id="lpSubmitBtn">
                                {{ $tr('lp.google.cta', 'Reserver ma place') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </button>

                            <div class="lp-trust">
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2"
                                            ry="2" />
                                        <path d="M7 11V7a5 5 0 0110 0v4" />
                                    </svg>
                                    {{ $tr('lp.trust.secure', 'Donnees securisees') }}
                                </span>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $tr('lp.trust.fast', 'Reponse sous 24h') }}
                                </span>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    {{ $tr('lp.trust.no_engagement', 'Sans engagement') }}
                                </span>
                            </div>
                        </form>

                        <div class="lp-success" id="lpSuccessMessage">
                            <div class="lp-success-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <h3>{{ $tr('templates/gls-form.messages.success_title', 'Demande envoyee !') }}</h3>
                            <p>{{ $tr('templates/gls-form.messages.success_text', 'Merci. Nous vous contacterons tres vite.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =================== FEATURED VIDEO — temporarily hidden =================== --}}
            {{-- To re-enable, uncomment this block. The Vimeo ID lives in the @php $featuredVideo at the top.
            <section class="lp-video-section">
                <div class="lp-section-head" data-reveal>
                    <span class="lp-section-eyebrow">{{ $tr('lp.google.video.eyebrow', 'Notre histoire') }}</span>
                    <h2 class="lp-section-title-big">{{ $tr('lp.google.video.title', 'Plan A, Plan B... ou Plan C ?') }}</h2>
                    <p class="lp-section-subtitle">{{ $tr('lp.google.video.subtitle', 'Le bon choix pour partir en Allemagne. Decouvrez en 56 secondes pourquoi GLS est le plan qui marche.') }}</p>
                </div>

                <div class="lp-video-card" data-reveal="scale" style="--reveal-delay:.05s">
                    <div class="lp-video-card-top">
                        <div>
                            <h2>{{ $tr('lp.google.video.title', 'Plan A, Plan B... ou Plan C ?') }}</h2>
                            <p>{{ $tr('lp.google.video.subtitle_short', "Le bon choix pour partir en Allemagne") }}</p>
                        </div>
                        <span class="lp-live-dot">56s</span>
                    </div>
                    <div class="lp-video-frame">
                        <iframe
                            src="https://player.vimeo.com/video/{{ $featuredVideo }}?title=0&byline=0&portrait=0&badge=0&autopause=0"
                            allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen loading="lazy"
                            title="GLS Témoignage - Plan C"></iframe>
                    </div>
                    <div class="lp-video-foot">
                        <div class="lp-video-foot-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                        </div>
                        <div class="lp-video-foot-text">
                            <b>{{ $tr('lp.google.video.foot.title', 'GLS, le plan qui marche vraiment') }}</b>
                            {{ $tr('lp.google.video.foot.text', "Pas de detour, pas de hasard - le chemin direct vers vos etudes en Allemagne.") }}
                        </div>
                    </div>
                </div>
            </section>
            --}}

            {{-- =================== HOW IT WORKS =================== --}}
            <section class="lp-how">
                <div class="lp-section-head" data-reveal>
                    <span class="lp-section-eyebrow">{{ $tr('lp.google.how.eyebrow', 'Simple & rapide') }}</span>
                    <h2 class="lp-section-title-big">{{ $tr('lp.google.how.title', 'Comment ca marche ?') }}</h2>
                    <p class="lp-section-subtitle">
                        {{ $tr('lp.google.how.subtitle', 'Trois etapes pour commencer votre apprentissage de l\'allemand.') }}
                    </p>
                </div>

                <div class="lp-steps">
                    <div class="lp-step" data-reveal style="--reveal-delay:.05s">
                        <span class="lp-step-num-big">01</span>
                        <div class="lp-step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="9" y1="15" x2="15" y2="15" />
                            </svg>
                        </div>
                        <h3>{{ $tr('lp.google.step1.title', 'Reservez votre place') }}</h3>
                        <p>{{ $tr('lp.google.step1.text', 'Remplissez le formulaire d\'inscription. Choisissez votre ville, type de cours et horaire en moins d\'une minute.') }}
                        </p>
                    </div>

                    <div class="lp-step" data-reveal style="--reveal-delay:.15s">
                        <span class="lp-step-num-big">02</span>
                        <div class="lp-step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l3 3L22 4" />
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                            </svg>
                        </div>
                        <h3>{{ $tr('lp.google.step2.title', 'Test de niveau gratuit') }}</h3>
                        <p>{{ $tr('lp.google.step2.text', 'Notre equipe vous contacte sous 24h pour evaluer votre niveau et vous orienter vers le bon groupe.') }}
                        </p>
                    </div>

                    <div class="lp-step" data-reveal style="--reveal-delay:.25s">
                        <span class="lp-step-num-big">03</span>
                        <div class="lp-step-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polygon points="10 8 16 12 10 16 10 8" />
                            </svg>
                        </div>
                        <h3>{{ $tr('lp.google.step3.title', 'Commencez vos cours') }}</h3>
                        <p>{{ $tr('lp.google.step3.text', 'Rejoignez votre groupe en presentiel ou en ligne. Progressez avec une methode certifiee et des profs passionnes.') }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- =================== VIDEO GALLERY =================== --}}
            <section class="lp-gallery">
                <div class="lp-section-head" data-reveal>
                    <span class="lp-section-eyebrow">{{ $tr('lp.google.gallery.eyebrow', 'Temoignages videos') }}</span>
                    <h2 class="lp-section-title-big">{{ $tr('lp.google.gallery.title', 'Ils ont reussi avec GLS') }}</h2>
                    <p class="lp-section-subtitle">
                        {{ $tr('lp.google.gallery.subtitle', 'Decouvrez les parcours d\'etudiants qui ont realise leur reve d\'aller en Allemagne.') }}
                    </p>
                </div>

                <div class="lp-gallery-scroller">
                    <div class="lp-gallery-grid">
                        @foreach ($galleryVideos as $v)
                            <div class="lp-gallery-item" data-reveal
                                style="--reveal-delay:{{ 0.05 + $loop->index * 0.1 }}s">
                                <div class="lp-gallery-video">
                                    <iframe
                                        src="https://player.vimeo.com/video/{{ $v['id'] }}?title=0&byline=0&portrait=0&badge=0&autopause=0"
                                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                                        title="{{ $v['title'] }}"></iframe>
                                </div>
                                <div class="lp-gallery-meta">
                                    <span class="lp-gallery-meta-dot {{ $v['dot'] }}"></span>
                                    <b>{{ $v['title'] }}</b>
                                    <span>&middot; {{ $v['tag'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- =================== OUR GLS CENTRES (sites grid) =================== --}}
            <section class="lp-sites">
                <div class="lp-section-head" data-reveal>
                    <span class="lp-section-eyebrow">{{ $tr('lp.google.sites.eyebrow', 'Nos centres') }}</span>
                    <h2 class="lp-section-title-big">{{ __('gls.sites.title') }}</h2>
                    <p class="lp-section-subtitle">{{ __('gls.sites.subtitle') }}</p>
                </div>

                <div class="lp-sites-grid">
                    <a href="{{ route('front.sites.show', 'gls-rabat') }}" class="lp-site-card small" data-reveal
                        style="--reveal-delay:.05s">
                        <img src="{{ asset('assets/images/sites/rabat.jpg') }}" alt="GLS Rabat" class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.rabat') }}</h3>
                        </div>
                    </a>

                    <a href="{{ route('front.sites.show', 'gls-kenitra') }}" class="lp-site-card small" data-reveal
                        style="--reveal-delay:.12s">
                        <img src="{{ asset('assets/images/sites/kenitra.jpg') }}" alt="GLS Kénitra"
                            class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.kenitra') }}</h3>
                        </div>
                    </a>

                    <a href="{{ route('front.sites.show', 'gls-marrakech') }}" class="lp-site-card wide" data-reveal
                        style="--reveal-delay:.18s">
                        <img src="{{ asset('assets/images/sites/marrakech.webp') }}" alt="GLS Marrakech"
                            class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.marrakech') }}</h3>
                        </div>
                    </a>

                    <a href="{{ route('front.sites.show', 'gls-sale') }}" class="lp-site-card wide" data-reveal
                        style="--reveal-delay:.05s">
                        <img src="{{ asset('assets/images/sites/sale.webp') }}" alt="GLS Salé" class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.sale') }}</h3>
                        </div>
                    </a>

                    <a href="{{ route('front.sites.show', 'gls-agadir') }}" class="lp-site-card small" data-reveal
                        style="--reveal-delay:.12s">
                        <img src="{{ asset('assets/images/sites/agadir.avif') }}" alt="GLS Agadir"
                            class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.agadir') }}</h3>
                        </div>
                    </a>

                    <a href="{{ route('front.sites.show', 'gls-casablanca') }}" class="lp-site-card small" data-reveal
                        style="--reveal-delay:.18s">
                        <img src="{{ asset('assets/images/sites/casablanca.jpg') }}" alt="GLS Casablanca"
                            class="lp-site-image">
                        <div class="lp-site-overlay">
                            <h3>{{ __('gls.sites.casablanca') }}</h3>
                        </div>
                    </a>
                </div>
            </section>

            {{-- =================== CONTACT + INTERACTIVE MAP =================== --}}
            <section class="lp-contact {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
                <div class="lp-contact-grid">

                    {{-- LEFT: contact info card --}}
                    <div class="lp-contact-card" data-reveal="left" style="--reveal-delay:.05s">
                        <h2 class="lp-contact-title">
                            {!! __('home.contact.title') !!}
                        </h2>

                        <div class="lp-contact-links">
                            <a href="tel:+212669515019" class="lp-contact-link">
                                <span class="lp-contact-label">{!! __('home.contact.call_label') !!}</span>
                                <span class="lp-contact-value">+212 6 69 51 50 19</span>
                            </a>

                            <a href="mailto:info@gls-sprachzentrum.ma" class="lp-contact-link">
                                <span class="lp-contact-label">{!! __('home.contact.email_label') !!}</span>
                                <span class="lp-contact-value">info@gls-sprachzentrum.ma</span>
                            </a>
                        </div>

                        <div class="lp-contact-visit">
                            <span class="lp-contact-label">{!! __('home.contact.visit_label') !!}</span>
                            @include('frontoffice.partials.gls-centers-links', ['linkClass' => 'lp-address-link'])
                        </div>

                        <div class="lp-contact-social">
                            <span class="lp-contact-label">{!! __('home.contact.follow_label') !!}</span>
                            <div class="lp-contact-social-row">
                                <a href="https://www.instagram.com/gls.sprachenzentrum/" class="lp-social-link ig"
                                    target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <a href="https://www.facebook.com/gls.sale/" class="lp-social-link fb" target="_blank"
                                    rel="noopener noreferrer" aria-label="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://www.youtube.com/@9onsolsTalks" class="lp-social-link yt" target="_blank"
                                    rel="noopener noreferrer" aria-label="YouTube">
                                    <i class="bi bi-youtube"></i>
                                </a>
                                <a href="https://api.whatsapp.com/send/?phone=0669515019&text&type=phone_number&app_absent=0"
                                    class="lp-social-link wa" target="_blank" rel="noopener noreferrer"
                                    aria-label="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="https://www.tiktok.com/@gls.sprachenzentrum?is_from_webapp=1&sender_device=pc"
                                    class="lp-social-link tt" target="_blank" rel="noopener noreferrer"
                                    aria-label="TikTok">
                                    <i class="bi bi-tiktok"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: INTERACTIVE MOROCCO MAP — 6 GLS centres --}}
                    <div class="lp-contact-map" data-reveal="right" style="--reveal-delay:.15s">
                        <div id="glsCentresMap" aria-label="GLS Sprachenzentrum centres au Maroc"></div>
                        <ul class="lp-contact-map-legend" id="glsMapLegend"></ul>
                    </div>

                    <script>
                        (function initGlsMap() {
                            if (typeof L === 'undefined') {
                                return setTimeout(initGlsMap, 80);
                            }
                            if (!document.getElementById('glsCentresMap')) return;
                            if (document.getElementById('glsCentresMap').dataset.ready === '1') return;
                            document.getElementById('glsCentresMap').dataset.ready = '1';

                            const centres = [{
                                    name: 'GLS Rabat',
                                    slug: 'gls-rabat',
                                    lat: 33.9976668,
                                    lng: -6.8485901,
                                    color: '#1c45db',
                                    gmap: 'https://maps.app.goo.gl/mUnSAVYEnGToS8i2A'
                                },
                                {
                                    name: 'GLS Salé',
                                    slug: 'gls-sale',
                                    lat: 34.0400773,
                                    lng: -6.8172275,
                                    color: '#009d5a',
                                    gmap: 'https://maps.app.goo.gl/pbSW4y4tt9RThx4a7'
                                },
                                {
                                    name: 'GLS Kénitra',
                                    slug: 'gls-kenitra',
                                    lat: 34.2582587,
                                    lng: -6.5876841,
                                    color: '#ff7a08',
                                    gmap: 'https://maps.app.goo.gl/pEsso9L8ygWpdSor5'
                                },
                                {
                                    name: 'GLS Casablanca',
                                    slug: 'gls-casablanca',
                                    lat: 33.5936893,
                                    lng: -7.6210973,
                                    color: '#9767f8',
                                    gmap: 'https://maps.app.goo.gl/EdqBoa3KWEYjuzoq7'
                                },
                                {
                                    name: 'GLS Marrakech',
                                    slug: 'gls-marrakech',
                                    lat: 31.6379228,
                                    lng: -8.009762,
                                    color: '#d22730',
                                    gmap: 'https://maps.app.goo.gl/krR8pGZue3DW3yyv6'
                                },
                                {
                                    name: 'GLS Agadir',
                                    slug: 'gls-agadir',
                                    lat: 30.4017457,
                                    lng: -9.5471754,
                                    color: '#fc0',
                                    gmap: 'https://maps.app.goo.gl/VX48ZDGFyXCyxsGU7'
                                },
                            ];

                            const map = L.map('glsCentresMap', {
                                zoomControl: true,
                                scrollWheelZoom: false,
                                attributionControl: false,
                            }).setView([32.0, -7.5], 6);

                            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                                maxZoom: 18,
                            }).addTo(map);

                            const baseUrl = @json(LaravelLocalization::localizeUrl('/sites/'));
                            const legend = document.getElementById('glsMapLegend');
                            const markers = {};

                            centres.forEach((c, i) => {
                                const html = `
                            <div class="gls-pin-wrap" style="--pin-color:${c.color};">
                                <div class="gls-pin"></div>
                                <div class="gls-pin-label">${c.name.replace('GLS ', '')}</div>
                            </div>`;
                                const icon = L.divIcon({
                                    html,
                                    className: 'gls-pin-icon',
                                    iconSize: [120, 56],
                                    iconAnchor: [12, 28],
                                });
                                const m = L.marker([c.lat, c.lng], {
                                    icon
                                }).addTo(map);
                                m.bindPopup(
                                    `<strong>${c.name}</strong><br><a href="${c.gmap}" target="_blank" rel="noopener" style="color:${c.color};font-weight:600;">Voir sur Google Maps →</a>`
                                );
                                markers[c.slug] = m;

                                const li = document.createElement('li');
                                li.innerHTML =
                                    `<span class="dot" style="background:${c.color}"></span> ${c.name.replace('GLS ', '')}`;
                                li.dataset.slug = c.slug;
                                li.addEventListener('click', () => {
                                    map.flyTo([c.lat, c.lng], 9, {
                                        duration: 0.6
                                    });
                                    m.openPopup();
                                });
                                legend.appendChild(li);
                            });

                            // Force size recompute (in case container measured 0 at init)
                            setTimeout(() => map.invalidateSize(), 200);
                            window.addEventListener('resize', () => map.invalidateSize());
                        })();
                    </script>

                </div>
            </section>

        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/gls-lp-form.js') }}"></script>
    <script src="{{ asset('assets/js/gls-lp-reveal.js') }}" defer></script>
@endpush
