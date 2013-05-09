CREATE TABLE `assessment_attached_quizzes` (
  `aaquiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `aquiz_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aaquiz_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `quiz_id` (`aquiz_id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessment_attached_quizzes` (`assessment_id`, `aquiz_id`, `updated_date`, `updated_by`)
(
    SELECT a.`content_id`, b.`aquiz_id`, a.`updated_date`, a.`updated_by` FROM `attached_quizzes` AS a
    JOIN `attached_quizzes` AS b
    ON a.`quiz_id` = b.`quiz_id`
    AND b.`content_type` != 'assessment'
    WHERE a.`content_type` = 'assessment'
)

UPDATE `settings` SET `value` = '1506' WHERE `shortname` = 'version_db';