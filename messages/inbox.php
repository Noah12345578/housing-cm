<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT
        messages.id,
        messages.message,
        messages.is_read,
        messages.created_at,
        messages.property_id,
        properties.title AS property_title,
        sender.full_name AS sender_name,
        receiver.full_name AS receiver_name,
        sender.id AS sender_id,
        receiver.id AS receiver_id
     FROM messages
     LEFT JOIN properties ON messages.property_id = properties.id
     INNER JOIN users AS sender ON messages.sender_id = sender.id
     INNER JOIN users AS receiver ON messages.receiver_id = receiver.id
     WHERE messages.sender_id = :user_id OR messages.receiver_id = :user_id
     ORDER BY messages.created_at DESC'
);

$statement->execute(['user_id' => $user['id']]);
$messages = $statement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Messagerie</h1>
        <p>Consulte ici les messages envoyes et recus a propos des logements.</p>
    </section>

    <section class="section">
        <?php if (!$messages): ?>
            <div class="feature-card">
                <h3>Aucun message pour le moment</h3>
                <p>Les demandes de contact envoyees depuis les fiches des logements apparaitront ici.</p>
            </div>
        <?php else: ?>
            <div class="message-list">
                <?php foreach ($messages as $message): ?>
                    <?php
                    $isIncoming = (int) $message['receiver_id'] === (int) $user['id'];
                    $contactName = $isIncoming ? $message['sender_name'] : $message['receiver_name'];
                    ?>
                    <article class="message-card">
                        <div class="message-top">
                            <div>
                                <h3><?php echo escape($contactName); ?></h3>
                                <p class="helper-text">
                                    A propos de :
                                    <?php echo escape($message['property_title'] ?? 'Message general'); ?>
                                </p>
                            </div>
                            <span class="badge"><?php echo $isIncoming ? 'Recu' : 'Envoye'; ?></span>
                        </div>

                        <p class="message-text"><?php echo nl2br(escape($message['message'])); ?></p>
                        <p class="helper-text">Envoye le <?php echo escape($message['created_at']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
