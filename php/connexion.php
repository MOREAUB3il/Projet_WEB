<?php
// Démarre la session pour pouvoir stocker l'utilisateur connecté.
session_start();

// Charge la connexion à la base de données ($pdo).
require_once __DIR__ . '/bdd.php';

/**
 * Redirige vers la page de connexion avec un message utilisateur.
 *
 * @param string $type Type du message : success ou error.
 * @param string $text Texte à afficher sur la page.
 */
function redirectWithMessage($type, $text) {
    $query = http_build_query(['status' => $type, 'message' => $text]);
    header("Location: ../html/connexion.html?$query");
    exit;
}

// Cette page ne doit être appelée que via un formulaire POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('error', 'Requête invalide.');
}

// Détermine l'action demandée : inscription ou connexion.
$action = $_POST['action'] ?? '';

// =========================
// Cas 1 : création de compte
// =========================
if ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérifie que tous les champs sont remplis.
    if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
        redirectWithMessage('error', 'Tous les champs sont requis.');
    }

    // Vérifie le format de l'email.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('error', 'Email invalide.');
    }

    // Vérifie que les deux mots de passe correspondent.
    if ($password !== $confirm_password) {
        redirectWithMessage('error', 'Les mots de passe ne correspondent pas.');
    }

    // Impose une longueur minimale au mot de passe.
    if (strlen($password) < 8) {
        redirectWithMessage('error', 'Le mot de passe doit faire au moins 8 caractères.');
    }

    try {
        // Vérifie si le nom d'utilisateur ou l'email existe déjà dans la table "utilisateurs".
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE nom_utilisateur = :username OR email = :email');
        $stmt->execute([':username' => $username, ':email' => $email]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            redirectWithMessage('error', 'Nom ou email déjà utilisé.');
        }

        // Hash le mot de passe avant de l'enregistrer.
        $pwHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertion avec les noms de colonnes en français
        $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (:username, :email, :password_hash)');
        $stmt->execute([':username' => $username, ':email' => $email, ':password_hash' => $pwHash]);

        // Connecte automatiquement l'utilisateur après son inscription.
        $_SESSION['user'] = [
            'id' => (int) $pdo->lastInsertId(),
            'username' => $username,
            'email' => $email,
            'role' => 'utilisateur' // On garde trace du rôle dans la session
        ];
        header('Location: ../php/menu.php');
        exit;

    } catch (PDOException $e) {
        redirectWithMessage('error', 'Une erreur est survenue lors de l’inscription.');
    }

// =====================
// Cas 2 : connexion
// =====================
} elseif ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Vérifie que les champs du formulaire sont bien renseignés.
    if ($username === '' || $password === '') {
        redirectWithMessage('error', 'Tous les champs sont requis.');
    }

    try {
        // Recherche l'utilisateur par nom ou par email dans la table "utilisateurs".
        $stmt = $pdo->prepare('SELECT id, nom_utilisateur, email, mot_de_passe, role FROM utilisateurs WHERE nom_utilisateur = :username OR email = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie le mot de passe saisi avec le hash stocké en base.
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user'] = [
                'id' => $user['id'], 
                'username' => $user['nom_utilisateur'], 
                'email' => $user['email'],
                'role' => $user['role']
            ];
            header('Location: ../php/menu.php');
            exit;
        }

        redirectWithMessage('error', 'Identifiants invalides.');

    } catch (PDOException $e) {
        redirectWithMessage('error', 'Une erreur est survenue lors de la connexion.');
    }

} else {
    // Si l'action n'est ni login ni register, on renvoie une erreur.
    redirectWithMessage('error', 'Action non reconnue.');
}