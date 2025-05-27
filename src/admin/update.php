<?php
require_once '../_config/config.php';

// Debug-Ausgabe
$debug = [];
$debug[] = "Request Method: " . $_SERVER['REQUEST_METHOD'];
$debug[] = "All Headers: " . print_r(getallheaders(), true);
$debug[] = "Raw Authorization Header: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'nicht gesetzt');
$debug[] = "Authorization Header (lowercase): " . ($_SERVER['http_authorization'] ?? 'nicht gesetzt');
$debug[] = "All Server Variables: " . print_r($_SERVER, true);

// Nur POST-Requests zulassen
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Nur POST-Requests erlaubt.\n";
    echo "Debug:\n" . implode("\n", $debug);
    exit;
}

// API-Key aus Authorization Header prüfen
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? '';
$debug[] = "Auth Header Value: " . $auth_header;

if (empty($auth_header) || !preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
    $debug[] = "Auth Header ungültig oder leer";
    $debug[] = "Regex Match Result: " . print_r($matches, true);
    http_response_code(401);
    echo "Authorization Header fehlt oder ist ungültig.\n";
    echo "Debug:\n" . implode("\n", $debug);
    exit;
}

$api_key = $matches[1];
$debug[] = "Extracted API Key: " . $api_key;
$debug[] = "Expected API Key: " . ADMIN_API_KEY;

if ($api_key !== ADMIN_API_KEY) {
    $debug[] = "API Key stimmt nicht überein";
    http_response_code(403);
    echo "Ungültiger API-Key.\n";
    echo "Debug:\n" . implode("\n", $debug);
    exit;
}

$migrationsDir = __DIR__ . '/../Migrations';

// Stelle sicher, dass die migrations-Tabelle existiert
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Alle SQL-Dateien im Migrationsordner auflisten
$migrationFiles = glob($migrationsDir . '/*.sql');

// Bereits angewendete Migrationen abfragen
$applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

// Migrationen sortieren (nach Dateiname)
natcasesort($migrationFiles);

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    if (!in_array($filename, $applied)) {
        echo "Wende Migration an: $filename ... ";
        $sql = file_get_contents($file);
        try {
            $pdo->exec($sql);
            $stmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');
            $stmt->execute([$filename]);
            echo "OK\n";
        } catch (PDOException $e) {
            echo "FEHLER: " . $e->getMessage() . "\n";
            break;
        }
    }
}
echo "Alle Migrationen sind aktuell.\n"; 