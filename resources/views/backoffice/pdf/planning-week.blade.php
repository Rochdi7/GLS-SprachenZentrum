<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning semaine - {{ $employee->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #1e3a5f; }
        .header img { height: 45px; margin-bottom: 8px; }
        .header h1 { font-size: 16px; color: #1e3a5f; margin: 0; }
        .header h2 { font-size: 12px; color: #666; margin: 4px 0 0; font-weight: normal; }
        .info td { padding: 2px 8px; font-size: 10px; }
        .info .lbl { font-weight: bold; color: #666; width: 100px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1e3a5f; color: white; padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.data td { border: 1px solid #e2e8f0; padding: 4px 6px; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .total { background: #eff6ff; font-weight: bold; }
        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .green { color: #059669; }
        .muted { color: #999; }
        .weekend td { background: #fafafa; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Planning Hebdomadaire</h1>
        <h2>{{ $employee->name }} ({{ $employee->staff_role ?? '—' }}){{ $site ? ' — ' . $site->name : '' }}</h2>
    </div>

    <table class="info" style="margin-bottom: 10px;">
        <tr>
            <td class="lbl">Employé :</td><td>{{ $employee->name }}</td>
            <td class="lbl">Centre :</td><td>{{ $site->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Poste :</td><td>{{ $employee->staff_role ?? '—' }}</td>
            <td class="lbl">Semaine :</td>
            <td>du {{ $weekStart->locale('fr')->isoFormat('DD MMMM') }} au {{ $weekEnd->locale('fr')->isoFormat('DD MMMM YYYY') }}</td>
        </tr>
    </table>

    @php
        $byDate = $schedules->keyBy(fn($s) => $s->date->format('Y-m-d'));
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = (clone $weekStart)->addDays($i);
            $days[] = [
                'date'    => $d,
                'key'     => $d->format('Y-m-d'),
                'weekend' => in_array($d->dayOfWeek, [0, 6]),
                'entry'   => $byDate->get($d->format('Y-m-d')),
            ];
        }
    @endphp

    <table class="data">
        <thead>
            <tr>
                <th style="width: 70px;">Date</th>
                <th style="width: 70px;">Jour</th>
                <th class="text-center" style="width: 55px;">Début</th>
                <th class="text-center" style="width: 55px;">Fin</th>
                <th class="text-center" style="width: 65px;">Amplitude</th>
                <th class="text-center" style="width: 90px;">Pause</th>
                <th class="text-center" style="width: 60px;">Durée pause</th>
                <th class="text-center" style="width: 60px;">Travaillé</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $day)
                @php $s = $day['entry']; @endphp
                <tr class="{{ $day['weekend'] ? 'weekend' : '' }}">
                    <td class="bold">{{ $day['date']->format('d/m/Y') }}</td>
                    <td>{{ $day['date']->locale('fr')->isoFormat('dddd') }}</td>
                    @if($s)
                        <td class="text-center">{{ substr($s->start_time, 0, 5) }}</td>
                        <td class="text-center">{{ substr($s->end_time, 0, 5) }}</td>
                        <td class="text-center">{{ $s->total_span_formatted }}</td>
                        <td class="text-center">{{ $s->break_start ? substr($s->break_start, 0, 5) . ' - ' . substr($s->break_end, 0, 5) : '—' }}</td>
                        <td class="text-center">{{ $s->break_minutes > 0 ? $s->break_formatted : '—' }}</td>
                        <td class="text-center bold green">{{ $s->worked_formatted }}</td>
                        <td>{{ $s->notes ?? '' }}</td>
                    @else
                        <td colspan="7" class="text-center muted">— Non planifié —</td>
                    @endif
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4" class="bold">TOTAL — {{ $schedules->count() }} jour(s) planifié(s)</td>
                <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($schedules->sum('total_span_minutes')) }}</td>
                <td></td>
                <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($totalBreak) }}</td>
                <td class="text-center bold green">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">GLS Sprachzentrum — Planning généré le {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
