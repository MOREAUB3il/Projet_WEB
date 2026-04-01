<?php
require_once __DIR__ . '/admin_check.php';

// Récupère toutes les capsules avec infos utilisateur
$stmt = $pdo->query('
    SELECT c.id, c.utilisateur_id, c.chemin_image, c.description, c.cree_le, u.nom_utilisateur, u.email
    FROM capsules c
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    ORDER BY c.cree_le DESC
');
$capsules = $stmt->fetchAll();

// Compte les favoris par capsule
$stmt = $pdo->query('SELECT capsule_id, COUNT(*) as count FROM favoris GROUP BY capsule_id');
$favorites_count = [];
while ($row = $stmt->fetch()) {
    $favorites_count[$row['capsule_id']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des capsules - Admin</title>
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
            min-height: 300px;
            background-color: bisque;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            /* Zéro animation autorisée ici */
            transition: none !important; 
            transform: none !important;
        }

        .admin-capsule-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
            display: block;
            margin-bottom: 15px;
            /* Zéro animation sur l'image */
            transition: none !important;
            transform: none !important;
        }
    </style>
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
                        <a href="admin_dashboard.php">Panneau Admin</a>
                        <hr>
                        <a href="deconnexion.php" class="deco">Déconnexion</a>
                    </div>
                </div>
            </li>
        </ul>
    </header>

    <h2 style="text-align: center; margin-top: 30px; color: peru;">🎬 Gestion des capsules</h2>

    <div style="display: flex; justify-content: center; gap: 15px; margin: 25px 0; flex-wrap: wrap;">
        <a href="admin_dashboard.php"><button class="Brecherche">Accueil Admin</button></a>
        <a href="admin_users.php"><button class="Brecherche">Gérer les Utilisateurs</button></a>
        <a href="admin_capsules.php"><button class="Brecherche" style="background-color: rosybrown;">Gérer les Capsules</button></a>
    </div>

    <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <input type="text" id="search" placeholder="Rechercher par description..." onkeyup="filterCapsules()" style="padding: 10px; border-radius: 5px; border: 2px solid peru; outline: none;">
        <select id="sortBy" onchange="sortCapsules()" style="padding: 10px; border-radius: 5px; border: 2px solid peru; outline: none;">
            <option value="recent">Plus récentes</option>
            <option value="oldest">Plus anciennes</option>
            <option value="title">Titre A-Z</option>
        </select>
    </div>

    <?php if (empty($capsules)): ?>
        <p style="text-align: center; width: 100%; color: #999;">Aucune capsule trouvée.</p>
    <?php else: ?>
        <ul class="admin-bloc" id="capsulesGrid">
            <?php foreach ($capsules as $capsule): ?>
            <li class="capsule-item" data-description="<?php echo strtolower(htmlspecialchars($capsule['description'] ?? '')); ?>" data-date="<?php echo $capsule['cree_le']; ?>">
                
                <img src="<?php echo htmlspecialchars($capsule['chemin_image']); ?>" alt="Capsule" class="admin-capsule-img" onerror="this.src='../assets/placeholder.png'">
                
                <div style="width: 100%; padding: 0 15px; box-sizing: border-box; text-align: center; flex-grow: 1;">
                    <h4 style="color: peru; margin: 0 0 5px 0; font-size: 16px;"><?php echo htmlspecialchars(substr($capsule['description'] ?? 'Sans titre', 0, 50)); ?></h4>
                    <p style="font-size: 13px; margin: 3px 0; color: #333;"><strong>Auteur:</strong> <?php echo htmlspecialchars($capsule['nom_utilisateur']); ?></p>
                    <p style="font-size: 13px; margin: 3px 0; color: #333;"><strong>Email:</strong> <?php echo htmlspecialchars($capsule['email']); ?></p>
                    
                    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; font-size: 12px; color: #666;">
                        <span><?php echo date('d/m/Y H:i', strtotime($capsule['cree_le'])); ?></span>
                        <?php if (isset($favorites_count[$capsule['id']])): ?>
                            <span style="background-color: rosybrown; color: white; padding: 3px 8px; border-radius: 12px; font-weight: bold;">❤️ <?php echo $favorites_count[$capsule['id']]; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; width: 100%; padding: 15px; box-sizing: border-box; justify-content: center; margin-top: auto;">
                    <a href="<?php echo htmlspecialchars($capsule['chemin_image']); ?>" target="_blank" style="background-color: peru; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 14px; font-weight: bold; text-align: center; flex: 1; transition: 0.2s;">Voir</a>
                    
                    <button onclick="deleteCapsule(<?php echo $capsule['id']; ?>, '<?php echo addslashes(htmlspecialchars(substr($capsule['nom_utilisateur'], 0, 20))); ?>')" style="background-color: rosybrown; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold; flex: 1; transition: 0.2s;">Supprimer</button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <script>
        function filterCapsules() {
            const search = document.getElementById('search').value.toLowerCase();
            const cards = document.querySelectorAll('.capsule-item');

            cards.forEach(card => {
                const description = card.dataset.description;
                card.style.display = description.includes(search) ? '' : 'none';
            });
        }

        function sortCapsules() {
            const sortBy = document.getElementById('sortBy').value;
            const grid = document.getElementById('capsulesGrid');
            const cards = Array.from(grid.querySelectorAll('.capsule-item'));

            cards.sort((a, b) => {
                if (sortBy === 'recent') {
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                } else if (sortBy === 'oldest') {
                    return new Date(a.dataset.date) - new Date(b.dataset.date);
                } else if (sortBy === 'title') {
                    return a.dataset.description.localeCompare(b.dataset.description);
                }
            });

            grid.innerHTML = '';
            cards.forEach(card => grid.appendChild(card));
        }

        function deleteCapsule(capsuleId, username) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette capsule de ' + username + ' ?')) {
                fetch('../php/admin_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_capsule', capsule_id: capsuleId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Capsule supprimée');
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