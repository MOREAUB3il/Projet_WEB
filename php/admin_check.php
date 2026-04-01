<?php
/**
 * Vérifie que l'utilisateur connecté est admin.
 * À inclure au début de chaque page admin.
 */
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../html/connexion.html?status=error&message=Accès refusé');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../php/menu.php");
    exit;
}

require_once __DIR__ . '/bdd.php';
?>
