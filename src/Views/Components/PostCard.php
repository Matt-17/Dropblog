<?php
/**
 * @var \PainBlog\Models\Post $post
 */
use PainBlog\Utils\HashIdHelper;
?>
<article class="post">
    <a href="/post/<?= HashIdHelper::encode($post->id) ?>" class="avatar-link">📝</a>
    <div class="post-content">
        <div class="post-date"><?= $post->getFormattedDate() ?></div>
        <?= $post->getFormattedExcerpt() ?>
    </div>
</article> 