<?php
// src/Controller/NotFoundController.php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;

class NotFoundController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Default-404-View
        $router->setDefault404('_shared/404.php');
    }
    public function handle(array $s): array
    {
        http_response_code(404);
        return ['view' => '_shared/404.php', 'vars' => []];
    }
}
