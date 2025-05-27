<?php
// Datenbank-Konfiguration
define('DB_HOST', '{{DB_HOST}}');
define('DB_NAME', '{{DB_NAME}}');
define('DB_USER', '{{DB_USER}}');
define('DB_PASS', '{{DB_PASS}}');
define('DB_CHARSET', 'utf8mb4');

// API-Key für Update-Skript
define('ADMIN_API_KEY', '{{ADMIN_API_KEY}}');

// Blog-Konfiguration
define('BLOG_TITLE', '{{BLOG_TITLE}}');

// URL-Generierung
define('URL_LENGTH', 8);
define('HASHIDS_SALT', 'painblog');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Zeitzone
date_default_timezone_set('Europe/Berlin');

// Fehlerberichterstattung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbankverbindung
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
} 