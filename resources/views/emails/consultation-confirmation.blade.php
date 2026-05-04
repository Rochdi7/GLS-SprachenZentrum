@component('emails.layouts.branded', [
    'title' => 'Votre demande de consultation est bien reçue',
    'subtitle' => 'GLS Sprachenzentrum — Apprentissage de l\'allemand au Maroc',
])

<p style="margin:0 0 14px 0;font-size:16px;">
    Bonjour <strong>{{ $consultation->name }}</strong>,
</p>

<p style="margin:0 0 14px 0;">
    Nous avons bien reçu votre demande de consultation. Un de nos conseillers pédagogiques vous contactera <strong>très prochainement</strong> pour discuter de votre projet d'apprentissage de l'allemand.
</p>

<div style="margin-top:18px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Récapitulatif
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Ville', 'value' => $consultation->city])
    @include('emails.partials.info-row', ['label' => 'Téléphone', 'value' => $consultation->phone])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $consultation->email])
    @include('emails.partials.info-row', ['label' => 'Demandée le', 'value' => $consultation->created_at->format('d/m/Y H:i')])
</table>

<p style="margin:24px 0 0 0;">
    À très bientôt,<br>
    <strong>L'équipe GLS Sprachenzentrum</strong>
</p>

@endcomponent
