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

CREATE TABLE IF NOT EXISTS `events_lu_topics` (
  `topic_id` int(12) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `topic_type` enum('general','ed10','ed11') NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `events_lu_topics` (`topic_id`, `topic_name`, `topic_description`, `topic_type`, `updated_date`, `updated_by`) VALUES
(1, 'Biostatistics', 'Biostatistics', 'ed10', 1215615910, 1),
(2, 'Communication Skills', 'Communication Skills', 'ed10', 1215615910, 1),
(3, 'Community Health', 'Community Health', 'ed10', 1215615910, 1),
(4, 'End-of-Life Care', 'End-of-Life Care', 'ed10', 1215615910, 1),
(5, 'Epidemiology', 'Epidemiology', 'ed10', 1215615910, 1),
(6, 'Evidence-Based Medicine', 'Evidence-Based Medicine', 'ed10', 1215615910, 1),
(7, 'Family Violence/Abuse', 'Family Violence/Abuse', 'ed10', 1215615910, 1),
(8, 'Medical Genetics', 'Medical Genetics', 'ed10', 1215615910, 1),
(9, 'Health Care Financing', 'Health Care Financing', 'ed10', 1215615910, 1),
(10, 'Health Care Systems', 'Health Care Systems', 'ed10', 1215615910, 1),
(11, 'Health Care Quality Review', 'Health Care Quality Review', 'ed10', 1215615910, 1),
(12, 'Home Health Care', 'Home Health Care', 'ed10', 1215615910, 1),
(13, 'Human Development/Life Cycle', 'Human Development/Life Cycle', 'ed10', 1215615910, 1),
(14, 'Human Sexuality', 'Human Sexuality', 'ed10', 1215615910, 1),
(15, 'Medical Ethics', 'Medical Ethics', 'ed10', 1215615910, 1),
(16, 'Medical Humanities', 'Medical Humanities', 'ed10', 1215615910, 1),
(17, 'Medical Informatics', 'Medical Informatics', 'ed10', 1215615910, 1),
(18, 'Medical Jurisprudence', 'Medical Jurisprudence', 'ed10', 1215615910, 1),
(19, 'Multicultural Medicine', 'Multicultural Medicine', 'ed10', 1215615910, 1),
(20, 'Nutrition', 'Nutrition', 'ed10', 1215615910, 1),
(21, 'Occupational Health/Medicine', 'Occupational Health/Medicine', 'ed10', 1215615910, 1),
(22, 'Pain Management', 'Pain Management', 'ed10', 1215615910, 1),
(23, 'Palliative Care', 'Palliative Care', 'ed10', 1215615910, 1),
(24, 'Patient Health Education', 'Patient Health Education', 'ed10', 1215615910, 1),
(25, 'Population-Based Medicine', 'Population-Based Medicine', 'ed10', 1215615910, 1),
(26, 'Practice Management', 'Practice Management', 'ed10', 1215615910, 1),
(27, 'Preventive Medicine', 'Preventive Medicine', 'ed10', 1215615910, 1),
(28, 'Rehabilitation/Care of the Disabled', 'Rehabilitation/Care of the Disabled', 'ed10', 1215615910, 1),
(29, 'Research Methods', 'Research Methods', 'ed10', 1215615910, 1),
(30, 'Substance Abuse', 'Substance Abuse', 'ed10', 1215615910, 1),
(31, 'Womens Health', 'Womens Health', 'ed10', 1215615910, 1),
(32, 'Anatomy', 'Anatomy', 'ed11', 1215615910, 1),
(33, 'Biochemistry', 'Biochemistry', 'ed11', 1215615910, 1),
(34, 'Genetics', 'Genetics', 'ed11', 1215615910, 1),
(35, 'Physiology', 'Physiology', 'ed11', 1215615910, 1),
(36, 'Microbiology and Immunology', 'Microbiology and Immunology', 'ed11', 1215615910, 1),
(37, 'Pathology', 'Pathology', 'ed11', 1215615910, 1),
(38, 'Pharmacology Therapeutics', 'Pharmacology Therapeutics', 'ed11', 1215615910, 1),
(39, 'Preventive Medicine', 'Preventive Medicine', 'ed11', 1215615910, 1);

DROP TABLE `event_lu_ed10`;
DROP TABLE `event_lu_ed11`;

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
SELECT `event_id`, `ed10_id` as `topic_id`, if(`major_topic`, 'major', 'minor') as `topic_coverage`, `minor_desc` as `topic_time`, `updated_date`, `updated_by` FROM `event_ed10`
UNION
SELECT `event_id`, `ed11_id`+31 as `topic_id`, if(`major_topic`, 'major', 'minor') as `topic_coverage`, `minor_desc` as `topic_time`, `updated_date`, `updated_by` FROM `event_ed11`;

DROP TABLE `event_ed10`;
DROP TABLE `event_ed11`;

UPDATE `settings` SET `value` = '1.2.0' WHERE `shortname` = 'version_db';


