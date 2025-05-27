<?php
/**
 * @var \PainBlog\Models\PostGroup[] $groupedPosts
 * @var string $emptyMessage
 */
?>
<?php if (empty($groupedPosts)): ?>
    <div class="no-posts"><?= htmlspecialchars($emptyMessage) ?></div>
<?php else: ?>
    <?php foreach ($groupedPosts as $group): ?>
        <div class="post-group">
            <h3 class="post-date-header"><?= $group->getFormattedDate() ?></h3>
            <?php foreach ($group->getPosts() as $post): ?>
                <?php include VIEWS_PATH . '/Components/PostCard.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?> 