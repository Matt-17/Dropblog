<?php
namespace PainBlog\Utils;

use PDO;
use DateTime;
use PainBlog\Models\Post;
use PainBlog\Models\PostGroup;

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
        // Erster Tag der Woche (Montag)
        $monday = new DateTime('monday this week');
        $monday->setTime(0, 0);

        $stmt = $pdo->prepare("
            SELECT 
                id,
                content,
                created_at as date
            FROM posts
            WHERE created_at >= ?
              AND created_at <= NOW()
            ORDER BY created_at DESC
        ");
        $stmt->execute([ $monday->format('Y-m-d H:i:s') ]);
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
                created_at as date
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
                created_at as date
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
}
