INSERT INTO `acl_permissions` (`resource_type`,`resource_value`,`entity_type`,`entity_value`,`app_id`,`create`,`read`,`update`,`delete`,`assertion`)
VALUES
	('gradebook', NULL, 'group', 'student', NULL, NULL, 1, NULL, NULL, NULL);
	
ALTER TABLE `community_discussions` ADD KEY `page_id` (`cdiscussion_id`,`cpage_id`,`community_id`);
ALTER TABLE `community_discussions` ADD KEY `community_id2` (`community_id`,`forum_active`,`cpage_id`,`forum_order`,`forum_title`);


ALTER TABLE `community_discussion_topics` ADD KEY `community_id` (`cdtopic_id`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `cdtopic_parent` (`cdtopic_parent`,`community_id`);
ALTER TABLE `community_discussion_topics` ADD KEY `user` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`,`proxy_id`,`release_date`,`release_until`);
ALTER TABLE `community_discussion_topics` ADD KEY `admin` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`);
ALTER TABLE `community_discussion_topics` ADD KEY `post` (`proxy_id`,`community_id`,`cdtopic_id`,`cdtopic_parent`,`topic_active`);
ALTER TABLE `community_discussion_topics` ADD KEY `release` (`proxy_id`,`community_id`,`cdtopic_parent`,`topic_active`,`release_date`);
ALTER TABLE `community_discussion_topics` ADD KEY `community` (`cdtopic_id`,`community_id`);