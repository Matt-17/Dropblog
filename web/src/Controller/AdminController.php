<?php
namespace Dropblog\Controller;

use Dropblog\Config;
use Dropblog\Utils\Database;
use Dropblog\Utils\Router;
use Dropblog\Utils\HashIdHelper;
use Dropblog\Models\PostModel;
use PDO;

class AdminController implements ControllerInterface
{
    private PDO $pdo;
    private PostModel $postModel;
    private bool $isDebug;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->postModel = new PostModel();
        $this->isDebug = Config::DEBUG ?? false;
    }

    public static function register(Router $router): void
    {
        $controller = new self();                                       
        $router->add('POST', 'admin/update', [$controller, 'handleUpdate'], true);
        $router->add('POST', 'admin/posts', [$controller, 'handleCreatePost'], true);
        $router->add('PUT', 'admin/posts/{hash}', [$controller, 'handleUpdatePost'], true);
    }

    public static function isApi(): bool
    {
        return true;
    }

    public function handleCreatePost(): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['content'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Missing content'
            ], 400);
        }

        // Create post
        $postId = $this->postModel->create($input['content']);

        if ($postId) {
            // Generate HashId for the post URL
            $hash = HashIdHelper::encode($postId);
            $url = "/post/$hash";
            
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Post created successfully', 
                'post_id' => $postId,
                'post_hash' => $hash,
                'post_url' => $url
            ], 201);
        } else {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Failed to create post'
            ], 500);
        }
    }

    private function authenticate(): bool|array
    {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? '';

        if (empty($auth_header) || !preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Unauthorized'
            ], 401);
        }

        $api_key = $matches[1];
        if ($api_key !== Config::ADMIN_API_KEY) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Forbidden'
            ], 403);
        }
        return true;
    }

    public function handleUpdate(): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
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
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Migration failed: ' . $e->getMessage()
                ], 500);
            }
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Migrations applied successfully',
            'applied' => $applied
        ], 200);
    }

    public function handleUpdatePost(string $hash): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        // Decode HashId to get post ID
        $postId = HashIdHelper::decode($hash);
        if (!$postId) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Invalid post hash'
            ], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['content'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Missing content'
            ], 400);
        }

        // Update post
        if ($this->postModel->update($postId, $input['content'])) {
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Post updated successfully',
                'post_id' => $postId,
                'post_hash' => $hash
            ], 200);
        } else {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Failed to update post'
            ], 500);
        }
    }

    private function jsonResponse(array $data, int $status = 200): array
    {
        // Add debug information only in debug mode
        if ($this->isDebug) {
            $data['debug'] = [
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'headers' => getallheaders(),
                'auth_header' => $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['http_authorization'] ?? 'not set'
            ];
        }

        return [
            'view' => 'Shared/json.php',
            'vars' => ['data' => $data],
            'status' => $status,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];
    }
}