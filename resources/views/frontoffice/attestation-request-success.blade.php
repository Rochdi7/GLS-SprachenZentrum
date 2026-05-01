@extends('frontoffice.layouts.app')

@section('title', 'Demande envoyée | GLS Sprachenzentrum')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .att-success-page { background:#fafafa; padding: 80px 0 100px; }
        .att-success-card {
            background:#fff; border-radius:16px;
            box-shadow:0 8px 24px rgba(0,0,0,.06);
            padding: 60px 40px; max-width: 620px; margin: 0 auto;
            text-align: center;
        }
        .att-success-card .icon {
            width: 78px; height: 78px;
            border-radius: 50%;
            background: #eaf7ec;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #1f7a3a;
            font-size: 2.4rem;
            margin-bottom: 1.25rem;
        }
        .att-success-card h1 { font-size: 1.7rem; font-weight: 800; margin-bottom: .5rem; }
        .att-success-card p { color: rgba(0,0,0,.7); margin-bottom: 1.5rem; }
        .att-success-card a.home {
            display: inline-block;
            background:#1a1a1a; color:#fff; text-decoration:none;
            padding:.75rem 1.5rem; border-radius:10px; font-weight:600;
        }
    </style>
@endpush

@section('content')
<main class="att-success-page">
    <div class="container">
        <div class="att-success-card">
            <div class="icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1>Demande bien reçue</h1>
            <p>
                Merci, votre demande d'attestation a été transmise à notre équipe.
                Vous recevrez un email dès qu'elle aura été examinée.
            </p>
            <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="home">Retour à l'accueil</a>
        </div>
    </div>
</main>
@endsection
