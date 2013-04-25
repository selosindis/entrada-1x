CREATE TABLE `profile_custom_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL DEFAULT '',
  `type` enum('TEXTAREA','TEXTINPUT','CHECKBOX','RICHTEXT') NOT NULL DEFAULT 'TEXTAREA',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `length` smallint(3) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `profile_custom_responses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1503' WHERE `shortname` = 'version_db';