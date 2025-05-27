<?php
namespace PainBlog\Models;

class Post
{
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
} 