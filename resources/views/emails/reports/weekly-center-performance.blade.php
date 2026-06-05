@component('emails.layouts.branded', [
    'title'    => 'Rapport Hebdomadaire — Performance Centres',
    'subtitle' => $reportData['period_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 20px 0;">
    <tr>
        <td align="center" width="50%" style="padding:0 4px 0 0;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#181615;">
                    {{ number_format($reportData['total_revenue'], 2) }} MAD
                </div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Revenus semaine</div>
            </div>
        </td>
        <td align="center" width="50%" style="padding:0 0 0 4px;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#181615;">{{ $reportData['total_new_registrations'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Nouvelles inscriptions</div>
            </div>
        </td>
    </tr>
</table>

@if(count($reportData['centers']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Performance par centre</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Inscriptions</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Présence</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Revenus</td>
    </tr>
    @foreach($reportData['centers'] as $center)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13.5px;font-weight:600;">{{ $center['center_name'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;">{{ $center['new_registrations'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;">
            @if($center['attendance_rate'] !== null)
                {{ $center['attendance_rate'] }}%
            @else
                —
            @endif
        </td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:right;font-weight:700;">
            {{ number_format($center['revenue'], 2) }} MAD
        </td>
    </tr>
    @endforeach
</table>
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré automatiquement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
