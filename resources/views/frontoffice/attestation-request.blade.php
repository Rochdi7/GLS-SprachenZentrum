@extends('frontoffice.layouts.app')

@section('title', 'Demande d\'attestation de participation | GLS Sprachenzentrum')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .att-request-page { background: #fafafa; padding: 60px 0 80px; }
        .att-request-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            padding: 40px 36px;
            max-width: 820px;
            margin: 0 auto;
        }
        .att-request-card h1 { font-size: 1.85rem; font-weight: 800; margin-bottom: .35rem; }
        .att-request-card .lead { color: rgba(0,0,0,.65); font-size: .98rem; margin-bottom: 1.75rem; }
        .att-request-card .form-label { font-weight: 600; font-size: .92rem; }
        .att-request-card .form-control,
        .att-request-card .form-select {
            border-radius: 10px;
            border: 1px solid #e2e2e2;
            padding: .65rem .9rem;
        }
        .att-request-card .form-control:focus,
        .att-request-card .form-select:focus {
            border-color: #c9a341;
            box-shadow: 0 0 0 .15rem rgba(201,163,65,.18);
        }
        .att-request-card small.help { color: rgba(0,0,0,.55); }
        .att-request-card .btn-submit {
            background: #1a1a1a;
            color: #fff;
            border: 0;
            padding: .85rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .att-request-card .btn-submit:hover { background: #000; }
        .att-request-card .section-divider {
            border-top: 1px solid #ececec;
            margin: 1.5rem 0;
        }
    </style>
@endpush

@section('content')
<main class="att-request-page">
    <div class="container">
        <div class="att-request-card">
            <h1>Demande d'attestation de participation</h1>
            <p class="lead">
                Remplissez ce formulaire pour demander votre attestation. Notre équipe la validera puis vous recontactera.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.store')) }}" method="POST" novalidate>
                @csrf

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
                               value="{{ old('email') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="phone" class="form-control"
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

                <div class="section-divider"></div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Groupe (nom de votre groupe) <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control" required
                               placeholder="Ex : Groupe du soir A2 — Centre Salé / nom de votre professeur"
                               value="{{ old('group_name') }}">
                        <small class="help">Indiquez le nom de votre groupe ou de votre professeur. Notre équipe le retrouvera dans le système.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Niveau <span class="text-danger">*</span></label>
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
                        <small class="help">L'attestation est délivrée en version bilingue allemand-français.</small>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-send me-1"></i> Envoyer ma demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
