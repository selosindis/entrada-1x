ALTER TABLE `reports_aamc_ci` CHANGE `report_start` `collection_start` BIGINT(64)  NOT NULL  DEFAULT '0';
ALTER TABLE `reports_aamc_ci` CHANGE `report_finish` `collection_finish` BIGINT(64)  NOT NULL  DEFAULT '0';
ALTER TABLE `reports_aamc_ci` ADD `report_start` VARCHAR(10)  NOT NULL  DEFAULT ''  AFTER `report_date`;
ALTER TABLE `reports_aamc_ci` ADD `report_finish` VARCHAR(10)  NOT NULL  DEFAULT ''  AFTER `report_start`;
ALTER TABLE `reports_aamc_ci` ADD `report_params` TEXT  NOT NULL  AFTER `report_supporting_link`;

UPDATE `settings` SET `value` = '1605' WHERE `shortname` = 'version_db';