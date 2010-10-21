UPDATE `settings` SET `value` = '1.2.0' WHERE `shortname` = 'version_db';


ALTER TABLE `student_awards_external` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_clineval_comments` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_community_health_and_epidemiology` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_contributions` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_critical_enquiries` ADD COLUMN `comment` varchar(4096) default NULL;
ALTER TABLE `student_research` ADD COLUMN `comment` varchar(4096) default NULL;

ALTER TABLE `student_mspr` ADD COLUMN  `carms_number` int(10) unsigned default NULL;