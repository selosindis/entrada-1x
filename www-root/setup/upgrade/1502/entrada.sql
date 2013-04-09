CREATE TABLE `assessment_quiz_questions` (
  `aqquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(11) NOT NULL,
  `qquestion_id` int(11) NOT NULL,
  PRIMARY KEY (`aqquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1502' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0.2' WHERE `shortname` = 'version_entrada';