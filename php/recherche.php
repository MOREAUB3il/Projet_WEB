<?php
session_start();
require_once __DIR__ . '/bdd.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html');
    exit;
}

$user_id = $_SESSION['user']['id'];

$q = trim($_GET['q'] ?? '');

try {
    // 1. Compteur pour le menu
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM favoris WHERE utilisateur_id = ?');
    $countStmt->execute([$user_id]);
    $favoriteCount = (int) $countStmt->fetchColumn();

    $capsules = [];
    
    // 2. Recherche en BDD
    if (!empty($q)) {
        $stmt = $pdo->prepare('
            SELECT c.*, u.nom_utilisateur, f.id AS is_liked 
            FROM capsules c 
            JOIN utilisateurs u ON c.utilisateur_id = u.id 
            LEFT JOIN favoris f ON (f.capsule_id = c.id AND f.utilisateur_id = :user_id)
            WHERE c.description LIKE :search OR u.nom_utilisateur LIKE :search
            ORDER BY c.cree_le DESC
        ');
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':search' => '%' . $q . '%'
        ]);
        
        $capsules = $stmt->fetchAll();
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recherche - Bloutub</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
      .btn-annuler {
          background-color: rosybrown;
          color: white;
          padding: 10px 20px;
          border-radius: 20px;
          text-decoration: none;
          font-weight: bold;
          font-size: 14px;
          display: inline-block;
          transition: transform 0.2s, background-color 0.2s;
      }
      .btn-annuler:hover {
          background-color: rgb(165, 42, 42); 
          transform: scale(1.05);
      }
  </style>
</head>
<body> 
<header>
    <div class="logo"><strong>Bloutub</strong></div><br>
    <ul class="Barre">
        <li>
            <form action="recherche.php" method="GET" style="display: flex; margin: 0; padding: 0;">
                <input type="text" name="q" placeholder="Rechercher..." class="search-input" value="<?= htmlspecialchars($q) ?>" required>
                <button type="submit" class="Brecherche">Rechercher</button>
            </form>
        </li> 
        <li class="push-right">
            <div class="dropdown">
                <button class="dropbtn">Mon Compte ▼</button>
                <div class="dropdown-content">
                    <a href="mes_creations.php">Mes créations</a>
                    <a href="mes_favoris.php">Favoris (<?= $favoriteCount ?>)</a>
                    <a href="creation.php">Créer</a>
                    <a href="menu.php">Menu Principal</a>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <hr>
                        <a href="admin_dashboard.php" style="color: #ffc107; font-weight: bold;">🛡️ Panneau Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="deconnexion.php" class="deco">Déconnexion</a>
                </div>
            </div>
        </li>
    </ul>
</header>

<h2 style="text-align:center; margin-top:30px; margin-bottom: 10px; color: peru;">
    Résultats de recherche pour "<?= htmlspecialchars($q) ?>"
</h2>

<div style="text-align: center; margin-bottom: 30px;">
    <a href="menu.php" class="btn-annuler">✖ Annuler la recherche</a>
</div>

<ul class="BLOC" id="nav-links">
    <?php if (empty($q)): ?>
        <p style="text-align: center; width: 100%; color: #999;">Veuillez entrer un mot-clé dans la barre de recherche en haut.</p>
    <?php elseif (empty($capsules)): ?>
        <p style="text-align: center; width: 100%; color: #999;">Oups ! Aucune capsule ne correspond à "<?= htmlspecialchars($q) ?>".</p>
    <?php else: ?>
        <?php foreach ($capsules as $capsule): ?>
            <li>
                <a href="#">
                    <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" alt="Capsule" class="menu-icon" onerror="this.src='../assets/placeholder.png';">
                    
                    <span>
                        <?= htmlspecialchars($capsule['description']) ?> <br>
                        <small><i>par <?= htmlspecialchars($capsule['nom_utilisateur']) ?></i></small>
                    </span>
                </a>

                <div class="like" style="justify-content: flex-end; width: 100%; padding: 0 20px 20px 20px; box-sizing: border-box;">
                    <div style="display: flex; gap: 15px;">
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
                </div>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<script src="../js/menu.js"></script>
</body>
</html>