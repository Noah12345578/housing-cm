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
        <span class="eyebrow">Gestion des annonces</span>
        <h1>Mes annonces</h1>
        <p>Retrouve ici tous les logements que tu as deja publies.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Pilotage rapide</span>
                <h2>Garde tes biens a jour</h2>
                <p>Modifie les informations, ajuste le statut et retire une annonce si elle n est plus disponible.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="/housing-cm/properties/create.php">Nouvelle annonce</a>
                <a class="btn btn-secondary" href="/housing-cm/properties/search.php">Voir les resultats</a>
            </div>
        </div>
    </section>

    <section class="section">
        <?php if (!$properties): ?>
            <div class="feature-card">
                <h3>Aucune annonce pour le moment</h3>
                <p>Tu n as pas encore publie de logement. Clique sur “Nouvelle annonce” pour ajouter ton premier bien.</p>
            </div>
        <?php else: ?>
            <div class="property-list">
                <?php foreach ($properties as $property): ?>
                    <article class="property-row property-row-rich">
                        <img
                            class="property-thumb"
                            src="<?php echo escape($property['image_path'] ?: '/housing-cm/assets/images/default-property.svg'); ?>"
                            alt="Image du logement <?php echo escape($property['title']); ?>"
                        >

                        <div class="property-main">
                            <div class="property-card-top">
                                <span class="badge"><?php echo escape($property['property_type']); ?></span>
                                <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                                <span class="badge"><?php echo escape($property['status']); ?></span>
                            </div>

                            <h3><?php echo escape($property['title']); ?></h3>
                            <p class="helper-text">
                                <?php echo escape($property['neighborhood_name']); ?>,
                                <?php echo escape($property['city_name']); ?>,
                                <?php echo escape($property['region_name']); ?>
                            </p>
                            <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>
                        </div>

                        <div class="property-meta">
                            <a class="btn btn-primary" href="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">Voir details</a>
                            <a class="btn btn-secondary" href="/housing-cm/properties/edit.php?id=<?php echo (int) $property['id']; ?>">Modifier</a>

                            <form action="/housing-cm/actions/update_property_status_action.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                <select name="status" class="inline-select">
                                    <option value="disponible" <?php echo $property['status'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="reserve" <?php echo $property['status'] === 'reserve' ? 'selected' : ''; ?>>Reserve</option>
                                    <option value="loue" <?php echo $property['status'] === 'loue' ? 'selected' : ''; ?>>Loue</option>
                                    <option value="vendu" <?php echo $property['status'] === 'vendu' ? 'selected' : ''; ?>>Vendu</option>
                                    <option value="retire" <?php echo $property['status'] === 'retire' ? 'selected' : ''; ?>>Retire</option>
                                </select>
                                <button class="btn btn-secondary" type="submit">Changer statut</button>
                            </form>

                            <form action="/housing-cm/actions/delete_property_action.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette annonce ?');">
                                <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                <button class="btn btn-danger" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
