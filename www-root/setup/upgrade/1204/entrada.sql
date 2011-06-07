<<<<<<< HEAD
UPDATE `settings` SET `value` = '1204' WHERE `shortname` = 'version_db';

CREATE TABLE `pg_eval_response_rates` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `response_type` varchar(20) NOT NULL,
  `completed` int(10) NOT NULL,
  `distributed` int(10) NOT NULL,
  `percent_complete` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `pg_one45_community` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `one45_name` varchar(50) NOT NULL,
  `community_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
=======
CREATE TABLE IF NOT EXISTS `objective_organisation`(
`objective_id` INT(12) NOT NULL, 
`organisation_id` INT(12) NOT NULL, 
KEY `objective_id` (`objective_id`),
KEY `organistion_id` (`organisation_id`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `topic_organisation`(
`topic_id` INT(12) NOT NULL, 
`organisation_id` INT(12) NOT NULL, 
KEY `topic_id` (`topic_id`),
KEY `organisation_id` (`organisation_id`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `objective_organisation` SELECT a.`objective_id`, b.`organisation_id` FROM `global_lu_objectives` AS a JOIN `entrada_auth.organisations` AS b ON 1=1;

ALTER TABLE `event_contacts` ADD `contact_role` VARCHAR(32) AFTER `proxy_id`;
>>>>>>> feat_organisations
