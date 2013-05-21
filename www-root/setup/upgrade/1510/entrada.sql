ALTER TABLE `evaluations` ADD COLUMN `identify_comments` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_comments`;

UPDATE `settings` SET `value` = '1510' WHERE `shortname` = 'version_db';