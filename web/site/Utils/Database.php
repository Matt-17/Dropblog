<?php
namespace Dropblog\Utils;

use PDO;
use PDOException;
use Dropblog\Config;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . Config::dbHost() . ";dbname=" . Config::dbName() . ";charset=" . Config::dbCharset();
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, Config::dbUser(), Config::dbPass(), $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$pdo;
    }
}
