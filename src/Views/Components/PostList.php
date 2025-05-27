<?php
/**
 * @var array $groupedPosts
 * @var string $emptyMessage
 */
?>
<div class="post-group">
    <?php if (empty($groupedPosts)): ?>
        <p class="empty-message"><?= htmlspecialchars($emptyMessage) ?></p>
    <?php else: ?>
        <?php foreach ($groupedPosts as $date => $posts): ?>
            <div class="post-date-group">
                <h3 class="post-date"><?= htmlspecialchars($date) ?></h3>
                <?php foreach ($posts as $post): ?>
                    <?php include VIEWS_PATH . '/Components/PostCard.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div> 