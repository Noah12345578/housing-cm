<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$flashMessage = getFlashMessage();

if (!defined('APP_OUTPUT_BUFFER_ACTIVE')) {
    define('APP_OUTPUT_BUFFER_ACTIVE', true);
    ob_start(static function (string $buffer): string {
        return str_replace('/housing-cm/', basePath(), $buffer);
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housing CM</title>
    <link rel="stylesheet" href="/housing-cm/assets/css/style.css">
</head>
<body>
<?php if ($flashMessage): ?>
    <div class="flash-message flash-<?php echo escape($flashMessage['type'] ?? 'success'); ?>">
        <div class="container">
            <?php echo escape($flashMessage['text'] ?? ''); ?>
        </div>
    </div>
<?php endif; ?>
