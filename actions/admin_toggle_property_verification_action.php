<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/admin/properties.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$admin = currentUser();

if (($admin['role'] ?? '') !== 'admin') {
    setFlashMessage('Acces reserve a l administrateur.', 'error');
    redirect('/housing-cm/user/dashboard.php');
}

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;

if ($propertyId <= 0) {
    setFlashMessage('Annonce invalide pour la verification.', 'error');
    redirect('/housing-cm/admin/properties.php');
}

$statement = $pdo->prepare('SELECT is_verified FROM properties WHERE id = :id LIMIT 1');
$statement->execute(['id' => $propertyId]);
$property = $statement->fetch();

if (!$property) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/admin/properties.php');
}

$newState = (int) !$property['is_verified'];

$updateStatement = $pdo->prepare(
    'UPDATE properties
     SET is_verified = :is_verified
     WHERE id = :id'
);

$updateStatement->execute([
    'is_verified' => $newState,
    'id' => $propertyId,
]);

setFlashMessage(
    $newState === 1
        ? 'Annonce marquee comme verifiee.'
        : 'Badge de verification retire.'
);

redirect('/housing-cm/admin/properties.php');
