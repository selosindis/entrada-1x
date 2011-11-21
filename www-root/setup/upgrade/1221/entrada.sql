CREATE TABLE `community_templates` (
  `template_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(60) NOT NULL,
  `template_description` text,
  `organisation_id` int(12) unsigned DEFAULT NULL,
  `group` int(12) unsigned DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `community_templates` (`template_id`, `template_name`, `template_description`, `organisation_id`, `group`, `role`) VALUES
(1, 'default', "", NULL, NULL, NULL),
(2, 'meeting', "", NULL, NULL, NULL),
(3, 'vp', "", NULL, NULL, NULL),
(4, 'education', "", NULL, NULL, NULL);


UPDATE `settings` SET `value` = '1221' WHERE `shortname` = 'version_db';