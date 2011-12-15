DROP TABLE IF EXISTS `community_templates`;

ALTER TABLE `communities` MODIFY `community_template` VARCHAR(30) NOT NULL DEFAULT 'default';

CREATE TABLE IF NOT EXISTS `community_templates` (
  `template_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(60) NOT NULL,
  `template_description` text,
  `organisation_id` int(12) unsigned DEFAULT NULL,
  `group` int(12) unsigned DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `community_templates` (`template_id`, `template_name`, `template_description`, `organisation_id`, `group`, `role`) VALUES
(1,'default','',NULL,NULL,NULL),
(2,'committee','',NULL,NULL,NULL),
(3,'virtualPatient','',NULL,NULL,NULL),
(4,'learningModule','',NULL,NULL,NULL),
(5,'course','',NULL,NULL,NULL);

CREATE TABLE IF NOT EXISTS `communities_template_permissions` (
  `ctpermission_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `permission_type` enum('category_id','group') DEFAULT NULL,
  `permission_value` varchar(32) DEFAULT NULL,
  `template` varchar(32) NOT NULL,
  PRIMARY KEY (`ctpermission_id`),
  KEY `permission_index` (`permission_type`,`permission_value`,`template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `communities_template_permissions` (`ctpermission_id`, `permission_type`, `permission_value`, `template`) VALUES
(1,'','','default'),
(2,'group','faculty,staff','course'),
(3,'category_id','5','course'),
(4,'group','faculty,staff','committee'),
(5,'category_id','12','committee'),
(6,'group','faculty,staff','learningModule'),
(7,'group','faculty,staff','virtualPatient'),
(9,'category_id','','virtualPatient'),
(8,'category_id','','learningModule');


UPDATE `settings` SET `value` = '1221' WHERE `shortname` = 'version_db';