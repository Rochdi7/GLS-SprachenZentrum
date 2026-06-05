@component('emails.layouts.branded', [
    'title'    => 'Rapport Hebdomadaire — Présences',
    'subtitle' => $reportData['period_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 20px 0;">
    <tr>
        <td align="center" width="33%" style="padding:0 4px 0 0;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#181615;">{{ $reportData['total_sessions'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Sessions</div>
            </div>
        </td>
        <td align="center" width="33%" style="padding:0 4px;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#2e7d32;">{{ $reportData['total_present'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Présents</div>
            </div>
        </td>
        <td align="center" width="33%" style="padding:0 0 0 4px;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#c1272d;">{{ $reportData['attendance_rate'] }}%</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Taux présence</div>
            </div>
        </td>
    </tr>
</table>

{{-- By Center --}}
@if(count($reportData['by_center']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Par centre</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 20px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Présents</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Absents</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Taux</td>
    </tr>
    @foreach($reportData['by_center'] as $center)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13.5px;">{{ $center['center_name'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;color:#2e7d32;font-weight:600;">{{ $center['present'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;color:#c1272d;">{{ $center['absent'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:right;font-weight:700;">{{ $center['attendance_rate'] ?? '—' }}%</td>
    </tr>
    @endforeach
</table>
@endif

{{-- Top/Bottom performing classes --}}
@if(count($reportData['by_class']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Top classes ({{ count($reportData['by_class']) }} au total)</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Classe</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Taux</td>
    </tr>
    @foreach(array_slice($reportData['by_class'], 0, 10) as $cls)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13px;">{{ $cls['class_name'] }}</td>
        <td style="padding:8px 12px;font-size:13px;text-align:right;font-weight:700;">{{ $cls['attendance_rate'] ?? '—' }}%</td>
    </tr>
    @endforeach
</table>
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré automatiquement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
