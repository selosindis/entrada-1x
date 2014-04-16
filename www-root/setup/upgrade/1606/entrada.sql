ALTER TABLE `events` ADD `eventtype_id` int(12) DEFAULT '0' AFTER `recurring_id`;
ALTER TABLE `events` ADD `course_num` varchar(32) DEFAULT NULL AFTER `course_id`;
ALTER TABLE `events` ADD `draft_id` int(11) DEFAULT NULL AFTER `release_until`;

UPDATE `settings` SET `value` = '1606' WHERE `shortname` = 'version_db';