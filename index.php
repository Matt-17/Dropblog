<?php                              
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';    
use PainBlog\Utils\HashIdHelper;

require_once '_config/config.php';
require_once '_config/functions.php';

// URL-Pfad extrahieren
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);

// Entferne führende und abschließende Slashes
$path = trim($path, '/');

// Setze Standardwerte
$currentYear = date('Y');
$currentMonth = date('n');

// Routing
if (isset($_GET['site'])) {
    switch ($_GET['site']) {
       
        case 'post':
            if (!empty($_GET['id'])) {
                $url   = $_GET['id'];                                
                $id = HashIdHelper::decode($url);
                $post  = get_post_by_id($pdo, $id);

                if (!$post) {
                    header("HTTP/1.0 404 Not Found");
                    $content = '_content/404.php';
                } else {
                    $content  = '_content/post.php';
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                $content = '_content/404.php';
            }
            break;

        case 'month':
            if (isset($_GET['year']) && isset($_GET['month'])) {
                $currentYear = (int)$_GET['year'];
                $currentMonth = (int)$_GET['month'];
                $content = '_content/month.php';
            } else {
                header("HTTP/1.0 404 Not Found");
                $content = '_content/404.php';
            }
            break;

        default:
            header("HTTP/1.0 404 Not Found");
            $content = '_content/404.php';

    }
} elseif (empty($path)) {
    // Startseite
    $content = '_content/home.php';
} else {
    // 404
    header("HTTP/1.0 404 Not Found");
    $content = '_content/404.php';
}

// Prüfe ob die Content-Datei existiert
if (!file_exists($content)) {
    header("HTTP/1.0 404 Not Found");
    $content = '_content/404.php';
}

// Layout einbinden
include '_shared/_layout.php';
?> 