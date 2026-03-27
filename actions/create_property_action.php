<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['owner', 'agent']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/create.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$allowedPropertyTypes = ['chambre', 'studio', 'appartement', 'maison', 'mini_cite', 'terrain', 'autre'];
$allowedListingTypes = ['location', 'vente'];
$allowedStyles = ['moderne', 'classique'];
$allowedKitchenTypes = ['interne', 'externe'];
$allowedSecurityLevels = ['faible', 'moyen', 'bon', 'eleve'];
$allowedRoadAccess = ['mauvais', 'moyen', 'bon'];

$data = [
    'title' => normalizeText($_POST['title'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'property_type' => trim($_POST['property_type'] ?? ''),
    'listing_type' => trim($_POST['listing_type'] ?? ''),
    'property_style' => trim($_POST['property_style'] ?? 'classique'),
    'price' => trim($_POST['price'] ?? ''),
    'region_name' => normalizeText($_POST['region_name'] ?? ''),
    'city_name' => normalizeText($_POST['city_name'] ?? ''),
    'district_name' => normalizeText($_POST['district_name'] ?? ''),
    'neighborhood_name' => normalizeText($_POST['neighborhood_name'] ?? ''),
    'specific_area' => normalizeText($_POST['specific_area'] ?? ''),
    'rooms' => trim($_POST['rooms'] ?? '0'),
    'bedrooms' => trim($_POST['bedrooms'] ?? '0'),
    'living_rooms' => trim($_POST['living_rooms'] ?? '0'),
    'bathrooms' => trim($_POST['bathrooms'] ?? '0'),
    'kitchens' => trim($_POST['kitchens'] ?? '0'),
    'kitchen_type' => trim($_POST['kitchen_type'] ?? 'interne'),
    'surface_area' => trim($_POST['surface_area'] ?? ''),
    'security_level' => trim($_POST['security_level'] ?? 'moyen'),
    'road_access' => trim($_POST['road_access'] ?? 'moyen'),
    'is_furnished' => ($_POST['is_furnished'] ?? '0') === '1' ? 1 : 0,
    'has_water' => isset($_POST['has_water']) ? 1 : 0,
    'has_electricity' => isset($_POST['has_electricity']) ? 1 : 0,
    'has_parking' => isset($_POST['has_parking']) ? 1 : 0,
    'has_fence' => isset($_POST['has_fence']) ? 1 : 0,
    'near_school' => isset($_POST['near_school']) ? 1 : 0,
    'near_market' => isset($_POST['near_market']) ? 1 : 0,
    'near_hospital' => isset($_POST['near_hospital']) ? 1 : 0,
    'near_university' => isset($_POST['near_university']) ? 1 : 0,
    'near_transport' => isset($_POST['near_transport']) ? 1 : 0,
];

$_SESSION['old_property_input'] = $data;

if (
    $data['title'] === '' ||
    $data['description'] === '' ||
    $data['property_type'] === '' ||
    $data['listing_type'] === '' ||
    $data['price'] === '' ||
    $data['region_name'] === '' ||
    $data['city_name'] === '' ||
    $data['neighborhood_name'] === ''
) {
    setFlashMessage('Veuillez remplir tous les champs obligatoires de l annonce.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (strlen($data['title']) < 5) {
    setFlashMessage('Le titre de l annonce doit contenir au moins 5 caracteres.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (strlen($data['description']) < 20) {
    setFlashMessage('La description doit contenir au moins 20 caracteres.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['property_type'], $allowedPropertyTypes, true)) {
    setFlashMessage('Le type de logement est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['listing_type'], $allowedListingTypes, true)) {
    setFlashMessage('Le statut de l annonce est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['property_style'], $allowedStyles, true)) {
    setFlashMessage('Le style du logement est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['kitchen_type'], $allowedKitchenTypes, true)) {
    setFlashMessage('Le type de cuisine est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['security_level'], $allowedSecurityLevels, true)) {
    setFlashMessage('Le niveau de securite est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!in_array($data['road_access'], $allowedRoadAccess, true)) {
    setFlashMessage('Le niveau d acces a la route est invalide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
    setFlashMessage('Le prix doit etre un nombre valide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

$numericFields = ['rooms', 'bedrooms', 'living_rooms', 'bathrooms', 'kitchens'];

foreach ($numericFields as $field) {
    if (!is_numeric($data[$field]) || (int) $data[$field] < 0) {
        setFlashMessage('Les nombres de pieces, chambres, salons, douches et cuisines doivent etre valides.', 'error');
        redirect('/housing-cm/properties/create.php');
    }
}

if ($data['surface_area'] !== '' && (!is_numeric($data['surface_area']) || (float) $data['surface_area'] < 0)) {
    setFlashMessage('La superficie doit etre un nombre valide.', 'error');
    redirect('/housing-cm/properties/create.php');
}

$uploadedImage = $_FILES['main_image'] ?? null;
$imageRelativePath = null;

if ($uploadedImage && $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($uploadedImage['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('Erreur pendant l envoi de l image.', 'error');
        redirect('/housing-cm/properties/create.php');
    }

    if ($uploadedImage['size'] > 2 * 1024 * 1024) {
        setFlashMessage('L image est trop lourde. Taille maximale : 2 Mo.', 'error');
        redirect('/housing-cm/properties/create.php');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $uploadedImage['tmp_name']);
    finfo_close($fileInfo);

    if (!isset($allowedMimeTypes[$mimeType])) {
        setFlashMessage('Format d image non autorise. Utilise JPG, PNG ou WEBP.', 'error');
        redirect('/housing-cm/properties/create.php');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $fileName = 'property_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $uploadDirectory = dirname(__DIR__) . '/uploads/properties/';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($uploadedImage['tmp_name'], $destination)) {
        setFlashMessage('Impossible d enregistrer l image du logement.', 'error');
        redirect('/housing-cm/properties/create.php');
    }

    $imageRelativePath = '/housing-cm/uploads/properties/' . $fileName;
}

$user = currentUser();

$pdo->beginTransaction();

try {
    $locationStatement = $pdo->prepare(
        'INSERT INTO locations (region_name, city_name, district_name, neighborhood_name, specific_area)
         VALUES (:region_name, :city_name, :district_name, :neighborhood_name, :specific_area)'
    );

    $locationStatement->execute([
        'region_name' => $data['region_name'],
        'city_name' => $data['city_name'],
        'district_name' => $data['district_name'] !== '' ? $data['district_name'] : null,
        'neighborhood_name' => $data['neighborhood_name'],
        'specific_area' => $data['specific_area'] !== '' ? $data['specific_area'] : null,
    ]);

    $locationId = (int) $pdo->lastInsertId();

    $propertyStatement = $pdo->prepare(
        'INSERT INTO properties (
            user_id, location_id, title, description, property_type, listing_type, property_style, price,
            rooms, bedrooms, living_rooms, bathrooms, kitchens, kitchen_type, surface_area, is_furnished,
            has_water, has_electricity, has_parking, has_fence, security_level, road_access, near_school,
            near_market, near_hospital, near_university, near_transport
        ) VALUES (
            :user_id, :location_id, :title, :description, :property_type, :listing_type, :property_style, :price,
            :rooms, :bedrooms, :living_rooms, :bathrooms, :kitchens, :kitchen_type, :surface_area, :is_furnished,
            :has_water, :has_electricity, :has_parking, :has_fence, :security_level, :road_access, :near_school,
            :near_market, :near_hospital, :near_university, :near_transport
        )'
    );

    $propertyStatement->execute([
        'user_id' => $user['id'],
        'location_id' => $locationId,
        'title' => $data['title'],
        'description' => $data['description'],
        'property_type' => $data['property_type'],
        'listing_type' => $data['listing_type'],
        'property_style' => $data['property_style'],
        'price' => (float) $data['price'],
        'rooms' => (int) $data['rooms'],
        'bedrooms' => (int) $data['bedrooms'],
        'living_rooms' => (int) $data['living_rooms'],
        'bathrooms' => (int) $data['bathrooms'],
        'kitchens' => (int) $data['kitchens'],
        'kitchen_type' => $data['kitchen_type'],
        'surface_area' => $data['surface_area'] !== '' ? (float) $data['surface_area'] : null,
        'is_furnished' => $data['is_furnished'],
        'has_water' => $data['has_water'],
        'has_electricity' => $data['has_electricity'],
        'has_parking' => $data['has_parking'],
        'has_fence' => $data['has_fence'],
        'security_level' => $data['security_level'],
        'road_access' => $data['road_access'],
        'near_school' => $data['near_school'],
        'near_market' => $data['near_market'],
        'near_hospital' => $data['near_hospital'],
        'near_university' => $data['near_university'],
        'near_transport' => $data['near_transport'],
    ]);

    $propertyId = (int) $pdo->lastInsertId();

    if ($imageRelativePath !== null) {
        $imageStatement = $pdo->prepare(
            'INSERT INTO property_images (property_id, image_path, is_main) VALUES (:property_id, :image_path, :is_main)'
        );

        $imageStatement->execute([
            'property_id' => $propertyId,
            'image_path' => $imageRelativePath,
            'is_main' => 1,
        ]);
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    setFlashMessage('Une erreur est survenue pendant l enregistrement de l annonce.', 'error');
    redirect('/housing-cm/properties/create.php');
}

unset($_SESSION['old_property_input']);

setFlashMessage('Annonce enregistree avec succes.');
redirect('/housing-cm/properties/my-properties.php');
