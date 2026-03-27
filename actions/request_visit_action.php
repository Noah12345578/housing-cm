<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/search.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$ownerId = isset($_POST['owner_id']) ? (int) $_POST['owner_id'] : 0;
$preferredDate = trim($_POST['preferred_date'] ?? '');
$message = normalizeText($_POST['message'] ?? '');

if ($propertyId <= 0 || $ownerId <= 0 || $preferredDate === '') {
    setFlashMessage('Veuillez renseigner une date de visite valide.', 'error');
    redirect('/housing-cm/properties/search.php');
}

if ($ownerId === (int) $user['id']) {
    setFlashMessage('Tu ne peux pas demander une visite sur ton propre logement.', 'error');
    redirect('/housing-cm/properties/details.php?id=' . $propertyId);
}

$propertyStatement = $pdo->prepare('SELECT id, user_id, status FROM properties WHERE id = :id LIMIT 1');
$propertyStatement->execute(['id' => $propertyId]);
$property = $propertyStatement->fetch();

if (!$property) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/search.php');
}

if ($property['status'] !== 'disponible') {
    setFlashMessage('Ce logement n est plus disponible pour une visite.', 'error');
    redirect('/housing-cm/properties/details.php?id=' . $propertyId);
}

$visitDate = strtotime($preferredDate);

if ($visitDate === false || $visitDate < (time() + 300)) {
    setFlashMessage('La date de visite doit etre dans le futur.', 'error');
    redirect('/housing-cm/properties/details.php?id=' . $propertyId);
}

$insertStatement = $pdo->prepare(
    'INSERT INTO visit_requests (property_id, requester_id, owner_id, preferred_date, message)
     VALUES (:property_id, :requester_id, :owner_id, :preferred_date, :message)'
);

$insertStatement->execute([
    'property_id' => $propertyId,
    'requester_id' => $user['id'],
    'owner_id' => $ownerId,
    'preferred_date' => date('Y-m-d H:i:s', $visitDate),
    'message' => $message !== '' ? $message : null,
]);

setFlashMessage('Demande de visite envoyee avec succes.');
redirect('/housing-cm/user/visits.php');
