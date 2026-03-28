<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

verifyCsrfToken($_POST['csrf_token'] ?? null);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/my-properties.php');
}

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$status = trim($_POST['status'] ?? '');
$user = currentUser();

$allowedStatuses = ['disponible', 'reserve', 'loue', 'vendu', 'retire'];

if ($propertyId <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlashMessage('Mise a jour du statut invalide.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

$statement = $pdo->prepare(
    'UPDATE properties SET status = :status WHERE id = :id AND user_id = :user_id'
);

$statement->execute([
    'status' => $status,
    'id' => $propertyId,
    'user_id' => $user['id'],
]);

if ($statement->rowCount() === 0) {
    setFlashMessage('Impossible de modifier le statut de cette annonce.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

setFlashMessage('Statut de l annonce mis a jour.');
redirect('/housing-cm/properties/my-properties.php');
