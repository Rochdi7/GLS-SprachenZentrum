<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport Semaine — {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}</title>
    <style>
        @page { margin: 30mm 10mm 18mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #333; margin: 0; }

        /* ===== Fixed header on every page ===== */
        .page-header {
            position: fixed;
            top: -25mm;
            left: 0;
            right: 0;
            height: 23mm;
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 3mm;
        }
        .page-header img { height: 30px; margin-bottom: 2px; }
        .page-header h1 { font-size: 13px; color: #1e3a5f; margin: 0; line-height: 1.2; }
        .page-header h2 { font-size: 9px; color: #666; margin: 3px 0 0; font-weight: normal; line-height: 1.2; }

        /* ===== Fixed footer on every page ===== */
        .page-footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            height: 10mm;
            text-align: center;
            font-size: 7.5px;
            color: #aaa;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .page-footer .pn:before { content: counter(page); }
        .page-footer .pt:before { content: counter(pages); }

        /* ===== Section title (used at top of each page section) ===== */
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

        /* ===== Page 1: Per-day list (stacked, one block per day) ===== */
        .day-block {
            margin-bottom: 8px;
            border: 1px solid #dde2e8;
            border-radius: 3px;
        }
        .day-block .day-head {
            background: #1e3a5f;
            color: #fff;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .day-block .day-body { padding: 6px 8px; }
        .day-block .empty {
            color: #adb5bd;
            font-style: italic;
            font-size: 9px;
            padding: 4px 0;
        }

        /* ===== Per-teacher pivot inside a day ===== */
        table.day-pivot {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            table-layout: fixed;
        }
        table.day-pivot th {
            background: #f8fafc;
            color: #1e3a5f;
            border: 1px solid #e2e8f0;
            padding: 4px 5px;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.day-pivot td {
            border: 1px solid #e2e8f0;
            padding: 4px 5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-tn { width: 18%; }
        .col-grp { width: 14%; }
        .col-skl { width: 10%; }
        .col-ntx { width: 50%; }
        .col-pdf { width: 8%; text-align: center; }

        .gpill {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            font-size: 8px;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .skill-pill {
            display: inline-block;
            background: #e6f0ff;
            color: #1e3a5f;
            font-size: 8px;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 3px;
        }
        .pdf-icon { color: #d9534f; font-size: 9px; font-weight: bold; }
        .pdf-name { display: block; color: #888; font-size: 7px; word-break: break-all; }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7.5px;
            color: #aaa;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        /* ===== Page 2+: Per-teacher detail ===== */
        .page-break { page-break-before: always; }

        .teacher-section {
            margin-bottom: 8px;
            page-break-before: always;
        }
        .teacher-section.first-teacher { page-break-before: auto; }
        .teacher-section h3 {
            font-size: 11px;
            color: #fff;
            background: #1e3a5f;
            padding: 5px 10px;
            margin: 0 0 0;
            border-radius: 3px 3px 0 0;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            table-layout: fixed;
        }
        table.detail th {
            background: #f1f5f9;
            color: #1e3a5f;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.detail td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.detail tr:nth-child(even) { background: #f8fafc; }
        .d-date { width: 9%; text-align: center; }
        .d-jour { width: 11%; text-align: center; }
        .d-group { width: 14%; }
        .d-skill { width: 11%; }
        .d-notes { width: 47%; }
        .d-pdf { width: 8%; text-align: center; }
    </style>
</head>
<body>

    {{-- ===== Fixed header (repeats on every page) ===== --}}
    <div class="page-header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Rapport Semaine — Enseignants</h1>
        <h2>{{ $weekStart->translatedFormat('l d F Y') }} — {{ $weekEnd->translatedFormat('l d F Y') }}</h2>
    </div>

    {{-- ===== Fixed footer (repeats on every page) ===== --}}
    <div class="page-footer">
        GLS Sprachzentrum — Rapport généré le {{ now()->format('d/m/Y à H:i') }}
        &nbsp;·&nbsp; Page <span class="pn"></span> / <span class="pt"></span>
    </div>

    {{-- ==================== PAGE 1: Per-Day Summary ==================== --}}
    <div class="section-title">Résumé par jour</div>

    @foreach ($weekDays as $day)
        @php
            $key = $day->format('Y-m-d');
            $dayReports = $reports[$key] ?? collect();
        @endphp
        <div class="day-block">
            <div class="day-head">
                {{ ucfirst($day->translatedFormat('l')) }} {{ $day->format('d/m/Y') }}
                @if ($dayReports->isNotEmpty())
                    <span style="float:right; font-weight:normal; font-size:8.5px;">
                        {{ $dayReports->count() }} entrée{{ $dayReports->count() > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>
            <div class="day-body">
                @if ($dayReports->isEmpty())
                    <div class="empty">Aucun rapport pour ce jour.</div>
                @else
                    <table class="day-pivot">
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
                            @foreach ($dayReports as $report)
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
                                        @if ($report->attachment_path)
                                            <span class="pdf-icon">PDF</span>
                                            <span class="pdf-name">{{ $report->attachment_original_name ?? '—' }}</span>
                                        @else
                                            <span style="color:#bbb;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach

    {{-- ==================== PAGE 2+: Per-Teacher Detail (one page per teacher) ==================== --}}
    @if ($reportsByTeacher->isNotEmpty())
        <div class="page-break"></div>
        <div class="section-title">Détail par Enseignant</div>

        @foreach ($reportsByTeacher as $teacherName => $teacherReports)
            <div class="teacher-section {{ $loop->first ? 'first-teacher' : '' }}">
                <h3>{{ $teacherName }} <span style="float:right; font-weight:normal; font-size:9px;">{{ $teacherReports->count() }} rapport{{ $teacherReports->count() > 1 ? 's' : '' }}</span></h3>
                <table class="detail">
                    <thead>
                        <tr>
                            <th class="d-date">Date</th>
                            <th class="d-jour">Jour</th>
                            <th class="d-group">Groupe</th>
                            <th class="d-skill">Compétence</th>
                            <th class="d-notes">Notes / Activités</th>
                            <th class="d-pdf">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teacherReports as $report)
                            <tr>
                                <td class="d-date">{{ $report->report_date->format('d/m') }}</td>
                                <td class="d-jour">{{ ucfirst($report->report_date->translatedFormat('l')) }}</td>
                                <td class="d-group">
                                    @if ($report->group)
                                        <span class="gpill">{{ $report->group->name }}</span>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                                <td class="d-skill">
                                    @if ($report->skill)
                                        <span class="skill-pill">{{ \App\Models\WeeklyReport::SKILLS[$report->skill] ?? $report->skill }}</span>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                                <td class="d-notes">{!! nl2br(e($report->notes)) !!}</td>
                                <td class="d-pdf">
                                    @if ($report->attachment_path)
                                        <span class="pdf-icon">PDF</span>
                                        <span class="pdf-name">{{ $report->attachment_original_name ?? '—' }}</span>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
