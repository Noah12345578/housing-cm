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
        <span class="eyebrow">Administration</span>
        <h1>Gestion des annonces</h1>
        <p>Surveille ici les logements publies, leurs responsables et leur statut sur la plateforme.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card admin-hero-card">
            <div>
                <span class="eyebrow">Supervision</span>
                <h2>Controle rapide de la qualite des annonces</h2>
                <p>Cette vue aide a reperer les annonces actives, les logements deja retires et les responsables qui publient sur la plateforme.</p>
            </div>
            <div class="admin-mini-stats">
                <div class="detail-item"><strong>Total :</strong> <?php echo count($properties); ?> annonce(s)</div>
                <div class="detail-item"><strong>Consultation :</strong> acces direct a chaque fiche detail</div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="table-card">
            <div class="table-card-head">
                <div>
                    <span class="eyebrow">Catalogue global</span>
                    <h2>Toutes les annonces</h2>
                </div>
                <a class="btn btn-secondary" href="<?php echo escape(url('/admin/dashboard.php')); ?>">Retour dashboard</a>
            </div>
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
                            <td><span class="badge"><?php echo escape($property['property_type']); ?></span></td>
                            <td><?php echo escape($property['listing_type']); ?></td>
                            <td><?php echo escape(formatPrice($property['price'])); ?></td>
                            <td><span class="badge"><?php echo escape($property['status']); ?></span></td>
                            <td>
                                <div class="admin-action-stack">
                                    <a class="btn btn-primary" href="<?php echo escape(url('/properties/details.php?id=' . (int) $property['id'])); ?>">Voir</a>
                                    <form class="admin-inline-form" action="<?php echo escape(url('/actions/admin_update_property_action.php')); ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                        <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                        <select name="status" class="inline-select">
                                            <option value="disponible" <?php echo $property['status'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                            <option value="reserve" <?php echo $property['status'] === 'reserve' ? 'selected' : ''; ?>>Reserve</option>
                                            <option value="loue" <?php echo $property['status'] === 'loue' ? 'selected' : ''; ?>>Loue</option>
                                            <option value="vendu" <?php echo $property['status'] === 'vendu' ? 'selected' : ''; ?>>Vendu</option>
                                            <option value="retire" <?php echo $property['status'] === 'retire' ? 'selected' : ''; ?>>Retire</option>
                                        </select>
                                        <button class="btn btn-secondary" type="submit">Mettre a jour</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
