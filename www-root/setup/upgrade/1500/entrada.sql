INSERT INTO `quizzes_lu_questiontypes` (`questiontype_id`, `questiontype_title`, `questiontype_description`, `questiontype_active`, `questiontype_order`) VALUES
(2, 'Descriptive Text', '', 1, 0);
(3, 'Page Break', '', 1, 0);

UPDATE `settings` SET `value` = '1500' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0' WHERE `shortname` = 'version_entrada';