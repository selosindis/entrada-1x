CREATE TABLE IF NOT EXISTS `assessment_events` (
  `assessment_event_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) unsigned NOT NULL,
  `event_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`assessment_event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1611' WHERE `shortname` = 'version_db';
