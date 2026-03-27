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
        <h1>Inscription</h1>
        <p>Cree ton compte pour rechercher un logement ou publier une annonce.</p>
    </section>

    <section class="auth-card">
        <form class="auth-form" action="/housing-cm/actions/register_action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
            <div>
                <label for="full_name">Nom complet</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo escape($oldInput['full_name'] ?? ''); ?>" required>
            </div>

            <div class="grid-2">
                <div>
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo escape($oldInput['email'] ?? ''); ?>" required>
                </div>

                <div>
                    <label for="phone">Telephone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo escape($oldInput['phone'] ?? ''); ?>" required>
                </div>
            </div>

            <div>
                <label for="role">Je suis</label>
                <select id="role" name="role" required>
                    <option value="client" <?php echo (($oldInput['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client - je cherche un logement</option>
                    <option value="owner" <?php echo (($oldInput['role'] ?? '') === 'owner') ? 'selected' : ''; ?>>Proprietaire - je publie mes biens</option>
                    <option value="agent" <?php echo (($oldInput['role'] ?? '') === 'agent') ? 'selected' : ''; ?>>Agent - je gere des biens immobiliers</option>
                </select>
            </div>

            <div class="grid-2">
                <div>
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div>
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <p class="helper-text">Choisis un mot de passe d au moins 6 caracteres pour commencer simplement.</p>

            <button class="btn btn-primary" type="submit">Creer mon compte</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
