ALTER TABLE `communities` ADD COLUMN `octype_id` int(11) NOT NULL DEFAULT '1' AFTER `community_url`;

CREATE TABLE IF NOT EXISTS `community_type_page_options` (
  `ctpoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `ctpage_id` int(12) NOT NULL,
  `option_title` varchar(32) NOT NULL,
  `option_value` int(12) NOT NULL DEFAULT '1',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ctpoption_id`,`ctpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `community_type_pages` (
  `ctpage_id` int(12) NOT NULL AUTO_INCREMENT,
  `type_id` int(12) NOT NULL DEFAULT '0',
  `type_scope` enum('organisation','global') NOT NULL DEFAULT 'global',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `page_order` int(3) NOT NULL DEFAULT '0',
  `page_type` varchar(16) NOT NULL DEFAULT 'default',
  `menu_title` varchar(48) NOT NULL,
  `page_title` text NOT NULL,
  `page_url` varchar(512) DEFAULT NULL,
  `page_content` longtext NOT NULL,
  `page_active` tinyint(1) NOT NULL DEFAULT '1',
  `page_visible` tinyint(1) NOT NULL DEFAULT '1',
  `allow_member_view` tinyint(1) NOT NULL DEFAULT '1',
  `allow_troll_view` tinyint(1) NOT NULL DEFAULT '1',
  `allow_public_view` tinyint(1) NOT NULL DEFAULT '0',
  `lock_page` tinyint(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ctpage_id`),
  KEY `type_id` (`type_id`, `type_scope`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `community_type_pages` (`ctpage_id`, `type_id`, `type_scope`, `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`, `lock_page`, `updated_date`, `updated_by`)
VALUES
	(1,1,'global',0,0,'default','Home','Home','','',1,1,1,1,1,1,1362062187,1),
	(2,1,'global',0,1,'announcements','Announcements','Announcements','announcements','',1,1,1,1,0,0,1362062187,1),
	(3,1,'global',0,2,'discussions','Discussions','Discussions','discussions','',1,1,1,1,0,0,1362062187,1),
	(8,1,'global',0,3,'shares','Document Sharing','Document Sharing','shares','',1,1,1,1,0,0,1362062187,1),
	(4,1,'global',0,4,'events','Events','Events','events','',1,1,1,1,0,0,1362062187,1),
	(5,1,'global',0,5,'galleries','Galleries','Galleries','galleries','',1,1,1,1,0,0,1362062187,1),
	(6,1,'global',0,6,'polls','Polling','Polling','polls','',1,1,1,1,0,0,1362062187,1),
	(7,1,'global',0,7,'quizzes','Quizzes','Quizzes','quizzes','',1,1,1,1,0,0,1362062187,1),
	(9,2,'global',0,0,'course','Background','Background Information','',' ',1,1,1,0,1,1,1362062187,1),
	(10,2,'global',0,1,'course','Course Calendar','Course Calendar','course_calendar',' ',1,1,1,0,1,1,1362062187,1),
	(11,2,'global',0,2,'default','Prerequisites','Prerequisites (Foundational Knowledge)','prerequisites',' ',1,1,1,0,1,1,1362062187,1),
	(12,2,'global',0,3,'default','Course Aims','Aims of the Course','course_aims',' ',1,1,1,0,1,1,1362062187,1),
	(13,2,'global',0,4,'course','Learning Objectives','Learning Objectives','objectives',' ',1,1,1,0,1,1,1362062187,1),
	(14,2,'global',0,5,'course','MCC Presentations','MCC Presentations','mcc_presentations',' ',1,1,1,0,1,1,1362062187,1),
	(15,2,'global',0,6,'default','Teaching Strategies','Teaching and Learning Strategies','teaching_strategies',' ',1,1,1,0,1,1,1362062187,1),
	(16,2,'global',0,7,'default','Assessment Strategies','Assessment Strategies','assessment_strategies',' ',1,1,1,0,1,1,1362062187,1),
	(17,2,'global',0,8,'default','Resources','Resources','resources',' ',1,1,1,0,1,1,1362062187,1),
	(18,2,'global',0,9,'default','Expectations of Students','What is Expected of Students','expectations_of_students',' ',1,1,1,0,1,1,1362062187,1),
	(19,2,'global',0,10,'default','Expectations of Faculty','What is Expected of Course Faculty','expectations_of_faculty',' ',1,1,1,0,1,1,1362062187,1);

CREATE TABLE IF NOT EXISTS `community_type_templates` (
  `cttemplate_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(12) unsigned NOT NULL,
  `type_id` int(12) unsigned NOT NULL,
  `type_scope` enum('organisation','global') NOT NULL DEFAULT 'global',
  PRIMARY KEY (`cttemplate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `community_type_templates` (`cttemplate_id`, `template_id`, `type_id`, `type_scope`)
VALUES
	(1,1,1,'global'),
	(2,2,1,'global'),
	(3,3,1,'global'),
	(4,4,1,'global'),
	(5,5,1,'global'),
	(6,5,2,'global');

CREATE TABLE IF NOT EXISTS `global_lu_community_type_options` (
  `ctoption_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `option_shortname` varchar(32) NOT NULL DEFAULT '',
  `option_name` varchar(84) NOT NULL DEFAULT '',
  PRIMARY KEY (`ctoption_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_community_type_options` (`ctoption_id`, `option_shortname`, `option_name`)
VALUES
	(1,'course_website','Course Website Functionality'),
	(2,'sequential_navigation','Learning Module Sequential Navigation');

CREATE TABLE IF NOT EXISTS `global_lu_community_types` (
  `ctype_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_type_name` varchar(84) DEFAULT NULL,
  `default_community_template` varchar(30) NOT NULL DEFAULT 'default',
  `default_community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `default_community_keywords` varchar(255) NOT NULL DEFAULT '',
  `default_community_protected` int(1) NOT NULL DEFAULT '1',
  `default_community_registration` int(1) NOT NULL DEFAULT '1',
  `default_community_members` text NOT NULL,
  `default_mail_list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `default_community_type_options` text NOT NULL,
  `community_type_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ctype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `global_lu_community_types` (`ctype_id`, `community_type_name`, `default_community_template`, `default_community_theme`, `default_community_keywords`, `default_community_protected`, `default_community_registration`, `default_community_members`, `default_mail_list_type`, `default_community_type_options`, `community_type_active`)
VALUES
	(1,'Community','default','default','',1,0,'','inactive','{}',1),
	(2,'Course Website','course','course','',1,0,'','inactive','{\"course_website\":\"1\"}',1),
	(3,'Online Learning Module','learningmodule','default','',1,0,'','inactive','{\"sequential_navigation\":\"1\"}',1);

CREATE TABLE IF NOT EXISTS `org_community_types` (
  `octype_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `community_type_name` varchar(84) NOT NULL DEFAULT '',
  `default_community_template` varchar(30) NOT NULL DEFAULT 'default',
  `default_community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `default_community_keywords` varchar(255) NOT NULL DEFAULT '',
  `default_community_protected` int(1) NOT NULL DEFAULT '1',
  `default_community_registration` int(1) NOT NULL DEFAULT '1',
  `default_community_members` text NOT NULL,
  `default_mail_list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `community_type_options` text NOT NULL,
  `community_type_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`octype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `community_page_navigation` (
  `cpnav_id` INT(12) NOT NULL AUTO_INCREMENT,
  `community_id` INT(12) NOT NULL,
  `cpage_id` INT(12) NOT NULL DEFAULT '0',
  `nav_page_id` int(11) DEFAULT NULL,
  `show_nav` INT(1) NOT NULL DEFAULT '1',
  `nav_title` VARCHAR(100) NOT NULL DEFAULT 'Next',
  `nav_type` ENUM('next','previous') NOT NULL DEFAULT 'next',
  `nav_url` varchar(1000) DEFAULT NULL,
  `updated_date` BIGINT(64) NOT NULL DEFAULT '0',
  `updated_by` INT(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpnav_id`),
  KEY `cpage_id` (`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1402' WHERE `shortname` = 'version_db';