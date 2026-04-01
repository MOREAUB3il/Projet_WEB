<?php
session_start();
require_once __DIR__ . '/bdd.php';

// Vérification de sécurité
if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html?status=error&message=' . urlencode('Veuillez vous connecter.'));
    exit;
}

$message = '';

// ==========================================
// TRAITEMENT DE LA CRÉATION DE CAPSULE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $userId = $_SESSION['user']['id'];

    // Vérifie si une image a bien été envoyée sans erreur
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dossierUpload = '../uploads/'; // Le dossier qu'on a créé
        
        // Sécurité : on génère un nom unique pour éviter d'écraser une image existante
        $nomFichier = uniqid() . '-' . basename($_FILES['image']['name']);
        $cheminCible = $dossierUpload . $nomFichier;

        // Vérifie que c'est bien une image (mime type)
        $typeFichier = mime_content_type($_FILES['image']['tmp_name']);
        if (strpos($typeFichier, 'image/') === 0) {
            
            // On déplace l'image du dossier temporaire vers notre dossier "uploads"
            if (move_uploaded_file($_FILES['image']['tmp_name'], $cheminCible)) {
                
                // Si l'image est bien sauvegardée, on enregistre les infos en BDD
                try {
                    $stmt = $pdo->prepare('INSERT INTO capsules (utilisateur_id, chemin_image, description) VALUES (:user_id, :chemin, :desc)');
                    $stmt->execute([
                        ':user_id' => $userId,
                        ':chemin' => $cheminCible,
                        ':desc' => $description
                    ]);
                    $message = "<p style='color: green; text-align: center;'>Capsule créée avec succès !</p>";
                } catch (PDOException $e) {
                    $message = "<p style='color: red; text-align: center;'>Erreur BDD : impossible de sauvegarder.</p>";
                }
            } else {
                $message = "<p style='color: red; text-align: center;'>Erreur lors du téléchargement de l'image sur le serveur.</p>";
            }
        } else {
            $message = "<p style='color: red; text-align: center;'>Le fichier doit être une image valide.</p>";
        }
    } else {
        $message = "<p style='color: red; text-align: center;'>Veuillez sélectionner une image.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Un petit style pour le bouton de validation */
        .btn-creer {
            display: block;
            margin: 20px auto;
            padding: 15px 30px;
            background-color: rgb(205, 121, 37);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-creer:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
<header>
     <div class="logo"><strong></strong></div><br>
    <ul class="Barre">
        <li><a href="#"><input type="text" placeholder="Rechercher..." class="search-input"><button type="submit" class="Brecherche">Rechercher</button></a></li> 
         <li class="push-right">
            <div class="dropdown">
                <button class="dropbtn">Mon Compte ▼</button>
                <div class="dropdown-content">
                    <a href="mes_creations.php">Mes créations</a>
                    <a href="mes_favoris.php">Favoris</a>
                    <a href="../php/menu.php">Menu</a>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <hr>
                        <a href="admin_dashboard.php" style="color: #ffc107; font-weight: bold;">🛡️ Panneau Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="../html/page_accueil.html" class="deco">Déconnexion</a>
                </div>
            </div>
        </li>
    </ul>
</header>

<?= $message ?> <form action="creation.php" method="post" enctype="multipart/form-data">
    <div class="zone">
        <div id="depot-img" class="depot">
            <span id="texte-img">Glissez et déposez une image ici</span>
            <input type="file" name="image" id="input-img" accept="image/*" hidden required>
        </div>

        <div id="depot-text" class="depot">
            <label for="description">Votre Description :</label><br>
            <textarea id="description" name="description" required></textarea>
        </div>
    </div>
    
    <button type="submit" class="btn-creer">✚</button>
</form>

<script src="../js/creation.js"></script>
</body>
</html>