<?php
// src/Controller/HomeController.php
namespace PainBlog\Controller;

use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;

class HomeController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        $router->add('', function() use ($router) {
            $pdo           = $router->getPdo();
            $posts         = PostUtils::getPostsOfLastWeek($pdo);
            $groupedPosts  = PostUtils::groupPostsByDate($posts);

            return [
                'view' => 'Components/PostList.php',
                'vars' => [
                    'groupedPosts' => $groupedPosts,
                    'emptyMessage' => 'Noch keine Posts vorhanden.',
                ],
            ];
        });
    }
    public function handle(array $s): array { return []; }
}
