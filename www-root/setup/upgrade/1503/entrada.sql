ALTER TABLE `events` ADD `objectives_release_date` BIGINT(64) DEFAULT 0  AFTER `event_objectives`;
ALTER TABLE `draft_events` ADD `objectives_release_date` BIGINT(64) DEFAULT 0  AFTER `event_objectives`;

UPDATE `settings` SET `value` = '1503' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0.3' WHERE `shortname` = 'version_entrada';