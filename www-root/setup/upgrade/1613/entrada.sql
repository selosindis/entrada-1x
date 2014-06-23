ALTER TABLE `events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' AFTER `draft_id`;
ALTER TABLE `draft_events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_duration`;

UPDATE `settings` SET `value` = '1613' WHERE `shortname` = 'version_db';
