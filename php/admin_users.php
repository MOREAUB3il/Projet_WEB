<?php
require_once __DIR__ . '/admin_check.php';

// Récupère tous les utilisateurs
$stmt = $pdo->query('SELECT id, nom_utilisateur, email, role, cree_le FROM utilisateurs ORDER BY cree_le DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - Admin</title>
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
        .admin-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .admin-nav a {
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
        .admin-nav a:hover {
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
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-edit {
            background-color: peru;
            color: bisque;
        }
        .btn-edit:hover {
            background-color: rgb(205, 121, 37);
        }
        .btn-delete {
            background-color: rosybrown;
            color: bisque;
        }
        .btn-delete:hover {
            background-color: rgb(165, 42, 42);
        }
        .btn-promote {
            background-color: rgb(205, 121, 37);
            color: bisque;
        }
        .btn-promote:hover {
            background-color: peru;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, bisque 0%, #fff 100%);
            border: 2px solid peru;
            border-radius: 8px;
        }
        .filter-section input, .filter-section select {
            padding: 8px 12px;
            border: 2px solid peru;
            border-radius: 4px;
            margin-right: 10px;
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
            <h1>👥 Gestion des utilisateurs</h1>
        </div>

        <div class="admin-nav">
            <a href="admin_dashboard.php">Accueil</a>
            <a href="admin_users.php" class="active">Utilisateurs</a>
            <a href="admin_capsules.php">Capsules</a>
            <a href="../php/menu.php">Retour site</a>
        </div>

        <div class="filter-section">
            <input type="text" id="search" placeholder="Rechercher par nom ou email..." onkeyup="filterTable()">
            <select id="roleFilter" onchange="filterTable()">
                <option value="">Tous les rôles</option>
                <option value="admin">Admin</option>
                <option value="utilisateur">Utilisateur</option>
            </select>
        </div>

        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="user-row" data-username="<?php echo strtolower($user['nom_utilisateur']); ?>" data-email="<?php echo strtolower($user['email']); ?>" data-role="<?php echo $user['role']; ?>">
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
                        <div class="action-buttons">
                            <?php if ($user['role'] === 'utilisateur'): ?>
                                <button class="btn-small btn-promote" onclick="promoteUser(<?php echo $user['id']; ?>)">Promouvoir</button>
                            <?php else: ?>
                                <button class="btn-small btn-edit" onclick="demoteUser(<?php echo $user['id']; ?>)">Rétrograder</button>
                            <?php endif; ?>
                            <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                <button class="btn-small btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nom_utilisateur']); ?>')">Supprimer</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const username = row.dataset.username;
                const email = row.dataset.email;
                const role = row.dataset.role;

                const matchesSearch = username.includes(search) || email.includes(search);
                const matchesRole = roleFilter === '' || role === roleFilter;

                row.style.display = matchesSearch && matchesRole ? '' : 'none';
            });
        }

        function promoteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir promouvoir cet utilisateur en admin ?')) {
                updateUserRole(userId, 'admin');
            }
        }

        function demoteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir rétrograder cet admin en utilisateur ?')) {
                updateUserRole(userId, 'utilisateur');
            }
        }

        function updateUserRole(userId, newRole) {
            fetch('../php/admin_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_role', user_id: userId, role: newRole })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Rôle modifié avec succès');
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(err => alert('Erreur réseau'));
        }

        function deleteUser(userId, username) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ' + username + ' ? Cette action est irréversible.')) {
                fetch('../php/admin_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_user', user_id: userId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Utilisateur supprimé');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(err => alert('Erreur réseau'));
            }
        }

        function logout() {
            fetch('../php/logout.php').then(() => {
                window.location.href = '../html/connexion.html';
            });
        }
    </script>
</body>
</html>
