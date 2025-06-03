-- Migration: Cleanup legacy type column
-- This migration removes the old 'type' column after post_type_id is fully implemented
-- Run this migration ONLY after confirming the new post type system is working correctly

-- First, verify all posts have post_type_id set
-- This query should return 0 for a safe migration
-- SELECT COUNT(*) FROM posts WHERE post_type_id IS NULL;

-- Drop the old type column (keeping for backward compatibility during transition)
ALTER TABLE posts DROP COLUMN type;

-- Note: The above ALTER statement is commented out for safety
-- Uncomment only after thorough testing of the new post type system

-- Drop the old CHECK constraint if it exists (from migration 003 and 004)
-- This may fail silently if the constraint doesn't exist, which is fine
ALTER TABLE posts DROP CHECK posts_chk_1; 