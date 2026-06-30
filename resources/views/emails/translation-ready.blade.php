@component('emails.layouts.branded', [
    'title' => 'Vos documents sont prêts',
    'subtitle' => 'Service de traduction — GLS Sprachenzentrum',
])

<p style="margin:0 0 14px 0;font-size:16px;">
    Bonjour <strong>{{ $translation->student_name }}</strong>,
</p>

<div style="background:#eafbf1;border-left:4px solid #009d5a;padding:14px 18px;border-radius:6px;margin:18px 0;">
    <strong style="color:#007342;">Bonne nouvelle !</strong>
    La traduction de vos documents est <strong>terminée</strong> et vos papiers sont
    <strong>prêts à être récupérés</strong> dans nos bureaux.
</div>

<p style="margin:0 0 14px 0;">
    Vous pouvez passer dès maintenant chez <strong>GLS Sprachenzentrum</strong> pour retirer
    vos documents originaux ainsi que leur traduction.
</p>

<div style="background:#fff6e9;border-left:4px solid #e08a00;padding:14px 18px;border-radius:6px;margin:18px 0;">
    <span style="color:#9a5a00;">Montant total à régler :</span>
    <strong style="color:#9a5a00;font-size:17px;">{{ number_format((int) $translation->total_cost, 0, ',', ' ') }} DH</strong>
    <div style="font-size:13px;color:#9a5a00;margin-top:4px;">Merci de prévoir ce montant lors du retrait.</div>
</div>

<div style="margin-top:22px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Récapitulatif de votre commande
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'CIN', 'value' => $translation->cin])
    @include('emails.partials.info-row', ['label' => 'Documents', 'value' => $translation->items->pluck('doc_type')->implode(', ')])
    @include('emails.partials.info-row', ['label' => 'Nombre de pages', 'value' => $translation->totalPages()])
    @include('emails.partials.info-row', ['label' => 'Montant total', 'value' => number_format((int) $translation->total_cost, 0, ',', ' ') . ' DH'])
</table>

<p style="margin:24px 0 0 0;">
    Merci de votre confiance.<br>
    <strong>L'équipe GLS Sprachenzentrum</strong>
</p>

@endcomponent
