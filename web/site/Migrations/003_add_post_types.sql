-- Add post type column with 'note' as default
ALTER TABLE posts 
ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'note' 
CHECK (type IN ('note', 'link', 'comment', 'quote', 'photo', 'code', 'question'));

-- Add metadata column for type-specific data (like URLs for links, sources for quotes, etc.)
ALTER TABLE posts
ADD COLUMN metadata JSON NULL; 