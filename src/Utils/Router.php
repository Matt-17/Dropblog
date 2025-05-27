<?php
namespace PainBlog\Utils;

class Route {
    /** @var string */
    public $method;
    /** @var string */
    public $pattern;
    /** @var callable */
    public $handler;
    /** @var bool */
    public $isApi;

    public function __construct(string $method, string $pattern, callable $handler, bool $isApi = false) {
        $this->method  = $method;
        $this->pattern = '#^' . $pattern . '$#';
        $this->handler = $handler;
        $this->isApi   = $isApi;
    }

    public function matches(string $path, string $method, array &$params): bool {
        if ($this->method !== $method) return false;
        if (!preg_match($this->pattern, $path, $m)) return false;
        foreach ($m as $key => $val) {
            if (is_string($key)) $params[$key] = $val;
        }
        return true;
    }
}

class Router {
    /** @var \PDO */
    public $pdo;
    /** @var Route[] */
    private $routes = [];
    /** @var string */
    private $default404 = 'Shared/404.php';

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPdo(): \PDO {
        return $this->pdo;
    }

    public function add(string $method, string $pattern, callable $handler, bool $isApi = false): void {
        $this->routes[] = new Route($method, $pattern, $handler, $isApi);
    }

    public function addLast(string $method, string $pattern, callable $handler, bool $isApi = false): void {
        // Remove any existing catch-all routes
        $this->routes = array_filter($this->routes, function($route) {
            return $route->pattern !== '#^.*$#';
        });
        // Add the new route at the end
        $this->routes[] = new Route($method, $pattern, $handler, $isApi);
    }

    public function dispatch(string $path): array {
        $method = $_SERVER['REQUEST_METHOD'];
                
        // Check if we have query string parameters that match our routes
        if (isset($_GET['site']) && isset($_GET['id']) && $_GET['site'] === 'post') {
            $path = 'post/' . $_GET['id'];
        } elseif (isset($_GET['site']) && isset($_GET['year']) && isset($_GET['month']) && $_GET['site'] === 'month') {
            $path = $_GET['year'] . '/' . $_GET['month'];
        }

        foreach ($this->routes as $route) {
            $params = [];
            if ($route->matches($path, $method, $params)) {
                $result = call_user_func_array($route->handler, $params);
                if ($route->isApi) {
                    // For API routes, ensure we have the correct structure
                    if (!isset($result['vars']['data'])) {
                        $result['vars']['data'] = $result['vars'] ?? [];
                    }
                }
                return $result;
            }
        }
        
        if ($method === 'GET') {
            return [
                'view' => 'Shared/404.php',
                'vars' => [
                    'debug' => $debug,
                    'pageTitle' => '404 – Eintrag nicht gefunden'
                ],
                'status' => 404
            ];
        }
        return [
            'view' => 'Shared/json.php',
            'vars' => [
                'data' => [
                    'success' => false,
                    'message' => 'Not found',
                    'code' => 404,
                    'debug' => $debug
                ]
            ],
            'status' => 404
        ];
    }

    public function setDefault404(string $view): void {
        $this->default404 = $view;
    }
}
