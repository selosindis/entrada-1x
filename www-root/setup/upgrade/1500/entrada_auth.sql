ALTER TABLE `user_departments` ADD `entrada_only` INT(1) NULL DEFAULT '0' AFTER `dep_title`;
ALTER TABLE `user_data` ADD `salt` VARCHAR(64) NULL DEFAULT NULL AFTER `password`;