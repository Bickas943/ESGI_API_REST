<?php
// web/register.php
session_start();
require_once '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

if (isset($_POST['register'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mdpClair = $_POST['mdp'];
    $mdpHashe = password_hash($mdpClair, PASSWORD_DEFAULT);
    $adresse = $_POST['adresse'] ?? '';
    $n_tel = $_POST['n_tel'] ?? '';

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, mdp, email, adresse, n_tel) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $mdpHashe, $email, $adresse, $n_tel])) {
            $message = "Inscription réussie. Vous pouvez vous connecter.";
        } else {
            $message = "Erreur lors de l'inscription.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inscription</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post">
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
            
            <button type="submit" name="register">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html>
