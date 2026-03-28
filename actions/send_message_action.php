<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/search.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$receiverId = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
$messageText = normalizeText($_POST['message'] ?? '');
$redirectTo = trim($_POST['redirect_to'] ?? '/housing-cm/messages/inbox.php');

if ($receiverId <= 0 || $messageText === '') {
    setFlashMessage('Veuillez remplir correctement le formulaire de message.', 'error');
    redirect('/housing-cm/properties/search.php');
}

if (strlen($messageText) < 5) {
    setFlashMessage('Le message est trop court.', 'error');
    redirect($redirectTo);
}

if ($receiverId === (int) $user['id']) {
    setFlashMessage('Tu ne peux pas t envoyer un message a toi-meme.', 'error');
    redirect($redirectTo);
}

if ($propertyId > 0) {
    $propertyStatement = $pdo->prepare('SELECT id, user_id FROM properties WHERE id = :id LIMIT 1');
    $propertyStatement->execute(['id' => $propertyId]);
    $property = $propertyStatement->fetch();

    if (!$property) {
        setFlashMessage('Annonce introuvable.', 'error');
        redirect('/housing-cm/properties/search.php');
    }
}

$insertStatement = $pdo->prepare(
    'INSERT INTO messages (sender_id, receiver_id, property_id, message) VALUES (:sender_id, :receiver_id, :property_id, :message)'
);

$insertStatement->execute([
    'sender_id' => $user['id'],
    'receiver_id' => $receiverId,
    'property_id' => $propertyId > 0 ? $propertyId : null,
    'message' => $messageText,
]);

setFlashMessage('Message envoye avec succes.');
redirect($redirectTo);
