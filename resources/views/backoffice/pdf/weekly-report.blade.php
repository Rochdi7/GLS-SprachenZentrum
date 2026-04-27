<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport Semaine — {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #1e3a5f; }
        .header img { height: 45px; margin-bottom: 8px; }
        .header h1 { font-size: 16px; color: #1e3a5f; margin: 0; }
        .header h2 { font-size: 11px; color: #666; margin: 4px 0 0; font-weight: normal; }

        /* ===== Page 1: Calendar grid ===== */
        table.calendar { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        table.calendar th {
            background: #1e3a5f;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            width: 20%;
        }
        table.calendar td {
            border: 1px solid #dde2e8;
            vertical-align: top;
            padding: 6px;
            width: 20%;
        }
        .day-num { font-weight: bold; font-size: 11px; margin-bottom: 5px; color: #1e3a5f; }
        .report-entry {
            background: #f0f4ff;
            border-left: 2px solid #4680ff;
            border-radius: 3px;
            padding: 4px 5px;
            margin-bottom: 4px;
            font-size: 8px;
            line-height: 1.35;
        }
        .report-entry .t-name { font-weight: bold; color: #1e3a5f; font-size: 8.5px; margin-bottom: 1px; }
        .report-entry .t-group { display: inline-block; background: #fff3cd; color: #856404; font-size: 7.5px; font-weight: bold; padding: 1px 4px; border-radius: 3px; margin-bottom: 2px; }
        .report-entry .t-notes { color: #444; word-wrap: break-word; overflow-wrap: break-word; }
        .report-entry .t-attach { color: #d9534f; font-size: 7.5px; margin-top: 2px; font-style: italic; }
        .empty-cell { color: #bbb; font-style: italic; font-size: 8px; }

        /* ===== Page 2: Detail table ===== */
        .page-break { page-break-before: always; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        table.detail th {
            background: #1e3a5f;
            color: white;
            padding: 5px 6px;
            text-align: left;
            font-size: 9px;
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
        .col-teacher { width: 16%; }
        .col-date { width: 9%; text-align: center; }
        .col-jour { width: 10%; text-align: center; }
        .col-group { width: 13%; }
        .col-notes { width: 52%; }
        .col-group .gpill { display: inline-block; background: #fff3cd; color: #856404; font-size: 8px; font-weight: bold; padding: 2px 6px; border-radius: 3px; }

        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 6px; }
    </style>
</head>
<body>
    {{-- ==================== PAGE 1: Calendar Grid ==================== --}}
    <div class="header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Rapport Semaine — Enseignants</h1>
        <h2>{{ $weekStart->translatedFormat('l d F Y') }} — {{ $weekEnd->translatedFormat('l d F Y') }}</h2>
    </div>

    <table class="calendar">
        <thead>
            <tr>
                @foreach ($weekDays as $day)
                    <th>{{ ucfirst($day->translatedFormat('l')) }} {{ $day->format('d/m') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($weekDays as $day)
                    @php $key = $day->format('Y-m-d'); $dayReports = $reports[$key] ?? collect(); @endphp
                    <td>
                        <div class="day-num">{{ $day->format('d') }}</div>
                        @forelse ($dayReports as $report)
                            <div class="report-entry">
                                <div class="t-name">{{ $report->teacher->name }}</div>
                                @if ($report->group)
                                    <div class="t-group">{{ $report->group->name }}</div>
                                @endif
                                <div class="t-notes">{!! nl2br(e($report->notes)) !!}</div>
                                @if ($report->attachment_path)
                                    <div class="t-attach">PDF joint : {{ $report->attachment_original_name ?? 'document.pdf' }}</div>
                                @endif
                            </div>
                        @empty
                            <span class="empty-cell">Aucun rapport</span>
                        @endforelse
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>

    <div class="footer">GLS Sprachzentrum — Rapport généré le {{ now()->format('d/m/Y à H:i') }}</div>

    {{-- ==================== PAGE 2: Detail Table ==================== --}}
    @if ($reportsByTeacher->isNotEmpty())
    <div class="page-break"></div>

    <div class="header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Détail des Rapports — Enseignants</h1>
        <h2>{{ $weekStart->translatedFormat('l d F Y') }} — {{ $weekEnd->translatedFormat('l d F Y') }}</h2>
    </div>

    <table class="detail">
        <thead>
            <tr>
                <th class="col-teacher">Enseignant</th>
                <th class="col-date">Date</th>
                <th class="col-jour">Jour</th>
                <th class="col-group">Groupe</th>
                <th class="col-notes">Notes / Activités</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportsByTeacher as $teacherName => $teacherReports)
                @foreach ($teacherReports as $report)
                    <tr>
                        @if ($loop->first)
                            <td class="col-teacher" style="font-weight:bold;" rowspan="{{ $teacherReports->count() }}">{{ $teacherName }}</td>
                        @endif
                        <td class="col-date">{{ $report->report_date->format('d/m') }}</td>
                        <td class="col-jour">{{ ucfirst($report->report_date->translatedFormat('l')) }}</td>
                        <td class="col-group">
                            @if ($report->group)
                                <span class="gpill">{{ $report->group->name }}</span>
                            @else
                                <span style="color:#aaa;">—</span>
                            @endif
                        </td>
                        <td class="col-notes">
                            {!! nl2br(e($report->notes)) !!}
                            @if ($report->attachment_path)
                                <div style="color:#d9534f; font-size:8px; font-style:italic; margin-top:2px;">
                                    PDF joint : {{ $report->attachment_original_name ?? 'document.pdf' }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">GLS Sprachzentrum — Rapport généré le {{ now()->format('d/m/Y à H:i') }}</div>
    @endif
</body>
</html>
