<?php
namespace PainBlog\Controller;

use PainBlog\Config;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;
use PDO;

class AdminController
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

    public function handleUpdate(): array
    {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return ['view' => 'Shared/error.php', 'vars' => [
                'message' => 'Method not allowed',
                'code' => 405
            ]];
        }

        // Verify API key
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? '';
        if (empty($auth_header) || !preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            http_response_code(401);
            return ['view' => 'Shared/error.php', 'vars' => [
                'message' => 'Unauthorized',
                'code' => 401
            ]];
        }

        if ($matches[1] !== Config::ADMIN_API_KEY) {
            http_response_code(403);
            return ['view' => 'Shared/error.php', 'vars' => [
                'message' => 'Forbidden',
                'code' => 403
            ]];
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
                return [
                    'view' => 'Shared/error.php',
                    'vars' => [
                        'error' => [
                            'code' => 500,
                            'message' => 'Migration failed: ' . $e->getMessage()
                        ]
                    ]
                ];
            }
        }

        return [
            'view' => 'Shared/error.php',
            'vars' => [
                'success' => true,
                'message' => 'Migrations applied successfully',
                'applied' => $applied
            ]
        ];
    }
} 