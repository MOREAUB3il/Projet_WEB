<?php
session_start();

// On vide toutes les variables de session
$_SESSION = array();

// On détruit la session
session_destroy();

// On redirige vers la page d'accueil PHP
header('Location: page_accueil.php');
exit;
?>