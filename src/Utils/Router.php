<?php
namespace PainBlog\Utils;

class Router
{
    private \PDO $pdo;
    private array $routes = [];
    private string $default404 = 'Shared/404.php';
    private array $apiRoutes = ['admin/update'];

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
        // Check if this is an API route
        $isApiRoute = in_array($path, $this->apiRoutes);

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
            if ($key === 'admin/update' && $path === 'admin/update') {
                $result = $handler();
                if ($isApiRoute) {
                    // For API routes, don't use the layout
                    return $result;
                }
                return $result;
            }
        }

        if ($isApiRoute) {
            http_response_code(404);
            return [
                'view' => 'Shared/json.php',
                'vars' => [
                    'data' => [
                        'success' => false,
                        'message' => 'Not found',
                        'code' => 404
                    ]
                ]
            ];
        }

        http_response_code(404);
        return ['view' => $this->default404, 'vars' => []];
    }

    public function setDefault404(string $path): void
    {
        $this->default404 = $path;
    }
}
