ALTER TABLE `assessments` ADD COLUMN `grade_weighting` int(11) NOT NULL default '0' AFTER `numeric_grade_points_total`;

CREATE TABLE `assessment_exceptions` (
  `aexception_id` int(12) NOT NULL auto_increment,
  `assessment_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `grade_weighting` int(11) NOT NULL default '0',
  PRIMARY KEY  (`aexception_id`),
  KEY `proxy_id` (`assessment_id`,`proxy_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `community_discussions` ADD KEY `page_id` (`cdiscussion_id`,`cpage_id`,`community_id`);
ALTER TABLE `community_discussions` ADD KEY `community_id2` (`community_id`,`forum_active`,`cpage_id`,`forum_order`,`forum_title`);


ALTER TABLE `community_discussion_topics` ADD KEY `community_id` (`cdtopic_id`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `cdtopic_parent` (`cdtopic_parent`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `user` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`,`proxy_id`,`release_date`,`release_until`);
ALTER TABLE `community_discussion_topics` ADD KEY `admin` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`);
ALTER TABLE `community_discussion_topics` ADD KEY `post` (`proxy_id`,`community_id`,`cdtopic_id`,`cdtopic_parent`,`topic_active`);
ALTER TABLE `community_discussion_topics` ADD KEY `release` (`proxy_id`,`community_id`,`cdtopic_parent`,`topic_active`,`release_date`);
ALTER TABLE `community_discussion_topics` ADD KEY `community` (`cdtopic_id`,`community_id`);

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