@extends('frontoffice.layouts.app')

@section('title', 'Demande d\'attestation de participation | GLS Sprachenzentrum')

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}">

@section('content')
<main>

    {{-- ── HERO ─────────────────────────────────────── --}}
    <section class="att-hero">
        <div class="container att-hero-inner">
            <span class="att-eyebrow"><span class="dot"></span> Démarche en ligne</span>
            <h1>Demandez votre <span class="accent">attestation de participation</span></h1>
            <p class="lead">
                Remplissez le formulaire ci-dessous. Notre équipe valide votre demande et vous renvoie l'attestation par email.
            </p>

            <div class="att-steps">
                <div class="att-step-chip"><span class="num">1</span> Remplir le formulaire</div>
                <div class="att-step-chip muted"><span class="num">2</span> Validation par GLS</div>
                <div class="att-step-chip muted"><span class="num">3</span> Réception par email</div>
            </div>
        </div>
    </section>

    {{-- ── FORM ─────────────────────────────────────── --}}
    <section class="att-page">
        <div class="container">
            <div class="att-card">

                @if ($errors->any())
                    <div class="att-error">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> Veuillez corriger :</strong>
                        <ul>
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.store')) }}" method="POST" novalidate>
                    @csrf

                    {{-- IDENTITÉ --}}
                    <h3 class="att-section-title"><i class="bi bi-person-vcard"></i> Vos informations personnelles</h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="vous@exemple.com"
                                   value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="phone" class="form-control"
                                   placeholder="06 …"
                                   value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="birth_date" class="form-control"
                                   value="{{ old('birth_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lieu de naissance</label>
                            <input type="text" name="birth_place" class="form-control"
                                   value="{{ old('birth_place') }}">
                        </div>
                    </div>

                    <hr class="att-divider">

                    {{-- COURS --}}
                    <h3 class="att-section-title"><i class="bi bi-mortarboard"></i> Votre cours chez GLS</h3>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Groupe / nom du professeur <span class="text-danger">*</span></label>
                            <input type="text" name="group_name" class="form-control" required
                                   placeholder="Ex : Groupe du soir A2 — Centre Salé / Mme Sara"
                                   value="{{ old('group_name') }}">
                            <small class="help"><i class="bi bi-info-circle"></i> Notre équipe le retrouvera dans le système.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Niveau atteint <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                @foreach (['A1', 'A2', 'B1', 'B2'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Langue de l'attestation</label>
                            <input type="text" class="form-control" value="Bilingue (Allemand / Français)" readonly>
                            <input type="hidden" name="language" value="de_fr">
                            <small class="help"><i class="bi bi-globe2"></i> Document délivré en version bilingue.</small>
                        </div>
                    </div>

                    <div class="att-actions">
                        <span class="att-secure-note">
                            <i class="bi bi-shield-lock-fill"></i> Vos données restent confidentielles.
                        </span>
                        <button type="submit" class="att-submit">
                            <i class="bi bi-send-fill"></i> Envoyer ma demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>
@endsection
