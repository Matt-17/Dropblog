<?php
namespace PainBlog\Models;

use DateTime;

class PostGroup
{
    public function __construct(
        public readonly DateTime $date,
        public readonly array $posts
    ) {}

    public function getFormattedDate(): string
    {
        return $this->date->format(Config::DATE_FORMAT);
    }
} 