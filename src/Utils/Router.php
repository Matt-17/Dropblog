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

    public function dispatch(string $path): array {
        $method = $_SERVER['REQUEST_METHOD'];
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
                'view' => $this->default404,
                'vars' => [],
                'status' => 404
            ];
        }
        return [
            'view' => 'Shared/json.php',
            'vars' => ['data' => ['success'=>false,'message'=>'Not found','code'=>404]],
            'status' => 404
        ];
    }

    public function setDefault404(string $view): void {
        $this->default404 = $view;
    }
}
