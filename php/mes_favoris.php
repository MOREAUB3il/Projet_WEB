<?php
session_start();
require_once __DIR__ . '/bdd.php';

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT c.*, u.nom_utilisateur 
    FROM capsules c
    JOIN favoris f ON f.capsule_id = c.id
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    WHERE f.utilisateur_id = ?
    ORDER BY f.cree_le DESC
');
$stmt->execute([$user_id]);
$mes_favoris = $stmt->fetchAll();

// Affichage identique...