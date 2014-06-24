ALTER TABLE `assignments` ADD `max_file_uploads` INT(11) NOT NULL DEFAULT '1' AFTER `assignment_uploads`;
ALTER TABLE `assignment_comments` CHANGE `afile_id` `proxy_to_id` INT(12) NOT NULL DEFAULT '0';

UPDATE `settings` SET `value` = '1627' WHERE `shortname` = 'version_db';
