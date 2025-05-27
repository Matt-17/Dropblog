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
        // Check for admin token
        $token = $_GET['token'] ?? '';
        if ($token !== Config::ADMIN_TOKEN) {
            http_response_code(401);
            return [
                'view' => 'Shared/error.php',
                'vars' => [
                    'error' => [
                        'code' => 401,
                        'message' => 'Unauthorized'
                    ]
                ]
            ];
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