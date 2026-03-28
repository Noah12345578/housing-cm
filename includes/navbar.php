<?php
$loggedInUser = currentUser();
$userRole = $loggedInUser['role'] ?? null;
?>
<header class="site-header">
    <div class="container navbar">
        <a class="brand" href="/housing-cm/index.php">Housing CM</a>

        <nav class="main-nav">
            <div class="nav-primary">
                <a class="nav-link" href="/housing-cm/index.php">Accueil</a>
                <a class="nav-link" href="/housing-cm/properties/search.php">Rechercher</a>
            </div>

            <?php if ($loggedInUser): ?>
                <div class="nav-group">
                    <?php if (count(comparePropertyIds()) > 0): ?>
                        <a class="nav-link" href="/housing-cm/properties/compare.php">Comparer (<?php echo count(comparePropertyIds()); ?>)</a>
                    <?php endif; ?>

                    <?php if (in_array($userRole, ['owner', 'agent'], true)): ?>
                        <details class="nav-dropdown">
                            <summary>Mes annonces</summary>
                            <div class="nav-dropdown-menu">
                                <a href="/housing-cm/properties/create.php">Publier une annonce</a>
                                <a href="/housing-cm/properties/my-properties.php">Gerer mes annonces</a>
                            </div>
                        </details>
                    <?php endif; ?>

                    <details class="nav-dropdown">
                        <summary>Mon espace</summary>
                        <div class="nav-dropdown-menu">
                            <a href="/housing-cm/user/dashboard.php">Tableau de bord</a>
                            <a href="/housing-cm/user/profile.php">Profil</a>
                            <a href="/housing-cm/user/favorites.php">Favoris</a>
                            <a href="/housing-cm/user/search-history.php">Historique</a>
                            <a href="/housing-cm/user/visits.php">Visites</a>
                            <a href="/housing-cm/messages/inbox.php">Messages</a>
                            <a href="/housing-cm/user/reports.php">Signalements</a>
                        </div>
                    </details>

                    <?php if ($userRole === 'admin'): ?>
                        <details class="nav-dropdown">
                            <summary>Administration</summary>
                            <div class="nav-dropdown-menu">
                                <a href="/housing-cm/admin/dashboard.php">Vue generale</a>
                                <a href="/housing-cm/admin/statistics.php">Statistiques</a>
                                <a href="/housing-cm/admin/users.php">Utilisateurs</a>
                                <a href="/housing-cm/admin/properties.php">Annonces</a>
                                <a href="/housing-cm/admin/reports.php">Signalements</a>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>

                <div class="nav-account">
                    <span class="nav-user"><?php echo escape($loggedInUser['full_name']); ?></span>
                    <a class="btn btn-nav" href="/housing-cm/auth/logout.php">Deconnexion</a>
                </div>
            <?php else: ?>
                <div class="nav-account">
                    <a class="nav-link" href="/housing-cm/auth/login.php">Connexion</a>
                    <a class="btn btn-nav" href="/housing-cm/auth/register.php">Inscription</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
