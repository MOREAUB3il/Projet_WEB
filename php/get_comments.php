<?php
session_start();
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json');

try {
    $capsuleId = intval($_GET['capsule_id'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $page = max(1, $page);
    $limit = 10; // Commentaires par page
    $offset = ($page - 1) * $limit;

    if ($capsuleId <= 0) {
        throw new Exception('ID Capsule invalide');
    }

    // Récupérer le nombre total de commentaires
    $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM commentaires WHERE capsule_id = :capsule_id');
    $countStmt->execute([':capsule_id' => $capsuleId]);
    $total = $countStmt->fetch()['total'];

    // Récupérer les commentaires
    $stmt = $pdo->prepare('
        SELECT 
            c.id,
            c.comment_text,
            c.cree_le,
            u.id as user_id,
            u.nom_utilisateur
        FROM commentaires c
        JOIN utilisateurs u ON c.utilisateur_id = u.id
        WHERE c.capsule_id = :capsule_id
        ORDER BY c.cree_le DESC
        LIMIT :limit OFFSET :offset
    ');
    
    $stmt->bindValue(':capsule_id', $capsuleId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $comments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $comments[] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'comment_text' => htmlspecialchars($row['comment_text'], ENT_QUOTES, 'UTF-8'),
            'nom_utilisateur' => htmlspecialchars($row['nom_utilisateur'], ENT_QUOTES, 'UTF-8'),
            'cree_le' => date('d/m/Y à H:i', strtotime($row['cree_le']))
        ];
    }

    echo json_encode([
        'status' => 'success',
        'comments' => $comments,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages_total' => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur base de données']);
}
