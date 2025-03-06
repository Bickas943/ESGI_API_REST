<?php
// users.php
require 'db.php';
header('Content-Type: application/json');

/**
 * Fonction d'authentification : récupère le token via l'URL (paramètre ?token=...)
 * et le vérifie dans la table user_tokens.
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
            $stmt2 = $pdo->prepare("SELECT id, nom, prenom, email, adresse, n_tel FROM users WHERE id = ?");
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
        // Si un ID est précisé, afficher cet utilisateur
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT id, nom, prenom, email, adresse, n_tel FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
        } else {
            // Sinon, afficher tous les utilisateurs
            $stmt = $pdo->query("SELECT id, nom, prenom, email, adresse, n_tel FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;
    case 'POST':
        // Création d'un utilisateur
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['nom'], $data['prenom'], $data['mdp'], $data['email'])) {
            echo json_encode(['error' => 'Champs requis manquants']);
            exit;
        }
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $mdpClair = $data['mdp'];
        $mdpHashe = password_hash($mdpClair, PASSWORD_DEFAULT);
        $email = $data['email'];
        $adresse = isset($data['adresse']) ? $data['adresse'] : '';
        $n_tel = isset($data['n_tel']) ? $data['n_tel'] : '';

        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, mdp, email, adresse, n_tel) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $mdpHashe, $email, $adresse, $n_tel])) {
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
        if (count($fields) === 0) {
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $values[] = $_GET['id'];
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
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$_GET['id']])) {
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
