<?php
namespace Dropblog\Models;

use Dropblog\Config;
use Dropblog\Utils\ParsedownExtended;
use DateTime;

class Post
{
    private static ?ParsedownExtended $parsedown = null;

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
        $date->setTimezone(new \DateTimeZone(Config::timezone()));

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
            self::$parsedown = new ParsedownExtended();
            self::$parsedown->setSafeMode(true);
        }
        return self::$parsedown->text($this->content);
    }

    public function getFormattedDate(): string
    {
        return $this->date->format(Config::dateFormat());
    }

    public function getYear(): int
    {
        return (int)$this->date->format('Y');
    }

    public function getMonth(): int
    {
        return (int)$this->date->format('m');
    }

    public function getHighlightedContent(?array $keywords = null): string
    {
        $content = $this->getFormattedContent();
        if (!$keywords || !is_array($keywords) || count($keywords) === 0) {
            return $content;
        }
        foreach ($keywords as $word) {
            if (trim($word) !== '') {
                $content = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $content);
            }
        }
        return $content;
    }
} 