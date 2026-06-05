@component('emails.layouts.branded', [
    'title'    => 'Rapport Hebdomadaire — Performance Groupes',
    'subtitle' => $reportData['period_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 20px 0;">
    <tr>
        <td align="center" width="50%" style="padding:0 4px 0 0;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#181615;">{{ $reportData['total_active_groups'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Groupes actifs</div>
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

@if(count($reportData['groups']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Performance par groupe</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Groupe</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Sessions</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Inscriptions</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Présence</td>
    </tr>
    @foreach(array_slice($reportData['groups'], 0, 20) as $group)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13px;">
            <div style="font-weight:600;">{{ $group['class_name'] }}</div>
            <div style="font-size:12px;color:#7a716c;">{{ $group['center_name'] }} · {{ $group['level'] }}</div>
        </td>
        <td style="padding:8px 12px;font-size:13px;text-align:center;">{{ $group['sessions_held'] }}</td>
        <td style="padding:8px 12px;font-size:13px;text-align:center;">
            @if($group['new_registrations'] > 0)
            <span style="color:#2e7d32;font-weight:600;">+{{ $group['new_registrations'] }}</span>
            @else
            <span style="color:#9a918a;">—</span>
            @endif
        </td>
        <td style="padding:8px 12px;font-size:13px;text-align:right;font-weight:700;">
            @if($group['attendance_rate'] !== null)
                {{ $group['attendance_rate'] }}%
            @else
                —
            @endif
        </td>
    </tr>
    @endforeach
</table>
@if(count($reportData['groups']) > 20)
<p style="margin:4px 0 0 0;font-size:12px;color:#9a918a;">
    … et {{ count($reportData['groups']) - 20 }} groupes supplémentaires.
</p>
@endif
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré automatiquement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
