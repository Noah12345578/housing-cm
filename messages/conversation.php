<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();
$contactId = isset($_GET['contact_id']) ? (int) $_GET['contact_id'] : 0;
$propertyId = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;

if ($contactId <= 0) {
    renderErrorPage(
        'Conversation introuvable',
        'La discussion demandee ne peut pas etre ouverte sans interlocuteur valide.',
        404,
        [
            ['label' => 'Retour aux messages', 'url' => url('/messages/inbox.php'), 'class' => 'btn btn-primary'],
        ]
    );
}

$contactStatement = $pdo->prepare(
    'SELECT id, full_name, role
     FROM users
     WHERE id = :id
     LIMIT 1'
);
$contactStatement->execute(['id' => $contactId]);
$contact = $contactStatement->fetch();

if (!$contact) {
    renderErrorPage(
        'Interlocuteur introuvable',
        'Le contact associe a cette conversation n existe plus ou n est plus accessible.',
        404,
        [
            ['label' => 'Retour aux messages', 'url' => url('/messages/inbox.php'), 'class' => 'btn btn-primary'],
        ]
    );
}

$property = null;

if ($propertyId > 0) {
    $propertyStatement = $pdo->prepare(
        'SELECT id, title
         FROM properties
         WHERE id = :id
         LIMIT 1'
    );
    $propertyStatement->execute(['id' => $propertyId]);
    $property = $propertyStatement->fetch();
}

$messagesStatement = $pdo->prepare(
    'SELECT
        id,
        sender_id,
        receiver_id,
        message,
        is_read,
        created_at
     FROM messages
     WHERE (
            (sender_id = :user_id AND receiver_id = :contact_id)
         OR (sender_id = :contact_id AND receiver_id = :user_id)
     )
       AND (
            (:property_id = 0 AND property_id IS NULL)
         OR property_id = :property_id
       )
     ORDER BY created_at ASC, id ASC'
);

$messagesStatement->execute([
    'user_id' => $user['id'],
    'contact_id' => $contactId,
    'property_id' => $propertyId,
]);

$messages = $messagesStatement->fetchAll();

if (!$messages) {
    renderErrorPage(
        'Conversation vide ou indisponible',
        'Nous n avons trouve aucun message correspondant a cette discussion.',
        404,
        [
            ['label' => 'Retour aux conversations', 'url' => url('/messages/inbox.php'), 'class' => 'btn btn-primary'],
        ]
    );
}

$markReadStatement = $pdo->prepare(
    'UPDATE messages
     SET is_read = 1
     WHERE sender_id = :contact_id
       AND receiver_id = :user_id
       AND is_read = 0
       AND (
            (:property_id = 0 AND property_id IS NULL)
         OR property_id = :property_id
       )'
);
$markReadStatement->execute([
    'contact_id' => $contactId,
    'user_id' => $user['id'],
    'property_id' => $propertyId,
]);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Conversation</span>
        <h1><?php echo escape($contact['full_name']); ?></h1>
        <p>
            <?php if ($property): ?>
                Discussion a propos de : <?php echo escape($property['title']); ?>
            <?php else: ?>
                Discussion generale
            <?php endif; ?>
        </p>
    </section>

    <section class="conversation-shell">
        <article class="detail-card">
            <div class="message-top">
                <div>
                    <h2>Fil de discussion</h2>
                    <p class="helper-text">Role de l interlocuteur : <?php echo escape($contact['role']); ?></p>
                </div>
                <a class="btn btn-secondary" href="<?php echo escape(url('/messages/inbox.php')); ?>">Retour aux conversations</a>
            </div>

            <div class="conversation-thread">
                <?php foreach ($messages as $message): ?>
                    <?php $isOwnMessage = (int) $message['sender_id'] === (int) $user['id']; ?>
                    <article class="message-bubble <?php echo $isOwnMessage ? 'message-bubble-outgoing' : 'message-bubble-incoming'; ?>">
                        <div class="message-meta-row">
                            <span><?php echo $isOwnMessage ? 'Toi' : escape($contact['full_name']); ?></span>
                            <span><?php echo escape($message['created_at']); ?></span>
                        </div>
                        <p class="message-text"><?php echo nl2br(escape($message['message'])); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>

        <aside class="detail-card conversation-composer">
            <span class="eyebrow">Repondre</span>
            <h2>Envoyer un nouveau message</h2>
            <p class="helper-text">Ta reponse sera ajoutee a cette conversation.</p>

            <form class="auth-form" action="<?php echo escape(url('/actions/send_message_action.php')); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                <input type="hidden" name="property_id" value="<?php echo (int) ($property['id'] ?? 0); ?>">
                <input type="hidden" name="receiver_id" value="<?php echo (int) $contact['id']; ?>">
                <input type="hidden" name="redirect_to" value="<?php echo escape('/messages/conversation.php?contact_id=' . (int) $contact['id'] . '&property_id=' . (int) ($property['id'] ?? 0)); ?>">

                <div>
                    <label for="message">Ton message</label>
                    <textarea id="message" name="message" rows="8" required placeholder="Ecris ici ta reponse..."></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Envoyer la reponse</button>
            </form>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
