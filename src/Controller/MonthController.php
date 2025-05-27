<?php
// src/Controller/MonthController.php
namespace PainBlog\Controller;

use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;

class MonthController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        $router->add('month', function(string $year, string $month) use ($router) {
            $y = (int)$year; $m = (int)$month;
            $pdo           = $router->getPdo();
            $posts         = PostUtils::getPostsByMonth($pdo, $y, $m);
            $groupedPosts  = PostUtils::groupPostsByDate($posts);

            return [
                'view' => '_shared/post_group.php',
                'vars' => [
                    'groupedPosts'  => $groupedPosts,
                    'emptyMessage'  => 'Keine Posts für diesen Monat vorhanden.',
                    'currentYear'   => $y,
                    'currentMonth'  => $m,
                ],
            ];
        });
    }
    public function handle(array $s): array { return []; }
}
