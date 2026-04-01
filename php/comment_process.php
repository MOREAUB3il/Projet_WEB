<?php
session_start();
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json');

try {
    // Vérifier si utilisateur connecté
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Non authentifié']);
        exit;
    }

    // Vérifier la méthode REQUEST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
        exit;
    }

    // Récupérer les données JSON ou POST
    $data = $_POST;
    if (empty($data)) {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $data = $jsonData ?? [];
    }

    $capsuleId = intval($data['capsule_id'] ?? 0);
    $comment_text = trim($data['comment_text'] ?? '');
    $userId = $_SESSION['user']['id'];

    // Validations
    if ($capsuleId <= 0) {
        throw new Exception('ID Capsule invalide');
    }

    if (strlen($comment_text) === 0) {
        throw new Exception('Le commentaire ne peut pas être vide');
    }

    if (strlen($comment_text) > 500) {
        throw new Exception('Le commentaire ne doit pas dépasser 500 caractères');
    }

    // Vérifier que la capsule existe
    $capsuleStmt = $pdo->prepare('SELECT id, utilisateur_id FROM capsules WHERE id = :id');
    $capsuleStmt->execute([':id' => $capsuleId]);
    $capsule = $capsuleStmt->fetch();
    if (!$capsule) {
        throw new Exception('Capsule non trouvée');
    }

    // Vérifier que l'utilisateur n'est pas l'auteur
    if ($capsule['utilisateur_id'] === $userId) {
        throw new Exception('Vous ne pouvez pas commenter votre propre image');
    }

    // Insérer le commentaire dans la table commentaires
    $stmt = $pdo->prepare('INSERT INTO commentaires (capsule_id, utilisateur_id, comment_text) VALUES (:capsule_id, :user_id, :comment_text)');
    $stmt->execute([
        ':capsule_id' => $capsuleId,
        ':user_id' => $userId,
        ':comment_text' => $comment_text
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Commentaire publié',
        'comment_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur base de données']);
}
