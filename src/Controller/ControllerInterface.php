<?php
namespace Dropblog\Controller;

use Dropblog\Utils\Router;

interface ControllerInterface
{
    public static function register(Router $router): void;
    public static function isApi(): bool;
}
