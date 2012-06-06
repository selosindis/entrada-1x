ALTER TABLE `user_access` ADD COLUMN organisation_id INT(12);

CREATE TABLE IF NOT EXISTS `groups` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `group_name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `groups` (`group_name`) VALUES
('Student'),
('Alumni'),
('Faculty'),
('Resident'),
('Staff'),
('MedTech'),
('Guest');

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `role_name` VARCHAR(45) NOT NULL ,
  `groups_id` INT NOT NULL ,
  PRIMARY KEY (`id`, `groups_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`role_name`, `groups_id`) VALUES
(2013,1),
(2014,1),
(2015,1),
(2016,1),
('Alumni',2),
('Faculty',3),
('Lecturer',3),
('Director',3),
('Admin',3),
('Resident',4),
('Lecturer',4),
('Staff',5),
('Pcoordinator',5),
('Admin',5),
('Staff',6),
('Admin',6),
('Communityinvite',7);

CREATE  TABLE IF NOT EXISTS `groups_has_organisations` (
  `groups_id` INT NOT NULL ,
  `organisation_id` INT(12) UNSIGNED NOT NULL ,
  PRIMARY KEY (`groups_id`, `organisations_organisation_id`) )
ENGINE = MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `groups_has_organisations` (`organisation_id`, `groups_id`)
VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 5),
(1, 7);