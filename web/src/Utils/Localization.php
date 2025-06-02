<?php

namespace Dropblog\Utils;

use Dropblog\Config;

class Localization
{
    private static ?string $currentLocale = null;
    private static array $translations = [];
    private static string $defaultLocale = 'en-US';
    private static ?string $resourcesPath = null;

    public static function initialize(string $resourcesPath): void
    {
        self::$resourcesPath = $resourcesPath;
        
        // Get locale from Config, fallback to default
        $configLocale = defined('Dropblog\Config::LOCALE') ? Config::LOCALE : self::$defaultLocale;
        self::setLocale($configLocale);
    }

    public static function setLocale(string $locale): void
    {
        self::$currentLocale = $locale;
        self::loadTranslations($locale);
    }

    public static function getCurrentLocale(): string
    {
        return self::$currentLocale ?? self::$defaultLocale;
    }

    public static function translate(string $key, array $params = []): string
    {
        $locale = self::getCurrentLocale();
        
        // Try to find translation using fallback chain
        $translation = self::findTranslation($key, $locale);
        
        // If still not found, return the key itself
        if ($translation === null) {
            $translation = $key;
        }

        // Replace parameters
        foreach ($params as $param => $value) {
            $translation = str_replace('{' . $param . '}', $value, $translation);
        }

        return $translation;
    }

    public static function t(string $key, array $params = []): string
    {
        return self::translate($key, $params);
    }

    private static function findTranslation(string $key, string $locale): ?string
    {
        // Build fallback chain based on locale
        $fallbackFiles = self::buildFallbackChain($locale);
        
        foreach ($fallbackFiles as $filename) {
            if (!isset(self::$translations[$filename])) {
                self::loadTranslationFile($filename);
            }
            
            if (isset(self::$translations[$filename][$key])) {
                return self::$translations[$filename][$key];
            }
        }
        
        return null;
    }

    private static function buildFallbackChain(string $locale): array
    {
        $fallbackFiles = [];
        
        // Always start with default strings.json
        $fallbackFiles[] = 'strings.json';
        
        if ($locale !== 'en-US' && $locale !== 'en') {
            // For non-English locales, add language and regional variants
            
            // Extract language code (e.g., 'de' from 'de-DE')
            $parts = explode('-', $locale);
            $language = $parts[0];
            
            // Add language-specific file (e.g., strings.de.json)
            $languageFile = "strings.{$language}.json";
            if (!in_array($languageFile, $fallbackFiles)) {
                $fallbackFiles[] = $languageFile;
            }
            
            // Add regional file (e.g., strings.de-DE.json)
            $regionalFile = "strings.{$locale}.json";
            if (!in_array($regionalFile, $fallbackFiles)) {
                $fallbackFiles[] = $regionalFile;
            }
        }
        
        return $fallbackFiles;
    }

    private static function loadTranslations(string $locale): void
    {
        $fallbackFiles = self::buildFallbackChain($locale);
        
        foreach ($fallbackFiles as $filename) {
            self::loadTranslationFile($filename);
        }
    }

    private static function loadTranslationFile(string $filename): void
    {
        if (self::$resourcesPath === null) {
            throw new \RuntimeException('Localization system not initialized. Call Localization::initialize() first.');
        }

        if (!isset(self::$translations[$filename])) {
            $filePath = self::$resourcesPath . "/locales/{$filename}";
            
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $translations = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($translations)) {
                    self::$translations[$filename] = self::flattenArray($translations);
                } else {
                    self::$translations[$filename] = [];
                }
            } else {
                self::$translations[$filename] = [];
            }
        }
    }

    /**
     * Flatten nested array to dot notation keys
     * e.g., ['common' => ['search' => 'Search']] becomes ['common.search' => 'Search']
     */
    private static function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
} 