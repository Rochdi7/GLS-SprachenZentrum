@component('emails.layouts.branded', [
    'title' => "Nouvelle demande d'attestation",
    'subtitle' => 'Une étudiant(e) vient de soumettre une demande via le site',
])

<p style="margin:0 0 18px 0;">
    Une nouvelle <strong>demande d'attestation de participation</strong> a été déposée. Les détails sont récapitulés ci-dessous.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:18px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Étudiant', 'value' => $request->last_name . ' ' . $request->first_name])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $request->email])
    @include('emails.partials.info-row', ['label' => 'Téléphone', 'value' => $request->phone])
    @include('emails.partials.info-row', ['label' => 'Date de naissance', 'value' => $request->birth_date?->format('d/m/Y')])
    @include('emails.partials.info-row', ['label' => 'Lieu de naissance', 'value' => $request->birth_place])
    @include('emails.partials.info-row', ['label' => 'Groupe', 'value' => $request->group_name])
    @include('emails.partials.info-row', ['label' => 'Niveau', 'value' => $request->level])
</table>

@if(filled($request->notes))
<div style="margin-top:18px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Notes de l'étudiant
</div>
<div style="margin-top:8px;background:#fbf8f1;border:1px solid #efeae0;border-radius:10px;padding:14px 16px;font-size:14.5px;line-height:1.7;color:#2d2926;white-space:pre-wrap;">{{ $request->notes }}</div>
@endif

@component('emails.partials.button', ['url' => route('backoffice.attestation_requests.show', $request->id), 'color' => 'primary'])
    Examiner la demande
@endcomponent

<p style="margin:18px 0 0 0;font-size:13.5px;color:#6e6660;">
    Connectez-vous au backoffice pour accepter ou refuser cette demande.
</p>

@endcomponent
