<?php
namespace PainBlog\Models;

use Parsedown;

class Post
{
    private static ?Parsedown $parsedown = null;

    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly string $date,
        public readonly string $excerpt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            content: $data['content'],
            date: $data['date'],
            excerpt: $data['excerpt'] ?? self::generateExcerpt($data['content'])
        );
    }

    private static function generateExcerpt(string $content): string
    {
        // Take first paragraph or first 200 chars as excerpt
        $firstParagraph = strtok($content, "\n\n");
        return mb_substr($firstParagraph, 0, 200);
    }

    public function getFormattedContent(): string
    {
        if (self::$parsedown === null) {
            self::$parsedown = new Parsedown();
            self::$parsedown->setSafeMode(true);
        }
        return self::$parsedown->text($this->content);
    }

    public function getFormattedExcerpt(): string
    {
        if (self::$parsedown === null) {
            self::$parsedown = new Parsedown();
            self::$parsedown->setSafeMode(true);
        }
        return self::$parsedown->text($this->excerpt);
    }
} 