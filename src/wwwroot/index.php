<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PainBlog\Config;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;

// Bootstrap
Config::init();
$pdo    = Database::getConnection();
$router = new Router($pdo);

// Auto-register Controller-Klassen
foreach (glob(__DIR__.'/../Controller/*Controller.php') as $file) {
    require_once $file;
    $class = 'PainBlog\\Controller\\'.basename($file, '.php');
    if (is_callable([$class,'register'])) {
        $class::register($router);
    }
}

// Dispatch
$path     = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$response = $router->dispatch($path);

// Set status code if provided
if (isset($response['status'])) {
    http_response_code($response['status']);
}

// Check if this is an API route
$controllerClass = 'PainBlog\\Controller\\' . ucfirst(explode('/', $path)[0]) . 'Controller';
if (class_exists($controllerClass) && is_callable([$controllerClass, 'isApi']) && $controllerClass::isApi()) {
    require __DIR__ . '/../Views/' . $response['view'];
    exit;
}

// Variablen für View
extract($response['vars'], EXTR_OVERWRITE);
$content = $response['view'];

// Layout laden
include __DIR__ . '/../Views/Layouts/Layout.php';
