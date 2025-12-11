<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 40px;
        }

        .logo {
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .title {
            text-align: center;
            letter-spacing: 5px;
            font-size: 26px;
            margin-top: 20px;
        }

        .title2 {
            text-align: center;
            letter-spacing: 4px;
            font-size: 20px;
            margin-bottom: 40px;
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
            border-bottom: 1px solid #777;
            width: 180px;
            margin-bottom: 5px;
        }

        .label {
            font-size: 11px;
            color: #555;
        }

        .section-title {
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .score-left {
            font-size: 12px;
        }

        .score-right {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        .result-row td {
            padding-top: 20px;
            font-size: 14px;
        }

        .signature-block {
            width: 50%;
            text-align: center;
            margin-top: 50px;
            font-size: 11px;
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

    <!-- HEADER -->
    <table>
        <tr>
            <td>
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="logo">
            </td>
            <td style="text-align:right; font-size:11px;">
                GLS – Language & Integration Center
            </td>
        </tr>
    </table>

    <!-- TITLES -->
    <div class="title">ZERTIFIKAT</div>
    <div class="title2">{{ $certificate->exam_level }}</div>

    <!-- PERSONAL INFORMATION -->
    <table style="margin-bottom: 40px;">
        <tr>
            <td class="col">
                <div class="col-title">{{ $certificate->last_name }}</div>
                <div class="line"></div>
                <div class="label">Name</div>

                <br>

                <div>{{ $certificate->birth_date->format('d.m.Y') }}</div>
                <div class="line"></div>
                <div class="label">Geburtsdatum</div>
            </td>

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


    <!-- WRITTEN EXAM -->
    <div class="section-title">Schriftliche Prüfung</div>

    <table>
        <tr>
            <td class="score-left"><strong>Gesamt</strong></td>
            <td class="score-right">{{ $certificate->written_total }} / {{ $certificate->written_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Leseverstehen</td>
            <td class="score-right">{{ $certificate->reading_score }} / {{ $certificate->reading_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Sprachbausteine</td>
            <td class="score-right">{{ $certificate->grammar_score }} / {{ $certificate->grammar_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Hörverstehen</td>
            <td class="score-right">{{ $certificate->listening_score }} / {{ $certificate->listening_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Schriftlicher Ausdruck</td>
            <td class="score-right">{{ $certificate->writing_score }} / {{ $certificate->writing_max }}</td>
        </tr>
    </table>


    <!-- ORAL EXAM -->
    <div class="section-title">Mündliche Prüfung</div>

    <table>
        <tr>
            <td class="score-left"><strong>Gesamt</strong></td>
            <td class="score-right">{{ $certificate->oral_total }} / {{ $certificate->oral_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Präsentation</td>
            <td class="score-right">{{ $certificate->presentation_score }} / {{ $certificate->presentation_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Diskussion</td>
            <td class="score-right">{{ $certificate->discussion_score }} / {{ $certificate->discussion_max }}</td>
        </tr>
        <tr>
            <td class="score-left">• Problemlösung</td>
            <td class="score-right">{{ $certificate->problemsolving_score }} / {{ $certificate->problemsolving_max }}</td>
        </tr>
    </table>


    <!-- RESULT -->
    <table class="result-row">
        <tr>
            <td class="score-left"><strong>Ergebnis</strong></td>
            <td class="score-right">{{ $certificate->final_result }}</td>
        </tr>
    </table>


    <!-- DATES -->
    <table style="margin-top: 30px;">
        <tr>
            <td class="score-left">Datum der Prüfung</td>
            <td class="score-right">{{ $certificate->exam_date->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="score-left">Datum der Ausstellung</td>
            <td class="score-right">{{ $certificate->issue_date->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="score-left">Teilnehmernummer</td>
            <td class="score-right">{{ $certificate->certificate_number }}</td>
        </tr>
    </table>


    <!-- SIGNATURES -->
    <table style="margin-top: 50px;">
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
