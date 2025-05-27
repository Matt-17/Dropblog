<?php
// Prüfe ob alle erforderlichen Daten vorhanden sind
if (!defined('BLOG_TITLE') || !isset($currentYear) || !isset($currentMonth) || !isset($content)) {
    throw new RuntimeException('Fehlende Daten');
}

// Hole Monatsnamen
$monthNames = get_month_names();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(BLOG_TITLE) ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <header class="header">
        <h1><?= htmlspecialchars(BLOG_TITLE) ?></h1>
    </header>
    <div class="content">
        <?php include $content; ?>
    </div>

    <footer class="footer">
        <?php
        $prev = get_previous_month($currentMonth, $currentYear);
        ?>
        <a href="/<?= $prev['year'] ?>/<?= sprintf('%02d', $prev['month']) ?>">← <?= htmlspecialchars($monthNames[$prev['month']]) ?></a>        
        •

        <a href="/<?= $currentYear ?>/<?= sprintf('%02d', $currentMonth) ?>" class="current-month"><?= htmlspecialchars($monthNames[$currentMonth]) ?> <?= $currentYear ?></a>        
        •

        <?php
        $next = get_next_month($currentMonth, $currentYear);
        if (!is_future_month($next['month'], $next['year'])):
        ?>
            <a href="/<?= $next['year'] ?>/<?= sprintf('%02d', $next['month']) ?>"><?= htmlspecialchars($monthNames[$next['month']]) ?> →</a>     
            •
        <?php endif; ?>

        <a href="/">Startseite</a>

        <div class="powered-by">Powered by <?= htmlspecialchars(BLOG_TITLE) ?></div>
    </footer>
</body>
</html> 