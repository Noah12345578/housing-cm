<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/my-properties.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
$user = currentUser();

if ($propertyId <= 0 || $imageId <= 0) {
    setFlashMessage('Image introuvable.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

$imageStatement = $pdo->prepare(
    'SELECT property_images.id
     FROM property_images
     INNER JOIN properties ON property_images.property_id = properties.id
     WHERE property_images.id = :image_id
       AND property_images.property_id = :property_id
       AND properties.user_id = :user_id
     LIMIT 1'
);

$imageStatement->execute([
    'image_id' => $imageId,
    'property_id' => $propertyId,
    'user_id' => $user['id'],
]);

if (!$imageStatement->fetch()) {
    setFlashMessage('Tu ne peux pas modifier cette image.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

$pdo->beginTransaction();

try {
    $resetStatement = $pdo->prepare(
        'UPDATE property_images SET is_main = 0 WHERE property_id = :property_id'
    );
    $resetStatement->execute(['property_id' => $propertyId]);

    $mainStatement = $pdo->prepare(
        'UPDATE property_images SET is_main = 1 WHERE id = :image_id AND property_id = :property_id'
    );
    $mainStatement->execute([
        'image_id' => $imageId,
        'property_id' => $propertyId,
    ]);

    $pdo->commit();
    setFlashMessage('Image principale mise a jour avec succes.');
} catch (Throwable $exception) {
    $pdo->rollBack();
    setFlashMessage('Impossible de definir cette image comme principale.', 'error');
}

redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
