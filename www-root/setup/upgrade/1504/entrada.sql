ALTER TABLE `evaluations` ADD COLUMN `evaluation_completions` int(12) NOT NULL DEFAULT '0' AFTER `evaluation_finish`;

UPDATE `evaluations` AS a SET a.`evaluation_completions` = (SELECT COUNT(b.`evaluation_id`) FROM `evaluation_progress` AS b
WHERE b.`progress_value` = 'complete'
AND b.`evaluation_id` = a.`evaluation_id`
GROUP BY b.`evaluation_id`);

UPDATE `settings` SET `value` = '1504' WHERE `shortname` = 'version_db';