@extends('layouts.main')

@section('title', 'QR Code — Avis étudiants')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-link', route('backoffice.feedbacks.index'))
@section('breadcrumb-item-active', 'QR code à partager')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .qr-wrap{display:flex;justify-content:center;padding:24px;background:#fff;border-radius:12px;border:1px solid #e9ecef}
        .qr-wrap svg{width:320px;height:320px;max-width:100%}
        .qr-url{font-family:monospace;font-size:.9rem;word-break:break-all;background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:10px 12px}
        @media print{
            .no-print{display:none !important}
            .card{border:none !important;box-shadow:none !important}
            body{background:#fff !important}
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center no-print">
                    <h5 class="mb-0"><i class="ti ti-qrcode me-1"></i> QR Code — Formulaire d'avis étudiants</h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-sm btn-primary">
                            <i class="ti ti-printer me-1"></i> Imprimer
                        </button>
                        <a href="{{ route('backoffice.feedbacks.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body text-center">
                    <h4 class="fw-bold mb-1">GLS Sprachenzentrum</h4>
                    <p class="text-muted mb-4">Scannez ce QR code pour partager votre avis</p>

                    <div class="qr-wrap mb-4">
                        {!! $qrSvg !!}
                    </div>

                    <p class="mb-2"><strong>Lien direct :</strong></p>
                    <div class="qr-url mb-4">{{ $url }}</div>

                    <div class="no-print">
                        <small class="text-muted">
                            Imprimez cette page et affichez-la dans vos centres. Les étudiants pourront scanner le code avec leur téléphone pour accéder au formulaire d'avis.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
