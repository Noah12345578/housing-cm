<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$roleDistribution = $pdo->query(
    'SELECT role, COUNT(*) AS total
     FROM users
     GROUP BY role
     ORDER BY total DESC'
)->fetchAll();

$statusDistribution = $pdo->query(
    'SELECT status, COUNT(*) AS total
     FROM properties
     GROUP BY status
     ORDER BY total DESC'
)->fetchAll();

$cityDistribution = $pdo->query(
    'SELECT locations.city_name, COUNT(*) AS total
     FROM properties
     INNER JOIN locations ON properties.location_id = locations.id
     GROUP BY locations.city_name
     ORDER BY total DESC
     LIMIT 6'
)->fetchAll();

$typeDistribution = $pdo->query(
    'SELECT property_type, COUNT(*) AS total
     FROM properties
     GROUP BY property_type
     ORDER BY total DESC
     LIMIT 6'
)->fetchAll();

$listingDistribution = $pdo->query(
    'SELECT listing_type, COUNT(*) AS total
     FROM properties
     GROUP BY listing_type
     ORDER BY total DESC'
)->fetchAll();

$maxCityTotal = max(array_map(static fn (array $item): int => (int) $item['total'], $cityDistribution ?: [['total' => 1]]));
$maxTypeTotal = max(array_map(static fn (array $item): int => (int) $item['total'], $typeDistribution ?: [['total' => 1]]));
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Administration</span>
        <h1>Statistiques de la plateforme</h1>
        <p>Observe les tendances utiles : qui publie, ou les annonces se concentrent et quels types de biens dominent.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card admin-hero-card">
            <div>
                <span class="eyebrow">Pilotage</span>
                <h2>Des chiffres simples pour mieux decider</h2>
                <p>Cette page aide a voir rapidement l etat du catalogue, la repartition des comptes et les zones les plus actives.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?php echo escape(url('/admin/dashboard.php')); ?>">Retour dashboard</a>
                <a class="btn btn-secondary" href="<?php echo escape(url('/admin/properties.php')); ?>">Voir les annonces</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="dashboard-grid dashboard-grid-4">
            <?php foreach ($roleDistribution as $role): ?>
                <article class="stat-card">
                    <h3>Role : <?php echo escape($role['role']); ?></h3>
                    <p><?php echo (int) $role['total']; ?> utilisateur(s)</p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section section-soft">
        <div class="admin-grid">
            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Villes</span>
                        <h2>Villes les plus actives</h2>
                    </div>
                </div>
                <div class="stats-list">
                    <?php foreach ($cityDistribution as $city): ?>
                        <?php $width = $maxCityTotal > 0 ? ((int) $city['total'] / $maxCityTotal) * 100 : 0; ?>
                        <div class="stats-row">
                            <div class="stats-row-head">
                                <strong><?php echo escape($city['city_name']); ?></strong>
                                <span><?php echo (int) $city['total']; ?> annonce(s)</span>
                            </div>
                            <div class="stats-bar">
                                <span style="width: <?php echo (float) $width; ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Types de biens</span>
                        <h2>Logements les plus publies</h2>
                    </div>
                </div>
                <div class="stats-list">
                    <?php foreach ($typeDistribution as $type): ?>
                        <?php $width = $maxTypeTotal > 0 ? ((int) $type['total'] / $maxTypeTotal) * 100 : 0; ?>
                        <div class="stats-row">
                            <div class="stats-row-head">
                                <strong><?php echo escape($type['property_type']); ?></strong>
                                <span><?php echo (int) $type['total']; ?> annonce(s)</span>
                            </div>
                            <div class="stats-bar">
                                <span style="width: <?php echo (float) $width; ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="admin-grid">
            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Statut des annonces</span>
                        <h2>Repartition du catalogue</h2>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Statut</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statusDistribution as $status): ?>
                            <tr>
                                <td><span class="badge"><?php echo escape($status['status']); ?></span></td>
                                <td><?php echo (int) $status['total']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>

            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Type d offre</span>
                        <h2>Location vs vente</h2>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Offre</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listingDistribution as $listing): ?>
                            <tr>
                                <td><span class="badge"><?php echo escape($listing['listing_type']); ?></span></td>
                                <td><?php echo (int) $listing['total']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
