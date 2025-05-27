<?php
// src/Controller/PostController.php
namespace PainBlog\Controller;

use PainBlog\Utils\HashIdHelper;
use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;

class PostController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        $router->add('post', function(string $hash) use ($router) {
            $pdo  = $router->getPdo();
            $id   = HashIdHelper::decode($hash);
            $post = $id ? PostUtils::getPostById($pdo, $id) : null;

            if (!$post) {
                http_response_code(404);
                return [
                    'view' => 'Shared/404.php',
                    'vars' => ['pageTitle' => '404 – Eintrag nicht gefunden'],
                ];
            }

            $groupedPosts = PostUtils::groupPostsByDate([$post]);

            return [
                'view' => 'Components/PostList.php',
                'vars' => [
                    'groupedPosts' => $groupedPosts,
                    'emptyMessage' => '',
                    'currentYear'  => $post->getYear(),
                    'currentMonth' => $post->getMonth(),
                ],
            ];
        });
    }
    public function handle(array $s): array { return []; }
}
