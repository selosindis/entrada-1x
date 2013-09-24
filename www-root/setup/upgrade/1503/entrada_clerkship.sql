CREATE TABLE IF NOT EXISTS `apartment_contacts` (
  `acontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `apartment_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `department_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acontact_id`),
  KEY `apartment_id` (`apartment_id`,`proxy_id`,`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `apartments` ADD COLUMN `department_id` int(12) NOT NULL DEFAULT '0' AFTER `region_id`;