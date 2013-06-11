INSERT INTO `categories` (`category_id`, `category_parent`, `category_code`, `category_type`, `category_name`, `category_desc`, `category_min`, `category_max`, `category_buffer`, `category_start`, `category_finish`, `subcategory_strict`, `category_expiry`, `category_status`, `category_order`, `rotation_id`)
VALUES
(1, 0, NULL, 12, 'School of Medicine', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'published', 0, 0),
(2, 0, NULL, 13, 'All Students', NULL, NULL, NULL, NULL, 0, 1924927200, 0, 0, 'published', 0, 0),
(3, 2, NULL, 17, 'Example Stream', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 0, 0),
(4, 3, NULL, 32, 'Pediatrics', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 4, 4),
(5, 3, NULL, 32, 'Obstetrics & Gynecology', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 3, 3),
(6, 3, NULL, 32, 'Perioperative', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 5, 5),
(7, 3, NULL, 32, 'Surgery - Urology', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 7, 7),
(8, 3, NULL, 32, 'Surgery - Orthopedic', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 8, 8),
(9, 3, NULL, 32, 'Family Medicine', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 1, 1),
(10, 3, NULL, 32, 'Psychiatry', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 6, 6),
(11, 3, NULL, 32, 'Medicine', NULL, 9, 9, NULL, 0, 1924927200, 0, 0, 'published', 2, 2),
(12, 3, NULL, 32, 'Integrated', NULL, 2, 2, NULL, 0, 1924927200, 0, 0, 'published', 9, 9);

ALTER TABLE `logbook_mandatory_objectives` ADD COLUMN `grad_year_min` int(11) NOT NULL DEFAULT '2011' AFTER `number_required`;
ALTER TABLE `logbook_mandatory_objectives` ADD COLUMN `grad_year_max` int(11) NOT NULL DEFAULT '0' AFTER `grad_year_min`;

ALTER TABLE `logbook_preferred_procedures` ADD COLUMN `grad_year_min` int(11) NOT NULL DEFAULT '2011' AFTER `number_required`;
ALTER TABLE `logbook_preferred_procedures` ADD COLUMN `grad_year_max` int(11) NOT NULL DEFAULT '0' AFTER `grad_year_min`;

ALTER TABLE `logbook_overdue` ADD COLUMN `procedures_required` int(12) NOT NULL DEFAULT '0' AFTER `logged_completed`;
ALTER TABLE `logbook_overdue` ADD COLUMN `procedures_completed` int(12) NOT NULL DEFAULT '0' AFTER `procedures_required`;

ALTER TABLE `categories` ADD COLUMN `organisation_id` int(12) DEFAULT NULL AFTER `category_order`;

CREATE TABLE `electives` (
  `electives_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `geo_location` varchar(15) NOT NULL DEFAULT 'National',
  `department_id` int(12) NOT NULL,
  `discipline_id` int(11) NOT NULL,
  `sub_discipline` varchar(100) DEFAULT NULL,
  `schools_id` int(11) NOT NULL,
  `other_medical_school` varchar(150) DEFAULT NULL,
  `objective` text NOT NULL,
  `preceptor_prefix` varchar(10) DEFAULT NULL,
  `preceptor_first_name` varchar(50) DEFAULT NULL,
  `preceptor_last_name` varchar(50) NOT NULL,
  `address` varchar(250) NOT NULL,
  `countries_id` int(12) NOT NULL,
  `city` varchar(100) NOT NULL,
  `prov_state` varchar(200) DEFAULT NULL,
  `region_id` int(12) NOT NULL DEFAULT '0',
  `postal_zip_code` varchar(20) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`electives_id`),
  KEY `region_id` (`region_id`),
  KEY `event_id` (`event_id`),
  KEY `department_id` (`department_id`),
  KEY `discipline_id` (`discipline_id`),
  KEY `schools_id` (`schools_id`),
  KEY `countries_id` (`countries_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3151 DEFAULT CHARSET=latin1;

CREATE TABLE `logbook_deficiency_plans` (
  `ldeficiency_plan_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `rotation_id` int(12) NOT NULL DEFAULT '0',
  `plan_body` text,
  `timeline_start` int(12) NOT NULL DEFAULT '0',
  `timeline_finish` int(12) NOT NULL DEFAULT '0',
  `clerk_accepted` int(1) NOT NULL DEFAULT '0',
  `administrator_accepted` int(1) NOT NULL DEFAULT '0',
  `administrator_comments` text,
  `administrator_id` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ldeficiency_plan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `logbook_location_types` (
  `llocation_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lltype_id` int(11) NOT NULL,
  `llocation_id` int(11) NOT NULL,
  PRIMARY KEY (`llocation_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_location_types` (`llocation_type_id`, `lltype_id`, `llocation_id`)
VALUES
(1, 1, 1),
(2, 1, 3),
(3, 2, 6),
(4, 1, 5),
(5, 1, 9),
(6, 2, 2),
(7, 2, 4),
(8, 2, 5),
(9, 2, 7),
(10, 2, 8),
(11, 3, 10),
(12, 3, 11);

CREATE TABLE `logbook_lu_location_types` (
  `lltype_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location_type` varchar(32) DEFAULT NULL,
  `location_type_short` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`lltype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_lu_location_types` (`lltype_id`, `location_type`, `location_type_short`)
VALUES
(1, 'Ambulatory', 'Amb'),
(2, 'Inpatient', 'Inp'),
(3, 'Alternative', 'Alt');

CREATE TABLE `logbook_mandatory_objective_locations` (
  `lmolocation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lmobjective_id` int(11) DEFAULT NULL,
  `lltype_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`lmolocation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_mandatory_objective_locations` (`lmolocation_id`, `lmobjective_id`, `lltype_id`)
VALUES
(1, 98, 1),
(2, 99, 1),
(3, 100, 1),
(4, 101, 1),
(5, 102, 1),
(6, 103, 1),
(7, 104, 1),
(8, 105, 1),
(9, 106, 1),
(10, 107, 1),
(11, 108, 1),
(12, 109, 1),
(13, 110, 1),
(14, 111, 1),
(15, 112, 1),
(16, 113, 1),
(17, 77, 1),
(18, 77, 2),
(19, 78, 1),
(20, 78, 2),
(21, 79, 1),
(22, 79, 2),
(23, 80, 1),
(24, 80, 2),
(25, 81, 1),
(26, 81, 2),
(27, 82, 1),
(28, 82, 2),
(29, 83, 1),
(30, 83, 2),
(31, 84, 1),
(32, 84, 2),
(33, 85, 2),
(34, 86, 1),
(35, 86, 2),
(36, 87, 1),
(37, 87, 2),
(38, 88, 1),
(39, 88, 2),
(40, 89, 1),
(41, 89, 2),
(42, 90, 1),
(43, 90, 2),
(44, 91, 1),
(45, 91, 2),
(46, 92, 1),
(47, 92, 2),
(48, 93, 1),
(49, 93, 2),
(50, 95, 1),
(51, 95, 2),
(52, 97, 1),
(53, 97, 2),
(54, 66, 1),
(55, 67, 1),
(56, 68, 2),
(57, 69, 2),
(58, 70, 2),
(59, 71, 1),
(60, 72, 1),
(61, 73, 1),
(62, 74, 1),
(63, 75, 1),
(64, 76, 1),
(65, 13, 2),
(66, 44, 2),
(67, 49, 1),
(68, 49, 2),
(69, 51, 1),
(70, 37, 1),
(71, 37, 2),
(72, 42, 1),
(73, 42, 2),
(74, 26, 1),
(75, 26, 2),
(76, 12, 1),
(77, 12, 2),
(78, 27, 1),
(79, 27, 2),
(80, 28, 1),
(81, 28, 2),
(82, 30, 1),
(83, 30, 2),
(84, 14, 1),
(85, 15, 1),
(86, 16, 1),
(87, 17, 1),
(88, 17, 2),
(89, 18, 1),
(90, 19, 1),
(91, 19, 2),
(92, 20, 1),
(93, 21, 1),
(94, 22, 1),
(95, 22, 2),
(96, 23, 1),
(97, 23, 2),
(98, 24, 1),
(99, 24, 2),
(100, 25, 1),
(101, 25, 2),
(102, 1, 1),
(103, 2, 1),
(104, 4, 1),
(105, 4, 2),
(106, 5, 1),
(107, 6, 1),
(108, 6, 2),
(109, 7, 1),
(110, 8, 1),
(111, 9, 1),
(112, 9, 2),
(113, 10, 1),
(114, 10, 2),
(115, 11, 1),
(116, 11, 2),
(117, 13, 1);

CREATE TABLE `logbook_preferred_procedure_locations` (
  `lpplocation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lpprocedure_id` int(11) DEFAULT NULL,
  `lltype_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`lpplocation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `logbook_preferred_procedure_locations` (`lpplocation_id`, `lpprocedure_id`, `lltype_id`)
VALUES
(1, 3, 2),
(2, 2, 2),
(3, 7, 1),
(4, 5, 2),
(5, 10, 1),
(6, 8, 1),
(7, 6, 2),
(8, 4, 2),
(9, 17, 2),
(10, 15, 2),
(11, 18, 2),
(12, 16, 2),
(13, 14, 1),
(14, 19, 2),
(15, 26, 2),
(16, 24, 2),
(17, 27, 2),
(18, 29, 2);