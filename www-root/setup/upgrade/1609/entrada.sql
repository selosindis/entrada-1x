ALTER TABLE `course_objectives` ADD `objective_start` INT(12) DEFAULT NULL AFTER `objective_details`;
ALTER TABLE `course_objectives` ADD `objective_finish` INT(12) DEFAULT NULL AFTER `objective_start`;
ALTER TABLE `course_objectives` ADD `active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `updated_by`;
 
UPDATE `settings` SET `value` = '1609' WHERE `shortname` = 'version_db';