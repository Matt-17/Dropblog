<?php

namespace Dropblog\Utils;

use Parsedown;

class ParsedownExtended extends Parsedown
{
    public function text($text)
    {
        // Get the normal Parsedown output
        $html = parent::text($text);
        
        // Post-process to add syntax highlighting
        return $this->addSyntaxHighlighting($html);
    }
    
    private function addSyntaxHighlighting($html)
    {
        // Pattern to match code blocks with language classes
        $pattern = '/<pre><code class="language-([^"]+)">(.*?)<\/code><\/pre>/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $language = $matches[1];
            $code = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
            
            // Use our syntax highlighter
            return SyntaxHighlighter::highlight($code, $language);
        }, $html);
    }
} 