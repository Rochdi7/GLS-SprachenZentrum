@extends('frontoffice.layouts.app')

@section('title', __('certificate.page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/verify-certificate.css') }}">

@section('content')

    {{-- TOASTS --}}
    @if (session('certificate_error') || session('certificate_success'))
        <div class="position-fixed top-0 end-0 p-3 vc-toasts">
            @if (session('certificate_error'))
                <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('certificate_error') }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            @endif

            @if (session('certificate_success'))
                <div class="toast align-items-center text-bg-success border-0 show mt-2" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-check-circle-fill me-1"></i> {{ __('certificate.success_toast') }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            @endif
        </div>
    @endif

<main>

    {{-- ── HERO + SEARCH ───────────────────────────── --}}
    <section class="vc-hero">
        <div class="container vc-hero-inner">
            <span class="vc-eyebrow"><span class="dot"></span> {{ __('certificate.page_title') }}</span>
            <h1>{{ __('certificate.heading') }}</h1>
            <p class="lead">{{ __('certificate.subheading') }}</p>

            <div class="vc-search-card mt-4">
                <form action="{{ route('front.certificate.check.post') }}" method="POST">
                    @csrf
                    <label class="form-label">{{ __('certificate.form_label') }}</label>
                    <div class="input-icon mb-2">
                        <input type="text" name="certificate_number"
                               class="form-control text-uppercase"
                               placeholder="{{ __('certificate.form_placeholder') }}"
                               autocomplete="off"
                               autofocus required>
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <div class="vc-search-helper">
                        <i class="bi bi-shield-check"></i>
                        Vérification authentique délivrée par GLS Sprachenzentrum.
                    </div>
                    <button type="submit" class="vc-search-btn mt-3">
                        <i class="bi bi-search me-1"></i> {{ __('certificate.submit') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- ── RESULT ─────────────────────────────────── --}}
    @php
        $cert = session('certificate_success');
        $publicToken = is_array($cert) ? ($cert['public_token'] ?? null) : null;
    @endphp

    @if ($cert)
        <section class="vc-result-wrap">
            <div class="container">
                <div class="vc-cert-card">

                    <div class="vc-cert-banner">
                        <i class="bi bi-shield-check"></i>
                        Certificat vérifié et authentique
                    </div>

                    <div class="vc-cert-body">

                        {{-- LEFT --}}
                        <div class="vc-cert-info">
                            <h2><span class="badge-icon"><i class="bi bi-award-fill"></i></span> {{ __('certificate.details_title') }}</h2>

                            <div class="vc-row">
                                <span class="vc-row-icon"><i class="bi bi-person"></i></span>
                                <div class="vc-row-text">
                                    <div class="vc-row-label">{{ __('certificate.label_name') }}</div>
                                    <div class="vc-row-val">{{ $cert['first_name'] }} {{ $cert['last_name'] }}</div>
                                </div>
                            </div>

                            <div class="vc-row">
                                <span class="vc-row-icon"><i class="bi bi-mortarboard"></i></span>
                                <div class="vc-row-text">
                                    <div class="vc-row-label">{{ __('certificate.label_level') }}</div>
                                    <div class="vc-row-val">{{ $cert['exam_level'] }}</div>
                                </div>
                            </div>

                            <div class="vc-row">
                                <span class="vc-row-icon"><i class="bi bi-calendar-event"></i></span>
                                <div class="vc-row-text">
                                    <div class="vc-row-label">{{ __('certificate.label_exam_date') }}</div>
                                    <div class="vc-row-val">{{ \Carbon\Carbon::parse($cert['exam_date'])->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <div class="vc-row">
                                <span class="vc-row-icon"><i class="bi bi-calendar2-check"></i></span>
                                <div class="vc-row-text">
                                    <div class="vc-row-label">{{ __('certificate.label_issued_date') }}</div>
                                    <div class="vc-row-val">{{ \Carbon\Carbon::parse($cert['issued_date'])->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <div class="vc-row">
                                <span class="vc-row-icon"><i class="bi bi-hash"></i></span>
                                <div class="vc-row-text">
                                    <div class="vc-row-label">{{ __('certificate.label_number') }}</div>
                                    <div class="vc-row-val vc-cert-num">{{ $cert['certificate_number'] }}</div>
                                </div>
                            </div>

                            <div class="vc-cert-cta">
                                @if($publicToken)
                                    <a href="{{ route('certificates.public.download', ['token' => $publicToken]) }}"
                                       target="_blank"
                                       class="vc-download-btn">
                                        <i class="bi bi-download"></i> {{ __('certificate.download_pdf') }}
                                    </a>
                                @else
                                    <div class="vc-warning">
                                        <i class="bi bi-exclamation-triangle"></i> {{ __('certificate.token_missing') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- RIGHT — QR --}}
                        <div class="vc-cert-qr">
                            @if($publicToken)
                                <div class="vc-qr-frame">
                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('certificates.public.download', ['token' => $publicToken])) }}"
                                        alt="QR Code Certificat">
                                </div>
                                <div class="vc-qr-caption">
                                    <strong>{{ __('certificate.qr_caption_line1') }}</strong>
                                    {{ __('certificate.qr_caption_line2') }}
                                </div>
                            @else
                                <div class="vc-qr-unavailable">
                                    <i class="bi bi-qr-code"></i><br>
                                    {{ __('certificate.qr_unavailable') }}
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </section>
    @endif

</main>

@endsection
