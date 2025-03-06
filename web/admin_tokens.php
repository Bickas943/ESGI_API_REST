<?php
// web/admin_tokens.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Récupérer toutes les clés API avec les infos utilisateur associées
$stmt = $pdo->query("SELECT ut.id AS token_id, ut.created_at, u.id AS user_id, u.email 
                     FROM user_tokens ut 
                     JOIN users u ON ut.user_id = u.id");
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gestion des clés API</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Gestion des clés API</h2>
    <a href="admin_dashboard.php">Retour au Dashboard</a>
    <table>
        <thead>
            <tr>
                <th>ID Clé</th>
                <th>Date de création</th>
                <th>User ID</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tokens as $token): ?>
            <tr>
                <td><?= htmlspecialchars($token['token_id']) ?></td>
                <td><?= htmlspecialchars($token['created_at']) ?></td>
                <td><?= htmlspecialchars($token['user_id']) ?></td>
                <td><?= htmlspecialchars($token['email']) ?></td>
                <td>
                    <a href="admin_delete_token.php?id=<?= $token['token_id'] ?>" onclick="return confirm('Supprimer cette clé API ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
