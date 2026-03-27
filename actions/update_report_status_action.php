<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if (($user['role'] ?? '') !== 'admin') {
    setFlashMessage('Acces reserve a l administrateur.', 'error');
    redirect('/housing-cm/user/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/admin/reports.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$reportId = isset($_POST['report_id']) ? (int) $_POST['report_id'] : 0;
$status = trim($_POST['status'] ?? '');

$allowedStatuses = ['traite', 'rejete'];

if ($reportId <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlashMessage('Mise a jour du signalement invalide.', 'error');
    redirect('/housing-cm/admin/reports.php');
}

$updateStatement = $pdo->prepare('UPDATE reports SET status = :status WHERE id = :id');
$updateStatement->execute([
    'status' => $status,
    'id' => $reportId,
]);

setFlashMessage('Le statut du signalement a ete mis a jour.');
redirect('/housing-cm/admin/reports.php');
