<?php
/**
 * @var \PainBlog\Models\Post $post
 */
?>
<article class="post-item">
    <div class="post-meta">
        <time datetime="<?= $post->date ?>"><?= $post->date ?></time>
    </div>
    <div class="post-excerpt">
        <?= $post->excerpt ?>
    </div>
</article> 