<?php

namespace Dropblog\Utils;

class SyntaxHighlighter
{
    /**
     * Highlight code based on language
     */
    public static function highlight(string $code, string $language): string
    {
        switch (strtolower($language)) {
            case 'php':
                return self::highlightPhp($code);
            case 'javascript':
            case 'js':
                return self::highlightJavaScript($code);
            case 'css':
                return self::highlightCss($code);
            case 'html':
                return self::highlightHtml($code);
            case 'sql':
                return self::highlightSql($code);
            case 'csharp':
            case 'cs':
            case 'c#':
                return self::highlightCSharp($code);
            default:
                return self::highlightGeneric($code);
        }
    }

    /**
     * Highlight PHP using built-in highlight_string()
     */
    private static function highlightPhp(string $code): string
    {
        // Add opening tag if missing
        if (!str_starts_with(trim($code), '<?php')) {
            $code = "<?php\n" . $code;
            $removeTag = true;
        }

        $highlighted = highlight_string($code, true);
        
        // Remove the opening tag if we added it
        if (isset($removeTag)) {
            $highlighted = preg_replace('/<\?php<br\s*\/?>/', '', $highlighted, 1);
        }

        // Clean up and style
        $highlighted = str_replace(['<code>', '</code>'], '', $highlighted);
        
        return '<pre class="language-php"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Simple JavaScript highlighter
     */
    private static function highlightJavaScript(string $code): string
    {
        $keywords = ['function', 'var', 'let', 'const', 'if', 'else', 'for', 'while', 'return', 'class', 'extends', 'import', 'export'];
        $highlighted = htmlspecialchars($code);
        
        // Highlight keywords
        foreach ($keywords as $keyword) {
            $highlighted = preg_replace('/\b' . $keyword . '\b/', '<span class="keyword">' . $keyword . '</span>', $highlighted);
        }
        
        // Highlight strings
        $highlighted = preg_replace('/"([^"]*)"/', '<span class="string">"$1"</span>', $highlighted);
        $highlighted = preg_replace("/'([^']*)'/", '<span class="string">\'$1\'</span>', $highlighted);
        
        // Highlight comments
        $highlighted = preg_replace('/\/\/(.*)$/', '<span class="comment">//$1</span>', $highlighted, -1, PREG_OFFSET_CAPTURE);
        
        return '<pre class="language-javascript"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Simple CSS highlighter
     */
    private static function highlightCss(string $code): string
    {
        $highlighted = htmlspecialchars($code);
        
        // Highlight selectors
        $highlighted = preg_replace('/([.#]?[a-zA-Z][a-zA-Z0-9_-]*)\s*{/', '<span class="selector">$1</span> {', $highlighted);
        
        // Highlight properties
        $highlighted = preg_replace('/([a-zA-Z-]+)\s*:/', '<span class="property">$1</span>:', $highlighted);
        
        // Highlight values
        $highlighted = preg_replace('/:\s*([^;]+);/', ': <span class="value">$1</span>;', $highlighted);
        
        return '<pre class="language-css"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Simple HTML highlighter
     */
    private static function highlightHtml(string $code): string
    {
        $highlighted = htmlspecialchars($code);
        
        // Highlight tags
        $highlighted = preg_replace('/&lt;(\/?[a-zA-Z][a-zA-Z0-9]*)(.*?)&gt;/', '<span class="tag">&lt;$1<span class="attr">$2</span>&gt;</span>', $highlighted);
        
        return '<pre class="language-html"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Simple SQL highlighter
     */
    private static function highlightSql(string $code): string
    {
        $keywords = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'INNER', 'LEFT', 'RIGHT', 'ON', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'TABLE', 'ALTER', 'DROP'];
        $highlighted = htmlspecialchars($code);
        
        foreach ($keywords as $keyword) {
            $highlighted = preg_replace('/\b' . $keyword . '\b/i', '<span class="keyword">' . $keyword . '</span>', $highlighted);
        }
        
        return '<pre class="language-sql"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Simple C# highlighter
     */
    private static function highlightCSharp(string $code): string
    {
        $keywords = [
            // Access modifiers
            'public', 'private', 'protected', 'internal',
            // Type keywords
            'class', 'struct', 'interface', 'enum', 'namespace', 'using',
            // Control flow
            'if', 'else', 'for', 'foreach', 'while', 'do', 'switch', 'case', 'break', 'continue', 'return',
            // Data types
            'int', 'string', 'bool', 'double', 'float', 'decimal', 'var', 'object', 'void',
            // Other keywords
            'new', 'this', 'base', 'static', 'abstract', 'virtual', 'override', 'async', 'await',
            'try', 'catch', 'finally', 'throw', 'in', 'out', 'ref', 'params', 'const', 'readonly',
            'get', 'set', 'value', 'yield', 'where', 'select', 'from', 'orderby', 'group', 'join'
        ];
        
        $highlighted = htmlspecialchars($code);
        
        // Highlight keywords
        foreach ($keywords as $keyword) {
            $highlighted = preg_replace('/\b' . $keyword . '\b/', '<span class="keyword">' . $keyword . '</span>', $highlighted);
        }
        
        // Highlight strings (regular strings)
        $highlighted = preg_replace('/"([^"\\\\]*(\\\\.[^"\\\\]*)*)"/', '<span class="string">"$1"</span>', $highlighted);
        $highlighted = preg_replace("/'([^'\\\\]*(\\\\.[^'\\\\]*)*)'/",'<span class="string">\'$1\'</span>', $highlighted);
        
        // Highlight verbatim strings (@"...")
        $highlighted = preg_replace('/@"([^"]*(""|[^"])*)"/', '<span class="string">@"$1"</span>', $highlighted);
        
        // Highlight single-line comments
        $highlighted = preg_replace('/\/\/(.*)$/m', '<span class="comment">//$1</span>', $highlighted);
        
        // Highlight multi-line comments
        $highlighted = preg_replace('/\/\*(.*?)\*\//s', '<span class="comment">/*$1*/</span>', $highlighted);
        
        // Highlight attributes
        $highlighted = preg_replace('/\[([^\]]+)\]/', '<span class="attr">[$1]</span>', $highlighted);
        
        // Highlight generic types
        $highlighted = preg_replace('/&lt;([^&]+)&gt;/', '<span class="generic">&lt;$1&gt;</span>', $highlighted);
        
        return '<pre class="language-csharp"><code>' . $highlighted . '</code></pre>';
    }

    /**
     * Generic highlighter for unknown languages
     */
    private static function highlightGeneric(string $code): string
    {
        return '<pre class="language-text"><code>' . htmlspecialchars($code) . '</code></pre>';
    }
} 