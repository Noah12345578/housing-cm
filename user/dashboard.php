<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$user = currentUser();

$historyStatement = $pdo->prepare(
    'SELECT keywords, city_name, max_price, property_type, created_at
     FROM search_history
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 3'
);
$historyStatement->execute(['user_id' => $user['id']]);
$recentSearches = $historyStatement->fetchAll();

$suggestedProperties = [];

if ($recentSearches) {
    $latestSearch = $recentSearches[0];
    $suggestionSql = 'SELECT
            properties.id,
            properties.title,
            properties.price,
            properties.property_type,
            property_images.image_path,
            locations.city_name,
            locations.neighborhood_name
         FROM properties
         INNER JOIN locations ON properties.location_id = locations.id
         LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
         WHERE properties.status = :status';

    $suggestionParams = ['status' => 'disponible'];

    if (!empty($latestSearch['city_name'])) {
        $suggestionSql .= ' AND locations.city_name LIKE :city_name';
        $suggestionParams['city_name'] = '%' . $latestSearch['city_name'] . '%';
    }

    if (!empty($latestSearch['property_type'])) {
        $suggestionSql .= ' AND properties.property_type = :property_type';
        $suggestionParams['property_type'] = $latestSearch['property_type'];
    }

    if ($latestSearch['max_price'] !== null) {
        $suggestionSql .= ' AND properties.price <= :max_price';
        $suggestionParams['max_price'] = (float) $latestSearch['max_price'];
    }

    $suggestionSql .= ' GROUP BY properties.id ORDER BY properties.created_at DESC LIMIT 3';

    $suggestionStatement = $pdo->prepare($suggestionSql);
    $suggestionStatement->execute($suggestionParams);
    $suggestedProperties = $suggestionStatement->fetchAll();
}
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

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Espace personnel</span>
                <h2>Gere tes actions depuis un seul endroit</h2>
                <p>Retrouve rapidement tes favoris, messages, demandes de visite, signalements et informations de profil.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="/housing-cm/properties/search.php">Explorer les annonces</a>
                <a class="btn btn-secondary" href="/housing-cm/user/search-history.php">Voir mon historique</a>
                <a class="btn btn-secondary" href="/housing-cm/user/profile.php">Voir mon profil</a>
            </div>
        </div>
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

    <section class="section">
        <div class="cards-3">
            <article class="feature-card">
                <h3>Favoris</h3>
                <p>Enregistre les logements qui t interessent pour les comparer plus facilement.</p>
            </article>
            <article class="feature-card">
                <h3>Messages</h3>
                <p>Retrouve les discussions avec les proprietaires et agents immobiliers.</p>
            </article>
            <article class="feature-card">
                <h3>Visites</h3>
                <p>Consulte les demandes envoyees ou recues et leur statut actuel.</p>
            </article>
        </div>
    </section>

    <section class="section section-soft">
        <div class="admin-grid">
            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Mes recherches</span>
                        <h2>Dernieres recherches utiles</h2>
                    </div>
                    <a class="btn btn-secondary" href="/housing-cm/user/search-history.php">Voir tout</a>
                </div>

                <?php if (!$recentSearches): ?>
                    <div class="stats-list">
                        <p class="helper-text">Aucune recherche enregistree pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="history-list history-list-compact">
                        <?php foreach ($recentSearches as $search): ?>
                            <?php
                            $params = [];
                            if (!empty($search['city_name'])) {
                                $params['city'] = $search['city_name'];
                            }
                            if (!empty($search['keywords'])) {
                                $params['neighborhood'] = $search['keywords'];
                            }
                            if (!empty($search['property_type'])) {
                                $params['property_type'] = $search['property_type'];
                            }
                            if ($search['max_price'] !== null) {
                                $params['max_price'] = (int) $search['max_price'];
                            }
                            $searchUrl = '/housing-cm/properties/search.php' . (!empty($params) ? '?' . http_build_query($params) : '');
                            ?>
                            <article class="feature-card history-card">
                                <div class="message-top">
                                    <div>
                                        <h3><?php echo escape($search['city_name'] ?: 'Recherche generale'); ?></h3>
                                        <p class="helper-text">Le <?php echo escape($search['created_at']); ?></p>
                                    </div>
                                    <a class="btn btn-primary" href="<?php echo escape($searchUrl); ?>">Relancer</a>
                                </div>

                                <div class="metric-row">
                                    <?php if (!empty($search['keywords'])): ?>
                                        <span><?php echo escape($search['keywords']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($search['property_type'])): ?>
                                        <span><?php echo escape($search['property_type']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($search['max_price'] !== null): ?>
                                        <span>Budget max : <?php echo escape(formatPrice($search['max_price'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="table-card">
                <div class="table-card-head">
                    <div>
                        <span class="eyebrow">Suggestions</span>
                        <h2>Biens proches de tes dernieres recherches</h2>
                    </div>
                </div>

                <?php if (!$suggestedProperties): ?>
                    <div class="stats-list">
                        <p class="helper-text">Fais quelques recherches connecte a ton compte pour voir apparaitre des suggestions ici.</p>
                    </div>
                <?php else: ?>
                    <div class="cards-3 cards-compact">
                        <?php foreach ($suggestedProperties as $property): ?>
                            <article class="property-card property-card-rich">
                                <img
                                    class="property-card-image"
                                    src="<?php echo escape(url($property['image_path'] ?: '/assets/images/default-property.svg')); ?>"
                                    alt="Image du logement <?php echo escape($property['title']); ?>"
                                >
                                <div class="property-card-body">
                                    <h3><?php echo escape($property['title']); ?></h3>
                                    <p class="property-location"><?php echo escape($property['neighborhood_name']); ?>, <?php echo escape($property['city_name']); ?></p>
                                    <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>
                                    <div class="card-actions">
                                        <a class="btn btn-primary" href="<?php echo escape(url('/properties/details.php?id=' . (int) $property['id'])); ?>">Voir details</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
