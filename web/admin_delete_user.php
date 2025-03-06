<?php
// web/admin_delete_user.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        header('Location: admin_users.php');
        exit;
    } else {
        echo "Erreur lors de la suppression.";
    }
} else {
    header('Location: admin_users.php');
}
?>
