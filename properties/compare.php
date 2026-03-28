<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$compareIds = comparePropertyIds();

$properties = [];

if ($compareIds) {
    $placeholders = implode(',', array_fill(0, count($compareIds), '?'));
    $statement = $pdo->prepare(
        'SELECT
            properties.id,
            properties.title,
            properties.property_type,
            properties.listing_type,
            properties.property_style,
            properties.price,
            properties.rooms,
            properties.bedrooms,
            properties.living_rooms,
            properties.bathrooms,
            properties.kitchens,
            properties.kitchen_type,
            properties.surface_area,
            properties.is_furnished,
            properties.has_water,
            properties.has_electricity,
            properties.has_parking,
            properties.has_fence,
            properties.security_level,
            properties.road_access,
            properties.status,
            property_images.image_path,
            locations.region_name,
            locations.city_name,
            locations.neighborhood_name
         FROM properties
         INNER JOIN locations ON properties.location_id = locations.id
         LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
         WHERE properties.id IN (' . $placeholders . ')
         GROUP BY properties.id'
    );
    $statement->execute($compareIds);
    $rows = $statement->fetchAll();

    $propertyMap = [];
    foreach ($rows as $row) {
        $propertyMap[(int) $row['id']] = $row;
    }

    foreach ($compareIds as $propertyId) {
        if (isset($propertyMap[$propertyId])) {
            $properties[] = $propertyMap[$propertyId];
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Comparaison</span>
        <h1>Comparer des logements</h1>
        <p>Observe les biens cote a cote pour mieux choisir selon le prix, la localisation et les equipements.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Aide a la decision</span>
                <h2>Une vue claire sur les differences essentielles</h2>
                <p>Ajoute jusqu a 3 logements depuis la recherche, les favoris ou la fiche detail pour les comparer sans revenir en arriere.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?php echo escape(url('/properties/search.php')); ?>">Retour a la recherche</a>
            </div>
        </div>
    </section>

    <section class="section">
        <?php if (!$properties): ?>
            <div class="feature-card">
                <h3>Aucun logement a comparer</h3>
                <p>Ajoute des biens a la comparaison depuis les cartes d annonces ou les favoris pour remplir ce tableau.</p>
            </div>
        <?php else: ?>
            <div class="compare-grid">
                <?php foreach ($properties as $property): ?>
                    <article class="detail-card compare-card">
                        <img
                            class="property-card-image compare-image"
                            src="<?php echo escape(url($property['image_path'] ?: '/assets/images/default-property.svg')); ?>"
                            alt="Image du logement <?php echo escape($property['title']); ?>"
                        >

                        <div class="property-card-top">
                            <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                            <span class="badge"><?php echo escape($property['property_type']); ?></span>
                            <span class="badge"><?php echo escape($property['status']); ?></span>
                        </div>

                        <h2><?php echo escape($property['title']); ?></h2>
                        <p class="property-location">
                            <?php echo escape($property['neighborhood_name']); ?>,
                            <?php echo escape($property['city_name']); ?>,
                            <?php echo escape($property['region_name']); ?>
                        </p>
                        <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>

                        <div class="compare-list">
                            <div class="detail-item"><strong>Style :</strong> <?php echo escape($property['property_style']); ?></div>
                            <div class="detail-item"><strong>Pieces :</strong> <?php echo (int) $property['rooms']; ?></div>
                            <div class="detail-item"><strong>Chambres :</strong> <?php echo (int) $property['bedrooms']; ?></div>
                            <div class="detail-item"><strong>Salons :</strong> <?php echo (int) $property['living_rooms']; ?></div>
                            <div class="detail-item"><strong>Douches :</strong> <?php echo (int) $property['bathrooms']; ?></div>
                            <div class="detail-item"><strong>Cuisines :</strong> <?php echo (int) $property['kitchens']; ?> (<?php echo escape($property['kitchen_type']); ?>)</div>
                            <div class="detail-item"><strong>Superficie :</strong> <?php echo $property['surface_area'] !== null ? escape($property['surface_area']) . ' m2' : 'Non precisee'; ?></div>
                            <div class="detail-item"><strong>Meuble :</strong> <?php echo $property['is_furnished'] ? 'Oui' : 'Non'; ?></div>
                            <div class="detail-item"><strong>Eau :</strong> <?php echo $property['has_water'] ? 'Oui' : 'Non'; ?></div>
                            <div class="detail-item"><strong>Electricite :</strong> <?php echo $property['has_electricity'] ? 'Oui' : 'Non'; ?></div>
                            <div class="detail-item"><strong>Parking :</strong> <?php echo $property['has_parking'] ? 'Oui' : 'Non'; ?></div>
                            <div class="detail-item"><strong>Cloture :</strong> <?php echo $property['has_fence'] ? 'Oui' : 'Non'; ?></div>
                            <div class="detail-item"><strong>Securite :</strong> <?php echo escape($property['security_level']); ?></div>
                            <div class="detail-item"><strong>Route :</strong> <?php echo escape($property['road_access']); ?></div>
                        </div>

                        <div class="card-actions">
                            <a class="btn btn-primary" href="<?php echo escape(url('/properties/details.php?id=' . (int) $property['id'])); ?>">Voir details</a>
                            <form action="<?php echo escape(url('/actions/toggle_compare_action.php')); ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                <input type="hidden" name="redirect_to" value="/housing-cm/properties/compare.php">
                                <button class="btn btn-secondary" type="submit">Retirer</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
