<?php
// web/admin_delete_order.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['id'])) {
    $orderId = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$orderId])) {
        header('Location: admin_orders.php');
        exit;
    } else {
        echo "Erreur lors de la suppression.";
    }
} else {
    header('Location: admin_orders.php');
}
?>
