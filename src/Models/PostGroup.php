<?php
namespace PainBlog\Models;

use DateTime;

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
        return $this->date->format(Config::DATE_FORMAT);
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