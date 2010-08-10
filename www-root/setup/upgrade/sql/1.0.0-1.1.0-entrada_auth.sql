
-- Table: acl_permissions

INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'role', 'pcoordinator', 1, NULL, 1, NULL, NULL, 'GradebookOwner');
INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'group:role', 'faculty:admin', 1, NULL, 1, NULL, NULL, 'GradebookOwner');
INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES('gradebook', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, 'GradebookOwner');

-- Table: departments

ALTER TABLE `departments` ADD `parent_id` INT( 12 ) NOT NULL DEFAULT '0' AFTER `entity_id`,
ADD INDEX ( `parent_id` );

ALTER TABLE `departments` ADD `department_active` INT( 1 ) NOT NULL DEFAULT '1' AFTER `department_desc`,
ADD INDEX ( `department_active` );

