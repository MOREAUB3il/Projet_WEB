<?php
session_start();
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json');

try {
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Utilisateur non connecté');
    }

    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['role'] ?? 'user';
    $commentId = intval($_POST['comment_id'] ?? 0);

    if ($commentId <= 0) {
        throw new Exception('ID commentaire invalide');
    }

    // Récupérer les infos du commentaire
    $stmt = $pdo->prepare('SELECT utilisateur_id FROM commentaires WHERE id = :id');
    $stmt->execute([':id' => $commentId]);
    $comment = $stmt->fetch();

    if (!$comment) {
        throw new Exception('Commentaire introuvable');
    }

    // Vérifier que c'est l'auteur du commentaire OU un admin
    if ($comment['utilisateur_id'] != $userId && $userRole !== 'admin') {
        throw new Exception('Vous n\'êtes pas autorisé à supprimer ce commentaire');
    }

    // Supprimer le commentaire
    $deleteStmt = $pdo->prepare('DELETE FROM commentaires WHERE id = :id');
    $deleteStmt->execute([':id' => $commentId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Commentaire supprimé'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
