<?php
// web/admin_dashboard.php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    <p>Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom']) ?> <?= htmlspecialchars($_SESSION['user']['nom']) ?> (Admin)</p>
    <nav>
        <ul>
            <li><a href="admin_users.php">Gérer les utilisateurs</a></li>
            <li><a href="admin_orders.php">Gérer les commandes</a></li>
            <li><a href="admin_tokens.php">Gérer les clés API</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>
</div>
</body>
</html>
