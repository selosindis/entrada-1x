
-- Table: acl_permissions

ALTER TABLE `acl_permissions` CHANGE `app_id` `app_id` int(12) NULL DEFAULT NULL;

DELETE FROM `acl_permissions` WHERE `resource_type` IN ('objective', 'objectivecontent');

INSERT INTO `acl_permissions` (`resource_type`, `resource_value`, `entity_type`, `entity_value`, `app_id`, `create`, `read`, `update`, `delete`, `assertion`) VALUES
('gradebook', NULL, 'role', 'pcoordinator', 1, NULL, 1, NULL, NULL, 'GradebookOwner'), 
('gradebook', NULL, 'group:role', 'faculty:admin', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
('gradebook', NULL, 'group:role', 'faculty:director', 1, NULL, 1, NULL, NULL, 'GradebookOwner'),
('dashboard', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'NotGuest'),
('regionaled', NULL, 'group', 'resident', '1', NULL, '1', NULL, NULL, 'HasAccommodations'),
('regionaled', NULL, 'group', 'student', '1', NULL, '1', NULL, NULL, 'HasAccommodations'),
('regionaled_tab', NULL, 'group', 'resident', '1', NULL, '1', NULL, NULL, 'HasAccommodations'),
('awards', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('mspr', NULL, 'group:role', 'staff:admin', 1, 1, 1, 1, 1, NULL),
('mspr', NULL, 'group', 'student', 1, NULL, 1, 1, NULL, NULL);

-- Table: departments

ALTER TABLE `departments` ADD `parent_id` INT( 12 ) NOT NULL DEFAULT '0' AFTER `entity_id`,
ADD INDEX ( `parent_id` );

ALTER TABLE `departments` ADD `department_active` INT( 1 ) NOT NULL DEFAULT '1' AFTER `department_desc`,
ADD INDEX ( `department_active` );

-- Table: departments

ALTER TABLE `user_data` ADD `grad_year` int(11) default NULL, `entry_year` int(11) default NULL; 

