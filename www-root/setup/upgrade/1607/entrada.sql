ALTER TABLE `student_observerships` ADD `title` varchar(256) NOT NULL AFTER `student_id`;
ALTER TABLE `student_observerships` ADD `city` varchar(32) DEFAULT NULL AFTER `location`;
ALTER TABLE `student_observerships` ADD `prov` varchar(32) DEFAULT NULL AFTER `city`;
ALTER TABLE `student_observerships` ADD `country` varchar(32) DEFAULT NULL AFTER `prov`;
ALTER TABLE `student_observerships` ADD `postal_code` varchar(12) DEFAULT NULL AFTER `country`;
ALTER TABLE `student_observerships` ADD `address_l1` varchar(64) DEFAULT NULL AFTER `postal_code`;
ALTER TABLE `student_observerships` ADD `address_l2` varchar(64) DEFAULT NULL AFTER `address_l1`;
ALTER TABLE `student_observerships` ADD `observership_details` text DEFAULT NULL AFTER `address_l2`;
ALTER TABLE `student_observerships` ADD `activity_type` varchar(32) DEFAULT NULL AFTER `observership_details`;
ALTER TABLE `student_observerships` ADD `clinical_discipline` varchar(32) DEFAULT NULL AFTER `activity_type`;
ALTER TABLE `student_observerships` ADD `organisation` varchar(32) DEFAULT NULL AFTER `clinical_discipline`;
ALTER TABLE `student_observerships` ADD `order` int(3) DEFAULT NULL AFTER `organisation`;
ALTER TABLE `student_observerships` ADD `reflection_id` int(11) DEFAULT NULL AFTER `order`;

CREATE TABLE IF NOT EXISTS `observership_reflections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `observership_id` int(11) NOT NULL,
  `physicians_role` text NOT NULL,
  `physician_reflection` text NOT NULL,
  `role_practice` text,
  `observership_challenge` text NOT NULL,
  `discipline_reflection` text NOT NULL,
  `challenge_predictions` text,
  `questions` text,
  `career` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1607' WHERE `shortname` = 'version_db';