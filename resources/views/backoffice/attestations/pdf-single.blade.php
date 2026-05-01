@php
    /**
     * Single-language attestation PDF — pixel-stable layout for DomPDF.
     * $lang in ['de', 'fr', 'en']
     */

    $T = [
        'de' => [
            'title'           => 'Teilnahmebestätigung',
            'name_label'      => 'Name, Vorname',
            'birth_date_lbl'  => 'geboren am',
            'birth_place_lbl' => 'geboren in',
            'participation'   => 'hat in der Zeit vom :start bis :end an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.',
            'units_label'     => 'Der Kurs umfasste',
            'units_suffix'    => 'Unterrichtseinheiten zu je 45 Minuten.',
            'fees_full'       => 'Die Kursgebühren wurden vollständig entrichtet.',
            'fees_partial'    => 'Die Kursgebühren wurden teilweise entrichtet.',
            'niveau_period'   => 'Das Niveau beginnt von :start bis :end',
            'level_title'     => 'Referenzniveau des Kurses :',
            'kursinfo_title'  => 'Kursinfo :',
            'kursinfo_line'   => 'Stufe :idx von :total',
            'legal'           => 'Die Bewertung der im Kurs erzielten Ergebnisse erfolgt durch die Lehrkräfte. Diese Teilnahmebestätigung ist kein Diplom. Die Bewertungsskala umfasst 4 Stufen: sehr gut, gut, befriedigend, regelmäßige Teilnahme.',
            'place_label'     => 'Ort',
            'date_label'      => 'Datum',
            'signature'       => 'Kursleitung :',
        ],
        'fr' => [
            'title'           => 'Attestation de participation',
            'name_label'      => 'Nom, Prénom',
            'birth_date_lbl'  => 'Date de naissance',
            'birth_place_lbl' => 'Lieu de naissance',
            'participation'   => 'a participé du :start au :end à un cours de langue allemande au GLS Sprachenzentrum.',
            'units_label'     => 'Le cours comprenait',
            'units_suffix'    => "unités d'enseignement de 45 minutes chacune.",
            'fees_full'       => 'Les frais de cours ont été intégralement payés.',
            'fees_partial'    => 'Les frais de cours ont été partiellement payés.',
            'niveau_period'   => 'Le niveau commence du :start au :end',
            'level_title'     => 'Niveau de référence européen :',
            'kursinfo_title'  => 'Information sur le cours :',
            'kursinfo_line'   => 'Niveau :idx sur :total',
            'legal'           => "L'appréciation des résultats obtenus en cours est faite par les enseignant(e)s. Cette attestation de présence n'est pas un diplôme. Le barème comprend 4 appréciations : très bien, bien, assez bien, participation régulière.",
            'place_label'     => 'Lieu',
            'date_label'      => 'Date',
            'signature'       => 'Direction du cours :',
        ],
        'en' => [
            'title'           => 'Certificate of Attendance',
            'name_label'      => 'Last name, First name',
            'birth_date_lbl'  => 'Date of birth',
            'birth_place_lbl' => 'Place of birth',
            'participation'   => 'attended a German language course at GLS Sprachenzentrum from :start to :end.',
            'units_label'     => 'The course consisted of',
            'units_suffix'    => 'teaching units of 45 minutes each.',
            'fees_full'       => 'Course fees have been paid in full.',
            'fees_partial'    => 'Course fees have been paid in part.',
            'niveau_period'   => 'The level runs from :start to :end',
            'level_title'     => 'European reference level:',
            'kursinfo_title'  => 'Course information:',
            'kursinfo_line'   => 'Level :idx of :total',
            'legal'           => 'Course results are assessed by the teachers. This attendance certificate is not a diploma. The grading scale comprises 4 grades: very good, good, satisfactory, regular attendance.',
            'place_label'     => 'Place',
            'date_label'      => 'Date',
            'signature'       => 'Course director:',
        ],
    ];

    $erfolgTranslations = [
        'de' => ['Erfolg' => 'sehr gut', 'mit gutem Erfolg' => 'gut', 'mit Erfolg' => 'befriedigend', 'teilgenommen' => 'regelmäßige Teilnahme'],
        'fr' => ['Erfolg' => 'très bien', 'mit gutem Erfolg' => 'bien', 'mit Erfolg' => 'assez bien', 'teilgenommen' => 'participation régulière'],
        'en' => ['Erfolg' => 'very good', 'mit gutem Erfolg' => 'good', 'mit Erfolg' => 'satisfactory', 'teilgenommen' => 'regular attendance'],
    ];

    $t = $T[$lang] ?? $T['de'];
    $erfolgT = $erfolgTranslations[$lang] ?? $erfolgTranslations['de'];

    $todayWord = ['de' => 'heute', 'fr' => "aujourd'hui", 'en' => 'today'][$lang] ?? 'heute';

    $startCourse = $attestation->course_start_date?->format('d-m-Y') ?? '—';
    $endCourse   = $attestation->is_ongoing ? $todayWord : ($attestation->course_end_date?->format('d-m-Y') ?? '—');
    $startNiveau = $attestation->niveau_start_date?->format('d-m-Y') ?? '—';
    $endNiveau   = $attestation->is_ongoing ? $todayWord : ($attestation->niveau_end_date?->format('d-m-Y') ?? '—');

    $participationText = strtr($t['participation'], [':start' => $startCourse, ':end' => $endCourse]);
    $niveauPeriodText  = strtr($t['niveau_period'], [':start' => $startNiveau, ':end' => $endNiveau]);
    $kursinfoLine      = strtr($t['kursinfo_line'], [':idx' => $attestation->stufe_index, ':total' => $attestation->stufe_total]);

    $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen'];
    $levels = ['A1', 'A2', 'B1', 'B2'];

    $methodologyText = trim((string) ($attestation->methodology_text ?? '')) !== ''
        ? $attestation->methodology_text
        : $t['legal'];

    $site = $attestation->group?->site;
    $footerAddress = $site?->address ?? 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech.';
    $footerPhone   = $site?->phone   ?? '0808540625 / 0622996078';
    $footerEmail   = $site?->email   ?? 'info@glssprachenzentrum.ma';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $t['title'] }} — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page { size: A4 portrait; margin: 35px 40px 95px 40px; }
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
        table.header td.logo-cell { width: 140px; }
        table.header td.title-cell { padding-left: 22px; text-align: left; }
        .header-logo { width: 120px; height: auto; display: block; }
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
        .block {
            margin-bottom: 14px;
            font-size: 10.5pt;
            line-height: 1.6;
        }

        /* ===== UNITS — tighter, balanced grid ===== */
        table.units {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 16px 0;
            table-layout: fixed;
        }
        table.units td { padding: 0; vertical-align: middle; font-size: 10.5pt; }
        table.units td.label-col { width: 32%; padding-right: 8px; }
        table.units td.num-col {
            width: 14%;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            white-space: nowrap;
        }
        table.units td.suffix-col {
            width: 54%;
            padding-left: 8px;
        }

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
        .niveau-period {
            margin: 18px 0 6px;
            font-size: 10.5pt;
        }

        /* ===== LEVELS — left-aligned, tighter spacing ===== */
        .levels-title {
            margin: 10px 0 8px;
            font-size: 10.5pt;
            text-decoration: underline;
        }
        table.levels {
            border-collapse: collapse;
            margin: 0;
        }
        table.levels td {
            font-size: 11.5pt;
            font-weight: bold;
            padding: 0 28px 0 0;
            white-space: nowrap;
            vertical-align: middle;
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

        /* ===== SIGNATURE — centred, stamp area on the right ===== */
        .sig-wrap { text-align: center; margin-top: 28px; }
        table.sig {
            border-collapse: collapse;
            display: inline-table;
            margin: 0 auto;
        }
        table.sig td { vertical-align: top; padding: 0; }
        table.sig td.left  { text-align: center; padding-right: 60px; }
        table.sig td.right { text-align: center; }
        .sig-value {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.2;
            white-space: nowrap;
        }
        .sig-label { font-size: 8.5pt; color: #666; font-style: italic; margin-top: 2px; }

        .signature {
            margin-top: 22px;
            text-align: center;
            font-size: 10pt;
            text-decoration: underline;
        }

        /* ===== FOOTER ===== */
        .footer {
            position: fixed;
            left: 40px; right: 40px;
            bottom: 25px;
            text-align: center;
            font-size: 8pt;
            color: #444;
            border-top: 1px solid #aaa;
            padding-top: 6px;
            line-height: 1.55;
        }
        .footer .addr { text-decoration: underline; font-weight: 600; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo" alt="GLS">
            </td>
            <td class="title-cell">
                <span class="header-title">{{ $t['title'] }}</span>
            </td>
        </tr>
    </table>

    <div class="name-block">
        <div class="name-value">{{ strtoupper($attestation->last_name) }} {{ strtoupper($attestation->first_name) }}</div>
        <div class="name-label">{{ $t['name_label'] }}</div>
    </div>

    <table class="birth">
        <tr>
            <td>
                <div class="birth-value">{{ $attestation->birth_date?->format('d/m/Y') }}</div>
                <div class="birth-label">{{ $t['birth_date_lbl'] }}</div>
            </td>
            <td class="right">
                <div class="birth-value">{{ strtoupper($attestation->birth_place) }}</div>
                <div class="birth-label">{{ $t['birth_place_lbl'] }}</div>
            </td>
        </tr>
    </table>

    <div class="block">{{ $participationText }}</div>

    <table class="units">
        <tr>
            <td class="label-col">{{ $t['units_label'] }}</td>
            <td class="num-col">{{ $attestation->units_45min }}</td>
            <td class="suffix-col">{{ $t['units_suffix'] }}</td>
        </tr>
    </table>

    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'full' ? 'checked' : '' }}"></span>{{ $t['fees_full'] }}
    </div>
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'partial' ? 'checked' : '' }}"></span>{{ $t['fees_partial'] }}
    </div>

    <div class="niveau-period">{{ $niveauPeriodText }}</div>

    <div class="levels-title">{{ $t['level_title'] }}</div>

    <table class="levels">
        <tr>
            @foreach($levels as $lvl)
                <td>
                    <span class="check-box {{ $attestation->level === $lvl ? 'checked' : '' }}"></span>{{ $lvl }}
                </td>
            @endforeach
        </tr>
    </table>

    <div class="kursinfo-title">{{ $t['kursinfo_title'] }}</div>
    <div class="kursinfo-line">{{ $kursinfoLine }}</div>

    <div class="erfolg-line">
        @foreach($erfolgList as $i => $opt)
            <span class="{{ $attestation->erfolg === $opt ? 'erfolg-active' : '' }}">{{ $erfolgT[$opt] ?? $opt }}</span>@if($i < count($erfolgList) - 1) , @endif
        @endforeach
        .
    </div>

    <div class="legal">{!! nl2br(e($methodologyText)) !!}</div>

    <div class="sig-wrap">
        <table class="sig">
            <tr>
                <td class="left">
                    <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                    <div class="sig-label">{{ $t['place_label'] }}</div>
                </td>
                <td class="right">
                    <div class="sig-value">{{ $attestation->issue_date?->format('d.m.Y') }}</div>
                    <div class="sig-label">{{ $t['date_label'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="signature">{{ $t['signature'] }}</div>

    <div class="footer">
        <div class="addr">{{ $footerAddress }}</div>
        Tel : {{ $footerPhone }} , Email : {{ $footerEmail }}
    </div>

</body>
</html>
