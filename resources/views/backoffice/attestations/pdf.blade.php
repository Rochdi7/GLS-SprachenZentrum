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

    // Prefer the direct site relation; fall back to group → site.
    $site = $attestation->site ?? $attestation->group?->site;

    // Session duration depends on the centre (NOT a fixed 45 min):
    //   2h    → Rabat, Casablanca, Sale, Kenitra, Online
    //   2h30  → Marrakech, Agadir
    $durationHours = $site?->getCourseDuration() ?? 2;
    $durationDe = $durationHours == 2.5 ? '2 Stunden 30 Minuten' : '2 Stunden';
    $durationFr = $durationHours == 2.5 ? '2 heures 30' : '2 heures';

    // Format helper — empty values are rendered as a non-breaking dash so layout doesn't collapse.
    $fmt = fn ($d) => $d?->format('d-m-Y') ?? '—';

    $courseStart  = $fmt($attestation->course_start_date);
    $courseEnd    = $attestation->is_ongoing ? 'heute' : $fmt($attestation->course_end_date);
    $niveauStart  = $fmt($attestation->niveau_start_date);
    $niveauEnd    = $attestation->is_ongoing ? 'heute' : $fmt($attestation->niveau_end_date);

    $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen'];
    $levels     = ['A1', 'A2', 'B1', 'B2', 'C1'];
    $checkedLevels = $attestation->checked_levels;

    $defaultMethodology = "L'appréciation des résultats obtenus en cours est faite par les enseignant(e)s. Cette attestation de présence n'est pas un diplôme. Le barème comprend 4 appréciations : très bien, bien, assez bien, participation régulière.";
    $methodologyText = trim((string) ($attestation->methodology_text ?? '')) !== ''
        ? $attestation->methodology_text
        : $defaultMethodology;

    // Footer — address/phone/email resolved from site record.
    $addressMap = [
        'Casablanca' => '14 Bd de Paris, 1er étage N°8, Casablanca 20000',
        'Marrakech'  => '3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000',
        'Rabat'      => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat',
        'Kenitra'    => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000',
        'Kénitra'    => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000',
        'Sale'       => 'Avenue Mohamed V, Rue Halima N°12 Diyar, Salé',
        'Salé'       => 'Avenue Mohamed V, Rue Halima N°12 Diyar, Salé',
        'Agadir'     => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
    ];
    $siteCity      = $site?->city ?? '';
    $footerAddress = $addressMap[$siteCity] ?? ($site?->address ?? '');
    $footerPhone   = $site?->phone ?? '+212 6 69 51 50 19 / +212 5 37 37 20 03';
    $footerEmail   = $site?->email ?? 'info@gls-sprachzentrum.ma';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teilnahmebestätigung — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page { size: A4 portrait; margin: 35px 40px 20px 40px; }
        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ===== HEADER ===== */
        table.header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
            table-layout: fixed;
        }
        table.header td { vertical-align: middle; padding: 0; }
        table.header td.logo-cell { width: 160px; }
        table.header td.title-cell { padding-right: 40px; text-align: center; }
        .header-logo { width: 120px; height: auto; display: block; margin-left: 20px; }
        .header-title {
            font-size: 21pt;
            font-weight: bold;
            text-decoration: underline;
            line-height: 1;
            white-space: nowrap;
            letter-spacing: -0.2px;
        }

        /* ===== NAME ===== */
        .name-block { margin-bottom: 16px; }
        .name-value {
            font-size: 15pt;
            font-weight: bold;
            line-height: 1.2;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }
        .name-label { font-size: 8.5pt; color: #666; font-style: italic; margin-top: 2px; }

        /* ===== BIRTH ===== */
        table.birth {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            table-layout: fixed;
        }
        table.birth td { width: 50%; vertical-align: top; padding: 0; }
        table.birth td.right { padding-left: 18px; }
        .birth-value {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .birth-label { font-size: 8.5pt; color: #666; font-style: italic; margin-top: 2px; }

        /* ===== PARAGRAPHS ===== */
        .block { margin-bottom: 14px; }
        .line-de { font-size: 10.5pt; line-height: 1.6; }
        .line-fr { font-size: 10pt; line-height: 1.5; color: #333; margin-top: 2px; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .nowrap { white-space: nowrap; }

        /* ===== UNITS — balanced fixed columns ===== */
        table.units {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 16px 0;
            table-layout: fixed;
        }
        table.units td { padding: 0; vertical-align: middle; }
        table.units td.label-de { width: 32%; font-size: 10.5pt; padding-right: 8px; }
        table.units td.label-fr { width: 32%; font-size: 10pt; color: #333; padding-right: 8px; }
        table.units td.num {
            width: 14%;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            white-space: nowrap;
        }
        table.units td.suffix-de { width: 54%; font-size: 10.5pt; padding-left: 8px; }
        table.units td.suffix-fr { width: 54%; font-size: 10pt; color: #333; padding-left: 8px; }

        /* ===== CHECKBOX ROWS ===== */
        .check-row {
            margin: 4px 0;
            padding-left: 8px;
            font-size: 10.5pt;
            line-height: 1.55;
        }
        .check-box {
            display: inline-block;
            width: 12px; height: 12px;
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

        /* ===== NIVEAU PERIOD ===== */
        .niveau-period { margin: 18px 0 6px; font-size: 10.5pt; }

        /* ===== LEVELS — centered on the page ===== */
        .levels-title { margin: 10px 0 8px; font-size: 10.5pt; text-align: center; }
        .levels-wrap { text-align: center; margin-bottom: 14px; }
        table.levels {
            border-collapse: collapse;
            display: inline-table;
            margin: 0 auto;
        }
        table.levels td {
            font-size: 11.5pt;
            font-weight: bold;
            padding: 0 28px;
            white-space: nowrap;
            vertical-align: middle;
            text-align: left;
        }

        /* ===== KURSINFO ===== */
        .kursinfo-title {
            font-size: 10.5pt;
            font-weight: 600;
            margin-top: 14px;
            margin-bottom: 4px;
        }
        .kursinfo-line  { font-size: 10.5pt; margin-bottom: 4px; }
        .erfolg-line    { font-size: 10pt; margin-bottom: 12px; line-height: 1.5; color: #333; }
        .erfolg-active  { font-weight: bold; text-decoration: underline; color: #111; }

        .legal {
            font-size: 8.5pt;
            color: #555;
            line-height: 1.55;
            margin-bottom: 24px;
            text-align: justify;
        }

        /* ===== SIGNATURE — centred pair, Kursleitung sits under Datum ===== */
        .sig-wrap { text-align: center; margin-top: 28px; }
        table.sig {
            border-collapse: collapse;
            display: inline-table;
            margin: 0 auto;
        }
        table.sig td { vertical-align: top; padding: 0; }
        table.sig td.left  { text-align: center; padding-right: 160px; }
        table.sig td.right { text-align: center; }
        .sig-value {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.2;
            white-space: nowrap;
        }
        .sig-label { font-size: 8.5pt; color: #666; font-style: italic; margin-top: 2px; }

        .kursleitung-cell {
            padding-top: 22px;
            text-align: center;
            font-size: 10pt;
            text-decoration: underline;
            white-space: nowrap;
        }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            left: 40px; right: 40px;
            bottom: 0;
            text-align: center;
            font-size: 8pt;
            color: #444;
            border-top: 1px solid #aaa;
            padding-top: 6px;
            line-height: 1.55;
        }
        .footer div { line-height: 1.5; }
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

    {{-- ============ PARTICIPATION (matches Word reference) ============ --}}
    <div class="block">
        <div class="line-de">
            hat in der Zeit <span class="underline">vom</span> / a participé
            <span class="bold nowrap">{{ $courseStart }}</span> bis
            <span class="bold nowrap">{{ $courseEnd }}</span>
            <br>
            an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.
        </div>
        <div class="line-fr">
            A un cours de la langue Allemande au GLS Sprachenzentrum.
        </div>
    </div>

    {{-- ============ UNITS — fixed grid: label | number | suffix ============ --}}
    <table class="units">
        <tr>
            <td class="label-de">Der Kurs umfasste</td>
            <td class="num" rowspan="2">{{ $attestation->units_45min }}</td>
            <td class="suffix-de"><span class="underline">Unterrichtseinheiten</span> zu je {{ $durationDe }}.</td>
        </tr>
        <tr>
            <td class="label-fr">Le cours comprenait</td>
            <td class="suffix-fr">unités de cours de {{ $durationFr }} chacune.</td>
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

    <div class="levels-wrap">
        <table class="levels">
            <tr>
                @foreach($levels as $lvl)
                    <td>
                        <span class="check-box {{ in_array($lvl, $checkedLevels, true) ? 'checked' : '' }}"></span>{{ $lvl }}
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    {{-- ============ KURSINFO ============ --}}
    <div class="kursinfo-title">Kursinfo / Information sur le cours :</div>
    <div class="kursinfo-line">
        <span class="underline">Stufe</span> {{ $attestation->stufe_index }} von {{ $attestation->stufe_total }},
        Niveau {{ $attestation->stufe_index }} de {{ $attestation->stufe_total }}
    </div>

    <div class="erfolg-line">
        @foreach($erfolgList as $i => $opt)
            <span>{{ $opt }}</span>@if($i < count($erfolgList) - 1) , @endif
        @endforeach
        .
    </div>

    <div class="legal">{!! nl2br(e($methodologyText)) !!}</div>

    {{-- ============ SIGNATURE (centred, Kursleitung directly under Datum) ============ --}}
    <div class="sig-wrap">
        <table class="sig">
            <tr>
                <td class="left">
                    <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                    <div class="sig-label">Ort, Lieu</div>
                </td>
                <td class="right">
                    <div class="sig-value">{{ $attestation->issue_date?->format('d.m.Y') }}</div>
                    <div class="sig-label">Datum, Date</div>
                </td>
            </tr>
            <tr>
                <td class="left"></td>
                <td class="kursleitung-cell">Kursleitung :</td>
            </tr>
        </table>
    </div>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        <div>Adresse : {{ $footerAddress }}</div>
        <div>Tel : {{ $footerPhone }} , Email : {{ $footerEmail }}</div>
    </div>

</body>
</html>
