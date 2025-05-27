<?php
use PainBlog\Utils\PostUtils;

if (!$post) {
    http_response_code(404);
    include '_content/404.php';
    exit;
}

// Gruppiere den einen Post
$groupedPosts  = PostUtils::groupPostsByDate([$post]);
$emptyMessage = '';   // oder 'Eintrag nicht gefunden.', wird hier nicht gebraucht

include '_shared/post_group.php';
