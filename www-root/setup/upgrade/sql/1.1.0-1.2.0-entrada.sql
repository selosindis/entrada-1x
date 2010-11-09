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

UPDATE `settings` SET `value` = '1.2.0' WHERE `shortname` = 'version_db';

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

