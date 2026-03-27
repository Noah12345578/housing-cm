<?php

$host = 'your_host';
$dbName = 'your_database_name';
$dbUser = 'your_database_user';
$dbPass = 'your_database_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    die('Erreur de connexion a la base de donnees : ' . $exception->getMessage());
}
