<?php
namespace PainBlog\Utils;

use PDO;
use PainBlog\Utils\HashIdHelper;

class PostUtils
{
    public static function getGroupedPosts(PDO $pdo, string $whereClause, array $params = []): array
    {
        $sql = "
            SELECT 
                DATE(created_at) as post_date,
                GROUP_CONCAT(
                    CONCAT(id, ':', content, ':', created_at)
                    ORDER BY created_at DESC
                    SEPARATOR '||'
                ) as posts
            FROM posts 
            WHERE $whereClause
            GROUP BY DATE(created_at)
            ORDER BY post_date DESC
        ";

        if (empty($params)) {
            $stmt = $pdo->query($sql);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $results = $stmt->fetchAll();
        $groupedPosts = [];

        foreach ($results as $row) {
            $date = $row['post_date'];
            $posts = [];
            foreach (explode('||', $row['posts']) as $postString) {
                [$id, $content, $created_at] = explode(':', $postString);
                $posts[] = ['id' => (int)$id, 'content' => $content, 'date' => $date];
            }
            $groupedPosts[$date] = $posts;
        }

        return $groupedPosts;
    }

    public static function getPostById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("
            SELECT id, content, DATE_FORMAT(created_at, '%Y-%m-%d') as date
            FROM posts 
            WHERE id = ? AND created_at <= NOW()
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function idToUrl(int $id): string
    {
        return HashIdHelper::encode($id);
    }
}
