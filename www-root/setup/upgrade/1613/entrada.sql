ALTER TABLE `events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' BEFORE `draft_id`;
ALTER TABLE `draft_events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' BEFORE `release_date`;

UPDATE `settings` SET `value` = '1613' WHERE `shortname` = 'version_db';
