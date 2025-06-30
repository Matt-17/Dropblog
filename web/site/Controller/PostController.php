<?php
// src/Controller/PostController.php
namespace Dropblog\Controller;

use Dropblog\Utils\Database;
use Dropblog\Utils\HashIdHelper;
use Dropblog\Utils\PostUtils;
use Dropblog\Utils\Router;
use Dropblog\Controller\ControllerInterface;
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
        $id = HashIdHelper::decode($hash);
        $post = $id ? PostUtils::getPostById($this->pdo, $id) : null;
     
        if (!$post) {
            return [
                'view' => 'Shared/404.php',
                'vars' => ['pageTitle' => '404 – Eintrag nicht gefunden'],
            ];
        }

        $groupedPosts = PostUtils::groupPostsByDate([$post]);

        return [
            'view' => 'ListPage.php',
            'vars' => [
                'groupedPosts' => $groupedPosts,
                'emptyMessage' => '',
                'moreResultsExist' => false,
                'currentYear'  => $post->getYear(),
                'currentMonth' => $post->getMonth(),
            ],
        ];
    }
}
