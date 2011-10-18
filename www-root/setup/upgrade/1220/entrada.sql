CREATE TABLE IF NOT EXISTS `notice_audience`(
	`naudience_id` INT(11) NOT NULL AUTO_INCREMENT, 
	`notice_id` INT(11) NOT NULL,
	`audience_type` VARCHAR(20) NOT NULL,
	`audience_value` INT(11) NOT NULL DEFAULT '0',
	`updated_by` INT(11) NOT NULL DEFAULT '0',
	`updated_date` bigint(64) NOT NULL DEFAULT '0',
	PRIMARY KEY (`naudience_id`),
	KEY `audience_id`(`notice_id`,`audience_type`,`audience_value`,`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;	

-- RUN THE UPGRADE.PHP FILE AFTER THE QUERY ABOVE BUT BEFORE THE QUERY BELOW

ALTER TABLE `notices` DROP COLUMN `target`; 


UPDATE `settings` SET `value` = '1220' WHERE `shortname` = 'version_db';