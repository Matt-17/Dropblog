<?php
namespace PainBlog\Utils;

use Parsedown;

class MarkdownUtils
{
    public static function markdownToHtml(string $markdown): string
    {
        static $parsedown = null;

        if ($parsedown === null) {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode(true);
        }

        return $parsedown->text($markdown);
    }
}
