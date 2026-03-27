<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$user = currentUser();
?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Tableau de bord</h1>
        <p>
            Bienvenue <?php echo escape($user['full_name']); ?>.
            Tu es connecte en tant que <?php echo escape($user['role']); ?>.
        </p>
    </section>

    <section class="section">
        <div class="dashboard-grid">
            <article class="stat-card">
                <h3>Compte actif</h3>
                <p>Ton espace personnel est maintenant accessible avec une vraie session PHP.</p>
            </article>

            <article class="stat-card">
                <h3>Prochaine etape</h3>
                <p>Tu peux maintenant publier des annonces, les rechercher, envoyer des messages et gerer les demandes de visite.</p>
            </article>

            <article class="stat-card">
                <h3>Profil</h3>
                <p>Email : <?php echo escape($user['email']); ?><br>Telephone : <?php echo escape($user['phone']); ?></p>
            </article>
        </div>
    </section>

    <?php if (($user['role'] ?? '') === 'admin'): ?>
        <section class="section">
            <div class="card-actions">
                <a class="btn btn-primary" href="/housing-cm/admin/dashboard.php">Ouvrir le tableau de bord admin</a>
                <a class="btn btn-secondary" href="/housing-cm/admin/reports.php">Voir les signalements</a>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
