ALTER TABLE `settings`
DROP PRIMARY KEY;

ALTER TABLE `settings`
ADD COLUMN `setting_id` INT(12) PRIMARY KEY AUTO_INCREMENT FIRST;

ALTER TABLE `settings`
ADD COLUMN `organisation_id` INT(12) DEFAULT NULL AFTER `shortname`;

INSERT INTO `settings` (`shortname`, `organisation_id`, `value`)
  VALUES
  ('export_grades', NULL, '{\"enabled\":0}'),
  ('export_weighted_grade', NULL, '1');

UPDATE `settings` SET `value` = '1607' WHERE `shortname` = 'version_db';