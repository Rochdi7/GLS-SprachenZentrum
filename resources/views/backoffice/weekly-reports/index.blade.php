@extends('layouts.main')

@section('title', 'Rapport Semaine')
@section('breadcrumb-item', 'Pilotage')
@section('breadcrumb-item-active', 'Rapport Semaine')

@section('css')
<style>
    /* ===========================
       PAGE WRAPPER
    =========================== */
    .wr-page { padding: 0 0 32px; }

    /* ===========================
       HERO HEADER
    =========================== */
    .wr-hero {
        background: linear-gradient(135deg, #1a2f5e 0%, #2d4f9a 55%, #4680ff 100%);
        border-radius: 16px;
        padding: 28px 32px 24px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 8px 32px rgba(70,128,255,.22);
        position: relative;
        overflow: hidden;
    }
    .wr-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        pointer-events: none;
    }
    .wr-hero .hero-left { flex: 1; min-width: 0; position: relative; }
    .wr-hero .hero-eyebrow {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: rgba(255,255,255,.6);
        margin-bottom: 4px;
    }
    .wr-hero h1 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        letter-spacing: -.3px;
    }
    .wr-hero h1 .week-range-hero {
        display: block;
        font-size: 1rem;
        font-weight: 500;
        color: rgba(255,255,255,.8);
        margin-top: 4px;
        letter-spacing: 0;
    }
    .wr-hero .hero-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        position: relative;
    }
    .wr-hero .btn-hero-pdf {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255,255,255,.15);
        color: #fff;
        border: 1.5px solid rgba(255,255,255,.3);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        backdrop-filter: blur(8px);
        transition: background .15s, border-color .15s;
        white-space: nowrap;
    }
    .wr-hero .btn-hero-pdf:hover {
        background: rgba(255,255,255,.25);
        border-color: rgba(255,255,255,.5);
        color: #fff;
        text-decoration: none;
    }

    /* ===========================
       WEEK NAV BAR
    =========================== */
    .wr-nav-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .wr-nav-bar .nav-week-label {
        font-size: .95rem;
        font-weight: 700;
        color: #1a2f5e;
        min-width: 200px;
        text-align: center;
        padding: 0 8px;
    }
    .wr-nav-bar .btn-nav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        background: #f4f6fb;
        color: #4a5568;
        border: 1px solid #e2e8f0;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s, border-color .15s, color .15s;
        white-space: nowrap;
        cursor: pointer;
    }
    .wr-nav-bar .btn-nav:hover { background: #e8f0fe; border-color: #c2d3fd; color: #1a3a6e; }
    .wr-nav-bar .btn-nav.today-btn {
        background: #4680ff;
        color: #fff;
        border-color: #4680ff;
    }
    .wr-nav-bar .btn-nav.today-btn:hover { background: #3a6fd6; border-color: #3a6fd6; }
    .wr-nav-bar .btn-calendar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: #f4f6fb;
        color: #4a5568;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: background .15s, border-color .15s;
    }
    .wr-nav-bar .btn-calendar-icon:hover { background: #e8f0fe; border-color: #c2d3fd; color: #4680ff; }

    /* ===========================
       CALENDAR TABLE
    =========================== */
    .calendar-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 14px; }
    .week-calendar {
        width: 100%;
        min-width: 700px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,.06);
    }
    .week-calendar th {
        background: linear-gradient(180deg, #2d4f9a 0%, #4680ff 100%);
        color: #fff;
        text-align: center;
        padding: 13px 8px;
        font-size: 0.8rem;
        font-weight: 700;
        width: 20%;
        letter-spacing: .3px;
        text-transform: uppercase;
    }
    .week-calendar th:first-child { border-radius: 14px 0 0 0; }
    .week-calendar th:last-child  { border-radius: 0 14px 0 0; }
    .week-calendar th .th-date { font-size: 1.1rem; font-weight: 800; display: block; margin-top: 2px; letter-spacing: 0; text-transform: none; opacity: .85; }

    .week-calendar td {
        border: 1px solid #edf0f5;
        vertical-align: top;
        padding: 10px;
        min-height: 160px;
        height: 160px;
        width: 20%;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        position: relative;
        background: #fff;
    }
    .week-calendar td:hover { background: #f5f8ff; border-color: #c2d3fd; }
    .week-calendar td.today {
        background: linear-gradient(180deg, #f0f6ff 0%, #e8f0fe 100%);
        border-color: #4680ff;
    }
    .week-calendar tbody tr td:first-child { border-left: none; }
    .week-calendar tbody tr td:last-child { border-right: none; }
    .week-calendar tbody tr:last-child td:first-child { border-radius: 0 0 0 14px; }
    .week-calendar tbody tr:last-child td:last-child  { border-radius: 0 0 14px 0; }

    .day-number {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 8px;
        color: #2d3748;
        line-height: 1;
    }
    .today .day-number {
        color: #4680ff;
        background: #4680ff;
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }

    .btn-add-day {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        border: none;
        background: #4680ff;
        color: #fff;
        font-size: 15px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s, transform .1s;
        box-shadow: 0 2px 6px rgba(70,128,255,.4);
    }
    .week-calendar td:hover .btn-add-day { opacity: 1; }
    .btn-add-day:hover { transform: scale(1.1); }

    /* ===========================
       WEEK BANNER (spans all 5 cols)
    =========================== */
    .week-banner-row td {
        background: linear-gradient(90deg, #f0f4ff, #e8f0fe);
        border: none;
        border-bottom: 2px solid #c2d3fd;
        padding: 8px 14px;
        text-align: center;
        cursor: pointer;
        height: auto !important;
        min-height: auto !important;
    }
    .week-banner-row td:hover { background: linear-gradient(90deg, #e0ebff, #d4e4fd); }
    .week-banner-inner {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .8rem;
        font-weight: 700;
        color: #1a3a6e;
        letter-spacing: .2px;
    }
    .week-banner-inner i { font-size: 1rem; color: #4680ff; }
    .week-banner-inner .banner-hint {
        font-weight: 400;
        color: #6c8ebf;
        font-size: .72rem;
        margin-left: 4px;
    }

    /* ===========================
       REPORT CHIP
    =========================== */
    .report-chip {
        display: flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, #e8f0fe, #dce8fd);
        border-left: 3px solid #4680ff;
        border-radius: 6px;
        padding: 5px 8px;
        margin-bottom: 4px;
        font-size: 0.75rem;
        line-height: 1.3;
        cursor: pointer;
        transition: background .15s, transform .1s, box-shadow .1s;
        box-shadow: 0 1px 3px rgba(70,128,255,.08);
    }
    .report-chip:hover {
        background: linear-gradient(135deg, #d4e4fd, #c8dcfc);
        transform: translateX(2px);
        box-shadow: 0 2px 6px rgba(70,128,255,.15);
    }
    .report-chip .teacher-name {
        font-weight: 700;
        color: #1a3a6e;
        white-space: nowrap;
        flex-shrink: 0;
        font-size: .73rem;
    }
    .report-chip .notes-preview {
        color: #4a5568;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
        flex: 1;
    }
    .report-chip .pdf-icon { color: #e53e3e; flex-shrink: 0; font-size: .9rem; }
    .report-chip .group-badge {
        background: #fefce8;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: .65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ===========================
       ATTACHMENT EXISTING
    =========================== */
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
    .attachment-existing a { color: #4680ff; text-decoration: none; font-weight: 500; word-break: break-all; flex: 1; min-width: 0; }
    .attachment-existing a:hover { text-decoration: underline; }

    /* ===========================
       MODAL
    =========================== */
    #reportModal .modal-dialog { max-height: calc(100vh - 1rem); }
    #reportModal .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
    }
    #reportModal #reportForm { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden; }
    #reportModal .modal-header,
    #reportModal .modal-footer { flex: 0 0 auto; }
    #reportModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        min-height: 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        background: #f8fafc;
    }
    #reportModal .modal-header {
        padding: 0;
        border-bottom: none;
        align-items: stretch;
        background: linear-gradient(135deg, #1a2f5e 0%, #3560c4 100%);
        border-radius: 16px 16px 0 0;
    }
    #reportModal .modal-header-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        width: 100%;
        padding: 20px 22px 18px;
        gap: 12px;
    }
    #reportModal .modal-header .btn-close {
        filter: invert(1) brightness(2);
        opacity: .7;
        margin: 0;
        padding: 6px;
        flex-shrink: 0;
        align-self: flex-start;
    }
    #reportModal .modal-header .btn-close:hover { opacity: 1; }
    #reportModal .modal-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 6px;
        letter-spacing: -.2px;
    }
    #reportModal .modal-week-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.25);
        color: #fff;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 0.78rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }
    #reportModal .modal-week-hint {
        font-size: .7rem;
        color: rgba(255,255,255,.6);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    #reportModal .modal-footer {
        background: #fff;
        border-top: 1px solid #e8ecf1;
        padding: 12px 20px;
        border-radius: 0 0 16px 16px;
    }

    /* ===========================
       NOTE ROW (MODAL)
    =========================== */
    .note-row {
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 16px 14px;
        margin-bottom: 14px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        transition: border-color .15s, box-shadow .15s;
    }
    .note-row:hover { border-color: #a5b4fc; box-shadow: 0 4px 12px rgba(70,128,255,.08); }
    .note-row .row-number {
        position: absolute;
        top: -10px;
        left: 14px;
        background: linear-gradient(135deg, #4680ff, #2d4f9a);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 800;
        padding: 2px 10px;
        border-radius: 10px;
        letter-spacing: .4px;
        text-transform: uppercase;
        box-shadow: 0 2px 6px rgba(70,128,255,.35);
    }
    .note-row .btn-remove-row {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: none;
        background: #f8f9fa;
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
        right: 40px;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: none;
        background: #f8f9fa;
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
    .note-row .form-label { font-size: 0.78rem; font-weight: 700; margin-bottom: 4px; color: #374151; }
    .note-row .form-control, .note-row .form-select { font-size: 0.84rem; border-radius: 8px; }
    .note-row textarea { resize: vertical; min-height: 70px; }

    /* ===========================
       SKILLS GRID
    =========================== */
    .skills-grid { display: flex; flex-direction: column; gap: 8px; }
    .skill-block {
        display: grid;
        grid-template-columns: 110px 1fr;
        gap: 10px;
        align-items: start;
    }
    .skill-block .skill-label {
        background: linear-gradient(135deg, #1a2f5e 0%, #2d4f9a 100%);
        color: #fff;
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .6px;
        padding: 6px 8px;
        border-radius: 8px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        box-shadow: 0 2px 6px rgba(29,47,94,.25);
    }
    .skill-block > *:not(.skill-label) { grid-column: 2; }
    .skill-block .skill-notes, .skill-block .skill-attachment { font-size: .78rem; }
    .skill-block .existing-pdf { padding: 3px 6px; background: #fff; border: 1px solid #e9ecef; border-radius: 4px; }
    .mode-skills .row-number { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .skill-attachments-list { display: flex; flex-direction: column; gap: 2px; }
    .skill-attachment-row a:hover { text-decoration: underline; }
    @media (max-width: 575.98px) {
        .skill-block { grid-template-columns: 76px 1fr; gap: 6px; }
        .skill-block .skill-label { font-size: .62rem; padding: 4px 5px; min-height: 44px; }
    }
    .note-row .file-hint { font-size: 0.68rem; color: #9ca3af; margin-top: 2px; display: block; }
    .note-row .existing-pdf {
        display: flex; align-items: center; gap: 6px; font-size: 0.76rem;
        margin-bottom: 5px; padding: 5px 9px;
        background: #fff; border: 1px solid #e9ecef; border-radius: 6px;
    }
    .note-row .existing-pdf a { color: #4680ff; text-decoration: none; word-break: break-all; flex: 1; min-width: 0; }
    .note-row .existing-pdf a:hover { text-decoration: underline; }

    /* ===========================
       MODAL BODY TOOLBAR
    =========================== */
    .modal-body-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 18px 10px;
        background: #fff;
        border-bottom: 1px solid #e8ecf1;
        margin-bottom: 16px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .modal-body-toolbar .count-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0f4ff;
        color: #1a3a6e;
        border: 1px solid #c2d3fd;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: .78rem;
        font-weight: 600;
    }

    .btn-add-row {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #4680ff, #2d5ce6);
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .15s, transform .1s;
        white-space: nowrap;
        box-shadow: 0 3px 10px rgba(70,128,255,.3);
    }
    .btn-add-row:hover { opacity: .9; }
    .btn-add-row:active { transform: translateY(1px); }
    .btn-add-row i { font-size: 0.95rem; }

    .btn-add-row-ghost {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: transparent;
        color: #4680ff;
        border: 1.5px dashed #a5b4fc;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }
    .btn-add-row-ghost:hover { background: #eef3ff; border-color: #4680ff; }
    .btn-add-row-ghost i { font-size: 0.95rem; }

    #emptyRowsHint {
        text-align: center;
        color: #9ca3af;
        font-style: italic;
        padding: 40px 20px;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        margin: 0 4px;
        background: #fff;
    }
    #emptyRowsHint.is-loading { border-style: solid; border-color: #c2d3fd; background: #f8faff; }
    #emptyRowsHint.is-loading .loading-wrap {
        display: inline-flex; align-items: center; gap: 10px;
        font-style: normal; color: #4680ff; font-weight: 600;
    }
    .loading-spinner {
        width: 22px; height: 22px;
        border: 3px solid #e0e7ff;
        border-top-color: #4680ff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 575.98px) {
        .btn-add-row { padding: 7px 12px; font-size: 0.78rem; }
        .note-row { padding: 16px 12px 12px; }
    }

    /* ===========================
       MOBILE: STACKED CARDS
    =========================== */
    .mobile-days { display: none; }

    /* Mobile week edit banner */
    .mobile-week-banner {
        background: linear-gradient(135deg, #1a2f5e 0%, #4680ff 100%);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(70,128,255,.25);
    }
    .mobile-week-banner .mwb-label { color: #fff; font-weight: 700; font-size: .92rem; }
    .mobile-week-banner .mwb-sub { color: rgba(255,255,255,.7); font-size: .74rem; margin-top: 2px; }
    .mobile-week-banner .mwb-btn {
        background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.35);
        color: #fff;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .day-card {
        border: 1px solid #e8ecf1;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 10px;
        cursor: pointer;
        transition: box-shadow .15s, border-color .15s;
        background: #fff;
    }
    .day-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.07); }
    .day-card:active { box-shadow: 0 0 0 3px rgba(70,128,255,.2); }
    .day-card.today { border-color: #4680ff; border-left-width: 3px; }

    .day-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e8ecf1;
    }
    .day-card.today .day-card-header { background: #edf3ff; }
    .day-card-header .day-label { font-weight: 700; font-size: .92rem; color: #2d3748; }
    .day-card.today .day-card-header .day-label { color: #4680ff; }
    .day-card-header .btn-add-mobile {
        width: 28px; height: 28px;
        border-radius: 7px; border: none;
        background: #4680ff; color: #fff;
        font-size: 17px;
        display: flex; align-items: center; justify-content: center;
    }
    .day-card-body { padding: 10px 14px; min-height: 48px; }
    .day-card-body .report-chip { font-size: 0.82rem; padding: 7px 10px; }
    .day-card-body .report-chip .notes-preview { max-width: none; white-space: normal; }
    .day-card-body .empty-label { color: #9ca3af; font-size: .8rem; font-style: italic; }

    /* ===========================
       RESPONSIVE
    =========================== */
    @media (max-width: 991.98px) {
        .desktop-calendar { display: none !important; }
        .mobile-days { display: block !important; }
        .wr-hero { padding: 20px 20px 18px; }
        .wr-hero h1 { font-size: 1.25rem; }
    }
    @media (min-width: 992px) {
        .desktop-calendar { display: block; }
        .mobile-days { display: none; }
    }
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .week-calendar td { padding: 7px; height: 140px; }
        .report-chip .notes-preview { max-width: 80px; }
        .week-calendar th { font-size: 0.73rem; padding: 10px 4px; }
    }
    @media (min-width: 1200px) {
        .report-chip .notes-preview { max-width: 130px; }
    }

    /* ===========================
       WEEK CARD (main view)
    =========================== */
    .week-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 16px rgba(0,0,0,.06);
        overflow: hidden;
        cursor: pointer;
        transition: box-shadow .18s, border-color .18s;
    }
    .week-card:hover { box-shadow: 0 6px 28px rgba(70,128,255,.13); border-color: #a5b4fc; }
    .week-card.has-today { border-color: #4680ff; border-width: 2px; }

    /* Day header strip */
    .wc-header {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        background: linear-gradient(135deg, #1a2f5e 0%, #3560c4 60%, #4680ff 100%);
    }
    .wc-day-head {
        text-align: center;
        padding: 14px 8px 12px;
        border-right: 1px solid rgba(255,255,255,.1);
        transition: background .15s;
    }
    .wc-day-head:last-child { border-right: none; }
    .wc-day-head.wc-today { background: rgba(255,255,255,.15); }
    .wc-day-head .wc-dname {
        display: block;
        font-size: .7rem;
        font-weight: 700;
        color: rgba(255,255,255,.65);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 3px;
    }
    .wc-day-head .wc-dnum {
        display: block;
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.3px;
    }
    .wc-day-head.wc-today .wc-dnum {
        background: rgba(255,255,255,.25);
        border-radius: 8px;
        padding: 2px 10px;
        display: inline-block;
    }

    /* Body */
    .wc-body {
        padding: 22px 24px 20px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
    }
    .wc-empty {
        flex: 1;
        color: #9ca3af;
        font-size: .88rem;
        font-style: italic;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
    }
    .wc-empty i { font-size: 1.2rem; color: #c7d2fe; }

    /* Chips grid */
    .wc-chips {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-content: flex-start;
    }
    .wc-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #e8f0fe, #dce8fd);
        border: 1px solid #c2d3fd;
        border-radius: 20px;
        padding: 6px 12px 6px 8px;
        font-size: .8rem;
        cursor: pointer;
        transition: background .15s, transform .1s, box-shadow .1s;
        box-shadow: 0 1px 3px rgba(70,128,255,.08);
    }
    .wc-chip:hover {
        background: linear-gradient(135deg, #c7d7fd, #b8ccfc);
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(70,128,255,.18);
    }
    .wc-chip-icon { font-size: 1rem; color: #4680ff; flex-shrink: 0; }
    .wc-chip-name { font-weight: 700; color: #1a3a6e; }
    .wc-chip-badge {
        background: #fef9c3;
        color: #854d0e;
        border: 1px solid #fde68a;
        font-size: .68rem;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 10px;
        white-space: nowrap;
    }
    .wc-chip-pdf { color: #ef4444; font-size: .9rem; flex-shrink: 0; }

    /* Edit CTA */
    .wc-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(135deg, #4680ff, #2d5ce6);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: .84rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(70,128,255,.3);
        transition: opacity .15s, transform .1s;
        flex-shrink: 0;
        align-self: center;
    }
    .wc-edit-btn:hover { opacity: .9; transform: translateY(-1px); }
    .wc-edit-btn i { font-size: 1rem; }

    @media (max-width: 767.98px) {
        .wc-body { padding: 16px; gap: 14px; }
        .wc-edit-btn { width: 100%; justify-content: center; }
        .wc-day-head { padding: 10px 4px; }
        .wc-day-head .wc-dnum { font-size: .85rem; }
    }

    /* ===========================
       MONTH MODAL — WEEK LIST VIEW
       One row per week (Mon–Fri only)
    =========================== */
    .month-nav {
        display: flex; align-items: center; justify-content: center;
        gap: 10px; margin-bottom: 16px; flex-wrap: wrap;
    }
    .month-nav .month-label {
        font-size: 1rem; font-weight: 700; min-width: 180px;
        text-align: center; text-transform: capitalize; color: #1a2f5e;
    }

    /* Column header row */
    .week-list-header {
        display: grid;
        grid-template-columns: 56px 1fr 1fr 1fr 1fr 1fr;
        gap: 4px;
        margin-bottom: 4px;
    }
    .week-list-header .wlh-cell {
        background: linear-gradient(180deg, #2d4f9a, #4680ff);
        color: #fff; text-align: center; padding: 7px 4px;
        font-size: 0.72rem; font-weight: 700; border-radius: 6px;
        text-transform: uppercase; letter-spacing: .4px;
    }
    .week-list-header .wlh-cell:first-child {
        background: #e8ecf1; color: #6c757d;
        font-size: .65rem;
    }

    /* Week rows */
    .week-list { display: flex; flex-direction: column; gap: 5px; }

    .week-row {
        display: grid;
        grid-template-columns: 56px 1fr 1fr 1fr 1fr 1fr;
        gap: 4px;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        transition: box-shadow .15s;
    }
    .week-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,.10); }

    /* Week number badge cell */
    .week-row .wr-num {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        border-radius: 8px;
        font-size: .65rem; font-weight: 800;
        letter-spacing: .3px; color: #fff;
        padding: 6px 4px;
        line-height: 1.2;
        text-align: center;
    }
    .week-row .wr-num .wr-s { font-size: .55rem; font-weight: 600; opacity: .8; }

    /* Day cells within a week row */
    .week-row .wr-day {
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 7px 8px;
        font-size: .72rem;
        min-height: 72px;
        position: relative;
        transition: border-color .12s, filter .12s;
    }
    .week-row:hover .wr-day { filter: brightness(.97); }
    .week-row .wr-day.wr-today {
        border-color: #4680ff !important;
        border-width: 2px !important;
        box-shadow: 0 0 0 2px rgba(70,128,255,.15);
    }
    .week-row .wr-day.wr-other { opacity: .45; }
    .week-row .wr-day .wr-date {
        font-weight: 800; font-size: .8rem; margin-bottom: 4px; color: #1e293b;
        display: flex; align-items: center; gap: 4px;
    }
    .week-row .wr-day.wr-today .wr-date { color: #4680ff; }
    .week-row .wr-day .wr-date .today-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: #4680ff; flex-shrink: 0;
    }
    .wr-chip {
        background: rgba(255,255,255,.55);
        border-left: 2px solid rgba(0,0,0,.12);
        padding: 2px 5px; margin-bottom: 2px; border-radius: 3px;
        font-size: .62rem; line-height: 1.25;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        font-weight: 600; color: rgba(0,0,0,.65);
    }
    .wr-chip .wr-tn { color: rgba(0,0,0,.8); }
    .wr-more { font-size: .6rem; font-weight: 700; color: rgba(0,0,0,.4); margin-top: 1px; }

    /* Per-week color palettes — bg for day cells + accent for num badge */
    .week-row.wk-0 .wr-day  { background: #eef2ff; border-color: #c7d4fe; }
    .week-row.wk-0 .wr-num  { background: linear-gradient(135deg,#4680ff,#6366f1); }
    .week-row.wk-1 .wr-day  { background: #f0fdf4; border-color: #bbf7d0; }
    .week-row.wk-1 .wr-num  { background: linear-gradient(135deg,#16a34a,#22c55e); }
    .week-row.wk-2 .wr-day  { background: #fefce8; border-color: #fde68a; }
    .week-row.wk-2 .wr-num  { background: linear-gradient(135deg,#d97706,#fbbf24); }
    .week-row.wk-3 .wr-day  { background: #fff1f2; border-color: #fecdd3; }
    .week-row.wk-3 .wr-num  { background: linear-gradient(135deg,#e11d48,#f43f5e); }
    .week-row.wk-4 .wr-day  { background: #ecfeff; border-color: #a5f3fc; }
    .week-row.wk-4 .wr-num  { background: linear-gradient(135deg,#0891b2,#06b6d4); }
    .week-row.wk-5 .wr-day  { background: #faf5ff; border-color: #e9d5ff; }
    .week-row.wk-5 .wr-num  { background: linear-gradient(135deg,#7c3aed,#a855f7); }

    @media (max-width: 575.98px) {
        .week-list-header { grid-template-columns: 40px repeat(5, 1fr); }
        .week-row { grid-template-columns: 40px repeat(5, 1fr); }
        .week-row .wr-day { min-height: 54px; padding: 5px 4px; }
        .week-row .wr-num { font-size: .6rem; }
        .wr-chip { display: none; }
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

<div class="wr-page">
    <div class="col-12">

        {{-- ===== HERO ===== --}}
        <div class="wr-hero">
            <div class="hero-left">
                <div class="hero-eyebrow"><i class="ph-duotone ph-chalkboard-teacher me-1"></i> Pilotage · Enseignants</div>
                <h1>
                    Rapport Semaine
                    <span class="week-range-hero">
                        <i class="ph-duotone ph-calendar-blank" style="font-size:.85rem; margin-right:4px;"></i>
                        {{ $weekDays->first()->locale('fr')->isoFormat('D MMM') }} — {{ $weekDays->last()->locale('fr')->isoFormat('D MMM YYYY') }}
                    </span>
                </h1>
            </div>
            <div class="hero-right">
                <a href="{{ route('backoffice.weekly_reports.export_pdf', ['week' => $date->format('Y-m-d')]) }}"
                   class="btn-hero-pdf">
                    <i class="ph-duotone ph-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        {{-- ===== WEEK NAV BAR ===== --}}
        <div class="wr-nav-bar">
            <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->subWeek()->format('Y-m-d')]) }}"
               class="btn-nav">
                <i class="ph-duotone ph-caret-left"></i> Préc.
            </a>

            <span class="nav-week-label">
                {{ $weekDays->first()->locale('fr')->isoFormat('D MMM') }} — {{ $weekDays->last()->locale('fr')->isoFormat('D MMM YYYY') }}
            </span>

            <button type="button" class="btn-calendar-icon" title="Vue mensuelle" onclick="openMonthModal()">
                <i class="ph-duotone ph-calendar-blank"></i>
            </button>

            <a href="{{ route('backoffice.weekly_reports.index', ['week' => $date->copy()->addWeek()->format('Y-m-d')]) }}"
               class="btn-nav">
                Suiv. <i class="ph-duotone ph-caret-right"></i>
            </a>

            @if (!$date->isCurrentWeek())
                <a href="{{ route('backoffice.weekly_reports.index') }}" class="btn-nav today-btn">
                    <i class="ph-duotone ph-crosshair-simple"></i> Aujourd'hui
                </a>
            @endif
        </div>

        <div class="px-0">

                {{-- ===== WEEK CARD (single unified block) ===== --}}
                @php
                    $weekLabel = $weekDays->first()->locale('fr')->isoFormat('D MMM') . ' – ' . $weekDays->last()->locale('fr')->isoFormat('D MMM YYYY');
                    $fridayKey = $weekDays->last()->format('Y-m-d');
                    $allWeekReports = collect();
                    foreach ($weekDays as $wd) {
                        $allWeekReports = $allWeekReports->merge($reports[$wd->format('Y-m-d')] ?? collect());
                    }
                    $weekHasToday = $weekDays->contains(fn($d) => $d->isToday());
                @endphp

                <div class="week-card {{ $weekHasToday ? 'has-today' : '' }}"
                     onclick="openWeekModal('{{ $fridayKey }}', '{{ addslashes($weekLabel) }}', {})">

                    {{-- Day header strip --}}
                    <div class="wc-header">
                        @foreach ($weekDays as $day)
                            <div class="wc-day-head {{ $day->isToday() ? 'wc-today' : '' }}">
                                <span class="wc-dname">{{ $day->locale('fr')->isoFormat('ddd') }}</span>
                                <span class="wc-dnum">{{ $day->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Content area --}}
                    <div class="wc-body">
                        @if ($allWeekReports->isEmpty())
                            <div class="wc-empty">
                                <i class="ph-duotone ph-pencil-simple-line"></i>
                                Aucun rapport cette semaine — cliquez pour en ajouter
                            </div>
                        @else
                            {{-- Group chips by teacher+group --}}
                            @php
                                $grouped = $allWeekReports->groupBy(fn($r) => $r->teacher_id . '_' . ($r->group_id ?? 0));
                            @endphp
                            <div class="wc-chips">
                                @foreach ($grouped as $key => $groupReports)
                                    @php $rep = $groupReports->first(); @endphp
                                    <div class="wc-chip"
                                         onclick="event.stopPropagation(); openWeekModal('{{ $fridayKey }}', '{{ addslashes($weekLabel) }}', { teacherId: {{ $rep->teacher_id }}, groupId: {{ $rep->group_id ?? 'null' }} })">
                                        <i class="ph-duotone ph-user-circle wc-chip-icon"></i>
                                        <span class="wc-chip-name">{{ $rep->teacher->name }}</span>
                                        @if ($rep->group)
                                            <span class="wc-chip-badge">{{ $rep->group->name }}</span>
                                        @endif
                                        @if ($rep->attachment_path)
                                            <i class="ph-duotone ph-file-pdf wc-chip-pdf"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Edit CTA button --}}
                        <button class="wc-edit-btn"
                                onclick="event.stopPropagation(); openWeekModal('{{ $fridayKey }}', '{{ addslashes($weekLabel) }}', { freshOnly: true })">
                            <i class="ph-duotone ph-pencil-simple"></i>
                            {{ $allWeekReports->isEmpty() ? 'Saisir le rapport' : 'Modifier' }}
                        </button>
                    </div>
                </div>


        </div>{{-- /px-0 --}}
    </div>{{-- /col-12 --}}
</div>{{-- /wr-page --}}

{{-- ==================== MODAL: Day Reports (multi-row list) ==================== --}}
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <form id="reportForm" method="POST" action="{{ route('backoffice.weekly_reports.batch_sync') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="report_date" id="modalDate">

                <div class="modal-header">
                    <div class="modal-header-inner">
                        <div>
                            <div class="modal-title">
                                <i class="ph-duotone ph-calendar-check me-1"></i> Rapport de la Semaine
                            </div>
                            <div class="modal-week-badge">
                                <i class="ph-duotone ph-calendar-blank"></i>
                                <span id="modalDateLabel">—</span>
                            </div>
                            <div class="modal-week-hint">
                                <i class="ph-duotone ph-info"></i>
                                Les notes couvrent <strong style="color:rgba(255,255,255,.85)">toute la semaine</strong>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="padding:0;">
                    <div class="modal-body-toolbar">
                        <div class="count-pill">
                            <i class="ph-duotone ph-list-checks"></i>
                            <span id="rowsCountLabel">0 note(s)</span>
                        </div>
                        <button type="button" class="btn-add-row" onclick="addRow()">
                            <i class="ph-duotone ph-plus"></i> Ajouter une note
                        </button>
                    </div>

                    <div style="padding:16px 18px 8px;" id="rowsContainer"></div>

                    <div id="emptyRowsHint" style="margin:0 18px 18px;">
                        Aucune note pour cette semaine.<br>
                        Cliquez sur <strong>« Ajouter une note »</strong> pour commencer.
                    </div>

                    <div class="text-center mt-2 pb-3" id="addRowFooter" style="display:none;">
                        <button type="button" class="btn-add-row-ghost" onclick="addRow()">
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
                <div class="week-list-header" id="monthGridHeader">
                    <div class="wlh-cell">S#</div>
                    <div class="wlh-cell">Lun</div>
                    <div class="wlh-cell">Mar</div>
                    <div class="wlh-cell">Mer</div>
                    <div class="wlh-cell">Jeu</div>
                    <div class="wlh-cell">Ven</div>
                </div>
                <div class="week-list" id="monthGrid"></div>
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
    const FOR_DAY_URL  = '{{ route('backoffice.weekly_reports.for_day') }}';
    const FOR_WEEK_URL = '{{ route('backoffice.weekly_reports.for_week') }}';
    const TEACHER_GROUPS = @json($teacherGroupsMap);
    const SKILLS = @json(\App\Models\WeeklyReport::SKILLS);
    const rowsContainer = document.getElementById('rowsContainer');
    const emptyHint = document.getElementById('emptyRowsHint');
    let rowCounter = 0;

    // Event delegation: any change on a teacher-select inside the modal swaps modes.
    // This survives Choices.js wrapping and any other dynamic select handling.
    rowsContainer.addEventListener('change', function (ev) {
        if (ev.target && ev.target.classList && ev.target.classList.contains('teacher-select')) {
            onTeacherChange(ev.target);
        }
    });

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
        // Always use the 6-skill grid (Lesen, Hören, Grammatik, Schreiben, Sprechen, Activités).
        // The simple-notes mode is no longer used — every row is a structured skill grid.
        return true;
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
        colG.innerHTML = '<label class="form-label">Groupe (optionnel)</label>';
        const groupSel = document.createElement('select');
        groupSel.className = 'form-select form-select-sm group-select';
        groupSel.innerHTML = groupOptionsHtml(r.teacher_id, r.group_id);
        // Auto-select the only group when the teacher has exactly one
        const teacherGroups = TEACHER_GROUPS[r.teacher_id] || [];
        if (!r.group_id && teacherGroups.length === 1) {
            groupSel.value = String(teacherGroups[0].id);
        }
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

            // Existing attachments list (multi-file). Each row shows the file name + a
            // checkbox that, when ticked, marks the attachment for deletion on save.
            const attachments = Array.isArray(skillData.attachments) ? skillData.attachments : [];

            // Backward compat: if the row only has the legacy single-file fields
            // (skillData.attachment_url) and no attachments[] yet, surface it as one entry.
            if (attachments.length === 0 && skillData.attachment_url) {
                attachments.push({
                    id: null, // null = legacy single-file, removal goes through skill-remove-attachment
                    url: skillData.attachment_url,
                    name: skillData.attachment_name || 'PDF',
                    legacy: true,
                });
            }

            if (attachments.length > 0) {
                const list = document.createElement('div');
                list.className = 'skill-attachments-list mt-1';
                for (const att of attachments) {
                    const row = document.createElement('div');
                    row.className = 'skill-attachment-row';
                    row.style.cssText = 'display:flex; align-items:center; gap:6px; padding:3px 6px; background:#fff; border:1px solid #e9ecef; border-radius:3px; margin-bottom:3px; font-size:.7rem;';

                    const link = document.createElement('a');
                    link.href = att.url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.style.cssText = 'flex:1; min-width:0; word-break:break-all; color:#4680ff; text-decoration:none;';
                    link.innerHTML = '<i class="ph-duotone ph-file-pdf" style="color:#d9534f;"></i> ' + escapeAttr(att.name);
                    row.appendChild(link);

                    const removeWrap = document.createElement('label');
                    removeWrap.style.cssText = 'display:inline-flex; align-items:center; gap:3px; margin:0; font-size:.7rem; cursor:pointer; color:#b91c1c;';
                    const removeIn = document.createElement('input');
                    removeIn.type = 'checkbox';
                    if (att.legacy) {
                        // Legacy single-file: drives the old skill-remove-attachment path
                        removeIn.className = 'skill-remove-attachment';
                    } else {
                        removeIn.className = 'skill-remove-attachment-id';
                        removeIn.dataset.attachmentId = att.id;
                    }
                    removeIn.style.margin = '0';
                    removeWrap.appendChild(removeIn);
                    removeWrap.appendChild(document.createTextNode('Suppr.'));
                    row.appendChild(removeWrap);

                    list.appendChild(row);
                }
                block.appendChild(list);
            }

            // Multi-file input — user can pick several PDFs at once and pick again to add more.
            const fileIn = document.createElement('input');
            fileIn.type = 'file';
            fileIn.multiple = true;
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
        const friday = document.getElementById('modalDate').value; // Always the week's Friday
        const teacherSel = rowEl.querySelector('select.teacher-select');
        const groupSel   = rowEl.querySelector('select.group-select');
        const teacherId  = teacherSel ? teacherSel.value : '';
        const groupId    = groupSel   ? groupSel.value   : '';

        if (!teacherId) {
            alert('Veuillez sélectionner un enseignant.');
            return;
        }

        const params = new URLSearchParams({ week: friday, teacher_id: teacherId });
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
                const legacyRemoveChk = block.querySelector('.skill-remove-attachment');
                const removeIdChks = block.querySelectorAll('.skill-remove-attachment-id:checked');
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                const wantsLegacyRemove = legacyRemoveChk && legacyRemoveChk.checked;
                const wantsAnyAttachmentRemoval = wantsLegacyRemove || removeIdChks.length > 0;
                const existingId = idInput ? idInput.value : '';

                // Skip empty skills with no existing record (nothing to save / nothing to delete)
                if (!notes && !hasFile && !existingId && !wantsAnyAttachmentRemoval) return;

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
                if (wantsLegacyRemove) append(`rows[${flatIdx}][remove_attachment]`, '1');

                // Multi-file: per-attachment removal ids
                removeIdChks.forEach(chk => {
                    append(`rows[${flatIdx}][remove_attachment_ids][]`, chk.dataset.attachmentId);
                });

                if (hasFile) {
                    // File input must stay in place (can't be cloned). Rename it to the
                    // multi-file payload key — `multiple` already collects all selected PDFs.
                    fileInput.dataset.skillFlat = '1';
                    fileInput.name = `rows[${flatIdx}][attachments][]`;
                }

                flatIdx++;
            });
        });
    });

    /**
     * openWeekModal — opens the report modal for the whole week.
     * `fridayDate` is the Friday of that week (used as report_date for all new rows).
     * `weekLabel`  is the human-readable range shown in the modal header.
     */
    async function openWeekModal(fridayDate, weekLabel, opts) {
        const options = opts || {};
        const freshOnly = options.freshOnly === true;
        const filterTeacherId = options.teacherId !== undefined && options.teacherId !== null ? String(options.teacherId) : null;
        const filterGroupId   = options.groupId   !== undefined && options.groupId   !== null ? String(options.groupId)   : '';

        rowCounter = 0;
        rowsContainer.innerHTML = '';
        document.getElementById('modalDate').value = fridayDate;
        document.getElementById('modalDateLabel').textContent = weekLabel;
        emptyHint.style.display = 'block';

        if (freshOnly) {
            emptyHint.classList.remove('is-loading');
            emptyHint.innerHTML = 'Aucune note pour cette semaine.<br>Cliquez sur <strong>« Ajouter une note »</strong> pour commencer.';
            reportModal.show();
            addRow();
            refreshRowNumbers();
            return;
        }

        emptyHint.classList.add('is-loading');
        emptyHint.innerHTML = '<span class="loading-wrap"><span class="loading-spinner"></span> Chargement…</span>';
        reportModal.show();

        try {
            const res = await fetch(`${FOR_WEEK_URL}?week=${encodeURIComponent(fridayDate)}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (res.ok) {
                const json = await res.json();
                let reports = json.reports || [];

                if (filterTeacherId !== null) {
                    reports = reports.filter(r => {
                        if (String(r.teacher_id) !== filterTeacherId) return false;
                        const rg = r.group_id !== undefined && r.group_id !== null ? String(r.group_id) : '';
                        return rg === filterGroupId;
                    });
                }

                // Group by (teacher_id, group_id) — one card per bucket, skills merged across the week.
                const buckets = new Map();

                for (const r of reports) {
                    const key = `${r.teacher_id}::${r.group_id || ''}`;
                    if (!buckets.has(key)) {
                        buckets.set(key, {
                            teacher_id: r.teacher_id,
                            group_id:   r.group_id,
                            skills:     {},
                        });
                    }
                    const bucket  = buckets.get(key);
                    const skillKey = r.skill || 'aktivitaet';
                    if (bucket.skills[skillKey]) {
                        bucket.skills[skillKey].notes =
                            (bucket.skills[skillKey].notes || '') + '\n' + (r.notes || '');
                    } else {
                        bucket.skills[skillKey] = {
                            id:              r.id,
                            notes:           r.notes,
                            attachment_url:  r.attachment_url,
                            attachment_name: r.attachment_name,
                            attachments:     r.attachments || [],
                        };
                    }
                }

                for (const bucket of buckets.values()) {
                    rowsContainer.appendChild(buildSkillsRow(bucket));
                }
                refreshRowNumbers();
            }
        } catch (e) { /* ignore */ }

        emptyHint.classList.remove('is-loading');
        emptyHint.innerHTML = 'Aucune note pour cette semaine.<br>Cliquez sur <strong>« Ajouter une note »</strong> pour commencer.';
        if (rowsContainer.children.length === 0) addRow();
        refreshRowNumbers();
    }

    // Keep openDayModal as a legacy alias (month modal still uses it via goToWeek navigation)
    function openDayModal(date, label, opts) {
        openWeekModal(date, label, opts);
    }

    // ==================== Month Modal ====================
    const monthModal = new bootstrap.Modal(document.getElementById('monthModal'));
    const EVENTS_URL = '{{ route('backoffice.weekly_reports.events') }}';
    const WEEK_INDEX_URL = '{{ route('backoffice.weekly_reports.index') }}';
    const MONTH_NAMES = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    const DAY_HEADS = ['Lun','Mar','Mer','Jeu','Ven']; // Mon–Fri only (kept for reference)
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
        const year  = monthCursor.getFullYear();
        const month = monthCursor.getMonth();
        document.getElementById('monthLabel').textContent = `${MONTH_NAMES[month]} ${year}`;

        // Build Mon–Fri weeks that overlap with this month.
        // Start from the Monday of the week containing the 1st.
        const firstOfMonth = new Date(year, month, 1);
        const startOffset  = (firstOfMonth.getDay() + 6) % 7; // Mon=0
        const gridStart    = new Date(year, month, 1 - startOffset);

        // Collect all Mon–Fri weeks until we've passed the last day of the month.
        const lastOfMonth = new Date(year, month + 1, 0);
        const weeks = [];
        let cur = new Date(gridStart);
        while (cur <= lastOfMonth) {
            const monday = new Date(cur);
            const weekDays = [];
            for (let d = 0; d < 5; d++) {
                weekDays.push(new Date(cur.getFullYear(), cur.getMonth(), cur.getDate() + d));
            }
            weeks.push({ monday, weekDays });
            cur.setDate(cur.getDate() + 7);
        }

        const grid    = document.getElementById('monthGrid');
        const loading = document.getElementById('monthLoading');
        loading.classList.remove('d-none');
        grid.innerHTML = '';

        // Fetch reports for the full range
        const rangeStart = fmtDate(weeks[0].monday);
        const rangeEnd   = fmtDate(weeks[weeks.length - 1].weekDays[4]);
        let reports = [];
        try {
            const res = await fetch(`${EVENTS_URL}?start=${rangeStart}&end=${rangeEnd}`, {
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
        weeks.forEach(({ monday, weekDays }, rowIdx) => {
            // S1–S4/S5: week number relative to this month (1-based)
            const wkNum  = rowIdx + 1;
            const wkBand = rowIdx % 6;
            const fridayKey = fmtDate(weekDays[4]);
            const weekLabel = `${weekDays[0].getDate()} — ${weekDays[4].getDate()} ${MONTH_NAMES[weekDays[4].getMonth()]} ${weekDays[4].getFullYear()}`;

            // Collect all reports for this Mon–Fri range (aggregated, not per-day)
            const weekReports = [];
            for (const wd of weekDays) {
                const list = byDate[fmtDate(wd)] || [];
                for (const r of list) weekReports.push(r);
            }

            // Deduplicate by teacher+group
            const seen = new Set();
            const uniqueReports = weekReports.filter(r => {
                const k = `${r.teacher_id}::${r.group_id || ''}`;
                if (seen.has(k)) return false;
                seen.add(k); return true;
            });

            // Build day cells (Mon–Fri)
            let dayCells = '';
            for (const wd of weekDays) {
                const key     = fmtDate(wd);
                const isToday = key === todayStr;
                const isOther = wd.getMonth() !== month;
                const dayList = byDate[key] || [];

                const dayClasses = ['wr-day'];
                if (isToday) dayClasses.push('wr-today');
                if (isOther) dayClasses.push('wr-other');

                // Show up to 2 chips per day cell
                let chipsHtml = '';
                const max = 2;
                for (let i = 0; i < Math.min(dayList.length, max); i++) {
                    const r = dayList[i];
                    const nameShort = escapeHtml(r.teacher_name).split(' ')[0];
                    chipsHtml += `<div class="wr-chip"><span class="wr-tn">${nameShort}</span>${r.group_name ? ' · ' + escapeHtml(r.group_name) : ''}</div>`;
                }
                if (dayList.length > max) {
                    chipsHtml += `<div class="wr-more">+${dayList.length - max}</div>`;
                }

                const todayDot = isToday ? '<span class="today-dot"></span>' : '';
                dayCells += `<div class="${dayClasses.join(' ')}">
                    <div class="wr-date">${todayDot}${wd.getDate()}</div>
                    ${chipsHtml}
                </div>`;
            }

            // Summary chip count for the week badge
            const totalWk = uniqueReports.length;
            const countBadge = totalWk > 0
                ? `<span style="font-size:.55rem;background:rgba(255,255,255,.25);border-radius:6px;padding:1px 4px;margin-top:2px;">${totalWk} prof${totalWk > 1 ? 's' : ''}</span>`
                : '';

            html += `<div class="week-row wk-${wkBand}" onclick="goToWeek('${fridayKey}')" title="Semaine ${wkNum} — ${weekLabel}">
                <div class="wr-num">
                    <span class="wr-s">S</span>${wkNum}
                    ${countBadge}
                </div>
                ${dayCells}
            </div>`;
        });

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
