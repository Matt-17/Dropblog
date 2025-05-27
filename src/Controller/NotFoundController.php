<?php
// src/Controller/NotFoundController.php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;

class NotFoundController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Default-404 festlegen
        $router->setDefault404('Shared/404.php');

        $controller = new self();
        // Ganz am Ende: alle GET-Requests, die sonst kein Match hatten
        $router->add(
            'GET',
            '.*',
            [$controller, 'handle']
        );
    } 

    public static function isApi(): bool
    {
        return false;
    }

    public function handle(array $segments): array
    {
        http_response_code(404);
        return [
            'view' => 'Shared/404.php',
            'vars' => []
        ];
    }
}
