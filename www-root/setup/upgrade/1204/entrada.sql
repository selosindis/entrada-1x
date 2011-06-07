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
