<?php
namespace Dropblog\Models;

use Dropblog\Utils\Database;
use PDO;
use InvalidArgumentException;

class PostType
{
    private PDO $pdo;
    
    // Cache for post types to avoid repeated database queries
    private static ?array $typeCache = null;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get all active post types ordered by sort_order
     * 
     * @return array Array of post type records
     */
    public function getAllActive(): array
    {
        if (self::$typeCache === null) {
            $stmt = $this->pdo->prepare("
                SELECT id, slug, name, description, icon_filename, emoji, sort_order 
                FROM post_types 
                WHERE is_active = 1 
                ORDER BY sort_order ASC, name ASC
            ");
            $stmt->execute();
            self::$typeCache = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return self::$typeCache;
    }

    /**
     * Get all post types (including inactive) for admin purposes
     * 
     * @return array Array of all post type records
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, slug, name, description, icon_filename, emoji, is_active, sort_order,
                   created_at, updated_at
            FROM post_types 
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a post type by slug
     * 
     * @param string $slug The slug to search for
     * @return array|false Post type record or false if not found
     */
    public function getBySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, slug, name, description, icon_filename, emoji, is_active, sort_order
            FROM post_types 
            WHERE slug = :slug AND is_active = 1
        ");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get a post type by ID
     * 
     * @param int $id The ID to search for
     * @return array|false Post type record or false if not found
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT id, slug, name, description, icon_filename, emoji, is_active, sort_order
            FROM post_types 
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new post type
     * 
     * @param array $data Post type data
     * @return int|false The ID of the newly created post type, or false on failure
     */
    public function create(array $data): int|false
    {
        // Validate required fields
        if (empty($data['slug']) || empty($data['name'])) {
            throw new InvalidArgumentException("Slug and name are required");
        }

        // Check if slug already exists
        if ($this->getBySlug($data['slug'])) {
            throw new InvalidArgumentException("A post type with this slug already exists");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO post_types (slug, name, description, icon_filename, emoji, is_active, sort_order) 
            VALUES (:slug, :name, :description, :icon_filename, :emoji, :is_active, :sort_order)
        ");
        
        $params = [
            ':slug' => $data['slug'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':icon_filename' => $data['icon_filename'] ?? null,
            ':emoji' => $data['emoji'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':sort_order' => $data['sort_order'] ?? 0
        ];
        
        if ($stmt->execute($params)) {
            self::$typeCache = null; // Clear cache
            return (int)$this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing post type
     * 
     * @param int $id The ID of the post type to update
     * @param array $data Updated data
     * @return bool True if successful, false otherwise
     */
    public function update(int $id, array $data): bool
    {
        // Check if post type exists
        if (!$this->getById($id)) {
            throw new InvalidArgumentException("Post type not found");
        }

        // If slug is being changed, check for duplicates
        if (!empty($data['slug'])) {
            $existing = $this->getBySlug($data['slug']);
            if ($existing && $existing['id'] != $id) {
                throw new InvalidArgumentException("A post type with this slug already exists");
            }
        }

        $updateFields = [];
        $params = [':id' => $id];

        foreach (['slug', 'name', 'description', 'icon_filename', 'emoji', 'is_active', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return true; // Nothing to update
        }

        $sql = "UPDATE post_types SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        $result = $stmt->execute($params);
        if ($result) {
            self::$typeCache = null; // Clear cache
        }
        return $result;
    }

    /**
     * Delete a post type (only if no posts are using it)
     * 
     * @param int $id The ID of the post type to delete
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException If post type is in use
     */
    public function delete(int $id): bool
    {
        // Check if post type exists
        if (!$this->getById($id)) {
            throw new InvalidArgumentException("Post type not found");
        }

        // Check if any posts are using this post type
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE post_type_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            throw new InvalidArgumentException("Cannot delete post type: $count posts are using this type");
        }

        // Safe to delete
        $stmt = $this->pdo->prepare("DELETE FROM post_types WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        
        if ($result) {
            self::$typeCache = null; // Clear cache
        }
        return $result;
    }

    /**
     * Get the icon path for a post type (with fallback)
     * 
     * @param array $postType Post type record
     * @return string The path to the icon
     */
    public static function getIconPath(array $postType): string
    {
        if (!empty($postType['icon_filename'])) {
            return "/assets/images/post-types/" . $postType['icon_filename'];
        }
        
        // Fallback to emoji or default icon
        return "/assets/images/post-types/icon-default.png";
    }

    /**
     * Get post type usage statistics
     * 
     * @return array Array with usage counts for each post type
     */
    public function getUsageStats(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT pt.id, pt.slug, pt.name, pt.emoji, COUNT(p.id) as post_count
            FROM post_types pt
            LEFT JOIN posts p ON pt.id = p.post_type_id
            GROUP BY pt.id, pt.slug, pt.name, pt.emoji
            ORDER BY post_count DESC, pt.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clear the type cache (useful for testing or after updates)
     */
    public static function clearCache(): void
    {
        self::$typeCache = null;
    }

    /**
     * Validate post type slug format
     * 
     * @param string $slug The slug to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidSlug(string $slug): bool
    {
        // Slug must be 2-50 characters, alphanumeric + hyphens/underscores, no spaces
        return preg_match('/^[a-z0-9_-]{2,50}$/', $slug) === 1;
    }
} 