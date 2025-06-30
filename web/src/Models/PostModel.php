<?php
namespace Dropblog\Models;

use Dropblog\Utils\Database;
use PDO;
use InvalidArgumentException;

class PostModel
{
    private PDO $pdo;
    private PostType $postTypeModel;

    // Legacy constant - deprecated, will be removed in future version
    // @deprecated Use PostType model instead
    public const VALID_TYPES = ['note', 'link', 'comment', 'quote', 'photo', 'code', 'question', 'shopping', 'rant', 'poll', 'media', 'book', 'announcement', 'calendar'];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->postTypeModel = new PostType();
    }

    /**
     * Get the icon path for a post type
     * 
     * @param string $typeSlug The post type slug
     * @return string The path to the icon
     * @deprecated Use PostType::getIconPath() instead
     */
    public static function getTypeIcon(string $typeSlug): string
    {
        $postTypeModel = new PostType();
        $postType = $postTypeModel->getBySlug($typeSlug);
        
        if (!$postType) {
            // Fallback for backward compatibility
            return "/assets/images/icon-{$typeSlug}.png";
        }
        
        return PostType::getIconPath($postType);
    }

    /**
     * Create a new blog post
     * 
     * @param string $content The markdown content of the post
     * @param string $typeSlug The slug of the post type (default: 'note')
     * @param array|null $metadata Optional metadata for the post type
     * @return int|false The ID of the newly created post, or false on failure
     */
    public function create(string $content, string $typeSlug = 'note', ?array $metadata = null): int|false
    {
        // Get post type by slug
        $postType = $this->postTypeModel->getBySlug($typeSlug);
        if (!$postType) {
            throw new InvalidArgumentException("Invalid post type: $typeSlug");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO posts (content, post_type_id, type, metadata, created_at) 
            VALUES (:content, :post_type_id, :type, :metadata, NOW())
        ");
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        
        $params = [
            ':content' => $content,
            ':post_type_id' => $postType['id'],
            ':type' => $typeSlug, // Keep for backward compatibility
            ':metadata' => $metadataJson
        ];
        
        if ($stmt->execute($params)) {
            return (int)$this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing blog post
     * 
     * @param int $id The ID of the post to update
     * @param string $content The new markdown content of the post
     * @param string|null $typeSlug Optional new type slug for the post
     * @param array|null $metadata Optional new metadata for the post
     * @return bool True if the update was successful, false otherwise
     */
    public function update(int $id, string $content, ?string $typeSlug = null, ?array $metadata = null): bool
    {
        $sql = "UPDATE posts SET content = :content, updated_at = NOW()";
        $params = [':content' => $content, ':id' => $id];

        if ($typeSlug !== null) {
            // Validate post type exists
            $postType = $this->postTypeModel->getBySlug($typeSlug);
            if (!$postType) {
                throw new InvalidArgumentException("Invalid post type: $typeSlug");
            }
            
            $sql .= ", post_type_id = :post_type_id, type = :type";
            $params[':post_type_id'] = $postType['id'];
            $params[':type'] = $typeSlug; // Keep for backward compatibility
        }

        if ($metadata !== null) {
            $sql .= ", metadata = :metadata";
            $params[':metadata'] = json_encode($metadata);
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get a post by ID with post type information
     * 
     * @param int $id The ID of the post to retrieve
     * @return array|false The post data with post type info or false if not found
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pt.slug as post_type_slug, pt.name as post_type_name, 
                   pt.emoji as post_type_emoji, pt.icon_filename as post_type_icon
            FROM posts p 
            LEFT JOIN post_types pt ON p.post_type_id = pt.id
            WHERE p.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post) {
            if ($post['metadata']) {
                $post['metadata'] = json_decode($post['metadata'], true);
            }
            
            // Add post type information for easier access
            $post['post_type'] = [
                'id' => $post['post_type_id'],
                'slug' => $post['post_type_slug'],
                'name' => $post['post_type_name'],
                'emoji' => $post['post_type_emoji'],
                'icon_filename' => $post['post_type_icon']
            ];
        }
        return $post;
    }

    /**
     * Get all posts with post type information
     * 
     * @param int $limit Maximum number of posts to retrieve
     * @param int $offset Offset for pagination
     * @return array Array of posts with post type information
     */
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pt.slug as post_type_slug, pt.name as post_type_name,
                   pt.emoji as post_type_emoji, pt.icon_filename as post_type_icon
            FROM posts p 
            LEFT JOIN post_types pt ON p.post_type_id = pt.id
            ORDER BY p.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process each post to add structured post type info
        foreach ($posts as &$post) {
            if ($post['metadata']) {
                $post['metadata'] = json_decode($post['metadata'], true);
            }
            
            $post['post_type'] = [
                'id' => $post['post_type_id'],
                'slug' => $post['post_type_slug'],
                'name' => $post['post_type_name'],
                'emoji' => $post['post_type_emoji'],
                'icon_filename' => $post['post_type_icon']
            ];
        }
        
        return $posts;
    }

    /**
     * Get posts by post type
     * 
     * @param string $typeSlug The post type slug
     * @param int $limit Maximum number of posts to retrieve
     * @param int $offset Offset for pagination
     * @return array Array of posts of the specified type
     */
    public function getByType(string $typeSlug, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pt.slug as post_type_slug, pt.name as post_type_name,
                   pt.emoji as post_type_emoji, pt.icon_filename as post_type_icon
            FROM posts p 
            JOIN post_types pt ON p.post_type_id = pt.id
            WHERE pt.slug = :type_slug
            ORDER BY p.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':type_slug', $typeSlug);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process each post to add structured post type info
        foreach ($posts as &$post) {
            if ($post['metadata']) {
                $post['metadata'] = json_decode($post['metadata'], true);
            }
            
            $post['post_type'] = [
                'id' => $post['post_type_id'],
                'slug' => $post['post_type_slug'],
                'name' => $post['post_type_name'],
                'emoji' => $post['post_type_emoji'],
                'icon_filename' => $post['post_type_icon']
            ];
        }
        
        return $posts;
    }

    /**
     * Delete a post by ID
     * 
     * @param int $id The ID of the post to delete
     * @return bool True if successful, false otherwise
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Get valid post types (from database)
     * 
     * @return array Array of valid post type slugs
     * @deprecated Use PostType::getAllActive() instead
     */
    public function getValidTypes(): array
    {
        $postTypes = $this->postTypeModel->getAllActive();
        return array_column($postTypes, 'slug');
    }
}
