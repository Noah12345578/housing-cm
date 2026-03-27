<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$statement = $pdo->query(
    'SELECT
        reports.id,
        reports.reason,
        reports.description,
        reports.status,
        reports.created_at,
        reporter.full_name AS reporter_name,
        properties.id AS property_id,
        properties.title AS property_title
     FROM reports
     INNER JOIN users AS reporter ON reports.user_id = reporter.id
     LEFT JOIN properties ON reports.property_id = properties.id
     ORDER BY reports.created_at DESC'
);

$reports = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Administration des signalements</h1>
        <p>Cette page permet a l administrateur de suivre les annonces signalees et de traiter les cas suspects.</p>
    </section>

    <section class="section">
        <?php if (!$reports): ?>
            <div class="feature-card">
                <h3>Aucun signalement</h3>
                <p>Aucun utilisateur n a encore signale d annonce.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($reports as $report): ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($report['property_title'] ?? 'Annonce indisponible'); ?></h3>
                                <p class="helper-text">
                                    Signale par : <?php echo escape($report['reporter_name']); ?> |
                                    Motif : <?php echo escape($report['reason']); ?>
                                </p>
                            </div>
                            <span class="badge"><?php echo escape($report['status']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($report['description'] ?? 'Aucune precision supplementaire.')); ?></p>
                        <p class="helper-text">Date : <?php echo escape($report['created_at']); ?></p>

                        <?php if ($report['status'] === 'en_attente'): ?>
                            <div class="card-actions">
                                <form action="/housing-cm/actions/update_report_status_action.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                                    <input type="hidden" name="status" value="traite">
                                    <button class="btn btn-primary" type="submit">Marquer traite</button>
                                </form>

                                <form action="/housing-cm/actions/update_report_status_action.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                                    <input type="hidden" name="status" value="rejete">
                                    <button class="btn btn-secondary" type="submit">Rejeter</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
