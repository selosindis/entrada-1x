ALTER TABLE `assignments` ADD `max_file_uploads` INT(11)  NOT NULL DEFAULT 1 AFTER `assignment_uploads`;
ALTER TABLE `assignment_comments` DROP COLUMN `afile_id`;

UPDATE `settings` SET `value` = '1607' WHERE `shortname` = 'version_db';
