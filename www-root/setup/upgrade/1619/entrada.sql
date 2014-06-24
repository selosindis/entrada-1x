ALTER TABLE `events` ADD COLUMN `keywords_release_date` bigint(64) DEFAULT '0' AFTER `event_objectives`;
ALTER TABLE `events` ADD COLUMN `keywords_hidden` int(1) DEFAULT '0' AFTER `event_objectives`;

UPDATE `settings` SET `value` = '1619' WHERE `shortname` = 'version_db';
