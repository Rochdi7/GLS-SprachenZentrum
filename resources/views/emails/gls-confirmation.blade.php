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
    $groupLabel = !empty($group)
        ? ($group->display_name ?? ($group->name ?? 'Groupe ' . $group->id))
        : 'Aucun groupe sélectionné';
@endphp

@component('emails.layouts.branded', [
    'title' => 'Merci pour votre inscription !',
    'subtitle' => 'GLS Sprachenzentrum — Centre de langue allemande au Maroc',
])

<p style="margin:0 0 14px 0;font-size:16px;">
    Bonjour <strong>{{ $data['nom'] ?? '' }} {{ $data['prenom'] ?? '' }}</strong>,
</p>

<div style="background:#eafbf1;border-left:4px solid #009d5a;padding:14px 18px;border-radius:6px;margin:18px 0;">
    Nous avons bien reçu votre demande d'inscription au <strong>GLS Sprachenzentrum</strong>. Notre équipe pédagogique vous contactera très prochainement pour finaliser votre dossier.
</div>

<div style="margin-top:18px;font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#7a716c;font-weight:600;">
    Détails de votre demande
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #efeae0;border-radius:10px;border-collapse:separate;overflow:hidden;margin:10px 0 8px 0;">
    @include('emails.partials.info-row', ['label' => 'Nom', 'value' => $data['nom'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Prénom', 'value' => $data['prenom'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Email', 'value' => $data['email'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Téléphone', 'value' => $data['phone'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Adresse', 'value' => $data['adresse'] ?? null])
    @include('emails.partials.info-row', ['label' => 'Niveau choisi', 'value' => $data['niveau'] ?? 'Non spécifié'])
    @include('emails.partials.info-row', ['label' => 'Type de cours', 'value' => $typeLabel])
    @if($centreLabel)
        @include('emails.partials.info-row', ['label' => 'Centre', 'value' => $centreLabel])
    @endif
    @include('emails.partials.info-row', ['label' => 'Groupe', 'value' => $groupLabel])
    @if(!empty($data['horaire_prefere']))
        @include('emails.partials.info-row', ['label' => 'Horaire', 'value' => $data['horaire_prefere']])
    @endif
    @if(!empty($data['date_start']))
        @include('emails.partials.info-row', ['label' => 'Date de début', 'value' => $data['date_start']])
    @endif
</table>

<p style="margin:24px 0 0 0;">
    Willkommen bei GLS — bienvenue dans notre école !<br>
    <strong>L'équipe GLS Sprachenzentrum</strong>
</p>

@endcomponent
