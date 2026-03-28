<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        latest.id,
        latest.message,
        latest.created_at,
        latest.property_id,
        latest.sender_id,
        latest.receiver_id,
        contact.full_name AS contact_name,
        properties.title AS property_title,
        (
            SELECT COUNT(*)
            FROM messages AS unread
            WHERE unread.sender_id = CASE WHEN latest.sender_id = :user_id THEN latest.receiver_id ELSE latest.sender_id END
              AND unread.receiver_id = :user_id
              AND unread.is_read = 0
              AND (
                    (latest.property_id IS NULL AND unread.property_id IS NULL)
                 OR unread.property_id = latest.property_id
              )
        ) AS unread_count
     FROM messages AS latest
     INNER JOIN (
        SELECT MAX(id) AS latest_id
        FROM messages
        WHERE sender_id = :user_id OR receiver_id = :user_id
        GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id), COALESCE(property_id, 0)
     ) AS grouped_messages ON grouped_messages.latest_id = latest.id
     INNER JOIN users AS contact
        ON contact.id = CASE WHEN latest.sender_id = :user_id THEN latest.receiver_id ELSE latest.sender_id END
     LEFT JOIN properties ON latest.property_id = properties.id
     ORDER BY latest.created_at DESC'
);

$statement->execute(['user_id' => $user['id']]);
$conversations = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Messagerie</span>
        <h1>Conversations</h1>
        <p>Retrouve ici chaque discussion ouverte avec un proprietaire, un agent ou un client.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Suivi des echanges</span>
                <h2>Une boite de reception plus claire</h2>
                <p>Chaque discussion est regroupee par interlocuteur et par annonce pour eviter les messages disperses.</p>
            </div>
            <div class="trust-strip trust-strip-compact">
                <div class="trust-item">
                    <strong>Vue par conversation</strong>
                    <span>Ouvre une discussion et reponds directement dans le fil.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <?php if (!$conversations): ?>
            <div class="feature-card">
                <h3>Aucune conversation pour le moment</h3>
                <p>Les messages envoyes depuis les fiches des logements creeront automatiquement une discussion ici.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($conversations as $conversation): ?>
                    <?php
                    $isIncoming = (int) $conversation['receiver_id'] === (int) $user['id'];
                    $conversationUrl = url(
                        '/messages/conversation.php?contact_id=' . (int) ($isIncoming ? $conversation['sender_id'] : $conversation['receiver_id']) .
                        '&property_id=' . (int) ($conversation['property_id'] ?? 0)
                    );
                    ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($conversation['contact_name']); ?></h3>
                                <p class="helper-text">
                                    A propos de :
                                    <?php echo escape($conversation['property_title'] ?? 'Discussion generale'); ?>
                                </p>
                            </div>
                            <div class="card-actions">
                                <?php if ((int) $conversation['unread_count'] > 0): ?>
                                    <span class="badge"><?php echo (int) $conversation['unread_count']; ?> non lu(s)</span>
                                <?php endif; ?>
                                <a class="btn btn-primary" href="<?php echo escape($conversationUrl); ?>">Ouvrir</a>
                            </div>
                        </div>

                        <div class="message-meta-row">
                            <span><?php echo $isIncoming ? 'Dernier message recu' : 'Dernier message envoye'; ?></span>
                            <span><?php echo escape($conversation['created_at']); ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($conversation['message'])); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
