<?php
namespace PainBlog\Models;

use PainBlog\Config;
use Parsedown;
use DateTime;

class Post
{
    private static ?Parsedown $parsedown = null;

    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly DateTime $date,
        public readonly string $excerpt
    ) {}

    public static function fromArray(array $data): self
    {
        $date = new DateTime($data['date']);
        $date->setTimezone(new \DateTimeZone(Config::TIMEZONE));

        return new self(
            id: (int)$data['id'],
            content: $data['content'],
            date: $date,
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

    public function getFormattedDate(): string
    {
        return $this->date->format(Config::DATE_FORMAT);
    }

    public function getYear(): int
    {
        return (int)$this->date->format('Y');
    }

    public function getMonth(): int
    {
        return (int)$this->date->format('m');
    }
} 