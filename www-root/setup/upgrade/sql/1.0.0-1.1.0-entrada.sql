CREATE TABLE IF NOT EXISTS `assessments` (
  `assessment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `grad_year` int(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `marking_scheme_id` int(10) unsigned NOT NULL,
  `numeric_grade_points_total` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_grades` (
  `grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`grade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `assessment_marking_schemes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handler` varchar(255) NOT NULL DEFAULT 'Boolean',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `assessment_marking_schemes` (`id`,`name`,`handler`,`enabled`) VALUES
(1, 'Pass/Fail', 'Boolean', 1),
(2, 'Percentage', 'Percentage', 1),
(3, 'Numeric', 'Numeric', 1),
(4, 'Complete/Incomplete', 'IncompleteComplete', 1);

ALTER TABLE `courses` ADD COLUMN `objective_type` enum('event','course') DEFAULT 'course' AFTER `importance`;

ALTER TABLE `course_objectives` ADD COLUMN `objective_type` enum('event','course') DEFAULT 'course' AFTER `importance`;

CREATE TABLE IF NOT EXISTS `settings` (
  `shortname` VARCHAR( 64 ) NOT NULL ,
  `value` TEXT NOT NULL ,
  PRIMARY KEY ( `shortname` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `student_awards_external` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL,
  `awarding_body` varchar(4096) NOT NULL,
  `award_terms` mediumtext NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_awards_internal` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_awards_internal_types` (
  `id` int(11) NOT NULL auto_increment,
  `award_terms` mediumtext NOT NULL,
  `title` varchar(200) NOT NULL default '',
  `disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title_unique` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_clineval_comments` (
  `id` int(11) NOT NULL auto_increment,
  `source` varchar(4096) NOT NULL,
  `comment` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_community_health_and_epidemiology` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_contributions` (
  `id` int(11) NOT NULL auto_increment,
  `role` varchar(4096) NOT NULL,
  `org_event` varchar(256) NOT NULL default '',
  `date` varchar(256) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  `user_id` int(11) NOT NULL,
  `start_month` int(11) default NULL,
  `start_year` int(11) default NULL,
  `end_month` int(11) default NULL,
  `end_year` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_critical_enquiries` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_disciplinary_actions` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `action_details` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_formal_remediations` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `remediation_details` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_international_activities` (
  `id` int(11) NOT NULL auto_increment,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL default '0000-00-00 00:00:00',
  `end` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_leaves_of_absence` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `absence_details` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_mspr` (
  `user_id` int(11) default NULL,
  `last_update` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `generated` int(11) default NULL,
  `closed` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_mspr_class` (
  `year` int(11) NOT NULL default '0',
  `closed` int(11) default NULL,
  PRIMARY KEY  (`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_observerships` (
  `id` int(11) NOT NULL auto_increment,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL default '0000-00-00 00:00:00',
  `end` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_research` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `citation` varchar(4096) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `priority` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_studentships` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL default '0000',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `student_student_run_electives` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_month` tinyint(2) unsigned default NULL,
  `start_year` smallint(4) unsigned default NULL,
  `end_month` tinyint(2) unsigned default NULL,
  `end_year` smallint(4) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`shortname`, `value`) VALUES ('version_db', '1.1.0');