<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teilnahmebestätigung — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            margin: 0;
            padding: 35px 50px 130px 50px;
            line-height: 1.45;
        }

        /* ----------------- HEADER ----------------- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-logo {
            width: 150px;
            height: auto;
        }

        .header-title {
            text-align: right;
            font-size: 30px;
            font-weight: bold;
            color: #1a1a1a;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        /* ----------------- FIELD BLOCKS ----------------- */
        .field-block {
            margin-bottom: 12px;
        }

        .field-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
        }

        .field-value-medium {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
        }

        .field-label {
            font-size: 11px;
            color: #555;
            font-style: italic;
        }

        /* Two-column layout */
        .two-cols {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .two-cols td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px 0 0;
        }

        .two-cols td.right {
            padding: 0 0 0 20px;
        }

        /* ----------------- SENTENCES ----------------- */
        .paragraph {
            margin-top: 14px;
            margin-bottom: 14px;
            font-size: 12.5px;
            line-height: 1.55;
        }

        .paragraph .fr {
            display: block;
            font-size: 12px;
        }

        .inline-date,
        .inline-units {
            font-weight: bold;
            display: inline-block;
            min-width: 90px;
            text-align: center;
            padding: 0 8px;
        }

        .inline-units {
            min-width: 60px;
            font-size: 16px;
        }

        /* ----------------- CHECK ROW ----------------- */
        .check-row {
            margin: 8px 0;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .check-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1.4px solid #1a1a1a;
            position: relative;
            vertical-align: middle;
            margin-right: 10px;
            margin-left: 30px;
        }

        .check-box.checked::before,
        .check-box.checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 22px;
            height: 1.6px;
            background: #1a1a1a;
        }

        .check-box.checked::before {
            transform: translate(-50%, -50%) rotate(45deg);
        }

        .check-box.checked::after {
            transform: translate(-50%, -50%) rotate(-45deg);
        }

        /* ----------------- LEVEL BOXES ----------------- */
        .levels-row {
            margin: 10px 0 14px 0;
            text-align: left;
        }

        .level-cell {
            display: inline-block;
            margin-right: 24px;
            font-size: 14px;
            font-weight: bold;
        }

        /* ----------------- KURSINFO ----------------- */
        .kursinfo {
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .erfolg-line {
            margin-bottom: 4px;
            font-size: 12px;
            line-height: 1.6;
        }

        .erfolg-active {
            font-weight: bold;
            text-decoration: underline;
        }

        .legal {
            font-size: 9.5px;
            color: #444;
            line-height: 1.5;
            margin-top: 4px;
            margin-bottom: 22px;
        }

        /* ----------------- SIGNATURE ----------------- */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .sig-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px;
        }

        .sig-value {
            font-size: 15px;
            font-weight: bold;
        }

        .sig-label {
            font-size: 11px;
            color: #555;
            font-style: italic;
        }

        .sig-right {
            text-align: right;
        }

        .sig-kursleitung {
            margin-top: 30px;
            text-align: right;
            font-style: italic;
            font-size: 12px;
            text-decoration: underline;
        }

        /* ----------------- FOOTER ----------------- */
        .footer {
            position: fixed;
            left: 50px;
            right: 50px;
            bottom: 30px;
            text-align: center;
            font-size: 10px;
            color: #333;
            border-top: 1px solid #999;
            padding-top: 8px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    {{-- ======================================== --}}
    {{--                HEADER                    --}}
    {{-- ======================================== --}}
    <table class="header-table">
        <tr>
            <td style="width: 35%;">
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo" alt="GLS">
            </td>
            <td class="header-title" style="width: 65%;">
                Teilnahmebestätigung
            </td>
        </tr>
    </table>

    {{-- ======================================== --}}
    {{--           ÉTUDIANT — NOM/PRÉNOM          --}}
    {{-- ======================================== --}}
    <div class="field-block">
        <div class="field-value">{{ strtoupper($attestation->last_name) }} {{ strtoupper($attestation->first_name) }}</div>
        <div class="field-label">Name, Vorname / Nom, Prénom</div>
    </div>

    {{-- ======================================== --}}
    {{--    DATE + LIEU DE NAISSANCE              --}}
    {{-- ======================================== --}}
    <table class="two-cols">
        <tr>
            <td>
                <div class="field-value-medium">{{ $attestation->birth_date->format('d/m/Y') }}</div>
                <div class="field-label">geboren am / Date de Naissance</div>
            </td>
            <td class="right">
                <div class="field-value-medium">{{ strtoupper($attestation->birth_place) }}</div>
                <div class="field-label">geboren in / Lieu de Naissance</div>
            </td>
        </tr>
    </table>

    {{-- ======================================== --}}
    {{--   PARTICIPATION (vom .. bis ..)          --}}
    {{-- ======================================== --}}
    <div class="paragraph">
        hat in der Zeit <u>vom</u> / a participé
        <span class="inline-date">{{ $attestation->course_start_date->format('d-m-Y') }}</span>
        bis
        <span class="inline-date">{{ $attestation->course_end_date->format('d-m-Y') }}</span>
        <br>
        an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.
        <span class="fr">A un cours de la langue Allemagne au GLS Sprachenzentrum.</span>
    </div>

    {{-- ======================================== --}}
    {{--   UNITÉS DE COURS                        --}}
    {{-- ======================================== --}}
    <div class="paragraph">
        Der Kurs umfasste
        <span class="inline-units">{{ $attestation->units_45min }}</span>
        Unterrichtseinheiten 45 Minuten.
        <span class="fr">Le cours comprenait <strong>{{ $attestation->units_45min }}</strong> unités de cours de 45 minutes.</span>
    </div>

    {{-- ======================================== --}}
    {{--   FRAIS — 2 cases                        --}}
    {{-- ======================================== --}}
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'full' ? 'checked' : '' }}"></span>
        Die Kursgebühren wurden vollständig entrichtet.
    </div>
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'partial' ? 'checked' : '' }}"></span>
        Die Kursgebühren wurden teilweise entrichtet.
    </div>

    {{-- ======================================== --}}
    {{--   PÉRIODE DU NIVEAU                      --}}
    {{-- ======================================== --}}
    <div class="paragraph">
        Das Niveau beginnt von
        <span class="inline-date">{{ $attestation->niveau_start_date->format('d-m-Y') }}</span>
        bis
        <span class="inline-date">{{ $attestation->niveau_end_date->format('d-m-Y') }}</span>
    </div>

    {{-- ======================================== --}}
    {{--   NIVEAU DE RÉFÉRENCE                    --}}
    {{-- ======================================== --}}
    <div style="font-size: 13px; margin-bottom: 6px;">
        <u>Referenzniveau des Kurses</u> / Niveau de référence européen :
    </div>

    @php
        $levels = ['A1', 'A2', 'B1', 'B2', 'C1'];
    @endphp

    <div class="levels-row">
        @foreach($levels as $lvl)
            <span class="level-cell">
                <span class="check-box {{ $attestation->level === $lvl ? 'checked' : '' }}"></span>
                {{ $lvl }}
            </span>
        @endforeach
    </div>

    {{-- ======================================== --}}
    {{--   KURSINFO                                --}}
    {{-- ======================================== --}}
    <div style="margin-top: 8px; font-size: 13px;">
        Kursinfo / Information sur le cours :
    </div>
    <div class="kursinfo">
        Stufe <strong>{{ $attestation->stufe_index }}</strong> von <strong>{{ $attestation->stufe_total }}</strong>,
        Niveau <strong>{{ $attestation->stufe_index }}</strong> de <strong>{{ $attestation->stufe_total }}</strong>
    </div>

    @php
        $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen'];
    @endphp

    <div class="erfolg-line">
        @foreach($erfolgList as $i => $opt)
            <span class="{{ $attestation->erfolg === $opt ? 'erfolg-active' : '' }}">{{ $opt }}</span>@if($i < count($erfolgList) - 1) , @endif
        @endforeach
        .
    </div>

    <div class="legal">
        L'appréciation des résultats obtenus en cours est faite par les enseignant(e)s. Cette attestation de présence n'est pas un diplôme. Le barème comprend 4 appréciations : très bien, bien, assez bien, participation régulière.
    </div>

    {{-- ======================================== --}}
    {{--   ORT — DATUM — KURSLEITUNG               --}}
    {{-- ======================================== --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                <div class="sig-label">Ort / Lieu</div>
            </td>
            <td class="sig-right">
                <div class="sig-value">{{ $attestation->issue_date->format('d.m.Y') }}</div>
                <div class="sig-label">Datum / Date</div>

                <div class="sig-kursleitung">Kursleitung :</div>
            </td>
        </tr>
    </table>

    {{-- ======================================== --}}
    {{--                FOOTER                    --}}
    {{-- ======================================== --}}
    <div class="footer">
        Adresse : Rue Halima Saadia N12 Lgherabliva en face la pharmacie centrale près de station tram Divar<br>
        Tel : 0808540625 / 0622996078 , Email : gls.sprachenzentrum.sale@gmail.com
    </div>

</body>
</html>
