<?php
// web/admin_edit_user.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit;
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'] ?? '';
    $n_tel = $_POST['n_tel'] ?? '';
    $admin = $_POST['admin'];
    
    if (!empty($_POST['mdp'])) {
        $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=?, adresse=?, n_tel=?, admin=?, mdp=? WHERE id=?");
        $result = $stmt->execute([$nom, $prenom, $email, $adresse, $n_tel, $admin, $mdp, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=?, adresse=?, n_tel=?, admin=? WHERE id=?");
        $result = $stmt->execute([$nom, $prenom, $email, $adresse, $n_tel, $admin, $id]);
    }
    if ($result) {
        header('Location: admin_users.php');
        exit;
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Modifier l'utilisateur</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h2>Modifier l'utilisateur</h2>
    <a href="admin_users.php">Retour à la liste</a>
    <form method="post">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
        <label>Prénom :</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <label>Adresse :</label>
        <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>">
        <label>Numéro de téléphone :</label>
        <input type="text" name="n_tel" value="<?= htmlspecialchars($user['n_tel']) ?>">
        <label>Admin :</label>
        <select name="admin">
            <option value="0" <?= $user['admin'] == 0 ? 'selected' : '' ?>>Non</option>
            <option value="1" <?= $user['admin'] == 1 ? 'selected' : '' ?>>Oui</option>
        </select>
        <label>Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
        <input type="password" name="mdp">
        <button type="submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
