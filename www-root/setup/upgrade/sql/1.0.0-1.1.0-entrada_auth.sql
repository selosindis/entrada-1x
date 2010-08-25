
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

-- Table: registered_apps

ALTER TABLE `registered_apps` CHANGE `script_id` `script_id` varchar(32) NOT NULL DEFAULT '0';
ALTER TABLE `registered_apps` CHANGE `script_password` `script_password` varchar(32) NOT NULL DEFAULT '';

-- Table: user_data

ALTER TABLE `user_data` ADD `grad_year` int(11) default NULL, ADD `entry_year` int(11) default NULL;

-- Function: isnumeric

delimiter $$

drop function if exists `isnumeric` $$
create function `isnumeric` (s varchar(255)) returns int
begin
set @match = '^(([0-9+-.$]{1})|([+-]?[$]?[0-9]*(([.]{1}[0-9]*)|([.]?[0-9]+))))$';

return if(s regexp @match, 1, 0);
end $$

delimiter ;

-- Table: user_data

UPDATE `user_data` a, `user_access` b SET a.`grad_year` = b.`role` WHERE a.`id` = b.`user_id` AND b.`group` = "student" AND isnumeric(b.`role`) AND `app_id` = 1;
UPDATE `user_data` SET `entry_year` = `grad_year` - 4 WHERE `grad_year` IS NOT NULL;

-- Function: isnumeric

DROP function `isnumeric`;