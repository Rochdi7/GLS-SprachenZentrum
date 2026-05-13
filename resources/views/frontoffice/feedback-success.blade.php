@extends('frontoffice.layouts.app')

@section('title', 'Merci pour votre avis — GLS')

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}">

@section('content')
<main class="att-success-page">
    <div class="container">
        <div class="att-success-card">
            <div class="icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1>Merci pour votre retour !</h1>
            <p>
                Votre message a bien été transmis à l'équipe GLS Sprachenzentrum.<br>
                Chaque avis compte pour améliorer la qualité de nos services.
            </p>
            <div class="att-success-actions">
                <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="att-btn-primary">
                    <i class="bi bi-house-door"></i> Retour à l'accueil
                </a>
                <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="att-btn-ghost">
                    <i class="bi bi-chat-dots"></i> Nous contacter
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
