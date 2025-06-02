<?php
/**
 * @var \Dropblog\Models\PostGroup[] $groupedPosts
 * @var string $query
 * @var string $emptyMessage
 * @var bool $moreResultsExist
 */
use Dropblog\Models\PostGroup;
?>

<div class="search-container">
    <form action="/search" method="get" class="search-form">
        <input type="text" name="q" placeholder="Suche..." value="<?= htmlspecialchars($query) ?>">
        <button type="submit">Suchen</button>
    </form>
</div>

<?php if (empty($groupedPosts)): ?>
    <div class="no-posts"><?= htmlspecialchars($emptyMessage) ?></div>
<?php else: ?>
    <?php include VIEWS_PATH . '/ListPage.php'; ?>
<?php endif; ?> 