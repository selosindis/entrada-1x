ALTER TABLE `event_quizzes` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quizzes` CHANGE `equiz_id` `aquiz_id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quizzes` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
RENAME TABLE `event_quizzes` TO `attached_quizzes`;

ALTER TABLE `event_quiz_progress` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_progress` CHANGE `equiz_id` `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_progress` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_progress` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
RENAME TABLE `event_quiz_progress` TO `quiz_progress`;

ALTER TABLE `event_quiz_responses` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_responses` CHANGE `equiz_id` `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_responses` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_responses` CHANGE `eqresponse_id` `qpresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quiz_responses` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL;
RENAME TABLE `event_quiz_responses` TO `quiz_progress_responses`;

ALTER TABLE `quiz_questions` ADD COLUMN `question_active` int(1) NOT NULL DEFAULT '1' AFTER `question_order`;
ALTER TABLE `quiz_question_responses` ADD COLUMN `response_active` int(1) NOT NULL DEFAULT '1' AFTER `response_feedback`;

ALTER TABLE `student_awards_external` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_community_health_and_epidemiology` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_contributions` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_critical_enquiries` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_research` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_observerships` drop column `start`;
ALTER TABLE `student_observerships` drop column `end`;
ALTER TABLE `student_observerships` add column (
  `start` int(11) NOT NULL,
  `end` int(11) default NULL,
  `preceptor_firstname` varchar(256) default NULL,
  `preceptor_lastname` varchar(256) default NULL,
  `preceptor_proxy_id` int(12) unsigned default NULL);
  
ALTER TABLE `student_mspr` ADD COLUMN  `carms_number` int(10) unsigned default NULL;

INSERT INTO `communities_modules` (`module_id`,`module_shortname`,`module_version`,`module_title`,`module_description`,`module_active`,`module_permissions`,`updated_date`,`updated_by`)
VALUES (7, 'quizzes', '1.0.0', 'Quizzes', 'This module allows communities to create their own quizzes for summative or formative evaluation.', 1, 'a:1:{s:5:\"index\";i:0;}', 1216256830, 3499);
	
INSERT INTO `community_modules` (`community_id`, `module_id`, `module_active`)
SELECT `community_id`, 7, 1 FROM `communities` WHERE `community_active` = 1;

CREATE TABLE IF NOT EXISTS `evaluations` (
  `evaluation_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(12) NOT NULL,
  `evaluation_title` varchar(128) NOT NULL,
  `evaluation_description` text NOT NULL,
  `evaluation_active` tinyint(1) NOT NULL,
  `evaluation_start` bigint(64) NOT NULL,
  `evaluation_finish` bigint(64) NOT NULL,
  `min_submittable` tinyint(1) NOT NULL DEFAULT '1',
  `max_submittable` tinyint(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` bigint(64) NOT NULL,
  PRIMARY KEY (`evaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluations_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_shortname` varchar(32) NOT NULL,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`questiontype_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_id`, `questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`) VALUES
(1, 'matrix_single', 'Choice Matrix (single response)', 'The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).', 1),
(2, 'descriptive_text', 'Descriptive Text', 'Allows you to add descriptive text information to your evaluation form. This could be instructions or other details relevant to the question or series of questions.', 1);

CREATE TABLE IF NOT EXISTS `evaluations_lu_targets` (
  `target_id` int(11) NOT NULL AUTO_INCREMENT,
  `target_shortname` varchar(32) NOT NULL,
  `target_title` varchar(64) NOT NULL,
  `target_description` text NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`target_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `evaluations_lu_targets` (`target_id`, `target_shortname`, `target_title`, `target_description`, `target_active`) VALUES
(1, 'course', 'Course Evaluation', '', 1),
(2, 'teacher', 'Teacher Evaluation', '', 1),
(3, 'student', 'Student Assessment', '', 0),
(4, 'rotation_core', 'Clerkship Core Rotation Evaluation', '', 0),
(5, 'rotation_elective', 'Clerkship Elective Rotation Evaluation', '', 0),
(6, 'preceptor', 'Clerkship Preceptor Evaluation', '', 0),
(7, 'peer', 'Peer Assessment', '', 0),
(8, 'self', 'Self Assessment', '', 0);

CREATE TABLE IF NOT EXISTS `evaluation_evaluators` (
  `eevaluator_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `evaluator_type` enum('proxy_id','grad_year','organisation_id') NOT NULL,
  `evaluator_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eevaluator_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_forms` (
  `eform_id` int(12) NOT NULL AUTO_INCREMENT,
  `target_id` int(12) NOT NULL,
  `form_parent` int(12) NOT NULL,
  `form_title` varchar(64) NOT NULL,
  `form_description` text NOT NULL,
  `form_active` tinyint(1) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_questions` (
  `efquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(121) NOT NULL,
  `questiontype_id` int(12) NOT NULL,
  `question_text` longtext NOT NULL,
  `question_order` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_form_responses` (
  `efresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` tinyint(3) NOT NULL DEFAULT '0',
  `response_is_html` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_passing_level` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress` (
  `eprogress_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `etarget_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `progress_value` enum('inprogress','complete','cancelled') NOT NULL DEFAULT 'inprogress',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eprogress_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_responses` (
  `eresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(12) NOT NULL,
  `eform_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `efquestion_id` int(12) NOT NULL,
  `efresponse_id` int(12) NOT NULL,
  `comments` text NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_targets` (
  `etarget_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_value` int(12) NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etarget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `assessments` CHANGE `grad_year` `grad_year` varchar(35) NOT NULL DEFAULT '';

ALTER TABLE `community_pages` CHANGE `page_url` `page_url` varchar(329);
ALTER TABLE `community_pages` ADD INDEX (`cpage_id`, `community_id`, `page_url`, `page_active`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `parent_id`, `page_url`, `page_active`);
ALTER TABLE `community_pages` ADD INDEX (`page_order`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `page_url`);
ALTER TABLE `community_pages` ADD INDEX (`community_id`, `page_type`);
ALTER TABLE `community_page_options` ADD INDEX (`cpage_id`);
ALTER TABLE `community_members` ADD INDEX (`community_id`, `proxy_id`, `member_active`);

CREATE TABLE IF NOT EXISTS `events_lu_topics` (
  `topic_id` int(12) NOT NULL AUTO_INCREMENT,
  `ed10_id` int(12) NULL,
  `ed11_id` int(12) NULL,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `topic_type` enum('ed10','ed11','other') NOT NULL DEFAULT 'other',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `events_lu_topics` (`ed10_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`)
SELECT `ed10_id`, `topic_name`, `topic_description`, 'ed10', `updated_date`, `updated_by` FROM `events_lu_ed10` ORDER BY `ed10_id` ASC;

INSERT INTO `events_lu_topics` (`ed11_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`)
SELECT `ed11_id`, `topic_name`, `topic_description`, 'ed11', `updated_date`, `updated_by` FROM `events_lu_ed11` ORDER BY `ed11_id` ASC;

CREATE TABLE IF NOT EXISTS `event_topics` (
  `etopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `topic_id` tinyint(1) DEFAULT '0',
  `topic_coverage`  enum('major','minor') NOT NULL,
  `topic_time` varchar(25) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`etopic_id`),
  KEY `event_id` (`event_id`),
  KEY `topic_id` (`topic_id`),
  KEY `topic_coverage` (`topic_coverage`),
  KEY `topic_time` (`topic_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `event_topics` (`event_id`, `topic_id`, `topic_coverage`, `topic_time`, `updated_date`, `updated_by`)
SELECT a.`event_id`, b.`topic_id`, IF(a.`major_topic`, 'major', 'minor') AS `topic_coverage`, a.`minor_desc` AS `topic_time`, a.`updated_date`, a.`updated_by`
FROM `event_ed10` AS a
LEFT JOIN `events_lu_topics` AS b
ON b.`ed10_id` = a.`ed10_id`;

INSERT INTO `event_topics` (`event_id`, `topic_id`, `topic_coverage`, `topic_time`, `updated_date`, `updated_by`)
SELECT a.`event_id`, b.`topic_id`, IF(a.`major_topic`, 'major', 'minor') AS `topic_coverage`, a.`minor_desc` AS `topic_time`, a.`updated_date`, a.`updated_by`
FROM `event_ed11` AS a
LEFT JOIN `events_lu_topics` AS b
ON b.`ed11_id` = a.`ed11_id`;

ALTER TABLE `events_lu_topics` DROP `ed10_id`;
ALTER TABLE `events_lu_topics` DROP `ed11_id`;

RENAME TABLE `events_lu_ed10` TO `backup_events_lu_ed10`;
RENAME TABLE `events_lu_ed11` TO `backup_events_lu_ed11`;
RENAME TABLE `event_ed10` TO `backup_event_ed10`;
RENAME TABLE `event_ed11` TO `backup_event_ed11`;

UPDATE `settings` SET `value` = '1.2.0' WHERE `shortname` = 'version_db';

INSERT INTO `ar_lu_on_call_locations` (`id`,`on_call_location`)
VALUES	('', 'Other (specify)');