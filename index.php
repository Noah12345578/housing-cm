<?php
require_once __DIR__ . '/includes/functions.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-card">
                <span class="eyebrow">Plateforme immobiliere adaptee au Cameroun</span>
                <h1>Trouver un logement fiable au Cameroun devient plus simple.</h1>
                <p>
                    Recherche par ville, quartier, budget et type de logement.
                    Compare les annonces, consulte les details utiles et contacte rapidement
                    un proprietaire ou un agent immobilier.
                </p>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>Recherche ciblee</strong>
                        <span>Ville, quartier, budget, type</span>
                    </div>
                    <div class="hero-stat">
                        <strong>Contact direct</strong>
                        <span>Messages, visites, signalements</span>
                    </div>
                </div>

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
            <p class="section-subtitle">Une approche plus claire, plus locale et plus pratique pour chercher ou proposer un logement.</p>

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

    <section class="section section-soft">
        <div class="container">
            <h2 class="section-title">Categories les plus recherchees</h2>
            <div class="category-grid">
                <article class="category-card">
                    <strong>Chambres</strong>
                    <span>Pour petits budgets et recherches rapides.</span>
                </article>
                <article class="category-card">
                    <strong>Studios</strong>
                    <span>Pratiques pour etudiants et jeunes actifs.</span>
                </article>
                <article class="category-card">
                    <strong>Appartements</strong>
                    <span>Pour familles, couples ou collocations.</span>
                </article>
                <article class="category-card">
                    <strong>Maisons</strong>
                    <span>Espaces plus grands pour location ou vente.</span>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container trust-strip">
            <div class="trust-item">
                <strong>Fiches detaillees</strong>
                <span>Pour limiter les deplacements inutiles.</span>
            </div>
            <div class="trust-item">
                <strong>Messagerie simple</strong>
                <span>Pour discuter avant toute visite.</span>
            </div>
            <div class="trust-item">
                <strong>Signalement</strong>
                <span>Pour reduire les annonces douteuses.</span>
            </div>
            <div class="trust-item">
                <strong>Demandes de visite</strong>
                <span>Pour mieux organiser les rencontres.</span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Comment ca marche ?</h2>
            <div class="steps-grid">
                <article class="step-card">
                    <span class="step-number">1</span>
                    <h3>Recherche</h3>
                    <p>Le client filtre selon la ville, le quartier, le budget et le type de logement.</p>
                </article>
                <article class="step-card">
                    <span class="step-number">2</span>
                    <h3>Comparaison</h3>
                    <p>Il consulte les details, les favoris et les informations utiles avant de se deplacer.</p>
                </article>
                <article class="step-card">
                    <span class="step-number">3</span>
                    <h3>Prise de contact</h3>
                    <p>Il envoie un message, demande une visite et clarifie les modalites avec le responsable.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
