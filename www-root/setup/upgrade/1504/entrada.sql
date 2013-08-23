CREATE TABLE  IF NOT EXISTS `event_lti_consumers` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`event_id` INT NOT NULL ,
	`is_required` TINYINT NOT NULL ,
	`valid_from` INT NOT NULL ,
	`valid_until` INT NOT NULL ,
	`timeframe` VARCHAR( 100 ) NOT NULL ,
	`launch_url` VARCHAR( 500 ) NOT NULL ,
	`lti_key` VARCHAR( 300 ) NOT NULL ,
	`lti_secret` VARCHAR( 300 ) NOT NULL ,
	`lti_title` VARCHAR( 300 ) NOT NULL ,
	`lti_notes` TEXT NOT NULL ,
	`lti_params` TEXT NOT NULL ,
	`updated_date` BIGINT NOT NULL ,
	`updated_by` INT NOT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `course_lti_consumers` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`course_id` INT NOT NULL ,
	`is_required` TINYINT NOT NULL DEFAULT  '0',
	`valid_from` BIGINT NOT NULL ,
	`valid_until` BIGINT NOT NULL ,
	`launch_url` VARCHAR( 500 ) NOT NULL ,
	`lti_key` VARCHAR( 300 ) NOT NULL ,
	`lti_secret` VARCHAR( 300 ) NOT NULL ,
	`lti_title` VARCHAR( 300 ) NOT NULL ,
	`lti_notes` TEXT NOT NULL ,
	`updated_date` BIGINT NOT NULL ,
	`updated_by` INT NOT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

UPDATE `settings` SET `value` = '1504' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0.4' WHERE `shortname` = 'version_entrada';