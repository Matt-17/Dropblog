<?php
// src/Controller/HomeController.php
namespace Dropblog\Controller;

use Dropblog\Utils\PostUtils;
use Dropblog\Utils\Router;
use Dropblog\Controller\ControllerInterface;
use PDO;

class HomeController implements ControllerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function register(Router $router): void
    {
        // Instanz erstellen und PDO übergeben
        $controller = new self($router->getPdo());

        $router->add(
            'GET',
            '',               // Pattern für "/"
            [$controller, 'index']
        );
    }     

    public static function isApi(): bool
    {
        return false;
    }

    public function index(): array
    {
        // jetzt hier $this->pdo nutzen
        $posts        = PostUtils::getPostsOfLastWeek($this->pdo);
        $groupedPosts = PostUtils::groupPostsByDate($posts);

        return [
            'view' => 'ListPage.php',
            'vars' => [
                'groupedPosts' => $groupedPosts,
                'emptyMessage' => 'Noch keine Posts vorhanden.',
                'moreResultsExist' => false,
            ],
        ];
    }
}
