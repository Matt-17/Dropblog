<?php
namespace PainBlog\Utils;

class Router
{
    private array $routes = [];
    private string $default404 = '_content/404.php';

    public function add(string $name, callable $handler): void
    {
        $this->routes[$name] = $handler;
    }

    public function dispatch(string $path): string
    {
        // Home
        if (isset($this->routes['']) && $path === '') {
            return ($this->routes[''])();
        }

        // Post: /post/ABCDEFGH
        if (isset($this->routes['post']) &&
            preg_match('#^post/([A-Za-z0-9]{8})$#', $path, $m)
        ) {
            return ($this->routes['post'])($m[1]);
        }

        // Month: /2025/05
        if (isset($this->routes['month']) &&
            preg_match('#^([0-9]{4})/([0-9]{2})$#', $path, $m)
        ) {
            return ($this->routes['month'])($m[1], $m[2]);
        }

        http_response_code(404);
        return $this->default404;
    }

    public function setDefault404(string $path): void
    {
        $this->default404 = $path;
    }
}
