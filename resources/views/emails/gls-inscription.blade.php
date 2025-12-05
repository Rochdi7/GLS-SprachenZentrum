<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle inscription GLS</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">
    <div style="background:white; padding:25px; border-radius:12px; max-width:600px; margin:auto;">
        <h2 style="color:#111;">Nouvelle inscription GLS</h2>

        <p><strong>Nom :</strong> {{ $data['name'] }}</p>
        <p><strong>Email :</strong> {{ $data['email'] }}</p>
        <p><strong>Téléphone :</strong> {{ $data['phone'] }}</p>
        <p><strong>Adresse :</strong> {{ $data['adresse'] }}</p>

        <p><strong>Niveau :</strong> {{ $data['niveau'] }}</p>
        <p><strong>Type de cours :</strong> {{ $data['type_cours'] ?? 'Non spécifié' }}</p>
        <p><strong>Horaire préféré :</strong> {{ $data['horaire_prefere'] ?? 'Non spécifié' }}</p>
        <p><strong>Date de début :</strong> {{ $data['date_start'] ?? 'Non spécifiée' }}</p>

        <p><strong>Centre choisi :</strong> {{ $data['centre'] }}</p>

        <hr>

        <p style="font-size:14px; color:#666;">GLS Sprachenzentrum – Maroc</p>
    </div>
</body>
</html>
