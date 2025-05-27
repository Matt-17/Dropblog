<?php
// src/Controller/NotFoundController.php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;

class NotFoundController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Default-404-View
        $router->setDefault404('Shared/404.php');

        $router->add('404', function() {
            http_response_code(404);
            return ['view' => 'Shared/404.php', 'vars' => []];
        });
    }
    public function handle(array $s): array { return []; }
}
