<?php
require_once __DIR__ . '/admin_check.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    // ==================
    // Gestion utilisateurs
    // ==================
    
    if ($action === 'update_role') {
        $user_id = (int) $data['user_id'];
        $new_role = $data['role']; // 'admin' ou 'utilisateur'
        
        if ($user_id === $_SESSION['user']['id']) {
            throw new Exception('Vous ne pouvez pas modifier votre propre rôle');
        }
        
        if (!in_array($new_role, ['admin', 'utilisateur'])) {
            throw new Exception('Rôle invalide');
        }
        
        $stmt = $pdo->prepare('UPDATE utilisateurs SET role = :role WHERE id = :id');
        $stmt->execute([':role' => $new_role, ':id' => $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Rôle mis à jour']);
        exit;
    }
    
    if ($action === 'delete_user') {
        $user_id = (int) $data['user_id'];
        
        if ($user_id === $_SESSION['user']['id']) {
            throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
        }
        
        // Supprime d'abord les favoris et capsules
        $stmt = $pdo->prepare('DELETE FROM favoris WHERE utilisateur_id = :id');
        $stmt->execute([':id' => $user_id]);
        
        // Récupère les images avant suppression
        $stmt = $pdo->prepare('SELECT chemin_image FROM capsules WHERE utilisateur_id = :id');
        $stmt->execute([':id' => $user_id]);
        $images = $stmt->fetchAll();
        
        // Supprime les fichiers physiques
        foreach ($images as $img) {
            $filepath = __DIR__ . '/../' . $img['chemin_image'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        // Supprime les capsules
        $stmt = $pdo->prepare('DELETE FROM capsules WHERE utilisateur_id = :id');
        $stmt->execute([':id' => $user_id]);
        
        // Supprime l'utilisateur
        $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = :id');
        $stmt->execute([':id' => $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        exit;
    }
    
    // ==================
    // Gestion capsules
    // ==================
    
    if ($action === 'delete_capsule') {
        $capsule_id = (int) $data['capsule_id'];
        
        // Récupère le chemin de l'image
        $stmt = $pdo->prepare('SELECT chemin_image FROM capsules WHERE id = :id');
        $stmt->execute([':id' => $capsule_id]);
        $capsule = $stmt->fetch();
        
        if (!$capsule) {
            throw new Exception('Capsule non trouvée');
        }
        
        // Supprime le fichier physique
        $filepath = __DIR__ . '/../' . $capsule['chemin_image'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Supprime les favoris associés
        $stmt = $pdo->prepare('DELETE FROM favoris WHERE capsule_id = :id');
        $stmt->execute([':id' => $capsule_id]);
        
        // Supprime la capsule
        $stmt = $pdo->prepare('DELETE FROM capsules WHERE id = :id');
        $stmt->execute([':id' => $capsule_id]);
        
        echo json_encode(['success' => true, 'message' => 'Capsule supprimée']);
        exit;
    }
    
    throw new Exception('Action non reconnue');
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
