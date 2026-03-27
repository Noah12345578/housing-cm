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
        <h1>Gestion des utilisateurs</h1>
        <p>Liste de tous les comptes de la plateforme.</p>
    </section>

    <section class="section">
        <div class="table-card">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo (int) $user['id']; ?></td>
                            <td><?php echo escape($user['full_name']); ?></td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td><?php echo escape($user['phone']); ?></td>
                            <td><?php echo escape($user['role']); ?></td>
                            <td><?php echo escape($user['status']); ?></td>
                            <td><?php echo escape($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
