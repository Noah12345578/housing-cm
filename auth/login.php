<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

redirectIfLoggedIn();

$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Connexion</h1>
        <p>Connecte-toi pour acceder a ton espace personnel.</p>
    </section>

    <section class="auth-card">
        <form class="auth-form" action="/housing-cm/actions/login_action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
            <div>
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="<?php echo escape($oldInput['email'] ?? ''); ?>" required>
            </div>

            <div>
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button class="btn btn-primary" type="submit">Se connecter</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
