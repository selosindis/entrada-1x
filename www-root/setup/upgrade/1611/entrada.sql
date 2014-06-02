ALTER TABLE `evaluations_lu_questions` ADD COLUMN `question_description` longtext DEFAULT NULL AFTER `question_text`;
UPDATE `settings` SET `value` = '1611' WHERE `shortname` = 'version_db';