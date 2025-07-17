-- Add tags column to initiative table
-- This script adds the missing tags column that is needed for the tag functionality

ALTER TABLE `initiative` 
ADD COLUMN `tags` varchar(9000) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '[]' AFTER `lastUpdated`;

-- Update existing records to have empty tags array
UPDATE `initiative` SET `tags` = '[]' WHERE `tags` IS NULL;

-- Add index for better performance
ALTER TABLE `initiative` ADD INDEX `idx_tags` (`tags`(100));

-- Verify the column was added
DESCRIBE `initiative`; 