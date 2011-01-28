ALTER TABLE `assessments` ADD COLUMN `grade_weighting` int(11) NOT NULL default '0' AFTER `numeric_grade_points_total`;

CREATE TABLE `assessment_exceptions` (
  `aexception_id` int(12) NOT NULL auto_increment,
  `assessment_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `grade_weighting` int(11) NOT NULL default '0',
  PRIMARY KEY  (`aexception_id`),
  KEY `proxy_id` (`assessment_id`,`proxy_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;