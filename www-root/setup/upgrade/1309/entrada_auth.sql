ALTER TABLE `departments` ADD COLUMN updated_date bigint(64) unsigned NOT NULL;
ALTER TABLE `departments` ADD COLUMN updated_by int(12) unsigned NOT NULL;
