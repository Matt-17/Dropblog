<?php
// src/Controller/MonthController.php
namespace PainBlog\Controller;

use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Database;
use PainBlog\Utils\Router;
use PainBlog\Controller\ControllerInterface;
use PDO;

class MonthController implements ControllerInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public static function register(Router $router): void
    {
        $controller = new self();
        // Pattern mit Named Groups year und month
        $router->add(
            'GET',
            '(?P<year>[0-9]{4})/(?P<month>[0-9]{2})',
            [$controller, 'show']
        );
    }

    public function show(string $year, string $month): array
    {
        $y = (int)$year;
        $m = (int)$month;
        $posts        = PostUtils::getPostsByMonth($this->pdo, $y, $m);
        $groupedPosts = PostUtils::groupPostsByDate($posts);

        return [
            'view' => 'Components/PostList.php',
            'vars' => [
                'groupedPosts'  => $groupedPosts,
                'emptyMessage'  => 'Keine Posts für diesen Monat vorhanden.',
                'currentYear'   => $y,
                'currentMonth'  => $m,
            ],
        ];
    }
}
