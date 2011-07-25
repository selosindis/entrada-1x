CREATE TABLE IF NOT EXISTS `user_organisation` (
	`id` int(12) NOT NULL AUTO_INCREMENT, 
	`organisation_id` int(3) NOT NULL, 
	`proxy_id` int(12) NOT NULL, 
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;