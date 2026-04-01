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
        .capsules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .capsule-card {
            background: white;
            border: 2px solid peru;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .capsule-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .capsule-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, bisque 0%, #fff 100%);
        }
        .capsule-info {
            padding: 15px;
            background: linear-gradient(135deg, bisque 0%, #fff 100%);
        }
        .capsule-info h3 {
            margin: 0 0 10px 0;
            color: peru;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: bold;
        }
        .capsule-info p {
            margin: 5px 0;
            color: #666;
            font-size: 13px;
        }
        .capsule-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }
        .favorite-badge {
            background-color: rosybrown;
            color: bisque;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
        }
        .capsule-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        .btn-small {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-view {
            background-color: peru;
            color: bisque;
        }
        .btn-view:hover {
            background-color: rgb(205, 121, 37);
        }
        .btn-delete {
            background-color: rosybrown;
            color: bisque;
        }
        .btn-delete:hover {
            background-color: rgb(165, 42, 42);
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
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
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
            <h1>🎬 Gestion des capsules</h1>
        </div>

        <div class="admin-nav">
            <a href="admin_dashboard.php">Accueil</a>
            <a href="admin_users.php">Utilisateurs</a>
            <a href="admin_capsules.php" class="active">Capsules</a>
            <a href="../php/menu.php">Retour site</a>
        </div>

        <div class="filter-section">
            <input type="text" id="search" placeholder="Rechercher par description..." onkeyup="filterCapsules()">
            <select id="sortBy" onchange="sortCapsules()">
                <option value="recent">Plus récentes</option>
                <option value="oldest">Plus anciennes</option>
                <option value="title">Titre A-Z</option>
            </select>
        </div>

        <?php if (empty($capsules)): ?>
            <div class="empty-state">
                <p>Aucune capsule trouvée</p>
            </div>
        <?php else: ?>
            <div class="capsules-grid" id="capsulesGrid">
                <?php foreach ($capsules as $capsule): ?>
                <div class="capsule-card" data-description="<?php echo strtolower($capsule['description'] ?? ''); ?>" data-date="<?php echo $capsule['cree_le']; ?>">
                    <img src="<?php echo htmlspecialchars($capsule['chemin_image']); ?>" alt="Capsule" class="capsule-image" onerror="this.src='../assets/placeholder.png'">
                    <div class="capsule-info">
                        <h3><?php echo htmlspecialchars(substr($capsule['description'] ?? 'Sans titre', 0, 50)); ?></h3>
                        <p><strong>Auteur:</strong> <?php echo htmlspecialchars($capsule['nom_utilisateur']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($capsule['email']); ?></p>
                        <div class="capsule-meta">
                            <span><?php echo date('d/m/Y H:i', strtotime($capsule['cree_le'])); ?></span>
                            <?php if (isset($favorites_count[$capsule['id']])): ?>
                                <span class="favorite-badge">❤️ <?php echo $favorites_count[$capsule['id']]; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="capsule-actions">
                            <a href="<?php echo htmlspecialchars($capsule['chemin_image']); ?>" target="_blank" class="btn-small btn-view">Voir</a>
                            <button class="btn-small btn-delete" onclick="deleteCapsule(<?php echo $capsule['id']; ?>, '<?php echo htmlspecialchars(substr($capsule['nom_utilisateur'], 0, 20)); ?>')">Supprimer</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filterCapsules() {
            const search = document.getElementById('search').value.toLowerCase();
            const cards = document.querySelectorAll('.capsule-card');

            cards.forEach(card => {
                const description = card.dataset.description;
                card.style.display = description.includes(search) ? '' : 'none';
            });
        }

        function sortCapsules() {
            const sortBy = document.getElementById('sortBy').value;
            const grid = document.getElementById('capsulesGrid');
            const cards = Array.from(grid.querySelectorAll('.capsule-card'));

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

        function logout() {
            fetch('../php/logout.php').then(() => {
                window.location.href = '../html/connexion.html';
            });
        }
    </script>
</body>
</html>
