<?php
/**
 * @var \PainBlog\Models\Post $post
 */
?>
<article class="post">
    <a href="/post/<?= $post->id ?>" class="avatar-link">📝</a>
    <div class="post-content">
        <p><?= $post->excerpt ?></p>
    </div>
</article> 