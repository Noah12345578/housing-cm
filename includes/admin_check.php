<?php

require_once __DIR__ . '/auth_check.php';

$adminUser = currentUser();

if (($adminUser['role'] ?? '') !== 'admin') {
    setFlashMessage('Acces reserve a l administrateur.', 'error');
    redirect('/housing-cm/user/dashboard.php');
}
