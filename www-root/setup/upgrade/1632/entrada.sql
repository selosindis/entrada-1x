CREATE TABLE IF NOT EXISTS `event_lu_resource_types` (
  `event_resource_type_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(100) DEFAULT NULL,
  `description` text,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `event_lu_resource_types` (`event_resource_type_id`, `resource_type`, `description`, `updated_date`, `updated_by`, `active`)
VALUES
	(1, 'Podcast', 'Attach a podcast to this learning event.', UNIX_TIMESTAMP(), 1, 1),
	(2, 'Bring to Class', 'Attach a description of materials students should bring to class.', UNIX_TIMESTAMP(), 1, 0),
	(3, 'Link', 'Attach links to external websites that relate to the learning event.', UNIX_TIMESTAMP(), 1, 1),
	(4, 'Homework', 'Attach a description to indicate homework tasks assigned to students.', UNIX_TIMESTAMP(), 1, 0),
    (5, 'Lecture Notes', 'Attach files such as documents, pdfs or images.', UNIX_TIMESTAMP(), 1, 1),
    (6, 'Lecture Slides', 'Attach files such as documents, powerpoint files, pdfs or images.', UNIX_TIMESTAMP(), 1, 1),
    (7, 'Online Learning Module', 'Attach links to external learning modules.', UNIX_TIMESTAMP(), 1, 1),
    (8, 'Quiz', 'Attach an existing quiz to this learning event.', UNIX_TIMESTAMP(), 1, 1),
    (9, 'Textbook Reading', 'Attach a reading list related to this learning event.', UNIX_TIMESTAMP(), 1, 0),
    (10, 'LTI Provider', '', UNIX_TIMESTAMP(), 1, 0),
    (11, 'Other Files', 'Attach miscellaneous media files to this learning event.', UNIX_TIMESTAMP(), 1, 1);

CREATE TABLE IF NOT EXISTS `event_resource_entities` (
  `event_resource_entity_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `entity_type` int(12) NOT NULL,
  `entity_value` int(12) NOT NULL,
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_resource_homework` (
  `event_resource_homework_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_homework` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_homework_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_resource_class_work` (
  `event_resource_class_work_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_class_work` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_class_work_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_resource_textbook_reading` (
  `event_resource_textbook_reading_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_textbook_reading` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_textbook_reading_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `event_id`, '3', `elink_id`, `release_date`, `release_until` FROM `event_links`;

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `content_id`, '8', `aquiz_id`, `release_date`, `release_until` FROM `attached_quizzes` 
WHERE `content_type` = 'event';

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `event_id`, '11', `efile_id`, `release_date`, `release_until` FROM `event_files` 
WHERE `file_category` = 'other';

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `event_id`, '1', `efile_id`, `release_date`, `release_until` FROM `event_files` 
WHERE `file_category` = 'podcast';

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `event_id`, '5', `efile_id`, `release_date`, `release_until` FROM `event_files` 
WHERE `file_category` = 'lecture_notes';

INSERT INTO `event_resource_entities` (`event_id`, `entity_type`, `entity_value`, `release_date`, `release_until`) SELECT `event_id`, '6', `efile_id`, `release_date`, `release_until` FROM `event_files` 
WHERE `file_category` = 'lecture_slides';

UPDATE `settings` SET `value` = '1632' WHERE `shortname` = 'version_db';
