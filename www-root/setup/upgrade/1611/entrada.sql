ALTER TABLE `assessments` ADD `active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `grade_threshold`;
 
UPDATE `settings` SET `value` = '1611' WHERE `shortname` = 'version_db';
