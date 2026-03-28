<?php
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$users = $pdo->query(
    'SELECT id, full_name, email, phone, role, status, created_at
     FROM users
     ORDER BY created_at DESC'
)->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <span class="eyebrow">Administration</span>
        <h1>Gestion des utilisateurs</h1>
        <p>Consulte rapidement les comptes crees, leurs roles et leur etat general.</p>
    </section>

    <section class="dashboard-hero">
        <div class="dashboard-hero-card admin-hero-card">
            <div>
                <span class="eyebrow">Comptes</span>
                <h2>Une vue claire sur les utilisateurs de la plateforme</h2>
                <p>Cette page aide a identifier rapidement qui utilise le service, quel role il occupe et depuis quand le compte existe.</p>
            </div>
            <div class="admin-mini-stats">
                <div class="detail-item"><strong>Total :</strong> <?php echo count($users); ?> utilisateur(s)</div>
                <div class="detail-item"><strong>Bloques :</strong> <?php echo count(array_filter($users, static fn (array $user): bool => $user['status'] === 'blocked')); ?> compte(s)</div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="table-card">
            <div class="table-card-head">
                <div>
                    <span class="eyebrow">Liste complete</span>
                    <h2>Tous les comptes</h2>
                </div>
                <a class="btn btn-secondary" href="<?php echo escape(url('/admin/dashboard.php')); ?>">Retour dashboard</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Date creation</th>
                        <th>Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo (int) $user['id']; ?></td>
                            <td><?php echo escape($user['full_name']); ?></td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td><?php echo escape($user['phone']); ?></td>
                            <td><span class="badge"><?php echo escape($user['role']); ?></span></td>
                            <td><span class="badge"><?php echo escape($user['status']); ?></span></td>
                            <td><?php echo escape($user['created_at']); ?></td>
                            <td>
                                <form class="admin-inline-form" action="<?php echo escape(url('/actions/admin_update_user_action.php')); ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <select name="role" class="inline-select">
                                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                        <option value="owner" <?php echo $user['role'] === 'owner' ? 'selected' : ''; ?>>Owner</option>
                                        <option value="agent" <?php echo $user['role'] === 'agent' ? 'selected' : ''; ?>>Agent</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <select name="status" class="inline-select">
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="blocked" <?php echo $user['status'] === 'blocked' ? 'selected' : ''; ?>>Bloque</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
