<?php
/**
 * @var \Dropblog\Models\Post $post
 */
use Dropblog\Models\Post;
use Dropblog\Utils\HashIdHelper;
?>

<article class="post">
    <a href="/post/<?= HashIdHelper::encode($post->id) ?>" class="avatar-link">
        <img src="/assets/images/avatar.png" alt="Avatar" class="avatar">
    </a>
    <div class="post-content">
        <?= $post->getFormattedContent() ?>
    </div>
</article> 