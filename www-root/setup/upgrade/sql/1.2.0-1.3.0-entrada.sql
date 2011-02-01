-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

ALTER TABLE `tasks`
 ADD COLUMN `verification_type` enum('faculty','other','none') NOT NULL default 'none',
 ADD COLUMN `faculty_selection_policy` enum('off','allow','require') NOT NULL default 'allow',
 ADD COLUMN `completion_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
 ADD COLUMN `rejection_comment_policy` enum('no_comments','require_comments','allow_comments') NOT NULL default 'allow_comments',
 ADD COLUMN `verification_notification_policy` smallint(5) unsigned NOT NULL default '0';
   
UPDATE `tasks` SET 
 `verification_type`='faculty'
 WHERE `require_verification`=1;
  
UPDATE `tasks` SET
 `verification_notification_policy`=1;
 
ALTER TABLE `tasks`
 DROP COLUMN `require_verification`;
 

-- --------------------------------------------------------

--
-- Table structure for table `task_associated_faculty`
--

CREATE TABLE IF NOT EXISTS `task_associated_faculty` (
  `task_id` int(12) unsigned NOT NULL,
  `faculty_id` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`task_id`,`faculty_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `task_completion`
--

ALTER TABLE `task_completion`
 ADD COLUMN `faculty_id` int(12) unsigned default NULL,
 ADD COLUMN `completion_comment` text,
 ADD COLUMN `rejection_comment` text,
 ADD COLUMN `rejection_date` bigint(64) default NULL;

-- Cannot make any assumptions about faculty verification versus task owner verification. Leave faculty_id as-is

-- --------------------------------------------------------

--
-- Table structure for table `task_verifiers`
--

CREATE TABLE IF NOT EXISTS `task_verifiers` (
  `task_id` int(12) unsigned NOT NULL,
  `verifier_id` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`task_id`,`verifier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;