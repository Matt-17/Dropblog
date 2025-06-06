<?php
namespace Dropblog\Controller;

use Dropblog\Utils\Database;
use Dropblog\Utils\Router;
use Dropblog\Utils\PostUtils;
use Dropblog\Utils\Localization;
use Dropblog\Controller\ControllerInterface;
use PDO;

class SearchController implements ControllerInterface
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
            'search',
            [$controller, 'show']
        );
    }

    public static function isApi(): bool
    {
        return false;
    }

    public function show(): array
    {
        $query = $_GET['q'] ?? '';
        $posts = [];
        $groupedPosts = [];
        $emptyMessage = '';
        $moreResultsExist = false;

        if (!empty($query)) {
            $posts = PostUtils::searchPosts($this->pdo, $query);
            
            if (count($posts) > 100) {
                $moreResultsExist = true;
                array_pop($posts);
            }

            if (empty($posts)) {
                $emptyMessage = Localization::t('messages.no_search_results') . ': ' . htmlspecialchars($query);
            } else {
                 $groupedPosts = PostUtils::groupPostsByDate($posts);
            }
        } else {
             $emptyMessage = Localization::t('common.enter_search_term');
        }

        $keywords = array_filter(explode(' ', $query));

        return [
            'view' => 'SearchPage.php',
            'vars' => [
                'groupedPosts' => $groupedPosts,
                'query' => htmlspecialchars($query),
                'emptyMessage' => $emptyMessage,
                'moreResultsExist' => $moreResultsExist,
                'keywords' => $keywords,
            ],
        ];
    }
} 