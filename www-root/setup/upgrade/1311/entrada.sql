CREATE TABLE IF NOT EXISTS `event_attendance` (
  `eattendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eattendance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `attached_quizzes` ADD COLUMN `require_attendance` INT NOT NULL DEFAULT '0' AFTER `required`;


UPDATE `settings` SET `value` = '1311' WHERE `shortname` = 'version_db';