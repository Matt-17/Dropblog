<?php
// Hole Posts für den ausgewählten Monat
$stmt = $pdo->prepare("
    SELECT p.*, DATE_FORMAT(p.created_at, '%Y-%m-%d') as date
    FROM posts p
    WHERE YEAR(p.created_at) = ? AND MONTH(p.created_at) = ?
    AND p.created_at <= NOW()
    ORDER BY p.created_at DESC
");
$stmt->execute([$currentYear, $currentMonth]);
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

$emptyMessage = 'Keine Posts für diesen Monat vorhanden.';
include '_shared/post_group.php'; 