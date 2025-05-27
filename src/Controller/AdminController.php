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
        $router->add('admin/update', [$controller, 'handleUpdate']);
    }

    public static function isApi(): bool
    {
        return true;
    }

    public function handleUpdate(): array
    {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed',
                'code' => 405
            ]);
        }

        // Verify API key
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? '';
        if (empty($auth_header) || !preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            http_response_code(401);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ]);
        }

        if ($matches[1] !== Config::ADMIN_API_KEY) {
            http_response_code(403);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403
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
                    'code' => 500
                ]);
            }
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Migrations applied successfully',
            'applied' => $applied
        ]);
    }

    private function jsonResponse(array $data): array
    {
        return [
            'view' => 'Shared/json.php',
            'vars' => ['data' => $data]
        ];
    }
} 