<?php
if (!isset($post) || !is_array($post) || !isset($post['id'])) {
    return;
}
?>
<article class="post">
    <a href="/post/<?= id_to_url($post['id']) ?>" class="avatar-link">●</a>
    <div class="post-content">
        <?= markdown_to_html($post['content'] ?? '') ?>
    </div>
</article>
