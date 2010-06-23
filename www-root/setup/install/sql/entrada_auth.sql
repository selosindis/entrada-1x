SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `permission_id` int(12) NOT NULL auto_increment,
  `resource_type` varchar(64) default NULL,
  `resource_value` int(12) default NULL,
  `entity_type` varchar(64) default NULL,
  `entity_value` varchar(64) default NULL,
  `app_id` int(12) NOT NULL,
  `create` tinyint(1) default NULL,
  `read` tinyint(1) default NULL,
  `update` tinyint(1) default NULL,
  `delete` tinyint(1) default NULL,
  `assertion` varchar(50) default NULL,
  PRIMARY KEY  (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=116 ;

INSERT INTO `acl_permissions` (`permission_id`, `resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES
(NULL, 'community', NULL, NULL, NULL, 1, 1, 1, NULL, NULL, 'NotGuest'),
(NULL, 'course', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest'),
(NULL, 'dashboard', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'discussion', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'library', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'people', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'podcast', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'profile', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'search', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'event', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'ResourceOrganisation&NotGuest'),
(NULL, 'resourceorganisation', 1, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'coursecontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
(NULL, 'event', NULL, 'role', 'pcoordinator', 1, 1, NULL, NULL, NULL, 'CourseOwner'),
(NULL, 'event', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, 1, 'EventOwner'),
(NULL, 'eventcontent', NULL, 'role', 'pcoordinator', 1, NULL, NULL, 1, NULL, 'EventOwner'),
(NULL, 'coursecontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
(NULL, 'coursecontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'CourseOwner'),
(NULL, 'eventcontent', NULL, 'role', 'lecturer', 1, NULL, NULL, 1, NULL, 'EventOwner'),
(NULL, 'eventcontent', NULL, 'role', 'director', 1, NULL, NULL, 1, NULL, 'EventOwner'),
(NULL, 'eventcontent', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, NULL, 'EventOwner'),
(NULL, NULL, NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'notice', NULL, 'group:role', 'faculty:director', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'notice', NULL, 'group:role', 'faculty:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'notice', NULL, 'group:role', 'staff:admin', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'notice', NULL, 'group:role', 'staff:pcoordinator', 1, 1, NULL, 1, 1, 'ResourceOrganisation'),
(NULL, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:director', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'resourceorganisation', 1, 'organisation:group:role', '1:faculty:admin', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:pcoordinator', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'poll', NULL, 'role', 'admin', 1, 1, NULL, 1, 1, NULL),
(NULL, 'poll', NULL, 'role', 'pcoordinator', 1, 1, NULL, 1, 1, NULL),
(NULL, 'quiz', NULL, 'group:role', 'faculty:director', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, 'firstlogin', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'community', NULL, NULL, NULL, 1, NULL, NULL, 1, 1, 'CommunityOwner'),
(NULL, 'quiz', NULL, 'group:role', 'faculty:admin', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, 'quiz', NULL, 'group:role', 'faculty:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, 'quiz', NULL, 'group:role', 'staff:admin', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, 'quiz', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, NULL, 1, 1, 'QuizOwner'),
(NULL, NULL, NULL, 'group:role', 'guest:communityinvite', 1, 0, 0, 0, 0, NULL),
(NULL, 'clerkship', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'Clerkship'),
(NULL, 'clerkship', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'clerkship', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
(NULL, NULL, NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, 'ResourceOrganisation'),
(NULL, 'resourceorganisation', 1, 'organisation:group:role', '1:staff:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'clerkship', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'clerkship', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL),
(NULL, 'quiz', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'NotGuest'),
(NULL, 'quiz', NULL, 'group', 'faculty', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'quiz', NULL, 'group', 'staff', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'quiz', NULL, 'group:role', 'resident:lecturer', 1, 1, NULL, NULL, NULL, NULL),
(NULL, 'photo', NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, 'Photo'),
(NULL, 'photo', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'photo', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'clerkshipschedules', NULL, 'group', 'faculty', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'clerkshipschedules', NULL, 'group', 'staff', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'reportindex', NULL, 'organisation:group:role', '1:staff:admin', '1', NULL, '1', NULL, NULL, NULL),
(NULL, 'report', NULL, 'organisation:group:role', '1:staff:admin', '1', NULL, '1', NULL, NULL, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'faculty:director', 1, 1, 1, 1, 1, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'faculty:clerkship', 1, 1, 1, 1, 1, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'faculty:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'faculty:lecturer', 1, 1, 1, 1, 1, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'assistant_support', NULL, 'group:role', 'staff:pcoordinator', 1, 1, 1, 1, 1, NULL),
(NULL, 'lottery', NULL, 'group', 'student', 1, NULL, 1, NULL, NULL, 'ClerkshipLottery'),
(NULL, 'lottery', NULL, 'group:role', 'staff:admin', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'lottery', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, NULL),
(NULL, 'logbook', NULL, 'group:role', 'staff:pcoordinator', 1, NULL, 1, 1, NULL, NULL),
(NULL, 'objective', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL),
(NULL, 'objective', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL),
(NULL, 'objectivecontent', NULL, 'group', 'staff', 1, NULL, NULL, 1, 1, NULL),
(NULL, 'objectivecontent', NULL, 'group', 'faculty', 1, NULL, NULL, 1, 1, NULL),
(NULL, 'objective', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'objective', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL),
(NULL, 'annualreport', NULL, 'group', 'faculty', 1, 1, 1, 1, 1, NULL),
(NULL, 'gradebook', NULL, 'role', 'pcoordinator', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
(NULL, 'gradebook', NULL, 'group:role', 'faculty:admin', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
(NULL, 'gradebook', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, 'GradebookOwner');

CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` int(12) UNSIGNED NOT NULL auto_increment,
  `organisation_id` int(12) UNSIGNED NOT NULL default 1,
  `entity_id` int(12) UNSIGNED NOT NULL default 0,
  `department_title` varchar(128) NOT NULL default '',
  `department_address1` varchar(128) NOT NULL default '',
  `department_address2` varchar(128) NOT NULL default '',
  `department_city` varchar(64) NOT NULL default 'Kingston',
  `department_province` varchar(64) NOT NULL default 'ON',
  `department_country` varchar(64) NOT NULL default 'CA',
  `department_postcode` varchar(16) NOT NULL default '',
  `department_telephone` varchar(32) NOT NULL default '',
  `department_fax` varchar(32) NOT NULL default '',
  `department_email` varchar(128) NOT NULL default '',
  `department_url` text NOT NULL,
  `department_desc` text,
  PRIMARY KEY  (`department_id`),
  UNIQUE KEY `organisation_id` (`organisation_id`,`entity_id`,`department_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `departments` (`department_id`, `organisation_id`, `entity_id`, `department_title`, `department_address1`, `department_address2`, `department_city`, `department_province`, `department_country`, `department_postcode`, `department_telephone`, `department_fax`, `department_email`, `department_url`, `department_desc`) VALUES
(1, 1, 5, 'Medical IT', '', '', 'Kingston', 'ON', 'CA', '', '', '', '', '', NULL);

CREATE TABLE IF NOT EXISTS `entity_type` (
  `entity_id` int(12)  UNSIGNED NOT NULL auto_increment,
  `entity_title` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`entity_id`)
) ENGINE=MyISAM COMMENT='Used to define entities (departments, schools, etc)';

INSERT INTO `entity_type` (`entity_id`, `entity_title`) VALUES
(1, 'Faculty'),
(2, 'School'),
(3, 'Department'),
(4, 'Division'),
(5, 'Unit');

CREATE TABLE IF NOT EXISTS `locations` (
  `location_id` int(12) UNSIGNED NOT NULL auto_increment,
  `organisation_id` int(12) UNSIGNED NOT NULL default 1,
  `department_id` int(12) UNSIGNED NOT NULL default 0,
  `location_title` varchar(128) NOT NULL default '',
  `location_address1` varchar(128) NOT NULL default '',
  `location_address2` varchar(128) NOT NULL default '',
  `location_city` varchar(64) NOT NULL default 'Kingston',
  `location_province` char(2) NOT NULL default 'ON',
  `location_country` char(2) NOT NULL default 'CA',
  `location_postcode` varchar(7) NOT NULL default '',
  `location_telephone` varchar(32) NOT NULL default '',
  `location_fax` varchar(32) NOT NULL default '',
  `location_email` varchar(128) NOT NULL default '',
  `location_url` text NOT NULL,
  `location_longitude` varchar(12) default NULL,
  `location_latitude` varchar(12) default NULL,
  `location_desc` text,
  PRIMARY KEY  (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location_ipranges` (
  `iprange_id` int(12) UNSIGNED NOT NULL auto_increment,
  `location_id` int(12) UNSIGNED NOT NULL default '0',
  `block_start` varchar(32) NOT NULL default '0',
  `block_end` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`iprange_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `organisations` (
  `organisation_id` int(12) UNSIGNED NOT NULL auto_increment,
  `organisation_title` varchar(128) NOT NULL default '',
  `organisation_address1` varchar(128) NOT NULL default '',
  `organisation_address2` varchar(128) NOT NULL default '',
  `organisation_city` varchar(64) NOT NULL default 'Kingston',
  `organisation_province` varchar(64) NOT NULL default 'ON',
  `organisation_country` varchar(64) NOT NULL default 'CA',
  `organisation_postcode` varchar(16) NOT NULL default '',
  `organisation_telephone` varchar(32) NOT NULL default '',
  `organisation_fax` varchar(32) NOT NULL default '',
  `organisation_email` varchar(128) NOT NULL default '',
  `organisation_url` text NOT NULL,
  `organisation_desc` text,
  PRIMARY KEY  (`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `organisations` (`organisation_id`, `organisation_title`, `organisation_address1`, `organisation_address2`, `organisation_city`, `organisation_province`, `organisation_country`, `organisation_postcode`, `organisation_telephone`, `organisation_fax`, `organisation_email`, `organisation_url`, `organisation_desc`) VALUES
(1, 'Your University', 'University Avenue', '', 'Kingston', 'ON', 'CA', 'K7L3N6', '613-533-2000', '', '', 'http://www.yourschool.ca', NULL);

CREATE TABLE IF NOT EXISTS `password_reset` (
  `id` int(12) UNSIGNED NOT NULL auto_increment,
  `ip` varchar(24) NOT NULL default '',
  `date` bigint(64) NOT NULL default '0',
  `user_id` int(12) NOT NULL default '0',
  `hash` varchar(64) NOT NULL default '',
  `complete` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `registered_apps` (
  `id` int(12) UNSIGNED NOT NULL auto_increment,
  `script_id` varchar(25) NOT NULL default '0',
  `script_password` varchar(255) NOT NULL default '',
  `server_ip` varchar(75) NOT NULL default '',
  `server_url` text NOT NULL,
  `employee_rep` int(12) UNSIGNED NOT NULL default 0,
  `notes` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `script_id` (`script_id`),
  KEY `script_password` (`script_password`),
  KEY `server_ip` (`server_ip`),
  KEY `employee_rep` (`employee_rep`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `registered_apps` (`id`, `script_id`, `script_password`, `server_ip`, `server_url`, `employee_rep`, `notes`) VALUES
(1, '30000001', MD5('apple123'), '%', '%', 1, 'Entrada');

CREATE TABLE IF NOT EXISTS `sessions` (
  `sesskey` varchar(64) NOT NULL default '',
  `expiry` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `expireref` varchar(250) default '',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `sessdata` longtext,
  PRIMARY KEY  (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `statistics` (
  `statistic_id` int(12) UNSIGNED NOT NULL auto_increment,
  `proxy_id` int(12) UNSIGNED NOT NULL default '0',
  `app_id` int(12) UNSIGNED NOT NULL default '0',
  `role` varchar(32) NOT NULL default '',
  `group` varchar(32) NOT NULL default '',
  `timestamp` bigint(64) NOT NULL default '0',
  `prune_after` bigint(64) NOT NULL default '0',
  PRIMARY KEY  (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`app_id`,`role`,`group`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_access` (
  `id` int(12) UNSIGNED NOT NULL auto_increment,
  `user_id` int(12) UNSIGNED NOT NULL default '0',
  `app_id` int(12) UNSIGNED NOT NULL default '0',
  `account_active` enum('true','false') NOT NULL default 'true',
  `access_starts` bigint(64) NOT NULL default '0',
  `access_expires` bigint(64) NOT NULL default '0',
  `last_login` bigint(64) NOT NULL default '0',
  `last_ip` varchar(75) NOT NULL default '',
  `login_attempts` INT NULL,
  `locked_out_until` BIGINT( 64 ) NULL,
  `role` varchar(35) NOT NULL default '',
  `group` varchar(35) NOT NULL default '',
  `extras` longtext NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `app_id` (`app_id`),
  KEY `account_active` (`account_active`),
  KEY `access_starts` (`access_starts`),
  KEY `access_expires` (`access_expires`),
  KEY `role` (`role`),
  KEY `group` (`group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_access` (`id`, `user_id`, `app_id`, `account_active`, `access_starts`, `access_expires`, `last_login`, `last_ip`, `role`, `group`, `extras`, `notes`) VALUES
(1, 1, 1, 'true', 1216149930, 0, 0, '', 'admin', 'medtech', 'YToxOntzOjE2OiJhbGxvd19wb2RjYXN0aW5nIjtzOjM6ImFsbCI7fQ==', '');

CREATE TABLE IF NOT EXISTS `user_data` (
  `id` int(12) UNSIGNED NOT NULL auto_increment,
  `number` int(12) UNSIGNED NOT NULL default '0',
  `username` varchar(25) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `organisation_id` int(12) NOT NULL default '1',
  `department` varchar(255) default NULL,
  `prefix` varchar(10) NOT NULL default '',
  `firstname` varchar(35) NOT NULL default '',
  `lastname` varchar(35) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `email_alt` varchar(255) NOT NULL default '',
  `google_id` varchar(128) default NULL,
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `city` varchar(35) NOT NULL default '',
  `province` varchar(35) NOT NULL default '',
  `postcode` varchar(12) NOT NULL default '',
  `country` varchar(35) NOT NULL default '',
  `notes` text NOT NULL,
  `office_hours` text,
  `privacy_level` int(1) default '0',
  `notifications` int(1) NOT NULL default '0',
  `clinical` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `number` (`number`),
  KEY `password` (`password`),
  KEY `firstname` (`firstname`),
  KEY `lastname` (`lastname`),
  KEY `privacy_level` (`privacy_level`),
  KEY `google_id` (`google_id`),
  KEY `clinical` (`clinical`),
  FULLTEXT KEY `firstname_2` (`firstname`,`lastname`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `user_data` (`id`, `number`, `username`, `password`, `organisation_id`, `department`, `prefix`, `firstname`, `lastname`, `email`, `email_alt`, `telephone`, `fax`, `address`, `city`, `province`, `postcode`, `country`, `notes`, `privacy_level`) VALUES
(1, 4857241, '%ADMIN_USERNAME%', '%ADMIN_PASSWORD_HASH%', 1, NULL, '', '%ADMIN_FIRSTNAME%', '%ADMIN_LASTNAME%', '%ADMIN_EMAIL%', '', '', '', '', '', '', '', '', 'System Administrator', 0);

CREATE TABLE IF NOT EXISTS `user_departments` (
  `udep_id` int(12) UNSIGNED NOT NULL auto_increment,
  `user_id` int(12) UNSIGNED NOT NULL default '0',
  `dep_id` int(12) UNSIGNED NOT NULL default '0',
  `dep_title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`udep_id`),
  KEY `user_id` (`user_id`),
  KEY `dep_id` (`dep_id`),
  KEY `dep_title` (`dep_title`)
) ENGINE=MyISAM AUTO_INCREMENT=1253 ;

INSERT INTO `user_departments` (`udep_id`, `user_id`, `dep_id`, `dep_title`) VALUES
(1, 1, 1, 'System Administrator');

CREATE TABLE IF NOT EXISTS `user_incidents` (
  `incident_id` int(12) UNSIGNED NOT NULL auto_increment,
  `proxy_id` int(12) UNSIGNED NOT NULL default '0',
  `incident_title` text NOT NULL,
  `incident_description` text,
  `incident_severity` tinyint(1) NOT NULL default '1',
  `incident_status` tinyint(1) NOT NULL default '1',
  `incident_author_id` int(12) NOT NULL default '0',
  `incident_date` bigint(64) NOT NULL default '0',
  `follow_up_date` bigint(64) default NULL,
  PRIMARY KEY  (`incident_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_photos` (
  `photo_id` int(12) UNSIGNED NOT NULL auto_increment,
  `proxy_id` int(12) UNSIGNED NOT NULL default '0',
  `photo_mimetype` varchar(64) NOT NULL,
  `photo_filesize` int(32) NOT NULL default '0',
  `photo_active` int(1) NOT NULL default '1',
  `photo_type` int(1) NOT NULL default '0',
  `updated_date` bigint(64) NOT NULL default '0',
  PRIMARY KEY  (`photo_id`),
  KEY `photo_active` (`photo_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `preference_id` int(12) UNSIGNED NOT NULL auto_increment,
  `app_id` int(12) UNSIGNED NOT NULL default '0',
  `proxy_id` int(12) UNSIGNED NOT NULL default '0',
  `module` varchar(32) NOT NULL default '',
  `preferences` text NOT NULL,
  `updated` bigint(64) NOT NULL default '0',
  PRIMARY KEY  (`preference_id`),
  KEY `app_id` (`app_id`,`proxy_id`,`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
