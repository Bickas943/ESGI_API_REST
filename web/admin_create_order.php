<?php
// web/admin_create_order.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $numero_commande = $_POST['numero_commande'];
    $prix = $_POST['prix'];
    $objet = $_POST['objet'];
    $date_livraison = $_POST['date_livraison'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, numero_commande, prix, objet, date_livraison, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $numero_commande, $prix, $objet, $date_livraison, $status])) {
        header('Location: admin_orders.php');
        exit;
    } else {
        echo "Erreur lors de la création de la commande.";
    }
} else {
    header('Location: admin_orders.php');
}
?>
