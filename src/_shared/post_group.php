<?php
if (!isset($groupedPosts) || !is_array($groupedPosts)) {
    $groupedPosts = [];
}
if (!isset($emptyMessage)) {
    $emptyMessage = 'Keine Posts vorhanden.';
}

if (empty($groupedPosts)): ?>
    <div class="no-posts"><?= htmlspecialchars($emptyMessage) ?></div>
<?php else: ?>
    <?php foreach ($groupedPosts as $date => $dayPosts): ?>
        <div class="post-group">
            <div class="post-date-header"><?= format_date($date) ?></div>
            <?php foreach ($dayPosts as $post): ?>
                <?php include '_shared/post_item.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
