<?php
use PainBlog\Utils\PostUtils;

// Hole die 10 neuesten Posts
$where = "created_at <= NOW()";
$params = [];
$groupedPosts = PostUtils::getGroupedPosts($GLOBALS['pdo'], $where, $params);

// Optional: Limit auf 10 neueste (je nach SQL kannst du auch den Query anpassen)
$groupedPosts = array_slice($groupedPosts, 0, 10);

$emptyMessage = 'Noch keine Posts vorhanden.';
include '_shared/post_group.php';
