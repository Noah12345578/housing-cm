<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

verifyCsrfToken($_POST['csrf_token'] ?? null);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/my-properties.php');
}

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$user = currentUser();

if ($propertyId <= 0) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

$existingPropertyStatement = $pdo->prepare(
    'SELECT properties.id, properties.location_id
     FROM properties
     WHERE properties.id = :id AND properties.user_id = :user_id
     LIMIT 1'
);

$existingPropertyStatement->execute([
    'id' => $propertyId,
    'user_id' => $user['id'],
]);

$existingProperty = $existingPropertyStatement->fetch();

if (!$existingProperty) {
    setFlashMessage('Vous ne pouvez pas modifier cette annonce.', 'error');
    redirect('/housing-cm/properties/my-properties.php');
}

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
    setFlashMessage('Veuillez remplir tous les champs obligatoires.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

if (strlen($data['title']) < 5 || strlen($data['description']) < 20) {
    setFlashMessage('Le titre ou la description est trop court.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

if (!in_array($data['property_type'], $allowedPropertyTypes, true) ||
    !in_array($data['listing_type'], $allowedListingTypes, true) ||
    !in_array($data['property_style'], $allowedStyles, true) ||
    !in_array($data['kitchen_type'], $allowedKitchenTypes, true) ||
    !in_array($data['security_level'], $allowedSecurityLevels, true) ||
    !in_array($data['road_access'], $allowedRoadAccess, true)
) {
    setFlashMessage('Certaines valeurs du formulaire sont invalides.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
    setFlashMessage('Le prix doit etre valide.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

foreach (['rooms', 'bedrooms', 'living_rooms', 'bathrooms', 'kitchens'] as $field) {
    if (!is_numeric($data[$field]) || (int) $data[$field] < 0) {
        setFlashMessage('Les nombres de pieces et dependances doivent etre valides.', 'error');
        redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
    }
}

if ($data['surface_area'] !== '' && (!is_numeric($data['surface_area']) || (float) $data['surface_area'] < 0)) {
    setFlashMessage('La superficie doit etre valide.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

$existingImageCountStatement = $pdo->prepare(
    'SELECT COUNT(*) FROM property_images WHERE property_id = :property_id'
);

$existingImageCountStatement->execute(['property_id' => $propertyId]);
$existingImageCount = (int) $existingImageCountStatement->fetchColumn();

$newImagePaths = [];

try {
    $newImagePaths = storePropertyImages($_FILES['property_images'] ?? [], max(0, 6 - $existingImageCount));
} catch (RuntimeException $exception) {
    setFlashMessage($exception->getMessage(), 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

$pdo->beginTransaction();

try {
    $locationStatement = $pdo->prepare(
        'UPDATE locations
         SET region_name = :region_name,
             city_name = :city_name,
             district_name = :district_name,
             neighborhood_name = :neighborhood_name,
             specific_area = :specific_area
         WHERE id = :location_id'
    );

    $locationStatement->execute([
        'region_name' => $data['region_name'],
        'city_name' => $data['city_name'],
        'district_name' => $data['district_name'] !== '' ? $data['district_name'] : null,
        'neighborhood_name' => $data['neighborhood_name'],
        'specific_area' => $data['specific_area'] !== '' ? $data['specific_area'] : null,
        'location_id' => $existingProperty['location_id'],
    ]);

    $propertyStatement = $pdo->prepare(
        'UPDATE properties
         SET title = :title,
             description = :description,
             property_type = :property_type,
             listing_type = :listing_type,
             property_style = :property_style,
             price = :price,
             rooms = :rooms,
             bedrooms = :bedrooms,
             living_rooms = :living_rooms,
             bathrooms = :bathrooms,
             kitchens = :kitchens,
             kitchen_type = :kitchen_type,
             surface_area = :surface_area,
             is_furnished = :is_furnished,
             has_water = :has_water,
             has_electricity = :has_electricity,
             has_parking = :has_parking,
             has_fence = :has_fence,
             security_level = :security_level,
             road_access = :road_access,
             near_school = :near_school,
             near_market = :near_market,
             near_hospital = :near_hospital,
             near_university = :near_university,
             near_transport = :near_transport
         WHERE id = :property_id AND user_id = :user_id'
    );

    $propertyStatement->execute([
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
        'property_id' => $propertyId,
        'user_id' => $user['id'],
    ]);

    if ($newImagePaths) {
        $imageStatement = $pdo->prepare(
            'INSERT INTO property_images (property_id, image_path, is_main) VALUES (:property_id, :image_path, :is_main)'
        );

        foreach ($newImagePaths as $index => $imagePath) {
            $imageStatement->execute([
                'property_id' => $propertyId,
                'image_path' => $imagePath,
                'is_main' => ($existingImageCount === 0 && $index === 0) ? 1 : 0,
            ]);
        }
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    foreach ($newImagePaths as $imagePath) {
        deleteStoredFile($imagePath);
    }
    setFlashMessage('Une erreur est survenue pendant la modification.', 'error');
    redirect('/housing-cm/properties/edit.php?id=' . $propertyId);
}

unset($_SESSION['old_property_input']);

setFlashMessage('Annonce mise a jour avec succes.');
redirect('/housing-cm/properties/my-properties.php');
