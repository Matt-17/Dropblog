<?php
namespace PainBlog\Models;

use DateTime;
use PainBlog\Config;
use PainBlog\Utils\DateUtils;

class PostGroup
{
    private array $posts;

    public function __construct(
        public readonly DateTime $date,
        array $posts = []
    ) {
        $this->posts = $posts;
    }

    public function getFormattedDate(): string
    {
        return DateUtils::formatDate($this->date);
    }

    public function addPost(Post $post): void
    {
        $this->posts[] = $post;
    }

    public function getPosts(): array
    {
        return $this->posts;
    }
} 