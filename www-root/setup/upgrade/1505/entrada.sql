ALTER TABLE `courses` ADD `sync_ldap_courses` text DEFAULT NULL AFTER `sync_ldap`;
ALTER TABLE `notices` ADD COLUMN `created_by` int(12) NOT NULL DEFAULT '0'  AFTER `updated_by`;
ALTER TABLE `quizzes` ADD COLUMN `created_by` int(12) NOT NULL DEFAULT '0'  AFTER `updated_by`;

UPDATE `settings` SET `value` = '1505' WHERE `shortname` = 'version_db';