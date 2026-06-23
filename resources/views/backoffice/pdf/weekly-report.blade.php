<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport Semaine — {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}</title>
    <style>
        @page { margin: 28mm 10mm 16mm 10mm; size: A4 landscape; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; margin: 0; }

        /* ===== Fixed header ===== */
        .page-header {
            position: fixed;
            top: -24mm;
            left: 0; right: 0;
            height: 22mm;
            text-align: center;
            border-bottom: 2px solid #1a2f5e;
            padding-bottom: 3mm;
        }
        .page-header img { height: 28px; margin-bottom: 2px; }
        .page-header h1 { font-size: 13px; color: #1a2f5e; margin: 0; font-weight: bold; }
        .page-header h2 { font-size: 8.5px; color: #666; margin: 2px 0 0; font-weight: normal; }

        /* ===== Fixed footer ===== */
        .page-footer {
            position: fixed;
            bottom: -12mm;
            left: 0; right: 0;
            height: 9mm;
            text-align: center;
            font-size: 7px;
            color: #aaa;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
        .page-footer .pn:before { content: counter(page); }
        .page-footer .pt:before { content: counter(pages); }

        /* ===== Section title ===== */
        .section-title {
            background: #1a2f5e;
            color: #fff;
            text-align: center;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        /* ===== Page 1: Week summary — one table for the whole week ===== */
        .week-summary-block {
            border: 1px solid #c6d3e6;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .week-summary-head {
            background: linear-gradient(135deg, #1a2f5e 0%, #2d4f9a 100%);
            color: #fff;
            padding: 6px 12px;
            font-size: 10px;
            font-weight: bold;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }
        .week-summary-head .wsh-range { display: table-cell; }
        .week-summary-head .wsh-count { display: table-cell; text-align: right; font-weight: normal; font-size: 8.5px; opacity: .85; }

        table.week-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            table-layout: fixed;
        }
        table.week-table th {
            background: #eef3fb;
            color: #1a2f5e;
            border: 1px solid #d0ddf0;
            padding: 4px 6px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.week-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.week-table tr:nth-child(even) td { background: #f8fafd; }
        .col-tn  { width: 16%; }
        .col-grp { width: 13%; }
        .col-skl { width: 11%; }
        .col-ntx { width: 52%; }
        .col-pdf { width: 8%; text-align: center; }

        .gpill {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            font-size: 7.5px;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .skill-pill {
            display: inline-block;
            background: #e6f0ff;
            color: #1a2f5e;
            font-size: 7.5px;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .pdf-icon { color: #c0392b; font-size: 8.5px; font-weight: bold; }
        .pdf-name { display: block; color: #888; font-size: 7px; word-break: break-all; }

        .no-reports {
            padding: 10px 12px;
            color: #adb5bd;
            font-style: italic;
            font-size: 8.5px;
            text-align: center;
        }

        /* ===== Page 2+: Per-teacher detail ===== */
        .page-break { page-break-before: always; }
        .teacher-section { margin-bottom: 10px; page-break-before: always; }
        .teacher-section.first-teacher { page-break-before: auto; }

        .teacher-header {
            background: linear-gradient(135deg, #1a2f5e 0%, #2d4f9a 100%);
            color: #fff;
            padding: 6px 12px;
            border-radius: 3px 3px 0 0;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }
        .th-name { display: table-cell; font-size: 11px; font-weight: bold; }
        .th-count { display: table-cell; text-align: right; font-size: 8.5px; font-weight: normal; opacity: .8; vertical-align: middle; }

        /* Skills grid: one column per skill ===== */
        .skills-grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            table-layout: fixed;
        }
        .skills-grid-table th {
            background: #1a2f5e;
            color: #fff;
            border: 1px solid #2d4f9a;
            padding: 4px 6px;
            font-size: 8px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .skills-grid-table td {
            border: 1px solid #d0ddf0;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .skills-grid-table .group-header td {
            background: #eef3fb;
            color: #1a2f5e;
            font-weight: bold;
            font-size: 8px;
            padding: 3px 6px;
            border-bottom: 1px solid #c6d3e6;
        }
        .empty-cell { color: #ccc; font-style: italic; text-align: center; font-size: 7.5px; }
        .pdf-badge { color: #c0392b; font-size: 7px; }
    </style>
</head>
<body>

    {{-- Fixed header --}}
    <div class="page-header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Rapport Semaine — Enseignants</h1>
        <h2>{{ $weekStart->translatedFormat('l d F Y') }} — {{ $weekEnd->translatedFormat('l d F Y') }}</h2>
    </div>

    {{-- Fixed footer --}}
    <div class="page-footer">
        GLS Sprachzentrum — Rapport généré le {{ now()->format('d/m/Y à H:i') }}
        &nbsp;·&nbsp; Page <span class="pn"></span> / <span class="pt"></span>
    </div>

    {{-- ==================== PAGE 1: Résumé de la semaine ==================== --}}
    @php
        $allReports = $reportsByTeacher->flatten();
        $totalEntries = $allReports->count();
    @endphp

    <div class="section-title">Résumé de la Semaine</div>

    <div class="week-summary-block">
        <div class="week-summary-head">
            <span class="wsh-range">
                Semaine du {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}
            </span>
            <span class="wsh-count">
                {{ $totalEntries }} entrée{{ $totalEntries > 1 ? 's' : '' }} &nbsp;·&nbsp;
                {{ $reportsByTeacher->count() }} enseignant{{ $reportsByTeacher->count() > 1 ? 's' : '' }}
            </span>
        </div>

        @if ($allReports->isEmpty())
            <div class="no-reports">Aucun rapport enregistré pour cette semaine.</div>
        @else
            <table class="week-table">
                <thead>
                    <tr>
                        <th class="col-tn">Enseignant</th>
                        <th class="col-grp">Groupe</th>
                        <th class="col-skl">Compétence</th>
                        <th class="col-ntx">Notes / Activités</th>
                        <th class="col-pdf">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportsByTeacher as $teacherName => $teacherReports)
                        @foreach ($teacherReports->sortBy('skill') as $report)
                            <tr>
                                <td class="col-tn"><strong>{{ $report->teacher->name }}</strong></td>
                                <td class="col-grp">
                                    @if ($report->group)
                                        <span class="gpill">{{ $report->group->name }}</span>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                                <td class="col-skl">
                                    @if ($report->skill)
                                        <span class="skill-pill">{{ \App\Models\WeeklyReport::SKILLS[$report->skill] ?? $report->skill }}</span>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                                <td class="col-ntx">{!! nl2br(e($report->notes)) !!}</td>
                                <td class="col-pdf">
                                    @php $hasAny = $report->attachment_path || $report->attachments->isNotEmpty(); @endphp
                                    @if ($report->attachment_path)
                                        <span class="pdf-icon">PDF</span>
                                        <span class="pdf-name">{{ $report->attachment_original_name ?? '—' }}</span>
                                    @endif
                                    @foreach ($report->attachments as $att)
                                        <span class="pdf-icon">PDF</span>
                                        <span class="pdf-name">{{ $att->original_name ?? '—' }}</span>
                                    @endforeach
                                    @if (!$hasAny)
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ==================== PAGE 2+: Detail par enseignant (skills grid) ==================== --}}
    @if ($reportsByTeacher->isNotEmpty())
        <div class="page-break"></div>
        <div class="section-title">Détail par Enseignant</div>

        @php $skillKeys = array_keys(\App\Models\WeeklyReport::SKILLS); @endphp

        @foreach ($reportsByTeacher as $teacherName => $teacherReports)
            @php
                // Group by (group_id) → skills map
                $byGroup = $teacherReports->groupBy(fn ($r) => $r->group_id ?? '__none__');
                $count = $teacherReports->count();
            @endphp

            <div class="teacher-section {{ $loop->first ? 'first-teacher' : '' }}">
                <div class="teacher-header">
                    <span class="th-name">{{ $teacherName }}</span>
                    <span class="th-count">{{ $count }} rapport{{ $count > 1 ? 's' : '' }}</span>
                </div>

                <table class="skills-grid-table">
                    <thead>
                        <tr>
                            <th style="width:14%">Groupe</th>
                            @foreach (\App\Models\WeeklyReport::SKILLS as $sk => $sl)
                                <th style="width:{{ round(86 / count(\App\Models\WeeklyReport::SKILLS), 1) }}%">{{ $sl }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byGroup as $groupId => $groupReports)
                            @php
                                $group = $groupReports->first()->group;
                                $bySkill = $groupReports->keyBy('skill');
                            @endphp
                            <tr>
                                <td style="font-weight:bold; color:#1a2f5e;">
                                    @if ($group)
                                        <span class="gpill">{{ $group->name }}</span>
                                    @else
                                        <em style="color:#aaa; font-size:8px;">Général</em>
                                    @endif
                                </td>
                                @foreach (\App\Models\WeeklyReport::SKILLS as $sk => $sl)
                                    @php $r = $bySkill->get($sk); @endphp
                                    <td>
                                        @if ($r && trim($r->notes) !== '')
                                            {!! nl2br(e($r->notes)) !!}
                                            @php $hasAny = $r->attachment_path || $r->attachments->isNotEmpty(); @endphp
                                            @if ($hasAny)
                                                <div style="margin-top:3px;">
                                                    @if ($r->attachment_path)
                                                        <span class="pdf-badge">&#x25A0; PDF</span>
                                                    @endif
                                                    @foreach ($r->attachments as $att)
                                                        <span class="pdf-badge">&#x25A0; PDF</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            <span class="empty-cell">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
