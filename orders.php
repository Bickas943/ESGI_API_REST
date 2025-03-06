<?php
// orders.php
require 'db.php';
header('Content-Type: application/json');

/**
 * Authentifie l'utilisateur via le token passé dans l'URL (?token=...)
 * et retourne les informations de l'utilisateur.
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
        // Si l'utilisateur est admin, il peut voir toutes les commandes.
        if ($currentUser['admin'] == 1) {
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($order);
            } elseif (isset($_GET['numero_commande'])) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE numero_commande = ?");
                $stmt->execute([$_GET['numero_commande']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($order);
            } else {
                $stmt = $pdo->query("SELECT * FROM orders");
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($orders);
            }
        } else {
            // Les utilisateurs non-admin ne voient que leurs commandes.
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $currentUser['id']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($order);
            } elseif (isset($_GET['numero_commande'])) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE numero_commande = ? AND user_id = ?");
                $stmt->execute([$_GET['numero_commande'], $currentUser['id']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($order);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
                $stmt->execute([$currentUser['id']]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($orders);
            }
        }
        break;
        
    case 'POST':
        // Création d'une commande.
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['numero_commande'], $data['prix'], $data['objet'], $data['date_livraison'], $data['status'])) {
            echo json_encode(['error' => 'Champs requis manquants']);
            exit;
        }
        // Pour les admins, on peut accepter un user_id dans le corps (optionnel), sinon on force l'user_id à celui authentifié.
        if ($currentUser['admin'] == 1 && isset($data['user_id'])) {
            $user_id = $data['user_id'];
        } else {
            $user_id = $currentUser['id'];
        }
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
        // Si non-admin, vérifier que la commande appartient à l'utilisateur authentifié.
        if ($currentUser['admin'] != 1) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $currentUser['id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                echo json_encode(['error' => 'Commande non trouvée ou vous n\'êtes pas autorisé à la modifier']);
                exit;
            }
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
        // Si non-admin, vérifier que la commande appartient à l'utilisateur authentifié.
        if ($currentUser['admin'] != 1) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $currentUser['id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                echo json_encode(['error' => 'Commande non trouvée ou vous n\'êtes pas autorisé à la supprimer']);
                exit;
            }
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
