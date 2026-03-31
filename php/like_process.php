<?php
session_start();
require_once __DIR__ . '/bdd.php';
header('Content-Type: application/json; charset=utf-8');

// On vérifie que l'utilisateur est connecté et qu'on a un ID de capsule
if (!isset($_SESSION['user']) || !isset($_POST['capsule_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$capsule_id = (int)$_POST['capsule_id'];

try {
    // 1. On vérifie si le like existe déjà
    $check = $pdo->prepare('SELECT id FROM favoris WHERE utilisateur_id = ? AND capsule_id = ?');
    $check->execute([$user_id, $capsule_id]);
    $existingLike = $check->fetch();

    if ($existingLike) {
        // 2. Si oui, on le supprime (Unlike)
        $delete = $pdo->prepare('DELETE FROM favoris WHERE utilisateur_id = ? AND capsule_id = ?');
        $delete->execute([$user_id, $capsule_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // 3. Sinon, on l'ajoute (Like)
        $insert = $pdo->prepare('INSERT INTO favoris (utilisateur_id, capsule_id) VALUES (?, ?)');
        $insert->execute([$user_id, $capsule_id]);
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}