<?php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;
use PainBlog\Utils\PostUtils;
use PainBlog\Controller\ControllerInterface;

class HomeController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Route für Startseite („letzte Woche“)
        $router->add('', function() use ($router) {
            $pdo         = $router->getPdo();
            $posts       = PostUtils::getPostsOfLastWeek($pdo);
            $grouped     = PostUtils::groupPostsByDate($posts);
            return [
                'view' => '_content/home.php',
                'vars' => [
                    'groupedPosts' => $grouped,
                    'emptyMessage' => 'Noch keine Posts vorhanden.',
                ],
            ];
        });
    }

    // Nicht benutzt, weil wir in der Closure rendern
    public function handle(array $segments): array
    {
        return [];
    }
}
