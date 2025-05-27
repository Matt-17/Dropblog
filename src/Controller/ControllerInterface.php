<?php
namespace PainBlog\Controller;

use PainBlog\Utils\Router;

interface ControllerInterface
{
    public static function register(Router $router): void;
    public static function isApi(): bool;
}
