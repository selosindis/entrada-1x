INSERT INTO `acl_permissions` (`resource_type`,`resource_value`,`entity_type`,`entity_value`,`app_id`,`create`,`read`,`update`,`delete`,`assertion`)
VALUES
	('eventclosed', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'EventEnrollment&NotGuest');

UPDATE `settings` SET `value` = '1401' WHERE `shortname` = 'version_db';
UPDATE `settings` SET `value` = '1.4.0' WHERE `shortname` = 'version_entrada';