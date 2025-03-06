<?php
// web/admin_tokens.php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Récupérer tous les tokens existants (avec info utilisateur associée)
$stmt = $pdo->query("SELECT ut.id AS token_id, ut.created_at, u.id AS user_id, u.email 
                     FROM user_tokens ut 
                     JOIN users u ON ut.user_id = u.id
                     ORDER BY ut.created_at DESC");
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste de tous les utilisateurs pour le formulaire
$stmt2 = $pdo->query("SELECT id, email FROM users ORDER BY id ASC");
$allUsers = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$message = '';

// Gestion du formulaire de création de token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_token'])) {
        $userId = $_POST['user_id'];

        // Vérifier que l'utilisateur existe réellement
        $checkStmt = $pdo->prepare("SELECT id, email FROM users WHERE id = ?");
        $checkStmt->execute([$userId]);
        $userCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($userCheck) {
            // Générer un token brut
            $rawToken = bin2hex(random_bytes(16));
            // Hasher le token
            $hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);

            // Insérer dans user_tokens
            $stmt3 = $pdo->prepare("INSERT INTO user_tokens (user_id, hashed_token) VALUES (?, ?)");
            if ($stmt3->execute([$userId, $hashedToken])) {
                $message = "Clé d'API générée pour l'utilisateur ID={$userId} ({$userCheck['email']}).<br>"
                         . "<strong>Notez ce token, il ne sera plus affiché :</strong> $rawToken";
            } else {
                $message = "Erreur lors de la création du token.";
            }
        } else {
            $message = "Utilisateur introuvable. Aucune clé générée.";
        }
    }
}
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

    <!-- Affichage d'un éventuel message (succès ou erreur) -->
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <h3>Créer une nouvelle clé d'API</h3>
    <form method="post">
        <label>Choisir un utilisateur :</label>
        <select name="user_id" required>
            <?php foreach ($allUsers as $usr): ?>
                <option value="<?= $usr['id'] ?>">
                    <?= htmlspecialchars($usr['id']) ?> - <?= htmlspecialchars($usr['email']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="create_token">Générer le token</button>
    </form>

    <h3>Liste des clés existantes</h3>
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
                    <a href="admin_delete_token.php?id=<?= $token['token_id'] ?>"
                       onclick="return confirm('Supprimer cette clé API ?')">
                       Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
