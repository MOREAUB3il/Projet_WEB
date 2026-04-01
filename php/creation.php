<?php
session_start();
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/image_optimizer.php';

// Vérification de sécurité
if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html?status=error&message=' . urlencode('Veuillez vous connecter.'));
    exit;
}

$message = '';
$messageType = 'info'; // success, error, warning

// CONFIG D'UPLOAD SÉCURISÉE
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_DIR', '../uploads/');

// Créer le dossier uploads s'il n'existe pas
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ==========================================
// TRAITEMENT DE LA CRÉATION DE CAPSULE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $description = trim($_POST['description'] ?? '');
        $userId = $_SESSION['user']['id'];

        // Valider la description
        if (empty($description)) {
            throw new Exception('La description est obligatoire');
        }
        if (strlen($description) > 1000) {
            throw new Exception('La description ne peut pas dépasser 1000 caractères');
        }

        // Vérifie si une image a bien été envoyée sans erreur
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite serveur)',
                UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire)',
                UPLOAD_ERR_PARTIAL => 'Fichier téléchargé partiellement',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier sélectionné',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture de fichier'
            ];
            throw new Exception($uploadErrors[$errorCode] ?? 'Erreur d\'upload');
        }

        // Vérifier la taille du fichier
        if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
            throw new Exception('Le fichier dépasse 5 MB');
        }

        // Vérifier le type MIME (vrai, pas juste l'extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_MIMES)) {
            throw new Exception('Format d\'image non autorisé (JPEG, PNG, WebP, GIF acceptés)');
        }

        // Générer un nom de fichier sécurisé et unique
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            throw new Exception('Extension fichier non valide');
        }

        // Créer un nom unique et sécurisé
        $randomName = bin2hex(random_bytes(10)); // Génère 20 caractères aléatoires
        $newFileName = $randomName . '.' . strtolower($fileExtension);
        $uploadPath = UPLOAD_DIR . $newFileName;

        // Vérifier que le fichier n'existe pas déjà
        if (file_exists($uploadPath)) {
            throw new Exception('Le fichier existe déjà, veuillez réessayer');
        }

        // Déplacer le fichier en toute sécurité
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            throw new Exception('Erreur lors du déplacement du fichier sur le serveur');
        }

        // Optimiser l'image (compression, redimensionnement, thumbnail)
        $optimizedPath = optimizeImage($uploadPath);
        if (!$optimizedPath) {
            // Si l'optimisation échoue, utiliser le chemin original
            $optimizedPath = $uploadPath;
        } else {
            $uploadPath = $optimizedPath;
        }

        // Enregistrer en BDD avec try/catch
        try {
            $stmt = $pdo->prepare('INSERT INTO capsules (utilisateur_id, chemin_image, description) VALUES (:user_id, :chemin, :desc)');
            $stmt->execute([
                ':user_id' => $userId,
                ':chemin' => $uploadPath,
                ':desc' => $description
            ]);
            $message = "✓ Capsule créée avec succès et optimisée !";
            $messageType = 'success';
        } catch (PDOException $e) {
            // Si la BDD échoue, supprimer le fichier
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            throw new Exception('Erreur base de données: impossible de sauvegarder');
        }
    } catch (Exception $e) {
        $message = "❌ " . $e->getMessage();
        $messageType = 'error';
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
        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            animation: slideIn 0.3s ease-in;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .toast.success {
            background-color: #4CAF50;
        }
        .toast.error {
            background-color: #f44336;
        }
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
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
                        <a href="admin_dashboard.php">Panneau Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="../html/page_accueil.html" class="deco">Déconnexion</a>
                </div>
            </div>
        </li>
    </ul>
</header>

<?php if (!empty($message)): ?>
    <div id="toast" class="toast <?php echo htmlspecialchars($messageType); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <script>
        // Fermer le toast après 3 secondes
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    </script>
<?php endif; ?> <form action="creation.php" method="post" enctype="multipart/form-data">
    <div class="zone">
        <div id="depot-img" class="depot">
            <span id="texte-img">Glissez et déposez une image ici</span>
            <input type="file" name="image" id="input-img" accept="image/*" hidden required>
        </div>

        <div id="depot-text" class="depot">
            <label for="description">Votre Description :</label><br>
            <textarea id="description" name="description" maxlength="1000" required></textarea>
            <small id="char-count" style="color: #999; font-size: 12px;">0/1000 caractères</small>
        </div>
    </div>
    
    <button type="submit" class="btn-creer">✚</button>
</form>

<div id="toast-container"></div>

<script src="../js/creation.js"></script>
<script src="../js/pagination.js"></script>
<script>
    // Compteur de caractères
    const descInput = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    
    if (descInput) {
        descInput.addEventListener('input', () => {
            charCount.textContent = descInput.value.length + '/1000 caractères';
        });
    }
    
    // Gestion des toasts
    function showFlash(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Afficher toast si message de succès
    <?php if (isset($_GET['success'])): ?>
        showFlash('Image créée avec succès ! ✓', 'success');
    <?php endif; ?>
</script>
</body>
</html>