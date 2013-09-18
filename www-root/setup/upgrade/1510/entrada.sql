CREATE TABLE `organisation_lu_restricted_days` (
  `orday_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `date_type` enum('specific','computed','weekly','monthly') NOT NULL DEFAULT 'specific',
  `offset` tinyint(1) DEFAULT NULL,
  `day` tinyint(2) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `day_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`orday_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1510' WHERE `shortname` = 'version_db';