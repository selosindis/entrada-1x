INSERT INTO `quizzes_lu_questiontypes` (`questiontype_id`, `questiontype_title`, `questiontype_description`, `questiontype_active`, `questiontype_order`) VALUES
(2, 'Descriptive Text', '', 1, 0),
(3, 'Page Break', '', 1, 0);

ALTER TABLE `ar_peer_reviewed_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_non_peer_reviewed_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_book_chapter_mono` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_poster_reports` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `ar_conference_papers` ADD `visible_on_website` INT(1)  NULL  DEFAULT '0'  AFTER `proxy_id`;
ALTER TABLE `user_departments` ADD `entrada_only` INT(1)  NULL  DEFAULT '0'  AFTER `dep_title`;

UPDATE `notice_audience` SET `audience_type` = 'cohort' WHERE `audience_type` = 'cohorts';
UPDATE `notice_audience` SET `audience_type` = 'student' WHERE `audience_type` = 'students';
UPDATE `notice_audience` SET `audience_type` = 'all:student' WHERE `audience_type` = 'all:students';
UPDATE `notice_audience` SET `audience_type` = 'all' WHERE `audience_type` = 'all:users';

DELETE FROM `notice_audience` WHERE `notice_id` NOT IN (SELECT `notice_id` FROM `notices`);

UPDATE `settings` SET `value` = '1500' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.5.0' WHERE `shortname` = 'version_entrada';