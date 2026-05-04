@component('emails.layouts.branded', [
    'title' => 'Votre demande a été acceptée',
    'subtitle' => 'Attestation de participation — GLS Sprachenzentrum',
])

<p style="margin:0 0 14px 0;font-size:16px;">
    Bonjour <strong>{{ $request->first_name }} {{ $request->last_name }}</strong>,
</p>

<div style="background:#eafbf1;border-left:4px solid #009d5a;padding:14px 18px;border-radius:6px;margin:18px 0;">
    <strong style="color:#007342;">Bonne nouvelle !</strong>
    Votre demande d'attestation de participation a été <strong>acceptée</strong>.
</div>

<p style="margin:0 0 14px 0;">
    Notre équipe finalise actuellement votre document. Vous serez recontacté(e) très prochainement pour la remise de votre attestation.
</p>

<div style="margin-top:22px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Récapitulatif de votre demande
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Groupe', 'value' => $request->group_name])
    @include('emails.partials.info-row', ['label' => 'Niveau', 'value' => $request->level])
</table>

<p style="margin:24px 0 0 0;">
    Merci de votre confiance.<br>
    <strong>L'équipe GLS Sprachenzentrum</strong>
</p>

@endcomponent
