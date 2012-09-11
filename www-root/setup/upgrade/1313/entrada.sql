ALTER TABLE `student_observerships` MODIFY `preceptor_prefix` VARCHAR(4) AFTER `end`;
ALTER TABLE `student_observerships` ADD `preceptor_email` VARCHAR(255) AFTER `preceptor_proxy_id`;
ALTER TABLE `student_observerships` ADD `status2` ENUM('UNCONFIRMED','CONFIRMED','REJECTED') DEFAULT 'UNCONFIRMED' AFTER `preceptor_email`;
ALTER TABLE `student_observerships` ADD `unique_id` VARCHAR(64) AFTER `status`;
ALTER TABLE `student_observerships` ADD `notice_sent` INT(11) AFTER `unique_id`;

UPDATE `settings` SET `value` = '1313' WHERE `shortname` = 'version_db';