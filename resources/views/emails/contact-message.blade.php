@component('emails.layouts.branded', [
    'title' => 'Nouveau message — formulaire de contact',
    'subtitle' => 'GLS Sprachenzentrum',
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:0 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Nom', 'value' => $data['name']])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $data['email']])
    @include('emails.partials.info-row', ['label' => 'Sujet', 'value' => $data['subject']])
</table>

<div style="margin-top:22px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Message
</div>

<div style="margin-top:10px;background:#fbf8f1;border:1px solid #efeae0;border-radius:10px;padding:16px 18px;font-size:14.5px;line-height:1.7;color:#2d2926;white-space:pre-wrap;">{{ $data['message'] }}</div>

@isset($data['email'])
@component('emails.partials.button', ['url' => 'mailto:' . $data['email'], 'color' => 'primary'])
    Répondre directement
@endcomponent
@endisset

@endcomponent
