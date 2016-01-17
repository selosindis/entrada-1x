ALTER TABLE `assessments_lu_meta` CHANGE `type` `type` ENUM('rating','project','exam','paper','assessment','presentation','quiz','RAT','reflection') CHARACTER SET utf8 NULL DEFAULT NULL;
UPDATE `assessments_lu_meta` SET `type` = 'assessment' WHERE `type` = '';

UPDATE `settings` SET `value` = '1635' WHERE `shortname` = 'version_db';