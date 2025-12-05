@extends('frontoffice.layouts.app')

@section('title', 'Vérification de Certificat')

@section('content')

{{-- TOASTS (popups en haut à droite) --}}
@if (session('certificate_error') || session('certificate_success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        @if (session('certificate_error'))
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                         {{ session('certificate_error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('certificate_success'))
            <div class="toast align-items-center text-bg-success border-0 show mt-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        Certificat trouvé avec succès.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
@endif

<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="fw-bold">Vérifier un Certificat GLS</h1>
        <p class="text-muted">Entrez le numéro du certificat pour vérifier son authenticité.</p>
    </div>

    {{-- FORMULAIRE --}}
    <div class="row justify-content-center">
        <div class="col-md-6">

            <form action="{{ route('front.certificate.check.post') }}" method="POST" class="card p-4 shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Numéro du certificat</label>
                    <input type="text" name="certificate_number" class="form-control"
                           placeholder="Ex : K5FDM3VB" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Vérifier
                </button>
            </form>

        </div>
    </div>

    {{-- DÉTAILS DU CERTIFICAT SI TROUVÉ --}}
    @php
        $cert = session('certificate_success');
    @endphp

    @if ($cert)
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Certificat GLS</h4>

                        <p><strong>Nom :</strong> {{ $cert['first_name'] }} {{ $cert['last_name'] }}</p>

                        <p><strong>Niveau :</strong> {{ $cert['level'] }}</p>

                        <p><strong>Date d’examen :</strong>
                            {{ \Carbon\Carbon::parse($cert['exam_date'])->format('d/m/Y') }}
                        </p>

                        <p><strong>Date de délivrance :</strong>
                            {{ \Carbon\Carbon::parse($cert['issued_date'])->format('d/m/Y') }}
                        </p>

                        <p><strong>Numéro du certificat :</strong> {{ $cert['certificate_number'] }}</p>

                        <a href="{{ route('backoffice.certificates.pdf', $cert['id']) }}"
                           target="_blank" class="btn btn-outline-primary mt-3">
                            Télécharger le certificat (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection
