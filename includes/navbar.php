<?php
$loggedInUser = currentUser();
?>
<header class="site-header">
    <div class="container navbar">
        <a class="brand" href="/housing-cm/index.php">Housing CM</a>

        <nav class="main-nav">
            <a href="/housing-cm/index.php">Accueil</a>
            <a href="/housing-cm/properties/search.php">Rechercher</a>
            <?php if ($loggedInUser): ?>
                <?php if (in_array($loggedInUser['role'], ['owner', 'agent'], true)): ?>
                    <a href="/housing-cm/properties/create.php">Publier</a>
                    <a href="/housing-cm/properties/my-properties.php">Mes annonces</a>
                <?php endif; ?>
                <?php if (($loggedInUser['role'] ?? '') === 'admin'): ?>
                    <a href="/housing-cm/admin/dashboard.php">Administration</a>
                <?php endif; ?>
                <a href="/housing-cm/user/favorites.php">Favoris</a>
                <a href="/housing-cm/user/visits.php">Visites</a>
                <a href="/housing-cm/messages/inbox.php">Messages</a>
                <a href="/housing-cm/user/reports.php">Signalements</a>
                <a href="/housing-cm/user/dashboard.php">Tableau de bord</a>
                <a href="/housing-cm/auth/logout.php">Deconnexion</a>
            <?php else: ?>
                <a href="/housing-cm/auth/register.php">Inscription</a>
                <a href="/housing-cm/auth/login.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
