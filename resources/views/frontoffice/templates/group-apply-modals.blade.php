{{-- resources/views/frontoffice/templates/group-apply-modals.blade.php --}}

<link rel="stylesheet" href="{{ asset('assets/css/gls-form.css') }}">

@php
    $applyGroups = $applyGroups ?? collect();
@endphp

<div class="modal fade" id="glsApplyGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: transparent; border: 0;">
            <div class="modal-body p-0">

                <div class="container" id="glsApplyRoot">

                    <div class="form-card">

                        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

                        <div class="decorative-element"></div>

                        <div class="form-content">

                            <div class="form-header" id="applyGroupFormHeader">
                                <h2 class="form-title">{{ __('templates/group-apply-modals.header.title') }}</h2>
                                <p class="form-subtitle">{{ __('templates/group-apply-modals.header.subtitle') }}</p>
                            </div>

                            <div class="error-message" id="applyGroupErrorMessage"></div>

                            <form id="applyGroupForm" method="POST" action="{{ route('front.groups.apply') }}">
                                @csrf

                                <input type="hidden" name="group_id" id="applyGroupId" value="">

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.group.label') }}</label>
                                    <input type="text" id="applyGroupLabel" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.group.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.schedule.label') }}</label>
                                    <input type="text" id="applyGroupSchedule" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.schedule.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.level.label') }}</label>
                                    <input type="text" id="applyGroupLevel" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.level.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyFullName">{{ __('templates/group-apply-modals.fields.full_name.label') }}
                                        <span class="required">*</span></label>
                                    <input type="text" id="applyFullName" name="full_name"
                                        placeholder="{{ __('templates/group-apply-modals.fields.full_name.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="apply_email">{{ __('templates/group-apply-modals.fields.email.label') }}
                                        <span class="required">*</span></label>
                                    <input type="email" id="apply_email" name="email"
                                        placeholder="{{ __('templates/group-apply-modals.fields.email.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="applyPhone">{{ __('templates/group-apply-modals.fields.phone.label') }}
                                        <span class="required">*</span></label>
                                    <input type="tel" id="applyPhone" name="phone"
                                        placeholder="{{ __('templates/group-apply-modals.fields.phone.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyAddress">{{ __('templates/group-apply-modals.fields.address.label') }}</label>
                                    <input type="text" id="applyAddress" name="address"
                                        placeholder="{{ __('templates/group-apply-modals.fields.address.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyBirthday">{{ __('templates/group-apply-modals.fields.birthday.label') }}</label>
                                    <div class="att-datepicker" data-att-datepicker data-locale="{{ app()->getLocale() }}">
                                        <input type="date" id="applyBirthday" name="birthday" class="att-datepicker__native"
                                               max="{{ now()->subYears(10)->format('Y-m-d') }}">
                                        <button type="button" class="att-datepicker__btn" aria-haspopup="dialog" aria-expanded="false">
                                            <svg class="att-datepicker__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <span class="att-datepicker__value att-datepicker__value--placeholder">{{ __('templates/group-apply-modals.fields.birthday.label') }}</span>
                                            <svg class="att-datepicker__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                        </button>
                                        <div class="att-datepicker__panel" role="dialog" aria-modal="false" hidden>
                                            <div class="att-datepicker__head">
                                                <button type="button" class="att-datepicker__nav" data-nav="prev" aria-label="Previous month"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
                                                <div class="att-datepicker__head-labels">
                                                    <button type="button" class="att-datepicker__head-month" data-pick="month"></button>
                                                    <button type="button" class="att-datepicker__head-year"  data-pick="year"></button>
                                                </div>
                                                <button type="button" class="att-datepicker__nav" data-nav="next" aria-label="Next month"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
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

                                <div class="form-group">
                                    <label
                                        for="applyNote">{{ __('templates/group-apply-modals.fields.note.label') }}</label>
                                    <textarea id="applyNote" name="note" placeholder="{{ __('templates/group-apply-modals.fields.note.placeholder') }}"></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="button" class="button"
                                        data-bs-dismiss="modal">{{ __('templates/group-apply-modals.buttons.cancel') }}</button>
                                    <button type="submit" class="button"
                                        id="applyGroupSubmitBtn">{{ __('templates/group-apply-modals.buttons.submit') }}</button>
                                </div>
                            </form>

                            <div class="success-message" id="applyGroupSuccessMessage">
                                <div class="success-icon"></div>
                                <h3>{{ __('templates/group-apply-modals.messages.success_title') }}</h3>
                                <p id="applyGroupSuccessText">
                                    {{ __('templates/group-apply-modals.messages.success_text') }}</p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- Fallback data (si tu veux ouvrir via ?group=ID) --}}
<select id="applyGroupsData" class="d-none">
    @foreach ($applyGroups as $g)
        <option value="{{ $g->id }}" data-label="{{ strtoupper($g->level ?? ($g->niveau ?? '')) . ' - ' . ($g->time_range ?? ($g->period_label ?? ($g->period ?? ''))) }}"
            data-schedule="{{ $g->period_label ?? ($g->period ?? '') }}"
            data-level="{{ $g->level ?? ($g->niveau ?? '') }}">
        </option>
    @endforeach
</select>

<script src="{{ asset('assets/js/apply-group.js') }}" defer></script>
