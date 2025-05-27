<?php
namespace PainBlog\Controller;

use PainBlog\Utils\HashIdHelper;
use PainBlog\Utils\PostUtils;
use PainBlog\Utils\Router;

class PostController implements ControllerInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }

    public static function register(Router $router): void
    {
        // Registriere die Route "post/{hash}"
        $router->add('post', function(string $hash) use ($router) {
            // ID dekodieren und Daten holen
            $id   = HashIdHelper::decode($hash);
            $post = $id ? PostUtils::getPostById($router->getPdo(), $id) : null;

            if (!$post) {
                http_response_code(404);
                return ['view' => '_content/404.php', 'vars' => []];
            }

            return ['view' => '_content/post.php', 'vars' => ['post' => $post]];
        });
    }

    public function handle(array $segments): array
    {
        // bleibt leer, weil wir im Closure rendern
        return [];
    }
}
