CREATE TABLE IF NOT EXISTS `course_keywords` (
  `ckeyword_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL,
  `keyword_id` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ckeyword_id`),
  KEY `course_id` (`course_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_keywords` (
  `ekeyword_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `keyword_id` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ekeyword_id`),
  KEY `event_id` (`event_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1604' WHERE `shortname` = 'version_db';

