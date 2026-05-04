@extends('frontoffice.layouts.app')

@section('title', 'Suivi de mes traductions — GLS Sprachenzentrum')

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/track-translation.css') }}">

@section('content')
<main>

    {{-- ── HERO + SEARCH ───────────────────────────── --}}
    <section class="tt-hero">
        <div class="container tt-hero-inner">
            <span class="tt-eyebrow"><span class="dot"></span> Portail étudiant</span>
            <h1>Suivez vos <span class="accent">traductions Maroc&nbsp;–&nbsp;Allemagne</span></h1>
            <p class="lead">Entrez votre numéro de CIN pour voir où en sont vos documents — du dépôt à la remise.</p>

            <div class="tt-search-card mt-4">
                <form method="GET" action="{{ route('front.translations.track') }}">
                    <label class="form-label">Votre numéro de CIN</label>
                    <div class="input-icon mb-2">
                        <input type="text" name="cin" class="form-control text-uppercase"
                               placeholder="AB123456"
                               value="{{ $cin }}"
                               autocomplete="off"
                               autofocus required>
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div class="tt-search-helper">
                        <i class="bi bi-shield-check"></i>
                        Vos données restent confidentielles. Seules vos commandes s'affichent.
                    </div>
                    <button type="submit" class="tt-search-btn mt-3">
                        <i class="bi bi-search me-1"></i> Rechercher mes commandes
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ── RESULTS ─────────────────────────────────── --}}
    @if($searched)
        <section class="tt-results">
            <div class="container">
              <div class="tt-results-inner">

                @if($orders->isEmpty())
                    <div class="tt-empty">
                        <div class="icon"><i class="bi bi-search-heart"></i></div>
                        <h4 class="mb-2">Aucune commande trouvée</h4>
                        <p>Aucune traduction n'est enregistrée pour le CIN <strong>{{ $cin }}</strong>.<br>
                        Vérifiez votre saisie ou contactez l'accueil GLS.</p>
                    </div>
                @else
                    @php $studentName = $orders->first()->student_name; @endphp
                    @php $initials = collect(explode(' ', trim($studentName)))->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->implode(''); @endphp

                    <div class="tt-results-head">
                        <div class="tt-results-count">
                            {{ $orders->count() }} commande{{ $orders->count() > 1 ? 's' : '' }} trouvée{{ $orders->count() > 1 ? 's' : '' }}
                        </div>
                        <span class="tt-student-chip">
                            <span class="avatar">{{ $initials ?: '?' }}</span>
                            <span><span class="text-muted">Étudiant&nbsp;:</span> <strong>{{ $studentName }}</strong></span>
                        </span>
                    </div>

                    @foreach($orders as $o)
                        @php
                            $stepIdx = match($o->status) {
                                'pending'    => 1,
                                'translator' => 2,
                                'delivered'  => 3,
                                default      => 0,
                            };
                            $statusClass = 'tt-' . $o->status;
                        @endphp

                        <div class="tt-order">
                            {{-- Head --}}
                            <div class="tt-order-head">
                                <div class="tt-order-meta">
                                    <div class="tt-order-id">Commande <span class="hash">#{{ $o->id }}</span></div>
                                    <div class="tt-order-sub">
                                        <span><i class="bi bi-files"></i>{{ $o->items->count() }} document{{ $o->items->count() > 1 ? 's' : '' }}</span>
                                        <span class="dot-sep"><i class="bi bi-file-earmark-text"></i>{{ $o->totalPages() }} page{{ $o->totalPages() > 1 ? 's' : '' }}</span>
                                        @if($o->date_received)
                                            <span class="dot-sep"><i class="bi bi-calendar2-event"></i>Déposé le {{ $o->date_received->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <span class="tt-status-pill {{ $statusClass }}">
                                    <span class="dot"></span> {{ $o->statusLabel() }}
                                </span>
                            </div>

                            {{-- Items --}}
                            @if($o->items->isNotEmpty())
                                <div class="tt-items">
                                    @foreach($o->items as $it)
                                        <div class="tt-item">
                                            <div class="tt-item-left">
                                                <span class="tt-item-icon"><i class="bi bi-file-earmark-text"></i></span>
                                                <div>
                                                    <div class="tt-item-name">{{ $it->doc_type }}</div>
                                                    <div class="tt-item-meta">{{ $it->page_count }} page{{ $it->page_count > 1 ? 's' : '' }}</div>
                                                </div>
                                            </div>
                                            <div class="tt-item-price">{{ number_format($it->line_total, 0, ',', ' ') }} DH</div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="tt-total">
                                    <span class="tt-total-label">Total commande</span>
                                    <span class="tt-total-val">{{ number_format($o->total_cost, 0, ',', ' ') }} DH</span>
                                </div>
                            @endif

                            {{-- Timeline --}}
                            <div class="tt-timeline">
                                <div class="tt-step {{ $stepIdx >= 1 ? ($stepIdx === 1 ? 'current' : 'done') : '' }}">
                                    <span class="tt-step-circle">
                                        @if($stepIdx > 1) <i class="bi bi-check-lg"></i>
                                        @else <i class="bi bi-inbox"></i>
                                        @endif
                                    </span>
                                    <span class="tt-step-label">Reçu (GLS)</span>
                                </div>
                                <div class="tt-step-line {{ $stepIdx >= 2 ? 'done' : '' }}"></div>

                                <div class="tt-step {{ $stepIdx >= 2 ? ($stepIdx === 2 ? 'current' : 'done') : '' }}">
                                    <span class="tt-step-circle">
                                        @if($stepIdx > 2) <i class="bi bi-check-lg"></i>
                                        @else <i class="bi bi-translate"></i>
                                        @endif
                                    </span>
                                    <span class="tt-step-label">Chez Traducteur</span>
                                </div>
                                <div class="tt-step-line {{ $stepIdx >= 3 ? 'done' : '' }}"></div>

                                <div class="tt-step {{ $stepIdx >= 3 ? 'done' : '' }}">
                                    <span class="tt-step-circle">
                                        @if($stepIdx >= 3) <i class="bi bi-check-lg"></i>
                                        @else <i class="bi bi-bag-check"></i>
                                        @endif
                                    </span>
                                    <span class="tt-step-label">Rendu</span>
                                </div>
                            </div>

                            @if($o->status === 'delivered' && $o->date_handed_over)
                                <div class="tt-delivered-banner">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Documents remis à l'étudiant le {{ $o->date_handed_over->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif

              </div>
            </div>
        </section>
    @endif

</main>
@endsection
