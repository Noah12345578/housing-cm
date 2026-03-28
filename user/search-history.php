<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        id,
        keywords,
        region_name,
        city_name,
        min_price,
        max_price,
        property_type,
        created_at
     FROM search_history
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 20'
);

$statement->execute(['user_id' => $user['id']]);
$historyEntries = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Mon espace</span>
        <h1>Historique de recherche</h1>
        <p>Retrouve ici tes dernieres recherches pour relancer rapidement une exploration similaire.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Memoire utilisateur</span>
                <h2>Repars d une recherche deja utile</h2>
                <p>Cet historique t aide a gagner du temps quand tu explores plusieurs quartiers, budgets ou types de logements.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="<?php echo escape(url('/properties/search.php')); ?>">Nouvelle recherche</a>
            </div>
        </div>
    </section>

    <section class="section">
        <?php if (!$historyEntries): ?>
            <div class="feature-card">
                <h3>Aucune recherche enregistree</h3>
                <p>Des que tu filtreras des annonces connecte a ton compte, tes recherches utiles apparaitront ici.</p>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($historyEntries as $entry): ?>
                    <?php
                    $params = [];

                    if (!empty($entry['city_name'])) {
                        $params['city'] = $entry['city_name'];
                    }

                    if (!empty($entry['keywords'])) {
                        $params['neighborhood'] = $entry['keywords'];
                    }

                    if (!empty($entry['property_type'])) {
                        $params['property_type'] = $entry['property_type'];
                    }

                    if ($entry['max_price'] !== null) {
                        $params['max_price'] = (int) $entry['max_price'];
                    }

                    $searchUrl = url('/properties/search.php' . (!empty($params) ? '?' . http_build_query($params) : ''));
                    ?>
                    <article class="feature-card history-card">
                        <div class="message-top">
                            <div>
                                <h3>
                                    <?php echo escape($entry['city_name'] ?: 'Recherche generale'); ?>
                                    <?php if (!empty($entry['keywords'])): ?>
                                        - <?php echo escape($entry['keywords']); ?>
                                    <?php endif; ?>
                                </h3>
                                <p class="helper-text">Le <?php echo escape($entry['created_at']); ?></p>
                            </div>
                            <a class="btn btn-primary" href="<?php echo escape($searchUrl); ?>">Relancer</a>
                        </div>

                        <div class="metric-row">
                            <?php if (!empty($entry['property_type'])): ?>
                                <span><?php echo escape($entry['property_type']); ?></span>
                            <?php endif; ?>
                            <?php if ($entry['max_price'] !== null): ?>
                                <span>Budget max : <?php echo escape(formatPrice($entry['max_price'])); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($entry['region_name'])): ?>
                                <span><?php echo escape($entry['region_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
