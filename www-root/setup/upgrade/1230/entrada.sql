ALTER TABLE `student_observerships` ADD `preceptor_prefix` varchar(4) default NULL AFTER `end`;
UPDATE `settings` SET `value` = '1230' WHERE `shortname` = 'version_db';