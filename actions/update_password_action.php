<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

verifyCsrfToken($_POST['csrf_token'] ?? null);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/user/profile.php');
}

$user = currentUser();
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmNewPassword = $_POST['confirm_new_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmNewPassword === '') {
    setFlashMessage('Tous les champs du mot de passe sont obligatoires.', 'error');
    redirect('/housing-cm/user/profile.php');
}

if (strlen($newPassword) < 6) {
    setFlashMessage('Le nouveau mot de passe doit contenir au moins 6 caracteres.', 'error');
    redirect('/housing-cm/user/profile.php');
}

if ($newPassword !== $confirmNewPassword) {
    setFlashMessage('La confirmation du nouveau mot de passe ne correspond pas.', 'error');
    redirect('/housing-cm/user/profile.php');
}

$statement = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
$statement->execute(['id' => $user['id']]);
$dbUser = $statement->fetch();

if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
    setFlashMessage('Le mot de passe actuel est incorrect.', 'error');
    redirect('/housing-cm/user/profile.php');
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStatement = $pdo->prepare(
    'UPDATE users SET password = :password WHERE id = :id'
);

$updateStatement->execute([
    'password' => $hashedPassword,
    'id' => $user['id'],
]);

setFlashMessage('Mot de passe modifie avec succes.');
redirect('/housing-cm/user/profile.php');
