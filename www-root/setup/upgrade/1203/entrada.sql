CREATE TABLE IF NOT EXISTS `eventtype_organisation`(
`eventtype_id` INT(12) NOT NULL, 
`organisation_id` INT(12) NOT NULL, 
KEY `eventtype_id` (`eventtype_id`),
KEY `organisation_id` (`organisation_id`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO eventtype_organisation SELECT a.eventtype_id, b.organisation_id FROM events_lu_eventtypes AS a JOIN entrada_auth.organisations AS b ON 1=1;

UPDATE `settings` SET `value` = '1203' WHERE `shortname` = 'version_db';