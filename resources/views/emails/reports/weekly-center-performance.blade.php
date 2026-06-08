@component('emails.layouts.branded', [
    'title'    => 'Rapport Hebdomadaire — Performance Centres',
    'subtitle' => $reportData['period_label'],
])

{{-- KPI Summary --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 24px 0;">
    <tr>
        <td align="center" width="50%" style="padding:0 4px 0 0;">
            <div style="background:#f0fdf4;border-radius:10px;padding:16px 10px;text-align:center;border:1px solid #bbf7d0;">
                <div style="font-size:26px;font-weight:700;color:#16a34a;">
                    {{ number_format($reportData['total_revenue'], 0, ',', ' ') }} <span style="font-size:14px;">MAD</span>
                </div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">Revenus semaine</div>
            </div>
        </td>
        <td align="center" width="50%" style="padding:0 0 0 4px;">
            <div style="background:#eff6ff;border-radius:10px;padding:16px 10px;text-align:center;border:1px solid #bfdbfe;">
                <div style="font-size:26px;font-weight:700;color:#1d4ed8;">{{ $reportData['total_new_registrations'] }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">Nouvelles inscriptions</div>
            </div>
        </td>
    </tr>
</table>

@if(count($reportData['centers']) > 0)

{{-- CSS Bar Chart (Gmail-safe: pure table/div, no SVG/JS) --}}
@php
    $chartData = collect($reportData['centers'])->sortByDesc('revenue')->values();
    $maxRev    = $chartData->max('revenue') ?: 1;
    $colors    = ['#1d4ed8','#16a34a','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899'];
@endphp

<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Revenus par centre
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background:#f8fafc;border-radius:10px;padding:0;margin:0 0 20px 0;">
    <tr><td style="padding:14px 14px 8px 14px;">

    @foreach($chartData as $i => $center)
    @php
        $pct   = $maxRev > 0 ? max(2, round($center['revenue'] / $maxRev * 100)) : 2;
        $color = $colors[$i % count($colors)];
        $val   = $center['revenue'] > 0
                    ? number_format($center['revenue'], 0, ',', ' ') . ' MAD'
                    : '—';
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin-bottom:8px;">
        <tr>
            <td width="100" style="font-size:11.5px;color:#374151;padding-right:8px;vertical-align:middle;white-space:nowrap;">
                {{ $center['center_name'] }}
            </td>
            <td style="vertical-align:middle;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="background:#e5e7eb;border-radius:5px;height:20px;">
                    <tr>
                        <td width="{{ $pct }}%" style="background:{{ $color }};border-radius:5px;height:20px;line-height:20px;font-size:0;">&nbsp;</td>
                        <td width="{{ 100 - $pct }}%" style="height:20px;font-size:0;">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td width="90" style="font-size:11px;font-weight:700;color:#111827;padding-left:8px;text-align:right;vertical-align:middle;white-space:nowrap;">
                {{ $val }}
            </td>
        </tr>
    </table>
    @endforeach

    </td></tr>
</table>

{{-- Data table --}}
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">Performance par centre</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Inscrip.</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:center;">Présence</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Revenus</td>
    </tr>
    @foreach($chartData as $center)
    <tr style="border-top:1px solid #efeae0;">
        <td style="padding:8px 12px;font-size:13.5px;font-weight:600;">{{ $center['center_name'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;">{{ $center['new_registrations'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:center;">
            {{ $center['attendance_rate'] !== null ? $center['attendance_rate'] . '%' : '—' }}
        </td>
        <td style="padding:8px 12px;font-size:13.5px;text-align:right;font-weight:700;">
            {{ number_format($center['revenue'], 0, ',', ' ') }} MAD
        </td>
    </tr>
    @endforeach
</table>
@endif

<p style="margin:18px 0 0 0;font-size:12.5px;color:#9a918a;">
    Généré automatiquement le {{ now()->timezone(config('reports.timezone', 'Africa/Casablanca'))->format('d/m/Y à H:i') }}
</p>

@endcomponent
