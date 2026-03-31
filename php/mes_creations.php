<?php
session_start();
require_once __DIR__ . '/bdd.php';

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare('
    SELECT c.*, u.nom_utilisateur 
    FROM capsules c 
    JOIN utilisateurs u ON c.utilisateur_id = u.id 
    WHERE c.utilisateur_id = ? 
    ORDER BY c.cree_le DESC
');
$stmt->execute([$user_id]);
$mes_capsules = $stmt->fetchAll();

// Ici tu peux réutiliser le même HTML que menu.php pour l'affichage
include 'menu_template.php'; // Ou copie-colle le HTML de menu.php