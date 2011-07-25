UPDATE `settings` SET `value` = '1208' WHERE `shortname` = 'version_db';

CREATE TABLE `curriculum_type_organisation` IF NOT EXISTS(
	`curriculum_type_id` INT NOT NULL, 
	`organisation_id` INT NOT NULL
)  ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `curriculum_periods` IF NOT EXISTS(
	`cperiod_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`curriculum_type_id` INT NOT NULL,
	`start_date` BIGINT(64) NOT NULL,
	`finish_date` BIGINT(64) NOT NULL,
	`active` INT(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8;