<?php
use Dropblog\Config;
use Dropblog\Utils\DateUtils;
use Dropblog\Utils\Localization;

// Start output buffering
ob_start();

// Define the views directory path
define('VIEWS_PATH', __DIR__ . '/..');

// Initialize localization
Localization::initialize(__DIR__ . '/../../resources');

// Titel direkt aus Config
$title = Config::BLOG_TITLE;

// Sicherstellen, dass die View-Datei gesetzt ist
if (!isset($content)) {
    throw new \RuntimeException(Localization::t('messages.missing_view'));
}

// Defaults für Jahr/Monat, falls ein Controller sie nicht explizit gesetzt hat
$currentYear  = $currentYear  ?? date('Y');
$currentMonth = $currentMonth ?? date('n');

// Monatsnamen und Nachbar-Monate aus DateUtils
$monthNames = DateUtils::getMonthNames();
$prev       = DateUtils::getPreviousMonth($currentMonth, $currentYear);
$next       = DateUtils::getNextMonth($currentMonth, $currentYear);

// Get the view content first
$viewPath = VIEWS_PATH . '/' . ltrim($content, '/');
if (!file_exists($viewPath)) {
    throw new \RuntimeException(Localization::t('messages.view_not_found') . ": {$viewPath}");
}

// Capture the view output
ob_start();
include $viewPath;
$viewContent = ob_get_clean();

// Now we can safely set response codes if needed
if (isset($status)) {
    http_response_code($status);
}

$currentLocale = Localization::getCurrentLocale();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLocale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <!-- Prism.js CSS for syntax highlighting -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
</head>
<body>
  <header class="header">
    <h1><?= htmlspecialchars($title) ?></h1>
  </header>

  <div class="content">
    <?= $viewContent ?>
  </div>

  <footer class="footer">
    <a href="/<?= $prev['year'] ?>/<?= sprintf('%02d', $prev['month']) ?>" title="<?= Localization::t('navigation.previous_month') ?>">
      ← <?= htmlspecialchars($monthNames[$prev['month']]) ?>
    </a>
    •
    <a href="/<?= $currentYear ?>/<?= sprintf('%02d', $currentMonth) ?>" class="current-month" title="<?= Localization::t('navigation.current_month') ?>">
      <?= htmlspecialchars($monthNames[$currentMonth]) ?> <?= $currentYear ?>
    </a>
    <?php if (!DateUtils::isFutureMonth($next['month'], $next['year'])): ?> •
      <a href="/<?= $next['year'] ?>/<?= sprintf('%02d', $next['month']) ?>" title="<?= Localization::t('navigation.next_month') ?>">
        <?= htmlspecialchars($monthNames[$next['month']]) ?> →
      </a>
    <?php endif; ?>
    • <a href="/"><?= Localization::t('common.home') ?></a>
    • <a href="/search"><?= Localization::t('common.search') ?></a>
    <div class="powered-by"><?= Localization::t('common.powered_by', ['title' => $title]) ?></div>
  </footer>

  <!-- Prism.js JavaScript for syntax highlighting -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
<?php
// End output buffering and send the content
ob_end_flush(); 