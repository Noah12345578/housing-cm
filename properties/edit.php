<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';
require_once __DIR__ . '/../config/database.php';

$user = currentUser();
$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($propertyId <= 0) {
    renderErrorPage(
        'Annonce introuvable',
        'Nous ne retrouvons pas l annonce que tu souhaites modifier.',
        404,
        [
            ['label' => 'Mes annonces', 'url' => url('/properties/my-properties.php'), 'class' => 'btn btn-primary'],
            ['label' => 'Publier une annonce', 'url' => url('/properties/create.php'), 'class' => 'btn btn-secondary'],
        ]
    );
}

$statement = $pdo->prepare(
    'SELECT
        properties.*,
        locations.region_name,
        locations.city_name,
        locations.district_name,
        locations.neighborhood_name,
        locations.specific_area
     FROM properties
     INNER JOIN locations ON properties.location_id = locations.id
     WHERE properties.id = :id AND properties.user_id = :user_id
     LIMIT 1'
);

$statement->execute([
    'id' => $propertyId,
    'user_id' => $user['id'],
]);

$property = $statement->fetch();

if (!$property) {
    renderErrorPage(
        'Modification impossible',
        'Cette annonce n existe pas dans ton espace ou tu n as pas l autorisation de la modifier.',
        403,
        [
            ['label' => 'Retour a mes annonces', 'url' => url('/properties/my-properties.php'), 'class' => 'btn btn-primary'],
            ['label' => 'Accueil', 'url' => url('/index.php'), 'class' => 'btn btn-secondary'],
        ]
    );
}

$oldInput = $_SESSION['old_property_input'] ?? [];
unset($_SESSION['old_property_input']);

$formData = array_merge($property, $oldInput);

$imagesStatement = $pdo->prepare(
    'SELECT id, image_path, is_main
     FROM property_images
     WHERE property_id = :property_id
     ORDER BY is_main DESC, id ASC'
);

$imagesStatement->execute(['property_id' => $propertyId]);
$propertyImages = $imagesStatement->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Modifier une annonce</h1>
        <p>Met a jour les informations de ton logement sans recreer une nouvelle annonce.</p>
    </section>

    <section class="auth-card">
        <?php if ($propertyImages): ?>
            <div class="edit-gallery">
                <?php foreach ($propertyImages as $image): ?>
                    <article class="edit-gallery-item">
                        <img
                            class="edit-gallery-image"
                            src="<?php echo escape(url($image['image_path'])); ?>"
                            alt="Photo de l annonce"
                        >
                        <div class="card-actions">
                            <?php if ((int) $image['is_main'] === 1): ?>
                                <span class="badge">Image principale</span>
                            <?php else: ?>
                                <form action="<?php echo escape(url('/actions/set_property_main_image_action.php')); ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                    <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                    <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                                    <button class="btn btn-secondary" type="submit">Definir principale</button>
                                </form>
                            <?php endif; ?>

                            <form action="<?php echo escape(url('/actions/delete_property_image_action.php')); ?>" method="POST" onsubmit="return confirm('Supprimer cette image ?');">
                                <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
                                <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">
                                <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                                <button class="btn btn-danger" type="submit">Supprimer image</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" action="/housing-cm/actions/update_property_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
            <input type="hidden" name="property_id" value="<?php echo (int) $property['id']; ?>">

            <div>
                <label for="title">Titre de l annonce</label>
                <input type="text" id="title" name="title" value="<?php echo escape($formData['title'] ?? ''); ?>" required>
            </div>

            <div>
                <label for="description">Description complete</label>
                <textarea id="description" name="description" rows="5" required><?php echo escape($formData['description'] ?? ''); ?></textarea>
            </div>

            <div class="grid-2">
                <div>
                    <label for="property_type">Type de logement</label>
                    <select id="property_type" name="property_type" required>
                        <option value="chambre" <?php echo (($formData['property_type'] ?? '') === 'chambre') ? 'selected' : ''; ?>>Chambre</option>
                        <option value="studio" <?php echo (($formData['property_type'] ?? '') === 'studio') ? 'selected' : ''; ?>>Studio</option>
                        <option value="appartement" <?php echo (($formData['property_type'] ?? '') === 'appartement') ? 'selected' : ''; ?>>Appartement</option>
                        <option value="maison" <?php echo (($formData['property_type'] ?? '') === 'maison') ? 'selected' : ''; ?>>Maison</option>
                        <option value="mini_cite" <?php echo (($formData['property_type'] ?? '') === 'mini_cite') ? 'selected' : ''; ?>>Mini-cite</option>
                        <option value="terrain" <?php echo (($formData['property_type'] ?? '') === 'terrain') ? 'selected' : ''; ?>>Terrain</option>
                        <option value="autre" <?php echo (($formData['property_type'] ?? '') === 'autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="listing_type">Statut de l annonce</label>
                    <select id="listing_type" name="listing_type" required>
                        <option value="location" <?php echo (($formData['listing_type'] ?? '') === 'location') ? 'selected' : ''; ?>>A louer</option>
                        <option value="vente" <?php echo (($formData['listing_type'] ?? '') === 'vente') ? 'selected' : ''; ?>>A vendre</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="property_style">Style</label>
                    <select id="property_style" name="property_style" required>
                        <option value="classique" <?php echo (($formData['property_style'] ?? 'classique') === 'classique') ? 'selected' : ''; ?>>Classique</option>
                        <option value="moderne" <?php echo (($formData['property_style'] ?? '') === 'moderne') ? 'selected' : ''; ?>>Moderne</option>
                    </select>
                </div>

                <div>
                    <label for="price">Prix en FCFA</label>
                    <input type="number" id="price" name="price" min="0" value="<?php echo escape($formData['price'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="region_name">Region</label>
                    <input type="text" id="region_name" name="region_name" value="<?php echo escape($formData['region_name'] ?? ''); ?>" required>
                </div>

                <div>
                    <label for="city_name">Ville</label>
                    <input type="text" id="city_name" name="city_name" value="<?php echo escape($formData['city_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="district_name">Arrondissement</label>
                    <input type="text" id="district_name" name="district_name" value="<?php echo escape($formData['district_name'] ?? ''); ?>">
                </div>

                <div>
                    <label for="neighborhood_name">Quartier</label>
                    <input type="text" id="neighborhood_name" name="neighborhood_name" value="<?php echo escape($formData['neighborhood_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div>
                <label for="specific_area">Zone precise</label>
                <input type="text" id="specific_area" name="specific_area" value="<?php echo escape($formData['specific_area'] ?? ''); ?>">
            </div>

            <div>
                <label for="property_images">Ajouter de nouvelles photos</label>
                <input type="file" id="property_images" name="property_images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                <p class="helper-text">Tu peux completer ta galerie jusqu a 6 photos au total.</p>
            </div>

            <div class="grid-2">
                <div>
                    <label for="rooms">Nombre de pieces</label>
                    <input type="number" id="rooms" name="rooms" min="0" value="<?php echo escape($formData['rooms'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="bedrooms">Nombre de chambres</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo escape($formData['bedrooms'] ?? '0'); ?>">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="living_rooms">Nombre de salons</label>
                    <input type="number" id="living_rooms" name="living_rooms" min="0" value="<?php echo escape($formData['living_rooms'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="bathrooms">Nombre de douches</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" value="<?php echo escape($formData['bathrooms'] ?? '0'); ?>">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="kitchens">Nombre de cuisines</label>
                    <input type="number" id="kitchens" name="kitchens" min="0" value="<?php echo escape($formData['kitchens'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="kitchen_type">Type de cuisine</label>
                    <select id="kitchen_type" name="kitchen_type">
                        <option value="interne" <?php echo (($formData['kitchen_type'] ?? 'interne') === 'interne') ? 'selected' : ''; ?>>Interne</option>
                        <option value="externe" <?php echo (($formData['kitchen_type'] ?? '') === 'externe') ? 'selected' : ''; ?>>Externe</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="surface_area">Superficie en m2</label>
                    <input type="number" step="0.01" id="surface_area" name="surface_area" min="0" value="<?php echo escape($formData['surface_area'] ?? ''); ?>">
                </div>

                <div>
                    <label for="security_level">Niveau de securite</label>
                    <select id="security_level" name="security_level">
                        <option value="faible" <?php echo (($formData['security_level'] ?? '') === 'faible') ? 'selected' : ''; ?>>Faible</option>
                        <option value="moyen" <?php echo (($formData['security_level'] ?? 'moyen') === 'moyen') ? 'selected' : ''; ?>>Moyen</option>
                        <option value="bon" <?php echo (($formData['security_level'] ?? '') === 'bon') ? 'selected' : ''; ?>>Bon</option>
                        <option value="eleve" <?php echo (($formData['security_level'] ?? '') === 'eleve') ? 'selected' : ''; ?>>Eleve</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="road_access">Acces a la route</label>
                    <select id="road_access" name="road_access">
                        <option value="mauvais" <?php echo (($formData['road_access'] ?? '') === 'mauvais') ? 'selected' : ''; ?>>Mauvais</option>
                        <option value="moyen" <?php echo (($formData['road_access'] ?? 'moyen') === 'moyen') ? 'selected' : ''; ?>>Moyen</option>
                        <option value="bon" <?php echo (($formData['road_access'] ?? '') === 'bon') ? 'selected' : ''; ?>>Bon</option>
                    </select>
                </div>

                <div>
                    <label for="is_furnished">Logement meuble</label>
                    <select id="is_furnished" name="is_furnished">
                        <option value="0" <?php echo (($formData['is_furnished'] ?? '0') == '0') ? 'selected' : ''; ?>>Non</option>
                        <option value="1" <?php echo (($formData['is_furnished'] ?? '') == '1') ? 'selected' : ''; ?>>Oui</option>
                    </select>
                </div>
            </div>

            <div class="checkbox-grid">
                <label class="checkbox-item"><input type="checkbox" name="has_water" value="1" <?php echo !empty($formData['has_water']) ? 'checked' : ''; ?>> Eau disponible</label>
                <label class="checkbox-item"><input type="checkbox" name="has_electricity" value="1" <?php echo !empty($formData['has_electricity']) ? 'checked' : ''; ?>> Electricite disponible</label>
                <label class="checkbox-item"><input type="checkbox" name="has_parking" value="1" <?php echo !empty($formData['has_parking']) ? 'checked' : ''; ?>> Parking</label>
                <label class="checkbox-item"><input type="checkbox" name="has_fence" value="1" <?php echo !empty($formData['has_fence']) ? 'checked' : ''; ?>> Cloture</label>
                <label class="checkbox-item"><input type="checkbox" name="near_school" value="1" <?php echo !empty($formData['near_school']) ? 'checked' : ''; ?>> Proche d une ecole</label>
                <label class="checkbox-item"><input type="checkbox" name="near_market" value="1" <?php echo !empty($formData['near_market']) ? 'checked' : ''; ?>> Proche du marche</label>
                <label class="checkbox-item"><input type="checkbox" name="near_hospital" value="1" <?php echo !empty($formData['near_hospital']) ? 'checked' : ''; ?>> Proche d un hopital</label>
                <label class="checkbox-item"><input type="checkbox" name="near_university" value="1" <?php echo !empty($formData['near_university']) ? 'checked' : ''; ?>> Proche d une universite</label>
                <label class="checkbox-item"><input type="checkbox" name="near_transport" value="1" <?php echo !empty($formData['near_transport']) ? 'checked' : ''; ?>> Proche du transport</label>
            </div>

            <button class="btn btn-primary" type="submit">Mettre a jour l annonce</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
