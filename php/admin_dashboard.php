<?php
require_once __DIR__ . '/admin_check.php';

// Récupère les statistiques
$stats = [];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs');
$stats['total_users'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs WHERE role = "admin"');
$stats['total_admins'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM capsules');
$stats['total_capsules'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM favoris');
$stats['total_favorites'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs WHERE DATE(cree_le) = CURDATE()');
$stats['users_today'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM capsules WHERE DATE(cree_le) = CURDATE()');
$stats['capsules_today'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT id, nom_utilisateur, email, role, cree_le FROM utilisateurs ORDER BY cree_le DESC LIMIT 4');
$latest_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Bloutub</title>
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
                        <a href="mes_favoris.php">Favoris</a>
                        <a href="creation.php">Créer</a>
                        <a href="menu.php">Menu Principal</a>
                        <hr>
                        <a href="admin_dashboard.php" style="color: #ffc107; font-weight: bold;">🛡️ Panneau Admin</a>
                        <hr>
                        <a href="deconnexion.php" class="deco">Déconnexion</a>
                    </div>
                </div>
            </li>
        </ul>
    </header>

    <h2 style="text-align: center; margin-top: 30px; color: peru;">🛡️ Tableau de bord Admin</h2>

    <div style="display: flex; justify-content: center; gap: 15px; margin: 25px 0; flex-wrap: wrap;">
        <a href="admin_dashboard.php"><button class="Brecherche" style="background-color: rosybrown;">Accueil Admin</button></a>
        <a href="admin_users.php"><button class="Brecherche">Gérer les Utilisateurs</button></a>
        <a href="admin_capsules.php"><button class="Brecherche">Gérer les Capsules</button></a>
    </div>

    <h3 style="text-align: center; color: peru; margin-bottom: 20px;">Statistiques du site</h3>

    <ul class="BLOC">
        <li style="min-height: 120px;">
            <a href="admin_users.php" style="justify-content: center;">
                <h3 style="color: #333; margin: 0;">Utilisateurs</h3>
                <span style="font-size: 48px; color: peru; font-weight: bold;"><?php echo $stats['total_users']; ?></span>
            </a>
        </li>
        <li style="min-height: 120px;">
            <a href="admin_users.php" style="justify-content: center;">
                <h3 style="color: #333; margin: 0;">Admins</h3>
                <span style="font-size: 48px; color: rgb(205, 121, 37); font-weight: bold;"><?php echo $stats['total_admins']; ?></span>
            </a>
        </li>
        <li style="min-height: 120px;">
            <a href="admin_capsules.php" style="justify-content: center;">
                <h3 style="color: #333; margin: 0;">Capsules</h3>
                <span style="font-size: 48px; color: rosybrown; font-weight: bold;"><?php echo $stats['total_capsules']; ?></span>
            </a>
        </li>
        <li style="min-height: 120px;">
            <a href="#" style="justify-content: center; cursor: default;">
                <h3 style="color: #333; margin: 0;">Favoris</h3>
                <span style="font-size: 48px; color: peru; font-weight: bold;"><?php echo $stats['total_favorites']; ?></span>
            </a>
        </li>
    </ul>

    <h3 style="text-align: center; color: peru; margin-top: 40px; margin-bottom: 20px;">Derniers inscrits</h3>

    <ul class="BLOC">
        <?php foreach ($latest_users as $user): ?>
            <li style="min-height: 150px;">
                <a href="admin_users.php" style="justify-content: center;">
                    <span style="font-size: 22px; font-weight: bold; color: peru;"><?php echo htmlspecialchars($user['nom_utilisateur']); ?></span>
                    <span style="color: #666; margin-top: 5px;"><?php echo htmlspecialchars($user['email']); ?></span>
                    
                    <div style="margin-top: 15px;">
                        <span style="background-color: <?php echo $user['role'] === 'admin' ? 'rgb(205, 121, 37)' : 'rosybrown'; ?>; color: white; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                    
                    <small style="margin-top: 15px; color: #999;">Inscrit le : <?php echo date('d/m/Y', strtotime($user['cree_le'])); ?></small>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <br><br>
</body>
</html>