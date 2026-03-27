<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$city = trim($_GET['city'] ?? '');
$neighborhood = trim($_GET['neighborhood'] ?? '');
$propertyType = trim($_GET['property_type'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$listingType = trim($_GET['listing_type'] ?? '');
$bedrooms = trim($_GET['bedrooms'] ?? '');

$allowedPropertyTypes = ['chambre', 'studio', 'appartement', 'maison', 'mini_cite', 'terrain', 'autre'];
$allowedListingTypes = ['location', 'vente'];
$favoritePropertyIds = [];

if (isLoggedIn()) {
    $favoriteStatement = $pdo->prepare('SELECT property_id FROM favorites WHERE user_id = :user_id');
    $favoriteStatement->execute(['user_id' => currentUser()['id']]);
    $favoritePropertyIds = array_map('intval', array_column($favoriteStatement->fetchAll(), 'property_id'));
}

$sql = 'SELECT
            properties.id,
            properties.title,
            properties.description,
            properties.property_type,
            properties.listing_type,
            properties.property_style,
            properties.price,
            properties.bedrooms,
            properties.bathrooms,
            properties.living_rooms,
            properties.has_water,
            properties.has_electricity,
            properties.has_parking,
            properties.status,
            property_images.image_path,
            locations.region_name,
            locations.city_name,
            locations.neighborhood_name
        FROM properties
        INNER JOIN locations ON properties.location_id = locations.id
        LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
        WHERE properties.status = :status';

$params = [
    'status' => 'disponible',
];

if ($city !== '') {
    $sql .= ' AND locations.city_name LIKE :city';
    $params['city'] = '%' . $city . '%';
}

if ($neighborhood !== '') {
    $sql .= ' AND locations.neighborhood_name LIKE :neighborhood';
    $params['neighborhood'] = '%' . $neighborhood . '%';
}

if ($propertyType !== '' && in_array($propertyType, $allowedPropertyTypes, true)) {
    $sql .= ' AND properties.property_type = :property_type';
    $params['property_type'] = $propertyType;
}

if ($listingType !== '' && in_array($listingType, $allowedListingTypes, true)) {
    $sql .= ' AND properties.listing_type = :listing_type';
    $params['listing_type'] = $listingType;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= ' AND properties.price <= :max_price';
    $params['max_price'] = (float) $maxPrice;
}

if ($bedrooms !== '' && is_numeric($bedrooms)) {
    $sql .= ' AND properties.bedrooms >= :bedrooms';
    $params['bedrooms'] = (int) $bedrooms;
}

$sql .= ' ORDER BY properties.created_at DESC';

$statement = $pdo->prepare($sql);
$statement->execute($params);
$properties = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Recherche de logements</h1>
        <p>Cette page accueillera les filtres avances et les resultats venant de MySQL.</p>
    </section>

    <section class="search-card">
        <form class="search-form" method="GET">
            <div class="grid-2">
                <div>
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?php echo escape($city); ?>">
                </div>

                <div>
                    <label for="neighborhood">Quartier</label>
                    <input type="text" id="neighborhood" name="neighborhood" value="<?php echo escape($neighborhood); ?>">
                </div>
            </div>

            <div class="grid-3">
                <div>
                    <label for="property_type">Type de logement</label>
                    <select id="property_type" name="property_type">
                        <option value="">Tous les types</option>
                        <option value="chambre" <?php echo $propertyType === 'chambre' ? 'selected' : ''; ?>>Chambre</option>
                        <option value="studio" <?php echo $propertyType === 'studio' ? 'selected' : ''; ?>>Studio</option>
                        <option value="appartement" <?php echo $propertyType === 'appartement' ? 'selected' : ''; ?>>Appartement</option>
                        <option value="maison" <?php echo $propertyType === 'maison' ? 'selected' : ''; ?>>Maison</option>
                        <option value="mini_cite" <?php echo $propertyType === 'mini_cite' ? 'selected' : ''; ?>>Mini-cite</option>
                        <option value="terrain" <?php echo $propertyType === 'terrain' ? 'selected' : ''; ?>>Terrain</option>
                        <option value="autre" <?php echo $propertyType === 'autre' ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="listing_type">Type d offre</label>
                    <select id="listing_type" name="listing_type">
                        <option value="">Location et vente</option>
                        <option value="location" <?php echo $listingType === 'location' ? 'selected' : ''; ?>>A louer</option>
                        <option value="vente" <?php echo $listingType === 'vente' ? 'selected' : ''; ?>>A vendre</option>
                    </select>
                </div>

                <div>
                    <label for="max_price">Budget maximum</label>
                    <input type="number" id="max_price" name="max_price" value="<?php echo escape($maxPrice); ?>">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="bedrooms">Nombre minimum de chambres</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo escape($bedrooms); ?>">
                </div>

                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">Filtrer</button>
                    <a class="btn btn-secondary" href="/housing-cm/properties/search.php">Reinitialiser</a>
                </div>
            </div>
        </form>
    </section>

    <section class="section">
        <h2 class="section-title">Resultats</h2>
        <p class="helper-text"><?php echo count($properties); ?> annonce(s) trouvee(s).</p>

        <?php if (!$properties): ?>
            <div class="feature-card">
                <h3>Aucun logement trouve</h3>
                <p>Essaie d elargir les filtres ou de publier une nouvelle annonce si tu es proprietaire ou agent.</p>
            </div>
        <?php else: ?>
            <div class="cards-3">
                <?php foreach ($properties as $property): ?>
                    <article class="property-card">
                        <img
                            class="property-card-image"
                            src="<?php echo escape($property['image_path'] ?: '/housing-cm/assets/images/default-property.svg'); ?>"
                            alt="Image du logement <?php echo escape($property['title']); ?>"
                        >
                        <div class="property-card-body">
                            <div class="property-card-top">
                                <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                                <span class="badge"><?php echo escape($property['status']); ?></span>
                            </div>

                            <h3><?php echo escape($property['title']); ?></h3>
                            <p class="property-location">
                                <?php echo escape($property['neighborhood_name']); ?>,
                                <?php echo escape($property['city_name']); ?>,
                                <?php echo escape($property['region_name']); ?>
                            </p>

                            <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>

                            <p class="property-summary">
                                <?php echo (int) $property['bedrooms']; ?> chambre(s),
                                <?php echo (int) $property['bathrooms']; ?> douche(s),
                                <?php echo (int) $property['living_rooms']; ?> salon(s)
                            </p>

                            <p class="property-features">
                                Eau : <?php echo $property['has_water'] ? 'Oui' : 'Non'; ?> |
                                Electricite : <?php echo $property['has_electricity'] ? 'Oui' : 'Non'; ?> |
                                Parking : <?php echo $property['has_parking'] ? 'Oui' : 'Non'; ?>
                            </p>

                            <div class="card-actions">
                                <a class="btn btn-primary" href="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">Voir details</a>

                                <?php if (isLoggedIn()): ?>
                                    <form action="/housing-cm/actions/favorite_action.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                        <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                        <input type="hidden" name="redirect_to" value="/housing-cm/properties/search.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . escape($_SERVER['QUERY_STRING']) : ''; ?>">
                                        <button class="btn btn-secondary" type="submit">
                                            <?php echo in_array((int) $property['id'], $favoritePropertyIds, true) ? 'Retirer favori' : 'Ajouter favori'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
