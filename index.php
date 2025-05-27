<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use PainBlog\Config;
use PainBlog\Utils\HashIdHelper;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;
use PainBlog\Utils\PostUtils;
use PainBlog\Utils\DateUtils;
use PainBlog\Utils\MarkdownUtils;

// Initialisierung
Config::init();                      
define('BLOG_TITLE', Config::BLOG_TITLE); // Damit _layout.php die Konstante kennt

// Datenbankverbindung
$pdo = Database::getConnection();     

// Setze Jahr/Monat Defaults (kann durch $_GET überschrieben werden)
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

// Router erstellen
$router = new Router();
$router->setDefault404('_content/404.php');

// Startseite
$router->add('', fn() => '_content/home.php');

// Monat
$router->add('month', function() {
    if (isset($_GET['year'], $_GET['month'])) {
        return '_content/month.php';
    }
    http_response_code(404);
    return '_content/404.php';
});

// Post-Route
$router->add('post', function($path) use ($pdo) {
    // Hole die ID aus dem Path, z.B. 'post/o72yl5njhi'
    $parts = explode('/', $path);
    $hashId = $parts[1] ?? null;
    if ($hashId) {
        $id = HashIdHelper::decode($hashId);
        $post = get_post_by_id($pdo, $id);
        if ($post) {
            $GLOBALS['post'] = $post;
            return '_content/post.php';
        }
    }
    http_response_code(404);
    return '_content/404.php';
});


// Dispatch der Route
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$content = $router->dispatch($path);

// Prüfen, ob Content-Datei existiert
if (!file_exists($content)) {
    http_response_code(404);
    $content = '_content/404.php';
}

// Globale Wrapper-Funktionen, damit alles weiterhin funktioniert
function get_month_names() { return DateUtils::getMonthNames(); }
function get_previous_month($month, $year) { return DateUtils::getPreviousMonth($month, $year); }
function get_next_month($month, $year) { return DateUtils::getNextMonth($month, $year); }
function is_future_month($month, $year) { return DateUtils::isFutureMonth($month, $year); }
function format_date($date) { return DateUtils::formatDate($date); }
function get_post_by_id($pdo, $id) { return PostUtils::getPostById($pdo, $id); }
function get_grouped_posts($pdo, $where_clause, $params = []) { return PostUtils::getGroupedPosts($pdo, $where_clause, $params); }
function id_to_url($id) { return PostUtils::idToUrl($id); }
function markdown_to_html($markdown) { return MarkdownUtils::markdownToHtml($markdown); }

// Layout einbinden
include '_shared/_layout.php';
