<?php
session_start();
require_once __DIR__ . '/bdd.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html');
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT c.*, u.nom_utilisateur, f.id AS is_favori
    FROM capsules c
    JOIN favoris f ON f.capsule_id = c.id
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    WHERE f.utilisateur_id = ?
    ORDER BY f.cree_le DESC
');
$stmt->execute([$user_id]);
$mes_favoris = $stmt->fetchAll();
$favoriteCount = count($mes_favoris);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Favoris</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header>
    <div class="logo"><strong>Bloutub</strong></div><br>
    <ul class="Barre">
        <li><a href="#"><input type="text" placeholder="Rechercher..." class="search-input"><button type="submit" class="Brecherche">Rechercher</button></a></li> 
         <li class="push-right">
            <div class="dropdown">
                <button class="dropbtn">Mon Compte ▼</button>
                <div class="dropdown-content">
                    <a href="mes_creations.php">Mes créations</a>
                    <a href="../php/menu.php">Menu</a>
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

<div id="flash-message" style="display:none; position:fixed; top:80px; right:20px; padding:10px 14px; border-radius:4px; z-index:1000; font-weight:bold;"></div>

<h2 style="text-align:center; margin-top:20px;">Mes Favoris (<span id="favorite-count"><?= $favoriteCount ?></span>)</h2>

<ul class="BLOC" id="nav-links">
    <?php if (empty($mes_favoris)): ?>
        <p style="text-align: center; width: 100%;">Aucun favori pour le moment. Ajoutez des capsules en favoris depuis le menu.</p>
    <?php else: ?>
        <?php foreach ($mes_favoris as $capsule): ?>
            <?php $isFavori = !empty($capsule['is_favori']); ?>
            <li>
                <a href="#">
                    <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" alt="Capsule" class="menu-icon" onerror="this.src='../assets/image.png';">
                    <span>
                        <?= htmlspecialchars($capsule['description']) ?> <br>
                        <small><i>par <?= htmlspecialchars($capsule['nom_utilisateur']) ?></i></small>
                    </span>
                </a>
                <div class="like">
                    <button class="bookmark-btn <?= $isFavori ? 'is-active' : '' ?>" onclick="toggleBookmark(this, <?= $capsule['id'] ?>)">
                        <svg class="bookmark-icon" viewBox="0 0 32 32">
                            <path d="M6 5C6 3.89543 6.89543 3 8 3H24C25.1046 3 26 3.89543 26 5V28L16 21L6 28V5Z" />
                        </svg>
                    </button>
                    <button class="remove-btn" onclick="removeFavorite(this, <?= $capsule['id'] ?>)">Retirer</button>
                </div>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<script src="../js/menu.js"></script>
</body>
</html>