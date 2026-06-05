@component('emails.layouts.branded', [
    'title'    => 'Rapport Hebdomadaire — Étudiants Impayés',
    'subtitle' => $reportData['period_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 20px 0;">
    <tr>
        <td align="center" width="50%" style="padding:0 4px 0 0;">
            <div style="background:#fff3f3;border:1px solid #fbc7c7;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#c1272d;">{{ $reportData['total_unpaid_students'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Étudiants impayés</div>
            </div>
        </td>
        <td align="center" width="50%" style="padding:0 0 0 4px;">
            <div style="background:#fff3f3;border:1px solid #fbc7c7;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:#c1272d;">
                    {{ number_format($reportData['total_outstanding_amount'], 2) }} MAD
                </div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Montant en attente</div>
            </div>
        </td>
    </tr>
</table>

@if(count($reportData['by_center']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Par centre</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 20px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Étudiants</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Montant dû</td>
    </tr>
    @foreach($reportData['by_center'] as $center)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13.5px;">{{ $center['center_name'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;font-weight:600;color:#c1272d;">{{ $center['student_count'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:right;font-weight:700;">{{ number_format($center['outstanding_amount'], 2) }} MAD</td>
    </tr>
    @endforeach
</table>
@endif

@if(count($reportData['students']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Liste des impayés (top {{ min(20, count($reportData['students'])) }})
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Étudiant</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Dû</td>
    </tr>
    @foreach(array_slice($reportData['students'], 0, 20) as $student)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13px;">
            <div style="font-weight:600;">{{ $student['student_name'] }}</div>
            @if($student['phone'])
            <div style="font-size:12px;color:#7a716c;">{{ $student['phone'] }}</div>
            @endif
        </td>
        <td style="padding:8px 12px;font-size:13px;color:#7a716c;">{{ $student['center_name'] }}</td>
        <td style="padding:8px 12px;font-size:13px;text-align:right;font-weight:700;color:#c1272d;">
            {{ number_format($student['outstanding_amount'], 2) }} MAD
        </td>
    </tr>
    @endforeach
</table>
@if(count($reportData['students']) > 20)
<p style="margin:4px 0 0 0;font-size:12px;color:#9a918a;">
    … et {{ count($reportData['students']) - 20 }} autres étudiants.
</p>
@endif
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré automatiquement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
