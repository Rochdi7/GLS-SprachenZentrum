<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport — {{ $teacher->name }} {{ $group ? ' / ' . $group->name : '' }} — {{ $date->format('d/m/Y') }}</title>
    <style>
        @page { margin: 28mm 14mm 16mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 0; }

        .page-header {
            position: fixed;
            top: -23mm;
            left: 0;
            right: 0;
            height: 21mm;
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 3mm;
        }
        .page-header img { height: 28px; margin-bottom: 2px; }
        .page-header h1 { font-size: 13px; color: #1e3a5f; margin: 0; line-height: 1.2; }
        .page-header h2 { font-size: 9px; color: #666; margin: 3px 0 0; font-weight: normal; line-height: 1.2; }

        .page-footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            height: 9mm;
            text-align: center;
            font-size: 7.5px;
            color: #aaa;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .info-card .row { margin-bottom: 4px; font-size: 10px; }
        .info-card .label { display: inline-block; width: 90px; font-weight: bold; color: #1e3a5f; }
        .info-card .value { color: #333; }
        .gpill {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 3px;
        }

        table.skills {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
            margin-top: 4px;
        }
        table.skills th {
            background: #1e3a5f;
            color: #fff;
            padding: 6px 8px;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table.skills td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.skills tr:nth-child(even) { background: #f8fafc; }

        .col-skill { width: 22%; }
        .col-content { width: 78%; }
        .skill-pill {
            display: inline-block;
            background: #1e3a5f;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .skill-attach {
            margin-top: 5px;
            font-size: 8.5px;
            color: #d9534f;
            font-style: italic;
        }
        .empty-row td { color: #adb5bd; font-style: italic; }
        .section-title {
            background: #1e3a5f;
            color: #fff;
            text-align: center;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        /* For the free-form (no skill) reports */
        .freeform {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="page-header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Rapport Détaillé — {{ $teacher->name }}</h1>
        <h2>{{ ucfirst($date->translatedFormat('l')) }} {{ $date->format('d F Y') }}</h2>
    </div>

    <div class="page-footer">
        GLS Sprachzentrum — Rapport généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

    <div class="section-title">Détail du Rapport</div>

    {{-- Info card --}}
    <div class="info-card">
        <div class="row">
            <span class="label">Enseignant :</span>
            <span class="value">{{ $teacher->name }}</span>
        </div>
        <div class="row">
            <span class="label">Date :</span>
            <span class="value">{{ ucfirst($date->translatedFormat('l')) }} {{ $date->format('d F Y') }}</span>
        </div>
        <div class="row">
            <span class="label">Groupe :</span>
            <span class="value">
                @if ($group)
                    <span class="gpill">{{ $group->name }}</span>
                @else
                    <em style="color:#888;">Aucun groupe / général</em>
                @endif
            </span>
        </div>
        <div class="row">
            <span class="label">Entrées :</span>
            <span class="value">{{ $reports->count() }}</span>
        </div>
    </div>

    {{-- Group reports by skill (if any have skills) --}}
    @php
        $hasSkills = $reports->whereNotNull('skill')->isNotEmpty();
        $bySkill = $reports->keyBy('skill');
    @endphp

    @if ($hasSkills)
        <table class="skills">
            <thead>
                <tr>
                    <th class="col-skill">Compétence</th>
                    <th class="col-content">Activité / Contenu</th>
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Models\WeeklyReport::SKILLS as $skillKey => $skillLabel)
                    @php $report = $bySkill->get($skillKey); @endphp
                    <tr class="{{ $report ? '' : 'empty-row' }}">
                        <td class="col-skill">
                            <span class="skill-pill">{{ $skillLabel }}</span>
                        </td>
                        <td class="col-content">
                            @if ($report)
                                {!! nl2br(e($report->notes)) !!}
                                @if ($report->attachment_path)
                                    <div class="skill-attach">
                                        <strong>PDF joint :</strong> {{ $report->attachment_original_name ?? 'document.pdf' }}
                                    </div>
                                @endif
                            @else
                                <em>Aucune activité enregistrée pour cette compétence.</em>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Free-form notes (no skill structure) --}}
        @foreach ($reports as $report)
            <div class="freeform">
                {!! nl2br(e($report->notes)) !!}
                @if ($report->attachment_path)
                    <div class="skill-attach">
                        <strong>PDF joint :</strong> {{ $report->attachment_original_name ?? 'document.pdf' }}
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</body>
</html>
