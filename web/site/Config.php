<?php
namespace Dropblog;

class Config
{
    public static function init(): void
    {
        self::loadEnv(__DIR__ . '/.env');
        date_default_timezone_set(self::timezone());
        error_reporting(E_ALL);
        ini_set('display_errors', self::debug() ? '1' : '0');
    }

    // Enable debug mode
    public static function debug(): bool
    {
        return getenv('DEBUG') === 'true';
    }

    // Database configuration
    public static function dbHost(): string     { return self::env('DB_HOST', 'localhost'); }
    public static function dbName(): string     { return self::env('DB_NAME', 'dropblog'); }
    public static function dbUser(): string     { return self::env('DB_USER', 'root'); }
    public static function dbPass(): string     { return self::env('DB_PASS', ''); }
    public static function dbCharset(): string  { return self::env('DB_CHARSET', 'utf8mb4'); }

    // API key used for update script
    public static function apiKey(): string     { return self::env('ADMIN_API_KEY', 'changeme'); }

    // Blog settings
    public static function blogTitle(): string  { return self::env('BLOG_TITLE', 'My Blog'); }

    // Localization
    public static function locale(): string     { return self::env('LOCALE', 'en-US'); } // e.g., 'en-US', 'de-DE', 'de-AT'

    // Timezone (used globally)
    public static function timezone(): string   { return self::env('TIMEZONE', 'Europe/Berlin'); }

    // Date format string
    public static function dateFormat(): string { return self::env('DATE_FORMAT', 'd. F Y'); }

    // URL generation settings
    public static function urlLength(): int      { return (int) self::env('URL_LENGTH', '8'); }
    public static function hashidsSalt(): string { return self::env('HASHIDS_SALT', 'dropblog'); }

    // Internal helpers
    private static function env(string $key, string $fallback): string
    {
        $val = getenv($key);
        return ($val !== false && $val !== '') ? $val : $fallback;
    }

    private static function loadEnv(string $path): void
    {
        if (!file_exists($path)) return;

        foreach (file($path) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$name, $value] = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
        }
    }
}
