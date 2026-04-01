<?php
session_start();
require_once __DIR__ . '/bdd.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html');
    exit;
}

$user_id = $_SESSION['user']['id'];

try {
    // Requête avec une "LEFT JOIN" sur les favoris pour savoir si l'utilisateur a liké
    $stmt = $pdo->prepare('
        SELECT c.*, u.nom_utilisateur, f.id AS is_liked
        FROM capsules c
        JOIN utilisateurs u ON c.utilisateur_id = u.id
        LEFT JOIN favoris f ON (f.capsule_id = c.id AND f.utilisateur_id = ?)
        ORDER BY c.cree_le DESC
    ');
    $stmt->execute([$user_id]);
    $capsules = $stmt->fetchAll();

    // Nombre de favoris pour mémo dans le menu
    $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM favoris WHERE utilisateur_id = ?');
    $countStmt->execute([$user_id]);
    $favoriteCount = (int) $countStmt->fetchColumn();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu de Navigation</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body> <header>
    <div class="logo"><strong>MonSite</strong></div><br>
    <ul class="Barre">
        <li><a href="#"><input type="text" placeholder="Rechercher..." class="search-input"><button type="submit" class="Brecherche">Rechercher</button></a></li> 
        <li class="push-right">
            <div class="dropdown">
                <button class="dropbtn">Mon Compte ▼</button>
                <div class="dropdown-content">
                    <a href="creation.php">Mes créations</a>
                    <a href="mes_favoris.php">Favoris (<?= $favoriteCount ?>)</a>
                    <a href="creation.php">Créer</a>
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

<ul class="BLOC" id="nav-links">
    <?php if (empty($capsules)): ?>
        <p style="text-align: center; width: 100%;">Aucune capsule pour le moment. Crée-en une !</p>
    <?php else: ?>
        <?php foreach ($capsules as $capsule): ?>
            <li>
                <a href="#">
                    <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" alt="Capsule" class="menu-icon" onerror="this.src='../assets/image.png';">
                    
                    <span>
                        <?= htmlspecialchars($capsule['description']) ?> <br>
                        <small><i>par <?= htmlspecialchars($capsule['nom_utilisateur']) ?></i></small>
                    </span>

                    <div class="like">
                        <?php $isFavori = !empty($capsule['is_liked']); ?>
                        <button class="heart-container <?= $isFavori ? 'is-active' : '' ?>" onclick="toggleHeart(this, <?= $capsule['id'] ?>)">
                            <svg class="heart-icon" viewBox="0 0 32 32">
                                <path d="M16 28.5L14.1 26.8C7.33 20.65 3 16.73 3 12.05C3 8.27 5.97 5.3 9.75 5.3C11.89 5.3 13.94 6.3 15.28 7.87L16 8.71L16.72 7.87C18.06 6.3 20.11 5.3 22.25 5.3C26.03 5.3 29 8.27 29 12.05C29 16.73 24.67 20.65 17.9 26.8L16 28.5Z" />
                            </svg>
                        </button>
                        
                        <button class="bookmark-btn <?= $isFavori ? 'is-active' : '' ?>" onclick="toggleBookmark(this, <?= $capsule['id'] ?>)">
                            <svg class="bookmark-icon" viewBox="0 0 32 32">
                                <path d="M6 5C6 3.89543 6.89543 3 8 3H24C25.1046 3 26 3.89543 26 5V28L16 21L6 28V5Z" />
                            </svg>
                        </button>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<script src="../js/menu.js"></script>
</body>
</html>