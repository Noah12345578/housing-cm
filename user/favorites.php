<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        favorites.id AS favorite_id,
        properties.id,
        properties.title,
        properties.property_type,
        properties.listing_type,
        properties.price,
        properties.status,
        property_images.image_path,
        locations.region_name,
        locations.city_name,
        locations.neighborhood_name
     FROM favorites
     INNER JOIN properties ON favorites.property_id = properties.id
     INNER JOIN locations ON properties.location_id = locations.id
     LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
     WHERE favorites.user_id = :user_id
     ORDER BY favorites.created_at DESC'
);

$statement->execute(['user_id' => $user['id']]);
$favorites = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Mes favoris</h1>
        <p>Retrouve ici les logements que tu veux comparer ou revoir plus tard.</p>
    </section>

    <section class="section">
        <?php if (!$favorites): ?>
            <div class="feature-card">
                <h3>Aucun favori pour le moment</h3>
                <p>Ajoute des logements a tes favoris depuis la recherche ou la fiche detail.</p>
            </div>
        <?php else: ?>
            <div class="cards-3">
                <?php foreach ($favorites as $property): ?>
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

                            <div class="card-actions">
                                <a class="btn btn-primary" href="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">Voir details</a>

                                <form action="/housing-cm/actions/favorite_action.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                    <input type="hidden" name="redirect_to" value="/housing-cm/user/favorites.php">
                                    <button class="btn btn-secondary" type="submit">Retirer</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
