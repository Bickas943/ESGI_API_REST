<?php
// web/admin_orders.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Récupérer toutes les commandes avec l'email de l'utilisateur associé
$stmt = $pdo->query("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gestion des commandes</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Gestion des commandes</h2>
    <a href="admin_dashboard.php">Retour au Dashboard</a>
    <h3>Liste des commandes</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Email</th>
                <th>Numéro commande</th>
                <th>Prix</th>
                <th>Objet</th>
                <th>Date livraison</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['user_id']) ?></td>
                <td><?= htmlspecialchars($order['email']) ?></td>
                <td><?= htmlspecialchars($order['numero_commande']) ?></td>
                <td><?= htmlspecialchars($order['prix']) ?></td>
                <td><?= htmlspecialchars($order['objet']) ?></td>
                <td><?= htmlspecialchars($order['date_livraison']) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td>
                    <a href="admin_edit_order.php?id=<?= $order['id'] ?>">Modifier</a>
                    <a href="admin_delete_order.php?id=<?= $order['id'] ?>" onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Créer une nouvelle commande</h3>
    <form method="post" action="admin_create_order.php">
        <label>User ID :</label>
        <input type="number" name="user_id" required>
        <label>Numéro commande :</label>
        <input type="text" name="numero_commande" required>
        <label>Prix :</label>
        <input type="text" name="prix" required>
        <label>Objet :</label>
        <input type="text" name="objet" required>
        <label>Date livraison :</label>
        <input type="date" name="date_livraison" required>
        <label>Status :</label>
        <input type="text" name="status" required>
        <button type="submit">Créer</button>
    </form>
</div>
</body>
</html>
