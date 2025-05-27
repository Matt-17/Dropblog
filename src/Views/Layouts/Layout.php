<?php
use PainBlog\Config;
use PainBlog\Utils\DateUtils;

// Define the views directory path
define('VIEWS_PATH', __DIR__ . '/..');

// Titel direkt aus Config
$title = Config::BLOG_TITLE;

// Sicherstellen, dass die View-Datei gesetzt ist
if (!isset($content)) {
    throw new \RuntimeException('Fehlende View-Datei');
}

// Defaults für Jahr/Monat, falls ein Controller sie nicht explizit gesetzt hat
$currentYear  = $currentYear  ?? date('Y');
$currentMonth = $currentMonth ?? date('n');

// Monatsnamen und Nachbar-Monate aus DateUtils
$monthNames = DateUtils::getMonthNames();
$prev       = DateUtils::getPreviousMonth($currentMonth, $currentYear);
$next       = DateUtils::getNextMonth($currentMonth, $currentYear);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <header class="header">
    <h1><?= htmlspecialchars($title) ?></h1>
  </header>

  <div class="content">
    <?php 
    $viewPath = VIEWS_PATH . '/' . ltrim($content, '/');
    if (!file_exists($viewPath)) {
        throw new \RuntimeException("View file not found: {$viewPath}");
    }
    include $viewPath; 
    ?>
  </div>

  <footer class="footer">
    <a href="/<?= $prev['year'] ?>/<?= sprintf('%02d', $prev['month']) ?>">
      ← <?= htmlspecialchars($monthNames[$prev['month']]) ?>
    </a>
    •
    <a href="/<?= $currentYear ?>/<?= sprintf('%02d', $currentMonth) ?>" class="current-month">
      <?= htmlspecialchars($monthNames[$currentMonth]) ?> <?= $currentYear ?>
    </a>
    <?php if (!DateUtils::isFutureMonth($next['month'], $next['year'])): ?> •
      <a href="/<?= $next['year'] ?>/<?= sprintf('%02d', $next['month']) ?>">
        <?= htmlspecialchars($monthNames[$next['month']]) ?> →
      </a>
    <?php endif; ?>
    • <a href="/">Startseite</a>
    <div class="powered-by">Powered by <?= htmlspecialchars($title) ?></div>
  </footer>
</body>
</html> 