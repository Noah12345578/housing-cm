<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$properties = $pdo->query(
    'SELECT
        properties.id,
        properties.title,
        properties.listing_type,
        properties.property_type,
        properties.price,
        properties.status,
        properties.created_at,
        users.full_name AS owner_name,
        locations.city_name,
        locations.neighborhood_name
     FROM properties
     INNER JOIN users ON properties.user_id = users.id
     INNER JOIN locations ON properties.location_id = locations.id
     ORDER BY properties.created_at DESC'
)->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Gestion des annonces</h1>
        <p>Vue globale des logements publies sur la plateforme.</p>
    </section>

    <section class="section">
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Responsable</th>
                        <th>Ville</th>
                        <th>Quartier</th>
                        <th>Type</th>
                        <th>Offre</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $property): ?>
                        <tr>
                            <td><?php echo (int) $property['id']; ?></td>
                            <td><?php echo escape($property['title']); ?></td>
                            <td><?php echo escape($property['owner_name']); ?></td>
                            <td><?php echo escape($property['city_name']); ?></td>
                            <td><?php echo escape($property['neighborhood_name']); ?></td>
                            <td><?php echo escape($property['property_type']); ?></td>
                            <td><?php echo escape($property['listing_type']); ?></td>
                            <td><?php echo escape(formatPrice($property['price'])); ?></td>
                            <td><?php echo escape($property['status']); ?></td>
                            <td>
                                <a class="btn btn-primary" href="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
