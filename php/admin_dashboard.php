<?php
require_once __DIR__ . '/admin_check.php';

// 1. CORRECTION DE L'ERREUR DU MENU : On calcule les favoris de l'admin
$userId = $_SESSION['user']['id'];
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM favoris WHERE utilisateur_id = ?');
$countStmt->execute([$userId]);
$favoriteCount = (int) $countStmt->fetchColumn();

// 2. Récupération des statistiques
$stats = [];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs');
$stats['total_users'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs WHERE role = "admin"');
$stats['total_admins'] = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM capsules');
$stats['total_capsules'] = $stmt->fetch()['total'];

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
    
    <style>
        .admin-bloc {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 20px auto;
            max-width: 1200px;
        }
        
        .admin-bloc li {
            flex: 1 0 250px;
            max-width: 300px;
            background-color: bisque !important; /* On force le fond beige */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 20px;
            box-sizing: border-box;
            transition: none !important;
            transform: none !important;
            box-shadow: none !important;
            cursor: default;
        }

        /* On s'assure que rien ne change au survol */
        .admin-bloc li:hover {
            transform: none !important;
            background-color: bisque !important;
            box-shadow: none !important;
        }
        
        .admin-bloc a {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
    </style>
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
            <li class="push-right">
                <div class="dropdown">
                    <button class="dropbtn">Mon Compte ▼</button>
                    <div class="dropdown-content">
                        <a href="mes_creations.php">Mes créations</a>
                        <a href="mes_favoris.php">Favoris (<?= $favoriteCount ?>)</a>
                        <a href="creation.php">Créer</a>
                        <a href="menu.php">Menu Principal</a>
                        <hr>
                        <a href="admin_dashboard.php">Panneau Admin</a>
                        <hr>
                        <a href="deconnexion.php" class="deco">Déconnexion</a>
                    </div>
                </div>
            </li>
        </ul>
    </header>

    <div style="display: flex; justify-content: center; gap: 15px; margin: 25px 0; flex-wrap: wrap;">
        <a href="admin_dashboard.php"><button class="Brecherche" style="background-color: rosybrown;">Accueil Admin</button></a>
        <a href="admin_users.php"><button class="Brecherche">Gérer les Utilisateurs</button></a>
        <a href="admin_capsules.php"><button class="Brecherche">Gérer les Capsules</button></a>
    </div>

    <h3 style="text-align: center; color: peru; margin-bottom: 20px;">Statistiques du site</h3>

    <ul class="admin-bloc">
        <li style="min-height: 120px;">
            <h3 style="color: #333; margin: 0; text-align: center;">Utilisateurs</h3>
            <span style="font-size: 48px; color: peru; font-weight: bold;"><?php echo $stats['total_users']; ?></span>
        </li>
        <li style="min-height: 120px;">
            <h3 style="color: #333; margin: 0; text-align: center;">Admins</h3>
            <span style="font-size: 48px; color: rgb(205, 121, 37); font-weight: bold;"><?php echo $stats['total_admins']; ?></span>
        </li>
        <li style="min-height: 120px;">
            <h3 style="color: #333; margin: 0; text-align: center;">Capsules</h3>
            <span style="font-size: 48px; color: rosybrown; font-weight: bold;"><?php echo $stats['total_capsules']; ?></span>
        </li>
        <li style="min-height: 120px;">
            <h3 style="color: #333; margin: 0; text-align: center;">Inscrits du jour</h3>
            <span style="font-size: 48px; color: peru; font-weight: bold;"><?php echo $stats['users_today']; ?></span>
        </li>
        <li style="min-height: 120px;">
            <h3 style="color: #333; margin: 0; text-align: center;">Capsules du jour</h3>
            <span style="font-size: 48px; color: rosybrown; font-weight: bold;"><?php echo $stats['capsules_today']; ?></span>
        </li>
    </ul>

    <h3 style="text-align: center; color: peru; margin-top: 40px; margin-bottom: 20px;">Derniers inscrits</h3>

    <ul class="admin-bloc">
        <?php foreach ($latest_users as $user): ?>
            <li style="min-height: 150px;">
                <a href="admin_users.php">
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