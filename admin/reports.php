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
$pendingReports = array_filter(
    $reports,
    static fn (array $report): bool => $report['status'] === 'en_attente'
);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Administration</span>
        <h1>Suivi des signalements</h1>
        <p>Analyse ici les annonces douteuses, les motifs donnes par les utilisateurs et les actions deja prises.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card admin-hero-card">
            <div>
                <span class="eyebrow">Moderation</span>
                <h2>Traiter rapidement les cas suspects</h2>
                <p>Un suivi clair des signalements aide a rassurer les utilisateurs et a garder un catalogue plus fiable.</p>
            </div>
            <div class="admin-mini-stats">
                <div class="detail-item"><strong>Total :</strong> <?php echo count($reports); ?> signalement(s)</div>
                <div class="detail-item"><strong>En attente :</strong> <?php echo count($pendingReports); ?> cas a verifier</div>
            </div>
        </div>
    </section>

    <section class="section">
        <?php if (!$reports): ?>
            <div class="feature-card">
                <h3>Aucun signalement</h3>
                <p>Aucun utilisateur n a encore remonte de probleme sur une annonce.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($reports as $report): ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($report['property_title'] ?? 'Annonce indisponible'); ?></h3>
                                <p class="helper-text">Signale par : <?php echo escape($report['reporter_name']); ?></p>
                            </div>
                            <span class="badge"><?php echo escape($report['status']); ?></span>
                        </div>

                        <div class="message-meta-row">
                            <span>Motif : <?php echo escape($report['reason']); ?></span>
                            <span>Le <?php echo escape($report['created_at']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($report['description'] ?? 'Aucune precision supplementaire.')); ?></p>

                        <div class="card-actions">
                            <?php if ($report['property_id']): ?>
                                <a class="btn btn-secondary" href="<?php echo escape(url('/properties/details.php?id=' . (int) $report['property_id'])); ?>">Voir l annonce</a>
                            <?php endif; ?>

                            <?php if ($report['status'] === 'en_attente'): ?>
                                <form action="<?php echo escape(url('/actions/update_report_status_action.php')); ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                                    <input type="hidden" name="status" value="traite">
                                    <button class="btn btn-primary" type="submit">Marquer traite</button>
                                </form>

                                <form action="<?php echo escape(url('/actions/update_report_status_action.php')); ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                                    <input type="hidden" name="status" value="rejete">
                                    <button class="btn btn-secondary" type="submit">Rejeter</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
