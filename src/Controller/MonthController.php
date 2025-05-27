<?php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;
use PainBlog\Utils\PostUtils;
use PainBlog\Controller\ControllerInterface;

class MonthController implements ControllerInterface
{
    public static function register(Router $router): void
    {
        // Route für Monats-Archiv /YYYY/MM
        $router->add('month', function(string $year, string $month) use ($router) {
            $y = (int)$year;
            $m = (int)$month;
            $pdo     = $router->getPdo();
            $posts   = PostUtils::getPostsByMonth($pdo, $y, $m);
            $grouped = PostUtils::groupPostsByDate($posts);
            return [
                'view' => '_content/month.php',
                'vars' => [
                    'groupedPosts'  => $grouped,
                    'emptyMessage'  => 'Keine Posts für diesen Monat vorhanden.',
                    'currentYear'   => $y,
                    'currentMonth'  => $m,
                ],
            ];
        });
    }

    public function handle(array $segments): array
    {
        return [];
    }
}
