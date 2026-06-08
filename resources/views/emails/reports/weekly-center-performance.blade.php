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

{{-- SVG Bar Chart --}}
@php
    $centers   = collect($reportData['centers'])->sortByDesc('revenue')->values();
    $maxRev    = $centers->max('revenue') ?: 1;
    $barH      = 22;
    $gap       = 10;
    $labelW    = 110;
    $barMaxW   = 260;
    $valW      = 80;
    $totalW    = $labelW + $barMaxW + $valW + 16;
    $svgH      = $centers->count() * ($barH + $gap) + 10;
    $colors    = ['#1d4ed8','#16a34a','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899'];
@endphp

<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Revenus par centre
</p>

<div style="background:#f8fafc;border-radius:10px;padding:16px;margin:0 0 20px 0;overflow:hidden;">
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $totalW }}" height="{{ $svgH }}"
     viewBox="0 0 {{ $totalW }} {{ $svgH }}"
     style="display:block;max-width:100%;font-family:Arial,Helvetica,sans-serif;">

    @foreach($centers as $i => $center)
    @php
        $y       = $i * ($barH + $gap) + 5;
        $barW    = $maxRev > 0 ? round($center['revenue'] / $maxRev * $barMaxW) : 0;
        $color   = $colors[$i % count($colors)];
        $label   = mb_strlen($center['center_name']) > 14
                    ? mb_substr($center['center_name'], 0, 13) . '…'
                    : $center['center_name'];
        $valText = $center['revenue'] > 0
                    ? number_format($center['revenue'], 0, ',', ' ') . ' MAD'
                    : '—';
    @endphp

    {{-- Label --}}
    <text x="{{ $labelW - 8 }}" y="{{ $y + $barH * 0.68 }}"
          text-anchor="end" font-size="11.5" fill="#374151">{{ $label }}</text>

    {{-- Bar background --}}
    <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ $barMaxW }}" height="{{ $barH }}"
          rx="5" fill="#e5e7eb"/>

    {{-- Bar fill --}}
    @if($barW > 0)
    <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $barH }}"
          rx="5" fill="{{ $color }}" opacity="0.85"/>
    @endif

    {{-- Value --}}
    <text x="{{ $labelW + $barMaxW + 8 }}" y="{{ $y + $barH * 0.68 }}"
          font-size="11" fill="#111827" font-weight="bold">{{ $valText }}</text>

    @endforeach
</svg>
</div>

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
    @foreach($centers as $center)
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
