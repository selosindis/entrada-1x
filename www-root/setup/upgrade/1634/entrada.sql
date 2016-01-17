ALTER TABLE `community_share_file_versions` MODIFY COLUMN `file_mimetype` varchar(128) NOT NULL;

UPDATE  `community_share_file_versions`
SET `file_mimetype` = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
WHERE `file_mimetype` = 'application/vnd.openxmlformats-officedocument.wordprocessingml.d';

UPDATE  `community_share_file_versions`
SET `file_mimetype` = 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
WHERE `file_mimetype` = 'application/vnd.openxmlformats-officedocument.presentationml.pre';

UPDATE  `community_share_file_versions`
SET `file_mimetype` = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
WHERE `file_mimetype` = 'application/vnd.openxmlformats-officedocument.spreadsheetml.shee';

UPDATE `settings` SET `value` = '1634' WHERE `shortname` = 'version_db';