INSERT INTO `quizzes_lu_quiztypes` (`quiztype_code`, `quiztype_title`, `quiztype_description`, `quiztype_active`, `quiztype_order`) VALUES
('hide', 'Hide Quiz Results', 'This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback), and requires either manual release of the results to the students, or use of a Gradebook Assessment to release the resulting score.', 1, 2);

UPDATE `settings` SET `value` = '1507' WHERE `shortname` = 'version_db';