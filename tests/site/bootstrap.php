<?php
/**
 * PHPUnit Bootstrap File for Dropblog Tests
 * 
 * This file is loaded before any tests are run and sets up
 * the testing environment.
 */

namespace Dropblog\Utils;

use PDO;

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define testing environment BEFORE loading anything
define('TESTING', true);

class Database
{
    private static ?PDO $testConnection = null;

    public static function getConnection(): PDO
    {
        if (self::$testConnection === null) {
            self::$testConnection = new PDO('sqlite::memory:');
            self::$testConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::setupTestSchema();
        }

        return self::$testConnection;
    }

    private static function setupTestSchema(): void
    {
        self::$testConnection->exec("
            CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                content TEXT NOT NULL,  
                post_type_id INTEGER DEFAULT 1,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        self::$testConnection->exec("
            CREATE TABLE post_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon_filename VARCHAR(255) NOT NULL,
                is_active BOOLEAN DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        self::$testConnection->exec("
            INSERT INTO post_types (slug, name, description, icon_filename, sort_order) VALUES
            ('note', 'Note', 'Quick thoughts and updates', 'note.svg', 1),
            ('link', 'Link', 'Shared links with commentary', 'link.svg', 2),
            ('photo', 'Photo', 'Photo posts with captions', 'photo.svg', 3)
        ");

        self::$testConnection->exec("
            CREATE TABLE migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename VARCHAR(255) NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public static function resetTestDatabase(): void
    {
        if (self::$testConnection !== null) {
            self::$testConnection->exec("DELETE FROM posts");
            self::$testConnection->exec("DELETE FROM migrations WHERE filename != '001_create_migrations_table.sql'");
            self::$testConnection->exec("DELETE FROM post_types");
            self::$testConnection->exec("
                INSERT INTO post_types (slug, name, description, icon_filename, sort_order) VALUES
                ('note', 'Note', 'Quick thoughts and updates', 'note.svg', 1),
                ('link', 'Link', 'Shared links with commentary', 'link.svg', 2),
                ('photo', 'Photo', 'Photo posts with captions', 'photo.svg', 3)
            ");
        }
    }
}

    // Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Global helper for tests
function resetTestDatabase(): void 
{
    \Dropblog\Utils\Database::resetTestDatabase();
}