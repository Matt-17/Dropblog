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

<?php 
// Include the PostList component to display results
include VIEWS_PATH . '/Components/PostList.php'; 
?> 