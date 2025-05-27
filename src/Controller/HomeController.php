<?php
// src/Controller/HomeController.php
namespace PainBlog\Controller;

use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;
use PainBlog\Controller\ControllerInterface;

class HomeController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        $controller = new self();
        $router->add(
            'GET',
            '',
            [$controller, 'index']
        );
    }  

    public static function isApi(): bool
    {
        return false;
    }

    public function index(): array
    {
        $pdo           = Router::getPdo(); // oder $this->pdo, je nach Umsetzung
        $posts         = PostUtils::getPostsOfLastWeek($pdo);
        $groupedPosts  = PostUtils::groupPostsByDate($posts);

        return [
            'view' => 'Components/PostList.php',
            'vars' => [
                'groupedPosts' => $groupedPosts,
                'emptyMessage' => 'Noch keine Posts vorhanden.',
            ],
        ];
    }
}
