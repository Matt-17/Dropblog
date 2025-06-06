<?php
/**
 * @var \Dropblog\Models\Post $post
 * @var array|null $keywords
 */
use Dropblog\Models\Post;
use Dropblog\Models\PostModel;
use Dropblog\Utils\HashIdHelper;
?>

<article class="post">
    <a href="/post/<?= HashIdHelper::encode($post->id) ?>" class="icon-link">
        <img src="<?= PostModel::getTypeIcon($post->type) ?>" alt="<?= ucfirst($post->type) ?>" class="icon">
    </a>
    <div class="post-content">
        <?= $post->getHighlightedContent($keywords ?? []) ?>
    </div>
</article>
