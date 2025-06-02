-- Add new post types to the CHECK constraint on the 'type' column in the posts table.
-- This script attempts to drop the existing CHECK constraint and add a new one
-- with the expanded list of allowed types.
-- Note: The constraint name might vary depending on your MySQL version or tools used.
-- If this script fails due to a constraint name error, you may need to
-- manually find the constraint name and update this script.

ALTER TABLE posts
DROP CHECK posts_chk_1; -- Replace 'posts_chk_1' with the actual constraint name if different

ALTER TABLE posts
ADD CONSTRAINT posts_chk_1 CHECK (type IN (
    'note', 'link', 'comment', 'quote', 'photo', 'code', 'question',
    'shopping', 'rant', 'poll', 'media', 'book', 'announcement', 'calendar'
)); 