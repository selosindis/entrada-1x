CREATE TABLE IF NOT EXISTS `organisation_lu_restricted_days` (
  `orday_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `date_type` enum('specific','computed','weekly','monthly') NOT NULL DEFAULT 'specific',
  `offset` tinyint(1) DEFAULT NULL,
  `day` tinyint(2) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `day_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`orday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `is_required` int(1) NOT NULL,
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `timeframe` varchar(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `course_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL,
  `is_required` int(1) NOT NULL DEFAULT '0',
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `curriculum_periods` ADD COLUMN `curriculum_period_title` VARCHAR(200) NOT NULL DEFAULT '' AFTER `curriculum_type_id`;

CREATE TABLE IF NOT EXISTS `portfolio_entries` (
  `pentry_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `submitted_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_by` int(10) unsigned NOT NULL,
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `flagged_by` int(10) unsigned NOT NULL,
  `flagged_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text NOT NULL,
  `_class` varchar(200) NOT NULL,
  `order` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('file','reflection','url') NOT NULL DEFAULT 'reflection',
  PRIMARY KEY (`pentry_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to record portfolio entries made by learners.';

CREATE TABLE IF NOT EXISTS `portfolio_artifact_permissions` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(10) unsigned NOT NULL,
  `allow_to` int(10) unsigned NOT NULL COMMENT 'Who allowed to access',
  `proxy_id` int(10) unsigned NOT NULL COMMENT 'Who has created this permission',
  `view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `portfolio_user_permissions_pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_user_permissions_pentry_id` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `portfolio_entry_comments` (
  `pecomment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `comment` text NOT NULL,
  `submitted_date` bigint(64) unsigned NOT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pecomment_id`),
  KEY `pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_entry_comments_ibfk_1` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to store comments on particular portfolio entries.';

CREATE TABLE IF NOT EXISTS `portfolios` (
  `portfolio_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(4) unsigned NOT NULL,
  `portfolio_name` varchar(100) NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL,
  `finish_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `organisation_id` int(11) NOT NULL,
  `allow_student_export` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`portfolio_id`),
  UNIQUE KEY `grad_year_unique` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The portfolio container for each class of learners.';

CREATE TABLE IF NOT EXISTS `portfolio_folders` (
  `pfolder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) unsigned NOT NULL,
  `title` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `allow_learner_artifacts` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfolder_id`),
  KEY `portfolio_id` (`portfolio_id`),
  CONSTRAINT `portfolio_folders_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`portfolio_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The list of folders within each portfolio.';

CREATE TABLE IF NOT EXISTS `portfolios_lu_artifacts` (
  `artifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `handler_object` varchar(80) NOT NULL COMMENT 'PHP class which handles displays form to user.',
  `allow_learner_addable` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`artifact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookup table that stores all available types of artifacts.';

CREATE TABLE IF NOT EXISTS `portfolio_folder_artifacts` (
  `pfartifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfolder_id` int(11) unsigned NOT NULL,
  `artifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `finish_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `allow_commenting` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text,
  `handler_object` varchar(80) NOT NULL,
  PRIMARY KEY (`pfartifact_id`),
  KEY `pfolder_id` (`pfolder_id`),
  KEY `artifact_id` (`artifact_id`),
  CONSTRAINT `portfolio_folder_artifacts_ibfk_1` FOREIGN KEY (`pfolder_id`) REFERENCES `portfolio_folders` (`pfolder_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `portfolio_folder_artifacts_ibfk_2` FOREIGN KEY (`artifact_id`) REFERENCES `portfolios_lu_artifacts` (`artifact_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of artifacts within a particular portfolio folder.';

CREATE TABLE IF NOT EXISTS `portfolio_folder_artifact_reviewers` (
  `pfareviewer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfareviewer_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  CONSTRAINT `portfolio_folder_artifact_reviewers_ibfk_1` FOREIGN KEY (`pfartifact_id`) REFERENCES `portfolio_folder_artifacts` (`pfartifact_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List teachers responsible for reviewing an artifact.';

CREATE TABLE IF NOT EXISTS `portfolio-advisors` (
  `padvisor_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `portfolio_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`padvisor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `evaluations` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `eform_id`;
ALTER TABLE `evaluation_forms` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `eform_id`;
ALTER TABLE `evaluations_lu_questions` ADD COLUMN `organisation_id` int(12) unsigned NOT NULL DEFAULT '0' AFTER `equestion_id`;

ALTER TABLE `evaluations`
CHANGE `min_submittable` `min_submittable` tinyint(3) NOT NULL DEFAULT '1';

ALTER TABLE `evaluations`
CHANGE `max_submittable` `max_submittable` tinyint(3) NOT NULL DEFAULT '1';

INSERT INTO `evaluations_lu_questiontypes` (`questiontype_shortname`, `questiontype_title`, `questiontype_description`, `questiontype_active`)
VALUES
  ('selectbox', 'Drop Down (single response)', 'The dropdown allows evaluators to answer each question by choosing one of up to 100 options which have been provided to populate a select box.', 1),
  ('vertical_matrix', 'Vertical Choice Matrix (single response)', 'The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).', 1);

UPDATE `evaluations_lu_questiontypes` SET `questiontype_title` = 'Horizontal Choice Matrix (single response)' WHERE `questiontype_shortname` = 'matrix_single';

ALTER TABLE `curriculum_periods` CHANGE `curriculum_period_title` `curriculum_period_title` VARCHAR(200)  CHARACTER SET utf8  NULL  DEFAULT '';

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

ALTER TABLE `reports_aamc_ci` CHANGE `report_start` `collection_start` BIGINT(64)  NOT NULL  DEFAULT '0';
ALTER TABLE `reports_aamc_ci` CHANGE `report_finish` `collection_finish` BIGINT(64)  NOT NULL  DEFAULT '0';
ALTER TABLE `reports_aamc_ci` ADD `report_start` VARCHAR(10)  NOT NULL  DEFAULT ''  AFTER `report_date`;
ALTER TABLE `reports_aamc_ci` ADD `report_finish` VARCHAR(10)  NOT NULL  DEFAULT ''  AFTER `report_start`;
ALTER TABLE `reports_aamc_ci` ADD `report_params` TEXT  NOT NULL  AFTER `report_supporting_link`;

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

ALTER TABLE `settings`
DROP PRIMARY KEY;

ALTER TABLE `settings`
ADD COLUMN `setting_id` INT(12) PRIMARY KEY AUTO_INCREMENT FIRST;

ALTER TABLE `settings`
ADD COLUMN `organisation_id` INT(12) DEFAULT NULL AFTER `shortname`;

INSERT INTO `settings` (`shortname`, `organisation_id`, `value`)
VALUES
  ('export_weighted_grade', NULL, '1'),
  ('export_calculated_grade', NULL, '{\"enabled\":0}');

ALTER TABLE `courses` ADD COLUMN `sync_groups` tinyint(1) DEFAULT NULL AFTER `sync_ldap_courses`;

ALTER TABLE `course_objectives` ADD `objective_start` INT(12) DEFAULT NULL AFTER `objective_details`;
ALTER TABLE `course_objectives` ADD `objective_finish` INT(12) DEFAULT NULL AFTER `objective_start`;
ALTER TABLE `course_objectives` ADD `active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `updated_by`;

INSERT INTO `settings` (`shortname`, `organisation_id`, `value`)
VALUES
  ('course_webpage_assessment_cohorts_count', NULL, '4');

ALTER TABLE `course_audience` ADD `ldap_sync_date` bigint(64) NOT NULL DEFAULT '0' AFTER `cperiod_id`;

CREATE TABLE IF NOT EXISTS `assessment_events` (
  `assessment_event_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) unsigned NOT NULL,
  `event_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`assessment_event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' AFTER `release_until`;
ALTER TABLE `draft_events` ADD `audience_visible` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_duration`;

ALTER TABLE `assessments` ADD `active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `grade_threshold`;

ALTER TABLE `evaluations_lu_questions` ADD COLUMN `question_description` longtext DEFAULT NULL AFTER `question_text`;

ALTER TABLE `quiz_questions` ADD COLUMN `qquestion_group_id` int(12) unsigned DEFAULT NULL AFTER `question_order`;

ALTER TABLE `events` ADD COLUMN `keywords_release_date` bigint(64) DEFAULT '0' AFTER `event_objectives`;
ALTER TABLE `events` ADD COLUMN `keywords_hidden` int(1) DEFAULT '0' AFTER `event_objectives`;

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

ALTER TABLE `assignments` ADD `max_file_uploads` INT(11) NOT NULL DEFAULT '1' AFTER `assignment_uploads`;
ALTER TABLE `assignment_comments` CHANGE `afile_id` `proxy_to_id` INT(12) NOT NULL DEFAULT '0';
ALTER TABLE `assignments` ADD COLUMN `notice_id` int(11) DEFAULT NULL AFTER `assessment_id`;

ALTER TABLE `event_topics` CHANGE `topic_id` `topic_id` INT(12) NOT NULL DEFAULT '0';

UPDATE `settings` SET `value` = '1600' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.6.0' WHERE `shortname` = 'version_entrada';
