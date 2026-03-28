<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$receivedStatement = $pdo->prepare(
    'SELECT
        visit_requests.id,
        visit_requests.preferred_date,
        visit_requests.message,
        visit_requests.status,
        visit_requests.created_at,
        properties.id AS property_id,
        properties.title AS property_title,
        requester.full_name AS requester_name,
        requester.phone AS requester_phone
     FROM visit_requests
     INNER JOIN properties ON visit_requests.property_id = properties.id
     INNER JOIN users AS requester ON visit_requests.requester_id = requester.id
     WHERE visit_requests.owner_id = :user_id
     ORDER BY visit_requests.created_at DESC'
);

$receivedStatement->execute(['user_id' => $user['id']]);
$receivedVisits = $receivedStatement->fetchAll();

$sentStatement = $pdo->prepare(
    'SELECT
        visit_requests.id,
        visit_requests.preferred_date,
        visit_requests.message,
        visit_requests.status,
        visit_requests.created_at,
        properties.id AS property_id,
        properties.title AS property_title,
        owner.full_name AS owner_name
     FROM visit_requests
     INNER JOIN properties ON visit_requests.property_id = properties.id
     INNER JOIN users AS owner ON visit_requests.owner_id = owner.id
     WHERE visit_requests.requester_id = :user_id
     ORDER BY visit_requests.created_at DESC'
);

$sentStatement->execute(['user_id' => $user['id']]);
$sentVisits = $sentStatement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Organisation des visites</span>
        <h1>Demandes de visite</h1>
        <p>Consulte les visites que tu as demandees et celles que tu as recues.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Planification</span>
                <h2>Suivi clair des rendez-vous immobiliers</h2>
                <p>Retrouve les propositions de visites, les dates souhaitees et les reponses des responsables de logement.</p>
            </div>
            <div class="card-actions">
                <a class="btn btn-primary" href="/housing-cm/properties/search.php">Chercher un logement</a>
            </div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Demandes recues</h2>
        <?php if (!$receivedVisits): ?>
            <div class="feature-card">
                <h3>Aucune demande recue</h3>
                <p>Les clients interesses par tes logements apparaitront ici.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($receivedVisits as $visit): ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($visit['requester_name']); ?></h3>
                                <p class="helper-text"><?php echo escape($visit['property_title']); ?></p>
                            </div>
                            <span class="badge"><?php echo escape($visit['status']); ?></span>
                        </div>

                        <div class="message-meta-row">
                            <span>Date souhaitee : <?php echo escape($visit['preferred_date']); ?></span>
                            <span>Telephone : <?php echo escape($visit['requester_phone']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($visit['message'] ?? 'Aucun message.')); ?></p>

                        <?php if ($visit['status'] === 'en_attente'): ?>
                            <div class="card-actions">
                                <form action="/housing-cm/actions/update_visit_status_action.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="visit_id" value="<?php echo (int) $visit['id']; ?>">
                                    <input type="hidden" name="status" value="acceptee">
                                    <button class="btn btn-primary" type="submit">Accepter</button>
                                </form>

                                <form action="/housing-cm/actions/update_visit_status_action.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="visit_id" value="<?php echo (int) $visit['id']; ?>">
                                    <input type="hidden" name="status" value="refusee">
                                    <button class="btn btn-secondary" type="submit">Refuser</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="section">
        <h2 class="section-title">Demandes envoyees</h2>
        <?php if (!$sentVisits): ?>
            <div class="feature-card">
                <h3>Aucune demande envoyee</h3>
                <p>Quand tu demandes une visite depuis une fiche detail, elle apparaitra ici.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($sentVisits as $visit): ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($visit['property_title']); ?></h3>
                                <p class="helper-text">Responsable : <?php echo escape($visit['owner_name']); ?></p>
                            </div>
                            <span class="badge"><?php echo escape($visit['status']); ?></span>
                        </div>

                        <div class="message-meta-row">
                            <span>Date souhaitee : <?php echo escape($visit['preferred_date']); ?></span>
                            <span>Responsable : <?php echo escape($visit['owner_name']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($visit['message'] ?? 'Aucun message.')); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
