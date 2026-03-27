<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/app.php';

function escape(array|string|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function basePath(): string
{
    return rtrim(APP_BASE_PATH, '/') . '/';
}

function normalizeAppPath(string $path): string
{
    $normalized = trim($path);

    if ($normalized === '') {
        return basePath();
    }

    if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
        return $normalized;
    }

    if (str_starts_with($normalized, '/housing-cm/')) {
        $normalized = substr($normalized, strlen('/housing-cm/'));
    } elseif ($normalized === '/housing-cm') {
        $normalized = '';
    } elseif (str_starts_with($normalized, '/')) {
        $normalized = ltrim($normalized, '/');
    }

    return basePath() . ltrim($normalized, '/');
}

function url(string $path = ''): string
{
    return normalizeAppPath($path);
}

function redirect(string $path): void
{
    header('Location: ' . normalizeAppPath($path));
    exit;
}

function formatPrice(float|int|string $price): string
{
    return number_format((float) $price, 0, ',', ' ') . ' FCFA';
}

function setFlashMessage(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type,
    ];
}

function getFlashMessage(): ?array
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlashMessage('Veuillez vous connecter pour acceder a cette page.', 'error');
        redirect('/housing-cm/auth/login.php');
    }
}

function requireRole(array $roles): void
{
    requireLogin();

    $user = currentUser();

    if (!$user || !in_array($user['role'], $roles, true)) {
        setFlashMessage('Vous n avez pas l autorisation d acceder a cette page.', 'error');
        redirect('/housing-cm/user/dashboard.php');
    }
}

function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        redirect('/housing-cm/user/dashboard.php');
    }
}

function normalizeText(string $value): string
{
    return trim(preg_replace('/\s+/', ' ', $value));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        setFlashMessage('Session de formulaire invalide. Veuillez reessayer.', 'error');
        redirect('/housing-cm/user/dashboard.php');
    }
}
