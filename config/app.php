<?php

$host = $_SERVER['HTTP_HOST'] ?? '';

if ($host === 'localhost' || $host === '127.0.0.1') {
    define('APP_BASE_PATH', '/housing-cm/');
} else {
    define('APP_BASE_PATH', '/');
}
