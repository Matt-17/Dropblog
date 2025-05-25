<?php

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    include '_content/404.php';
    exit;
}
?>

<div class="post-group">
    <div class="post-date-header"><?= format_date($post['date']) ?></div>
    <?php include '_shared/post_item.php'; ?>
</div>      
