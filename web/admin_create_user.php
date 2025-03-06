<?php
// web/admin_create_user.php
session_start();
require_once '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
    $adresse = $_POST['adresse'] ?? '';
    $n_tel = $_POST['n_tel'] ?? '';
    $admin = $_POST['admin'];
    
    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, mdp, email, adresse, n_tel, admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$nom, $prenom, $mdp, $email, $adresse, $n_tel, $admin])) {
        header('Location: admin_users.php');
    } else {
        echo "Erreur lors de la création de l'utilisateur.";
    }
} else {
    header('Location: admin_users.php');
}
?>
