@php
    $typeCours = $data['type_cours'] ?? null;
    $typeLabel = match($typeCours) {
        'presentiel' => 'Cours en présentiel',
        'en_ligne'   => 'Cours en ligne',
        default      => 'Non spécifié',
    };
    $centreLabel = ($typeCours === 'presentiel' && !empty($centre))
        ? ($centre->name . ' — ' . $centre->city)
        : ($typeCours === 'presentiel' ? 'Aucun centre sélectionné' : null);
    $groupSelected = !empty($group);
    $groupLabel = $groupSelected
        ? ($group->display_name ?? ($group->name ?? 'Groupe ' . $group->id))
        : 'Aucun groupe sélectionné';
@endphp

@component('emails.layouts.branded', [
    'title' => 'Nouvelle inscription GLS',
    'subtitle' => 'Notification — backoffice',
])

<p style="margin:0 0 14px 0;">
    Une nouvelle demande d'inscription vient d'être enregistrée via le formulaire du site.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Nom', 'value' => $data['nom'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Prénom', 'value' => $data['prenom'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $data['email'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Téléphone', 'value' => $data['phone'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Adresse', 'value' => $data['adresse'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Niveau', 'value' => $data['niveau'] ?? 'Non spécifié'])
    @include('emails.partials.info-row', ['label' => 'Type de cours', 'value' => $typeLabel])
    @if($centreLabel)
        @include('emails.partials.info-row', ['label' => 'Centre', 'value' => $centreLabel])
    @endif
</table>

@if(!$groupSelected)
<div style="background:#fdf3f3;border-left:4px solid #c1272d;padding:12px 16px;border-radius:6px;margin:16px 0;font-size:14px;color:#3a1517;">
    <strong>À traiter :</strong> aucun groupe sélectionné — merci de contacter le prospect pour proposer un groupe adapté.
</div>
@else
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Groupe', 'value' => $groupLabel])
</table>
@endif

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Horaire préféré', 'value' => $data['horaire_prefere'] ?? 'Non spécifié'])
    @include('emails.partials.info-row', ['label' => 'Date de début souhaitée', 'value' => $data['date_start'] ?? 'Non spécifiée'])
</table>

@endcomponent
