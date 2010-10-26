ALTER TABLE `event_quizzes` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quizzes` CHANGE `equiz_id` `aquiz_id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quizzes` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
RENAME TABLE `event_quizzes` TO `attached_quizzes`;

ALTER TABLE `event_quiz_progress` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_progress` CHANGE `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_progress` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_progress` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
RENAME TABLE `event_quiz_progress` TO `quiz_progress`;

ALTER TABLE `event_quiz_responses` ADD COLUMN `content_type` enum('event','community_page') NOT NULL DEFAULT 'event' AFTER `equiz_id`;
ALTER TABLE `event_quiz_responses` CHANGE `equiz_id` `aquiz_id` int(12) unsigned NOT NULL;
ALTER TABLE `event_quiz_responses` CHANGE `event_id` `content_id` int(12) NOT NULL DEFAULT '0';
ALTER TABLE `event_quiz_responses` CHANGE `eqresponse_id` `qpresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `event_quiz_responses` CHANGE `eqprogress_id` `qprogress_id` int(12) unsigned NOT NULL;
RENAME TABLE `event_quiz_responses` TO `quiz_progress_responses`;