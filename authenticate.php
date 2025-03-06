<?php
// authenticate.php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'], $data['mdp'])) {
    echo json_encode(['error' => 'Email et mot de passe requis']);
    exit;
}

$email = $data['email'];
$mdp = $data['mdp'];

// Récupérer l'utilisateur par email
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['error' => 'Utilisateur non trouvé']);
    exit;
}

// Vérifier le mot de passe avec password_verify
if (!password_verify($mdp, $user['mdp'])) {
    echo json_encode(['error' => 'Mot de passe invalide']);
    exit;
}

// Générer un token brut
$rawToken = bin2hex(random_bytes(16));
// Hashage du token
$hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);

// Insérer le token hashé dans la table user_tokens
$stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, hashed_token) VALUES (?, ?)");
$stmt->execute([$user['id'], $hashedToken]);

echo json_encode([
    'success' => true,
    'token' => $rawToken
]);
