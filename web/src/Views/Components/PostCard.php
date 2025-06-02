<?php
/**
 * @var \Dropblog\Models\Post $post
 * @var array|null $keywords
 */
use Dropblog\Models\Post;
use Dropblog\Models\PostModel;
use Dropblog\Utils\HashIdHelper;

function highlight_keywords($text, $keywords) {
    if (!$keywords || !is_array($keywords)) return $text;
    foreach ($keywords as $word) {
        if (trim($word) !== '') {
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $text);
        }
    }
    return $text;
}
?>

<article class="post">
    <a href="/post/<?= HashIdHelper::encode($post->id) ?>" class="icon-link">
        <img src="<?= PostModel::getTypeIcon($post->type) ?>" alt="<?= ucfirst($post->type) ?>" class="icon">
    </a>
    <div class="post-content">
        <?= $post->getHighlightedContent($keywords ?? []) ?>
    </div>
</article> 