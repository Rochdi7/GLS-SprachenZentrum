@php
    /**
     * Single-language attestation PDF.
     *
     * $lang in ['de', 'fr', 'en']
     */

    $T = [
        'de' => [
            'title'           => 'Teilnahmebestätigung',
            'name_label'      => 'Name, Vorname',
            'birth_date_lbl'  => 'geboren am',
            'birth_place_lbl' => 'geboren in',
            'participation'   => 'hat in der Zeit vom :start bis :end an einem Deutschkurs im GLS Sprachenzentrum teilgenommen.',
            'units'           => 'Der Kurs umfasste :units Unterrichtseinheiten zu je 45 Minuten.',
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
            'units'           => 'Le cours comprenait :units unités d\'enseignement de 45 minutes chacune.',
            'fees_full'       => 'Les frais de cours ont été intégralement payés.',
            'fees_partial'    => 'Les frais de cours ont été partiellement payés.',
            'niveau_period'   => 'Le niveau commence du :start au :end',
            'level_title'     => 'Niveau de référence européen :',
            'kursinfo_title'  => 'Information sur le cours :',
            'kursinfo_line'   => 'Niveau :idx sur :total',
            'legal'           => 'L\'appréciation des résultats obtenus en cours est faite par les enseignant(e)s. Cette attestation de présence n\'est pas un diplôme. Le barème comprend 4 appréciations : très bien, bien, assez bien, participation régulière.',
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
            'units'           => 'The course consisted of :units teaching units of 45 minutes each.',
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
        'de' => [
            'Erfolg'           => 'sehr gut',
            'mit gutem Erfolg' => 'gut',
            'mit Erfolg'       => 'befriedigend',
            'teilgenommen'     => 'regelmäßige Teilnahme',
        ],
        'fr' => [
            'Erfolg'           => 'très bien',
            'mit gutem Erfolg' => 'bien',
            'mit Erfolg'       => 'assez bien',
            'teilgenommen'     => 'participation régulière',
        ],
        'en' => [
            'Erfolg'           => 'very good',
            'mit gutem Erfolg' => 'good',
            'mit Erfolg'       => 'satisfactory',
            'teilgenommen'     => 'regular attendance',
        ],
    ];

    $t = $T[$lang] ?? $T['de'];
    $erfolgT = $erfolgTranslations[$lang] ?? $erfolgTranslations['de'];

    $startCourse = $attestation->course_start_date?->format('d-m-Y') ?? '—';
    $endCourse   = $attestation->course_end_date?->format('d-m-Y')   ?? '—';
    $startNiveau = $attestation->niveau_start_date?->format('d-m-Y') ?? '—';
    $endNiveau   = $attestation->niveau_end_date?->format('d-m-Y')   ?? '—';

    $participationText = strtr($t['participation'], [':start' => $startCourse, ':end' => $endCourse]);
    $unitsText         = strtr($t['units'], [':units' => $attestation->units_45min]);
    $niveauPeriodText  = strtr($t['niveau_period'], [':start' => $startNiveau, ':end' => $endNiveau]);
    $kursinfoLine      = strtr($t['kursinfo_line'], [':idx' => $attestation->stufe_index, ':total' => $attestation->stufe_total]);

    $erfolgList = ['Erfolg', 'mit gutem Erfolg', 'mit Erfolg', 'teilgenommen'];
    $levels = ['A1', 'A2', 'B1', 'B2', 'C1'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $t['title'] }} — {{ $attestation->last_name }} {{ $attestation->first_name }}</title>

    <style>
        @page { size: A4 portrait; margin: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12.5px;
            color: #1a1a1a;
            margin: 0;
            padding: 30px 55px 110px 55px;
            line-height: 1.5;
        }

        .header { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header td { vertical-align: middle; padding: 0; }
        .header-logo { width: 130px; height: auto; }
        .header-title {
            font-size: 26px;
            font-weight: bold;
            color: #1a1a1a;
            text-align: left;
            padding-left: 18px;
            text-decoration: underline;
            line-height: 1.1;
        }

        .name-block { margin-top: 8px; margin-bottom: 14px; }
        .name-value {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .name-label { font-size: 10.5px; color: #555; font-style: italic; }

        .birth { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .birth td { width: 50%; vertical-align: top; padding: 0; }
        .birth td.right { padding-left: 30px; }
        .birth-value { font-size: 17px; font-weight: bold; margin-bottom: 2px; }
        .birth-label { font-size: 10.5px; color: #555; font-style: italic; }

        .para { margin: 0 0 12px 0; font-size: 12.5px; line-height: 1.6; }

        .check-row { margin: 6px 0; font-size: 12.5px; line-height: 1.5; }
        .check-row .check-box { margin-right: 10px; margin-left: 28px; }

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
            top: 50%; left: 50%;
            width: 19px; height: 1.4px;
            background: #1a1a1a;
        }
        .check-box.checked::before { transform: translate(-50%, -50%) rotate(45deg); }
        .check-box.checked::after  { transform: translate(-50%, -50%) rotate(-45deg); }

        .niveau-period { margin: 14px 0 12px 0; font-size: 12.5px; }

        .levels-title { margin: 10px 0 8px; font-size: 12.5px; text-decoration: underline; }
        .levels-row { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .levels-row td {
            text-align: left;
            font-size: 14px;
            font-weight: bold;
            padding-right: 14px;
            white-space: nowrap;
        }
        .levels-row .check-box { margin-right: 8px; vertical-align: -2px; }

        .kursinfo-title { margin-top: 6px; margin-bottom: 4px; font-size: 12.5px; }
        .kursinfo-line { margin-bottom: 4px; font-size: 12.5px; }
        .erfolg-line { margin-bottom: 8px; font-size: 11.5px; line-height: 1.5; }
        .erfolg-active { font-weight: bold; text-decoration: underline; }

        .legal { font-size: 9.5px; color: #444; line-height: 1.5; margin-bottom: 22px; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .sig-table td { width: 50%; vertical-align: top; padding: 0; }
        .sig-table td.right { text-align: right; }
        .sig-value { font-size: 17px; font-weight: bold; margin-bottom: 1px; }
        .sig-label { font-size: 10.5px; color: #555; font-style: italic; }

        .signature { margin-top: 22px; text-align: right; font-size: 12px; text-decoration: underline; }

        .footer {
            position: fixed;
            left: 55px; right: 55px;
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

    <table class="header">
        <tr>
            <td style="width: 32%;">
                <img src="{{ public_path('assets/images/logo/gls.png') }}" class="header-logo" alt="GLS">
            </td>
            <td class="header-title">{{ $t['title'] }}</td>
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

    <div class="para">{{ $participationText }}</div>
    <div class="para">{{ $unitsText }}</div>

    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'full' ? 'checked' : '' }}"></span>{{ $t['fees_full'] }}
    </div>
    <div class="check-row">
        <span class="check-box {{ $attestation->fees_status === 'partial' ? 'checked' : '' }}"></span>{{ $t['fees_partial'] }}
    </div>

    <div class="niveau-period">{{ $niveauPeriodText }}</div>

    <div class="levels-title">{{ $t['level_title'] }}</div>

    <table class="levels-row">
        <tr>
            @foreach($levels as $lvl)
                <td>
                    <span class="check-box {{ $attestation->level === $lvl ? 'checked' : '' }}"></span>{{ $lvl === 'C1' ? 'C 1' : $lvl }}
                </td>
            @endforeach
            <td style="width: 100%;"></td>
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

    <div class="legal">{{ $t['legal'] }}</div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-value">{{ strtoupper($attestation->city) }}</div>
                <div class="sig-label">{{ $t['place_label'] }}</div>
            </td>
            <td class="right">
                <div class="sig-value">{{ $attestation->issue_date?->format('d.m.Y') }}</div>
                <div class="sig-label">{{ $t['date_label'] }}</div>
            </td>
        </tr>
    </table>

    <div class="signature">{{ $t['signature'] }}</div>

    <div class="footer">
        Adresse : Rue Halima Saadia N12 Lgherabliva en face la pharmacie centrale près de station tram Divar<br>
        Tel : 0808540625/0622996078 , Email : gls.sprachenzentrum.sale@gmail.com
    </div>

</body>
</html>
