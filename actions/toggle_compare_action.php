<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/housing-cm/properties/search.php');
}

verifyCsrfToken($_POST['csrf_token'] ?? null);

$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$redirectTo = trim($_POST['redirect_to'] ?? '/housing-cm/properties/search.php');

if ($propertyId <= 0) {
    setFlashMessage('Annonce introuvable pour la comparaison.', 'error');
    redirect('/housing-cm/properties/search.php');
}

$compareIds = comparePropertyIds();

if (in_array($propertyId, $compareIds, true)) {
    $compareIds = array_values(array_filter(
        $compareIds,
        static fn (int $id): bool => $id !== $propertyId
    ));
    $_SESSION['compare_properties'] = $compareIds;
    setFlashMessage('Le logement a ete retire de la comparaison.');
} else {
    if (count($compareIds) >= 3) {
        setFlashMessage('Tu peux comparer au maximum 3 logements a la fois.', 'error');
        redirect($redirectTo);
    }

    $compareIds[] = $propertyId;
    $_SESSION['compare_properties'] = array_values(array_unique($compareIds));
    setFlashMessage('Le logement a ete ajoute a la comparaison.');
}

redirect($redirectTo === '' ? '/housing-cm/properties/search.php' : $redirectTo);
