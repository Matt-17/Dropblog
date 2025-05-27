<?php
namespace PainBlog\Utils;

class Router
{
    private array $routes = [];
    private string $default404 = '_content/404.php';

    public function add(string $path, callable $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function dispatch(string $path): string
    {
        foreach ($this->routes as $route => $handler) {
            // Exakte Übereinstimmung
            if ($route === $path) {
                return $handler();
            }
            // Prüfe auf Prefix-Matching, z.B. 'post' für 'post/{id}'
            if (strpos($path, $route . '/') === 0) {
                return $handler($path);
            }
        }

        http_response_code(404);
        return $this->default404;
    }

    public function setDefault404(string $path): void
    {
        $this->default404 = $path;
    }
}
