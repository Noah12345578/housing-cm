<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

verifyCsrfToken($_POST['csrf_token'] ?? null);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/user/profile.php');
}

$user = currentUser();
$fullName = normalizeText($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = normalizeText($_POST['phone'] ?? '');

$_SESSION['old_profile_input'] = [
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
];

if ($fullName === '' || $email === '' || $phone === '') {
    setFlashMessage('Tous les champs du profil sont obligatoires.', 'error');
    redirect('/housing-cm/user/profile.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('Adresse e-mail invalide.', 'error');
    redirect('/housing-cm/user/profile.php');
}

if (strlen($fullName) < 3) {
    setFlashMessage('Le nom complet doit contenir au moins 3 caracteres.', 'error');
    redirect('/housing-cm/user/profile.php');
}

if (strlen($phone) < 6) {
    setFlashMessage('Le numero de telephone semble trop court.', 'error');
    redirect('/housing-cm/user/profile.php');
}

$emailCheckStatement = $pdo->prepare(
    'SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1'
);

$emailCheckStatement->execute([
    'email' => $email,
    'id' => $user['id'],
]);

if ($emailCheckStatement->fetch()) {
    setFlashMessage('Cette adresse e-mail est deja utilisee par un autre compte.', 'error');
    redirect('/housing-cm/user/profile.php');
}

$updateStatement = $pdo->prepare(
    'UPDATE users
     SET full_name = :full_name, email = :email, phone = :phone
     WHERE id = :id'
);

$updateStatement->execute([
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'id' => $user['id'],
]);

$_SESSION['user']['full_name'] = $fullName;
$_SESSION['user']['email'] = $email;
$_SESSION['user']['phone'] = $phone;

unset($_SESSION['old_profile_input']);

setFlashMessage('Profil mis a jour avec succes.');
redirect('/housing-cm/user/profile.php');
