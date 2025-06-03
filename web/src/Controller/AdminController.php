<?php
namespace Dropblog\Controller;

use Dropblog\Config;
use Dropblog\Utils\Database;
use Dropblog\Utils\Router;
use Dropblog\Utils\HashIdHelper;
use Dropblog\Models\PostModel;
use Dropblog\Models\PostType;
use PDO;

class AdminController implements ControllerInterface
{
    private PDO $pdo;
    private PostModel $postModel;
    private PostType $postTypeModel;
    private bool $isDebug;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->postModel = new PostModel();
        $this->postTypeModel = new PostType();
        $this->isDebug = Config::DEBUG ?? false;
    }

    public static function register(Router $router): void
    {
        $controller = new self();                                       
        $router->add('POST', 'admin/update', [$controller, 'handleUpdate'], true);
        $router->add('POST', 'admin/posts', [$controller, 'handleCreatePost'], true);
        $router->add('PUT', 'admin/posts/(?P<hash>[A-Za-z0-9]{8})', [$controller, 'handleUpdatePost'], true);
        
        // Post types management endpoints
        $router->add('GET', 'admin/post-types', [$controller, 'handleGetPostTypes'], true);
        $router->add('POST', 'admin/post-types', [$controller, 'handleCreatePostType'], true);
        $router->add('PUT', 'admin/post-types/(?P<id>\d+)', [$controller, 'handleUpdatePostType'], true);
        $router->add('DELETE', 'admin/post-types/(?P<id>\d+)', [$controller, 'handleDeletePostType'], true);
        $router->add('GET', 'admin/post-types/stats', [$controller, 'handleGetPostTypeStats'], true);
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

        // Validate post type if provided (use slug, not old type field)
        $typeSlug = $input['post_type'] ?? $input['type'] ?? 'note'; // Support both new and legacy field names
        $postType = $this->postTypeModel->getBySlug($typeSlug);
        if (!$postType) {
            $validTypes = array_column($this->postTypeModel->getAllActive(), 'slug');
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post type',
                'provided_type' => $typeSlug,
                'valid_types' => $validTypes
            ], 400);
        }

        // Create post with type and metadata
        $postId = $this->postModel->create(
            $input['content'],
            $typeSlug,
            $input['metadata'] ?? null
        );

        if ($postId) {
            // Generate HashId for the post URL
            $hash = HashIdHelper::encode($postId);
            $url = "/post/$hash";
            
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Post created successfully', 
                'post_id' => $postId,
                'post_hash' => $hash,
                'post_url' => $url,
                'post_type' => [
                    'slug' => $postType['slug'],
                    'name' => $postType['name'],
                    'emoji' => $postType['emoji'],
                    'icon_path' => PostType::getIconPath($postType)
                ]
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
            
            // Check if migration has already been applied
            $stmt = $this->pdo->prepare("SELECT id FROM migrations WHERE filename = ?");
            $stmt->execute([$filename]);
            if ($stmt->fetch()) {
                continue; // Skip if already applied
            }

            $sql = file_get_contents($migration);
            
            try {
                $this->pdo->exec($sql);
                
                // Record the migration as applied
                $stmt = $this->pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
                $stmt->execute([$filename]);
                
                $applied[] = $filename;
            } catch (\PDOException $e) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Migration failed: ' . $e->getMessage()
                ], 500);
            }
        }

        if (empty($applied)) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'No new migrations to apply'
            ], 200);
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

        // Validate request method
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
        
        // Get current post data to preserve content if not provided in update
        $currentPost = $this->postModel->getById($postId);
        if (!$currentPost) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Use existing content if not provided in the input
        $contentToUpdate = $input['content'] ?? $currentPost['content'];

        // Validate post type if provided
        $typeSlug = null;
        if (isset($input['post_type']) || isset($input['type'])) {
            $typeSlug = $input['post_type'] ?? $input['type']; // Support both new and legacy field names
            $postType = $this->postTypeModel->getBySlug($typeSlug);
            if (!$postType) {
                $validTypes = array_column($this->postTypeModel->getAllActive(), 'slug');
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid post type',
                    'provided_type' => $typeSlug,
                    'valid_types' => $validTypes
                ], 400);
            }
        }

        // Update post with new type and metadata if provided
        if ($this->postModel->update(
            $postId,
            $contentToUpdate,
            $typeSlug,
            $input['metadata'] ?? null
        )) {
            // Get updated post info
            $updatedPost = $this->postModel->getById($postId);
            
            return $this->jsonResponse([
                'success' => true, 
                'message' => 'Post updated successfully',
                'post_id' => $postId,
                'post_hash' => $hash,
                'post_type' => $updatedPost['post_type']
            ], 200);
        } else {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Failed to update post'
            ], 500);
        }
    }

    // === POST TYPES MANAGEMENT ENDPOINTS ===

    public function handleGetPostTypes(): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        // Get all active post types (for public API)
        $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === 'true';
        
        if ($includeInactive) {
            $postTypes = $this->postTypeModel->getAll();
        } else {
            $postTypes = $this->postTypeModel->getAllActive();
        }

        // Add full icon paths to each post type
        foreach ($postTypes as &$postType) {
            $postType['icon_path'] = PostType::getIconPath($postType);
        }

        return $this->jsonResponse([
            'success' => true,
            'post_types' => $postTypes,
            'total_count' => count($postTypes)
        ], 200);
    }

    public function handleCreatePostType(): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($input['slug']) || empty($input['name'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Missing required fields: slug and name are required'
            ], 400);
        }

        // Validate slug format
        if (!PostType::isValidSlug($input['slug'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Invalid slug format. Use 2-50 lowercase letters, numbers, hyphens, and underscores only.'
            ], 400);
        }

        try {
            $postTypeId = $this->postTypeModel->create($input);
            
            if ($postTypeId) {
                $newPostType = $this->postTypeModel->getById($postTypeId);
                $newPostType['icon_path'] = PostType::getIconPath($newPostType);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post type created successfully',
                    'post_type' => $newPostType
                ], 201);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create post type'
                ], 500);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function handleUpdatePostType(string $id): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        $postTypeId = (int)$id;
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate slug format if provided
        if (!empty($input['slug']) && !PostType::isValidSlug($input['slug'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Invalid slug format. Use 2-50 lowercase letters, numbers, hyphens, and underscores only.'
            ], 400);
        }

        try {
            $success = $this->postTypeModel->update($postTypeId, $input);
            
            if ($success) {
                $updatedPostType = $this->postTypeModel->getById($postTypeId);
                $updatedPostType['icon_path'] = PostType::getIconPath($updatedPostType);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post type updated successfully',
                    'post_type' => $updatedPostType
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update post type'
                ], 500);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function handleDeletePostType(string $id): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        $postTypeId = (int)$id;

        try {
            $success = $this->postTypeModel->delete($postTypeId);
            
            if ($success) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Post type deleted successfully'
                ], 200);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete post type'
                ], 500);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function handleGetPostTypeStats(): array
    {
        // Authenticate
        $authResult = $this->authenticate();
        if ($authResult !== true) {
            return $authResult;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Method not allowed'
            ], 405);
        }

        $stats = $this->postTypeModel->getUsageStats();
        
        return $this->jsonResponse([
            'success' => true,
            'post_type_stats' => $stats,
            'total_types' => count($stats)
        ], 200);
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