-- Migration: Create post_types table and migrate existing post types
-- This migration creates a customizable post type system

-- Create post_types table
CREATE TABLE IF NOT EXISTS post_types (
    id INT(11) NOT NULL AUTO_INCREMENT,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon_filename VARCHAR(255) NULL,
    emoji VARCHAR(10) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_slug (slug),
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default post types (migrating from hardcoded values)
INSERT INTO post_types (slug, name, description, icon_filename, emoji, sort_order) VALUES
('note', 'Note', 'Default text post for thoughts and updates', 'icon-note.png', '📝', 1),
('link', 'Link', 'Share interesting links with context', 'icon-link.png', '🔗', 2),
('comment', 'Comment', 'Comments or responses to other content', 'icon-comment.png', '💬', 3),
('quote', 'Quote', 'Share quotes with proper attribution', 'icon-quote.png', '💭', 4),
('photo', 'Photo', 'Photo posts with descriptions', 'icon-photo.png', '📷', 5),
('code', 'Code', 'Code snippets and programming content', 'icon-code.png', '💻', 6),
('question', 'Question', 'Ask questions to your audience', 'icon-question.png', '❓', 7),
('shopping', 'Shopping', 'Product recommendations and shopping lists', 'icon-shopping.png', '🛒', 8),
('rant', 'Rant', 'Express strong opinions and frustrations', 'icon-rant.png', '😤', 9),
('poll', 'Poll', 'Create polls and surveys', 'icon-poll.png', '📊', 10),
('media', 'Media', 'Share music, videos, and media content', 'icon-media.png', '🎵', 11),
('book', 'Book', 'Book reviews and reading recommendations', 'icon-book.png', '📚', 12),
('announcement', 'Announcement', 'Important announcements and news', 'icon-announcement.png', '📢', 13),
('calendar', 'Calendar', 'Events and date-related content', 'icon-calendar.png', '📅', 14);

-- Add post_type_id column to posts table
ALTER TABLE posts 
ADD COLUMN post_type_id INT(11) NULL AFTER content,
ADD CONSTRAINT fk_posts_post_type 
    FOREIGN KEY (post_type_id) REFERENCES post_types(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Update existing posts to use the new post_type_id system
-- Map existing 'type' values to post_type_id
UPDATE posts p 
JOIN post_types pt ON p.type = pt.slug 
SET p.post_type_id = pt.id;

-- Set default post type for any posts without a type
UPDATE posts 
SET post_type_id = (SELECT id FROM post_types WHERE slug = 'note' LIMIT 1) 
WHERE post_type_id IS NULL;

-- Make post_type_id NOT NULL now that all posts have values
ALTER TABLE posts 
MODIFY COLUMN post_type_id INT(11) NOT NULL;

-- Add index for better performance
ALTER TABLE posts 
ADD INDEX idx_post_type_created (post_type_id, created_at);

-- We keep the old 'type' column temporarily for backward compatibility
-- It will be removed in a future migration after confirming everything works 