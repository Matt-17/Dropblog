<?php
// src/Controller/PostController.php
namespace PainBlog\Controller;

use PainBlog\Utils\Database;
use PainBlog\Utils\HashIdHelper;
use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;
use PainBlog\Controller\ControllerInterface;
use PDO;

class PostController implements ControllerInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public static function register(Router $router): void
    {
        $controller = new self();
        $router->add(
            'GET',
            'post/(?P<hash>[A-Za-z0-9]{8})',
            [$controller, 'show']
        );
    } 

    public static function isApi(): bool
    {
        return false;
    }

    public function show(string $hash): array
    {
        $id   = HashIdHelper::decode($hash);
        $post = $id ? PostUtils::getPostById($this->pdo, $id) : null;

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
    }
}
