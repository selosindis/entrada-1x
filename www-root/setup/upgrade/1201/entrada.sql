INSERT INTO `communities_modules` (`module_id`, `module_shortname`, `module_version`, `module_title`, `module_description`, `module_active`, `module_permissions`, `updated_date`, `updated_by`) VALUES
(8, 'mtdtracking', '1.0.0', 'MTD Tracking', 'The MTD Tracking module allows Program Assistants to enter the weekly schedule for each of their Residents.', 0, 'a:2:{s:4:"edit";i:1;s:5:"index";i:0;}', 1216256830, 5440);

ALTER TABLE `communities_modules` ADD COLUMN `module_visible` int(1) NOT NULL DEFAULT '1' AFTER `module_active`;
UPDATE `communities_modules` SET `module_visible` = '0' WHERE `module_shortname` = 'mtdtracking';

UPDATE `settings` SET `value` = '1201' WHERE `shortname` = 'version_db';