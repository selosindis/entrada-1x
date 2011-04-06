ALTER TABLE `communities_modules` ADD COLUMN `module_visible` int(1) NOT NULL DEFAULT '1' AFTER `module_active`;
UPDATE `communities_modules` SET `module_visible` = '0' WHERE `module_shortname` = 'mtdtracking';

UPDATE `settings` SET `value` = '1201' WHERE `shortname` = 'version_db';