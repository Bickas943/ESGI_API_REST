<?php
// web/admin_edit_order.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: admin_orders.php');
    exit;
}
$orderId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo "Commande non trouvée.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $numero_commande = $_POST['numero_commande'];
    $prix = $_POST['prix'];
    $objet = $_POST['objet'];
    $date_livraison = $_POST['date_livraison'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET user_id=?, numero_commande=?, prix=?, objet=?, date_livraison=?, status=? WHERE id=?");
    if ($stmt->execute([$user_id, $numero_commande, $prix, $objet, $date_livraison, $status, $orderId])) {
        header('Location: admin_orders.php');
        exit;
    } else {
        echo "Erreur lors de la mise à jour de la commande.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Modifier la commande</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Modifier la commande</h2>
    <a href="admin_orders.php">Retour à la liste</a>
    <form method="post">
        <label>User ID :</label>
        <input type="number" name="user_id" value="<?= htmlspecialchars($order['user_id']) ?>" required>
        <label>Numéro commande :</label>
        <input type="text" name="numero_commande" value="<?= htmlspecialchars($order['numero_commande']) ?>" required>
        <label>Prix :</label>
        <input type="text" name="prix" value="<?= htmlspecialchars($order['prix']) ?>" required>
        <label>Objet :</label>
        <input type="text" name="objet" value="<?= htmlspecialchars($order['objet']) ?>" required>
        <label>Date livraison :</label>
        <input type="date" name="date_livraison" value="<?= htmlspecialchars($order['date_livraison']) ?>" required>
        <label>Status :</label>
        <input type="text" name="status" value="<?= htmlspecialchars($order['status']) ?>" required>
        <button type="submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
