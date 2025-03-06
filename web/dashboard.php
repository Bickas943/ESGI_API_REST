<?php
// web/dashboard.php
session_start();
require_once '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate'])) {
        // Générer une nouvelle clé d'API
        $rawToken = bin2hex(random_bytes(16));
        $hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, hashed_token) VALUES (?, ?)");
        if ($stmt->execute([$user['id'], $hashedToken])) {
            // La clé brute ne sera affichée qu'une seule fois à la création
            $message = "Nouvelle clé d'API générée. Notez-la, elle ne sera plus affichée : " . $rawToken;
        } else {
            $message = "Erreur lors de la génération de la clé d'API.";
        }
    } elseif (isset($_POST['delete_token'])) {
        // Suppression d'une clé d'API spécifique
        $tokenId = $_POST['token_id'];
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$tokenId, $user['id']])) {
            $message = "Clé d'API supprimée.";
        } else {
            $message = "Erreur lors de la suppression de la clé d'API.";
        }
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Récupérer les clés d'API de l'utilisateur
$stmt = $pdo->prepare("SELECT id, created_at FROM user_tokens WHERE user_id = ?");
$stmt->execute([$user['id']]);
$apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard API Keys</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Gestion des Clés d'API</h2>
        <p>Bienvenue, <?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?> !</p>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h3>Mes clés d'API</h3>
        <?php if (count($apiKeys) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date de création</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apiKeys as $key): ?>
                        <tr>
                            <td><?= htmlspecialchars($key['id']) ?></td>
                            <td><?= htmlspecialchars($key['created_at']) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette clé ?');">
                                    <input type="hidden" name="token_id" value="<?= htmlspecialchars($key['id']) ?>">
                                    <button type="submit" name="delete_token">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune clé d'API générée pour le moment.</p>
        <?php endif; ?>

        <form method="post" class="button-group">
            <button type="submit" name="generate">Générer une nouvelle clé d'API</button>
            <button type="submit" name="logout">Déconnexion</button>
        </form>
    </div>
</body>
</html>
