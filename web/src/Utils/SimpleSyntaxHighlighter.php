<?php

namespace Dropblog\Utils;

class SimpleSyntaxHighlighter
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
} 