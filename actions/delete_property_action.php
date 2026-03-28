<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

verifyCsrfToken($_POST['csrf_token'] ?? null);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/my-properties.php');
}

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$user = currentUser();

if ($propertyId <= 0) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

$statement = $pdo->prepare('DELETE FROM properties WHERE id = :id AND user_id = :user_id');
$statement->execute([
    'id' => $propertyId,
    'user_id' => $user['id'],
]);

if ($statement->rowCount() === 0) {
    setFlashMessage('Suppression impossible pour cette annonce.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

setFlashMessage('Annonce supprimee avec succes.');
redirect('/housing-cm/properties/my-properties.php');
