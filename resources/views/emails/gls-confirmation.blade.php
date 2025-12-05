<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation Inscription</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:20px;">
    <div style="background:white; padding:25px; border-radius:12px; max-width:600px; margin:auto;">

        <h2 style="color:#111;">Merci pour votre inscription, {{ $data['name'] }} !</h2>

        <p>Nous avons bien reçu votre demande d’inscription au GLS Sprachenzentrum.</p>

        <p style="margin-top:20px;"><strong>Détails de votre demande :</strong></p>

        <p><strong>Niveau choisi :</strong> {{ $data['niveau'] }}</p>
        <p><strong>Centre sélectionné :</strong> {{ $data['centre'] }}</p>

        @if(!empty($data['type_cours']))
            <p><strong>Type de cours :</strong> {{ $data['type_cours'] }}</p>
        @endif

        @if(!empty($data['horaire_prefere']))
            <p><strong>Horaire préféré :</strong> {{ $data['horaire_prefere'] }}</p>
        @endif

        @if(!empty($data['date_start']))
            <p><strong>Date de début :</strong> {{ $data['date_start'] }}</p>
        @endif

        <hr style="margin:25px 0;">

        <p>Notre équipe vous contactera très prochainement pour finaliser votre inscription.</p>

        <p style="color:#555; font-size:14px; margin-top:20px;">
            GLS Sprachenzentrum – Centres de langue allemande au Maroc
        </p>
    </div>
</body>
</html>
