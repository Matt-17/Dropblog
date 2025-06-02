<?php

namespace Dropblog\Utils;

use Parsedown;

class ParsedownExtended extends Parsedown
{
    protected function blockFencedCode($line)
    {
        $block = parent::blockFencedCode($line);
        
        if (isset($block)) {
            return $block;
        }

        if (preg_match('/^[ ]*```[ ]*([a-zA-Z0-9_+-]*)[ ]*$/', $line['text'], $matches)) {
            $language = !empty($matches[1]) ? $matches[1] : 'text';
            
            return [
                'char' => $line['text'][0],
                'openerLength' => 3,
                'language' => $language,
                'element' => [
                    'name' => 'pre',
                    'attributes' => [
                        'class' => "language-{$language}"
                    ],
                    'handler' => [
                        'function' => 'element',
                        'argument' => [
                            'name' => 'code',
                            'text' => '',
                        ],
                        'destination' => 'text'
                    ]
                ],
            ];
        }
    }

    protected function blockFencedCodeComplete($block)
    {
        // Get language from the block's element attributes
        $language = $block['element']['attributes']['class'] ?? 'text';
        $language = str_replace('language-', '', $language);
        
        // Get code from the block's text
        $code = $block['element']['handler']['argument']['text'];
        
        // Use our PHP syntax highlighter
        $highlighted = SyntaxHighlighter::highlight($code, $language);
        
        return [
            'element' => [
                'rawHtml' => $highlighted,
                'allowRawHtmlInSafeMode' => true
            ]
        ];
    }
} 