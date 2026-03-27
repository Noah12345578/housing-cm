<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/user/visits.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$visitId = isset($_POST['visit_id']) ? (int) $_POST['visit_id'] : 0;
$status = trim($_POST['status'] ?? '');

$allowedStatuses = ['acceptee', 'refusee'];

if ($visitId <= 0 || !in_array($status, $allowedStatuses, true)) {
    setFlashMessage('Action de visite invalide.', 'error');
    redirect('/housing-cm/user/visits.php');
}

$statement = $pdo->prepare('SELECT id, owner_id FROM visit_requests WHERE id = :id LIMIT 1');
$statement->execute(['id' => $visitId]);
$visit = $statement->fetch();

if (!$visit) {
    setFlashMessage('Demande de visite introuvable.', 'error');
    redirect('/housing-cm/user/visits.php');
}

if ((int) $visit['owner_id'] !== (int) $user['id']) {
    setFlashMessage('Tu n es pas autorise a modifier cette demande.', 'error');
    redirect('/housing-cm/user/visits.php');
}

$updateStatement = $pdo->prepare('UPDATE visit_requests SET status = :status WHERE id = :id');
$updateStatement->execute([
    'status' => $status,
    'id' => $visitId,
]);

setFlashMessage('Le statut de la demande de visite a ete mis a jour.');
redirect('/housing-cm/user/visits.php');
