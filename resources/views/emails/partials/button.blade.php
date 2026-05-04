@props(['url', 'color' => 'primary'])
@php
    $palette = [
        'primary' => ['bg' => '#1c45db', 'border' => '#1c45db'],
        'success' => ['bg' => '#009d5a', 'border' => '#009d5a'],
        'danger'  => ['bg' => '#c1272d', 'border' => '#c1272d'],
        'dark'    => ['bg' => '#181615', 'border' => '#181615'],
    ];
    $c = $palette[$color] ?? $palette['primary'];
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px auto;">
    <tr>
        <td align="center" style="border-radius:8px;background:{{ $c['bg'] }};">
            <a href="{{ $url }}" target="_blank"
               style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;border:1px solid {{ $c['border'] }};letter-spacing:.3px;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
