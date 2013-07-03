INSERT INTO `quizzes_lu_questiontypes` (`questiontype_id`, `questiontype_title`, `questiontype_description`, `questiontype_active`, `questiontype_order`) VALUES
(2, 'Descriptive Text', '', 1, 0),
(3, 'Page Break', '', 1, 0);

ALTER TABLE `ar_peer_reviewed_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_non_peer_reviewed_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_book_chapter_mono` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_poster_reports` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_conference_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;

UPDATE `notice_audience` SET `audience_type` = 'cohort' WHERE `audience_type` = 'cohorts';
UPDATE `notice_audience` SET `audience_type` = 'student' WHERE `audience_type` = 'students';
UPDATE `notice_audience` SET `audience_type` = 'all:student' WHERE `audience_type` = 'all:students';
UPDATE `notice_audience` SET `audience_type` = 'all' WHERE `audience_type` = 'all:users';

DELETE FROM `notice_audience` WHERE `notice_id` NOT IN (SELECT `notice_id` FROM `notices`);

CREATE TABLE IF NOT EXISTS `assessment_quiz_questions` (
  `aqquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(11) NOT NULL,
  `qquestion_id` int(11) NOT NULL,
  PRIMARY KEY (`aqquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `profile_custom_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL DEFAULT '',
  `type` enum('TEXTAREA','TEXTINPUT','CHECKBOX','RICHTEXT') NOT NULL DEFAULT 'TEXTAREA',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `length` smallint(3) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `profile_custom_responses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `evaluations` ADD COLUMN `evaluation_completions` int(12) NOT NULL DEFAULT '0' AFTER `evaluation_finish`;

UPDATE `evaluations` AS a SET a.`evaluation_completions` = (SELECT COUNT(b.`evaluation_id`) FROM `evaluation_progress` AS b
WHERE b.`progress_value` = 'complete'
AND b.`evaluation_id` = a.`evaluation_id`
GROUP BY b.`evaluation_id`);

CREATE TABLE `assessment_attached_quizzes` (
  `aaquiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `aquiz_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aaquiz_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `quiz_id` (`aquiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessment_attached_quizzes` (`assessment_id`, `aquiz_id`, `updated_date`, `updated_by`)
(
    SELECT a.`content_id`, b.`aquiz_id`, a.`updated_date`, a.`updated_by` FROM `attached_quizzes` AS a
    JOIN `attached_quizzes` AS b
    ON a.`quiz_id` = b.`quiz_id`
    AND b.`content_type` != 'assessment'
    WHERE a.`content_type` = 'assessment'
);

INSERT INTO `quizzes_lu_quiztypes` (`quiztype_code`, `quiztype_title`, `quiztype_description`, `quiztype_active`, `quiztype_order`) VALUES
('hide', 'Hide Quiz Results', 'This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback), and requires either manual release of the results to the students, or use of a Gradebook Assessment to release the resulting score.', 1, 2);

INSERT INTO `objective_audience` (`objective_id`, `organisation_id`, `audience_type`, `audience_value`, `updated_date`, `updated_by`)
(
	SELECT a.`objective_id`, b.`organisation_id`, 'COURSE', 'all', 0, 1 FROM `global_lu_objectives` AS a
	JOIN `objective_organisation` AS b
	ON a.`objective_id` = b.`objective_id`
	WHERE a.`objective_parent` = 0
);

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

ALTER TABLE `evaluations` ADD COLUMN `identify_comments` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_comments`;

ALTER TABLE `medbiq_instructional_methods` ADD `code` VARCHAR(10)  NULL  DEFAULT NULL  AFTER `instructional_method_id`;

UPDATE `medbiq_instructional_methods` SET `code` = "IM001" WHERE `instructional_method_id` = '1';
UPDATE `medbiq_instructional_methods` SET `code` = "IM002" WHERE `instructional_method_id` = '2';
UPDATE `medbiq_instructional_methods` SET `code` = "IM003" WHERE `instructional_method_id` = '3';
UPDATE `medbiq_instructional_methods` SET `code` = "IM004" WHERE `instructional_method_id` = '4';
UPDATE `medbiq_instructional_methods` SET `code` = "IM005" WHERE `instructional_method_id` = '5';
UPDATE `medbiq_instructional_methods` SET `code` = "IM006" WHERE `instructional_method_id` = '6';
UPDATE `medbiq_instructional_methods` SET `code` = "IM007" WHERE `instructional_method_id` = '7';
UPDATE `medbiq_instructional_methods` SET `code` = "IM008" WHERE `instructional_method_id` = '8';
UPDATE `medbiq_instructional_methods` SET `code` = "IM009" WHERE `instructional_method_id` = '9';
UPDATE `medbiq_instructional_methods` SET `code` = "IM010" WHERE `instructional_method_id` = '10';
UPDATE `medbiq_instructional_methods` SET `code` = "IM011" WHERE `instructional_method_id` = '11';
UPDATE `medbiq_instructional_methods` SET `code` = "IM012" WHERE `instructional_method_id` = '12';
UPDATE `medbiq_instructional_methods` SET `code` = "IM013" WHERE `instructional_method_id` = '13';
UPDATE `medbiq_instructional_methods` SET `code` = "IM014" WHERE `instructional_method_id` = '14';
UPDATE `medbiq_instructional_methods` SET `code` = "IM015" WHERE `instructional_method_id` = '15';
UPDATE `medbiq_instructional_methods` SET `code` = "IM016" WHERE `instructional_method_id` = '16';
UPDATE `medbiq_instructional_methods` SET `code` = "IM017" WHERE `instructional_method_id` = '17';
UPDATE `medbiq_instructional_methods` SET `code` = "IM018" WHERE `instructional_method_id` = '18';
UPDATE `medbiq_instructional_methods` SET `code` = "IM019" WHERE `instructional_method_id` = '19';
UPDATE `medbiq_instructional_methods` SET `code` = "IM020" WHERE `instructional_method_id` = '20';
UPDATE `medbiq_instructional_methods` SET `code` = "IM021" WHERE `instructional_method_id` = '21';
UPDATE `medbiq_instructional_methods` SET `code` = "IM022" WHERE `instructional_method_id` = '22';
UPDATE `medbiq_instructional_methods` SET `code` = "IM023" WHERE `instructional_method_id` = '23';
UPDATE `medbiq_instructional_methods` SET `code` = "IM024" WHERE `instructional_method_id` = '24';
UPDATE `medbiq_instructional_methods` SET `code` = "IM025" WHERE `instructional_method_id` = '25';
UPDATE `medbiq_instructional_methods` SET `code` = "IM026" WHERE `instructional_method_id` = '26';
UPDATE `medbiq_instructional_methods` SET `code` = "IM027" WHERE `instructional_method_id` = '27';
UPDATE `medbiq_instructional_methods` SET `code` = "IM028" WHERE `instructional_method_id` = '28';
UPDATE `medbiq_instructional_methods` SET `code` = "IM029" WHERE `instructional_method_id` = '29';
UPDATE `medbiq_instructional_methods` SET `code` = "IM030" WHERE `instructional_method_id` = '30';

ALTER TABLE `medbiq_assessment_methods` ADD `code` VARCHAR(10)  NULL  DEFAULT NULL  AFTER `assessment_method_id`;

UPDATE `medbiq_assessment_methods` SET `code` = "AM001" WHERE `assessment_method_id` = '1';
UPDATE `medbiq_assessment_methods` SET `code` = "AM002" WHERE `assessment_method_id` = '2';
UPDATE `medbiq_assessment_methods` SET `code` = "AM003" WHERE `assessment_method_id` = '3';
UPDATE `medbiq_assessment_methods` SET `code` = "AM004" WHERE `assessment_method_id` = '4';
UPDATE `medbiq_assessment_methods` SET `code` = "AM005" WHERE `assessment_method_id` = '5';
UPDATE `medbiq_assessment_methods` SET `code` = "AM006" WHERE `assessment_method_id` = '6';
UPDATE `medbiq_assessment_methods` SET `code` = "AM007" WHERE `assessment_method_id` = '7';
UPDATE `medbiq_assessment_methods` SET `code` = "AM008" WHERE `assessment_method_id` = '8';
UPDATE `medbiq_assessment_methods` SET `code` = "AM009" WHERE `assessment_method_id` = '9';
UPDATE `medbiq_assessment_methods` SET `code` = "AM010" WHERE `assessment_method_id` = '10';
UPDATE `medbiq_assessment_methods` SET `code` = "AM011" WHERE `assessment_method_id` = '11';
UPDATE `medbiq_assessment_methods` SET `code` = "AM012" WHERE `assessment_method_id` = '12';
UPDATE `medbiq_assessment_methods` SET `code` = "AM013" WHERE `assessment_method_id` = '13';
UPDATE `medbiq_assessment_methods` SET `code` = "AM014" WHERE `assessment_method_id` = '14';
UPDATE `medbiq_assessment_methods` SET `code` = "AM015" WHERE `assessment_method_id` = '15';
UPDATE `medbiq_assessment_methods` SET `code` = "AM016" WHERE `assessment_method_id` = '16';
UPDATE `medbiq_assessment_methods` SET `code` = "AM017" WHERE `assessment_method_id` = '17';
UPDATE `medbiq_assessment_methods` SET `code` = "AM018" WHERE `assessment_method_id` = '18';

CREATE TABLE IF NOT EXISTS `draft_options` (
  `draft_id` int(11) NOT NULL,
  `option` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1500' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0' WHERE `shortname` = 'version_entrada';