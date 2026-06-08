@php
    $payload        = $report->payload ?? [];
    $centersRanking = collect($payload['centers_ranking'] ?? []);
    $attentionItems = $payload['attention_items'] ?? [];
    $topCenter      = $payload['top_center_today'] ?? null;
@endphp

@component('emails.layouts.branded', [
    'title'    => 'Rapport CEO Quotidien',
    'subtitle' => $report->report_date->format('d/m/Y'),
])

{{-- KPI Row --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin:0 0 22px 0;">
    <tr>
        <td width="33%" align="center" style="padding:0 4px 0 0;">
            <div style="background:#f0fdf4;border-radius:10px;padding:14px 10px;text-align:center;border:1px solid #bbf7d0;">
                <div style="font-size:22px;font-weight:700;color:#16a34a;">
                    {{ number_format((float) ($report->revenue_yesterday ?? 0), 0, ',', ' ') }}
                    <span style="font-size:13px;font-weight:500;">MAD</span>
                </div>
                <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">Encaissement hier</div>
            </div>
        </td>
        <td width="33%" align="center" style="padding:0 4px;">
            <div style="background:#eff6ff;border-radius:10px;padding:14px 10px;text-align:center;border:1px solid #bfdbfe;">
                <div style="font-size:22px;font-weight:700;color:#1d4ed8;">{{ $report->new_registrations ?? '—' }}</div>
                <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">Nouvelles inscriptions</div>
            </div>
        </td>
        <td width="33%" align="center" style="padding:0 0 0 4px;">
            <div style="background:#fffbeb;border-radius:10px;padding:14px 10px;text-align:center;border:1px solid #fde68a;">
                @if($topCenter)
                    <div style="font-size:15px;font-weight:700;color:#92400e;">{{ $topCenter['name'] }}</div>
                    <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">{{ number_format($topCenter['amount'], 0, ',', ' ') }} MAD · Top centre</div>
                @else
                    <div style="font-size:15px;font-weight:700;color:#92400e;">—</div>
                    <div style="font-size:11.5px;color:#6b7280;margin-top:4px;">Top centre hier</div>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- SVG Bar Chart --}}
@if($centersRanking->count() > 0)
@php
    $sorted   = $centersRanking->sortByDesc('amount')->values();
    $maxAmt   = $sorted->max('amount') ?: 1;
    $barH     = 22;
    $gap      = 10;
    $labelW   = 110;
    $barMaxW  = 260;
    $valW     = 90;
    $totalW   = $labelW + $barMaxW + $valW + 16;
    $svgH     = $sorted->count() * ($barH + $gap) + 10;
    $colors   = ['#16a34a','#1d4ed8','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#ec4899'];
@endphp

<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Encaissements par centre — hier
</p>

<div style="background:#f8fafc;border-radius:10px;padding:16px;margin:0 0 20px 0;overflow:hidden;">
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $totalW }}" height="{{ $svgH }}"
     viewBox="0 0 {{ $totalW }} {{ $svgH }}"
     style="display:block;max-width:100%;font-family:Arial,Helvetica,sans-serif;">

    @foreach($sorted as $i => $center)
    @php
        $y       = $i * ($barH + $gap) + 5;
        $barW    = round($center['amount'] / $maxAmt * $barMaxW);
        $color   = $colors[$i % count($colors)];
        $name    = $center['name'] ?? '';
        $label   = mb_strlen($name) > 14 ? mb_substr($name, 0, 13) . '…' : $name;
        $valText = $center['amount'] > 0
                    ? number_format($center['amount'], 0, ',', ' ') . ' MAD'
                    : '—';
    @endphp
    <text x="{{ $labelW - 8 }}" y="{{ $y + $barH * 0.68 }}"
          text-anchor="end" font-size="11.5" fill="#374151">{{ $label }}</text>
    <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ $barMaxW }}" height="{{ $barH }}"
          rx="5" fill="#e5e7eb"/>
    @if($barW > 0)
    <rect x="{{ $labelW }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $barH }}"
          rx="5" fill="{{ $color }}" opacity="0.85"/>
    @endif
    <text x="{{ $labelW + $barMaxW + 8 }}" y="{{ $y + $barH * 0.68 }}"
          font-size="11" fill="#111827" font-weight="bold">{{ $valText }}</text>
    @endforeach

</svg>
</div>

{{-- Ranking table --}}
<p style="margin:0 0 10px 0;font-weight:700;font-size:14px;color:#181615;">
    Classement des centres
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 20px 0;">
    <tr style="background:#f4f1ea;">
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;width:36px;">#</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;">Centre</td>
        <td style="padding:8px 12px;font-size:12px;font-weight:700;color:#7a716c;text-align:right;">Encaissé (MAD)</td>
    </tr>
    @foreach($centersRanking as $i => $center)
    @php
        $rank  = $i + 1;
        $medal = match($rank) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $rank };
        $rowBg = $center['amount'] == 0 ? '#fafafa' : '#ffffff';
        $amtColor = $center['amount'] > 0 ? '#181615' : '#9a918a';
    @endphp
    <tr style="border-top:1px solid #efeae0;background:{{ $rowBg }};">
        <td style="padding:8px 12px;font-size:15px;">{{ $medal }}</td>
        <td style="padding:8px 12px;font-size:13.5px;font-weight:600;color:{{ $amtColor }};">{{ $center['name'] }}</td>
        <td style="padding:8px 12px;font-size:13.5px;font-weight:700;text-align:right;color:{{ $amtColor }};">
            {{ $center['amount'] > 0 ? number_format($center['amount'], 0, ',', ' ') : '—' }}
        </td>
    </tr>
    @endforeach
</table>
@endif

{{-- Attention items --}}
@if(count($attentionItems) > 0)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin:0 0 20px 0;">
    <div style="font-weight:700;font-size:13px;color:#92400e;margin-bottom:8px;">⚠️ Points d'attention</div>
    @foreach($attentionItems as $item)
    <div style="font-size:13px;color:#78350f;padding:3px 0;">• {{ $item }}</div>
    @endforeach
</div>
@else
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin:0 0 20px 0;font-size:13px;color:#15803d;">
    ✅ Aucun point d'attention — tous les indicateurs sont normaux.
</div>
@endif

<p style="margin:18px 0 0 0;font-size:12px;color:#9a918a;border-top:1px solid #efeae0;padding-top:14px;">
    Rapport généré le {{ $report->generated_at?->timezone('Africa/Casablanca')->format('d/m/Y à H:i') ?? '—' }}
    &nbsp;·&nbsp; GLS Portal
</p>

@endcomponent
