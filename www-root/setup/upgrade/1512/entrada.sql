CREATE TABLE IF NOT EXISTS `draft_options` (
  `draft_id` int(11) NOT NULL,
  `option` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1512' WHERE `shortname` = 'version_db';