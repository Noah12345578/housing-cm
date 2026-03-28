<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$city = trim($_GET['city'] ?? '');
$neighborhood = trim($_GET['neighborhood'] ?? '');
$propertyType = trim($_GET['property_type'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$listingType = trim($_GET['listing_type'] ?? '');
$bedrooms = trim($_GET['bedrooms'] ?? '');
$verifiedOnly = isset($_GET['verified_only']) && $_GET['verified_only'] === '1';
$sort = trim($_GET['sort'] ?? 'recent');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$allowedPropertyTypes = ['chambre', 'studio', 'appartement', 'maison', 'mini_cite', 'terrain', 'autre'];
$allowedListingTypes = ['location', 'vente'];
$allowedSorts = [
    'recent' => 'properties.created_at DESC',
    'price_asc' => 'properties.price ASC',
    'price_desc' => 'properties.price DESC',
    'bedrooms_desc' => 'properties.bedrooms DESC, properties.created_at DESC',
];

if (!isset($allowedSorts[$sort])) {
    $sort = 'recent';
}

$favoritePropertyIds = [];

if (isLoggedIn()) {
    $favoriteStatement = $pdo->prepare('SELECT property_id FROM favorites WHERE user_id = :user_id');
    $favoriteStatement->execute(['user_id' => currentUser()['id']]);
    $favoritePropertyIds = array_map('intval', array_column($favoriteStatement->fetchAll(), 'property_id'));
}

$baseSql = ' FROM properties
             INNER JOIN locations ON properties.location_id = locations.id
             LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
             WHERE properties.status = :status';

$params = [
    'status' => 'disponible',
];

if ($city !== '') {
    $baseSql .= ' AND locations.city_name LIKE :city';
    $params['city'] = '%' . $city . '%';
}

if ($neighborhood !== '') {
    $baseSql .= ' AND locations.neighborhood_name LIKE :neighborhood';
    $params['neighborhood'] = '%' . $neighborhood . '%';
}

if ($propertyType !== '' && in_array($propertyType, $allowedPropertyTypes, true)) {
    $baseSql .= ' AND properties.property_type = :property_type';
    $params['property_type'] = $propertyType;
}

if ($listingType !== '' && in_array($listingType, $allowedListingTypes, true)) {
    $baseSql .= ' AND properties.listing_type = :listing_type';
    $params['listing_type'] = $listingType;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $baseSql .= ' AND properties.price <= :max_price';
    $params['max_price'] = (float) $maxPrice;
}

if ($bedrooms !== '' && is_numeric($bedrooms)) {
    $baseSql .= ' AND properties.bedrooms >= :bedrooms';
    $params['bedrooms'] = (int) $bedrooms;
}

if ($verifiedOnly) {
    $baseSql .= ' AND properties.is_verified = :is_verified';
    $params['is_verified'] = 1;
}

$hasMeaningfulSearch = (
    $city !== '' ||
    $neighborhood !== '' ||
    $propertyType !== '' ||
    $maxPrice !== '' ||
    $listingType !== '' ||
    $bedrooms !== '' ||
    $verifiedOnly
);

if (isLoggedIn() && $hasMeaningfulSearch && $page === 1) {
    $historyPayload = [
        'user_id' => currentUser()['id'],
        'keywords' => $neighborhood !== '' ? $neighborhood : null,
        'region_name' => null,
        'city_name' => $city !== '' ? $city : null,
        'min_price' => null,
        'max_price' => ($maxPrice !== '' && is_numeric($maxPrice)) ? (float) $maxPrice : null,
        'property_type' => $propertyType !== '' ? $propertyType : null,
    ];

    $duplicateStatement = $pdo->prepare(
        'SELECT id
         FROM search_history
         WHERE user_id = :user_id
           AND ((keywords IS NULL AND :keywords IS NULL) OR keywords = :keywords)
           AND ((city_name IS NULL AND :city_name IS NULL) OR city_name = :city_name)
           AND ((max_price IS NULL AND :max_price IS NULL) OR max_price = :max_price)
           AND ((property_type IS NULL AND :property_type IS NULL) OR property_type = :property_type)
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $duplicateStatement->execute($historyPayload);
    $duplicateHistory = $duplicateStatement->fetchColumn();

    if (!$duplicateHistory) {
        $historyStatement = $pdo->prepare(
            'INSERT INTO search_history (user_id, keywords, region_name, city_name, min_price, max_price, property_type)
             VALUES (:user_id, :keywords, :region_name, :city_name, :min_price, :max_price, :property_type)'
        );
        $historyStatement->execute($historyPayload);
    }
}

$countStatement = $pdo->prepare('SELECT COUNT(DISTINCT properties.id)' . $baseSql);
$countStatement->execute($params);
$totalProperties = (int) $countStatement->fetchColumn();
$totalPages = max(1, (int) ceil($totalProperties / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
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
            properties.has_fence,
            properties.security_level,
            properties.road_access,
            properties.near_school,
            properties.near_market,
            properties.near_hospital,
            properties.near_university,
            properties.near_transport,
            properties.is_verified,
            properties.status,
            properties.created_at,
            properties.updated_at,
            property_images.image_path,
            (
                SELECT COUNT(*)
                FROM property_images AS gallery_images
                WHERE gallery_images.property_id = properties.id
            ) AS image_count,
            locations.region_name,
            locations.city_name,
            locations.neighborhood_name' .
        $baseSql .
        ' GROUP BY properties.id
          ORDER BY ' . $allowedSorts[$sort] .
        ' LIMIT :limit OFFSET :offset';

$statement = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $statement->bindValue(':' . $key, $value);
}

$statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
$statement->execute();
$properties = $statement->fetchAll();

$queryParams = $_GET;
unset($queryParams['page']);
$paginationBase = http_build_query($queryParams);
$paginationPrefix = $paginationBase !== '' ? $paginationBase . '&' : '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Recherche immobiliere</span>
        <h1>Recherche de logements</h1>
        <p>Trouve plus vite un logement grace a des filtres utiles, un tri pertinent et des resultats pagines plus faciles a comparer.</p>
    </section>

    <section class="search-shell">
        <aside class="search-sidebar">
            <div class="search-panel">
                <div class="search-panel-header">
                    <span class="eyebrow">Filtres</span>
                    <h2>Affiner la recherche</h2>
                    <p>Selectionne les criteres qui comptent le plus pour toi.</p>
                </div>

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

                    <div class="grid-2">
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
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="max_price">Budget maximum</label>
                            <input type="number" id="max_price" name="max_price" value="<?php echo escape($maxPrice); ?>">
                        </div>

                        <div>
                            <label for="bedrooms">Nombre minimum de chambres</label>
                            <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo escape($bedrooms); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="sort">Trier par</label>
                        <select id="sort" name="sort">
                            <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Plus recent</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Prix decroissant</option>
                            <option value="bedrooms_desc" <?php echo $sort === 'bedrooms_desc' ? 'selected' : ''; ?>>Plus de chambres</option>
                        </select>
                    </div>

                    <label class="checkbox-option" for="verified_only">
                        <input
                            type="checkbox"
                            id="verified_only"
                            name="verified_only"
                            value="1"
                            <?php echo $verifiedOnly ? 'checked' : ''; ?>
                        >
                        <span>Afficher uniquement les annonces verifiees</span>
                    </label>

                    <div class="filter-actions">
                        <button class="btn btn-primary" type="submit">Filtrer les annonces</button>
                        <a class="btn btn-secondary" href="<?php echo escape(url('/properties/search.php')); ?>">Reinitialiser</a>
                    </div>
                </form>
            </div>
        </aside>

        <section class="search-results">
            <div class="results-head">
                <div>
                    <span class="eyebrow">Resultats</span>
                    <h2 class="section-title">Logements disponibles</h2>
                    <p class="helper-text">
                        <?php echo $totalProperties; ?> annonce(s) trouvee(s) -
                        page <?php echo $page; ?> sur <?php echo $totalPages; ?>.
                    </p>
                    <?php if ($verifiedOnly): ?>
                        <p class="helper-text">Filtre actif : annonces verifiees uniquement.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$properties): ?>
                <div class="feature-card">
                    <h3>Aucun logement trouve</h3>
                    <p>Essaie d elargir les filtres ou de changer le tri pour faire ressortir d autres biens.</p>
                </div>
            <?php else: ?>
                <div class="cards-3">
                    <?php foreach ($properties as $property): ?>
                        <?php
                        $reliability = propertyReliabilityData($property);
                        $practicalInsights = propertyPracticalInsights($property);
                        ?>
                        <article class="property-card property-card-rich">
                            <img
                                class="property-card-image"
                                src="<?php echo escape(url($property['image_path'] ?: '/assets/images/default-property.svg')); ?>"
                                alt="Image du logement <?php echo escape($property['title']); ?>"
                            >
                            <div class="property-card-body">
                                <div class="property-card-top">
                                    <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                                    <span class="badge"><?php echo escape($property['status']); ?></span>
                                    <span class="badge"><?php echo escape($property['property_type']); ?></span>
                                    <?php if (!empty($property['is_verified'])): ?>
                                        <span class="badge badge-verified">Annonce verifiee</span>
                                    <?php endif; ?>
                                </div>

                                <div class="trust-score trust-score-<?php echo escape($reliability['tone']); ?>">
                                    <div>
                                        <span class="trust-score-label">Score de fiabilite</span>
                                        <strong><?php echo (int) $reliability['score']; ?>/100</strong>
                                    </div>
                                    <span class="trust-score-pill"><?php echo escape($reliability['label']); ?></span>
                                </div>

                                <h3><?php echo escape($property['title']); ?></h3>
                                <p class="property-location">
                                    <?php echo escape($property['neighborhood_name']); ?>,
                                    <?php echo escape($property['city_name']); ?>,
                                    <?php echo escape($property['region_name']); ?>
                                </p>

                                <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>

                                <div class="metric-row">
                                    <span><?php echo (int) $property['bedrooms']; ?> chambre(s)</span>
                                    <span><?php echo (int) $property['bathrooms']; ?> douche(s)</span>
                                    <span><?php echo (int) $property['living_rooms']; ?> salon(s)</span>
                                </div>

                                <p class="property-features">
                                    Eau : <?php echo $property['has_water'] ? 'Oui' : 'Non'; ?> |
                                    Electricite : <?php echo $property['has_electricity'] ? 'Oui' : 'Non'; ?> |
                                    Parking : <?php echo $property['has_parking'] ? 'Oui' : 'Non'; ?>
                                </p>

                                <div class="trust-hints">
                                    <?php foreach ($reliability['strengths'] as $strength): ?>
                                        <span class="trust-chip"><?php echo escape($strength); ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($practicalInsights['cautions']): ?>
                                    <p class="helper-text compact-helper">
                                        A verifier : <?php echo escape($practicalInsights['cautions'][0]); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="card-actions">
                                    <a class="btn btn-primary" href="<?php echo escape(url('/properties/details.php?id=' . (int) $property['id'])); ?>">Voir details</a>

                                    <form action="<?php echo escape(url('/actions/toggle_compare_action.php')); ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                        <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                        <input type="hidden" name="redirect_to" value="<?php echo escape('/properties/search.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>">
                                        <button class="btn btn-secondary" type="submit">
                                            <?php echo isCompared((int) $property['id']) ? 'Retirer comparaison' : 'Comparer'; ?>
                                        </button>
                                    </form>

                                    <?php if (isLoggedIn()): ?>
                                        <form action="<?php echo escape(url('/actions/favorite_action.php')); ?>" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                            <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                            <input type="hidden" name="redirect_to" value="<?php echo escape('/properties/search.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>">
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

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a class="pagination-link" href="<?php echo escape(url('/properties/search.php?' . $paginationPrefix . 'page=' . ($page - 1))); ?>">Precedent</a>
                        <?php endif; ?>

                        <?php for ($index = 1; $index <= $totalPages; $index++): ?>
                            <a
                                class="pagination-link <?php echo $index === $page ? 'pagination-link-active' : ''; ?>"
                                href="<?php echo escape(url('/properties/search.php?' . $paginationPrefix . 'page=' . $index)); ?>"
                            >
                                <?php echo $index; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a class="pagination-link" href="<?php echo escape(url('/properties/search.php?' . $paginationPrefix . 'page=' . ($page + 1))); ?>">Suivant</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
