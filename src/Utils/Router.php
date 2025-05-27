<?php
namespace PainBlog\Utils;

class Router
{
    private \PDO $pdo;
    private array $routes = [];
    private string $default404 = 'Shared/404.php';

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function add(string $name, callable $handler): void
    {
        $this->routes[$name] = $handler;
    }

    public function dispatch(string $path): array
    {
        // Home, month, post etc. per Pattern-Matching
        foreach ($this->routes as $key => $handler) {
            if ($key === '' && $path === '') {
                return $handler();
            }
            if ($key === 'post' && preg_match('#^post/([A-Za-z0-9]{8})$#', $path, $m)) {
                return $handler($m[1]);
            }
            if ($key === 'month' && preg_match('#^([0-9]{4})/([0-9]{2})$#', $path, $m)) {
                return $handler($m[1], $m[2]);
            }
        }
        http_response_code(404);
        return ['view' => $this->default404, 'vars' => []];
    }

    public function setDefault404(string $path): void
    {
        $this->default404 = $path;
    }
}
