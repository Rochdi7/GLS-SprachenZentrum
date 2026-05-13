@extends('frontoffice.layouts.app')

@section('title', __('feedback.page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}?v={{ @filemtime(public_path('assets/css/frontoffice/ressource/attestation-request.css')) ?: time() }}">
<style>
    .att-optional{
        display:inline-block;font-size:.72rem;font-weight:600;letter-spacing:.02em;
        color:#6b7280;background:#f3f4f6;border:1px solid #e5e7eb;
        padding:1px 8px;border-radius:999px;margin-left:6px;vertical-align:middle;
        text-transform:lowercase;
    }
</style>

@section('content')
<main>

    {{-- ── HERO ─────────────────────────────────────── --}}
    <section class="att-hero">
        <div class="container att-hero-inner">
            <span class="att-eyebrow"><span class="dot"></span> {{ __('feedback.eyebrow') }}</span>
            <h1>{{ __('feedback.heading') }} <span class="accent">{{ __('feedback.heading_accent') }}</span></h1>
            <p class="lead">{{ __('feedback.lead') }}</p>

            <div class="att-steps">
                <div class="att-step-chip"><span class="num">1</span> {{ __('feedback.step_1') }}</div>
                <div class="att-step-chip muted"><span class="num">2</span> {{ __('feedback.step_2') }}</div>
                <div class="att-step-chip muted"><span class="num">3</span> {{ __('feedback.step_3') }}</div>
            </div>
        </div>
    </section>

    {{-- ── FORM ─────────────────────────────────────── --}}
    <section class="att-page">
        <div class="container">

            <div class="att-gls-only" role="note">
                <div class="att-gls-only-icon"><i class="bi bi-chat-heart-fill"></i></div>
                <div class="att-gls-only-body">
                    <strong>{{ __('feedback.notice_title') }}</strong>
                    <p>{{ __('feedback.notice_text') }}</p>
                </div>
            </div>

            <div class="att-card">

                @if ($errors->any())
                    <div class="att-error">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> {{ __('feedback.errors_title') }}</strong>
                        <ul>
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ LaravelLocalization::localizeUrl(route('front.feedback.store')) }}" method="POST" novalidate>
                    @csrf

                    {{-- IDENTITÉ --}}
                    <h3 class="att-section-title"><i class="bi bi-person-vcard"></i> {{ __('feedback.section_identity') }}</h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                {{ __('feedback.full_name') }}
                                <span class="att-optional">({{ __('feedback.optional') }})</span>
                            </label>
                            <input type="text" name="full_name" class="form-control"
                                   placeholder="{{ __('feedback.full_name_placeholder') }}"
                                   value="{{ old('full_name') }}">
                            <small class="help"><i class="bi bi-incognito"></i> {{ __('feedback.full_name_help') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" id="fb-site-label">{{ __('feedback.site') }} <span class="text-danger">*</span></label>
                            <div class="att-select" data-att-select>
                                <select name="site_id" class="att-select__native" required aria-labelledby="fb-site-label">
                                    <option value="">{{ __('feedback.site_select') }}</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}" {{ (string) old('site_id') === (string) $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}@if($site->city) — {{ $site->city }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="fb-site-label">
                                    <span class="att-select__value att-select__value--placeholder">{{ __('feedback.site_select') }}</span>
                                    <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </button>
                                <ul class="att-select__menu" role="listbox" tabindex="-1" aria-labelledby="fb-site-label" hidden>
                                    @foreach ($sites as $site)
                                        <li class="att-select__opt" role="option" data-value="{{ $site->id }}" tabindex="-1" aria-selected="{{ (string) old('site_id') === (string) $site->id ? 'true' : 'false' }}">
                                            <span class="att-select__dot" aria-hidden="true"></span>
                                            <span class="att-select__opt-label">{{ $site->name }}@if($site->city) — {{ $site->city }}@endif</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr class="att-divider">

                    {{-- MESSAGE --}}
                    <h3 class="att-section-title"><i class="bi bi-chat-quote"></i> {{ __('feedback.section_message') }}</h3>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="fb-message">{{ __('feedback.message') }} <span class="text-danger">*</span></label>
                            <textarea id="fb-message"
                                      name="message"
                                      class="form-control @error('message') is-invalid @enderror"
                                      rows="6"
                                      maxlength="5000"
                                      required
                                      placeholder="{{ __('feedback.message_placeholder') }}">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="help"><i class="bi bi-pencil-square"></i> {{ __('feedback.message_help') }}</small>
                        </div>
                    </div>

                    <div class="att-actions">
                        <span class="att-secure-note">
                            <i class="bi bi-shield-lock-fill"></i> {{ __('feedback.secure_note') }}
                        </span>
                        <button type="submit" class="att-submit">
                            <i class="bi bi-send-fill"></i> {{ __('feedback.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>
@endsection
