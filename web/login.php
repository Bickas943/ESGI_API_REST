<?php
// web/login.php
session_start();
require_once '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($mdp, $user['mdp'])) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $message = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <?php if ($message): ?>
            <div class="message error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Email :</label>
            <input type="email" name="email" required>
            
            <label>Mot de passe :</label>
            <input type="password" name="mdp" required>
            
            <button type="submit" name="login">Se connecter</button>
        </form>
        <p>Pas de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
    </div>
</body>
</html>
