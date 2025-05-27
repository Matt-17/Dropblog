<?php
namespace PainBlog\Controller;

use PainBlog\Config;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;
use PDO;

class AdminController implements ControllerInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public static function register(Router $router): void
    {
        $controller = new self();                                       
        $router->add('POST', 'admin/update', [$controller, 'handleUpdate'], true);
    }

    public static function isApi(): bool
    {
        return true;
    }

    public function handleUpdate(): array
    {
        // Debug-Ausgabe
        $debug = [];
        $debug[] = "Request Method: " . $_SERVER['REQUEST_METHOD'];
        $debug[] = "All Headers: " . print_r(getallheaders(), true);
        $debug[] = "Raw Authorization Header: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'nicht gesetzt');
        $debug[] = "Authorization Header (lowercase): " . ($_SERVER['http_authorization'] ?? 'nicht gesetzt');
        $debug[] = "All Server Variables: " . print_r($_SERVER, true);

        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed',
                'code' => 405,
                'debug' => $debug
            ]);
        }

        // Verify API key
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? '';
        $debug[] = "Auth Header Value: " . $auth_header;

        if (empty($auth_header) || !preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            $debug[] = "Auth Header ungültig oder leer";
            $debug[] = "Regex Match Result: " . print_r($matches, true);
            http_response_code(401);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401,
                'debug' => $debug
            ]);
        }

        $api_key = $matches[1];
        $debug[] = "Extracted API Key: " . $api_key;
        $debug[] = "Expected API Key: " . Config::ADMIN_API_KEY;

        if ($api_key !== Config::ADMIN_API_KEY) {
            $debug[] = "API Key stimmt nicht überein";
            http_response_code(403);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403,
                'debug' => $debug
            ]);
        }

        // Run migrations
        $migrationsDir = __DIR__ . '/../Migrations';
        $migrations = glob($migrationsDir . '/*.sql');
        sort($migrations);

        $applied = [];
        foreach ($migrations as $migration) {
            $filename = basename($migration);
            $sql = file_get_contents($migration);
            
            try {
                $this->pdo->exec($sql);
                $applied[] = $filename;
            } catch (\PDOException $e) {
                http_response_code(500);
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Migration failed: ' . $e->getMessage(),
                    'code' => 500,
                    'debug' => $debug
                ]);
            }
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Migrations applied successfully',
            'applied' => $applied,
            'debug' => $debug
        ]);
    }

    private function jsonResponse(array $data): array
    {
        return [
            'view' => 'Shared/json.php',
            'vars' => ['data' => $data],
            'status' => $data['code'] ?? 200
        ];
    }
} 