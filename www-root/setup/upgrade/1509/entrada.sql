ALTER TABLE `evaluations_lu_questions` ADD COLUMN `question_code` varchar(48) DEFAULT NULL AFTER `questiontype_id`;

CREATE TABLE `evaluation_question_objectives` (
  `eqobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `equestion_id` int(12) NOT NULL,
  `objective_id` int(12) NOT NULL,
  `updated_date` bigint(64) DEFAULT NULL,
  `updated_by` int(12) DEFAULT NULL,
  PRIMARY KEY (`eqobjective_id`),
  KEY `equestion_id` (`equestion_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1509' WHERE `shortname` = 'version_db';