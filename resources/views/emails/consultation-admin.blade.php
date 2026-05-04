@component('emails.layouts.branded', [
    'title' => 'Nouvelle demande de consultation',
    'subtitle' => 'Notification — backoffice',
])

<p style="margin:0 0 14px 0;">
    Une nouvelle demande de consultation vient d'être enregistrée.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Nom', 'value' => $consultation->name])
    @include('emails.partials.info-row', ['label' => 'Ville', 'value' => $consultation->city])
    @include('emails.partials.info-row', ['label' => 'Téléphone', 'value' => $consultation->phone])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $consultation->email])
    @include('emails.partials.info-row', ['label' => 'Reçue le', 'value' => $consultation->created_at->format('d/m/Y H:i')])
</table>

<p style="margin:18px 0 0 0;font-size:13.5px;color:#6e6660;">
    Pensez à recontacter le prospect dans les meilleurs délais.
</p>

@endcomponent
