<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/role_check.php';

$oldInput = $_SESSION['old_property_input'] ?? [];
unset($_SESSION['old_property_input']);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container">
    <section class="page-header">
        <h1>Publier une annonce</h1>
        <p>Renseigne les informations essentielles du logement pour permettre une recherche claire et fiable.</p>
    </section>

    <section class="auth-card">
        <form class="auth-form" action="/housing-cm/actions/create_property_action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo escape(csrfToken()); ?>">
            <div>
                <label for="title">Titre de l annonce</label>
                <input type="text" id="title" name="title" value="<?php echo escape($oldInput['title'] ?? ''); ?>" required>
            </div>

            <div>
                <label for="description">Description complete</label>
                <textarea id="description" name="description" rows="5" required><?php echo escape($oldInput['description'] ?? ''); ?></textarea>
            </div>

            <div class="grid-2">
                <div>
                    <label for="property_type">Type de logement</label>
                    <select id="property_type" name="property_type" required>
                        <option value="">Choisir un type</option>
                        <option value="chambre" <?php echo (($oldInput['property_type'] ?? '') === 'chambre') ? 'selected' : ''; ?>>Chambre</option>
                        <option value="studio" <?php echo (($oldInput['property_type'] ?? '') === 'studio') ? 'selected' : ''; ?>>Studio</option>
                        <option value="appartement" <?php echo (($oldInput['property_type'] ?? '') === 'appartement') ? 'selected' : ''; ?>>Appartement</option>
                        <option value="maison" <?php echo (($oldInput['property_type'] ?? '') === 'maison') ? 'selected' : ''; ?>>Maison</option>
                        <option value="mini_cite" <?php echo (($oldInput['property_type'] ?? '') === 'mini_cite') ? 'selected' : ''; ?>>Mini-cite</option>
                        <option value="terrain" <?php echo (($oldInput['property_type'] ?? '') === 'terrain') ? 'selected' : ''; ?>>Terrain</option>
                        <option value="autre" <?php echo (($oldInput['property_type'] ?? '') === 'autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="listing_type">Statut de l annonce</label>
                    <select id="listing_type" name="listing_type" required>
                        <option value="">Choisir</option>
                        <option value="location" <?php echo (($oldInput['listing_type'] ?? '') === 'location') ? 'selected' : ''; ?>>A louer</option>
                        <option value="vente" <?php echo (($oldInput['listing_type'] ?? '') === 'vente') ? 'selected' : ''; ?>>A vendre</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="property_style">Style</label>
                    <select id="property_style" name="property_style" required>
                        <option value="classique" <?php echo (($oldInput['property_style'] ?? 'classique') === 'classique') ? 'selected' : ''; ?>>Classique</option>
                        <option value="moderne" <?php echo (($oldInput['property_style'] ?? '') === 'moderne') ? 'selected' : ''; ?>>Moderne</option>
                    </select>
                </div>

                <div>
                    <label for="price">Prix en FCFA</label>
                    <input type="number" id="price" name="price" min="0" value="<?php echo escape($oldInput['price'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="region_name">Region</label>
                    <input type="text" id="region_name" name="region_name" value="<?php echo escape($oldInput['region_name'] ?? ''); ?>" required>
                </div>

                <div>
                    <label for="city_name">Ville</label>
                    <input type="text" id="city_name" name="city_name" value="<?php echo escape($oldInput['city_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="district_name">Arrondissement</label>
                    <input type="text" id="district_name" name="district_name" value="<?php echo escape($oldInput['district_name'] ?? ''); ?>">
                </div>

                <div>
                    <label for="neighborhood_name">Quartier</label>
                    <input type="text" id="neighborhood_name" name="neighborhood_name" value="<?php echo escape($oldInput['neighborhood_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div>
                <label for="specific_area">Zone precise</label>
                <input type="text" id="specific_area" name="specific_area" value="<?php echo escape($oldInput['specific_area'] ?? ''); ?>" placeholder="Exemple : Carrefour, derriere le marche, entree goudronnee">
            </div>

            <div>
                <label for="property_images">Photos du logement</label>
                <input type="file" id="property_images" name="property_images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                <p class="helper-text">Tu peux ajouter jusqu a 6 photos. La premiere sera utilisee comme image principale.</p>
            </div>

            <div class="grid-2">
                <div>
                    <label for="rooms">Nombre de pieces</label>
                    <input type="number" id="rooms" name="rooms" min="0" value="<?php echo escape($oldInput['rooms'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="bedrooms">Nombre de chambres</label>
                    <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo escape($oldInput['bedrooms'] ?? '0'); ?>">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="living_rooms">Nombre de salons</label>
                    <input type="number" id="living_rooms" name="living_rooms" min="0" value="<?php echo escape($oldInput['living_rooms'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="bathrooms">Nombre de douches</label>
                    <input type="number" id="bathrooms" name="bathrooms" min="0" value="<?php echo escape($oldInput['bathrooms'] ?? '0'); ?>">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="kitchens">Nombre de cuisines</label>
                    <input type="number" id="kitchens" name="kitchens" min="0" value="<?php echo escape($oldInput['kitchens'] ?? '0'); ?>">
                </div>

                <div>
                    <label for="kitchen_type">Type de cuisine</label>
                    <select id="kitchen_type" name="kitchen_type">
                        <option value="interne" <?php echo (($oldInput['kitchen_type'] ?? 'interne') === 'interne') ? 'selected' : ''; ?>>Interne</option>
                        <option value="externe" <?php echo (($oldInput['kitchen_type'] ?? '') === 'externe') ? 'selected' : ''; ?>>Externe</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="surface_area">Superficie en m2</label>
                    <input type="number" step="0.01" id="surface_area" name="surface_area" min="0" value="<?php echo escape($oldInput['surface_area'] ?? ''); ?>">
                </div>

                <div>
                    <label for="security_level">Niveau de securite</label>
                    <select id="security_level" name="security_level">
                        <option value="faible" <?php echo (($oldInput['security_level'] ?? '') === 'faible') ? 'selected' : ''; ?>>Faible</option>
                        <option value="moyen" <?php echo (($oldInput['security_level'] ?? 'moyen') === 'moyen') ? 'selected' : ''; ?>>Moyen</option>
                        <option value="bon" <?php echo (($oldInput['security_level'] ?? '') === 'bon') ? 'selected' : ''; ?>>Bon</option>
                        <option value="eleve" <?php echo (($oldInput['security_level'] ?? '') === 'eleve') ? 'selected' : ''; ?>>Eleve</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="road_access">Acces a la route</label>
                    <select id="road_access" name="road_access">
                        <option value="mauvais" <?php echo (($oldInput['road_access'] ?? '') === 'mauvais') ? 'selected' : ''; ?>>Mauvais</option>
                        <option value="moyen" <?php echo (($oldInput['road_access'] ?? 'moyen') === 'moyen') ? 'selected' : ''; ?>>Moyen</option>
                        <option value="bon" <?php echo (($oldInput['road_access'] ?? '') === 'bon') ? 'selected' : ''; ?>>Bon</option>
                    </select>
                </div>

                <div>
                    <label for="is_furnished">Logement meuble</label>
                    <select id="is_furnished" name="is_furnished">
                        <option value="0" <?php echo (($oldInput['is_furnished'] ?? '0') === '0') ? 'selected' : ''; ?>>Non</option>
                        <option value="1" <?php echo (($oldInput['is_furnished'] ?? '') === '1') ? 'selected' : ''; ?>>Oui</option>
                    </select>
                </div>
            </div>

            <div class="checkbox-grid">
                <label class="checkbox-item"><input type="checkbox" name="has_water" value="1" <?php echo !empty($oldInput['has_water']) ? 'checked' : ''; ?>> Eau disponible</label>
                <label class="checkbox-item"><input type="checkbox" name="has_electricity" value="1" <?php echo !empty($oldInput['has_electricity']) ? 'checked' : ''; ?>> Electricite disponible</label>
                <label class="checkbox-item"><input type="checkbox" name="has_parking" value="1" <?php echo !empty($oldInput['has_parking']) ? 'checked' : ''; ?>> Parking</label>
                <label class="checkbox-item"><input type="checkbox" name="has_fence" value="1" <?php echo !empty($oldInput['has_fence']) ? 'checked' : ''; ?>> Cloture</label>
                <label class="checkbox-item"><input type="checkbox" name="near_school" value="1" <?php echo !empty($oldInput['near_school']) ? 'checked' : ''; ?>> Proche d une ecole</label>
                <label class="checkbox-item"><input type="checkbox" name="near_market" value="1" <?php echo !empty($oldInput['near_market']) ? 'checked' : ''; ?>> Proche du marche</label>
                <label class="checkbox-item"><input type="checkbox" name="near_hospital" value="1" <?php echo !empty($oldInput['near_hospital']) ? 'checked' : ''; ?>> Proche d un hopital</label>
                <label class="checkbox-item"><input type="checkbox" name="near_university" value="1" <?php echo !empty($oldInput['near_university']) ? 'checked' : ''; ?>> Proche d une universite</label>
                <label class="checkbox-item"><input type="checkbox" name="near_transport" value="1" <?php echo !empty($oldInput['near_transport']) ? 'checked' : ''; ?>> Proche du transport</label>
            </div>

            <button class="btn btn-primary" type="submit">Enregistrer l annonce</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
