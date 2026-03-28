<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$counts = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'users_blocked' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'blocked'")->fetchColumn(),
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
    'SELECT properties.id, properties.title, properties.listing_type, properties.status, properties.created_at, locations.city_name
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
        <span class="eyebrow">Administration</span>
        <h1>Tableau de bord administrateur</h1>
        <p>Pilote ici la confiance de la plateforme, la qualite des annonces et le suivi des utilisateurs.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card admin-hero-card">
            <div>
                <span class="eyebrow">Vue generale</span>
                <h2>Un poste de controle simple et lisible</h2>
                <p>Retrouve rapidement les inscriptions recentes, les annonces publiees et les signalements a traiter pour garder la plateforme fiable.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?php echo escape(url('/admin/users.php')); ?>">Voir les utilisateurs</a>
                <a class="btn btn-secondary" href="<?php echo escape(url('/admin/reports.php')); ?>">Traiter les signalements</a>
                <a class="btn btn-secondary" href="<?php echo escape(url('/admin/statistics.php')); ?>">Voir les statistiques</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="dashboard-grid dashboard-grid-4">
            <article class="stat-card stat-card-emphasis">
                <h3>Utilisateurs</h3>
                <p><?php echo $counts['users']; ?> compte(s) enregistres.</p>
            </article>

            <article class="stat-card">
                <h3>Comptes bloques</h3>
                <p><?php echo $counts['users_blocked']; ?> compte(s) actuellement limites.</p>
            </article>

            <article class="stat-card">
                <h3>Annonces</h3>
                <p><?php echo $counts['properties']; ?> annonce(s) actuellement en base.</p>
            </article>

            <article class="stat-card">
                <h3>Signalements et visites</h3>
                <p><?php echo $counts['reports_pending']; ?> signalement(s) et <?php echo $counts['visits_pending']; ?> visite(s) en attente.</p>
            </article>
        </div>
    </section>

    <section class="section section-soft">
        <div class="admin-grid">
            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Utilisateurs recents</span>
                        <h2>Dernieres inscriptions</h2>
                    </div>
                    <a class="btn btn-secondary" href="<?php echo escape(url('/admin/users.php')); ?>">Tout afficher</a>
                </div>
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
                                <td><span class="badge"><?php echo escape($user['role']); ?></span></td>
                                <td><?php echo escape($user['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>

            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Annonces recentes</span>
                        <h2>Nouveaux logements publies</h2>
                    </div>
                    <a class="btn btn-secondary" href="<?php echo escape(url('/admin/properties.php')); ?>">Voir tout</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Ville</th>
                            <th>Offre</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProperties as $property): ?>
                            <tr>
                                <td>
                                    <a class="table-link" href="<?php echo escape(url('/properties/details.php?id=' . (int) $property['id'])); ?>">
                                        <?php echo escape($property['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo escape($property['city_name']); ?></td>
                                <td><?php echo escape($property['listing_type']); ?></td>
                                <td><span class="badge"><?php echo escape($property['status']); ?></span></td>
                                <td><?php echo escape($property['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
