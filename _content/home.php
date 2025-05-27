<?php
use PainBlog\Utils\PostUtils;

// 1) Alle Posts der letzten Woche holen
$posts = PostUtils::getPostsOfLastWeek($GLOBALS['pdo']);

// 2) Nach Datum gruppieren
$groupedPosts = PostUtils::groupPostsByDate($posts);

$emptyMessage = 'Noch keine Posts vorhanden.';
include '_shared/post_group.php';
