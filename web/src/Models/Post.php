<?php
namespace Dropblog\Models;

use Dropblog\Config;
use Parsedown;
use DateTime;

class Post
{
    private static ?Parsedown $parsedown = null;

    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly DateTime $date,
        public readonly string $type,
        public readonly ?array $metadata
    ) {}

    public static function fromArray(array $data): self
    {
        $date = new DateTime($data['date']);
        $date->setTimezone(new \DateTimeZone(Config::TIMEZONE));

        return new self(
            id: (int)$data['id'],
            content: $data['content'],
            date: $date,
            type: $data['type'] ?? 'note',
            metadata: isset($data['metadata']) ? json_decode($data['metadata'], true) : null
        );
    }

    public function getFormattedContent(): string
    {
        if (self::$parsedown === null) {
            self::$parsedown = new Parsedown();
            self::$parsedown->setSafeMode(true);
        }
        return self::$parsedown->text($this->content);
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