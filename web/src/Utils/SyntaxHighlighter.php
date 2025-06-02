<?php

namespace Dropblog\Utils;

class SyntaxHighlighter // Renamed from SimpleSyntaxHighlighter
{
    public static function highlight(string $code, string $language): string
    {
        $code = trim($code);
        
        switch (strtolower($language)) {
            case 'csharp':
            case 'cs':
            case 'c#':
                return self::highlightCSharp($code);
            case 'php':
                return self::highlightPhp($code);
            case 'html':
                return self::highlightHtml($code);
            case 'javascript':
            case 'js':
                return self::highlightJavaScript($code);
            case 'css':
                return self::highlightCss($code);
            case 'sql':
                return self::highlightSql($code);
            default:
                return '<pre class="language-' . htmlspecialchars($language) . '"><code>' . htmlspecialchars($code) . '</code></pre>';
        }
    }
    
    private static function highlightCSharp(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            
            // Highlight keywords - but only as whole words
            $keywords = ['private', 'public', 'protected', 'void', 'string', 'int', 'bool', 'class', 'interface', 'return', 'new', 'this', 'static'];
            foreach ($keywords as $keyword) {
                $highlighted = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', '###KEYWORD_START###' . $keyword . '###KEYWORD_END###', $highlighted);
            }
            
            // Highlight comments
            if (preg_match('/^(\s*)\/\/(.*)$/', $highlighted, $matches)) {
                $highlighted = $matches[1] . '###COMMENT_START###//' . $matches[2] . '###COMMENT_END###';
            }
            
            // Highlight strings
            $highlighted = preg_replace('/&quot;([^&]*?)&quot;/', '###STRING_START###&quot;$1&quot;###STRING_END###', $highlighted);
            
            // Replace markers with actual HTML
            $highlighted = str_replace(['###KEYWORD_START###', '###KEYWORD_END###'], ['<span class="keyword">', '</span>'], $highlighted);
            $highlighted = str_replace(['###COMMENT_START###', '###COMMENT_END###'], ['<span class="comment">', '</span>'], $highlighted);
            $highlighted = str_replace(['###STRING_START###', '###STRING_END###'], ['<span class="string">', '</span>'], $highlighted);
            
            $result[] = $highlighted;
        }
        
        return '<pre class="language-csharp"><code>' . implode("\n", $result) . '</code></pre>';
    }
    
    private static function highlightPhp(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            
            // Highlight keywords
            $keywords = ['php', 'echo', 'print', 'function', 'class', 'public', 'private', 'return', 'if', 'else'];
            foreach ($keywords as $keyword) {
                $highlighted = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', '###KEYWORD_START###' . $keyword . '###KEYWORD_END###', $highlighted);
            }
            
            // Highlight comments
            if (preg_match('/^(\s*)\/\/(.*)$/', $highlighted, $matches)) {
                $highlighted = $matches[1] . '###COMMENT_START###//' . $matches[2] . '###COMMENT_END###';
            }
            
            // Highlight strings
            $highlighted = preg_replace('/&quot;([^&]*?)&quot;/', '###STRING_START###&quot;$1&quot;###STRING_END###', $highlighted);
            
            // Replace markers with actual HTML
            $highlighted = str_replace(['###KEYWORD_START###', '###KEYWORD_END###'], ['<span class="keyword">', '</span>'], $highlighted);
            $highlighted = str_replace(['###COMMENT_START###', '###COMMENT_END###'], ['<span class="comment">', '</span>'], $highlighted);
            $highlighted = str_replace(['###STRING_START###', '###STRING_END###'], ['<span class="string">', '</span>'], $highlighted);
            
            $result[] = $highlighted;
        }
        
        return '<pre class="language-php"><code>' . implode("\n", $result) . '</code></pre>';
    }

    private static function highlightHtml(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            // Highlight tags and attributes (simplified)
            $highlighted = preg_replace('/(&lt;\/?)([a-zA-Z0-9\-]+)/i', '$1<span class="tag">$2</span>', $highlighted);
            $highlighted = preg_replace('/([a-zA-Z0-9\-]+)=(&quot;[^&quot;]*&quot;|'[^']*'|[\w\-]+)/i', '<span class="attr">$1</span>=<span class="string">$2</span>', $highlighted);
            $result[] = $highlighted;
        }
        return '<pre class="language-html"><code>' . implode("\n", $result) . '</code></pre>';
    }

    private static function highlightJavaScript(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            $keywords = ['function', 'var', 'let', 'const', 'if', 'else', 'for', 'while', 'return', 'class', 'extends', 'import', 'export', 'try', 'catch', 'finally', 'await', 'async', 'new', 'this'];
            foreach ($keywords as $keyword) {
                $highlighted = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/', '###KEYWORD_START###' . $keyword . '###KEYWORD_END###', $highlighted);
            }
            if (preg_match('/^(\s*)\/\/(.*)$/', $highlighted, $matches)) {
                $highlighted = $matches[1] . '###COMMENT_START###//' . $matches[2] . '###COMMENT_END###';
            }
            $highlighted = preg_replace('/&quot;([^&]*?)&quot;/', '###STRING_START###&quot;$1&quot;###STRING_END###', $highlighted);
            $highlighted = preg_replace('/&#039;([^&]*?)&#039;/', '###STRING_START###&#039;$1&#039;###STRING_END###', $highlighted);
            $highlighted = preg_replace('/`([^`]*)`/', '###STRING_START###`$1`###STRING_END###', $highlighted); // Template literals

            $highlighted = str_replace(['###KEYWORD_START###', '###KEYWORD_END###'], ['<span class="keyword">', '</span>'], $highlighted);
            $highlighted = str_replace(['###COMMENT_START###', '###COMMENT_END###'], ['<span class="comment">', '</span>'], $highlighted);
            $highlighted = str_replace(['###STRING_START###', '###STRING_END###'], ['<span class="string">', '</span>'], $highlighted);
            $result[] = $highlighted;
        }
        return '<pre class="language-javascript"><code>' . implode("\n", $result) . '</code></pre>';
    }

    private static function highlightCss(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            // Selectors (simplified)
            $highlighted = preg_replace('/([^{]+)\{/', '<span class="selector">$1</span>{', $highlighted);
            // Properties
            $highlighted = preg_replace('/([a-zA-Z\-]+)\s*:/i', '<span class="property">$1</span>:', $highlighted);
            // Values (simplified, includes hex colors, units, etc.)
            $highlighted = preg_replace('/: ([^;]+);/', ': <span class="value">$1</span>;', $highlighted);
            // Comments
            $highlighted = preg_replace('/\/\*(.*?)\*\//s', '<span class="comment">/*$1*/</span>', $highlighted);
            $result[] = $highlighted;
        }
        return '<pre class="language-css"><code>' . implode("\n", $result) . '</code></pre>';
    }

    private static function highlightSql(string $code): string
    {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $line) {
            $highlighted = htmlspecialchars($line);
            $keywords = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'INNER', 'LEFT', 'RIGHT', 'ON', 'GROUP BY', 'ORDER BY', 'INSERT INTO', 'VALUES', 'UPDATE', 'SET', 'DELETE', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE', 'AS', 'AND', 'OR', 'NOT', 'NULL', 'COUNT', 'SUM', 'AVG', 'MIN', 'MAX', 'DISTINCT'];
            foreach ($keywords as $keyword) {
                $highlighted = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', '###KEYWORD_START###' . $keyword . '###KEYWORD_END###', $highlighted);
            }
            // Strings
            $highlighted = preg_replace('/&#039;([^&]*?)&#039;/', '###STRING_START###&#039;$1&#039;###STRING_END###', $highlighted);
            // Comments
            $highlighted = preg_replace('/-- (.*)$/m', '<span class="comment">-- $1</span>', $highlighted);

            $highlighted = str_replace(['###KEYWORD_START###', '###KEYWORD_END###'], ['<span class="keyword">', '</span>'], $highlighted);
            $highlighted = str_replace(['###STRING_START###', '###STRING_END###'], ['<span class="string">', '</span>'], $highlighted);
            $result[] = $highlighted;
        }
        return '<pre class="language-sql"><code>' . implode("\n", $result) . '</code></pre>';
    }

    // Placeholder for other language highlighters
} 