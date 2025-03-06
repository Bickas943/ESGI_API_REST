<?php
// web/admin_users.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Gestion des utilisateurs</h2>
    <a href="admin_dashboard.php">Retour au Dashboard</a>
    <h3>Liste des utilisateurs</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Tel</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td><?= htmlspecialchars($user['prenom']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['adresse']) ?></td>
                <td><?= htmlspecialchars($user['n_tel']) ?></td>
                <td><?= $user['admin'] == 1 ? 'Oui' : 'Non' ?></td>
                <td>
                    <a href="admin_edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                    <a href="admin_delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Créer un nouvel utilisateur</h3>
    <form method="post" action="admin_create_user.php">
        <label>Nom :</label>
        <input type="text" name="nom" required>
        <label>Prénom :</label>
        <input type="text" name="prenom" required>
        <label>Email :</label>
        <input type="email" name="email" required>
        <label>Mot de passe :</label>
        <input type="password" name="mdp" required>
        <label>Adresse :</label>
        <input type="text" name="adresse">
        <label>Numéro de téléphone :</label>
        <input type="text" name="n_tel">
        <label>Admin :</label>
        <select name="admin">
            <option value="0">Non</option>
            <option value="1">Oui</option>
        </select>
        <button type="submit">Créer</button>
    </form>
</div>
</body>
</html>
