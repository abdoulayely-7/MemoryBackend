<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du Mot de Passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-content {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }
        .hospital-info h1 {
            margin: 0;
            color: #217ba1; /* Couleur du nom de l'hôpital */
            font-size: 24px;
        }
        h1 {
            color: #217ba1; /* Couleur du titre principal */
            font-size: 22px;
            margin-top: 0;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        a {
            color: #217ba1; /* Couleur des liens */
            text-decoration: none;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #217ba1; /* Couleur du bouton */
            border-radius: 5px;
            text-decoration: none;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #aaa;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-content">
        <img src="{{ asset('public/medical.ico') }}" alt="Logo" class="logo">
        <div class="hospital-info">
            <h1>ABASS NDAO</h1>
        </div>
    </div>
    <h1>Réinitialisation de Mot de Passe</h1>
    <p>Bonjour,</p>
    <p>Vous avez demandé une réinitialisation de mot de passe. Veuillez cliquer sur le lien ci-dessous pour réinitialiser votre mot de passe :</p>
    <p><a href="{{ $resetUrl }}" class="button">Réinitialiser le Mot de Passe</a></p>
    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement ce message.</p>
    <p>Merci,<br>Votre équipe de support</p>
    <div class="footer">
        &copy; {{ date('Y') }} ABASS NDAO. Tous droits réservés.
    </div>
</div>
</body>
</html>
