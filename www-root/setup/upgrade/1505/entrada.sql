ALTER TABLE `courses` ADD `sync_ldap_courses` text DEFAULT NULL AFTER `sync_ldap`;

UPDATE `settings` SET `value` = '1505' WHERE `shortname` = 'version_db';
