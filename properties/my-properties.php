<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        properties.id,
        properties.title,
        properties.property_type,
        properties.listing_type,
        properties.price,
        properties.status,
        properties.created_at,
        property_images.image_path,
        locations.region_name,
        locations.city_name,
        locations.neighborhood_name
     FROM properties
     INNER JOIN locations ON properties.location_id = locations.id
     LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
     WHERE properties.user_id = :user_id
     ORDER BY properties.created_at DESC'
);

$statement->execute(['user_id' => $user['id']]);
$properties = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Mes annonces</h1>
        <p>Retrouve ici tous les logements que tu as deja publies.</p>
    </section>

    <section class="section">
        <?php if (!$properties): ?>
            <div class="feature-card">
                <h3>Aucune annonce pour le moment</h3>
                <p>Tu n as pas encore publie de logement. Clique sur “Publier” pour ajouter ta premiere annonce.</p>
            </div>
        <?php else: ?>
            <div class="property-list">
                <?php foreach ($properties as $property): ?>
                    <article class="property-row">
                        <img
                            class="property-thumb"
                            src="<?php echo escape($property['image_path'] ?: '/housing-cm/assets/images/default-property.svg'); ?>"
                            alt="Image du logement <?php echo escape($property['title']); ?>"
                        >
                        <div>
                            <h3><?php echo escape($property['title']); ?></h3>
                            <p class="helper-text">
                                <?php echo escape($property['neighborhood_name']); ?>,
                                <?php echo escape($property['city_name']); ?>,
                                <?php echo escape($property['region_name']); ?>
                            </p>
                        </div>

                        <div class="property-meta">
                            <span class="badge"><?php echo escape($property['property_type']); ?></span>
                            <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                            <span class="badge"><?php echo escape($property['status']); ?></span>
                            <strong><?php echo escape(formatPrice($property['price'])); ?></strong>
                            <a class="btn btn-primary" href="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">Voir details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
