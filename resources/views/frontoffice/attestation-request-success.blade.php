@extends('frontoffice.layouts.app')

@section('title', 'Demande envoyée | GLS Sprachenzentrum')

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}">

@section('content')
<main class="att-success-page">
    <div class="container">
        <div class="att-success-card">
            <div class="icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1>Demande bien reçue&nbsp;!</h1>
            <p>
                Merci, votre demande d'attestation a été transmise à notre équipe.<br>
                Vous recevrez un email dès qu'elle aura été examinée.
            </p>
            <div class="att-success-actions">
                <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="att-btn-primary">
                    <i class="bi bi-house-door"></i> Retour à l'accueil
                </a>
                <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="att-btn-ghost">
                    <i class="bi bi-chat-dots"></i> Contacter GLS
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
