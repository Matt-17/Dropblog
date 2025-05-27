<?php
/**
 * @var \PainBlog\Models\Post $post
 */
?>
<article class="post-item">
    <h2><a href="/post/<?= $post->id ?>"><?= htmlspecialchars($post->title) ?></a></h2>
    <div class="post-meta">
        <time datetime="<?= $post->date ?>"><?= $post->date ?></time>
    </div>
    <div class="post-excerpt">
        <?= $post->excerpt ?>
    </div>
</article> 