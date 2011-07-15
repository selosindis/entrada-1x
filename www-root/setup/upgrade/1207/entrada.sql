UPDATE `settings` SET `value` = '1207' WHERE `shortname` = 'version_db';

CREATE TABLE `course_audience`(
	`caudience_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	`course_id` INT NOT NULL, 
	`audience_type` ENUM('proxy_id','group_id') NOT NULL, 
	`audience_value` INT NOT NULL, 
	`enroll_start` BIGINT NOT NULL, 
	`enroll_finish` BIGINT NOT NULL, 
	`audience_active` INT(1) NOT NULL DEFAULT '1', 
 KEY `event_id` (`event_id`), 
 KEY `audience_type` (`audience_type`), 
 KEY `audience_value` (`audience_value`), 
 KEY `audience_active` (`audience_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `group_organisation`(
	`group_id` INT NOT NULL, 
	`organisation_id` INT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET = utf8;

ALTER TABLE `event_audience` MODIFY `audience_type` ENUM('proxy_id','grad_year','organisation_id','course');
ALTER TABLE `courses` ADD COLUMN `permission` ENUM('open','closed') NOT NULL DEFAULT 'closed' AFTER `course_message`;
ALTER TABLE `groups` ADD COLUMN `parent_id` INT DEFAULT NULL AFTER `group_name`;