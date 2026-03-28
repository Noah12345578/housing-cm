<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="error-shell">
        <article class="error-card">
            <span class="eyebrow">Erreur <?php echo (int) $errorPage['status_code']; ?></span>
            <h1><?php echo escape($errorPage['title']); ?></h1>
            <p><?php echo escape($errorPage['message']); ?></p>

            <div class="card-actions">
                <?php foreach ($errorPage['actions'] as $action): ?>
                    <a class="<?php echo escape($action['class'] ?? 'btn btn-primary'); ?>" href="<?php echo escape($action['url']); ?>">
                        <?php echo escape($action['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
