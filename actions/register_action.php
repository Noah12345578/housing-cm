<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/auth/register.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$fullName = normalizeText($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = normalizeText($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? 'client');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$_SESSION['old_input'] = [
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'role' => $role,
];

if ($fullName === '' || $email === '' || $phone === '' || $role === '' || $password === '' || $confirmPassword === '') {
    setFlashMessage('Tous les champs sont obligatoires.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('Veuillez saisir une adresse e-mail valide.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if (strlen($fullName) < 3) {
    setFlashMessage('Le nom complet doit contenir au moins 3 caracteres.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if (strlen($phone) < 6) {
    setFlashMessage('Le numero de telephone semble trop court.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if (!in_array($role, ['client', 'owner', 'agent'], true)) {
    setFlashMessage('Le role choisi est invalide.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if (strlen($password) < 6) {
    setFlashMessage('Le mot de passe doit contenir au moins 6 caracteres.', 'error');
    redirect('/housing-cm/auth/register.php');
}

if ($password !== $confirmPassword) {
    setFlashMessage('Les deux mots de passe ne correspondent pas.', 'error');
    redirect('/housing-cm/auth/register.php');
}

$checkStatement = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$checkStatement->execute(['email' => $email]);

if ($checkStatement->fetch()) {
    setFlashMessage('Cette adresse e-mail est deja utilisee.', 'error');
    redirect('/housing-cm/auth/register.php');
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$insertStatement = $pdo->prepare(
    'INSERT INTO users (full_name, email, phone, password, role) VALUES (:full_name, :email, :phone, :password, :role)'
);

$insertStatement->execute([
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'password' => $hashedPassword,
    'role' => $role,
]);

unset($_SESSION['old_input']);

setFlashMessage('Inscription reussie. Tu peux maintenant te connecter.');
redirect('/housing-cm/auth/login.php');
