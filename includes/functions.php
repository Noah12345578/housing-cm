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

function normalizeUploadedFiles(array $files): array
{
    if (!isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    $total = count($files['name']);

    for ($index = 0; $index < $total; $index++) {
        $normalized[] = [
            'name' => $files['name'][$index] ?? '',
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

function storePropertyImages(array $files, int $maxFiles = 6): array
{
    $normalizedFiles = array_values(array_filter(
        normalizeUploadedFiles($files),
        static fn (array $file): bool => ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ));

    if (!$normalizedFiles) {
        return [];
    }

    if ($maxFiles <= 0 || count($normalizedFiles) > $maxFiles) {
        throw new RuntimeException('Tu peux ajouter au maximum 6 images par annonce.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $uploadDirectory = dirname(__DIR__) . '/uploads/properties/';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Impossible de preparer le dossier des images.');
    }

    $storedPaths = [];

    foreach ($normalizedFiles as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Une erreur est survenue pendant l envoi d une image.');
        }

        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new RuntimeException('Chaque image doit faire moins de 2 Mo.');
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new RuntimeException('Formats autorises : JPG, PNG et WEBP uniquement.');
        }

        $extension = $allowedMimeTypes[$mimeType];
        $fileName = 'property_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDirectory . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Impossible d enregistrer une image sur le serveur.');
        }

        $storedPaths[] = '/housing-cm/uploads/properties/' . $fileName;
    }

    return $storedPaths;
}

function deleteStoredFile(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }

    $normalizedPath = str_replace('\\', '/', $relativePath);

    if (str_starts_with($normalizedPath, '/housing-cm/')) {
        $normalizedPath = substr($normalizedPath, strlen('/housing-cm/'));
    } elseif (str_starts_with($normalizedPath, '/')) {
        $normalizedPath = ltrim($normalizedPath, '/');
    }

    $absolutePath = dirname(__DIR__) . '/' . $normalizedPath;

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function comparePropertyIds(): array
{
    $ids = $_SESSION['compare_properties'] ?? [];

    if (!is_array($ids)) {
        return [];
    }

    return array_values(array_unique(array_map('intval', $ids)));
}

function isCompared(int $propertyId): bool
{
    return in_array($propertyId, comparePropertyIds(), true);
}

function renderErrorPage(string $title, string $message, int $statusCode = 404, array $actions = []): void
{
    http_response_code($statusCode);

    $defaultActions = [
        [
            'label' => 'Retour a l accueil',
            'url' => url('/index.php'),
            'class' => 'btn btn-primary',
        ],
    ];

    $errorPage = [
        'title' => $title,
        'message' => $message,
        'status_code' => $statusCode,
        'actions' => $actions ?: $defaultActions,
    ];

    include __DIR__ . '/error-page.php';
    exit;
}
