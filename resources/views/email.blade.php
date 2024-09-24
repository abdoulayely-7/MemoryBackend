<!DOCTYPE html>
<html lang="">
<head>
    <title>Notification de Rendez-vous</title>
</head>
<body>
<h1>Bonjour {{ $patient->prenom }},</h1>
<p>Votre rendez-vous prévu pour le {{ $datePlanning }} a été {{ $status }}.</p>
<p>Merci de votre confiance.</p>
<p>Cordialement,<br>L'équipe médicale</p>
</body>
</html>
