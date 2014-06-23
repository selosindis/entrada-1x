ALTER TABLE `course_audience` ADD `ldap_sync_date` bigint(64) NOT NULL DEFAULT '0' AFTER `cperiod_id`;

UPDATE `settings` SET `value` = '1611' WHERE `shortname` = 'version_db';