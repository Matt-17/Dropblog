<?php
// Hole die neuesten Posts
$stmt = $pdo->query("
    SELECT p.*, DATE_FORMAT(p.created_at, '%Y-%m-%d') as date
    FROM posts p
    WHERE p.created_at <= NOW()
    ORDER BY p.created_at DESC
    LIMIT 10
");
$posts = $stmt->fetchAll();

// Gruppiere Posts nach Datum
$groupedPosts = [];
foreach ($posts as $post) {
    $date = $post['date'];
    if (!isset($groupedPosts[$date])) {
        $groupedPosts[$date] = [];
    }
    $groupedPosts[$date][] = $post;
}

$emptyMessage = 'Noch keine Posts vorhanden.';
include '_shared/post_group.php'; 