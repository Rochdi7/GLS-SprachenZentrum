@extends('frontoffice.layouts.app')

@section('title', __('attestation-request.page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}">

@section('content')
<main>

    {{-- ── HERO ─────────────────────────────────────── --}}
    <section class="att-hero">
        <div class="container att-hero-inner">
            <span class="att-eyebrow"><span class="dot"></span> {{ __('attestation-request.eyebrow') }}</span>
            <h1>{{ __('attestation-request.heading') }} <span class="accent">{{ __('attestation-request.heading_accent') }}</span></h1>
            <p class="lead">{{ __('attestation-request.lead') }}</p>

            <div class="att-steps">
                <div class="att-step-chip"><span class="num">1</span> {{ __('attestation-request.step_1') }}</div>
                <div class="att-step-chip muted"><span class="num">2</span> {{ __('attestation-request.step_2') }}</div>
                <div class="att-step-chip muted"><span class="num">3</span> {{ __('attestation-request.step_3') }}</div>
            </div>
        </div>
    </section>

    {{-- ── FORM ─────────────────────────────────────── --}}
    <section class="att-page">
        <div class="container">
            <div class="att-card">

                @if ($errors->any())
                    <div class="att-error">
                        <strong><i class="bi bi-exclamation-triangle-fill"></i> {{ __('attestation-request.errors_title') }}</strong>
                        <ul>
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.store')) }}" method="POST" novalidate>
                    @csrf

                    {{-- IDENTITÉ --}}
                    <h3 class="att-section-title"><i class="bi bi-person-vcard"></i> {{ __('attestation-request.section_identity') }}</h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.last_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="{{ __('attestation-request.email_placeholder') }}"
                                   value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.phone') }}</label>
                            <input type="tel" name="phone" class="form-control"
                                   placeholder="{{ __('attestation-request.phone_placeholder') }}"
                                   value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.birth_date') }}</label>
                            <input type="date" name="birth_date" class="form-control"
                                   value="{{ old('birth_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.birth_place') }}</label>
                            <input type="text" name="birth_place" class="form-control"
                                   value="{{ old('birth_place') }}">
                        </div>
                    </div>

                    <hr class="att-divider">

                    {{-- COURS --}}
                    <h3 class="att-section-title"><i class="bi bi-mortarboard"></i> {{ __('attestation-request.section_course') }}</h3>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('attestation-request.group_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="group_name" class="form-control" required
                                   placeholder="{{ __('attestation-request.group_name_placeholder') }}"
                                   value="{{ old('group_name') }}">
                            <small class="help"><i class="bi bi-info-circle"></i> {{ __('attestation-request.group_name_help') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('attestation-request.level') }} <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="">{{ __('attestation-request.level_select') }}</option>
                                @foreach (['A1', 'A2', 'B1', 'B2'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="att-notes">{{ __('attestation-request.notes_label') }}</label>
                            <textarea id="att-notes"
                                      name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="{{ __('attestation-request.notes_placeholder') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="help"><i class="bi bi-pencil-square"></i> {{ __('attestation-request.notes_help') }}</small>
                        </div>
                    </div>

                    <div class="att-actions">
                        <span class="att-secure-note">
                            <i class="bi bi-shield-lock-fill"></i> {{ __('attestation-request.secure_note') }}
                        </span>
                        <button type="submit" class="att-submit">
                            <i class="bi bi-send-fill"></i> {{ __('attestation-request.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>
@endsection
