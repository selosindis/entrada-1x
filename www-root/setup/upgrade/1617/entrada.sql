ALTER TABLE `assessments` ADD COLUMN `active` int(1) unsigned DEFAULT '1' AFTER `grade_threshold`;

UPDATE `settings` SET `value` = '1617' WHERE `shortname` = 'version_db';