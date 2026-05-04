@props(['label', 'value'])
<tr>
    <td style="padding:10px 14px;border-bottom:1px solid #efeae0;font-size:13px;color:#7a716c;width:38%;text-transform:uppercase;letter-spacing:.6px;font-weight:600;vertical-align:top;">
        {{ $label }}
    </td>
    <td style="padding:10px 14px;border-bottom:1px solid #efeae0;font-size:14.5px;color:#1d1a18;font-weight:500;vertical-align:top;">
        {!! $value !== null && $value !== '' ? e($value) : '<span style="color:#bdb6ad;">—</span>' !!}
    </td>
</tr>
