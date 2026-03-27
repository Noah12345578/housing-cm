<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        reports.id,
        reports.reason,
        reports.description,
        reports.status,
        reports.created_at,
        properties.id AS property_id,
        properties.title AS property_title
     FROM reports
     LEFT JOIN properties ON reports.property_id = properties.id
     WHERE reports.user_id = :user_id
     ORDER BY reports.created_at DESC'
);

$statement->execute(['user_id' => $user['id']]);
$reports = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Mes signalements</h1>
        <p>Consulte ici les annonces que tu as signalees et l etat de traitement par l administrateur.</p>
    </section>

    <section class="section">
        <?php if (!$reports): ?>
            <div class="feature-card">
                <h3>Aucun signalement pour le moment</h3>
                <p>Si une annonce te semble douteuse, tu pourras la signaler depuis sa fiche detail.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($reports as $report): ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($report['property_title'] ?? 'Annonce indisponible'); ?></h3>
                                <p class="helper-text">Motif : <?php echo escape($report['reason']); ?></p>
                            </div>
                            <span class="badge"><?php echo escape($report['status']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($report['description'] ?? 'Aucune precision supplementaire.')); ?></p>
                        <p class="helper-text">Date : <?php echo escape($report['created_at']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
