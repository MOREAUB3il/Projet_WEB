<?php
// On démarre la session juste au cas où on voudrait afficher un message, mais pas d'obligation d'être connecté
session_start();
require_once __DIR__ . '/bdd.php';

try {
    // On récupère toutes les capsules et le nom de leurs auteurs
    $stmt = $pdo->query('
        SELECT c.*, u.nom_utilisateur 
        FROM capsules c 
        JOIN utilisateurs u ON c.utilisateur_id = u.id 
        ORDER BY c.cree_le DESC
    ');
    $capsules = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors du chargement de l'accueil : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil - Bloutub</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <div class="logo"><strong>Bloutub</strong></div><br>
        <ul class="Barre">
            <li>
                <form action="recherche.php" method="GET" style="display: flex; margin: 0; padding: 0;">
                    <input type="text" name="q" placeholder="Rechercher..." class="search-input" required>
                    <button type="submit" class="Brecherche">Rechercher</button>
                </form>
            </li> 
            <li class="push-right"><a href="../html/connexion.html"><button class="btn">LOGIN</button></a></li>
        </ul>
    </header>

    <ul class="BLOC" id="nav-links">
        <?php if (empty($capsules)): ?>
            <p style="text-align: center; width: 100%;">Aucune capsule pour le moment. Créez un compte pour être le premier à publier !</p>
        <?php else: ?>
            <?php foreach ($capsules as $capsule): ?>
                <li>
                    <a href="../html/connexion.html" title="Connectez-vous pour interagir !">
                        <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" alt="Capsule" class="menu-icon" onerror="this.src='../assets/image.png';">
                        
                        <span>
                            <?= htmlspecialchars($capsule['description']) ?> <br>
                            <small><i>par <?= htmlspecialchars($capsule['nom_utilisateur']) ?></i></small>
                        </span>
                        
                        <div style="margin-top: 15px; color: peru; font-size: 0.85em; font-weight: bold;">
                            🔒 Connectez-vous pour liker
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</body>
</html>