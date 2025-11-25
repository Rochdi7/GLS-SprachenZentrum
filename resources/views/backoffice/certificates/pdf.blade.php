<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 50px;
        }

        .logo {
            width: 120px;
        }

        .header-row {
            width: 100%;
        }

        .title {
            text-align: center;
            letter-spacing: 5px;
            font-size: 24px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .title2 {
            text-align: center;
            letter-spacing: 5px;
            font-size: 20px;
            margin-bottom: 50px;
        }

        .two-cols {
            width: 100%;
            margin-bottom: 50px;
        }

        .col {
            width: 50%;
            vertical-align: top;
            font-size: 13px;
        }

        .col-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .line {
            border-bottom: 1px solid #aaa;
            width: 150px;
            margin-bottom: 5px;
        }

        .label {
            font-size: 11px;
            color: #666;
        }

        .section-title {
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .score-row {
            width: 100%;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .score-left {
            width: 60%;
            color: #555;
        }

        .score-right {
            width: 40%;
            text-align: right;
            font-weight: bold;
        }

        .result-row {
            width: 100%;
            margin-top: 40px;
            font-size: 14px;
        }

        .footer-row {
            width: 100%;
            margin-top: 60px;
            font-size: 11px;
        }

        .signature-block {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #aaa;
            width: 70%;
            margin-left: auto;
            margin-right: auto;
        }

    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="header-row">
        <tr>
            <td>
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="logo">
            </td>
            <td style="text-align:right; font-size:11px;">
                GLS – Language & Integration Center
            </td>
        </tr>
    </table>

    {{-- TITLE --}}
    <div class="title">ZERTIFIKAT</div>
    <div class="title2">{{ $certificate->exam_level }}</div>


    {{-- TWO COLUMNS: LEFT + RIGHT --}}
    <table class="two-cols">
        <tr>
            {{-- LEFT Person --}}
            <td class="col">
                <div class="col-title">{{ $certificate->last_name }}</div>
                <div class="line"></div>
                <div class="label">Name</div>

                <br>

                <div>{{ \Carbon\Carbon::parse($certificate->birth_date)->format('m/d/Y') }}</div>
                <div class="line"></div>
                <div class="label">Geburtsdatum</div>
            </td>

            {{-- RIGHT Person --}}
            <td class="col">
                <div class="col-title">{{ $certificate->first_name }}</div>
                <div class="line"></div>
                <div class="label">Vorname</div>

                <br>

                <div>{{ strtoupper($certificate->birth_place) }}</div>
                <div class="line"></div>
                <div class="label">Geburtsort</div>
            </td>
        </tr>
    </table>

    {{-- SCHRIFTLICHE PRÜFUNG --}}
    <div class="section-title">Schriftliche Prüfung</div>

    <table class="score-row">
        <tr>
            <td class="score-left"><strong>Gesamt</strong></td>
            <td class="score-right">{{ $certificate->written_total }} / 225</td>
        </tr>
    </table>

    <table>
        <tr><td class="score-left">• Leseverstehen</td><td class="score-right">{{ $certificate->reading_score }} / 75</td></tr>
        <tr><td class="score-left">• Sprachbausteine</td><td class="score-right">{{ $certificate->grammar_score }} / 30</td></tr>
        <tr><td class="score-left">• Hörverstehen</td><td class="score-right">{{ $certificate->listening_score }} / 75</td></tr>
        <tr><td class="score-left">• Schriftlicher Ausdruck</td><td class="score-right">{{ $certificate->writing_score }} / 45</td></tr>
    </table>

    {{-- MÜNDLICHE PRÜFUNG --}}
    <div class="section-title">Mündliche Prüfung</div>

    <table class="score-row">
        <tr>
            <td class="score-left"><strong>Gesamt</strong></td>
            <td class="score-right">{{ $certificate->oral_total }} / 75</td>
        </tr>
    </table>

    <table>
        <tr><td class="score-left">• Präsentation</td><td class="score-right">{{ $certificate->presentation_score }} / 25</td></tr>
        <tr><td class="score-left">• Diskussion</td><td class="score-right">{{ $certificate->discussion_score }} / 25</td></tr>
        <tr><td class="score-left">• Problemlösung</td><td class="score-right">{{ $certificate->problemsolving_score }} / 25</td></tr>
    </table>

    {{-- RESULT --}}
    <table class="result-row">
        <tr>
            <td class="score-left"><strong>Ergebnis</strong></td>
            <td class="score-right">{{ $certificate->final_result }}</td>
        </tr>
    </table>

    {{-- EXAM DATES --}}
    <table style="margin-top:45px;">
        <tr>
            <td class="score-left">Datum der Prüfung</td>
            <td class="score-right">{{ \Carbon\Carbon::parse($certificate->exam_date)->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="score-left">Datum der Ausstellung</td>
            <td class="score-right">{{ \Carbon\Carbon::parse($certificate->issue_date)->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="score-left">Teilnehmernummer</td>
            <td class="score-right">{{ $certificate->certificate_number }}</td>
        </tr>
    </table>

    {{-- FOOTER SIGNATURE --}}
    <table class="footer-row">
        <tr>
            <td class="signature-block">
                <div class="signature-line"></div>
                Geschäftsführer
            </td>

            <td class="signature-block">
                <div class="signature-line"></div>
                Prüfungszentrum
            </td>
        </tr>
    </table>

</body>
</html>
