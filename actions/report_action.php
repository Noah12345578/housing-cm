<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/search.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$reason = trim($_POST['reason'] ?? '');
$description = normalizeText($_POST['description'] ?? '');

if ($propertyId <= 0 || $reason === '') {
    setFlashMessage('Veuillez choisir un motif de signalement.', 'error');
    redirect('/housing-cm/properties/search.php');
}

$allowedReasons = [
    'arnaque_suspectee',
    'fausses_informations',
    'logement_deja_pris',
    'prix_trompeur',
    'contenu_inapproprie',
    'autre',
];

if (!in_array($reason, $allowedReasons, true)) {
    setFlashMessage('Motif de signalement invalide.', 'error');
    redirect('/housing-cm/properties/details.php?id=' . $propertyId);
}

$propertyStatement = $pdo->prepare('SELECT id FROM properties WHERE id = :id LIMIT 1');
$propertyStatement->execute(['id' => $propertyId]);
$property = $propertyStatement->fetch();

if (!$property) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/search.php');
}

$insertStatement = $pdo->prepare(
    'INSERT INTO reports (user_id, property_id, reason, description)
     VALUES (:user_id, :property_id, :reason, :description)'
);

$insertStatement->execute([
    'user_id' => $user['id'],
    'property_id' => $propertyId,
    'reason' => $reason,
    'description' => $description !== '' ? $description : null,
]);

setFlashMessage('Le signalement a ete envoye a l administrateur.');
redirect('/housing-cm/user/reports.php');
