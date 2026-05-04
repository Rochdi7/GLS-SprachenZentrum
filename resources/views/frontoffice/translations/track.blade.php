@extends('frontoffice.layouts.app')

@section('title', 'Suivi de mes traductions — GLS')

@section('content')
<style>
    body { background-color: var(--light--off-white) !important; }
    .tr-pill { padding:.4rem .9rem; border-radius:999px; font-size:.8rem; display:inline-block; }
    .tr-pending    { background:#f1f3f5; color:#495057; border:1px solid #dee2e6; }
    .tr-translator { background:#fff4d6; color:#a06700; border:1px solid #ffe69c; }
    .tr-delivered  { background:#cfe2ff; color:#0a58ca; border:1px solid #9ec5fe; font-weight:600; }
    .tr-card { border:1px solid #e9ecef; border-radius:.75rem; }
    .tr-step { display:flex; align-items:center; gap:.5rem; flex:1; font-size:.78rem; color:#6c757d; }
    .tr-step .dot { width:10px; height:10px; border-radius:50%; background:#dee2e6; flex:none; }
    .tr-step.done .dot { background:#0a58ca; }
    .tr-step.done { color:#0a58ca; font-weight:600; }
    .tr-line { height:2px; background:#dee2e6; flex:1; }
    .tr-line.done { background:#0a58ca; }
</style>

<div class="container py-5">

    <div class="text-center mb-4">
        <h1 class="fw-bold">Suivi de mes traductions</h1>
        <p class="text-muted">Entrez votre CIN pour suivre l'état de vos traductions Maroc–Allemagne.</p>
    </div>

    {{-- FORM --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <form method="GET" action="{{ route('front.translations.track') }}" class="card p-4 shadow-sm">
                <div class="mb-3">
                    <label class="form-label">CIN</label>
                    <input
                        type="text"
                        name="cin"
                        value="{{ $cin }}"
                        class="form-control text-uppercase"
                        placeholder="Ex : AB123456"
                        required
                        autofocus
                    >
                    <small class="text-muted">Le numéro de votre carte d'identité nationale.</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Rechercher mes commandes
                </button>
            </form>
        </div>
    </div>

    {{-- RESULTS --}}
    @if($searched)
        @if($orders->isEmpty())
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-warning text-center">
                        Aucune commande de traduction n'a été trouvée pour le CIN <strong>{{ $cin }}</strong>.
                        <br><small>Vérifiez la saisie ou contactez l'accueil GLS.</small>
                    </div>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">{{ $orders->count() }} commande(s) trouvée(s)</h5>
                        <span class="text-muted small">Étudiant : <strong>{{ $orders->first()->student_name }}</strong></span>
                    </div>

                    @foreach($orders as $o)
                        @php
                            $stepIdx = match($o->status) {
                                'pending' => 1,
                                'translator' => 2,
                                'delivered' => 3,
                                default => 0,
                            };
                        @endphp
                        <div class="tr-card p-3 mb-3 bg-white">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">{{ $o->doc_type ?: 'Documents divers' }}</div>
                                    <small class="text-muted">
                                        {{ $o->page_count }} page(s)
                                        @if($o->date_received)
                                            · Déposé le {{ $o->date_received->format('d/m/Y') }}
                                        @endif
                                    </small>
                                </div>
                                <span class="tr-pill tr-{{ $o->status }}">{{ $o->statusLabel() }}</span>
                            </div>

                            {{-- Progress steps --}}
                            <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                                <div class="tr-step {{ $stepIdx >= 1 ? 'done' : '' }}">
                                    <span class="dot"></span> Reçu (GLS)
                                </div>
                                <div class="tr-line {{ $stepIdx >= 2 ? 'done' : '' }}"></div>
                                <div class="tr-step {{ $stepIdx >= 2 ? 'done' : '' }}">
                                    <span class="dot"></span> Chez Traducteur
                                </div>
                                <div class="tr-line {{ $stepIdx >= 3 ? 'done' : '' }}"></div>
                                <div class="tr-step {{ $stepIdx >= 3 ? 'done' : '' }}">
                                    <span class="dot"></span> Rendu
                                </div>
                            </div>

                            @if($o->status === 'delivered' && $o->date_handed_over)
                                <small class="text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Remis le {{ $o->date_handed_over->format('d/m/Y') }}
                                </small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

</div>
@endsection
