<?php
// web/admin_delete_token.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['id'])) {
    $tokenId = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE id = ?");
    if ($stmt->execute([$tokenId])) {
        header('Location: admin_tokens.php');
        exit;
    } else {
        echo "Erreur lors de la suppression de la clé API.";
    }
} else {
    header('Location: admin_tokens.php');
}
?>
