INSERT INTO `acl_permissions` (`permission_id`, `resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`)
VALUES
	('observerships', NULL, 'role', 'admin', 1, 1, 1, 1, 1, NULL),
	('observerships', NULL, 'role', 'student', 1, 1, 1, 1, 0, NULL);

