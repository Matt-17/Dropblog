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
}
