INSERT INTO `evaluations_lu_response_descriptors` (`organisation_id`, `descriptor`, `reportable`, `order`, `updated_date`, `updated_by`, `active`)
VALUES
  (1, 'Opportunities for Growth', 1, 1, 0, 3499, 1),
  (1, 'Developing', 1, 2, 0, 3499, 1),
  (1, 'Achieving', 1, 3, 0, 3499, 1),
  (1, 'Not Applicable', 0, 4, 0, 3499, 1);

UPDATE `settings` SET `value` = '1629' WHERE `shortname` = 'version_db';