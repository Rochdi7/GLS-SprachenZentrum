<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teilnahmebestätigung — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page { size: A4 portrait; margin: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12.5px;
            color: #1a1a1a;
            margin: 0;
            padding: 30px 55px 110px 55px;
            line-height: 1.45;
        }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .header td { vertical-align: middle; padding: 0; }

        .header-logo { width: 130px; height: auto; }

        .header-title {
            font-size: 28px;
            font-weight: bold;
            color: #1a1a1a;
            text-align: left;
            padding-left: 18px;
            text-decoration: underline;
            line-height: 1;
        }

        /* ===== NAME ===== */
        .name-block { margin-top: 8px; margin-bottom: 14px; }

        .name-value {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .name-label {
            font-size: 10.5px;
            color: #555;
            font-style: italic;
        }

        /* ===== BIRTH ===== */
        .birth { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .birth td { width: 50%; vertical-align: top; padding: 0; }
        .birth td.right { padding-left: 30px; }

        .birth-value {
            font-size: 17px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
        }
        .birth-label {
            font-size: 10.5px;
            color: #555;
            font-style: italic;
        }

        /* ===== PARAGRAPHS ===== */
        .para {
            margin: 0 0 8px 0;
            font-size: 12.5px;
            line-height: 1.55;
        }

        .para-fr {
            display: block;
            font-size: 12px;
            line-height: 1.45;
        }

        .inline-date {
            font-weight: bold;
            white-space: nowrap;
        }

        /* ===== UNITÉS — number positioned between DE & FR lines ===== */
        .units-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 14px 0;
        }
        .units-table td {
            vertical-align: middle;
            padding: 0;
            font-size: 12.5px;
            line-height: 1.55;
        }
        .units-table td.de-text { width: 30%; white-space: nowrap; padding-right: 8px; }
        .units-table td.units-num {
            width: 12%;
            text-align: center;
            font-size: 19px;
            font-weight: bold;
            white-space: nowrap;
        }
        .units-table td.de-suffix {
            width: 58%;
            padding-left: 8px;
            white-space: nowrap;
        }
        .units-table td.fr-text {
            font-size: 12px;
            white-space: nowrap;
        }

        /* ===== CHECK BOX ===== */
        .check-row {
            margin: 6px 0;
            font-size: 12.5px;
            line-height: 1.5;
        }
        .check-row .check-box {
            margin-right: 10px;
            margin-left: 28px;
        }

        .check-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1.4px solid #1a1a1a;
            position: relative;
            vertical-align: -2px;
        }
        .check-box.checked::before,
        .check-box.checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 19px;
            height: 1.4px;
            background: #1a1a1a;
        }
        .check-box.checked::before { transform: translate(-50%, -50%) rotate(45deg); }
        .check-box.checked::after  { transform: translate(-50%, -50%) rotate(-45deg); }

        /* ===== NIVEAU PERIOD ===== */
        .niveau-period {
            margin: 14px 0 12px 0;
            font-size: 12.5px;
        }

        /* ===== LEVELS ===== */
        .levels-title {
            margin-top: 10px;
            margin-bottom: 8px;
            font-size: 12.5px;
        }
        .levels-row {
            width: auto;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .levels-row td {
            text-align: left;
            font-size: 14px;
            font-weight: bold;
            padding-right: 32px;
            white-space: nowrap;
            vertical-align: middle;
        }
        .levels-row .check-box { margin-right: 6px; vertical-align: middle; }

        /* ===== KURSINFO ===== */
        .kursinfo-title {
            margin-top: 6px;
            margin-bottom: 4px;
            font-size: 12.5px;
        }
        .kursinfo-line {
            margin-bottom: 4px;
            font-size: 12.5px;
        }
        .erfolg-line {
            margin-bottom: 8px;
            font-size: 11.5px;
            line-height: 1.5;
        }
        .erfolg-active {
            font-weight: bold;
            text-decoration: underline;
        }

        .legal {
            font-size: 9.5px;
            color: #444;
            line-height: 1.5;
            margin-bottom: 22px;
        }

        /* ===== ORT / DATUM ===== */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .sig-table td { width: 50%; vertical-align: top; padding: 0; }
        .sig-table td.right { text-align: right; }

        .sig-value {
            font-size: 17px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 1px;
        }
        .sig-label {
            font-size: 10.5px;
            color: #555;
            font-style: italic;
        }

        .kursleitung {
            margin-top: 22px;
            text-align: right;
            font-size: 12px;
            text-decoration: underline;
        }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            left: 55px;
            right: 55px;
            bottom: 28px;
            text-align: center;
            font-size: 9.5px;
            color: #333;
            border-top: 1px solid #999;
            padding-top: 8px;
            line-height: 1.55;
        }
    </style>
</head>
<body>

    {{-- ============ HEADER ============ --}}
    <table class="header">
        <tr>
            <td style="width: 32%;">
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo" alt="GLS">
            </td>
            <td class="header-title">
                Teilnahmebestätigung
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

    {{-- ============ PARTICIPATION ============ --}}
    @php
        $courseEndLabel = $attestation->is_ongoing
            ? 'heute'
            : $attestation->course_end_date?->format('d-m-Y');
    @endphp
    <div class="para">
        hat in der Zeit <u>vom</u> / a participé <span class="inline-date">{{ $attestation->course_start_date?->format('d-m-Y') }}</span> bis <span class="inline-date">{{ $courseEndLabel }}</span>
        an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.
        <span class="para-fr">A un cours de la langue Allemande au GLS Sprachenzentrum.</span>
    </div>

    {{-- ============ UNITS — DE & FR with number centered between ============ --}}
    <table class="units-table">
        <tr>
            <td class="de-text">Der Kurs umfasste</td>
            <td class="units-num" rowspan="2">{{ $attestation->units_45min }}</td>
            <td class="de-suffix">Unterrichtseinheiten 45 Minuten.</td>
        </tr>
        <tr>
            <td class="fr-text">Le cours comprenait</td>
            <td class="fr-text">unités de cours de 45 minutes.</td>
        </tr>
    </table>

    {{-- ============ FRAIS ============ --}}
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'full' ? 'checked' : '' }}"></span>Die Kursgebühren wurden vollständig entrichtet.
    </div>
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'partial' ? 'checked' : '' }}"></span>Die Kursgebühren wurden teilweise entrichtet.
    </div>

    {{-- ============ NIVEAU PERIOD ============ --}}
    @php
        $niveauEndLabel = $attestation->is_ongoing
            ? 'heute'
            : $attestation->niveau_end_date?->format('d-m-Y');
    @endphp
    <div class="niveau-period">
        Das Niveau <u>beginnt</u> von <span class="inline-date">{{ $attestation->niveau_start_date?->format('d-m-Y') }}</span> bis <span class="inline-date">{{ $niveauEndLabel }}</span>
    </div>

    {{-- ============ LEVELS ============ --}}
    <div class="levels-title">
        <u>Referenzniveau des Kurses</u> &nbsp;/ Niveau de référence européen :
    </div>

    @php $levels = ['A1', 'A2', 'B1', 'B2']; @endphp

    <table class="levels-row">
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
        <u>Stufe</u> {{ $attestation->stufe_index }} von {{ $attestation->stufe_total }},
        Niveau {{ $attestation->stufe_index }} de {{ $attestation->stufe_total }}
    </div>

    @php $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen']; @endphp

    <div class="erfolg-line">
        @foreach($erfolgList as $i => $opt)
            <span class="{{ $attestation->erfolg === $opt ? 'erfolg-active' : '' }}">{{ $opt }}</span>@if($i < count($erfolgList) - 1) , @endif
        @endforeach
        .
    </div>

    @php
        $defaultMethodology = "L'appréciation des résultats obtenus en cours est faite par les enseignant(e)s . Cette attestation de présence n'est pas un diplôme . Le barème comprend 4 appréciations : très bien , bien , assez bien , participation régulière";
        $methodologyText = trim((string) ($attestation->methodology_text ?? '')) !== ''
            ? $attestation->methodology_text
            : $defaultMethodology;
    @endphp
    <div class="legal">{!! nl2br(e($methodologyText)) !!}</div>

    {{-- ============ ORT / DATUM / KURSLEITUNG ============ --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                <div class="sig-label">Ort , Lieu</div>
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
        Adresse : Rue Halima Saadia N12 Lgherabliva en face la pharmacie centrale près de station tram Divar<br>
        Tel : 0808540625/0622996078 , Email : gls.sprachenzentrum.sale@gmail.com
    </div>

</body>
</html>
