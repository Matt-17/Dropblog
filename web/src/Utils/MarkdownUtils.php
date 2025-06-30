<?php
namespace Dropblog\Utils;

use ParsedownExtended;

class MarkdownUtils
{
    public static function markdownToHtml(string $markdown): string
    {
        static $parsedown = null;

        if ($parsedown === null) {
            $parsedown = new ParsedownExtended();
            $parsedown->setSafeMode(true);
        }

        return $parsedown->text($markdown);
    }
}
