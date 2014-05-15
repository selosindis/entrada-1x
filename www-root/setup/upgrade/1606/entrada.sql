CREATE TABLE IF NOT EXISTS `evaluations_lu_response_descriptors` (
  `erdescriptor_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL,
  `descriptor` varchar(256) NOT NULL DEFAULT '',
  `reportable` tinyint(1) NOT NULL DEFAULT '1',
  `order` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`erdescriptor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_question_response_descriptors` (
  `eqrdescriptor_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eqresponse_id` int(12) unsigned NOT NULL,
  `erdescriptor_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`eqrdescriptor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `evaluation_progress_patient_encounters` (
  `eppencounter_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `encounter_name` varchar(255) DEFAULT NULL,
  `encounter_complexity` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`eppencounter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `evaluations_lu_targets` SET `target_title` = 'Patient Encounter Assessment' WHERE `target_shortname` = 'resident';

UPDATE `settings` SET `value` = '1605' WHERE `shortname` = 'version_db';
