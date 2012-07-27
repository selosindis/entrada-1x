ALTER TABLE `attached_quizzes` MODIFY `content_type` enum('event','community_page', 'assessment') NOT NULL DEFAULT 'event' AFTER `aquiz_id`;

UPDATE `settings` SET `value` = '1314' WHERE `shortname` = 'version_db';