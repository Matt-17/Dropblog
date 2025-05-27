<?php
/**
 * @var array $groupedPosts
 * @var string $emptyMessage
 */
?>
<?php if (empty($groupedPosts)): ?>
    <div class="no-posts"><?= htmlspecialchars($emptyMessage) ?></div>
<?php else: ?>
    <?php foreach ($groupedPosts as $date => $posts): ?>
        <div class="post-group">
            <h3 class="post-date-header"><?= htmlspecialchars($date) ?></h3>
            <?php foreach ($posts as $post): ?>
                <?php include VIEWS_PATH . '/Components/PostCard.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?> 