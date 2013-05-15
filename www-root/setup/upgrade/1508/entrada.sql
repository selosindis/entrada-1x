INSERT INTO `objective_audience` (`objective_id`, `organisation_id`, `audience_type`, `audience_value`, `updated_date`, `updated_by`)
(
	SELECT a.`objective_id`, b.`organisation_id`, 'COURSE', 'all', 0, 1 FROM `global_lu_objectives` AS a
	JOIN `objective_organisation` AS b
	ON a.`objective_id` = b.`objective_id`
	WHERE a.`objective_parent` = 0
);

UPDATE `settings` SET `value` = '1508' WHERE `shortname` = 'version_db';