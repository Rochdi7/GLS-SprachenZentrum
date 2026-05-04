@extends('frontoffice.layouts.app')

@section('title', __('track-translation.page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/track-translation.css') }}">

@section('content')
<main>

    {{-- ── HERO + SEARCH ───────────────────────────── --}}
    <section class="tt-hero">
        <div class="container tt-hero-inner">
            <span class="tt-eyebrow"><span class="dot"></span> {{ __('track-translation.eyebrow') }}</span>
            <h1>{{ __('track-translation.heading') }} <span class="accent">{!! __('track-translation.heading_accent') !!}</span></h1>
            <p class="lead">{{ __('track-translation.lead') }}</p>

            <div class="tt-search-card mt-4">
                <form method="GET" action="{{ route('front.translations.track') }}">
                    <label class="form-label">{{ __('track-translation.cin_label') }}</label>
                    <div class="input-icon mb-2">
                        <input type="text" name="cin" class="form-control text-uppercase"
                               placeholder="{{ __('track-translation.cin_placeholder') }}"
                               value="{{ $cin }}"
                               autocomplete="off"
                               autofocus required>
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div class="tt-search-helper">
                        <i class="bi bi-shield-check"></i>
                        {{ __('track-translation.helper_secure') }}
                    </div>
                    <button type="submit" class="tt-search-btn mt-3">
                        <i class="bi bi-search me-1"></i> {{ __('track-translation.search_btn') }}
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
                        <h4 class="mb-2">{{ __('track-translation.no_results_title') }}</h4>
                        <p>{{ __('track-translation.no_results_text', ['cin' => $cin]) }}<br>
                        {{ __('track-translation.no_results_hint') }}</p>
                    </div>
                @else
                    @php $studentName = $orders->first()->student_name; @endphp
                    @php $initials = collect(explode(' ', trim($studentName)))->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->implode(''); @endphp

                    <div class="tt-results-head">
                        <div class="tt-results-count">
                            {{ trans_choice('track-translation.orders_found', $orders->count(), ['count' => $orders->count()]) }}
                        </div>
                        <span class="tt-student-chip">
                            <span class="avatar">{{ $initials ?: '?' }}</span>
                            <span><span class="text-muted">{{ __('track-translation.student') }}&nbsp;:</span> <strong>{{ $studentName }}</strong></span>
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
                            $statusLabel = __('track-translation.status.' . $o->status);
                        @endphp

                        <div class="tt-order">
                            {{-- Head --}}
                            <div class="tt-order-head">
                                <div class="tt-order-meta">
                                    <div class="tt-order-id">{{ __('track-translation.order_label') }} <span class="hash">#{{ $o->id }}</span></div>
                                    <div class="tt-order-sub">
                                        <span><i class="bi bi-files"></i>{{ trans_choice('track-translation.documents', $o->items->count(), ['count' => $o->items->count()]) }}</span>
                                        <span class="dot-sep"><i class="bi bi-file-earmark-text"></i>{{ trans_choice('track-translation.pages', $o->totalPages(), ['count' => $o->totalPages()]) }}</span>
                                        @if($o->date_received)
                                            <span class="dot-sep"><i class="bi bi-calendar2-event"></i>{{ __('track-translation.deposited_on', ['date' => $o->date_received->format('d/m/Y')]) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <span class="tt-status-pill {{ $statusClass }}">
                                    <span class="dot"></span> {{ $statusLabel }}
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
                                                    <div class="tt-item-meta">{{ trans_choice('track-translation.page_count', $it->page_count, ['count' => $it->page_count]) }}</div>
                                                </div>
                                            </div>
                                            <div class="tt-item-price">{{ number_format($it->line_total, 0, ',', ' ') }} DH</div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="tt-total">
                                    <span class="tt-total-label">{{ __('track-translation.total_label') }}</span>
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
                                    <span class="tt-step-label">{{ __('track-translation.step_received') }}</span>
                                </div>
                                <div class="tt-step-line {{ $stepIdx >= 2 ? 'done' : '' }}"></div>

                                <div class="tt-step {{ $stepIdx >= 2 ? ($stepIdx === 2 ? 'current' : 'done') : '' }}">
                                    <span class="tt-step-circle">
                                        @if($stepIdx > 2) <i class="bi bi-check-lg"></i>
                                        @else <i class="bi bi-translate"></i>
                                        @endif
                                    </span>
                                    <span class="tt-step-label">{{ __('track-translation.step_translator') }}</span>
                                </div>
                                <div class="tt-step-line {{ $stepIdx >= 3 ? 'done' : '' }}"></div>

                                <div class="tt-step {{ $stepIdx >= 3 ? 'done' : '' }}">
                                    <span class="tt-step-circle">
                                        @if($stepIdx >= 3) <i class="bi bi-check-lg"></i>
                                        @else <i class="bi bi-bag-check"></i>
                                        @endif
                                    </span>
                                    <span class="tt-step-label">{{ __('track-translation.step_delivered') }}</span>
                                </div>
                            </div>

                            @if($o->status === 'delivered' && $o->date_handed_over)
                                <div class="tt-delivered-banner">
                                    <i class="bi bi-check-circle-fill"></i>
                                    {{ __('track-translation.delivered_banner', ['date' => $o->date_handed_over->format('d/m/Y')]) }}
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
