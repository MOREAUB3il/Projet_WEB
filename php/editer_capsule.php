<?php
session_start();
require_once __DIR__ . '/bdd.php';

// Vérification de sécurité
if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html');
    exit;
}

$userId = $_SESSION['user']['id'];
$capsuleId = $_GET['id'] ?? null;
$message = '';

if (!$capsuleId) {
    die("ID de capsule manquant.");
}

// 1. Récupérer les infos de la capsule (en vérifiant que c'est bien la sienne !)
$stmt = $pdo->prepare('SELECT * FROM capsules WHERE id = ? AND utilisateur_id = ?');
$stmt->execute([$capsuleId, $userId]);
$capsule = $stmt->fetch();

if (!$capsule) {
    die("Capsule introuvable ou vous n'avez pas l'autorisation de la modifier.");
}

// 2. Traitement du formulaire si on clique sur Modifier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $cheminFinal = $capsule['chemin_image']; // Par défaut, on garde l'ancienne image

    // Si l'utilisateur a uploadé une NOUVELLE image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dossierUpload = '../uploads/'; 
        $nomFichier = uniqid() . '-' . basename($_FILES['image']['name']);
        $cheminCible = $dossierUpload . $nomFichier;
        $typeFichier = mime_content_type($_FILES['image']['tmp_name']);

        if (strpos($typeFichier, 'image/') === 0) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $cheminCible)) {
                $cheminFinal = $cheminCible; // On utilise la nouvelle image
                
                // Optionnel : on supprime l'ancienne image du serveur pour faire de la place
                if (file_exists($capsule['chemin_image'])) {
                    unlink($capsule['chemin_image']);
                }
            } else {
                $message = "<p style='color: red; text-align: center;'>Erreur lors du téléchargement de l'image.</p>";
            }
        } else {
            $message = "<p style='color: red; text-align: center;'>Le fichier doit être une image valide.</p>";
        }
    }

    // 3. Mise à jour de la base de données
    if (empty($message)) {
        try {
            $updateStmt = $pdo->prepare('UPDATE capsules SET description = ?, chemin_image = ? WHERE id = ?');
            $updateStmt->execute([$description, $cheminFinal, $capsuleId]);
            
            // On le renvoie vers ses créations une fois terminé !
            header('Location: mes_creations.php');
            exit;
        } catch (PDOException $e) {
            $message = "<p style='color: red; text-align: center;'>Erreur BDD : impossible de modifier.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la capsule</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .btn-modifier {
            display: block;
            margin: 20px auto;
            padding: 15px 30px;
            background-color: #f39c12; /* Orange pour l'édition */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-modifier:hover {
            transform: scale(1.05);
        }
        .preview-actuelle {
            width: 100%;
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
        }
    </style>
</head>
<body>
<header>
     <div class="logo"><strong>Bloutub</strong></div><br>
    <ul class="Barre">
        <li><a href="#"><input type="text" placeholder="Rechercher..." class="search-input"><button type="submit" class="Brecherche">Rechercher</button></a></li> 
        <li class="push-right">
            <a href="mes_creations.php" style="color: peru; font-weight: bold; text-decoration: none;">◀ Retour</a>
        </li>
    </ul>
</header>

<h2 style="text-align: center; margin-top: 20px; color: peru;">Modifier ma capsule</h2>

<?= $message ?> 

<form action="editer_capsule.php?id=<?= $capsule['id'] ?>" method="post" enctype="multipart/form-data">
    <div class="zone">
    <div id="depot-img" class="depot" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px; box-sizing: border-box; overflow: hidden;">
        
        <span id="texte-img" style="margin-bottom: 10px; font-size: 14px; font-weight: bold; text-align: center;">Cliquez pour remplacer l'image</span>
        <input type="file" name="image" id="input-img" accept="image/*" hidden>
        
        <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" id="preview-actuelle" style="max-width: 100%; max-height: 160px; object-fit: contain; border-radius: 8px; display: block;" alt="Image actuelle">
        
    </div>

    <div id="depot-text" class="depot">
        <label for="description">Modifier la description :</label><br>
        <textarea id="description" name="description" required><?= htmlspecialchars($capsule['description']) ?></textarea>
    </div>
</div>
    
    <button type="submit" class="btn-modifier">💾 Enregistrer les modifications</button>
</form>

<script src="../js/creation.js"></script>

<script>
    const inputFichier = document.getElementById('input-img');
    const zoneDepot = document.getElementById('depot-img');
    const ancienneImage = document.getElementById('preview-actuelle');

    // Fonction qui fait disparaître l'ancienne image
    function cacherAncienneImage() {
        if (ancienneImage) {
            ancienneImage.style.display = 'none';
        }
    }

    // On déclenche la disparition si on choisit un fichier via le bouton
    inputFichier.addEventListener('change', cacherAncienneImage);
    
    // On déclenche la disparition si on glisse-dépose (drag & drop) un fichier
    zoneDepot.addEventListener('drop', cacherAncienneImage);
</script>
</body>
</html>