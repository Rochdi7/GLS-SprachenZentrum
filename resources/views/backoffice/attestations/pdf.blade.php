@php
    /**
     * Bilingual (DE/FR) Teilnahmebestätigung — pixel-stable layout for DomPDF.
     *
     * Layout principles:
     *  - A4 portrait, content area 535pt wide (A4 = 595pt, 30pt left+right margin).
     *  - Outer wrapper uses fixed-pixel widths everywhere — no percentages.
     *  - Every block has a fixed top/bottom margin so vertical rhythm never drifts.
     *  - White-space: nowrap on names/dates so long values don't wrap and break columns.
     */

    $site         = $attestation->group?->site;
    $courseStart  = $attestation->course_start_date?->format('d-m-Y') ?? '—';
    $courseEnd    = $attestation->is_ongoing ? 'heute' : ($attestation->course_end_date?->format('d-m-Y') ?? '—');
    $niveauStart  = $attestation->niveau_start_date?->format('d-m-Y') ?? '—';
    $niveauEnd    = $attestation->is_ongoing ? 'heute' : ($attestation->niveau_end_date?->format('d-m-Y') ?? '—');

    $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen'];
    $levels     = ['A1', 'A2', 'B1', 'B2'];

    $defaultMethodology = "L'appréciation des résultats obtenus en cours est faite par les enseignant(e)s. Cette attestation de présence n'est pas un diplôme. Le barème comprend 4 appréciations : très bien, bien, assez bien, participation régulière.";
    $methodologyText = trim((string) ($attestation->methodology_text ?? '')) !== ''
        ? $attestation->methodology_text
        : $defaultMethodology;

    // Footer — pulled from the centre when available, with a Salé fallback.
    $footerAddress = $site?->address  ?? 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech.';
    $footerPhone   = $site?->phone    ?? '0808540625 / 0622996078';
    $footerEmail   = $site?->email    ?? 'info@glssprachenzentrum.ma';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teilnahmebestätigung — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page { size: A4 portrait; margin: 30px 30px 100px 30px; }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.45;
        }

        /* ====== HEADER ====== */
        table.header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.header td { vertical-align: middle; padding: 0; }
        table.header td.logo-cell { width: 150px; }
        table.header td.title-cell {
            text-align: left;
            padding-left: 30px;
        }
        .header-logo { width: 130px; height: auto; display: block; }
        .header-title {
            font-size: 22pt;
            font-weight: bold;
            text-decoration: underline;
            line-height: 1;
            white-space: nowrap;
        }

        /* ====== NAME BLOCK ====== */
        .name-block { margin-bottom: 18px; }
        .name-value {
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.15;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .name-label {
            font-size: 8.5pt;
            color: #555;
            font-style: italic;
            margin-top: 3px;
        }

        /* ====== BIRTH ROW ====== */
        table.birth {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            table-layout: fixed;
        }
        table.birth td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }
        table.birth td.right { padding-left: 20px; }
        .birth-value {
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .birth-label {
            font-size: 8.5pt;
            color: #555;
            font-style: italic;
            margin-top: 3px;
        }

        /* ====== SHARED PARAGRAPH STYLES ====== */
        .block { margin-bottom: 14px; }
        .line-de { font-size: 11pt; line-height: 1.5; }
        .line-fr { font-size: 10.5pt; line-height: 1.4; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .nowrap { white-space: nowrap; }

        /* ====== UNITS GRID — fixed columns: text | number | suffix ====== */
        table.units {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            table-layout: fixed;
        }
        table.units td { padding: 0; vertical-align: middle; }
        table.units td.label-de { width: 175px; font-size: 11pt; }
        table.units td.label-fr { width: 175px; font-size: 10.5pt; }
        table.units td.num {
            width: 70px;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
        }
        table.units td.suffix-de { font-size: 11pt; padding-left: 6px; }
        table.units td.suffix-fr { font-size: 10.5pt; padding-left: 6px; }

        /* ====== CHECKBOX ROWS ====== */
        .check-row {
            margin: 5px 0;
            font-size: 11pt;
            line-height: 1.5;
        }
        .check-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.2px solid #111;
            position: relative;
            vertical-align: -2px;
            margin-right: 8px;
        }
        .check-box.checked::before,
        .check-box.checked::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 16px; height: 1.2px;
            background: #111;
        }
        .check-box.checked::before { transform: translate(-50%, -50%) rotate(45deg); }
        .check-box.checked::after  { transform: translate(-50%, -50%) rotate(-45deg); }

        /* ====== NIVEAU PERIOD ====== */
        .niveau-period {
            margin: 16px 0 10px;
            font-size: 11pt;
        }

        /* ====== LEVELS GRID — fixed-pitch cells ====== */
        .levels-title {
            margin: 8px 0 8px;
            font-size: 11pt;
        }
        table.levels {
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.levels td {
            font-size: 12pt;
            font-weight: bold;
            padding: 0 30px 0 0;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* ====== KURSINFO ====== */
        .kursinfo-title { font-size: 11pt; margin-top: 8px; margin-bottom: 4px; }
        .kursinfo-line  { font-size: 11pt; margin-bottom: 4px; }
        .erfolg-line    { font-size: 10.5pt; margin-bottom: 10px; line-height: 1.5; }
        .erfolg-active  { font-weight: bold; text-decoration: underline; }

        .legal {
            font-size: 9pt;
            color: #444;
            line-height: 1.5;
            margin-bottom: 22px;
        }

        /* ====== SIGNATURE ROW ====== */
        table.sig {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            table-layout: fixed;
        }
        table.sig td { width: 50%; vertical-align: top; padding: 0; }
        table.sig td.right { text-align: right; }
        .sig-value {
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.15;
            white-space: nowrap;
        }
        .sig-label {
            font-size: 8.5pt;
            color: #555;
            font-style: italic;
            margin-top: 3px;
        }
        .kursleitung {
            margin-top: 22px;
            text-align: right;
            font-size: 10.5pt;
            text-decoration: underline;
        }

        /* ====== FIXED FOOTER ====== */
        .footer {
            position: fixed;
            left: 30px; right: 30px;
            bottom: 20px;
            text-align: center;
            font-size: 8.5pt;
            color: #333;
            border-top: 1px solid #999;
            padding-top: 6px;
            line-height: 1.5;
        }
        .footer .addr { text-decoration: underline; }
    </style>
</head>
<body>

    {{-- ============ HEADER ============ --}}
    <table class="header">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo" alt="GLS">
            </td>
            <td class="title-cell">
                <span class="header-title">Teilnahmebestätigung</span>
            </td>
        </tr>
    </table>

    {{-- ============ NAME ============ --}}
    <div class="name-block">
        <div class="name-value">{{ strtoupper($attestation->last_name) }} {{ strtoupper($attestation->first_name) }}</div>
        <div class="name-label">Name, Vorname / Nom, Prénom</div>
    </div>

    {{-- ============ BIRTH ============ --}}
    <table class="birth">
        <tr>
            <td>
                <div class="birth-value">{{ $attestation->birth_date?->format('d/m/Y') }}</div>
                <div class="birth-label">geboren am / Date de Naissance</div>
            </td>
            <td class="right">
                <div class="birth-value">{{ strtoupper($attestation->birth_place) }}</div>
                <div class="birth-label">geboren in / Lieu de Naissance</div>
            </td>
        </tr>
    </table>

    {{-- ============ PARTICIPATION (DE on top, FR underneath) ============ --}}
    <div class="block">
        <div class="line-de">
            hat in der Zeit <span class="underline">vom</span>
            <span class="bold nowrap">{{ $courseStart }}</span> bis
            <span class="bold nowrap">{{ $courseEnd }}</span>
            an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.
        </div>
        <div class="line-fr">
            A participé du <span class="bold nowrap">{{ $courseStart }}</span> au
            <span class="bold nowrap">{{ $courseEnd }}</span>
            à un cours de langue allemande au GLS Sprachenzentrum.
        </div>
    </div>

    {{-- ============ UNITS — fixed grid: label | number | suffix ============ --}}
    <table class="units">
        <tr>
            <td class="label-de">Der Kurs umfasste</td>
            <td class="num" rowspan="2">{{ $attestation->units_45min }}</td>
            <td class="suffix-de">Unterrichtseinheiten zu je 45 Minuten.</td>
        </tr>
        <tr>
            <td class="label-fr">Le cours comprenait</td>
            <td class="suffix-fr">unités de cours de 45 minutes.</td>
        </tr>
    </table>

    {{-- ============ FEES ============ --}}
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'full' ? 'checked' : '' }}"></span>Die Kursgebühren wurden vollständig entrichtet.
    </div>
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'partial' ? 'checked' : '' }}"></span>Die Kursgebühren wurden teilweise entrichtet.
    </div>

    {{-- ============ NIVEAU PERIOD ============ --}}
    <div class="niveau-period">
        Das Niveau <span class="underline">beginnt</span> von
        <span class="bold nowrap">{{ $niveauStart }}</span> bis
        <span class="bold nowrap">{{ $niveauEnd }}</span>
    </div>

    {{-- ============ LEVELS ============ --}}
    <div class="levels-title">
        <span class="underline">Referenzniveau des Kurses</span> / Niveau de référence européen :
    </div>

    <table class="levels">
        <tr>
            @foreach($levels as $lvl)
                <td>
                    <span class="check-box {{ $attestation->level === $lvl ? 'checked' : '' }}"></span>{{ $lvl }}
                </td>
            @endforeach
        </tr>
    </table>

    {{-- ============ KURSINFO ============ --}}
    <div class="kursinfo-title">Kursinfo / Information sur le cours :</div>
    <div class="kursinfo-line">
        <span class="underline">Stufe</span> {{ $attestation->stufe_index }} von {{ $attestation->stufe_total }},
        Niveau {{ $attestation->stufe_index }} de {{ $attestation->stufe_total }}
    </div>

    <div class="erfolg-line">
        @foreach($erfolgList as $i => $opt)
            <span class="{{ $attestation->erfolg === $opt ? 'erfolg-active' : '' }}">{{ $opt }}</span>@if($i < count($erfolgList) - 1) , @endif
        @endforeach
        .
    </div>

    <div class="legal">{!! nl2br(e($methodologyText)) !!}</div>

    {{-- ============ SIGNATURE ============ --}}
    <table class="sig">
        <tr>
            <td>
                <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                <div class="sig-label">Ort, Lieu</div>
            </td>
            <td class="right">
                <div class="sig-value">{{ $attestation->issue_date?->format('d.m.Y') }}</div>
                <div class="sig-label">Datum, Date</div>
            </td>
        </tr>
    </table>

    <div class="kursleitung">Kursleitung :</div>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        <div class="addr">{{ $footerAddress }}</div>
        Tel : {{ $footerPhone }} , Email : {{ $footerEmail }}
    </div>

</body>
</html>
