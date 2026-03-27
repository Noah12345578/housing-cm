<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$counts = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'properties' => (int) $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn(),
    'reports_pending' => (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'en_attente'")->fetchColumn(),
    'visits_pending' => (int) $pdo->query("SELECT COUNT(*) FROM visit_requests WHERE status = 'en_attente'")->fetchColumn(),
];

$recentUsers = $pdo->query(
    'SELECT full_name, email, role, created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 5'
)->fetchAll();

$recentProperties = $pdo->query(
    'SELECT properties.title, properties.listing_type, properties.status, properties.created_at, locations.city_name
     FROM properties
     INNER JOIN locations ON properties.location_id = locations.id
     ORDER BY properties.created_at DESC
     LIMIT 5'
)->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Tableau de bord administrateur</h1>
        <p>Vue rapide de l activite de la plateforme, des utilisateurs et des annonces.</p>
    </section>

    <section class="section">
        <div class="dashboard-grid">
            <article class="stat-card">
                <h3>Utilisateurs</h3>
                <p><?php echo $counts['users']; ?> compte(s) enregistre(s).</p>
            </article>

            <article class="stat-card">
                <h3>Annonces</h3>
                <p><?php echo $counts['properties']; ?> annonce(s) publiee(s).</p>
            </article>

            <article class="stat-card">
                <h3>Signalements en attente</h3>
                <p><?php echo $counts['reports_pending']; ?> signalement(s) a traiter.</p>
            </article>

            <article class="stat-card">
                <h3>Visites en attente</h3>
                <p><?php echo $counts['visits_pending']; ?> demande(s) de visite a traiter.</p>
            </article>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Derniers utilisateurs</h2>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo escape($user['full_name']); ?></td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td><?php echo escape($user['role']); ?></td>
                            <td><?php echo escape($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Dernieres annonces</h2>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Ville</th>
                        <th>Type offre</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProperties as $property): ?>
                        <tr>
                            <td><?php echo escape($property['title']); ?></td>
                            <td><?php echo escape($property['city_name']); ?></td>
                            <td><?php echo escape($property['listing_type']); ?></td>
                            <td><?php echo escape($property['status']); ?></td>
                            <td><?php echo escape($property['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
