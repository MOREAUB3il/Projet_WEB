<?php
// Page d'erreur personnalisée
$errorCode = $_GET['code'] ?? 500;
$errorMessage = $_GET['message'] ?? 'Une erreur est survenue';

$errorMessages = [
    403 => 'Accès refusé - Vous n\'avez pas les autorisations nécessaires',
    404 => 'Page non trouvée',
    500 => 'Erreur serveur interne',
    400 => 'Mauvaise requête',
    401 => 'Non authentifié - Veuillez vous connecter'
];

$displayMessage = $errorMessages[$errorCode] ?? $errorMessage;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?php echo htmlspecialchars($errorCode); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .error-code {
            font-size: 72px;
            color: rgb(205, 121, 37);
            margin: 0;
        }
        .error-message {
            font-size: 20px;
            color: #333;
            margin: 20px 0;
        }
        .error-details {
            color: #666;
            font-size: 14px;
            margin: 20px 0;
        }
        .btn-retour {
            display: inline-block;
            background-color: rgb(205, 121, 37);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .btn-retour:hover {
            background-color: rgb(180, 105, 32);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code"><?php echo htmlspecialchars($errorCode); ?></h1>
        <p class="error-message"><?php echo htmlspecialchars($displayMessage); ?></p>
        <p class="error-details">
            <?php 
            if (isset($_SESSION['user'])) {
                echo "Utilisateur connecté : " . htmlspecialchars($_SESSION['user']['nom_utilisateur']);
            } else {
                echo "Vous n'êtes pas connecté";
            }
            ?>
        </p>
        <a href="../php/menu.php" class="btn-retour">↩️ Retour au menu</a>
    </div>
</body>
</html>
