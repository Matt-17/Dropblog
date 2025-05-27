<?php
require_once __DIR__ . '/vendor/autoload.php';

use PainBlog\Config;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;

// Bootstrap
Config::init();
define('BLOG_TITLE', Config::BLOG_TITLE);
$pdo    = Database::getConnection();
$router = new Router($pdo);

// Auto‐register aller Controller‐Klassen
foreach (glob(__DIR__ . '/src/Controller/*Controller.php') as $file) {
    require_once $file;
    $class = 'PainBlog\\Controller\\' . basename($file, '.php');
    if (method_exists($class, 'register')) {
        $class::register($router);
    }
}

// Dispatch und View‐Rendering
$path     = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$response = $router->dispatch($path);
extract($response['vars'], EXTR_OVERWRITE);
$content  = $response['view'];
include '_shared/_layout.php';
