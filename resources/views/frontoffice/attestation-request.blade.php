@extends('frontoffice.layouts.app')

@section('title', __('attestation-request.page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}?v={{ @filemtime(public_path('assets/css/frontoffice/ressource/attestation-request.css')) ?: time() }}">

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

            <div class="att-gls-only" role="note">
                <div class="att-gls-only-icon"><i class="bi bi-shield-exclamation"></i></div>
                <div class="att-gls-only-body">
                    <strong>{{ __('attestation-request.gls_only_title') }}</strong>
                    <p>{{ __('attestation-request.gls_only_text') }}</p>
                </div>
            </div>

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
                            <label class="form-label" id="att-birth-date-label">{{ __('attestation-request.birth_date') }}</label>
                            <div class="att-datepicker" data-att-datepicker data-locale="{{ app()->getLocale() }}">
                                <input type="date" name="birth_date" class="att-datepicker__native"
                                       value="{{ old('birth_date') }}" aria-labelledby="att-birth-date-label">
                                <button type="button" class="att-datepicker__btn" aria-haspopup="dialog" aria-expanded="false" aria-labelledby="att-birth-date-label">
                                    <svg class="att-datepicker__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span class="att-datepicker__value att-datepicker__value--placeholder">{{ __('attestation-request.birth_date') }}</span>
                                    <svg class="att-datepicker__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </button>
                                <div class="att-datepicker__panel" role="dialog" aria-modal="false" aria-labelledby="att-birth-date-label" hidden>
                                    <div class="att-datepicker__head">
                                        <button type="button" class="att-datepicker__nav" data-nav="prev" aria-label="Previous month">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                        </button>
                                        <div class="att-datepicker__head-labels">
                                            <button type="button" class="att-datepicker__head-month" data-pick="month"></button>
                                            <button type="button" class="att-datepicker__head-year"  data-pick="year"></button>
                                        </div>
                                        <button type="button" class="att-datepicker__nav" data-nav="next" aria-label="Next month">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                        </button>
                                    </div>
                                    <div class="att-datepicker__weekdays" aria-hidden="true"></div>
                                    <div class="att-datepicker__grid" role="grid"></div>
                                    <div class="att-datepicker__foot">
                                        <button type="button" class="att-datepicker__action" data-action="clear">{{ __('attestation-request.dp_clear') }}</button>
                                        <button type="button" class="att-datepicker__action att-datepicker__action--primary" data-action="today">{{ __('attestation-request.dp_today') }}</button>
                                    </div>
                                </div>
                            </div>
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
                            <label class="form-label" id="att-level-label">{{ __('attestation-request.level') }} <span class="text-danger">*</span></label>
                            <div class="att-select" data-att-select>
                                <select name="level" class="att-select__native" required aria-labelledby="att-level-label">
                                    <option value="">{{ __('attestation-request.level_select') }}</option>
                                    @foreach (['A1', 'A2', 'B1', 'B2'] as $lvl)
                                        <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="att-level-label">
                                    <span class="att-select__value att-select__value--placeholder">{{ __('attestation-request.level_select') }}</span>
                                    <svg class="att-select__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </button>
                                <ul class="att-select__menu" role="listbox" tabindex="-1" aria-labelledby="att-level-label" hidden>
                                    @foreach (['A1', 'A2', 'B1', 'B2'] as $lvl)
                                        <li class="att-select__opt" role="option" data-value="{{ $lvl }}" tabindex="-1" aria-selected="{{ old('level') === $lvl ? 'true' : 'false' }}">
                                            <span class="att-select__dot" aria-hidden="true"></span>
                                            <span class="att-select__opt-label">{{ $lvl }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
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
