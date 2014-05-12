ALTER TABLE `courses` ADD COLUMN `sync_groups` tinyint(1) DEFAULT NULL AFTER `sync_ldap_courses`;

UPDATE `settings` SET `value` = '1608' WHERE `shortname` = 'version_db';