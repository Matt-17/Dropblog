<?php
namespace Dropblog\Models;

use Dropblog\Utils\Database;
use PDO;
use InvalidArgumentException;

class PostModel
{
    private PDO $pdo;
    public const VALID_TYPES = ['note', 'link', 'comment', 'quote', 'photo', 'code', 'question'];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Get the icon path for a post type
     * 
     * @param string $type The post type
     * @return string The path to the icon
     */
    public static function getTypeIcon(string $type): string
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new InvalidArgumentException("Invalid post type: $type");
        }
        return "/assets/images/icon-{$type}.png";
    }

    /**
     * Create a new blog post
     * 
     * @param string $content The markdown content of the post
     * @param string $type The type of post (default: 'note')
     * @param array|null $metadata Optional metadata for the post type
     * @return int|false The ID of the newly created post, or false on failure
     */
    public function create(string $content, string $type = 'note', ?array $metadata = null): int|false
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new InvalidArgumentException("Invalid post type: $type");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO posts (content, type, metadata, created_at) 
            VALUES (:content, :type, :metadata, NOW())
        ");
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':metadata', $metadataJson);
        
        if ($stmt->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing blog post
     * 
     * @param int $id The ID of the post to update
     * @param string $content The new markdown content of the post
     * @param string|null $type Optional new type for the post
     * @param array|null $metadata Optional new metadata for the post
     * @return bool True if the update was successful, false otherwise
     */
    public function update(int $id, string $content, ?string $type = null, ?array $metadata = null): bool
    {
        if ($type !== null && !in_array($type, self::VALID_TYPES)) {
            throw new InvalidArgumentException("Invalid post type: $type");
        }

        $sql = "UPDATE posts SET content = :content, updated_at = NOW()";
        $params = [':content' => $content, ':id' => $id];

        if ($type !== null) {
            $sql .= ", type = :type";
            $params[':type'] = $type;
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
     * Get a post by ID
     * 
     * @param int $id The ID of the post to retrieve
     * @return array|false The post data or false if not found
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post && $post['metadata']) {
            $post['metadata'] = json_decode($post['metadata'], true);
        }
        return $post;
    }
}
