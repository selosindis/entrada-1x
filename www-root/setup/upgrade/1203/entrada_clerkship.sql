ALTER TABLE `apartments` ADD keys_firstname VARCHAR(32) AFTER `super_email`;
ALTER TABLE `apartments` ADD keys_lastname VARCHAR(32) AFTER `keys_firstname`;
ALTER TABLE `apartments` ADD keys_phone VARCHAR(32) AFTER `keys_lastname`;
ALTER TABLE `apartments` ADD keys_email VARCHAR(128) AFTER `keys_phone`;