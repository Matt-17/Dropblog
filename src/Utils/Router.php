<?php
namespace PainBlog\Utils;

class Route {
    public string $method;
    public string $pattern;
    public callable $handler;
    public bool $isApi;

    public function __construct(string $method, string $pattern, callable $handler, bool $isApi = false) {
        $this->method   = $method;
        $this->pattern  = '#^' . $pattern . '$#';
        $this->handler  = $handler;
        $this->isApi    = $isApi;
    }

    public function matches(string $path, string $method, array &$params): bool {
        if ($this->method !== $method) return false;
        if (!preg_match($this->pattern, $path, $m)) return false;
        // Parameter-Namen aus (?P<name>…)
        foreach ($m as $key => $val) {
            if (is_string($key)) $params[$key] = $val;
        }
        return true;
    }
}

class Router {
    private \PDO $pdo;
    /** @var Route[] */
    private array $routes = [];
    private string $default404 = 'Shared/404.php';

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
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
                if ($route->isApi) return $result;
                return $result;
            }
        }
        http_response_code(404);
        if ($method==='GET') {
            return ['view'=>$this->default404,'vars'=>[]];
        }
        return [
            'view'=>'Shared/json.php',
            'vars'=>['data'=>['success'=>false,'message'=>'Not found','code'=>404]]
        ];
    }

    public function setDefault404(string $view): void {
        $this->default404 = $view;
    }
}
