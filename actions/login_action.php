<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/auth/login.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$_SESSION['old_input'] = [
    'email' => $email,
];

if ($email === '' || $password === '') {
    setFlashMessage('Veuillez remplir votre e-mail et votre mot de passe.', 'error');
    redirect('/housing-cm/auth/login.php');
}

$statement = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
$statement->execute(['email' => $email]);
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    setFlashMessage('Identifiants incorrects.', 'error');
    redirect('/housing-cm/auth/login.php');
}

if ($user['status'] !== 'active') {
    setFlashMessage('Ce compte est bloque. Contacte l administrateur.', 'error');
    redirect('/housing-cm/auth/login.php');
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'profile_image' => $user['profile_image'] ?? null,
    'role' => $user['role'],
];

unset($_SESSION['old_input']);

setFlashMessage('Connexion reussie. Bienvenue sur ton espace.');
redirect('/housing-cm/user/dashboard.php');
