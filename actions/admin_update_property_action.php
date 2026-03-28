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
$status = trim($_POST['status'] ?? '');

$allowedStatuses = ['disponible', 'reserve', 'loue', 'vendu', 'retire'];

if ($propertyId <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlashMessage('Mise a jour d annonce invalide.', 'error');
    redirect('/housing-cm/admin/properties.php');
}

$statement = $pdo->prepare(
    'UPDATE properties
     SET status = :status
     WHERE id = :id'
);

$statement->execute([
    'status' => $status,
    'id' => $propertyId,
]);

setFlashMessage('Statut de l annonce mis a jour avec succes.');
redirect('/housing-cm/admin/properties.php');
