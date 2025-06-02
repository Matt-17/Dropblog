<?php
namespace Dropblog\Utils;

use PDO;
use DateTime;
use Dropblog\Models\Post;
use Dropblog\Models\PostGroup;

class PostUtils
{
    /**
     * Liefert alle Posts seit dem letzten Montag 00:00 bis jetzt
     *
     * @param PDO $pdo
     * @return Post[]
     */
    public static function getPostsOfLastWeek(PDO $pdo): array
    {
      
        // 7 Tage zurück von heute
        $lastWeek = new DateTime();
        $lastWeek->modify('-6 days');
        $lastWeek->setTime(0, 0, 0);

        $stmt = $pdo->prepare("
            SELECT 
                id,
                content,
                created_at as date,
                type,
                metadata
            FROM posts
            WHERE created_at >= ?
              AND created_at <= NOW()
            ORDER BY created_at DESC
        ");
        $stmt->execute([ $lastWeek->format('Y-m-d H:i:s') ]);
        return array_map(fn($row) => Post::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Liefert alle Posts für ein gegebenes Jahr/Monat
     *
     * @param PDO $pdo
     * @param int $year
     * @param int $month
     * @return Post[]
     */
    public static function getPostsByMonth(PDO $pdo, int $year, int $month): array
    {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                content,
                created_at as date,
                type,
                metadata
            FROM posts
            WHERE YEAR(created_at) = ?
              AND MONTH(created_at) = ?
              AND created_at <= NOW()
            ORDER BY created_at DESC
        ");
        $stmt->execute([ $year, $month ]);
        return array_map(fn($row) => Post::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Gruppiert ein flaches Posts-Array nach dem Datum
     *
     * @param Post[] $posts
     * @return PostGroup[]
     */
    public static function groupPostsByDate(array $posts): array
    {
        $grouped = [];
        foreach ($posts as $post) {
            $dateKey = $post->date->format('Y-m-d');
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = new PostGroup(date: $post->date);
            }
            $grouped[$dateKey]->addPost($post);
        }
        return array_values($grouped);
    }

    /**
     * @param PDO $pdo
     * @param int $id
     * @return Post|null
     */
    public static function getPostById(PDO $pdo, int $id): ?Post
    {
        $stmt = $pdo->prepare("
            SELECT 
                id, 
                content, 
                created_at as date,
                type,
                metadata
            FROM posts 
            WHERE id = ? AND created_at <= NOW()
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? Post::fromArray($data) : null;
    }

    public static function idToUrl(int $id): string
    {
        return HashIdHelper::encode($id);
    }

    /**
     * Search for posts containing all keywords.
     *
     * @param PDO $pdo
     * @param string $query The search query string.
     * @return Post[]
     */
    public static function searchPosts(PDO $pdo, string $query): array
    {
        $keywords = array_filter(explode(' ', $query)); // Split by space and remove empty keywords

        if (empty($keywords)) {
            return []; // Return empty array if no keywords
        }

        $sql = "SELECT id, content, created_at as date, type, metadata FROM posts WHERE";
        $params = [];

        foreach ($keywords as $index => $keyword) {
            $sql .= ($index > 0 ? " AND" : "") . " content LIKE ?";
            $params[] = '%' . $keyword . '%';
        }

        $sql .= " AND created_at <= NOW() ORDER BY created_at DESC LIMIT 101";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(fn($row) => Post::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
