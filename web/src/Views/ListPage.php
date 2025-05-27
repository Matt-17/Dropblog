<?php
/**
 * @var \Dropblog\Models\PostGroup[] $groupedPosts
 * @var string $emptyMessage
 * @var bool $moreResultsExist // Passed from controllers that fetch more than the display limit (e.g., SearchController)
 */
use Dropblog\Models\PostGroup;
?>

<?php if (empty($groupedPosts)): ?>
    <div class="no-posts"><?= htmlspecialchars($emptyMessage) ?></div>
<?php else: ?>
    <?php
    // Include the PostList component to display the posts
    include VIEWS_PATH . '/Components/PostList.php';
    ?>
<?php endif; ?>
