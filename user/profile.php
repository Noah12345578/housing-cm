<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();

$statement = $pdo->prepare(
    'SELECT id, full_name, email, phone, role, created_at
     FROM users
     WHERE id = :id
     LIMIT 1'
);

$statement->execute(['id' => $user['id']]);
$profile = $statement->fetch();

if (!$profile) {
    renderErrorPage(
        'Profil introuvable',
        'Nous n arrivons pas a charger les informations de ce compte pour le moment.',
        404,
        [
            ['label' => 'Retour au tableau de bord', 'url' => url('/user/dashboard.php'), 'class' => 'btn btn-primary'],
        ]
    );
}

$oldProfileInput = $_SESSION['old_profile_input'] ?? [];
unset($_SESSION['old_profile_input']);

$formData = array_merge($profile, $oldProfileInput);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Compte utilisateur</span>
        <h1>Mon profil</h1>
        <p>Modifie ici tes informations personnelles et ton mot de passe.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card">
            <div>
                <span class="eyebrow">Parametres du compte</span>
                <h2>Maintiens des informations fiables</h2>
                <p>Des coordonnees a jour facilitent les messages, les visites et la gestion de ton espace personnel.</p>
            </div>
            <div class="profile-summary">
                <div class="detail-item"><strong>Role :</strong> <?php echo escape($profile['role']); ?></div>
                <div class="detail-item"><strong>Membre depuis :</strong> <?php echo escape($profile['created_at']); ?></div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="profile-layout">
            <article class="auth-card">
                <h2>Informations personnelles</h2>
                <form class="auth-form" action="/housing-cm/actions/update_profile_action.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">

                    <div>
                        <label for="full_name">Nom complet</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo escape($formData['full_name'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label for="email">Adresse e-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo escape($formData['email'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label for="phone">Telephone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo escape($formData['phone'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label for="role">Role</label>
                        <input type="text" id="role" value="<?php echo escape($profile['role']); ?>" disabled>
                    </div>

                    <button class="btn btn-primary" type="submit">Mettre a jour mes informations</button>
                </form>
            </article>

            <article class="auth-card">
                <h2>Changer le mot de passe</h2>
                <form class="auth-form" action="/housing-cm/actions/update_password_action.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">

                    <div>
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div>
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div>
                        <label for="confirm_new_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                    </div>

                    <button class="btn btn-secondary" type="submit">Changer mon mot de passe</button>
                </form>
            </article>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
