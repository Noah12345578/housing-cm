<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/search.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$redirectTo = trim($_POST['redirect_to'] ?? '/housing-cm/properties/search.php');

if ($propertyId <= 0) {
    setFlashMessage('Annonce introuvable pour les favoris.', 'error');
    redirect('/housing-cm/properties/search.php');
}

$checkStatement = $pdo->prepare('SELECT id FROM favorites WHERE user_id = :user_id AND property_id = :property_id LIMIT 1');
$checkStatement->execute([
    'user_id' => $user['id'],
    'property_id' => $propertyId,
]);

$existingFavorite = $checkStatement->fetch();

if ($existingFavorite) {
    $deleteStatement = $pdo->prepare('DELETE FROM favorites WHERE id = :id');
    $deleteStatement->execute(['id' => $existingFavorite['id']]);
    setFlashMessage('Le logement a ete retire de tes favoris.');
} else {
    $insertStatement = $pdo->prepare(
        'INSERT INTO favorites (user_id, property_id) VALUES (:user_id, :property_id)'
    );
    $insertStatement->execute([
        'user_id' => $user['id'],
        'property_id' => $propertyId,
    ]);
    setFlashMessage('Le logement a ete ajoute a tes favoris.');
}

if ($redirectTo === '') {
    $redirectTo = '/housing-cm/properties/search.php';
}

redirect($redirectTo);
