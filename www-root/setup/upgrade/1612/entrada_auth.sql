INSERT INTO `acl_permissions`
(`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) 
VALUES ('masquerade', NULL, 'group:role', 'medtech:admin', 1, 1, 1, 1, 1, NULL);
