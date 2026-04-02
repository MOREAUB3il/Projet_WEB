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
            flex: 1 0 300px;
            max-width: 400px;
            min-height: 200px; /* Un peu moins haut que les capsules car pas d'image */
            background-color: bisque;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            padding: 20px;
            box-sizing: border-box;
            transition: none !important;
            transform: none !important;
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
                        <a href="mes_favoris.php">Favoris</a>
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
        <a href="admin_dashboard.php"><button class="Brecherche">Accueil Admin</button></a>
        <a href="admin_users.php"><button class="Brecherche" style="background-color: rosybrown;">Gérer les Utilisateurs</button></a>
        <a href="admin_capsules.php"><button class="Brecherche">Gérer les Capsules</button></a>
    </div>

    <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <input type="text" id="search" placeholder="Rechercher par nom ou email..." onkeyup="filterTable()" style="padding: 10px; border-radius: 5px; border: 2px solid peru; outline: none; width: 250px;">
        <select id="roleFilter" onchange="filterTable()" style="padding: 10px; border-radius: 5px; border: 2px solid peru; outline: none;">
            <option value="">Tous les rôles</option>
            <option value="admin">Admin</option>
            <option value="utilisateur">Utilisateur</option>
        </select>
    </div>

    <ul class="admin-bloc" id="usersGrid">
        <?php foreach ($users as $user): ?>
        <li class="user-item" data-username="<?php echo strtolower(htmlspecialchars($user['nom_utilisateur'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>">
            
            <div style="text-align: center; flex-grow: 1;">
                <h3 style="color: peru; margin: 0 0 10px 0; font-size: 24px;"><?php echo htmlspecialchars($user['nom_utilisateur']); ?></h3>
                <p style="color: #666; margin: 5px 0; font-size: 14px;"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div style="margin: 15px 0;">
                    <span style="background-color: <?php echo $user['role'] === 'admin' ? 'rgb(205, 121, 37)' : 'rosybrown'; ?>; color: white; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
                
                <p style="font-size: 12px; color: #999; margin-top: 15px;">Inscrit le : <?php echo date('d/m/Y H:i', strtotime($user['cree_le'])); ?></p>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
                <?php if ($user['role'] === 'utilisateur'): ?>
                    <button onclick="promoteUser(<?php echo $user['id']; ?>)" style="background-color: peru; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.2s;">Promouvoir</button>
                <?php else: ?>
                    <button onclick="demoteUser(<?php echo $user['id']; ?>)" style="background-color: rosybrown; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.2s;">Rétrograder</button>
                <?php endif; ?>
                
                <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                    <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes(htmlspecialchars($user['nom_utilisateur'])); ?>')" style="background-color: rgb(165, 42, 42); color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.2s;">Supprimer</button>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>

    <script>
        // Le script de filtre a été adapté pour fonctionner avec les cartes au lieu du tableau
        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const items = document.querySelectorAll('.user-item');

            items.forEach(item => {
                const username = item.dataset.username;
                const email = item.dataset.email;
                const role = item.dataset.role;

                const matchesSearch = username.includes(search) || email.includes(search);
                const matchesRole = roleFilter === '' || role === roleFilter;

                item.style.display = matchesSearch && matchesRole ? '' : 'none';
            });
        }

        // Les appels AJAX à ton fichier admin_process.php restent identiques !
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
    </script>
</body>
</html>