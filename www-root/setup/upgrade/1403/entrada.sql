ALTER TABLE `evaluations` ADD COLUMN `require_requests` tinyint(1) NOT NULL DEFAULT '0' AFTER `show_comments`;
ALTER TABLE `evaluations` ADD COLUMN `require_request_code` tinyint(1) NOT NULL DEFAULT '0' AFTER `require_requests`;
ALTER TABLE `evaluations` ADD COLUMN `request_timeout` bigint(64) NOT NULL DEFAULT '0' AFTER `require_request_code`;

CREATE TABLE IF NOT EXISTS `evaluation_requests` (
  `erequest_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `request_expires` bigint(64) NOT NULL DEFAULT '0',
  `request_code` varchar(255) DEFAULT NULL,
  `evaluation_id` int(11) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `target_proxy_id` int(11) DEFAULT NULL,
  `request_created` bigint(64) NOT NULL DEFAULT '0',
  `request_fulfilled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`erequest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1403' WHERE `shortname` = 'version_db';