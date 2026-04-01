<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/bdd.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['capsule_id']) || !isset($input['description'])) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$capsule_id = (int)$input['capsule_id'];
$newDescription = trim($input['description']);

if (empty($newDescription)) {
    echo json_encode(['success' => false, 'error' => 'La description ne peut pas être vide']);
    exit;
}

if (strlen($newDescription) > 500) {
    echo json_encode(['success' => false, 'error' => 'La description ne peut pas dépasser 500 caractères']);
    exit;
}

try {
    // Vérifier que l'utilisateur est l'auteur de la capsule
    $stmt = $pdo->prepare('SELECT utilisateur_id FROM capsules WHERE id = ?');
    $stmt->execute([$capsule_id]);
    $capsule = $stmt->fetch();

    if (!$capsule) {
        echo json_encode(['success' => false, 'error' => 'Capsule introuvable']);
        exit;
    }

    if ($capsule['utilisateur_id'] !== $user_id) {
        echo json_encode(['success' => false, 'error' => 'Vous n\'êtes pas autorisé à modifier cette capsule']);
        exit;
    }

    // Mettre à jour la description
    $stmt = $pdo->prepare('UPDATE capsules SET description = ? WHERE id = ? AND utilisateur_id = ?');
    $stmt->execute([$newDescription, $capsule_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Description mise à jour avec succès']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données : ' . $e->getMessage()]);
}
?>
