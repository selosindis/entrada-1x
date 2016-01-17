CREATE TABLE IF NOT EXISTS `learning_object_files` (
  `lo_file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(11) NOT NULL,
  `mime_type` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `proxy_id` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `learning_object_file_tags` (
  `lo_file_tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lo_file_id` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL DEFAULT '',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `learning_object_file_permissions` (
  `lo_file_permission_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lo_file_id` int(11) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `permission` enum('read','write','delete') NOT NULL DEFAULT 'read',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1630' WHERE `shortname` = 'version_db';
