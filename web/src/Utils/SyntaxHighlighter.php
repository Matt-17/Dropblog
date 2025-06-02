<?php

namespace Dropblog\Utils;

class SyntaxHighlighter
{
    /**
     * Highlight code based on language
     */
    public static function highlight(string $code, string $language): string
    {
        // Clean the code and escape HTML
        $code = trim($code);
        $escaped = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        
        switch (strtolower($language)) {
            case 'php':
                return self::highlightPhp($escaped);
            case 'csharp':
            case 'cs':
            case 'c#':
                return self::highlightCSharp($escaped);
            case 'javascript':
            case 'js':
                return self::highlightJavaScript($escaped);
            case 'css':
                return self::highlightCss($escaped);
            case 'html':
                return self::highlightHtml($escaped);
            case 'sql':
                return self::highlightSql($escaped);
            default:
                return self::highlightGeneric($escaped);
        }
    }

    /**
     * Highlight PHP - simple approach
     */
    private static function highlightPhp(string $code): string
    {
        // Simple keyword highlighting for PHP
        $keywords = ['php', 'echo', 'print', 'var', 'function', 'class', 'public', 'private', 'protected', 'static', 'return', 'if', 'else', 'foreach', 'for', 'while'];
        
        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', '<span class="keyword">' . $keyword . '</span>', $code);
        }
        
        // Highlight strings
        $code = preg_replace('/&quot;([^&]*?)&quot;/', '<span class="string">&quot;$1&quot;</span>', $code);
        $code = preg_replace('/&#039;([^&]*?)&#039;/', '<span class="string">&#039;$1&#039;</span>', $code);
        
        // Highlight comments
        $code = preg_replace('/\/\/(.*)$/m', '<span class="comment">//$1</span>', $code);
        
        return '<pre class="language-php"><code>' . $code . '</code></pre>';
    }

    /**
     * Simple C# highlighter
     */
    private static function highlightCSharp(string $code): string
    {
        $keywords = ['public', 'private', 'protected', 'void', 'string', 'int', 'bool', 'class', 'interface', 'namespace', 'using', 'if', 'else', 'return', 'new', 'this', 'static'];
        
        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', '<span class="keyword">' . $keyword . '</span>', $code);
        }
        
        // Highlight strings
        $code = preg_replace('/&quot;([^&]*?)&quot;/', '<span class="string">&quot;$1&quot;</span>', $code);
        
        // Highlight comments
        $code = preg_replace('/\/\/(.*)$/m', '<span class="comment">//$1</span>', $code);
        
        return '<pre class="language-csharp"><code>' . $code . '</code></pre>';
    }

    /**
     * Simple JavaScript highlighter
     */
    private static function highlightJavaScript(string $code): string
    {
        $keywords = ['function', 'var', 'let', 'const', 'if', 'else', 'for', 'while', 'return', 'class'];
        
        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', '<span class="keyword">' . $keyword . '</span>', $code);
        }
        
        // Highlight strings
        $code = preg_replace('/&quot;([^&]*?)&quot;/', '<span class="string">&quot;$1&quot;</span>', $code);
        
        // Highlight comments
        $code = preg_replace('/\/\/(.*)$/m', '<span class="comment">//$1</span>', $code);
        
        return '<pre class="language-javascript"><code>' . $code . '</code></pre>';
    }

    /**
     * Simple CSS highlighter
     */
    private static function highlightCss(string $code): string
    {
        // Highlight properties
        $code = preg_replace('/([a-zA-Z-]+)(\s*:\s*)/', '<span class="property">$1</span>$2', $code);
        
        return '<pre class="language-css"><code>' . $code . '</code></pre>';
    }

    /**
     * Simple HTML highlighter
     */
    private static function highlightHtml(string $code): string
    {
        // Highlight tags
        $code = preg_replace('/&lt;(\/?[a-zA-Z][a-zA-Z0-9]*)(.*?)&gt;/', '<span class="tag">&lt;$1$2&gt;</span>', $code);
        
        return '<pre class="language-html"><code>' . $code . '</code></pre>';
    }

    /**
     * Simple SQL highlighter
     */
    private static function highlightSql(string $code): string
    {
        $keywords = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'TABLE', 'ALTER', 'DROP'];
        
        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', '<span class="keyword">' . $keyword . '</span>', $code);
        }
        
        return '<pre class="language-sql"><code>' . $code . '</code></pre>';
    }

    /**
     * Generic highlighter for unknown languages
     */
    private static function highlightGeneric(string $code): string
    {
        return '<pre class="language-text"><code>' . $code . '</code></pre>';
    }
} 