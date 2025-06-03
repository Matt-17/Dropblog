-- Migration: Cleanup legacy type column
-- This migration removes the old 'type' column after post_type_id is fully implemented
-- Run this migration ONLY after confirming the new post type system is working correctly

-- First, verify all posts have post_type_id set
-- This query should return 0 for a safe migration
-- SELECT COUNT(*) FROM posts WHERE post_type_id IS NULL;

-- Drop the old type column
ALTER TABLE posts DROP COLUMN type;

-- Note: CHECK constraints in MariaDB/MySQL are often automatically named
-- and may not exist if they were never created properly.
-- We'll skip dropping them since they don't affect functionality
-- and the type column removal accomplishes our goal. 