<?php
// users.php
require 'db.php';
header('Content-Type: application/json');

/**
 * Authentifie l'utilisateur via le token passé dans l'URL (?token=...)
 * et retourne les informations (y compris le flag admin).
 */
function authenticateToken($pdo) {
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        echo json_encode(['error' => 'Token manquant dans l’URL (paramètre ?token=...)']);
        exit;
    }
    $rawToken = $_GET['token'];
    $stmt = $pdo->query("SELECT * FROM user_tokens");
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tokens as $t) {
        if (password_verify($rawToken, $t['hashed_token'])) {
            $stmt2 = $pdo->prepare("SELECT id, nom, prenom, email, adresse, n_tel, admin FROM users WHERE id = ?");
            $stmt2->execute([$t['user_id']]);
            $user = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }
        }
    }
    echo json_encode(['error' => 'Token invalide']);
    exit;
}

$currentUser = authenticateToken($pdo);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Si l'utilisateur est admin, il peut consulter tous les utilisateurs ou un utilisateur spécifique.
        if ($currentUser['admin'] == 1) {
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT id, nom, prenom, email, adresse, n_tel, admin FROM users WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($user);
            } else {
                $stmt = $pdo->query("SELECT id, nom, prenom, email, adresse, n_tel, admin FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($users);
            }
        } else {
            // Un utilisateur non admin ne peut voir que ses propres informations.
            if (isset($_GET['id']) && $_GET['id'] != $currentUser['id']) {
                echo json_encode(['error' => 'Accès non autorisé.']);
                exit;
            }
            echo json_encode($currentUser);
        }
        break;
        
    case 'POST':
        // Seul l'admin peut créer de nouveaux utilisateurs via cet endpoint.
        if ($currentUser['admin'] != 1) {
            echo json_encode(['error' => 'Accès non autorisé.']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['nom'], $data['prenom'], $data['mdp'], $data['email'])) {
            echo json_encode(['error' => 'Champs requis manquants']);
            exit;
        }
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $mdpHashe = password_hash($data['mdp'], PASSWORD_DEFAULT);
        $email = $data['email'];
        $adresse = isset($data['adresse']) ? $data['adresse'] : '';
        $n_tel = isset($data['n_tel']) ? $data['n_tel'] : '';
        $admin = isset($data['admin']) ? $data['admin'] : 0; // possibilité de définir admin

        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, mdp, email, adresse, n_tel, admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $mdpHashe, $email, $adresse, $n_tel, $admin])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la création de l’utilisateur']);
        }
        break;
        
    case 'PUT':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'ID utilisateur requis pour la mise à jour']);
            exit;
        }
        $targetId = $_GET['id'];
        // Un utilisateur non-admin ne peut modifier que son propre compte.
        if ($currentUser['admin'] != 1 && $targetId != $currentUser['id']) {
            echo json_encode(['error' => 'Accès non autorisé pour la mise à jour']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $fields = [];
        $values = [];
        if (isset($data['nom'])) { $fields[] = "nom = ?"; $values[] = $data['nom']; }
        if (isset($data['prenom'])) { $fields[] = "prenom = ?"; $values[] = $data['prenom']; }
        if (isset($data['mdp'])) { 
            $fields[] = "mdp = ?"; 
            $values[] = password_hash($data['mdp'], PASSWORD_DEFAULT); 
        }
        if (isset($data['email'])) { $fields[] = "email = ?"; $values[] = $data['email']; }
        if (isset($data['adresse'])) { $fields[] = "adresse = ?"; $values[] = $data['adresse']; }
        if (isset($data['n_tel'])) { $fields[] = "n_tel = ?"; $values[] = $data['n_tel']; }
        // Seul l'admin peut modifier le champ admin
        if (isset($data['admin']) && $currentUser['admin'] == 1) { 
            $fields[] = "admin = ?"; 
            $values[] = $data['admin']; 
        }
        if (count($fields) === 0) {
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $values[] = $targetId;
        $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?");
        if ($stmt->execute($values)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la mise à jour de l’utilisateur']);
        }
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'ID utilisateur requis pour la suppression']);
            exit;
        }
        $targetId = $_GET['id'];
        // Un utilisateur non-admin ne peut supprimer que son propre compte.
        if ($currentUser['admin'] != 1 && $targetId != $currentUser['id']) {
            echo json_encode(['error' => 'Accès non autorisé pour la suppression']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$targetId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la suppression de l’utilisateur']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
?>
