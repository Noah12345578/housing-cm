<?php
require_once __DIR__ . '/includes/functions.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-card">
                <h1>Trouver un logement fiable au Cameroun devient plus simple.</h1>
                <p>
                    Recherche par ville, quartier, budget et type de logement.
                    Compare les annonces, consulte les details utiles et contacte rapidement
                    un proprietaire ou un agent immobilier.
                </p>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="/housing-cm/properties/search.php">Rechercher un logement</a>
                    <a class="btn btn-secondary" href="/housing-cm/auth/register.php">Publier une annonce</a>
                </div>
            </div>

            <div class="search-card">
                <h2>Recherche rapide</h2>
                <form class="search-form" action="/housing-cm/properties/search.php" method="GET">
                    <div>
                        <label for="city">Ville</label>
                        <input type="text" id="city" name="city" placeholder="Exemple : Douala">
                    </div>

                    <div>
                        <label for="neighborhood">Quartier</label>
                        <input type="text" id="neighborhood" name="neighborhood" placeholder="Exemple : Makepe">
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="property_type">Type</label>
                            <select id="property_type" name="property_type">
                                <option value="">Tous les types</option>
                                <option value="chambre">Chambre</option>
                                <option value="studio">Studio</option>
                                <option value="appartement">Appartement</option>
                                <option value="maison">Maison</option>
                                <option value="mini_cite">Mini-cite</option>
                                <option value="terrain">Terrain</option>
                            </select>
                        </div>

                        <div>
                            <label for="max_price">Budget max</label>
                            <input type="number" id="max_price" name="max_price" placeholder="Exemple : 150000">
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Lancer la recherche</button>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Pourquoi utiliser Housing CM ?</h2>

            <div class="cards-3">
                <article class="feature-card">
                    <h3>Informations plus claires</h3>
                    <p>Chaque annonce doit presenter les elements essentiels : prix, quartier, type de logement, pieces et equipements.</p>
                </article>

                <article class="feature-card">
                    <h3>Gain de temps</h3>
                    <p>Les utilisateurs filtrent selon leurs besoins avant de se deplacer, ce qui reduit les visites inutiles.</p>
                </article>

                <article class="feature-card">
                    <h3>Contact direct</h3>
                    <p>Le site est pense pour faciliter les echanges entre client, proprietaire et agent immobilier.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
