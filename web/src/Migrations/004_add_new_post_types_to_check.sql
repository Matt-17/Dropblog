-- Add new post types to the CHECK constraint on the 'type' column in the posts table.
-- This script attempts to modify the column definition to include the updated CHECK constraint.
-- This approach might be more compatible if dropping the constraint by name is problematic.

ALTER TABLE posts
MODIFY COLUMN type VARCHAR(20) NOT NULL DEFAULT 'note' CHECK (type IN (
    'note', 'link', 'comment', 'quote', 'photo', 'code', 'question',
    'shopping', 'rant', 'poll', 'media', 'book', 'announcement', 'calendar'
)); 