<?php
require_once __DIR__ . '/admin_check.php';

// Récupère les statistiques
$stats = [];

// Nombre total d'utilisateurs
$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs');
$stats['total_users'] = $stmt->fetch()['total'];

// Nombre d'admins
$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs WHERE role = "admin"');
$stats['total_admins'] = $stmt->fetch()['total'];

// Nombre total de capsules
$stmt = $pdo->query('SELECT COUNT(*) as total FROM capsules');
$stats['total_capsules'] = $stmt->fetch()['total'];

// Nombre total de favoris
$stmt = $pdo->query('SELECT COUNT(*) as total FROM favoris');
$stats['total_favorites'] = $stmt->fetch()['total'];

// Utilisateur créé aujourd'hui
$stmt = $pdo->query('SELECT COUNT(*) as total FROM utilisateurs WHERE DATE(cree_le) = CURDATE()');
$stats['users_today'] = $stmt->fetch()['total'];

// Capsules créées aujourd'hui
$stmt = $pdo->query('SELECT COUNT(*) as total FROM capsules WHERE DATE(cree_le) = CURDATE()');
$stats['capsules_today'] = $stmt->fetch()['total'];

// Derniers utilisateurs
$stmt = $pdo->query('SELECT id, nom_utilisateur, email, role, cree_le FROM utilisateurs ORDER BY cree_le DESC LIMIT 5');
$latest_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - BlOutub</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #f4f4f4;
            padding: 15px 20px;
            border-bottom: 2px solid rosybrown;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: peru;
            margin-bottom: 10px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 3px solid peru;
            padding-bottom: 15px;
        }

        .admin-header h1 {
            color: peru;
            font-size: 32px;
        }

        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-info span {
            color: #333;
            font-weight: bold;
        }

        .admin-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .admin-nav a, .admin-nav button {
            padding: 12px 20px;
            background-color: peru;
            color: bisque;
            text-decoration: none;
            border: 2px solid peru;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover, .admin-nav button:hover {
            background-color: rgb(205, 121, 37);
            border-color: rgb(205, 121, 37);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .admin-nav a.active {
            background-color: rosybrown;
            color: white;
            border-color: rosybrown;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, bisque 0%, #fff 100%);
            border: 2px solid peru;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            margin: 0 0 15px 0;
            color: peru;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-card .number {
            font-size: 40px;
            font-weight: bold;
            color: peru;
        }
        .stat-card.warning .number {
            color: rgb(205, 121, 37);
        }
        .stat-card.success .number {
            color: rosybrown;
        }
        .table-section {
            background: linear-gradient(135deg, bisque 0%, #fff 100%);
            border: 2px solid peru;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .table-section h2 {
            margin-top: 0;
            color: peru;
            font-size: 22px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background-color: peru;
            color: bisque;
            padding: 14px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e0d0c0;
            color: #333;
        }
        tr:hover {
            background-color: #faf0e6;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-admin {
            background-color: rgb(205, 121, 37);
            color: white;
        }
        .badge-user {
            background-color: rosybrown;
            color: bisque;
        }
        .logout-btn {
            background-color: rgb(205, 121, 37);
            color: bisque;
            padding: 12px 20px;
            border: 2px solid rgb(205, 121, 37);
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: rosybrown;
            border-color: rosybrown;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎬 BlOutub Admin</div>
        <ul class="Barre">
            <li><span style="color: peru; font-weight: bold;">👤 <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span></li>
            <li class="push-right">
                <button class="logout-btn" onclick="logout()">Déconnexion</button>
            </li>
        </ul>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1>🛡️ Tableau de bord Admin</h1>
        </div>

        <div class="admin-nav">
            <a href="admin_dashboard.php" class="active">Accueil</a>
            <a href="admin_users.php">Utilisateurs</a>
            <a href="admin_capsules.php">Capsules</a>
            <a href="../php/menu.php">Retour site</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Utilisateurs totaux</h3>
                <div class="number"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Admins</h3>
                <div class="number warning"><?php echo $stats['total_admins']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Capsules totales</h3>
                <div class="number success"><?php echo $stats['total_capsules']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Favoris total</h3>
                <div class="number"><?php echo $stats['total_favorites']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Utilisateurs aujourd'hui</h3>
                <div class="number"><?php echo $stats['users_today']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Capsules aujourd'hui</h3>
                <div class="number success"><?php echo $stats['capsules_today']; ?></div>
            </div>
        </div>

        <div class="table-section">
            <h2>Derniers utilisateurs inscrits</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date inscription</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['nom_utilisateur']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['cree_le'])); ?></td>
                        <td>
                            <a href="admin_users.php" style="color: #007bff; text-decoration: none;">Gérer →</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function logout() {
            fetch('../php/logout.php').then(() => {
                window.location.href = '../html/connexion.html';
            });
        }
    </script>
</body>
</html>
