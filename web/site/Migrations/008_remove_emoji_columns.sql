-- Migration: Remove emoji column from post_types table
-- Clean up emoji-related fields for image-only post type system

-- Check if emoji column exists and drop it
SET @col_exists = (SELECT COUNT(*) 
                   FROM information_schema.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'post_types' 
                   AND COLUMN_NAME = 'emoji');

SET @sql = IF(@col_exists > 0, 
              'ALTER TABLE post_types DROP COLUMN emoji', 
              'SELECT "Column emoji already removed" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Make icon_filename NOT NULL if it isn't already
ALTER TABLE post_types MODIFY COLUMN icon_filename VARCHAR(255) NOT NULL; 