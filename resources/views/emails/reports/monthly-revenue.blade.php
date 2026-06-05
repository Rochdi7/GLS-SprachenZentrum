@component('emails.layouts.branded', [
    'title'    => 'Rapport Mensuel — Revenus',
    'subtitle' => $reportData['month_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 20px 0;">
    <tr>
        <td align="center" width="33%" style="padding:0 4px 0 0;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#181615;">
                    {{ number_format($reportData['total_revenue'], 2) }} MAD
                </div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Revenus totaux</div>
            </div>
        </td>
        <td align="center" width="33%" style="padding:0 4px;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#181615;">{{ $reportData['total_registrations'] }}</div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Inscriptions</div>
            </div>
        </td>
        <td align="center" width="33%" style="padding:0 0 0 4px;">
            <div style="background:#f4f1ea;border-radius:10px;padding:14px 10px;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#181615;">
                    {{ number_format($reportData['avg_daily_revenue'], 2) }} MAD
                </div>
                <div style="font-size:12px;color:#7a716c;margin-top:3px;">Moy. journalière</div>
            </div>
        </td>
    </tr>
</table>

@if(count($reportData['by_center']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Revenus par centre</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 20px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Paiements</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Revenus</td>
    </tr>
    @foreach($reportData['by_center'] as $center)
    @php
        $pct = $reportData['total_revenue'] > 0
            ? round($center['revenue'] / $reportData['total_revenue'] * 100, 1)
            : 0;
    @endphp
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13.5px;font-weight:600;">
            {{ $center['center_name'] }}
            <div style="font-size:11px;color:#9a918a;font-weight:400;">{{ $pct }}% du total</div>
        </td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;">{{ $center['payments'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:right;font-weight:700;">
            {{ number_format($center['revenue'], 2) }} MAD
        </td>
    </tr>
    @endforeach
</table>
@endif

@if(count($reportData['daily_breakdown']) > 0)
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Détail journalier ({{ count($reportData['daily_breakdown']) }} jours avec paiements)
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Date</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Transactions</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Revenus</td>
    </tr>
    @foreach($reportData['daily_breakdown'] as $day)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:7px 12px;font-size:13px;">
            {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
        </td>
        <td style="padding:7px 12px;font-size:13px;text-align:center;">{{ $day['count'] }}</td>
        <td style="padding:7px 12px;font-size:13px;text-align:right;font-weight:600;">
            {{ number_format($day['revenue'], 2) }} MAD
        </td>
    </tr>
    @endforeach
</table>
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré manuellement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
