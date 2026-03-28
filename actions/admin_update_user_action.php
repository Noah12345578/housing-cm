<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/admin/users.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$admin = currentUser();

if (($admin['role'] ?? '') !== 'admin') {
    setFlashMessage('Acces reserve a l administrateur.', 'error');
    redirect('/housing-cm/user/dashboard.php');
}

$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$role = trim($_POST['role'] ?? '');
$status = trim($_POST['status'] ?? '');

$allowedRoles = ['client', 'owner', 'agent', 'admin'];
$allowedStatuses = ['active', 'blocked'];

if ($userId <= 0 || !in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatuses, true)) {
    setFlashMessage('Mise a jour utilisateur invalide.', 'error');
    redirect('/housing-cm/admin/users.php');
}

if ($userId === (int) $admin['id']) {
    if ($role !== 'admin' || $status !== 'active') {
        setFlashMessage('Pour ta securite, tu ne peux pas modifier ton propre role admin ni te bloquer toi-meme.', 'error');
        redirect('/housing-cm/admin/users.php');
    }
}

$statement = $pdo->prepare(
    'UPDATE users
     SET role = :role, status = :status
     WHERE id = :id'
);

$statement->execute([
    'role' => $role,
    'status' => $status,
    'id' => $userId,
]);

setFlashMessage('Utilisateur mis a jour avec succes.');
redirect('/housing-cm/admin/users.php');
