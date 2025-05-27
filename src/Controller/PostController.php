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
                // $pageTitle könntest du hier setzen, wird in 404.php genutzt
                return [
                    'view' => '_shared/404.php',
                    'vars' => ['pageTitle' => '404 – Eintrag nicht gefunden'],
                ];
            }

            $groupedPosts = PostUtils::groupPostsByDate([$post]);

            return [
                'view' => '_shared/post_group.php',
                'vars' => [
                    'groupedPosts' => $groupedPosts,
                    'emptyMessage' => '',
                    'currentYear'  => (int)$post['date'][0-3], // wenn Datum parsen nötig
                    'currentMonth' => (int)substr($post['date'],5,2),
                ],
            ];
        });
    }
    public function handle(array $s): array { return []; }
}
