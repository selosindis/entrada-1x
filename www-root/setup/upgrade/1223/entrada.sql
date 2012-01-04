ALTER TABLE `ar_lu_degree_types` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1'

UPDATE `settings` SET `value` = '1223' WHERE `shortname` = 'version_db';