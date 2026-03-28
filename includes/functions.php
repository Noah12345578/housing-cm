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

function storeProfileImage(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Une erreur est survenue pendant l envoi de la photo de profil.');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('La photo de profil doit faire moins de 2 Mo.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Formats autorises pour la photo : JPG, PNG et WEBP.');
    }

    $uploadDirectory = dirname(__DIR__) . '/uploads/profiles/';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Impossible de preparer le dossier des photos de profil.');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $fileName = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Impossible d enregistrer la photo de profil.');
    }

    return '/housing-cm/uploads/profiles/' . $fileName;
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

function humanizeSlug(string $value): string
{
    $normalized = str_replace(['_', '-'], ' ', strtolower(trim($value)));

    return ucfirst($normalized);
}

function propertyReliabilityData(array $property): array
{
    $score = 0;
    $strengths = [];
    $warnings = [];

    $title = trim((string) ($property['title'] ?? ''));
    $description = trim((string) ($property['description'] ?? ''));
    $price = (float) ($property['price'] ?? 0);
    $region = trim((string) ($property['region_name'] ?? ''));
    $city = trim((string) ($property['city_name'] ?? ''));
    $neighborhood = trim((string) ($property['neighborhood_name'] ?? ''));
    $specificArea = trim((string) ($property['specific_area'] ?? ''));
    $imageCount = max(0, (int) ($property['image_count'] ?? 0));
    $rooms = (int) ($property['rooms'] ?? 0);
    $bedrooms = (int) ($property['bedrooms'] ?? 0);
    $bathrooms = (int) ($property['bathrooms'] ?? 0);
    $hasWater = !empty($property['has_water']);
    $hasElectricity = !empty($property['has_electricity']);
    $hasParking = !empty($property['has_parking']);
    $hasFence = !empty($property['has_fence']);
    $nearSchool = !empty($property['near_school']);
    $nearMarket = !empty($property['near_market']);
    $nearHospital = !empty($property['near_hospital']);
    $nearUniversity = !empty($property['near_university']);
    $nearTransport = !empty($property['near_transport']);
    $securityLevel = strtolower((string) ($property['security_level'] ?? ''));
    $roadAccess = strtolower((string) ($property['road_access'] ?? ''));
    $phone = trim((string) ($property['phone'] ?? ''));
    $isVerified = !empty($property['is_verified']);
    $updatedAt = $property['updated_at'] ?? $property['created_at'] ?? null;

    if (mb_strlen($title) >= 12) {
        $score += 8;
        $strengths[] = 'Titre assez clair';
    } else {
        $warnings[] = 'Titre peu explicite';
    }

    $descriptionLength = mb_strlen($description);
    if ($descriptionLength >= 180) {
        $score += 18;
        $strengths[] = 'Description bien detaillee';
    } elseif ($descriptionLength >= 90) {
        $score += 12;
        $strengths[] = 'Description correcte';
    } elseif ($descriptionLength >= 45) {
        $score += 6;
        $warnings[] = 'Description encore courte';
    } else {
        $warnings[] = 'Description trop courte';
    }

    if ($price > 0) {
        $score += 8;
        $strengths[] = 'Prix clairement affiche';
    } else {
        $warnings[] = 'Prix non renseigne';
    }

    $locationScore = 0;
    if ($region !== '') {
        $locationScore += 4;
    }

    if ($city !== '') {
        $locationScore += 4;
    }

    if ($neighborhood !== '') {
        $locationScore += 4;
    }

    if ($specificArea !== '') {
        $locationScore += 2;
    }

    $score += min($locationScore, 12);

    if ($locationScore >= 10) {
        $strengths[] = 'Localisation bien precisee';
    } else {
        $warnings[] = 'Localisation encore a preciser';
    }

    if ($imageCount >= 4) {
        $score += 18;
        $strengths[] = 'Galerie photo rassurante';
    } elseif ($imageCount >= 2) {
        $score += 12;
        $strengths[] = 'Plusieurs photos disponibles';
    } elseif ($imageCount === 1) {
        $score += 6;
        $warnings[] = 'Une seule photo disponible';
    } else {
        $warnings[] = 'Aucune photo disponible';
    }

    $layoutScore = 0;
    if ($rooms > 0) {
        $layoutScore += 3;
    }

    if ($bedrooms > 0) {
        $layoutScore += 4;
    }

    if ($bathrooms > 0) {
        $layoutScore += 3;
    }

    $score += $layoutScore;

    if ($layoutScore >= 7) {
        $strengths[] = 'Pieces principales bien renseignees';
    } else {
        $warnings[] = 'Caracteristiques interieures incompletes';
    }

    $utilityScore = 0;
    if ($hasWater) {
        $utilityScore += 4;
    }

    if ($hasElectricity) {
        $utilityScore += 4;
    }

    $score += $utilityScore;

    if ($utilityScore === 8) {
        $strengths[] = 'Eau et electricite indiquees';
    } else {
        $warnings[] = 'Informations reseaux a confirmer';
    }

    $trustInfrastructureScore = 0;
    if ($roadAccess !== '') {
        $trustInfrastructureScore += in_array($roadAccess, ['bon', 'moyen'], true) ? 4 : 2;
    }

    if ($securityLevel !== '') {
        $trustInfrastructureScore += in_array($securityLevel, ['bon', 'eleve', 'moyen'], true) ? 4 : 2;
    }

    $score += min($trustInfrastructureScore, 8);

    if ($trustInfrastructureScore >= 7) {
        $strengths[] = 'Acces et securite rassurants';
    } else {
        $warnings[] = 'Acces ou securite peu detailles';
    }

    $proximityCount = 0;
    foreach ([$nearSchool, $nearMarket, $nearHospital, $nearUniversity, $nearTransport] as $proximityFlag) {
        if ($proximityFlag) {
            $proximityCount++;
        }
    }

    if ($proximityCount >= 3) {
        $score += 10;
        $strengths[] = 'Vie pratique bien documentee';
    } elseif ($proximityCount >= 1) {
        $score += 6;
        $strengths[] = 'Quelques points utiles autour du bien';
    } else {
        $warnings[] = 'Environnement du quartier peu documente';
    }

    if ($phone !== '') {
        $score += 6;
        $strengths[] = 'Responsable joignable';
    } else {
        $warnings[] = 'Contact a completer';
    }

    if ($isVerified) {
        $score += 10;
        $strengths[] = 'Annonce verifiee par la plateforme';
    }

    if ($hasParking || $hasFence) {
        $score += 4;
        $strengths[] = 'Equipements complementaires indiques';
    }

    if ($updatedAt) {
        try {
            $updatedDate = new DateTime((string) $updatedAt);
            $daysSinceUpdate = (int) $updatedDate->diff(new DateTime())->days;

            if ($daysSinceUpdate <= 14) {
                $score += 8;
                $strengths[] = 'Annonce mise a jour recemment';
            } elseif ($daysSinceUpdate <= 45) {
                $score += 4;
            } else {
                $warnings[] = 'Disponibilite a reconfirmer';
            }
        } catch (Exception) {
            // Ignore invalid date formats and keep neutral score.
        }
    }

    $score = max(0, min(100, $score));

    if ($score >= 80) {
        $label = 'Tres fiable';
        $tone = 'excellent';
        $summary = 'Annonce tres complete, avec des informations solides pour decider vite.';
    } elseif ($score >= 65) {
        $label = 'Fiable';
        $tone = 'good';
        $summary = 'Annonce globalement rassurante, avec peu de zones d incertitude.';
    } elseif ($score >= 45) {
        $label = 'A verifier';
        $tone = 'medium';
        $summary = 'Annonce utile, mais quelques points meritent une verification avant visite.';
    } else {
        $label = 'Informations insuffisantes';
        $tone = 'low';
        $summary = 'Annonce encore trop incomplete pour inspirer pleinement confiance.';
    }

    return [
        'score' => $score,
        'label' => $label,
        'tone' => $tone,
        'summary' => $summary,
        'strengths' => array_values(array_unique(array_slice($strengths, 0, 4))),
        'warnings' => array_values(array_unique(array_slice($warnings, 0, 3))),
        'image_count' => $imageCount,
    ];
}

function propertyPracticalInsights(array $property): array
{
    $highlights = [];
    $cautions = [];

    if (!empty($property['has_water'])) {
        $highlights[] = 'Eau indiquee sur place';
    } else {
        $cautions[] = 'Disponibilite de l eau a confirmer';
    }

    if (!empty($property['has_electricity'])) {
        $highlights[] = 'Electricite mentionnee';
    } else {
        $cautions[] = 'Electricite a verifier';
    }

    if (!empty($property['near_transport'])) {
        $highlights[] = 'Transport proche';
    } else {
        $cautions[] = 'Acces transport non precise';
    }

    if (!empty($property['near_market'])) {
        $highlights[] = 'Marche a proximite';
    }

    if (!empty($property['near_school'])) {
        $highlights[] = 'Ecole proche';
    }

    if (!empty($property['near_hospital'])) {
        $highlights[] = 'Hopital proche';
    }

    if (!empty($property['near_university'])) {
        $highlights[] = 'Universite proche';
    }

    $roadAccess = strtolower((string) ($property['road_access'] ?? ''));
    if ($roadAccess !== '') {
        if ($roadAccess === 'bon') {
            $highlights[] = 'Acces route juge bon';
        } elseif ($roadAccess === 'moyen') {
            $cautions[] = 'Acces route moyen';
        } else {
            $cautions[] = 'Acces route delicat';
        }
    }

    $securityLevel = strtolower((string) ($property['security_level'] ?? ''));
    if ($securityLevel !== '') {
        if (in_array($securityLevel, ['bon', 'eleve'], true)) {
            $highlights[] = 'Niveau de securite rassurant';
        } elseif ($securityLevel === 'moyen') {
            $cautions[] = 'Securite moyenne';
        } else {
            $cautions[] = 'Securite faible annoncee';
        }
    }

    return [
        'highlights' => array_values(array_unique(array_slice($highlights, 0, 5))),
        'cautions' => array_values(array_unique(array_slice($cautions, 0, 4))),
    ];
}

function profileImageUrl(?string $path): string
{
    if ($path && trim($path) !== '') {
        return url($path);
    }

    return url('/assets/images/default-user.svg');
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
