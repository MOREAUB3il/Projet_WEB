<?php
session_start();
require_once __DIR__ . '/bdd.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html');
    exit;
}

$user_id = $_SESSION['user']['id'];
$capsule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($capsule_id === 0) {
    header('Location: menu.php');
    exit;
}

try {
    // Récupérer la capsule et vérifier si l'utilisateur l'a en favoris
    $stmt = $pdo->prepare('
        SELECT c.*, u.nom_utilisateur, f.id AS is_liked
        FROM capsules c
        JOIN utilisateurs u ON c.utilisateur_id = u.id
        LEFT JOIN favoris f ON (f.capsule_id = c.id AND f.utilisateur_id = ?)
        WHERE c.id = ?
    ');
    $stmt->execute([$user_id, $capsule_id]);
    $capsule = $stmt->fetch();

    if (!$capsule) {
        header('Location: menu.php');
        exit;
    }

    // Nombre de favoris pour le menu
    $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM favoris WHERE utilisateur_id = ?');
    $countStmt->execute([$user_id]);
    $favoriteCount = (int)$countStmt->fetchColumn();

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$isFavori = !empty($capsule['is_liked']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($capsule['description']) ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .capsule-container {
            display: flex;
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .capsule-image-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .capsule-image-section img {
            max-width: 100%;
            max-height: 600px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .capsule-details-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .capsule-details-section h2 {
            color: peru;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .capsule-details-section p {
            margin: 10px 0;
            line-height: 1.6;
        }

        .capsule-author {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .capsule-author small {
            color: #666;
        }

        .capsule-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-action {
            flex: 1;
            padding: 10px 15px;
            background-color: peru;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-action:hover {
            background-color: rgb(205, 121, 37);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: peru;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .edit-description-btn {
            background-color: bisque;
            color: peru;
            padding: 10px 15px;
            border: 1px solid peru;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .edit-description-btn:hover {
            background-color: peru;
            color: white;
        }

        .modal-edit {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-edit-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 5px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .modal-edit-header h2 {
            color: peru;
            margin: 0;
        }

        .close-edit {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .close-edit:hover {
            color: #000;
        }

        .modal-edit textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 150px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .modal-edit-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-edit-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-save {
            background-color: peru;
            color: white;
        }

        .btn-save:hover {
            background-color: rgb(205, 121, 37);
        }

        .btn-cancel {
            background-color: #ddd;
            color: #333;
        }

        .btn-cancel:hover {
            background-color: #ccc;
        }

        .like-section {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .heart-container, .bookmark-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s;
        }

        .heart-icon, .bookmark-icon {
            width: 30px;
            height: 30px;
            stroke: peru;
            fill: none;
            stroke-width: 2;
            transition: all 0.3s;
        }

        .heart-container.is-active .heart-icon,
        .bookmark-btn.is-active .bookmark-icon {
            fill: peru;
            stroke: peru;
        }

        @media (max-width: 768px) {
            .capsule-container {
                flex-direction: column;
                gap: 20px;
            }

            .capsule-image-section img {
                max-height: 400px;
            }

            .capsule-details-section h2 {
                font-size: 20px;
            }
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
                        <a href="mes_favoris.php">Favoris (<?= $favoriteCount ?>)</a>
                        <a href="creation.php">Créer</a>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <hr>
                            <a href="admin_dashboard.php">Panneau Admin</a>
                        <?php endif; ?>
                        <hr>
                        <a href="../html/page_accueil.html" class="deco">Déconnexion</a>
                    </div>
                </div>
            </li>
        </ul>
    </header>

    <div class="capsule-container">
        <!-- Image agrandie à gauche -->
        <div class="capsule-image-section">
            <img src="<?= htmlspecialchars($capsule['chemin_image']) ?>" alt="Capsule" onerror="this.src='../assets/image.png';">
        </div>

        <!-- Détails à droite -->
        <div class="capsule-details-section">
            <a href="menu.php" class="back-link">← Retour au menu</a>

            <h2><?= htmlspecialchars($capsule['description']) ?></h2>

            <?php if (!empty($capsule['contenu'])): ?>
                <p><?= htmlspecialchars($capsule['contenu']) ?></p>
            <?php endif; ?>

            <div class="capsule-author">
                <p><strong>Auteur :</strong> <?= htmlspecialchars($capsule['nom_utilisateur']) ?></p>
                <small>Publié le : <?= date('d/m/Y à H:i', strtotime($capsule['cree_le'])) ?></small>
                <?php if ($user_id === $capsule['utilisateur_id']): ?>
                    <button class="edit-description-btn" onclick="openEditModal()">✏️ Remplacer la description</button>
                <?php endif; ?>
            </div>

            <!-- Actions (favoris) -->
            <div class="like-section">
                <button class="heart-container <?= $isFavori ? 'is-active' : '' ?>" onclick="toggleHeart(this, <?= $capsule['id'] ?>)" title="Aimer">
                    <svg class="heart-icon" viewBox="0 0 32 32">
                        <path d="M16 28.5L14.1 26.8C7.33 20.65 3 16.73 3 12.05C3 8.27 5.97 5.3 9.75 5.3C11.89 5.3 13.94 6.3 15.28 7.87L16 8.71L16.72 7.87C18.06 6.3 20.11 5.3 22.25 5.3C26.03 5.3 29 8.27 29 12.05C29 16.73 24.67 20.65 17.9 26.8L16 28.5Z" />
                    </svg>
                </button>
                <button class="bookmark-btn <?= $isFavori ? 'is-active' : '' ?>" onclick="toggleBookmark(this, <?= $capsule['id'] ?>)" title="Ajouter aux favoris">
                    <svg class="bookmark-icon" viewBox="0 0 32 32">
                        <path d="M6 5C6 3.89543 6.89543 3 8 3H24C25.1046 3 26 3.89543 26 5V28L16 21L6 28V5Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal pour éditer la description -->
    <div id="editModal" class="modal-edit">
        <div class="modal-edit-content">
            <div class="modal-edit-header">
                <h2>Remplacer la description</h2>
                <button class="close-edit" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editForm">
                <textarea id="newDescription" placeholder="Nouvelle description..." required><?= htmlspecialchars($capsule['description']) ?></textarea>
                <div class="modal-edit-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/menu.js"></script>
    <script>
        function openEditModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const newDescription = document.getElementById('newDescription').value.trim();

            if (!newDescription) {
                alert('La description ne peut pas être vide.');
                return;
            }

            try {
                const response = await fetch('../php/edit_capsule.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        capsule_id: <?= $capsule['id'] ?>,
                        description: newDescription
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Description mise à jour avec succès!');
                    location.reload();
                } else {
                    alert('Erreur : ' + (data.error || 'Impossible de mettre à jour la description'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour.');
            }
        });
    </script>

    <!-- Section Commentaires -->
    <div class="comments-section" style="max-width: 1200px; margin: 30px auto; padding: 20px;">
        <h3 style="color: peru; margin-bottom: 20px;">Commentaires</h3>

        <!-- Formulaire pour ajouter un commentaire (uniquement pour les autres utilisateurs) -->
        <?php if ($user_id !== $capsule['utilisateur_id']): ?>
            <div style="margin-bottom: 30px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; border: 1px solid #ddd;">
                <textarea id="commentInput" placeholder="Ajouter un commentaire..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial; resize: vertical; min-height: 80px; box-sizing: border-box;"></textarea>
                <button onclick="submitComment(<?= $capsule['id'] ?>)" style="margin-top: 10px; padding: 10px 20px; background-color: peru; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Publier</button>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 30px; padding: 15px; background-color: #fff3cd; border-radius: 5px; border: 1px solid #ffc107; color: #856404;">
                <p style="margin: 0;"><strong>ℹ️ Remarque :</strong> Vous ne pouvez pas commenter votre propre image.</p>
            </div>
        <?php endif; ?>

        <!-- Liste des commentaires -->
        <div id="commentsList" style="border-top: 2px solid #ddd; padding-top: 20px;">
            <p style="text-align: center; color: #999;">Chargement des commentaires...</p>
        </div>
    </div>

    <script>
        // Charger les commentaires au chargement de la page
        const currentUserId = <?= $_SESSION['user']['id'] ?? 0 ?>;
        const currentUserRole = '<?= $_SESSION['user']['role'] ?? 'user' ?>';

        document.addEventListener('DOMContentLoaded', function() {
            loadComments(<?= $capsule['id'] ?>);
        });

        function loadComments(capsuleId) {
            fetch(`../php/get_comments.php?capsule_id=${capsuleId}&page=1`)
                .then(response => response.json())
                .then(data => {
                    const commentsList = document.getElementById('commentsList');
                    
                    if (data.comments.length === 0) {
                        commentsList.innerHTML = '<p style="text-align: center; color: #999;">Aucun commentaire pour le moment.</p>';
                        return;
                    }

                    commentsList.innerHTML = '';
                    data.comments.forEach(comment => {
                        // Vérifier si l'utilisateur actuel peut supprimer ce commentaire
                        const canDelete = comment.user_id === currentUserId || currentUserRole === 'admin';
                        const deleteBtn = canDelete ? `
                            <button onclick="deleteComment(${comment.id})" style="background-color: #ff6b6b; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 10px; font-size: 12px;">
                                🗑️ Supprimer
                            </button>
                        ` : '';

                        const commentHTML = `
                            <div style="padding: 15px; background-color: #f9f9f9; border-radius: 5px; margin-bottom: 15px; border-left: 3px solid peru;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <strong style="color: peru;">${comment.nom_utilisateur}</strong>
                                        <small style="color: #999; display: block; margin-bottom: 5px;">${comment.cree_le}</small>
                                        <p style="margin: 0; line-height: 1.5;">${comment.comment_text}</p>
                                    </div>
                                </div>
                                ${deleteBtn}
                            </div>
                        `;
                        commentsList.innerHTML += commentHTML;
                    });
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('commentsList').innerHTML = '<p style="color: red;">Erreur lors du chargement des commentaires.</p>';
                });
        }

        function deleteComment(commentId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                return;
            }

            const formData = new FormData();
            formData.append('comment_id', commentId);

            fetch('../php/delete_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Recharger les commentaires
                    loadComments(<?= $capsule['id'] ?>);
                    alert('Commentaire supprimé avec succès');
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            });
        }

        function submitComment(capsuleId) {
            const commentInput = document.getElementById('commentInput');
            const comment_text = commentInput.value.trim();

            if (!comment_text) {
                alert('Le commentaire ne peut pas être vide.');
                return;
            }

            if (comment_text.length > 500) {
                alert('Le commentaire ne doit pas dépasser 500 caractères.');
                return;
            }

            const formData = new FormData();
            formData.append('capsule_id', capsuleId);
            formData.append('comment_text', comment_text);

            fetch('../php/comment_process.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Commentaire publié !');
                        commentInput.value = '';
                        loadComments(capsuleId); // Recharger les commentaires
                    } else {
                        alert('Erreur: ' + (data.message || 'Impossible de publier le commentaire'));
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la publication du commentaire.');
                });
        }
    </script>

    <script src="../js/pagination.js"></script>
</body>
</html>
