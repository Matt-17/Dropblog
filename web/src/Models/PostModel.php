<?php
namespace Dropblog\Models;

use Dropblog\Utils\Database;
use PDO;

class PostModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Create a new blog post
     * 
     * @param string $content The markdown content of the post
     * @return int|false The ID of the newly created post, or false on failure
     */
    public function create(string $content): int|false
    {
        $stmt = $this->pdo->prepare("INSERT INTO posts (content, created_at) VALUES (:content, NOW())");
        $stmt->bindParam(':content', $content);
        
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
     * @return bool True if the update was successful, false otherwise
     */
    public function update(int $id, string $content): bool
    {
        $stmt = $this->pdo->prepare("UPDATE posts SET content = :content, updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
