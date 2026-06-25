<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2c2c2c;
            margin: 0;
            padding: 45px 55px 40px 55px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        /* HEADER */
        .header-logo {
            width: 140px;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
            color: #555;
            vertical-align: middle;
            font-style: italic;
        }

        /* TITLES */
        .title-main {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 12px;
            margin-top: 25px;
            margin-bottom: 4px;
            color: #1a1a1a;
        }

        .title-level {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 8px;
            margin-bottom: 30px;
            color: #1a1a1a;
        }

        /* PERSONAL INFO */
        .info-table {
            margin-bottom: 25px;
        }

        .info-table td {
            width: 50%;
            padding-right: 30px;
        }

        .info-table td:last-child {
            padding-right: 0;
            padding-left: 30px;
        }

        .field-label {
            font-size: 9.5px;
            color: #888;
            margin-top: 2px;
            margin-bottom: 10px;
            font-style: italic;
        }

        .field-value {
            font-size: 13px;
            font-weight: bold;
            padding-bottom: 4px;
            border-bottom: 1px solid #999;
            margin-bottom: 2px;
        }

        .field-value-normal {
            font-size: 12px;
            font-weight: bold;
            padding-bottom: 4px;
            border-bottom: 1px solid #999;
            margin-bottom: 2px;
        }

        /* SCORE SECTIONS */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 16px;
            margin-bottom: 5px;
            color: #1a1a1a;
            width: 55%;
            margin-left: auto;
            margin-right: auto;
        }

        .score-table {
            width: 55%;
            margin-left: auto;
            margin-right: auto;
        }

        .score-table td {
            padding: 2px 0;
            font-size: 11px;
        }

        .score-label {
            color: #333;
            width: 200px;
        }

        .score-val {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
            width: 40px;
        }

        .score-sep {
            text-align: center;
            white-space: nowrap;
            color: #555;
            width: 20px;
            padding: 0 2px;
        }

        .score-max {
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
            width: 40px;
        }

        .score-gesamt td {
            padding-top: 0;
            padding-bottom: 4px;
            font-size: 12px;
        }

        .score-gesamt .score-val {
            font-size: 13px;
        }

        .score-gesamt .score-max {
            font-size: 13px;
        }

        .score-item .score-label {
            padding-left: 20px;
        }

        .score-item .score-label::before {
            content: "\2022\00a0\00a0";
        }

        /* RESULT */
        .result-section {
            width: 55%;
            margin: 16px auto 0 auto;
        }

        .result-section td {
            padding: 3px 0;
            font-size: 12px;
        }

        .result-label {
            font-weight: bold;
            width: 200px;
        }

        .result-value {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        /* DATES */
        .dates-section {
            width: 55%;
            margin: 16px auto 0 auto;
        }

        .dates-section td {
            padding: 2px 0;
            font-size: 10.5px;
        }

        .dates-section .date-label {
            color: #555;
            width: 200px;
        }

        .dates-section .date-value {
            text-align: right;
            font-weight: bold;
        }

        /* SIGNATURES */
        .sig-table {
            margin-top: 35px;
        }

        .sig-cell {
            width: 50%;
            text-align: center;
            font-size: 10px;
            color: #555;
            font-style: italic;
        }

        .sig-line {
            border-top: 1px solid #aaa;
            width: 65%;
            margin: 0 auto 5px auto;
        }

        /* FOOTER */
        .footer-address {
            position: fixed;
            left: 55px;
            bottom: 35px;
            font-size: 8px;
            color: #888;
            line-height: 1.3;
        }

        /* QR CODE */
        .qr-block {
            position: fixed;
            right: 40px;
            bottom: 35px;
            text-align: center;
        }

        .qr-block img {
            width: 100px;
            height: 100px;
        }

        .qr-block .qr-text {
            font-size: 8px;
            color: #888;
            margin-top: 4px;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 300px;
            height: auto;
            margin-top: -200px;
            margin-left: -150px;
            opacity: 0.04;
            z-index: -1;
        }

    </style>
</head>

<body>

    <!-- WATERMARK -->
    <img src="{{ public_path('assets/images/logo/gls.png') }}" class="watermark">

    <!-- HEADER -->
    <table>
        <tr>
            <td>
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo">
            </td>
            <td class="header-right">
                GLS-Language & Integration Center
            </td>
        </tr>
    </table>

    <!-- TITLES -->
    <div class="title-main">ZERTIFIKAT</div>
    <div class="title-level">{{ strtoupper($certificate->exam_level) }}</div>

    <!-- PERSONAL INFO -->
    <table class="info-table">
        <tr>
            <td>
                <div class="field-value">{{ $certificate->last_name }}</div>
                <div class="field-label">Name</div>

                <div class="field-value-normal">{{ $certificate->birth_date->format('d/m/Y') }}</div>
                <div class="field-label">Geburtsdatum</div>
            </td>
            <td>
                <div class="field-value">{{ $certificate->first_name }}</div>
                <div class="field-label">Vorname</div>

                <div class="field-value-normal">{{ strtoupper($certificate->birth_place) }}</div>
                <div class="field-label">Geburtsort</div>
            </td>
        </tr>
    </table>


    @if ($certificate->isA1() || $certificate->isA2() || $certificate->isB1())
        {{-- A1 / A2 / B1 SCORES (same 4-field layout, stored max values differ) --}}

        <div class="section-title">Prüfungsergebnisse</div>

        <table class="score-table">
            <tr class="score-gesamt">
                <td class="score-label"><strong>Gesamt</strong></td>
                <td class="score-val">{{ $certificate->total_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->total_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Lesen</td>
                <td class="score-val">{{ $certificate->reading_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->reading_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Hören</td>
                <td class="score-val">{{ $certificate->listening_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->listening_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Schreiben</td>
                <td class="score-val">{{ $certificate->writing_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->writing_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Sprechen</td>
                <td class="score-val">{{ $certificate->speaking_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->speaking_max }}</td>
            </tr>
        </table>
    @else
        {{-- B2 SCORES --}}

        <!-- WRITTEN EXAM -->
        <div class="section-title">Schriftliche Prüfung</div>

        <table class="score-table">
            <tr class="score-gesamt">
                <td class="score-label"><strong>Gesamt</strong></td>
                <td class="score-val">{{ $certificate->written_total }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->written_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Leseverstehen</td>
                <td class="score-val">{{ $certificate->reading_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->reading_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Sprachbausteine</td>
                <td class="score-val">{{ $certificate->grammar_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->grammar_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Hörverstehen</td>
                <td class="score-val">{{ $certificate->listening_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->listening_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Schriftlicher Ausdruck</td>
                <td class="score-val">{{ $certificate->writing_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->writing_max }}</td>
            </tr>
        </table>

        <!-- ORAL EXAM -->
        <div class="section-title">Mündliche Prüfung</div>

        <table class="score-table">
            <tr class="score-gesamt">
                <td class="score-label"><strong>Gesamt</strong></td>
                <td class="score-val">{{ $certificate->oral_total }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->oral_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Präsentation</td>
                <td class="score-val">{{ $certificate->presentation_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->presentation_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Diskussion</td>
                <td class="score-val">{{ $certificate->discussion_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->discussion_max }}</td>
            </tr>
            <tr class="score-item">
                <td class="score-label">Problemlösung</td>
                <td class="score-val">{{ $certificate->problemsolving_score }}</td>
                <td class="score-sep">/</td>
                <td class="score-max">{{ $certificate->problemsolving_max }}</td>
            </tr>
        </table>
    @endif


    <!-- RESULT -->
    <table class="result-section">
        @if ($certificate->ergebnis_note)
            <tr>
                <td class="result-label">Ergebnis</td>
                <td class="result-value">{{ $certificate->ergebnis_note }}</td>
            </tr>
        @endif
        <tr>
            <td class="result-label">Note</td>
            <td class="result-value">{{ $certificate->final_result }}</td>
        </tr>
    </table>


    <!-- DATES -->
    <table class="dates-section">
        <tr>
            <td class="date-label">Datum der Prüfung</td>
            <td class="date-value">{{ $certificate->exam_date->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="date-label">Datum der Ausstellung</td>
            <td class="date-value">{{ $certificate->issue_date->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="date-label">Teilnehmernummer</td>
            <td class="date-value">{{ $certificate->certificate_number }}</td>
        </tr>
    </table>


    <!-- SIGNATURES -->
    <table class="sig-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-line"></div>
                Geschäftsführer
            </td>
            <td class="sig-cell">
                <div class="sig-line"></div>
                Prüfungszentrum
            </td>
        </tr>
    </table>

    <!-- FOOTER ADDRESS -->
    <div class="footer-address">
        Avenue Fal Ould Oumeir, Immeuble 77, 1er étage numéro 1, Rabat 10000
    </div>

    <!-- QR CODE -->
    @if (!empty($qrCodeBase64))
        <div class="qr-block">
            <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR">
            <div class="qr-text">Scan to download</div>
        </div>
    @endif

</body>

</html>
