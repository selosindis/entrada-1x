CREATE TABLE IF NOT EXISTS `objective_audience` (
  `oaudience_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL,
  `audience_type` enum('COURSE','EVENT') NOT NULL DEFAULT 'COURSE',
  `audience_value` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`oaudience_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1401' WHERE `shortname` = 'version_db';