<?php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;
use PainBlog\Controller\ControllerInterface;

class NotFoundController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Setze das 404-Template als Fallback
        $router->setDefault404('_content/404.php');
    }

    public function handle(array $segments): array
    {
        http_response_code(404);
        return [
            'view' => '_content/404.php',
            'vars' => [],
        ];
    }
}
