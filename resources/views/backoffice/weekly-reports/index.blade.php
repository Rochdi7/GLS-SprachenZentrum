@extends('layouts.main')

@section('title', 'Rapport Semaine')
@section('breadcrumb-item', 'Pilotage')
@section('breadcrumb-item-active', 'Rapport Semaine')

@section('css')
<style>
    /* ===== Week Navigation ===== */
    .week-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .week-nav .btn { padding: 6px 12px; white-space: nowrap; font-size: 0.82rem; }
    .week-label {
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        min-width: 200px;
    }

    /* ===== Desktop: Table Calendar (>=992px) ===== */
    .calendar-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .week-calendar { width: 100%; min-width: 700px; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
    .week-calendar th {
        background: #4680ff;
        color: #fff;
        text-align: center;
        padding: 10px 6px;
        font-size: 0.82rem;
        font-weight: 600;
        width: 20%;
    }
    .week-calendar th:first-child { border-radius: 8px 0 0 0; }
    .week-calendar th:last-child  { border-radius: 0 8px 0 0; }

    .week-calendar td {
        border: 1px solid #e9ecef;
        vertical-align: top;
        padding: 8px;
        min-height: 140px;
        height: 140px;
        width: 20%;
        cursor: pointer;
        transition: background .15s;
        position: relative;
    }
    .week-calendar td:hover { background: #f0f4ff; }
    .week-calendar td.today { background: #f0f7ff; border-color: #4680ff; }

    .day-number {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 6px;
        color: #333;
    }
    .today .day-number { color: #4680ff; }

    .btn-add-day {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background: #4680ff;
        color: #fff;
        font-size: 16px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s;
    }
    .week-calendar td:hover .btn-add-day { opacity: 1; }

    /* ===== Report Chip (shared) ===== */
    .report-chip {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        background: #e8f0fe;
        border-left: 3px solid #4680ff;
        border-radius: 4px;
        padding: 6px 8px;
        margin-bottom: 4px;
        font-size: 0.78rem;
        line-height: 1.35;
        cursor: pointer;
        transition: background .15s;
    }
    .report-chip:hover { background: #d4e4fd; }
    .report-chip .teacher-name {
        font-weight: 600;
        color: #1a3a6e;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .report-chip .notes-preview {
        color: #555;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }
    .report-chip .pdf-icon {
        color: #d9534f;
        flex-shrink: 0;
        font-size: 0.95rem;
        line-height: 1;
    }
    .report-chip .group-badge {
        background: #fff3cd;
        color: #856404;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 8px;
        white-space: nowrap;
        flex-shrink: 0;
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .attachment-existing {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        font-size: 0.85rem;
    }
    .attachment-existing a {
        color: #4680ff;
        text-decoration: none;
        font-weight: 500;
        word-break: break-all;
        flex: 1;
        min-width: 0;
    }
    .attachment-existing a:hover { text-decoration: underline; }

    /* ===== Multi-row modal ===== */
    #reportModal .modal-dialog { max-height: calc(100vh - 1rem); }
    #reportModal .modal-content { max-height: calc(100vh - 2rem); display: flex; flex-direction: column; overflow: hidden; }
    #reportModal #reportForm { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden; }
    #reportModal .modal-header,
    #reportModal .modal-footer { flex: 0 0 auto; }
    #reportModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        min-height: 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    #reportModal .modal-header { padding: 14px 18px; align-items: flex-start; }
    #reportModal .modal-header .btn-close { margin: 4px 0 0 auto; padding: 8px; flex-shrink: 0; }
    #reportModal .modal-title { font-size: 1.05rem; font-weight: 600; }
    #reportModal #modalDateLabel { font-size: 0.78rem; text-transform: capitalize; }

    .note-row {
        position: relative;
        border: 1px solid #e3e7ec;
        border-radius: 8px;
        padding: 14px 16px 14px 16px;
        margin-bottom: 14px;
        background: #fafbfc;
        transition: background .15s, border-color .15s;
    }
    .note-row:hover { background: #f5f7fa; border-color: #cfd6df; }
    .note-row .row-number {
        position: absolute;
        top: -9px;
        left: 14px;
        background: #4680ff;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 10px;
        letter-spacing: .3px;
    }
    .note-row .btn-remove-row {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #adb5bd;
        font-size: 12px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, color .15s;
        padding: 0;
    }
    .note-row .btn-remove-row:hover { background: #fee2e2; color: #b91c1c; }
    .note-row .btn-remove-row i { font-size: 13px; }

    .note-row .btn-eye-row {
        position: absolute;
        top: 10px;
        right: 38px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #6c757d;
        font-size: 12px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, color .15s;
        padding: 0;
    }
    .note-row .btn-eye-row:hover { background: #e0f2fe; color: #0284c7; }
    .note-row .btn-eye-row i { font-size: 14px; }

    .note-row .form-label { font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; color: #495057; }
    .note-row .form-control,
    .note-row .form-select { font-size: 0.85rem; }
    .note-row textarea { resize: vertical; min-height: 70px; }

    /* ===== Skills grid mode (multi-group teachers) ===== */
    .skills-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .skill-block {
        display: grid;
        grid-template-columns: 110px 1fr;
        gap: 10px;
        align-items: start;
    }
    .skill-block .skill-label {
        background: #1e3a5f;
        color: #fff;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 6px 8px;
        border-radius: 4px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
    }
    .skill-block > *:not(.skill-label) {
        grid-column: 2;
    }
    .skill-block .skill-notes,
    .skill-block .skill-attachment {
        font-size: .78rem;
    }
    .skill-block .existing-pdf {
        padding: 3px 6px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 3px;
    }
    .mode-skills .row-number { background: #f59e0b; }
    @media (max-width: 575.98px) {
        .skill-block { grid-template-columns: 80px 1fr; gap: 6px; }
        .skill-block .skill-label { font-size: .65rem; padding: 4px 5px; min-height: 44px; }
    }
    .note-row .file-hint { font-size: 0.7rem; color: #adb5bd; margin-top: 2px; display: block; }
    .note-row .existing-pdf {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        margin-bottom: 6px;
        padding: 6px 9px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 4px;
    }
    .note-row .existing-pdf a { color: #4680ff; text-decoration: none; word-break: break-all; flex: 1; min-width: 0; }
    .note-row .existing-pdf a:hover { text-decoration: underline; }

    .btn-add-row {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #4680ff;
        color: #fff;
        border: none;
        padding: 7px 14px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: background .15s, transform .1s;
        white-space: nowrap;
    }
    .btn-add-row:hover { background: #3a6fd6; }
    .btn-add-row:active { transform: translateY(1px); }
    .btn-add-row i { font-size: 0.95rem; line-height: 1; }

    .btn-add-row-ghost {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: transparent;
        color: #4680ff;
        border: 1.5px dashed #4680ff;
        padding: 7px 16px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-add-row-ghost:hover { background: #eef3ff; }
    .btn-add-row-ghost i { font-size: 0.95rem; }

    #emptyRowsHint {
        text-align: center;
        color: #adb5bd;
        font-style: italic;
        padding: 30px 10px;
        border: 2px dashed #e9ecef;
        border-radius: 8px;
    }
    #emptyRowsHint.is-loading { border-style: solid; border-color: #e0e7ff; background: #f8faff; }
    #emptyRowsHint.is-loading .loading-wrap {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-style: normal;
        color: #4680ff;
        font-weight: 500;
    }
    .loading-spinner {
        width: 22px;
        height: 22px;
        border: 3px solid #e0e7ff;
        border-top-color: #4680ff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 575.98px) {
        .btn-add-row { padding: 6px 10px; font-size: 0.78rem; }
        .note-row { padding: 14px 12px; }
    }

    /* ===== Mobile: Stacked Day Cards (<992px) ===== */
    .mobile-days { display: none; }

    .day-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
        cursor: pointer;
        transition: box-shadow .15s;
    }
    .day-card:active { box-shadow: 0 0 0 3px rgba(70,128,255,.25); }
    .day-card.today { border-color: #4680ff; }

    .day-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .day-card.today .day-card-header { background: #edf3ff; }
    .day-card-header .day-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #333;
    }
    .day-card.today .day-card-header .day-label { color: #4680ff; }
    .day-card-header .btn-add-mobile {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: #4680ff;
        color: #fff;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-card-body {
        padding: 10px 14px;
        min-height: 50px;
    }
    .day-card-body .report-chip {
        font-size: 0.85rem;
        padding: 8px 10px;
    }
    .day-card-body .report-chip .notes-preview {
        max-width: none;
        white-space: normal;
    }
    .day-card-body .empty-label {
        color: #adb5bd;
        font-size: 0.82rem;
        font-style: italic;
    }

    /* ===== Responsive breakpoints ===== */
    @media (max-width: 991.98px) {
        .desktop-calendar { display: none !important; }
        .mobile-days { display: block !important; }
    }
    @media (min-width: 992px) {
        .desktop-calendar { display: block; }
        .mobile-days { display: none; }
    }

    /* Tablet tweaks */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .week-calendar td { padding: 6px; height: 120px; }
        .report-chip .notes-preview { max-width: 90px; }
        .week-calendar th { font-size: 0.75rem; padding: 8px 4px; }
    }

    /* Large desktop */
    @media (min-width: 1200px) {
        .report-chip .notes-preview { max-width: 140px; }
    }

    /* ===== Month Modal Calendar ===== */
    .month-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .month-nav .month-label {
        font-size: 1rem;
        font-weight: 600;
        min-width: 180px;
        text-align: center;
        text-transform: capitalize;
    }
    .month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }
    .month-grid .mg-head {
        background: #4680ff;
        color: #fff;
        text-align: center;
        padding: 6px 2px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 4px;
        text-transform: capitalize;
    }
    .month-grid .mg-cell {
        border: 1px solid #e9ecef;
        border-radius: 4px;
        min-height: 90px;
        padding: 4px 5px;
        font-size: 0.72rem;
        background: #fff;
        overflow: hidden;
        position: relative;
    }
    .month-grid .mg-cell.other-month { background: #f8f9fa; color: #adb5bd; }
    .month-grid .mg-cell.today { border-color: #4680ff; background: #f0f7ff; }
    .month-grid .mg-cell.weekend { background: #fafafa; }
    .month-grid .mg-cell .mg-day { font-weight: 700; font-size: 0.82rem; margin-bottom: 2px; color: #333; }
    .month-grid .mg-cell.today .mg-day { color: #4680ff; }
    .month-grid .mg-cell .mg-chip {
        background: #e8f0fe;
        border-left: 2px solid #4680ff;
        padding: 2px 4px;
        margin-bottom: 2px;
        border-radius: 3px;
        font-size: 0.68rem;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .month-grid .mg-cell .mg-chip .tn { font-weight: 600; color: #1a3a6e; }
    .month-grid .mg-cell .mg-more { font-size: 0.68rem; color: #4680ff; font-weight: 600; }
    @media (max-width: 575.98px) {
        .month-grid .mg-cell { min-height: 60px; padding: 2px 3px; }
        .month-grid .mg-cell .mg-day { font-size: 0.72rem; }
        .month-grid .mg-cell .mg-chip { display: none; }
        .month-grid .mg-cell .mg-dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #4680ff;
            margin-right: 2px;
        }
    }
    @media (min-width: 576px) {
        .month-grid .mg-cell .mg-dot { display: none; }
    }
</style>
@endsection

@section('content')

{{-- Toast --}}
@if (session('success') || session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast hide" role="alert">
            <div class="toast-header">
                <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="" style="width:17px">
                <strong class="me-auto">GLS Backoffice</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">{{ session('success') ?? session('error') }}</div>
        </div>
    </div>
@endif

{{-- Validation errors banner (surfaces backend validation failures so saves don't fail silently) --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <strong><i class="ph-duotone ph-warning-circle me-1"></i> Erreur d'enregistrement :</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">

            {{-- Header with week navigation --}}
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h5 class="mb-0">Rapport Semaine — Enseignants</h5>
                    <a href="{{ route('backoffice.weekly_reports.export_pdf', ['week' => $date->format('Y-m-d')]) }}"
                       class="btn btn-outline-danger btn-sm">
                        <i class="ph-duotone ph-file-pdf me-1"></i> Export PDF
                    </a>
                </div>

                <div class="week-nav">
                    <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->subWeek()->format('Y-m-d')]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="ph-duotone ph-caret-left"></i> Préc.
                    </a>

                    <span class="week-label">
                        {{ $weekDays->first()->locale('fr')->isoFormat('D MMM') }} — {{ $weekDays->last()->locale('fr')->isoFormat('D MMM YYYY') }}
                    </span>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnOpenMonth"
                            title="Voir le mois complet" onclick="openMonthModal()">
                        <i class="ph-duotone ph-calendar-blank"></i>
                    </button>

                    <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->addWeek()->format('Y-m-d')]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        Suiv. <i class="ph-duotone ph-caret-right"></i>
                    </a>

                    @if (!$date->isCurrentWeek())
                        <a href="{{ route('backoffice.weekly_reports.index') }}" class="btn btn-primary btn-sm">Aujourd'hui</a>
                    @endif
                </div>
            </div>

            <div class="card-body p-2 p-md-3">

                {{-- ===== DESKTOP: Table Calendar ===== --}}
                <div class="desktop-calendar">
                    <div class="calendar-scroll">
                    <table class="week-calendar">
                        <thead>
                            <tr>
                                @foreach ($weekDays as $day)
                                    <th>{{ $day->locale('fr')->isoFormat('dddd DD/MM') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($weekDays as $day)
                                    @php
                                        $key        = $day->format('Y-m-d');
                                        $isToday    = $day->isToday();
                                        $dayReports = $reports[$key] ?? collect();
                                    @endphp
                                    <td class="{{ $isToday ? 'today' : '' }}"
                                        data-date="{{ $key }}"
                                        onclick="openDayModal('{{ $key }}', '{{ $day->locale('fr')->isoFormat('dddd D MMM YYYY') }}')">

                                        <div class="day-number">{{ $day->format('d') }}</div>
                                        <button class="btn-add-day" title="Ajouter rapport">+</button>

                                        @foreach ($dayReports as $report)
                                            <div class="report-chip"
                                                 onclick="event.stopPropagation(); openDayModal('{{ $key }}', '{{ $day->locale('fr')->isoFormat('dddd D MMM YYYY') }}')">
                                                <span class="teacher-name">{{ $report->teacher->name }}</span>
                                                @if ($report->group)
                                                    <span class="group-badge" title="{{ $report->group->name }}">{{ $report->group->name }}</span>
                                                @endif
                                                <span class="notes-preview">{{ Str::limit($report->notes, 40) }}</span>
                                                @if ($report->attachment_path)
                                                    <i class="ph-duotone ph-file-pdf pdf-icon" title="PDF joint"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                {{-- ===== MOBILE / TABLET: Stacked Day Cards ===== --}}
                <div class="mobile-days">
                    @foreach ($weekDays as $day)
                        @php
                            $key        = $day->format('Y-m-d');
                            $isToday    = $day->isToday();
                            $dayReports = $reports[$key] ?? collect();
                        @endphp
                        <div class="day-card {{ $isToday ? 'today' : '' }}">
                            <div class="day-card-header">
                                <span class="day-label">{{ $day->locale('fr')->isoFormat('dddd D MMM') }}</span>
                                <button class="btn-add-mobile"
                                        onclick="event.stopPropagation(); openDayModal('{{ $key }}', '{{ $day->locale('fr')->isoFormat('dddd D MMM YYYY') }}')"
                                        title="Ajouter rapport">+</button>
                            </div>
                            <div class="day-card-body"
                                 onclick="openDayModal('{{ $key }}', '{{ $day->locale('fr')->isoFormat('dddd D MMM YYYY') }}')">

                                @forelse ($dayReports as $report)
                                    <div class="report-chip"
                                         onclick="event.stopPropagation(); openDayModal('{{ $key }}', '{{ $day->locale('fr')->isoFormat('dddd D MMM YYYY') }}')">
                                        <span class="teacher-name">{{ $report->teacher->name }}</span>
                                        @if ($report->group)
                                            <span class="group-badge" title="{{ $report->group->name }}">{{ $report->group->name }}</span>
                                        @endif
                                        <span class="notes-preview">{{ Str::limit($report->notes, 80) }}</span>
                                        @if ($report->attachment_path)
                                            <i class="ph-duotone ph-file-pdf pdf-icon" title="PDF joint"></i>
                                        @endif
                                    </div>
                                @empty
                                    <span class="empty-label">Aucun rapport — toucher pour ajouter</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ==================== MODAL: Day Reports (multi-row list) ==================== --}}
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <form id="reportForm" method="POST" action="{{ route('backoffice.weekly_reports.batch_sync') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="report_date" id="modalDate">

                <div class="modal-header">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title mb-0" id="modalTitle">Rapports du jour</h5>
                        <small class="text-muted" id="modalDateLabel"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="text-muted" style="font-size:.85rem;">
                            <i class="ph-duotone ph-list-checks me-1"></i>
                            <span id="rowsCountLabel">0 note(s)</span>
                        </span>
                        <button type="button" class="btn-add-row" onclick="addRow()">
                            <i class="ph-duotone ph-plus"></i> Ajouter une note
                        </button>
                    </div>

                    <div id="rowsContainer"></div>

                    <div id="emptyRowsHint">
                        Aucune note pour ce jour.<br>
                        Cliquez sur <strong>« Ajouter une note »</strong> pour commencer.
                    </div>

                    <div class="text-center mt-3" id="addRowFooter" style="display:none;">
                        <button type="button" class="btn btn-add-row-ghost" onclick="addRow()">
                            <i class="ph-duotone ph-plus"></i> Ajouter une autre note
                        </button>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph-duotone ph-floppy-disk"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden teacher options (used by JS to clone dropdowns) --}}
<template id="teacherOptionsTpl">
    <option value="">— Sélectionner un enseignant —</option>
    @foreach ($teachers as $teacher)
        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
    @endforeach
</template>

{{-- ==================== MODAL: Full Month Calendar ==================== --}}
<div class="modal fade" id="monthModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ph-duotone ph-calendar-blank me-1"></i> Vue mensuelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="month-nav">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="shiftMonth(-1)">
                        <i class="ph-duotone ph-caret-left"></i>
                    </button>
                    <span class="month-label" id="monthLabel">—</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="shiftMonth(1)">
                        <i class="ph-duotone ph-caret-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="goToMonth(new Date())">
                        Aujourd'hui
                    </button>
                </div>
                <div id="monthLoading" class="text-center py-3 d-none">
                    <span class="loading-wrap" style="display:inline-flex; align-items:center; gap:10px; color:#4680ff; font-weight:500;">
                        <span class="loading-spinner"></span> Chargement…
                    </span>
                </div>
                <div class="month-grid" id="monthGrid"></div>
            </div>
        </div>
    </div>
</div>

{{-- Delete form (hidden) --}}
<form id="deleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
    const FOR_DAY_URL = '{{ route('backoffice.weekly_reports.for_day') }}';
    const TEACHER_GROUPS = @json($teacherGroupsMap);
    const SKILLS = @json(\App\Models\WeeklyReport::SKILLS);
    const rowsContainer = document.getElementById('rowsContainer');
    const emptyHint = document.getElementById('emptyRowsHint');
    let rowCounter = 0;

    function escapeAttr(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function teacherOptionsHtml(selectedId) {
        const tpl = document.getElementById('teacherOptionsTpl').innerHTML;
        if (selectedId === undefined || selectedId === null || selectedId === '') return tpl;
        const target = `<option value="${selectedId}"`;
        return tpl.replace(target, `${target} selected`);
    }

    function groupOptionsHtml(teacherId, selectedGroupId) {
        let html = '<option value="">— Aucun groupe / général —</option>';
        if (!teacherId) return html;
        const groups = TEACHER_GROUPS[teacherId] || [];
        for (const g of groups) {
            const sel = (selectedGroupId && String(selectedGroupId) === String(g.id)) ? ' selected' : '';
            html += `<option value="${g.id}"${sel}>${escapeAttr(g.label)}</option>`;
        }
        return html;
    }

    function teacherHasMultipleGroups(teacherId) {
        if (!teacherId) return false;
        const groups = TEACHER_GROUPS[teacherId] || [];
        return groups.length > 1;
    }

    function onTeacherChange(selectEl) {
        const row = selectEl.closest('.note-row');
        if (!row) return;
        const groupSel = row.querySelector('select.group-select');
        if (groupSel) {
            groupSel.innerHTML = groupOptionsHtml(selectEl.value, null);
        }

        // If the teacher has multiple groups and the row is currently in simple mode,
        // upgrade it to skills mode (grid). And vice-versa.
        const isSimple = row.classList.contains('mode-simple');
        const shouldBeMulti = teacherHasMultipleGroups(selectEl.value);
        if (isSimple && shouldBeMulti) {
            const replacement = buildSkillsRow({
                teacher_id: selectEl.value,
                group_id: null,
                skills: {},
            });
            row.replaceWith(replacement);
            refreshRowNumbers();
            return;
        }
        if (!isSimple && !shouldBeMulti) {
            const replacement = buildSimpleRow({
                teacher_id: selectEl.value,
                group_id: null,
                notes: '',
            });
            row.replaceWith(replacement);
            refreshRowNumbers();
            return;
        }
    }

    // Decide which builder to use based on the teacher's groups count.
    function buildRow(report) {
        const r = report || {};
        const isMulti = teacherHasMultipleGroups(r.teacher_id);
        return isMulti ? buildSkillsRow(r) : buildSimpleRow(r);
    }

    // ===== Simple row (single-group teacher OR teacher not yet selected) =====
    function buildSimpleRow(report) {
        const idx = rowCounter++;
        const r = report || {};
        const hasPdf = !!r.attachment_url;
        const idInput = r.id ? `<input type="hidden" name="rows[${idx}][id]" value="${r.id}">` : '';

        const existingPdf = hasPdf
            ? `<div class="existing-pdf">
                   <i class="ph-duotone ph-file-pdf" style="color:#d9534f; font-size:1.2rem;"></i>
                   <a href="${escapeAttr(r.attachment_url)}" target="_blank" rel="noopener">${escapeAttr(r.attachment_name || 'Voir le PDF')}</a>
                   <div class="form-check form-check-inline mb-0 ms-2">
                       <input class="form-check-input" type="checkbox" name="rows[${idx}][remove_attachment]" id="remove_${idx}" value="1">
                       <label class="form-check-label" for="remove_${idx}" style="font-size:.78rem;">Supprimer</label>
                   </div>
               </div>`
            : '';

        const pdfLabel = hasPdf ? 'Remplacer le PDF (optionnel)' : 'Joindre un fichier PDF (optionnel)';

        // Build row using DOM API rather than innerHTML to avoid third-party sanitizers
        // (browser extensions / CSP) that strip <textarea> from innerHTML strings.
        const row = document.createElement('div');
        row.className = 'note-row mode-simple';
        row.dataset.rowIndex = idx;

        // Top: row label + remove button
        const rowNumber = document.createElement('span');
        rowNumber.className = 'row-number';
        rowNumber.textContent = `Note #${idx + 1}`;
        row.appendChild(rowNumber);

        // Eye button (only if saved — has an id)
        if (r.id) {
            const btnEye = document.createElement('button');
            btnEye.type = 'button';
            btnEye.className = 'btn-eye-row';
            btnEye.title = 'Voir le détail / Exporter en PDF';
            btnEye.innerHTML = '<i class="ph-duotone ph-eye"></i>';
            btnEye.addEventListener('click', () => openDetailFromRow(row));
            row.appendChild(btnEye);
        }

        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.className = 'btn-remove-row';
        btnRemove.title = 'Supprimer cette note';
        btnRemove.innerHTML = '<i class="ph-duotone ph-x"></i>';
        btnRemove.addEventListener('click', () => removeRow(btnRemove));
        row.appendChild(btnRemove);

        // Hidden id input for existing reports
        if (r.id) {
            const idIn = document.createElement('input');
            idIn.type = 'hidden';
            idIn.name = `rows[${idx}][id]`;
            idIn.value = r.id;
            row.appendChild(idIn);
        }

        // Grid container
        const grid = document.createElement('div');
        grid.className = 'row g-2';
        row.appendChild(grid);

        // -- Teacher select --
        const colT = document.createElement('div');
        colT.className = 'col-md-6';
        colT.innerHTML = '<label class="form-label">Enseignant</label>';
        const teacherSel = document.createElement('select');
        teacherSel.name = `rows[${idx}][teacher_id]`;
        teacherSel.className = 'form-select form-select-sm teacher-select';
        teacherSel.required = true;
        teacherSel.addEventListener('change', () => onTeacherChange(teacherSel));
        teacherSel.innerHTML = teacherOptionsHtml(r.teacher_id);
        colT.appendChild(teacherSel);
        grid.appendChild(colT);

        // -- Group select --
        const colG = document.createElement('div');
        colG.className = 'col-md-6';
        colG.innerHTML = '<label class="form-label">Groupe (optionnel)</label>';
        const groupSel = document.createElement('select');
        groupSel.name = `rows[${idx}][group_id]`;
        groupSel.className = 'form-select form-select-sm group-select';
        groupSel.innerHTML = groupOptionsHtml(r.teacher_id, r.group_id);
        colG.appendChild(groupSel);
        grid.appendChild(colG);

        // -- Notes textarea (built via DOM API to bypass innerHTML sanitizers) --
        const colN = document.createElement('div');
        colN.className = 'col-12 mt-1';
        const notesLabel = document.createElement('label');
        notesLabel.className = 'form-label';
        notesLabel.textContent = 'Notes / Activités';
        colN.appendChild(notesLabel);
        const ta = document.createElement('textarea');
        ta.name = `rows[${idx}][notes]`;
        ta.className = 'form-control form-control-sm note-textarea';
        ta.rows = 3;
        ta.placeholder = "Décrivez l'activité...";
        ta.required = true;
        ta.style.display = 'block';
        ta.style.width = '100%';
        ta.style.minHeight = '70px';
        ta.value = r.notes || '';
        colN.appendChild(ta);
        grid.appendChild(colN);

        // -- File attachment --
        const colF = document.createElement('div');
        colF.className = 'col-12 mt-2';
        if (hasPdf) {
            const pdfBlock = document.createElement('div');
            pdfBlock.className = 'existing-pdf';
            const pdfIcon = document.createElement('i');
            pdfIcon.className = 'ph-duotone ph-file-pdf';
            pdfIcon.style.cssText = 'color:#d9534f; font-size:1.2rem;';
            pdfBlock.appendChild(pdfIcon);
            const pdfLink = document.createElement('a');
            pdfLink.href = r.attachment_url;
            pdfLink.target = '_blank';
            pdfLink.rel = 'noopener';
            pdfLink.textContent = r.attachment_name || 'Voir le PDF';
            pdfBlock.appendChild(pdfLink);
            const checkWrap = document.createElement('div');
            checkWrap.className = 'form-check form-check-inline mb-0 ms-2';
            const checkInput = document.createElement('input');
            checkInput.type = 'checkbox';
            checkInput.className = 'form-check-input';
            checkInput.name = `rows[${idx}][remove_attachment]`;
            checkInput.id = `remove_${idx}`;
            checkInput.value = '1';
            checkWrap.appendChild(checkInput);
            const checkLabel = document.createElement('label');
            checkLabel.className = 'form-check-label';
            checkLabel.htmlFor = `remove_${idx}`;
            checkLabel.style.fontSize = '.78rem';
            checkLabel.textContent = 'Supprimer';
            checkWrap.appendChild(checkLabel);
            pdfBlock.appendChild(checkWrap);
            colF.appendChild(pdfBlock);
        }
        const fileLabel = document.createElement('label');
        fileLabel.className = 'form-label';
        fileLabel.textContent = pdfLabel;
        colF.appendChild(fileLabel);
        const fileIn = document.createElement('input');
        fileIn.type = 'file';
        fileIn.name = `rows[${idx}][attachment]`;
        fileIn.className = 'form-control form-control-sm';
        fileIn.accept = 'application/pdf';
        colF.appendChild(fileIn);
        const fileHint = document.createElement('span');
        fileHint.className = 'file-hint';
        fileHint.textContent = 'PDF uniquement — 10 Mo max.';
        colF.appendChild(fileHint);
        grid.appendChild(colF);

        return row;
    }

    // ===== Skills row (multi-group teacher) =====
    // `report` shape: { teacher_id, group_id, skills: { lesen: {id, notes, attachment_url, attachment_name}, ... } }
    function buildSkillsRow(report) {
        const idx = rowCounter++;
        const r = report || {};
        const skillsData = r.skills || {};

        const row = document.createElement('div');
        row.className = 'note-row mode-skills';
        row.dataset.rowIndex = idx;

        // Header: row label + remove button
        const rowNumber = document.createElement('span');
        rowNumber.className = 'row-number';
        rowNumber.textContent = `Groupe #${idx + 1}`;
        row.appendChild(rowNumber);

        // Eye button (only if at least one skill is saved)
        const anySaved = Object.values(skillsData).some(s => s && s.id);
        if (anySaved) {
            const btnEye = document.createElement('button');
            btnEye.type = 'button';
            btnEye.className = 'btn-eye-row';
            btnEye.title = 'Voir le détail / Exporter en PDF';
            btnEye.innerHTML = '<i class="ph-duotone ph-eye"></i>';
            btnEye.addEventListener('click', () => openDetailFromRow(row));
            row.appendChild(btnEye);
        }

        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.className = 'btn-remove-row';
        btnRemove.title = 'Supprimer ce groupe';
        btnRemove.innerHTML = '<i class="ph-duotone ph-x"></i>';
        btnRemove.addEventListener('click', () => removeRow(btnRemove));
        row.appendChild(btnRemove);

        // Top: teacher + group selectors
        const topGrid = document.createElement('div');
        topGrid.className = 'row g-2';
        row.appendChild(topGrid);

        const colT = document.createElement('div');
        colT.className = 'col-md-6';
        colT.innerHTML = '<label class="form-label">Enseignant</label>';
        const teacherSel = document.createElement('select');
        teacherSel.className = 'form-select form-select-sm teacher-select';
        teacherSel.required = true;
        teacherSel.innerHTML = teacherOptionsHtml(r.teacher_id);
        teacherSel.addEventListener('change', () => onTeacherChange(teacherSel));
        colT.appendChild(teacherSel);
        topGrid.appendChild(colT);

        const colG = document.createElement('div');
        colG.className = 'col-md-6';
        colG.innerHTML = '<label class="form-label">Groupe</label>';
        const groupSel = document.createElement('select');
        groupSel.className = 'form-select form-select-sm group-select';
        groupSel.required = true;
        groupSel.innerHTML = groupOptionsHtml(r.teacher_id, r.group_id);
        colG.appendChild(groupSel);
        topGrid.appendChild(colG);

        // Skills container
        const skillsWrap = document.createElement('div');
        skillsWrap.className = 'skills-grid mt-3';
        row.appendChild(skillsWrap);

        Object.entries(SKILLS).forEach(([skillKey, skillLabel]) => {
            const skillData = skillsData[skillKey] || {};
            const block = document.createElement('div');
            block.className = 'skill-block';
            block.dataset.skill = skillKey;

            // Header label
            const headLabel = document.createElement('div');
            headLabel.className = 'skill-label';
            headLabel.textContent = skillLabel;
            block.appendChild(headLabel);

            // Hidden id input if updating existing skill report
            if (skillData.id) {
                const idIn = document.createElement('input');
                idIn.type = 'hidden';
                idIn.className = 'skill-id-input';
                idIn.value = skillData.id;
                block.appendChild(idIn);
            }

            // Textarea
            const ta = document.createElement('textarea');
            ta.className = 'form-control form-control-sm skill-notes';
            ta.rows = 2;
            ta.placeholder = `Activité ${skillLabel}...`;
            ta.value = skillData.notes || '';
            ta.style.minHeight = '50px';
            block.appendChild(ta);

            // Existing PDF + checkbox
            if (skillData.attachment_url) {
                const pdfBlock = document.createElement('div');
                pdfBlock.className = 'existing-pdf mt-1';
                pdfBlock.style.fontSize = '.7rem';
                const pdfLink = document.createElement('a');
                pdfLink.href = skillData.attachment_url;
                pdfLink.target = '_blank';
                pdfLink.rel = 'noopener';
                pdfLink.innerHTML = '<i class="ph-duotone ph-file-pdf" style="color:#d9534f;"></i> ' + (skillData.attachment_name || 'PDF');
                pdfBlock.appendChild(pdfLink);
                const removeChk = document.createElement('label');
                removeChk.style.cssText = 'margin-left:8px; font-size:.7rem; cursor:pointer;';
                const removeIn = document.createElement('input');
                removeIn.type = 'checkbox';
                removeIn.className = 'skill-remove-attachment';
                removeIn.style.marginRight = '3px';
                removeChk.appendChild(removeIn);
                removeChk.appendChild(document.createTextNode('Supprimer'));
                pdfBlock.appendChild(removeChk);
                block.appendChild(pdfBlock);
            }

            // File input
            const fileIn = document.createElement('input');
            fileIn.type = 'file';
            fileIn.className = 'form-control form-control-sm skill-attachment mt-1';
            fileIn.accept = 'application/pdf';
            fileIn.style.fontSize = '.72rem';
            block.appendChild(fileIn);

            skillsWrap.appendChild(block);
        });

        return row;
    }

    function refreshRowNumbers() {
        const rows = rowsContainer.querySelectorAll('.note-row');
        rows.forEach((row, i) => {
            const label = row.querySelector('.row-number');
            if (label) {
                const prefix = row.classList.contains('mode-skills') ? 'Groupe' : 'Note';
                label.textContent = `${prefix} #${i + 1}`;
            }
        });
        emptyHint.style.display = rows.length === 0 ? 'block' : 'none';
        const footer = document.getElementById('addRowFooter');
        if (footer) footer.style.display = rows.length > 0 ? 'block' : 'none';
        const counter = document.getElementById('rowsCountLabel');
        if (counter) counter.textContent = `${rows.length} entrée${rows.length > 1 ? 's' : ''}`;
    }

    function addRow(report) {
        const newRow = buildRow(report);
        rowsContainer.appendChild(newRow);
        refreshRowNumbers();

        // Scroll the modal body to the new row (only when user clicks "Ajouter")
        if (!report) {
            requestAnimationFrame(() => {
                const modalBody = document.querySelector('#reportModal .modal-body');
                if (modalBody) {
                    modalBody.scrollTo({
                        top: modalBody.scrollHeight,
                        behavior: 'smooth',
                    });
                }
                const firstSelect = newRow.querySelector('select.teacher-select');
                if (firstSelect) firstSelect.focus({ preventScroll: true });
            });
        }
    }

    function removeRow(btn) {
        const row = btn.closest('.note-row');
        if (row) row.remove();
        refreshRowNumbers();
    }

    // ==================== Eye button → navigate to show page ====================
    const SHOW_URL = '{{ route('backoffice.weekly_reports.show') }}';

    function openDetailFromRow(rowEl) {
        const date = document.getElementById('modalDate').value;
        const teacherSel = rowEl.querySelector('select.teacher-select');
        const groupSel = rowEl.querySelector('select.group-select');
        const teacherId = teacherSel ? teacherSel.value : '';
        const groupId = groupSel ? groupSel.value : '';

        if (!teacherId) {
            alert('Veuillez sélectionner un enseignant.');
            return;
        }

        const params = new URLSearchParams({ date, teacher_id: teacherId });
        if (groupId) params.set('group_id', groupId);
        window.open(`${SHOW_URL}?${params.toString()}`, '_blank');
    }

    // Before submission, expand each skills row into N rows (one per non-empty skill block)
    // so the backend's `rows[i][skill]` validation receives them in the expected shape.
    document.getElementById('reportForm').addEventListener('submit', function (ev) {
        const form = this;
        // Remove any name attributes added in a previous submit attempt
        form.querySelectorAll('[data-skill-flat="1"]').forEach(el => el.remove());

        let flatIdx = 0;

        // Renumber simple rows starting at 0
        form.querySelectorAll('.note-row.mode-simple').forEach(row => {
            const inputs = row.querySelectorAll('[name^="rows["]');
            inputs.forEach(inp => {
                inp.name = inp.name.replace(/^rows\[\d+\]/, `rows[${flatIdx}]`);
            });
            flatIdx++;
        });

        // Expand skill rows
        form.querySelectorAll('.note-row.mode-skills').forEach(row => {
            const teacherId = row.querySelector('select.teacher-select')?.value || '';
            const groupId = row.querySelector('select.group-select')?.value || '';

            row.querySelectorAll('.skill-block').forEach(block => {
                const skillKey = block.dataset.skill;
                const notes = block.querySelector('.skill-notes')?.value?.trim() || '';
                const idInput = block.querySelector('.skill-id-input');
                const fileInput = block.querySelector('.skill-attachment');
                const removeChk = block.querySelector('.skill-remove-attachment');
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                const wantsRemove = removeChk && removeChk.checked;
                const existingId = idInput ? idInput.value : '';

                // Skip empty skills with no existing record (nothing to save / nothing to delete)
                if (!notes && !hasFile && !existingId) return;

                // Build flat hidden inputs that batchSync expects
                const append = (name, value) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.dataset.skillFlat = '1';
                    inp.name = name;
                    inp.value = value;
                    form.appendChild(inp);
                };

                if (existingId) append(`rows[${flatIdx}][id]`, existingId);
                append(`rows[${flatIdx}][teacher_id]`, teacherId);
                append(`rows[${flatIdx}][group_id]`, groupId);
                append(`rows[${flatIdx}][skill]`, skillKey);
                append(`rows[${flatIdx}][notes]`, notes);
                if (wantsRemove) append(`rows[${flatIdx}][remove_attachment]`, '1');

                if (hasFile) {
                    // File inputs must stay in place (can't be cloned). Rename them in-place.
                    fileInput.dataset.skillFlat = '1';
                    fileInput.name = `rows[${flatIdx}][attachment]`;
                }

                flatIdx++;
            });
        });
    });

    async function openDayModal(date, label) {
        rowCounter = 0;
        rowsContainer.innerHTML = '';
        document.getElementById('modalDate').value = date;
        document.getElementById('modalDateLabel').textContent = label;
        emptyHint.style.display = 'block';
        emptyHint.classList.add('is-loading');
        emptyHint.innerHTML = '<span class="loading-wrap"><span class="loading-spinner"></span> Chargement…</span>';
        reportModal.show();

        try {
            const res = await fetch(`${FOR_DAY_URL}?date=${encodeURIComponent(date)}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (res.ok) {
                const json = await res.json();
                const reports = json.reports || [];

                // Group reports by (teacher_id, group_id). For multi-group teachers,
                // a single row should contain all 5 skills. For single-group / no-group,
                // each report becomes its own simple row.
                const buckets = new Map();
                const simpleReports = [];

                for (const r of reports) {
                    const isMulti = teacherHasMultipleGroups(r.teacher_id);
                    if (isMulti && r.skill) {
                        const key = `${r.teacher_id}::${r.group_id || ''}`;
                        if (!buckets.has(key)) {
                            buckets.set(key, {
                                teacher_id: r.teacher_id,
                                group_id: r.group_id,
                                skills: {},
                            });
                        }
                        buckets.get(key).skills[r.skill] = {
                            id: r.id,
                            notes: r.notes,
                            attachment_url: r.attachment_url,
                            attachment_name: r.attachment_name,
                        };
                    } else {
                        simpleReports.push(r);
                    }
                }

                // Render skills rows first (one per teacher+group bucket), then simple rows
                for (const bucket of buckets.values()) {
                    rowsContainer.appendChild(buildSkillsRow(bucket));
                }
                for (const r of simpleReports) {
                    rowsContainer.appendChild(buildSimpleRow(r));
                }
                refreshRowNumbers();
            }
        } catch (e) { /* ignore */ }

        emptyHint.classList.remove('is-loading');
        emptyHint.innerHTML = 'Aucune note pour ce jour.<br>Cliquez sur <strong>« Ajouter une note »</strong> pour commencer.';
        if (rowsContainer.children.length === 0) addRow();
        refreshRowNumbers();
    }

    // ==================== Month Modal ====================
    const monthModal = new bootstrap.Modal(document.getElementById('monthModal'));
    const EVENTS_URL = '{{ route('backoffice.weekly_reports.events') }}';
    const WEEK_INDEX_URL = '{{ route('backoffice.weekly_reports.index') }}';
    const MONTH_NAMES = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const DAY_HEADS = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    let monthCursor = new Date({{ $date->year }}, {{ $date->month - 1 }}, 1);

    function fmtDate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function openMonthModal() {
        monthCursor = new Date({{ $date->year }}, {{ $date->month - 1 }}, 1);
        renderMonth();
        monthModal.show();
    }

    function shiftMonth(delta) {
        monthCursor = new Date(monthCursor.getFullYear(), monthCursor.getMonth() + delta, 1);
        renderMonth();
    }

    function goToMonth(d) {
        monthCursor = new Date(d.getFullYear(), d.getMonth(), 1);
        renderMonth();
    }

    async function renderMonth() {
        const year = monthCursor.getFullYear();
        const month = monthCursor.getMonth();
        document.getElementById('monthLabel').textContent = `${MONTH_NAMES[month]} ${year}`;

        const firstOfMonth = new Date(year, month, 1);
        const lastOfMonth = new Date(year, month + 1, 0);

        // Grid starts on Monday of the week containing day 1
        const startOffset = (firstOfMonth.getDay() + 6) % 7; // Mon=0 .. Sun=6
        const gridStart = new Date(year, month, 1 - startOffset);

        // 6 rows * 7 cols = 42 cells
        const cells = [];
        for (let i = 0; i < 42; i++) {
            const d = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + i);
            cells.push(d);
        }

        const grid = document.getElementById('monthGrid');
        const loading = document.getElementById('monthLoading');
        loading.classList.remove('d-none');
        grid.innerHTML = '';

        // Fetch reports for grid range
        let reports = [];
        try {
            const res = await fetch(`${EVENTS_URL}?start=${fmtDate(cells[0])}&end=${fmtDate(cells[41])}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (res.ok) reports = await res.json();
        } catch (e) { /* ignore */ }

        const byDate = {};
        for (const r of reports) {
            (byDate[r.report_date] ||= []).push(r);
        }

        const todayStr = fmtDate(new Date());
        let html = '';
        for (const h of DAY_HEADS) html += `<div class="mg-head">${h}</div>`;

        for (const d of cells) {
            const key = fmtDate(d);
            const isOther = d.getMonth() !== month;
            const isToday = key === todayStr;
            const dow = d.getDay(); // 0 Sun .. 6 Sat
            const isWeekend = dow === 0 || dow === 6;
            const list = byDate[key] || [];

            const classes = ['mg-cell'];
            if (isOther) classes.push('other-month');
            if (isToday) classes.push('today');
            if (isWeekend) classes.push('weekend');

            let chipsHtml = '';
            const max = 3;
            for (let i = 0; i < Math.min(list.length, max); i++) {
                const r = list[i];
                const pdfMark = r.attachment_url ? ' 📎' : '';
                chipsHtml += `<div class="mg-chip" title="${escapeHtml(r.teacher_name)} — ${escapeHtml(r.notes)}">`
                    + `<span class="tn">${escapeHtml(r.teacher_name)}</span> `
                    + `<span>${escapeHtml(r.notes)}${pdfMark}</span></div>`;
            }
            if (list.length > max) {
                chipsHtml += `<div class="mg-more">+${list.length - max} autre${list.length - max > 1 ? 's' : ''}</div>`;
            }
            const dotsHtml = list.length > 0
                ? `<span class="mg-dot"></span><span class="mg-more" style="font-size:.68rem;">${list.length}</span>`
                : '';

            html += `<div class="${classes.join(' ')}" data-date="${key}" onclick="goToWeek('${key}')" style="cursor:pointer;">`
                + `<div class="mg-day">${d.getDate()} ${dotsHtml}</div>`
                + chipsHtml
                + `</div>`;
        }

        grid.innerHTML = html;
        loading.classList.add('d-none');
    }

    function goToWeek(dateStr) {
        window.location.href = `${WEEK_INDEX_URL}?week=${dateStr}`;
    }

    // Toast auto-show
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('liveToast');
        if (toastEl) new bootstrap.Toast(toastEl).show();
    });
</script>
@endsection
