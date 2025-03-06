<?php
// orders.php
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
            $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?");
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
        // Recherche par numéro de commande si le paramètre est présent
        if (isset($_GET['numero_commande'])) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND numero_commande = ?");
            $stmt->execute([$currentUser['id'], $_GET['numero_commande']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($order);
        } else {
            // Sinon, liste toutes les commandes de l'utilisateur connecté
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
            $stmt->execute([$currentUser['id']]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
        }
        break;
    case 'POST':
        // Création d'une commande pour l'utilisateur connecté
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['numero_commande'], $data['prix'], $data['objet'], $data['date_livraison'], $data['status'])) {
            echo json_encode(['error' => 'Champs requis manquants']);
            exit;
        }
        $user_id = $currentUser['id'];
        $numero_commande = $data['numero_commande'];
        $prix = $data['prix'];
        $objet = $data['objet'];
        $date_livraison = $data['date_livraison'];
        $status = $data['status'];
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, numero_commande, prix, objet, date_livraison, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $numero_commande, $prix, $objet, $date_livraison, $status])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la création de la commande']);
        }
        break;
    case 'PUT':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'ID de la commande requis pour la mise à jour']);
            exit;
        }
        $orderId = $_GET['id'];
        // Vérifier que la commande appartient à l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $currentUser['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            echo json_encode(['error' => 'Commande non trouvée ou non autorisée']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $fields = [];
        $values = [];
        if (isset($data['numero_commande'])) { $fields[] = "numero_commande = ?"; $values[] = $data['numero_commande']; }
        if (isset($data['prix'])) { $fields[] = "prix = ?"; $values[] = $data['prix']; }
        if (isset($data['objet'])) { $fields[] = "objet = ?"; $values[] = $data['objet']; }
        if (isset($data['date_livraison'])) { $fields[] = "date_livraison = ?"; $values[] = $data['date_livraison']; }
        if (isset($data['status'])) { $fields[] = "status = ?"; $values[] = $data['status']; }
        if (count($fields) === 0) {
            echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $values[] = $orderId;
        $stmt = $pdo->prepare("UPDATE orders SET " . implode(", ", $fields) . " WHERE id = ?");
        if ($stmt->execute($values)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la mise à jour de la commande']);
        }
        break;
    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'ID de la commande requis pour la suppression']);
            exit;
        }
        $orderId = $_GET['id'];
        // Vérifier que la commande appartient à l'utilisateur connecté
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $currentUser['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            echo json_encode(['error' => 'Commande non trouvée ou non autorisée']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        if ($stmt->execute([$orderId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la suppression de la commande']);
        }
        break;
    default:
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
?>
