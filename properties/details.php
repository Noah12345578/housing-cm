<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($propertyId <= 0) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/search.php');
}

$statement = $pdo->prepare(
    'SELECT
        properties.*,
        property_images.image_path,
        locations.region_name,
        locations.city_name,
        locations.district_name,
        locations.neighborhood_name,
        locations.specific_area,
        users.full_name,
        users.phone,
        users.email,
        users.role
     FROM properties
     INNER JOIN locations ON properties.location_id = locations.id
     INNER JOIN users ON properties.user_id = users.id
     LEFT JOIN property_images ON properties.id = property_images.property_id AND property_images.is_main = 1
     WHERE properties.id = :id
     LIMIT 1'
);

$statement->execute(['id' => $propertyId]);
$property = $statement->fetch();

if (!$property) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('/housing-cm/properties/search.php');
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<?php
$isFavorite = false;

if (isLoggedIn()) {
    $favoriteStatement = $pdo->prepare('SELECT id FROM favorites WHERE user_id = :user_id AND property_id = :property_id LIMIT 1');
    $favoriteStatement->execute([
        'user_id' => currentUser()['id'],
        'property_id' => $property['id'],
    ]);
    $isFavorite = (bool) $favoriteStatement->fetch();
}
?>

<main class="container">
    <section class="page-header">
        <h1><?php echo escape($property['title']); ?></h1>
        <p>
            <?php echo escape($property['neighborhood_name']); ?>,
            <?php echo escape($property['city_name']); ?>,
            <?php echo escape($property['region_name']); ?>
        </p>
    </section>

    <section class="details-layout">
        <article class="detail-card">
            <img
                class="detail-image"
                src="<?php echo escape($property['image_path'] ?: '/housing-cm/assets/images/default-property.svg'); ?>"
                alt="Image du logement <?php echo escape($property['title']); ?>"
            >

            <div class="detail-highlight">
                <span class="badge"><?php echo escape($property['listing_type']); ?></span>
                <span class="badge"><?php echo escape($property['property_type']); ?></span>
                <span class="badge"><?php echo escape($property['status']); ?></span>
            </div>

            <p class="property-price"><?php echo escape(formatPrice($property['price'])); ?></p>

            <h2>Description</h2>
            <p><?php echo nl2br(escape($property['description'])); ?></p>

            <h2>Caracteristiques</h2>
            <div class="details-grid">
                <div class="detail-item"><strong>Style :</strong> <?php echo escape($property['property_style']); ?></div>
                <div class="detail-item"><strong>Pieces :</strong> <?php echo (int) $property['rooms']; ?></div>
                <div class="detail-item"><strong>Chambres :</strong> <?php echo (int) $property['bedrooms']; ?></div>
                <div class="detail-item"><strong>Salons :</strong> <?php echo (int) $property['living_rooms']; ?></div>
                <div class="detail-item"><strong>Douches :</strong> <?php echo (int) $property['bathrooms']; ?></div>
                <div class="detail-item"><strong>Cuisines :</strong> <?php echo (int) $property['kitchens']; ?></div>
                <div class="detail-item"><strong>Type de cuisine :</strong> <?php echo escape($property['kitchen_type']); ?></div>
                <div class="detail-item"><strong>Meuble :</strong> <?php echo $property['is_furnished'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Superficie :</strong> <?php echo $property['surface_area'] !== null ? escape($property['surface_area']) . ' m2' : 'Non precisee'; ?></div>
                <div class="detail-item"><strong>Securite :</strong> <?php echo escape($property['security_level']); ?></div>
                <div class="detail-item"><strong>Acces route :</strong> <?php echo escape($property['road_access']); ?></div>
                <div class="detail-item"><strong>Zone precise :</strong> <?php echo escape($property['specific_area'] ?? 'Non precisee'); ?></div>
            </div>

            <h2>Equipements et proximite</h2>
            <div class="details-grid">
                <div class="detail-item"><strong>Eau :</strong> <?php echo $property['has_water'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Electricite :</strong> <?php echo $property['has_electricity'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Parking :</strong> <?php echo $property['has_parking'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Cloture :</strong> <?php echo $property['has_fence'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Proche ecole :</strong> <?php echo $property['near_school'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Proche marche :</strong> <?php echo $property['near_market'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Proche hopital :</strong> <?php echo $property['near_hospital'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Proche universite :</strong> <?php echo $property['near_university'] ? 'Oui' : 'Non'; ?></div>
                <div class="detail-item"><strong>Proche transport :</strong> <?php echo $property['near_transport'] ? 'Oui' : 'Non'; ?></div>
            </div>
        </article>

        <aside class="detail-card">
            <h2>Responsable du bien</h2>
            <p><strong>Nom :</strong> <?php echo escape($property['full_name']); ?></p>
            <p><strong>Role :</strong> <?php echo escape($property['role']); ?></p>
            <p><strong>Telephone :</strong> <?php echo escape($property['phone']); ?></p>
            <p><strong>Email :</strong> <?php echo escape($property['email']); ?></p>

            <?php if (isLoggedIn() && (int) currentUser()['id'] !== (int) $property['user_id']): ?>
                <hr class="separator">
                <form action="/housing-cm/actions/favorite_action.php" method="POST" class="favorite-form">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                    <input type="hidden" name="redirect_to" value="/housing-cm/properties/details.php?id=<?php echo (int) $property['id']; ?>">
                    <button class="btn btn-secondary" type="submit">
                        <?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>
                    </button>
                </form>

                <hr class="separator">
                <h2>Contacter</h2>
                <form class="auth-form" action="/housing-cm/actions/send_message_action.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                    <input type="hidden" name="receiver_id" value="<?php echo (int) $property['user_id']; ?>">

                    <div>
                        <label for="message">Votre message</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Bonjour, je suis interesse par ce logement. Est-il toujours disponible ?"></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Envoyer le message</button>
                </form>

                <hr class="separator">
                <h2>Demander une visite</h2>
                <form class="auth-form" action="/housing-cm/actions/request_visit_action.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                    <input type="hidden" name="owner_id" value="<?php echo (int) $property['user_id']; ?>">

                    <div>
                        <label for="preferred_date">Date et heure souhaitees</label>
                        <input type="datetime-local" id="preferred_date" name="preferred_date" required>
                    </div>

                    <div>
                        <label for="visit_message">Message complementaire</label>
                        <textarea id="visit_message" name="message" rows="4" placeholder="Bonjour, je souhaite visiter ce logement si possible en fin de journee."></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Envoyer la demande</button>
                </form>

                <hr class="separator">
                <h2>Signaler cette annonce</h2>
                <form class="auth-form" action="/housing-cm/actions/report_action.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">

                    <div>
                        <label for="reason">Motif</label>
                        <select id="reason" name="reason" required>
                            <option value="">Choisir un motif</option>
                            <option value="arnaque_suspectee">Arnaque suspectee</option>
                            <option value="fausses_informations">Fausses informations</option>
                            <option value="logement_deja_pris">Logement deja pris</option>
                            <option value="prix_trompeur">Prix trompeur</option>
                            <option value="contenu_inapproprie">Contenu inapproprie</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div>
                        <label for="report_description">Description</label>
                        <textarea id="report_description" name="description" rows="4" placeholder="Explique clairement pourquoi cette annonce te semble douteuse."></textarea>
                    </div>

                    <button class="btn btn-secondary" type="submit">Envoyer le signalement</button>
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <hr class="separator">
                <p class="helper-text">Connecte-toi pour envoyer un message, demander une visite ou signaler une annonce.</p>
                <a class="btn btn-primary" href="/housing-cm/auth/login.php">Se connecter</a>
            <?php endif; ?>

            <a class="btn btn-primary" href="/housing-cm/properties/search.php">Retour a la recherche</a>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
