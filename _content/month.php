<?php
use PainBlog\Utils\PostUtils;

// 1) Posts des Monats holen
$posts = PostUtils::getPostsByMonth($GLOBALS['pdo'], $currentYear, $currentMonth);

// 2) Nach Datum gruppieren
$groupedPosts = PostUtils::groupPostsByDate($posts);

$emptyMessage = 'Keine Posts für diesen Monat vorhanden.';
include '_shared/post_group.php';
