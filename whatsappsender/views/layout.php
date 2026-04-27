<?php /** @var string $title @var string $content */ ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'WhatsApp Sender') ?> · GLS</title>
    <link rel="icon" type="image/png" href="public/gls-round.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Satoshi (headings) via Fontshare — matches the rest of the GLS design family -->
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,600,700,900&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Brand */
            --gls-red:      #E4252B;
            --gls-yellow:   #FFCE00;
            --gls-black:    #111111;
            --wa-green:     #25D366;
            --wa-green-dk:  #128C7E;
            --wa-teal:      #075E54;

            /* Surfaces */
            --bg:           #f4f5f8;
            --surface:      #ffffff;
            --surface-alt:  #fafbfc;
            --ink:          #14181f;
            --muted:        #6b7280;
            --hairline:     #e9ebef;
            --hairline-soft:#f1f3f7;

            /* Design tokens */
            --radius-sm:    8px;
            --radius-md:    10px;
            --radius-lg:    14px;
            --shadow-xs:    0 1px 2px rgba(16,24,40,.04);
            --shadow-sm:    0 2px 8px rgba(16,24,40,.05);
            --ring-wa:      0 0 0 3px rgba(37,211,102,.18);

            /* Fonts */
            --font-body:    'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --font-heading: 'Satoshi', 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --font-mono:    'JetBrains Mono', 'Cascadia Code', Consolas, ui-monospace, monospace;
        }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { font-family: var(--font-body); }
        body { background: var(--bg); color: var(--ink); }

        /* Top bar: German flag stripes (black/red/yellow) along GLS branding */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--hairline);
            box-shadow: var(--shadow-xs);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-stripes {
            height: 3px;
            background: linear-gradient(to right,
                var(--gls-black) 0 33.33%,
                var(--gls-red) 33.33% 66.66%,
                var(--gls-yellow) 66.66% 100%);
        }
        .logo-wrap { display: flex; align-items: center; gap: 12px; }
        .logo-wrap img { height: 40px; width: auto; }
        .logo-wrap .divider { height: 32px; width: 1px; background: var(--hairline); }
        .logo-wrap .app-title { display: flex; flex-direction: column; line-height: 1.15; }
        .logo-wrap .app-title .app-name {
            font-family: var(--font-heading);
            font-weight: 700; font-size: 15px; color: var(--ink);
            display: inline-flex; align-items: center; gap: 6px;
            letter-spacing: -0.01em;
        }
        .logo-wrap .app-title .app-sub {
            font-size: 11px; color: var(--muted); font-weight: 500; letter-spacing: 0.3px;
        }
        .wa-dot {
            display: inline-block; width: 7px; height: 7px; border-radius: 50%;
            background: var(--wa-green); box-shadow: 0 0 0 3px rgba(37,211,102,.18);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 3px rgba(37,211,102,.18); }
            50%      { box-shadow: 0 0 0 5px rgba(37,211,102,.08); }
        }

        /* =========================================================
           BUTTONS — modern pill style
           Works across all Bootstrap variants used: btn-wa, btn-lg,
           btn-sm, btn-warning, btn-info, btn-danger, btn-light,
           btn-link, btn-outline-*.
           No markup changes needed anywhere.
           ========================================================= */
        .btn {
            --btn-y: 0.58rem;
            --btn-x: 1.05rem;
            --btn-fz: .92rem;
            --btn-shadow: 0 1px 2px rgba(16,24,40,.06), 0 0 0 1px rgba(16,24,40,.02) inset;
            --btn-shadow-hover: 0 8px 20px -6px rgba(16,24,40,.18), 0 2px 4px rgba(16,24,40,.06);
            font-family: var(--font-body);
            font-weight: 600;
            letter-spacing: -0.005em;
            border-radius: 10px;
            border: 1px solid transparent;
            padding: var(--btn-y) var(--btn-x);
            font-size: var(--btn-fz);
            line-height: 1.25;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            transition:
                transform .15s cubic-bezier(.4,0,.2,1),
                box-shadow .15s cubic-bezier(.4,0,.2,1),
                background-color .15s ease,
                border-color .15s ease,
                color .15s ease;
            box-shadow: var(--btn-shadow);
            white-space: nowrap;
            -webkit-tap-highlight-color: transparent;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: var(--btn-shadow-hover); }
        .btn:active { transform: translateY(0); box-shadow: var(--btn-shadow); transition-duration: .08s; }
        .btn:focus { outline: none; }
        .btn:focus-visible { outline: none; box-shadow: var(--btn-shadow-hover), var(--ring-wa); }
        .btn:disabled, .btn.disabled { opacity: .55; transform: none !important; box-shadow: var(--btn-shadow) !important; cursor: not-allowed; }

        /* Bootstrap icons inside buttons — sit on the baseline, crisp */
        .btn .bi { font-size: 1em; line-height: 1; display: inline-block; }

        /* Optical sizes */
        .btn-sm {
            --btn-y: 0.4rem;
            --btn-x: 0.75rem;
            --btn-fz: .82rem;
            border-radius: 8px;
            gap: 6px;
        }
        .btn-lg {
            --btn-y: 0.78rem;
            --btn-x: 1.4rem;
            --btn-fz: 1rem;
            border-radius: 12px;
            gap: 10px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        /* -------- Primary WhatsApp action -------- */
        .btn-wa {
            background: linear-gradient(180deg, #2ee370 0%, var(--wa-green) 100%);
            border-color: #1fbd5a;
            color: #fff;
            box-shadow:
                0 1px 2px rgba(18,140,126,.25),
                0 0 0 1px rgba(255,255,255,.08) inset,
                0 -1px 0 rgba(0,0,0,.08) inset;
        }
        .btn-wa::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.22), rgba(255,255,255,0) 55%);
            pointer-events: none;
            z-index: -1;
        }
        .btn-wa:hover, .btn-wa:focus {
            background: linear-gradient(180deg, #2ad366 0%, #1fba5a 100%);
            border-color: #17a450;
            color: #fff;
            box-shadow:
                0 10px 24px -8px rgba(37,211,102,.5),
                0 2px 4px rgba(18,140,126,.2),
                0 0 0 1px rgba(255,255,255,.08) inset;
        }
        .btn-wa:active {
            background: var(--wa-green-dk);
            border-color: var(--wa-green-dk);
        }

        /* -------- Semantic variants (warning / info / danger) --------
           Flattened gradient + unified pressed behaviour so they feel
           consistent with btn-wa, not like default Bootstrap swatches. */
        .btn-warning {
            background: linear-gradient(180deg, #fbbf24, #f59e0b);
            border-color: #d97706; color: #1f2937;
        }
        .btn-warning:hover, .btn-warning:focus {
            background: linear-gradient(180deg, #f59e0b, #d97706);
            border-color: #b45309; color: #1f2937;
            box-shadow: 0 10px 24px -8px rgba(245,158,11,.45), 0 2px 4px rgba(180,83,9,.2);
        }

        .btn-info {
            background: linear-gradient(180deg, #22d3ee, #0ea5e9);
            border-color: #0284c7; color: #fff;
        }
        .btn-info:hover, .btn-info:focus {
            background: linear-gradient(180deg, #0ea5e9, #0284c7);
            border-color: #0369a1; color: #fff;
            box-shadow: 0 10px 24px -8px rgba(14,165,233,.45), 0 2px 4px rgba(3,105,161,.2);
        }

        .btn-danger {
            background: linear-gradient(180deg, #f87171, #ef4444);
            border-color: #dc2626; color: #fff;
        }
        .btn-danger:hover, .btn-danger:focus {
            background: linear-gradient(180deg, #ef4444, #dc2626);
            border-color: #b91c1c; color: #fff;
            box-shadow: 0 10px 24px -8px rgba(239,68,68,.45), 0 2px 4px rgba(185,28,28,.2);
        }

        /* -------- Neutral / light -------- */
        .btn-light {
            background: #fff; border-color: var(--hairline); color: var(--ink);
        }
        .btn-light:hover, .btn-light:focus {
            background: #f9fafb; border-color: #d1d5db; color: var(--ink);
        }

        .btn-link {
            background: transparent; border-color: transparent;
            color: var(--muted); box-shadow: none !important;
            text-decoration: none; font-weight: 500;
        }
        .btn-link:hover, .btn-link:focus {
            color: var(--ink); background: #f3f4f6; transform: none;
        }

        /* -------- Outline variants -------- */
        .btn-outline-primary,
        .btn-outline-danger,
        .btn-outline-secondary {
            background: #fff;
            box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 0 0 1px rgba(16,24,40,.02) inset;
        }
        .btn-outline-primary {
            color: var(--wa-green-dk); border-color: #bbf7d0;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background: var(--wa-green); border-color: var(--wa-green); color: #fff;
            box-shadow: 0 8px 20px -6px rgba(37,211,102,.35);
        }
        .btn-outline-danger {
            color: #b91c1c; border-color: #fecaca;
        }
        .btn-outline-danger:hover, .btn-outline-danger:focus {
            background: #ef4444; border-color: #ef4444; color: #fff;
            box-shadow: 0 8px 20px -6px rgba(239,68,68,.35);
        }
        .btn-outline-secondary {
            color: #4b5563; border-color: var(--hairline);
        }
        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background: #f9fafb; border-color: #d1d5db; color: var(--ink);
            box-shadow: var(--btn-shadow-hover);
        }

        /* -------- Button group polish -------- */
        .btn-group > .btn { box-shadow: none; }
        .btn-group > .btn:hover { transform: none; }
        .btn-group > .btn:not(:last-child) { border-right-width: 0; }
        .btn-group > .btn:focus-visible { z-index: 2; }

        /* Full-width buttons shouldn't shift on hover (layout jank) */
        .btn.w-100:hover { transform: none; }

        /* Cards */
        .card {
            border: 1px solid var(--hairline); border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs); background: var(--surface);
            transition: box-shadow .15s ease;
        }
        .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--hairline-soft);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important;
            padding: 0.9rem 1.1rem;
        }
        .card-header h5, .card-header h6 { margin: 0; font-family: var(--font-heading); }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700; letter-spacing: -0.015em;
        }
        .fs-page-title {
            font-family: var(--font-heading);
            font-size: 1.75rem; font-weight: 900; letter-spacing: -0.025em;
        }
        .section-eyebrow {
            text-transform: uppercase; letter-spacing: .08em;
            font-size: .72rem; font-weight: 700; color: var(--muted);
        }

        /* Status pill */
        .badge-status { text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.6px; font-weight: 700; }
        .badge { font-weight: 500; padding: 0.35em 0.7em; border-radius: 8px; font-size: 0.78rem; }
        .status-pending  { background: #eef2f6; color: #4b5563; }
        .status-sending  { background: #fef3c7; color: #92400e; }
        .status-sent     { background: #d1fae5; color: #065f46; }
        .status-failed   { background: #fee2e2; color: #991b1b; }
        .status-skipped  { background: #ede9fe; color: #5b21b6; }
        .status-queued   { background: #eef2f6; color: #4b5563; }

        /* Log */
        .log-pre {
            background: #0b1020; color: #86efac;
            font-family: 'JetBrains Mono', 'Cascadia Code', Consolas, ui-monospace, monospace;
            font-size: 12.5px; padding: 16px; border-radius: 10px;
            max-height: 380px; overflow: auto; white-space: pre-wrap;
            margin: 0; border: 1px solid #1e293b;
        }
        .log-pre::-webkit-scrollbar { width: 8px; height: 8px; }
        .log-pre::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        /* Counter tiles */
        .stat-tile {
            padding: 14px 12px; border-radius: 12px; background: #fafbfc;
            border: 1px solid #eef0f4; transition: all .15s ease;
        }
        .stat-tile:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(16,24,40,.05); }
        .stat-tile .stat-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 8px; font-size: 15px;
        }
        .stat-tile .stat-value { font-size: 1.6rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; }
        .stat-tile .stat-label { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }

        /* Breadcrumb */
        .breadcrumb { background: transparent; padding: 0; margin-bottom: 0.5rem; font-size: 0.85rem; }
        .breadcrumb-item a { color: var(--muted); text-decoration: none; }
        .breadcrumb-item a:hover { color: var(--ink); }
        .breadcrumb-item.active { color: var(--ink); font-weight: 500; }

        /* Tables */
        .table > :not(caption) > * > * { padding: 0.8rem 0.9rem; }
        .table thead th {
            font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--muted); font-weight: 600; border-bottom: 1px solid #eef0f4;
        }
        .table tbody tr { border-bottom: 1px solid #f3f4f6; }
        .table tbody tr:last-child { border-bottom: none; }
        .table-hover tbody tr:hover { background: #fafbfc; }

        /* Alerts */
        .alert { border: none; border-radius: 12px; font-size: 0.92rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-info    { background: #dbeafe; color: #1e40af; }

        /* Progress */
        .progress { background: #eef0f4; border-radius: 999px; height: 10px; overflow: hidden; }
        .progress-bar { transition: width .3s ease; }

        /* Forms */
        .form-control, .form-select {
            border-radius: var(--radius-md); border-color: var(--hairline);
            padding: 0.6rem 0.9rem; font-size: .93rem;
            transition: border-color .12s ease, box-shadow .12s ease;
        }
        .form-control:hover, .form-select:hover { border-color: #d1d5db; }
        .form-control:focus, .form-select:focus { border-color: var(--wa-green); box-shadow: var(--ring-wa); }
        .form-label { font-weight: 600; font-size: 0.85rem; color: var(--ink); margin-bottom: 0.4rem; }
        .form-text { color: var(--muted); font-size: .8rem; }

        /* Nav pill for top actions */
        .nav-action {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 0.5rem 0.95rem; border-radius: 9px; font-size: 0.87rem; font-weight: 600;
            color: var(--muted); text-decoration: none; transition: all .12s ease;
            border: 1px solid transparent;
        }
        .nav-action:hover { background: #f3f4f6; color: var(--ink); }
        .nav-action.primary {
            background: var(--wa-green); color: #fff;
            box-shadow: 0 1px 2px rgba(37,211,102,.25);
        }
        .nav-action.primary:hover {
            background: var(--wa-green-dk); color: #fff;
            box-shadow: 0 4px 12px rgba(37,211,102,.28);
        }

        /* Center badge in topbar */
        .center-badge {
            cursor: default;
            background: #fff5f5;
            color: #b91c1c;
            font-weight: 700;
            border: 1px solid #fecaca;
        }
        .center-badge:hover { background: #fff5f5; color: #b91c1c; }

        /* Empty state */
        .empty-state { padding: 60px 20px; text-align: center; color: var(--muted); }
        .empty-state i { font-size: 3rem; opacity: 0.3; }

        /* Subtle fade-in for main content */
        main > * { animation: fadeIn .25s ease both; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

        /* =========================================================
           TOASTS — bottom-right stack, auto-dismiss, accessible
           ========================================================= */
        .gls-toast-host {
            position: fixed;
            right: 20px; bottom: 20px;
            display: flex; flex-direction: column; gap: 10px;
            z-index: 2000;
            pointer-events: none;
            max-width: calc(100vw - 40px);
        }
        .gls-toast {
            pointer-events: auto;
            min-width: 300px; max-width: 440px;
            background: #fff;
            border: 1px solid var(--hairline);
            border-left: 4px solid var(--muted);
            border-radius: 12px;
            padding: 12px 14px 12px 16px;
            box-shadow:
                0 14px 40px -10px rgba(16,24,40,.18),
                0 4px 10px rgba(16,24,40,.06);
            display: flex; align-items: flex-start; gap: 12px;
            animation: toastIn .28s cubic-bezier(.2,.9,.25,1);
            overflow: hidden;
            position: relative;
        }
        .gls-toast.-leaving { animation: toastOut .22s ease forwards; }
        .gls-toast .t-icon {
            width: 32px; height: 32px; flex: 0 0 32px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .gls-toast .t-body { flex: 1; min-width: 0; }
        .gls-toast .t-title {
            font-family: var(--font-heading);
            font-weight: 700; font-size: .95rem;
            letter-spacing: -.01em; color: var(--ink);
            line-height: 1.25; margin-bottom: 2px;
        }
        .gls-toast .t-msg {
            font-size: .85rem; color: #4b5563; line-height: 1.4;
            word-break: break-word;
        }
        .gls-toast .t-close {
            background: transparent; border: 0; padding: 2px 4px;
            color: #9ca3af; cursor: pointer; font-size: 1rem;
            border-radius: 6px; transition: color .12s, background .12s;
            align-self: flex-start;
        }
        .gls-toast .t-close:hover { color: var(--ink); background: #f3f4f6; }
        .gls-toast .t-bar {
            position: absolute; left: 0; bottom: 0; height: 2px;
            background: currentColor; opacity: .35;
            animation: toastBar linear forwards;
            transform-origin: left center;
        }

        .gls-toast.-success { border-left-color: var(--wa-green); }
        .gls-toast.-success .t-icon { background: #d1fae5; color: #065f46; }
        .gls-toast.-success .t-bar   { color: var(--wa-green); }

        .gls-toast.-error   { border-left-color: #ef4444; }
        .gls-toast.-error   .t-icon { background: #fee2e2; color: #991b1b; }
        .gls-toast.-error   .t-bar   { color: #ef4444; }

        .gls-toast.-warning { border-left-color: #f59e0b; }
        .gls-toast.-warning .t-icon { background: #fef3c7; color: #92400e; }
        .gls-toast.-warning .t-bar   { color: #f59e0b; }

        .gls-toast.-info    { border-left-color: #0ea5e9; }
        .gls-toast.-info    .t-icon { background: #dbeafe; color: #1e40af; }
        .gls-toast.-info    .t-bar   { color: #0ea5e9; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateY(14px) scale(.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toastOut {
            to { opacity: 0; transform: translateX(20px); max-height: 0; margin: 0; padding-top: 0; padding-bottom: 0; }
        }
        @keyframes toastBar { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        /* =========================================================
           MODAL — replaces native confirm()
           ========================================================= */
        .gls-modal-backdrop {
            position: fixed; inset: 0; z-index: 1900;
            background: rgba(15, 23, 42, .45);
            backdrop-filter: blur(2px);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            animation: bdIn .18s ease;
        }
        .gls-modal-backdrop.-leaving { animation: bdOut .16s ease forwards; }
        .gls-modal {
            background: #fff;
            border-radius: 16px;
            max-width: 440px; width: 100%;
            box-shadow:
                0 24px 60px -15px rgba(15,23,42,.35),
                0 0 0 1px rgba(16,24,40,.04);
            animation: modalIn .22s cubic-bezier(.2,.9,.25,1);
            overflow: hidden;
        }
        .gls-modal.-leaving { animation: modalOut .16s ease forwards; }
        .gls-modal .m-body { padding: 22px 22px 8px 22px; }
        .gls-modal .m-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 14px;
            background: #fee2e2; color: #991b1b;
        }
        .gls-modal.-info .m-icon    { background: #dbeafe; color: #1e40af; }
        .gls-modal.-warning .m-icon { background: #fef3c7; color: #92400e; }
        .gls-modal .m-title {
            font-family: var(--font-heading);
            font-size: 1.15rem; font-weight: 700; letter-spacing: -.015em;
            color: var(--ink); margin: 0 0 6px;
        }
        .gls-modal .m-msg {
            font-size: .92rem; color: #4b5563; line-height: 1.5;
            margin: 0 0 18px;
        }
        .gls-modal .m-actions {
            padding: 14px 22px 18px;
            display: flex; justify-content: flex-end; gap: 8px;
            background: #fafbfc;
            border-top: 1px solid var(--hairline-soft);
        }

        @keyframes bdIn   { from { opacity: 0; } to { opacity: 1; } }
        @keyframes bdOut  { to   { opacity: 0; } }
        @keyframes modalIn  { from { opacity: 0; transform: translateY(12px) scale(.97); } to { opacity: 1; transform: none; } }
        @keyframes modalOut { to   { opacity: 0; transform: translateY(6px) scale(.98); } }

        @media (prefers-reduced-motion: reduce) {
            .gls-toast, .gls-modal, .gls-modal-backdrop, main > * { animation: none !important; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-stripes"></div>
    <div class="container d-flex justify-content-between align-items-center py-3">
        <a href="index.php" class="logo-wrap text-decoration-none">
            <img src="public/gls.png" alt="GLS Sprachenzentrum">
            <div class="divider"></div>
            <div class="app-title">
                <span class="app-name">
                    <span class="wa-dot"></span> WhatsApp Sender
                </span>
                <span class="app-sub">
                    <i class="bi bi-hdd-network"></i> Local · GLS Sprachenzentrum
                </span>
            </div>
        </a>
        <nav class="d-flex gap-2 align-items-center">
            <?php $__center = function_exists('currentCenter') ? currentCenter() : null; if ($__center): ?>
                <span class="nav-action center-badge" title="Centre de ce poste">
                    <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($__center['name']) ?>
                </span>
            <?php endif; ?>
            <a href="index.php" class="nav-action">
                <i class="bi bi-collection"></i> Campagnes
            </a>
            <a href="index.php?action=export" class="nav-action" title="Exporter les données de ce centre">
                <i class="bi bi-download"></i> Export
            </a>
            <a href="index.php?action=settings" class="nav-action" title="Paramètres du centre">
                <i class="bi bi-gear"></i>
            </a>
            <a href="index.php?action=create" class="nav-action primary">
                <i class="bi bi-plus-lg"></i> Nouvelle campagne
            </a>
        </nav>
    </div>
</header>

<main class="container py-4 pb-5">
    <?= $content ?>
</main>

<footer class="text-center text-muted small py-4">
    <i class="bi bi-shield-check"></i>
    WhatsApp Sender · exécution locale sur votre PC Windows
</footer>

<!-- Toast host (populated by GlsUI.toast) -->
<div class="gls-toast-host" role="region" aria-live="polite" aria-label="Notifications"></div>

<!-- Flash messages → toasts -->
<?php
$__flashSuccess = $_SESSION['flash_success'] ?? null;
$__flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<script>
/**
 * GlsUI — tiny, dependency-free toast + modal-confirm helper.
 *
 * Usage:
 *   GlsUI.toast('Campagne créée', { type: 'success' });
 *   GlsUI.toast({ title: 'Échec', message: 'Détails…', type: 'error' });
 *   const ok = await GlsUI.confirm('Supprimer cette campagne ?');
 *   const ok = await GlsUI.confirm({
 *       title: 'Supprimer ?', message: '...', confirmText: 'Supprimer',
 *       confirmVariant: 'danger', type: 'warning'
 *   });
 */
(function () {
    const ICONS = {
        success: 'bi-check-circle-fill',
        error:   'bi-exclamation-octagon-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill',
    };
    const DEFAULT_TITLES = {
        success: 'Succès',
        error:   'Erreur',
        warning: 'Attention',
        info:    'Info',
    };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function toast(input, opts) {
        let cfg = (typeof input === 'string')
            ? Object.assign({ message: input }, opts || {})
            : Object.assign({}, input || {});
        cfg.type     = cfg.type || 'info';
        cfg.title    = cfg.title  || DEFAULT_TITLES[cfg.type] || 'Info';
        cfg.duration = cfg.duration == null ? (cfg.type === 'error' ? 6500 : 4500) : cfg.duration;

        const host = document.querySelector('.gls-toast-host');
        if (!host) return;

        const el = document.createElement('div');
        el.className = 'gls-toast -' + cfg.type;
        el.setAttribute('role', cfg.type === 'error' ? 'alert' : 'status');
        el.innerHTML = `
            <span class="t-icon"><i class="bi ${ICONS[cfg.type] || ICONS.info}"></i></span>
            <div class="t-body">
                <div class="t-title">${esc(cfg.title)}</div>
                ${cfg.message ? `<div class="t-msg">${esc(cfg.message)}</div>` : ''}
            </div>
            <button type="button" class="t-close" aria-label="Fermer"><i class="bi bi-x-lg"></i></button>
            ${cfg.duration > 0 ? `<span class="t-bar" style="animation-duration:${cfg.duration}ms"></span>` : ''}
        `;

        function dismiss() {
            if (el.classList.contains('-leaving')) return;
            el.classList.add('-leaving');
            setTimeout(() => el.remove(), 220);
        }
        el.querySelector('.t-close').addEventListener('click', dismiss);
        if (cfg.duration > 0) setTimeout(dismiss, cfg.duration);

        host.appendChild(el);
        return { dismiss };
    }

    function confirmModal(input) {
        const cfg = Object.assign({
            title: 'Confirmer',
            message: '',
            confirmText: 'Confirmer',
            cancelText: 'Annuler',
            confirmVariant: 'wa',   // 'wa' | 'danger' | 'warning' | 'info'
            type: 'warning',         // icon color
        }, typeof input === 'string' ? { message: input } : (input || {}));

        return new Promise((resolve) => {
            const bd = document.createElement('div');
            bd.className = 'gls-modal-backdrop';
            bd.innerHTML = `
                <div class="gls-modal -${esc(cfg.type)}" role="dialog" aria-modal="true" aria-labelledby="gls-m-t">
                    <div class="m-body">
                        <span class="m-icon"><i class="bi ${ICONS[cfg.type] || ICONS.warning}"></i></span>
                        <h3 class="m-title" id="gls-m-t">${esc(cfg.title)}</h3>
                        <p class="m-msg">${esc(cfg.message)}</p>
                    </div>
                    <div class="m-actions">
                        <button type="button" class="btn btn-light" data-act="cancel">${esc(cfg.cancelText)}</button>
                        <button type="button" class="btn btn-${esc(cfg.confirmVariant)}" data-act="confirm">${esc(cfg.confirmText)}</button>
                    </div>
                </div>`;

            function close(result) {
                bd.classList.add('-leaving');
                bd.querySelector('.gls-modal').classList.add('-leaving');
                setTimeout(() => { bd.remove(); document.removeEventListener('keydown', onKey); resolve(result); }, 160);
            }
            function onKey(e) {
                if (e.key === 'Escape') close(false);
                else if (e.key === 'Enter') close(true);
            }

            bd.addEventListener('click', (e) => { if (e.target === bd) close(false); });
            bd.querySelector('[data-act=cancel]').addEventListener('click', () => close(false));
            bd.querySelector('[data-act=confirm]').addEventListener('click', () => close(true));
            document.addEventListener('keydown', onKey);

            document.body.appendChild(bd);
            bd.querySelector('[data-act=confirm]').focus();
        });
    }

    window.GlsUI = { toast, confirm: confirmModal };
})();

// Replay PHP flash messages as toasts
<?php if ($__flashSuccess): ?>
GlsUI.toast(<?= json_encode($__flashSuccess, JSON_UNESCAPED_UNICODE) ?>, { type: 'success' });
<?php endif; ?>
<?php if ($__flashError): ?>
GlsUI.toast(<?= json_encode($__flashError, JSON_UNESCAPED_UNICODE) ?>, { type: 'error' });
<?php endif; ?>
</script>

</body>
</html>
