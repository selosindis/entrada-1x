CREATE TABLE IF NOT EXISTS `course_syllabi` (
  `syllabus_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) DEFAULT NULL,
  `syllabus_start` smallint(2) DEFAULT NULL,
  `syllabus_finish` smallint(2) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `repeat` tinyint(1) DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`syllabus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1504' WHERE `shortname` = 'version_db';