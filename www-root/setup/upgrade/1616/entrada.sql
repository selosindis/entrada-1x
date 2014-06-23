ALTER TABLE `quiz_questions` ADD COLUMN `qquestion_group_id` int(12) unsigned DEFAULT NULL AFTER `question_order`;

UPDATE `settings` SET `value` = '1616' WHERE `shortname` = 'version_db';