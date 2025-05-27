<?php
namespace PainBlog;

class Config
{
    // Datenbank-Konfiguration
    public const DB_HOST = '{{DB_HOST}}';
    public const DB_NAME = '{{DB_NAME}}';
    public const DB_USER = '{{DB_USER}}';
    public const DB_PASS = '{{DB_PASS}}';
    public const DB_CHARSET = 'utf8mb4';

    // API-Key für Update-Skript
    public const ADMIN_API_KEY = '{{ADMIN_API_KEY}}';

    // Blog-Konfiguration
    public const BLOG_TITLE = '{{BLOG_TITLE}}';

    // URL-Generierung
    public const URL_LENGTH = 8;
    public const HASHIDS_SALT = 'painblog';

    // Zeitzone (als Konstante oder init)
    public const TIMEZONE = 'Europe/Berlin';
    
    // Initialisiere allgemeine Einstellungen (z.B. Zeitzone, Error Reporting)
    public static function init(): void
    {
        date_default_timezone_set(self::TIMEZONE);
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }
}
