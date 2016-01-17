/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_book_chapter_mono` (
  `book_chapter_mono_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `editor_list` varchar(200) DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `visible_on_website` int(1) DEFAULT '0',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`book_chapter_mono_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_clinical_activity` (
  `clinical_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `description` text NOT NULL,
  `average_hours` int(11) DEFAULT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_clinical_education` (
  `clinical_education_id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(150) NOT NULL DEFAULT '',
  `level_description` varchar(255) DEFAULT NULL,
  `location` varchar(150) NOT NULL DEFAULT '',
  `location_description` varchar(255) DEFAULT NULL,
  `average_hours` int(11) NOT NULL DEFAULT '0',
  `research_percentage` int(1) DEFAULT '0',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_clinical_innovation` (
  `clinical_innovation_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinical_innovation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_clinics` (
  `clinics_id` int(11) NOT NULL AUTO_INCREMENT,
  `clinic` varchar(150) NOT NULL DEFAULT '',
  `patients` int(11) NOT NULL DEFAULT '0',
  `half_days` int(11) NOT NULL DEFAULT '0',
  `new_repeat` varchar(25) NOT NULL DEFAULT '',
  `weeks` int(2) NOT NULL DEFAULT '0',
  `average_clerks` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`clinics_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_conference_papers` (
  `conference_papers_id` int(11) NOT NULL AUTO_INCREMENT,
  `lectures_papers_list` text NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT '',
  `institution` text NOT NULL,
  `location` varchar(250) DEFAULT NULL,
  `countries_id` int(12) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `prov_state` varchar(200) DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT '',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `visible_on_website` int(1) DEFAULT '0',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`conference_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_consults` (
  `consults_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity` varchar(250) NOT NULL DEFAULT '',
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `months` int(2) NOT NULL DEFAULT '0',
  `average_consults` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`consults_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_continuing_education` (
  `continuing_education_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit` varchar(150) NOT NULL DEFAULT '',
  `location` varchar(150) NOT NULL DEFAULT '',
  `average_hours` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) NOT NULL DEFAULT '0',
  `end_year` int(4) NOT NULL DEFAULT '0',
  `total_hours` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`continuing_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_external_contributions` (
  `external_contributions_id` int(11) NOT NULL AUTO_INCREMENT,
  `organisation` varchar(255) NOT NULL DEFAULT '',
  `city_country` text,
  `countries_id` int(12) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `prov_state` varchar(200) DEFAULT NULL,
  `role` varchar(150) DEFAULT NULL,
  `role_description` text,
  `description` text NOT NULL,
  `days_of_year` int(3) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`external_contributions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_graduate_supervision` (
  `graduate_supervision_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `active` varchar(8) NOT NULL DEFAULT '',
  `supervision` varchar(7) NOT NULL DEFAULT '',
  `year_started` int(4) NOT NULL DEFAULT '0',
  `thesis_defended` char(3) NOT NULL DEFAULT '',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`graduate_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_graduate_teaching` (
  `graduate_teaching_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_number` varchar(25) NOT NULL DEFAULT '',
  `course_name` text NOT NULL,
  `assigned` char(3) NOT NULL DEFAULT '',
  `lec_enrollment` int(11) NOT NULL DEFAULT '0',
  `lec_hours` int(11) NOT NULL DEFAULT '0',
  `lab_enrollment` int(11) NOT NULL DEFAULT '0',
  `lab_hours` int(11) NOT NULL DEFAULT '0',
  `tut_enrollment` int(11) NOT NULL DEFAULT '0',
  `tut_hours` int(11) NOT NULL DEFAULT '0',
  `sem_enrollment` int(11) NOT NULL DEFAULT '0',
  `sem_hours` int(11) NOT NULL DEFAULT '0',
  `coord_enrollment` int(11) NOT NULL DEFAULT '0',
  `pbl_hours` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`graduate_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_innovation` (
  `innovation_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_number` varchar(25) DEFAULT NULL,
  `course_name` text,
  `type` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`innovation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_internal_contributions` (
  `internal_contributions_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_type` varchar(150) NOT NULL DEFAULT '',
  `activity_type_description` text,
  `role` varchar(150) NOT NULL DEFAULT '',
  `role_description` text,
  `description` text NOT NULL,
  `time_commitment` int(11) NOT NULL DEFAULT '0',
  `commitment_type` varchar(10) NOT NULL DEFAULT 'week',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) DEFAULT NULL,
  `end_month` int(2) DEFAULT '0',
  `end_year` int(4) DEFAULT '0',
  `meetings_attended` int(3) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`internal_contributions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_activity_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_activity_types` VALUES (1,'Lecture'),(2,'Seminar'),(3,'Workshop'),(4,'Other');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_clinical_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clinical_location` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_clinical_locations` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_conference_paper_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conference_paper_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_conference_paper_types` VALUES (1,'Invited Lecture'),(2,'Invited Conference Paper');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_consult_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consult_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_consult_locations` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_contribution_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_role` varchar(50) NOT NULL,
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_contribution_roles` VALUES (1,'Advisor',1),(2,'Chair',1),(3,'Co-Chair',1),(4,'Consultant',1),(5,'Delegate',1),(6,'Deputy Head',1),(7,'Director',1),(8,'Head',1),(9,'Member',1),(10,'Past President',1),(11,'President',1),(12,'Representative',1),(13,'Secretary',1),(14,'Vice Chair',1),(15,'Vice President',1),(16,'Other (specify)',1),(17,'Site Leader on a Clinical Trial',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_contribution_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contribution_type` varchar(50) NOT NULL,
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_contribution_types` VALUES (1,'Accreditation Committee',1),(2,'Committee (specify)',1),(3,'Council (specify)',1),(4,'Faculty Board',1),(5,'Search Committee (specify)',1),(6,'Senate',1),(7,'Senate Committee (specify)',1),(8,'Subcommittee (specify)',1),(9,'Other (specify)',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_degree_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `degree_type` varchar(50) NOT NULL DEFAULT '',
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_degree_types` VALUES (1,'BA',1),(2,'BSc',1),(3,'BNSc',1),(4,'MA',1),(5,'MD',1),(6,'M ED',1),(7,'MES',1),(8,'MSc',1),(9,'MScOT',1),(10,'MSc OT (Project)',1),(11,'MScPT',1),(12,'MSC PT (Project)',1),(13,'PDF',1),(14,'PhD',1),(15,'Clinical Fellow',1),(16,'Summer Research Student',1),(17,'MPA Candidate',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_education_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `education_location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_education_locations` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_focus_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `focus_group` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`),
  KEY `focus_group` (`focus_group`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_focus_groups` VALUES (1,'Cancer'),(2,'Neurosciences'),(3,'Cardiovascular, Circulatory and Respiratory'),(4,'Gastrointestinal'),(5,'Musculoskeletal\n'),(6,'Health Services Research'),(15,'Other'),(7,'Protein Function and Discovery'),(8,'Reproductive Sciences'),(9,'Genetics'),(10,'Nursing'),(11,'Primary Care Studies'),(12,'Emergency'),(13,'Critical Care'),(14,'Nephrology'),(16,'Educational Research'),(17,'Microbiology and Immunology'),(18,'Urology'),(19,'Psychiatry'),(20,'Anesthesiology'),(22,'Obstetrics and Gynecology'),(23,'Rehabilitation Therapy'),(24,'Occupational Therapy');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_hospital_location` (
  `hosp_id` int(11) NOT NULL DEFAULT '0',
  `hosp_desc` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_hospital_location` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_innovation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `innovation_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_innovation_types` VALUES (1,'Course Design'),(2,'Curriculum Development'),(3,'Educational Materials Development'),(4,'Software Development'),(5,'Educational Planning and Policy Development'),(6,'Development of Innovative Teaching Methods');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_membership_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membership_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_membership_roles` VALUES (1,'Examining Committee'),(2,'Comprehensive Exam Committee'),(3,'Mini Masters'),(4,'Supervisory Committee'),(5,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_on_call_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_call_location` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_on_call_locations` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_other_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `other_location` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_other_locations` VALUES (1,'Hospital A'),(2,'Hospital B'),(3,'Hospital C'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_patent_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patent_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_patent_types` VALUES (1,'License Granted'),(2,'Non-Disclosure Agreement'),(3,'Patent Applied For'),(4,'Patent Obtained');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_pr_roles` (
  `role_id` int(11) NOT NULL DEFAULT '0',
  `role_description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_pr_roles` VALUES (1,'First Author'),(2,'Corresponding Author'),(3,'Contributing Author');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_prize_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_category` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_prize_categories` VALUES (1,'Research'),(2,'Teaching'),(3,'Service');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_prize_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_prize_types` VALUES (1,'Award'),(2,'Honour'),(3,'Prize');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_profile_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_profile_roles` VALUES (1,'Researcher/Scholar'),(2,'Educator/Scholar'),(3,'Clinician/Scholar');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_publication_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publication_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_publication_statuses` VALUES (1,'Accepted'),(2,'In Press'),(3,'Presented'),(4,'Published'),(5,'Submitted');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_publication_type` (
  `type_id` int(11) NOT NULL DEFAULT '0',
  `type_description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`),
  KEY `type_description` (`type_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_publication_type` VALUES (1,'Peer-Reviewed Article'),(2,'Non-Peer-Reviewed Article'),(3,'Chapter'),(4,'Peer-Reviewed Abstract'),(5,'Non-Peer-Reviewed Abstract'),(6,'Complete Book'),(7,'Monograph'),(8,'Editorial'),(9,'Published Conference Proceeding'),(10,'Poster Presentations'),(11,'Technical Report');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_research_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `research_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_research_types` VALUES (1,'Infrastructure'),(2,'Operating'),(3,'Salary'),(4,'Training');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_scholarly_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scholarly_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_scholarly_types` VALUES (1,'Granting Body Referee'),(2,'Journal Editorship'),(3,'Journal Referee'),(4,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_self_education_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `self_education_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_self_education_types` VALUES (1,'Clinical'),(2,'Research'),(3,'Teaching'),(4,'Service/Administrative'),(5,'Other');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_supervision_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervision_type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_supervision_types` VALUES (1,'Joint'),(2,'Sole');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_trainee_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_level` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_trainee_levels` VALUES (1,'Clerk(s)'),(2,'Clinical Fellow(s)'),(3,'International Med. Graduate'),(4,'PGY (specify)'),(5,'Other (specify)');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_lu_undergraduate_supervision_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `undergarduate_supervision_course` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `ar_lu_undergraduate_supervision_courses` VALUES (1,'ANAT-499'),(2,'BCHM-421'),(3,'BCHM-422'),(4,'MICR-499'),(5,'PATH-499'),(6,'PHAR-499'),(7,'PHGY-499'),(8,'NURS-490'),(9,'ANAT499'),(10,'BCHM421'),(11,'BCHM422'),(12,'MICR499'),(13,'PATH499'),(14,'PHAR499'),(15,'PHGY499'),(16,'NURS490'),(17,'ANAT 499'),(18,'BCHM 421'),(19,'BCHM 422'),(20,'MICR 499'),(21,'PATH 499'),(22,'PHAR 499'),(23,'PHGY 499'),(24,'NURS 490');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_memberships` (
  `memberships_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `department` varchar(150) NOT NULL DEFAULT '',
  `university` varchar(255) NOT NULL DEFAULT '',
  `role` varchar(100) NOT NULL DEFAULT '',
  `role_description` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`memberships_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_non_peer_reviewed_papers` (
  `non_peer_reviewed_papers_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `visible_on_website` int(1) DEFAULT '0',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`non_peer_reviewed_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_on_call` (
  `on_call_id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `frequency` varchar(250) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`on_call_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_other` (
  `other_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` text NOT NULL,
  `type` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`other_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_other_activity` (
  `other_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `average_hours` int(11) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`other_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_patent_activity` (
  `patent_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `patent_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`patent_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_peer_reviewed_papers` (
  `peer_reviewed_papers_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `category` varchar(10) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `keywords` text,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `visible_on_website` int(1) DEFAULT '0',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`peer_reviewed_papers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_poster_reports` (
  `poster_reports_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `source` varchar(200) NOT NULL,
  `author_list` varchar(200) NOT NULL,
  `editor_list` varchar(200) DEFAULT NULL,
  `epub_url` text,
  `status_date` varchar(8) DEFAULT NULL,
  `epub_date` varchar(8) NOT NULL,
  `volume` varchar(25) DEFAULT NULL,
  `edition` varchar(25) DEFAULT NULL,
  `pages` varchar(25) DEFAULT NULL,
  `role_id` int(3) NOT NULL,
  `type_id` int(3) NOT NULL,
  `status` varchar(25) NOT NULL,
  `group_id` int(3) DEFAULT NULL,
  `hospital_id` int(3) DEFAULT NULL,
  `pubmed_id` varchar(200) NOT NULL,
  `year_reported` int(4) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `visible_on_website` int(1) DEFAULT '0',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`poster_reports_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_prizes` (
  `prizes_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(150) NOT NULL DEFAULT '',
  `prize_type` varchar(150) DEFAULT NULL,
  `description` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`prizes_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_procedures` (
  `procedures_id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(150) NOT NULL DEFAULT '',
  `site_description` text,
  `average_hours` int(11) DEFAULT NULL,
  `special_features` text NOT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`procedures_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_profile` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `education` float(5,2) NOT NULL DEFAULT '0.00',
  `research` float(5,2) NOT NULL DEFAULT '0.00',
  `clinical` float(5,2) NOT NULL DEFAULT '0.00',
  `combined` float(5,2) NOT NULL DEFAULT '0.00',
  `service` float(5,2) NOT NULL DEFAULT '0.00',
  `total` float(5,2) NOT NULL DEFAULT '0.00',
  `hospital_hours` int(11) NOT NULL DEFAULT '0',
  `on_call_hours` int(11) NOT NULL DEFAULT '0',
  `consistent` char(3) NOT NULL DEFAULT '',
  `consistent_comments` text,
  `career_goals` char(3) NOT NULL DEFAULT '',
  `career_comments` text,
  `roles` text NOT NULL,
  `roles_compatible` char(3) NOT NULL DEFAULT '',
  `roles_comments` text,
  `education_comments` text,
  `research_comments` text,
  `clinical_comments` text,
  `service_comments` text,
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `department` text NOT NULL,
  `cross_department` text,
  `report_completed` char(3) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_research` (
  `research_id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) DEFAULT NULL,
  `grant_title` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(25) DEFAULT NULL,
  `multiinstitutional` varchar(3) DEFAULT NULL,
  `agency` text,
  `role` varchar(50) NOT NULL,
  `principal_investigator` varchar(100) NOT NULL DEFAULT '',
  `co_investigator_list` text,
  `amount_received` decimal(20,2) NOT NULL DEFAULT '0.00',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) DEFAULT '0',
  `end_year` int(4) DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `funding_status` varchar(9) NOT NULL DEFAULT '',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`research_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_scholarly_activity` (
  `scholarly_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `scholarly_activity_type` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `location` varchar(25) DEFAULT NULL,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`scholarly_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_self_education` (
  `self_education_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `activity_type` varchar(150) NOT NULL DEFAULT '',
  `institution` varchar(255) NOT NULL DEFAULT '',
  `start_month` int(2) NOT NULL DEFAULT '0',
  `start_year` int(4) NOT NULL DEFAULT '0',
  `end_month` int(2) NOT NULL DEFAULT '0',
  `end_year` int(4) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`self_education_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_undergraduate_nonmedical_teaching` (
  `undergraduate_nonmedical_teaching_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_number` varchar(25) NOT NULL DEFAULT '',
  `course_name` text NOT NULL,
  `assigned` char(3) NOT NULL DEFAULT '',
  `lec_enrollment` int(11) NOT NULL DEFAULT '0',
  `lec_hours` int(11) NOT NULL DEFAULT '0',
  `lab_enrollment` int(11) NOT NULL DEFAULT '0',
  `lab_hours` int(11) NOT NULL DEFAULT '0',
  `tut_enrollment` int(11) NOT NULL DEFAULT '0',
  `tut_hours` int(11) NOT NULL DEFAULT '0',
  `sem_enrollment` int(11) NOT NULL DEFAULT '0',
  `sem_hours` int(11) NOT NULL DEFAULT '0',
  `coord_enrollment` int(11) NOT NULL DEFAULT '0',
  `pbl_hours` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`undergraduate_nonmedical_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_undergraduate_supervision` (
  `undergraduate_supervision_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(150) NOT NULL DEFAULT '',
  `degree` varchar(25) NOT NULL DEFAULT '',
  `course_number` varchar(25) DEFAULT NULL,
  `supervision` varchar(7) NOT NULL DEFAULT '',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`undergraduate_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_undergraduate_teaching` (
  `undergraduate_teaching_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_number` varchar(25) NOT NULL DEFAULT '',
  `course_name` text NOT NULL,
  `lecture_phase` varchar(6) DEFAULT NULL,
  `assigned` char(3) NOT NULL DEFAULT '',
  `lecture_hours` decimal(20,2) DEFAULT '0.00',
  `lab_hours` decimal(20,2) DEFAULT '0.00',
  `small_group_hours` decimal(20,2) DEFAULT '0.00',
  `patient_contact_session_hours` decimal(20,2) DEFAULT '0.00',
  `symposium_hours` decimal(20,2) DEFAULT '0.00',
  `directed_independant_learning_hours` decimal(20,2) DEFAULT '0.00',
  `review_feedback_session_hours` decimal(20,2) DEFAULT '0.00',
  `examination_hours` decimal(20,2) DEFAULT '0.00',
  `clerkship_seminar_hours` decimal(20,2) DEFAULT '0.00',
  `other_hours` decimal(20,2) DEFAULT '0.00',
  `coord_enrollment` int(11) DEFAULT '0',
  `comments` text,
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`undergraduate_teaching_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ar_ward_supervision` (
  `ward_supervision_id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(150) NOT NULL DEFAULT '',
  `average_patients` int(11) NOT NULL DEFAULT '0',
  `months` int(2) NOT NULL DEFAULT '0',
  `average_clerks` int(11) NOT NULL DEFAULT '0',
  `year_reported` int(4) NOT NULL DEFAULT '0',
  `proxy_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`ward_supervision_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_attached_quizzes` (
  `aaquiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `aquiz_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aaquiz_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `quiz_id` (`aquiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_events` (
  `assessment_event_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) unsigned NOT NULL,
  `event_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`assessment_event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_exceptions` (
  `aexception_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `grade_weighting` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aexception_id`),
  KEY `proxy_id` (`assessment_id`,`proxy_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_grades` (
  `grade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  `threshold_notified` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`grade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_marking_schemes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handler` varchar(255) NOT NULL DEFAULT 'Boolean',
  `description` text NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `assessment_marking_schemes` VALUES (1,'Pass/Fail','Boolean','Enter P for Pass, or F for Fail, in the student mark column.',1),(2,'Percentage','Percentage','Enter a percentage in the student mark column.',1),(3,'Numeric','Numeric','Enter a numeric total in the student mark column.',1),(4,'Complete/Incomplete','IncompleteComplete','Enter C for Complete, or I for Incomplete, in the student mark column.',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_objectives` (
  `aobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(11) DEFAULT NULL,
  `objective_details` text,
  `objective_type` enum('curricular_objective','clinical_presentation') NOT NULL DEFAULT 'curricular_objective',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`aobjective_id`),
  KEY `assessment_id` (`assessment_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_option_values` (
  `aovalue_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `aoption_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `value` varchar(32) DEFAULT '',
  PRIMARY KEY (`aovalue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_options` (
  `aoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(12) NOT NULL DEFAULT '0',
  `option_id` int(12) NOT NULL DEFAULT '0',
  `option_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aoption_id`),
  KEY `assessment_id` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessment_quiz_questions` (
  `aqquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(11) NOT NULL,
  `qquestion_id` int(11) NOT NULL,
  PRIMARY KEY (`aqquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments` (
  `assessment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `cohort` varchar(35) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `marking_scheme_id` int(10) unsigned NOT NULL,
  `numeric_grade_points_total` float unsigned DEFAULT NULL,
  `grade_weighting` float NOT NULL DEFAULT '0',
  `narrative` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `characteristic_id` int(4) NOT NULL,
  `show_learner` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `order` smallint(6) NOT NULL DEFAULT '0',
  `grade_threshold` float NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` bigint(64) NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assessment_id`),
  KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments_lu_meta` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `type` enum('rating','project','exam','paper','assessment','presentation','quiz','RAT','reflection') DEFAULT NULL,
  `title` varchar(60) NOT NULL,
  `description` text,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `assessments_lu_meta` VALUES (1,1,'rating','Faculty, resident or preceptor rating',NULL,1),(2,1,'project','Final project',NULL,1),(3,1,'exam','Final written examination',NULL,1),(4,1,'exam','Laboratory or practical examination (except OSCE/SP)',NULL,1),(5,1,'exam','Midterm examination',NULL,1),(6,1,'exam','NBME subject examination',NULL,1),(7,1,'exam','Oral exam',NULL,1),(8,1,'exam','OSCE/SP examination',NULL,1),(9,1,'paper','Paper',NULL,1),(10,1,'assessment','Peer-assessment',NULL,1),(11,1,'presentation','Presentation',NULL,1),(12,1,'quiz','Quiz',NULL,1),(13,1,'RAT','RAT',NULL,1),(14,1,'reflection','Reflection',NULL,1),(15,1,'assessment','Self-assessment',NULL,1),(16,1,'assessment','Other assessments',NULL,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments_lu_meta_options` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL,
  `active` tinyint(1) unsigned DEFAULT '1',
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `assessments_lu_meta_options` VALUES (1,'Essay questions',1,NULL),(2,'Fill-in, short answer questions',1,NULL),(3,'Multiple-choice, true/false, matching questions',1,NULL),(4,'Problem-solving written exercises',1,NULL),(5,'Track Late Submissions',1,'reflection, project, paper'),(6,'Track Resubmissions',1,'reflection, project, paper');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_comments` (
  `acomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_to_id` int(12) NOT NULL DEFAULT '0',
  `assignment_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acomment_id`),
  KEY `assignment_id` (`assignment_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `afile_id` (`proxy_to_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_contacts` (
  `acontact_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `contact_order` int(11) DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`acontact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_file_versions` (
  `afversion_id` int(11) NOT NULL AUTO_INCREMENT,
  `afile_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `file_mimetype` varchar(64) NOT NULL,
  `file_version` int(5) DEFAULT NULL,
  `file_filename` varchar(128) NOT NULL,
  `file_filesize` int(32) NOT NULL,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afversion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignment_files` (
  `afile_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `proxy_id` int(11) NOT NULL,
  `file_type` varchar(24) NOT NULL DEFAULT 'submission',
  `file_title` varchar(40) NOT NULL,
  `file_description` text,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` int(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`afile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `notice_id` int(11) DEFAULT NULL,
  `assignment_title` varchar(40) NOT NULL,
  `assignment_description` text NOT NULL,
  `assignment_active` int(11) NOT NULL,
  `required` int(1) NOT NULL,
  `due_date` bigint(64) NOT NULL DEFAULT '0',
  `assignment_uploads` int(11) NOT NULL DEFAULT '0',
  `max_file_uploads` int(11) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assignment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attached_quizzes` (
  `aquiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `content_type` enum('event','community_page','assessment') NOT NULL DEFAULT 'event',
  `content_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `require_attendance` int(1) NOT NULL DEFAULT '0',
  `random_order` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `quiz_title` varchar(128) NOT NULL,
  `quiz_notes` longtext NOT NULL,
  `quiztype_id` int(12) NOT NULL DEFAULT '0',
  `quiz_timeout` int(4) NOT NULL DEFAULT '0',
  `quiz_attempts` int(3) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aquiz_id`),
  KEY `content_id` (`content_id`),
  KEY `required` (`required`),
  KEY `timeframe` (`timeframe`),
  KEY `quiztype_id` (`quiztype_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `accesses` (`accesses`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `quiz_timeout` (`quiz_timeout`),
  KEY `quiz_attempts` (`quiz_attempts`),
  KEY `content_id_2` (`content_id`,`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communities` (
  `community_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_parent` int(12) NOT NULL DEFAULT '0',
  `category_id` int(12) NOT NULL DEFAULT '0',
  `community_url` text NOT NULL,
  `octype_id` int(12) NOT NULL DEFAULT '1',
  `community_template` varchar(30) NOT NULL DEFAULT 'default',
  `community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `community_shortname` varchar(32) NOT NULL,
  `community_title` varchar(64) NOT NULL,
  `community_description` text NOT NULL,
  `community_keywords` varchar(255) NOT NULL,
  `community_email` varchar(128) NOT NULL,
  `community_website` text NOT NULL,
  `community_protected` int(1) NOT NULL DEFAULT '1',
  `community_registration` int(1) NOT NULL DEFAULT '1',
  `community_members` text NOT NULL,
  `community_active` int(1) NOT NULL DEFAULT '1',
  `community_opened` bigint(64) NOT NULL DEFAULT '0',
  `community_notifications` int(1) NOT NULL DEFAULT '0',
  `sub_communities` int(1) NOT NULL DEFAULT '0',
  `storage_usage` int(32) NOT NULL DEFAULT '0',
  `storage_max` int(32) NOT NULL DEFAULT '104857600',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`community_id`),
  KEY `sub_communities` (`sub_communities`),
  KEY `community_parent` (`community_parent`,`category_id`,`community_protected`,`community_registration`,`community_opened`,`updated_date`,`updated_by`),
  KEY `community_shortname` (`community_shortname`),
  KEY `max_storage` (`storage_max`),
  KEY `storage_usage` (`storage_usage`),
  KEY `community_active` (`community_active`),
  FULLTEXT KEY `community_title` (`community_title`,`community_description`,`community_keywords`),
  FULLTEXT KEY `community_url` (`community_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communities_categories` (
  `category_id` int(12) NOT NULL AUTO_INCREMENT,
  `category_parent` int(12) NOT NULL DEFAULT '0',
  `category_title` varchar(64) NOT NULL,
  `category_description` text NOT NULL,
  `category_keywords` varchar(255) NOT NULL,
  `category_visible` int(1) NOT NULL DEFAULT '1',
  `category_status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `category_parent` (`category_parent`,`category_keywords`),
  KEY `category_status` (`category_status`),
  FULLTEXT KEY `category_description` (`category_description`,`category_keywords`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `communities_categories` VALUES (1,0,'Official Communities','','',1,0),(2,0,'Other Communities','','',1,0),(4,1,'Administration','A container for official administrative units to reside.','',1,0),(5,1,'Courses, etc.','A container for official course groups and communities to reside.','',1,0),(7,2,'Health & Wellness','','',1,0),(8,2,'Sports & Leisure','','',1,0),(9,2,'Learning & Teaching','','',1,0),(15,2,'Careers in Health Care','','',1,0),(11,2,'Miscellaneous','','',1,0),(12,1,'Committees','','',1,0),(14,2,'Social Responsibility','','',1,0),(16,2,'Cultures & Communities','','',1,0),(17,2,'Business & Finance','','',1,0),(18,2,'Arts & Entertainment','','',1,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communities_modules` (
  `module_id` int(12) NOT NULL AUTO_INCREMENT,
  `module_shortname` varchar(32) NOT NULL,
  `module_version` varchar(8) NOT NULL DEFAULT '1.0.0',
  `module_title` varchar(64) NOT NULL,
  `module_description` text NOT NULL,
  `module_active` int(1) NOT NULL DEFAULT '1',
  `module_visible` int(1) NOT NULL DEFAULT '1',
  `module_permissions` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_id`),
  KEY `module_shortname` (`module_shortname`),
  KEY `module_active` (`module_active`),
  FULLTEXT KEY `module_title` (`module_title`,`module_description`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `communities_modules` VALUES (1,'announcements','1.0.0','Announcements','The Announcements module allows you to post Announcements to your community.',1,1,'a:4:{s:3:\"add\";i:1;s:6:\"delete\";i:1;s:4:\"edit\";i:1;s:5:\"index\";i:0;}',1173116408,1),(2,'discussions','1.0.0','Discussions','The Discussions module is a simple method you can use to host discussions.',1,1,'a:10:{s:9:\"add-forum\";i:1;s:8:\"add-post\";i:0;s:12:\"delete-forum\";i:1;s:11:\"delete-post\";i:0;s:10:\"edit-forum\";i:1;s:9:\"edit-post\";i:0;s:5:\"index\";i:0;s:10:\"reply-post\";i:0;s:10:\"view-forum\";i:0;s:9:\"view-post\";i:0;}',1173116408,1),(3,'galleries','1.0.0','Galleries','The Galleries module allows you to add photo galleries and images to your community.',1,1,'a:13:{s:11:\"add-comment\";i:0;s:11:\"add-gallery\";i:1;s:9:\"add-photo\";i:0;s:10:\"move-photo\";i:0;s:14:\"delete-comment\";i:0;s:14:\"delete-gallery\";i:1;s:12:\"delete-photo\";i:0;s:12:\"edit-comment\";i:0;s:12:\"edit-gallery\";i:1;s:10:\"edit-photo\";i:0;s:5:\"index\";i:0;s:12:\"view-gallery\";i:0;s:10:\"view-photo\";i:0;}',1173116408,1),(4,'shares','1.0.0','Document Sharing','The Document Sharing module gives you the ability to upload and share documents within your community.',1,1,'a:15:{s:11:\"add-comment\";i:0;s:10:\"add-folder\";i:1;s:8:\"add-file\";i:0;s:9:\"move-file\";i:0;s:12:\"add-revision\";i:0;s:14:\"delete-comment\";i:0;s:13:\"delete-folder\";i:1;s:11:\"delete-file\";i:0;s:15:\"delete-revision\";i:0;s:12:\"edit-comment\";i:0;s:11:\"edit-folder\";i:1;s:9:\"edit-file\";i:0;s:5:\"index\";i:0;s:11:\"view-folder\";i:0;s:9:\"view-file\";i:0;}',1173116408,1),(5,'polls','1.0.0','Polling','This module allows communities to create their own polls for everything from adhoc open community polling to individual community member votes.',1,1,'a:10:{s:8:\"add-poll\";i:1;s:12:\"add-question\";i:1;s:13:\"edit-question\";i:1;s:15:\"delete-question\";i:1;s:11:\"delete-poll\";i:1;s:9:\"edit-poll\";i:1;s:9:\"view-poll\";i:0;s:9:\"vote-poll\";i:0;s:5:\"index\";i:0;s:8:\"my-votes\";i:0;}',1216256830,1408),(6,'events','1.0.0','Events','The Events module allows you to post events to your community which will be accessible through iCalendar ics files or viewable in the community.',1,1,'a:4:{s:3:\"add\";i:1;s:6:\"delete\";i:1;s:4:\"edit\";i:1;s:5:\"index\";i:0;}',1225209600,1),(7,'quizzes','1.0.0','Quizzes','This module allows communities to create their own quizzes for summative or formative evaluation.',1,1,'a:4:{s:5:\"index\";i:0;s:7:\"attempt\";i:0;s:7:\"results\";i:0;s:13:\"save-response\";i:0;}',1216256830,1),(8,'mtdtracking','1.0.0','MTD Tracking','The MTD Tracking module allows Program Assistants to enter the weekly schedule for each of their Residents.',0,0,'a:2:{s:4:\"edit\";i:1;s:5:\"index\";i:0;}',1216256830,5440);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communities_most_active` (
  `cmactive_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `activity_order` int(2) NOT NULL,
  PRIMARY KEY (`cmactive_id`,`community_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communities_template_permissions` (
  `ctpermission_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `permission_type` enum('category_id','group') DEFAULT NULL,
  `permission_value` varchar(32) DEFAULT NULL,
  `template` varchar(32) NOT NULL,
  PRIMARY KEY (`ctpermission_id`),
  KEY `permission_index` (`permission_type`,`permission_value`,`template`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `communities_template_permissions` VALUES (1,'','','default'),(2,'group','faculty,staff,medtech','course'),(3,'category_id','5','course'),(4,'group','faculty,staff,medtech','committee'),(5,'category_id','12','committee'),(6,'group','faculty,staff,medtech','learningmodule'),(7,'group','faculty,staff,medtech','virtualpatient'),(9,'category_id','','virtualpatient'),(8,'category_id','','learningmodule');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_announcements` (
  `cannouncement_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `announcement_active` int(1) NOT NULL DEFAULT '1',
  `pending_moderation` int(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `announcement_title` varchar(128) NOT NULL,
  `announcement_description` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cannouncement_id`),
  KEY `community_id` (`community_id`,`proxy_id`,`release_date`,`release_until`,`updated_date`,`updated_by`),
  FULLTEXT KEY `announcement_title` (`announcement_title`,`announcement_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_courses` (
  `community_course_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `course_id` int(12) NOT NULL,
  PRIMARY KEY (`community_course_id`),
  KEY `community_id` (`community_id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_discussion_topics` (
  `cdtopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `cdtopic_parent` int(12) NOT NULL DEFAULT '0',
  `cdiscussion_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `anonymous` int(1) NOT NULL DEFAULT '0',
  `topic_title` varchar(128) NOT NULL DEFAULT '',
  `topic_description` text NOT NULL,
  `topic_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cdtopic_id`),
  KEY `cdiscussion_parent` (`cdtopic_parent`,`community_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `cdiscussion_id` (`cdiscussion_id`),
  KEY `topic_active` (`topic_active`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `community_id` (`cdtopic_id`,`community_id`),
  KEY `cdtopic_parent` (`cdtopic_parent`,`community_id`),
  KEY `user` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`,`proxy_id`,`release_date`,`release_until`),
  KEY `admin` (`cdiscussion_id`,`community_id`,`topic_active`,`cdtopic_parent`),
  KEY `post` (`proxy_id`,`community_id`,`cdtopic_id`,`cdtopic_parent`,`topic_active`),
  KEY `release` (`proxy_id`,`community_id`,`cdtopic_parent`,`topic_active`,`release_date`),
  KEY `community` (`cdtopic_id`,`community_id`),
  FULLTEXT KEY `topic_title` (`topic_title`,`topic_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_discussions` (
  `cdiscussion_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `forum_title` varchar(64) NOT NULL DEFAULT '',
  `forum_description` text NOT NULL,
  `forum_order` int(6) NOT NULL DEFAULT '0',
  `forum_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_post` int(1) NOT NULL DEFAULT '0',
  `allow_public_reply` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_post` int(1) NOT NULL DEFAULT '0',
  `allow_troll_reply` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_post` int(1) NOT NULL DEFAULT '1',
  `allow_member_reply` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cdiscussion_id`),
  KEY `community_id` (`community_id`,`forum_order`,`allow_member_post`,`allow_member_reply`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_post` (`allow_troll_post`),
  KEY `allow_troll_reply` (`allow_troll_reply`),
  KEY `allow_public_post` (`allow_public_post`),
  KEY `allow_public_reply` (`allow_public_reply`),
  KEY `forum_active` (`forum_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `page_id` (`cdiscussion_id`,`cpage_id`,`community_id`),
  KEY `community_id2` (`community_id`,`forum_active`,`cpage_id`,`forum_order`,`forum_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_events` (
  `cevent_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `event_active` int(1) NOT NULL DEFAULT '1',
  `pending_moderation` int(1) NOT NULL DEFAULT '0',
  `event_start` bigint(64) NOT NULL DEFAULT '0',
  `event_finish` bigint(64) NOT NULL DEFAULT '0',
  `event_location` varchar(128) NOT NULL,
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `event_title` varchar(128) NOT NULL,
  `event_description` text NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cevent_id`),
  KEY `community_id` (`community_id`,`cpage_id`,`proxy_id`,`event_start`,`event_finish`,`release_date`,`release_until`,`updated_date`,`updated_by`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_galleries` (
  `cgallery_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `gallery_title` varchar(64) NOT NULL,
  `gallery_description` text NOT NULL,
  `gallery_cgphoto_id` int(12) NOT NULL DEFAULT '0',
  `gallery_order` int(6) NOT NULL DEFAULT '0',
  `gallery_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_upload` int(1) NOT NULL DEFAULT '0',
  `allow_public_comment` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_upload` int(1) NOT NULL DEFAULT '0',
  `allow_troll_comment` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_upload` int(1) NOT NULL DEFAULT '1',
  `allow_member_comment` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgallery_id`),
  KEY `community_id` (`community_id`,`gallery_order`,`allow_member_upload`,`allow_member_comment`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_upload` (`allow_troll_upload`),
  KEY `allow_troll_comments` (`allow_troll_comment`),
  KEY `allow_public_upload` (`allow_public_upload`),
  KEY `allow_public_comments` (`allow_public_comment`),
  KEY `gallery_active` (`gallery_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `gallery_cgphoto_id` (`gallery_cgphoto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_gallery_comments` (
  `cgcomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgphoto_id` int(12) NOT NULL DEFAULT '0',
  `cgallery_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgcomment_id`),
  KEY `cgallery_id` (`cgallery_id`,`community_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `cgphoto_id` (`cgphoto_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_gallery_photos` (
  `cgphoto_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgallery_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `photo_mimetype` varchar(64) NOT NULL,
  `photo_filename` varchar(128) NOT NULL,
  `photo_filesize` int(32) NOT NULL DEFAULT '0',
  `photo_title` varchar(128) NOT NULL,
  `photo_description` text NOT NULL,
  `photo_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgphoto_id`),
  KEY `cgallery_id` (`cgallery_id`,`community_id`,`proxy_id`,`photo_filesize`,`updated_date`,`updated_by`),
  KEY `photo_active` (`photo_active`),
  KEY `release_date` (`release_date`,`release_until`),
  FULLTEXT KEY `photo_title` (`photo_title`,`photo_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_history` (
  `chistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) DEFAULT '0',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `record_parent` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `history_key` varchar(255) DEFAULT NULL,
  `history_message` text NOT NULL,
  `history_display` int(1) NOT NULL DEFAULT '0',
  `history_timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`chistory_id`),
  KEY `community_id` (`community_id`,`history_display`),
  KEY `history_timestamp` (`history_timestamp`),
  KEY `cpage_id` (`cpage_id`),
  KEY `record_id` (`record_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_mailing_list_members` (
  `cmlmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL,
  `email` varchar(64) NOT NULL,
  `member_active` int(1) NOT NULL DEFAULT '0',
  `list_administrator` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmlmember_id`),
  UNIQUE KEY `member_id` (`community_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_mailing_lists` (
  `cmlist_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `list_name` varchar(64) NOT NULL,
  `list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `last_checked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmlist_id`),
  KEY `community_id` (`community_id`,`list_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_members` (
  `cmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `member_joined` bigint(64) NOT NULL DEFAULT '0',
  `member_acl` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmember_id`),
  KEY `community_id` (`community_id`,`proxy_id`,`member_joined`,`member_acl`),
  KEY `member_active` (`member_active`),
  KEY `community_id_2` (`community_id`,`proxy_id`,`member_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_modules` (
  `cmodule_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `module_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmodule_id`),
  KEY `community_id` (`community_id`,`module_id`,`module_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_notifications` (
  `cnotification_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `release_time` bigint(64) NOT NULL DEFAULT '0',
  `community` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL,
  `subject` varchar(128) NOT NULL DEFAULT '',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `author_id` int(12) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  `url` varchar(45) NOT NULL,
  PRIMARY KEY (`cnotification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_notify_members` (
  `cnmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `record_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `notify_type` varchar(32) NOT NULL DEFAULT 'announcement',
  `notify_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cnmember_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_page_navigation` (
  `cpnav_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `nav_page_id` int(11) DEFAULT NULL,
  `show_nav` int(1) NOT NULL DEFAULT '1',
  `nav_title` varchar(100) NOT NULL DEFAULT 'Next',
  `nav_type` enum('next','previous') NOT NULL DEFAULT 'next',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpnav_id`),
  KEY `cpage_id` (`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_page_options` (
  `cpoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL,
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `option_title` varchar(32) NOT NULL,
  `option_value` int(12) NOT NULL DEFAULT '1',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpoption_id`,`community_id`,`cpage_id`),
  KEY `cpage_id` (`cpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_pages` (
  `cpage_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `page_order` int(3) NOT NULL DEFAULT '0',
  `page_type` varchar(16) NOT NULL DEFAULT 'default',
  `menu_title` varchar(48) NOT NULL,
  `page_title` text NOT NULL,
  `page_url` varchar(329) NOT NULL,
  `page_content` longtext NOT NULL,
  `page_active` int(1) NOT NULL DEFAULT '1',
  `page_visible` int(1) NOT NULL DEFAULT '1',
  `allow_member_view` int(1) NOT NULL DEFAULT '1',
  `allow_troll_view` int(1) NOT NULL DEFAULT '1',
  `allow_public_view` int(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpage_id`),
  KEY `cpage_id` (`cpage_id`,`community_id`,`page_url`,`page_active`),
  KEY `community_id` (`community_id`,`parent_id`,`page_url`,`page_active`),
  KEY `page_order` (`page_order`),
  KEY `community_id_2` (`community_id`,`page_url`),
  KEY `community_id_3` (`community_id`,`page_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_permissions` (
  `cpermission_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `module_id` int(12) NOT NULL DEFAULT '0',
  `action` varchar(64) NOT NULL,
  `level` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpermission_id`),
  KEY `community_id` (`community_id`,`module_id`,`action`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_polls` (
  `cpolls_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `poll_title` varchar(64) NOT NULL,
  `poll_description` text NOT NULL,
  `poll_terminology` varchar(32) NOT NULL DEFAULT 'Poll',
  `poll_active` int(1) NOT NULL DEFAULT '1',
  `poll_order` int(6) NOT NULL DEFAULT '0',
  `poll_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_multiple` int(1) NOT NULL DEFAULT '0',
  `number_of_votes` int(4) DEFAULT NULL,
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_vote` int(1) NOT NULL DEFAULT '0',
  `allow_public_results` int(1) NOT NULL DEFAULT '0',
  `allow_public_results_after` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '0',
  `allow_troll_vote` int(1) NOT NULL DEFAULT '0',
  `allow_troll_results` int(1) NOT NULL DEFAULT '0',
  `allow_troll_results_after` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_vote` int(1) NOT NULL DEFAULT '1',
  `allow_member_results` int(1) NOT NULL DEFAULT '0',
  `allow_member_results_after` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpolls_id`),
  KEY `community_id` (`community_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `poll_title` (`poll_title`),
  KEY `poll_notifications` (`poll_notifications`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_multiple` (`allow_multiple`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_member_vote` (`allow_member_vote`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_polls_access` (
  `cpaccess_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpaccess_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_polls_questions` (
  `cpquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `poll_question` text NOT NULL,
  `question_order` int(2) NOT NULL DEFAULT '0',
  `minimum_responses` int(2) NOT NULL DEFAULT '1',
  `maximum_responses` int(2) NOT NULL DEFAULT '1',
  `question_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cpquestion_id`),
  KEY `cpolls_id` (`cpolls_id`),
  KEY `community_id` (`community_id`),
  KEY `cpage_id` (`cpage_id`),
  FULLTEXT KEY `poll_question` (`poll_question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_polls_responses` (
  `cpresponses_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpquestion_id` int(12) NOT NULL,
  `cpolls_id` int(12) NOT NULL DEFAULT '0',
  `response` text NOT NULL,
  `response_index` int(5) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpresponses_id`),
  KEY `cpolls_id` (`cpolls_id`),
  KEY `response_index` (`response_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_polls_results` (
  `cpresults_id` int(12) NOT NULL AUTO_INCREMENT,
  `cpresponses_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cpresults_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_share_comments` (
  `cscomment_id` int(12) NOT NULL AUTO_INCREMENT,
  `csfile_id` int(12) NOT NULL DEFAULT '0',
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `comment_title` varchar(128) NOT NULL,
  `comment_description` text NOT NULL,
  `comment_active` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cscomment_id`),
  KEY `cshare_id` (`cshare_id`,`community_id`,`proxy_id`,`comment_active`,`updated_date`,`updated_by`),
  KEY `csfile_id` (`csfile_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `comment_title` (`comment_title`,`comment_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_share_file_versions` (
  `csfversion_id` int(12) NOT NULL AUTO_INCREMENT,
  `csfile_id` int(12) NOT NULL DEFAULT '0',
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `file_version` int(5) NOT NULL DEFAULT '1',
  `file_mimetype` varchar(128) NOT NULL,
  `file_filename` varchar(128) NOT NULL,
  `file_filesize` int(32) NOT NULL DEFAULT '0',
  `file_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`csfversion_id`),
  KEY `cshare_id` (`csfile_id`,`cshare_id`,`community_id`,`proxy_id`,`file_version`,`updated_date`,`updated_by`),
  KEY `file_active` (`file_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_share_files` (
  `csfile_id` int(12) NOT NULL AUTO_INCREMENT,
  `cshare_id` int(12) NOT NULL DEFAULT '0',
  `community_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `file_title` varchar(128) NOT NULL,
  `file_description` text NOT NULL,
  `file_active` int(1) NOT NULL DEFAULT '1',
  `allow_member_revision` int(1) NOT NULL DEFAULT '0',
  `allow_troll_revision` int(1) NOT NULL DEFAULT '0',
  `access_method` int(1) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `notify` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`csfile_id`),
  KEY `cshare_id` (`cshare_id`,`community_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `file_active` (`file_active`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `allow_member_edit` (`allow_member_revision`,`allow_troll_revision`),
  KEY `access_method` (`access_method`),
  FULLTEXT KEY `file_title` (`file_title`,`file_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_shares` (
  `cshare_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_id` int(12) NOT NULL DEFAULT '0',
  `cpage_id` int(12) NOT NULL DEFAULT '0',
  `folder_title` varchar(64) NOT NULL,
  `folder_description` text NOT NULL,
  `folder_icon` int(3) NOT NULL DEFAULT '1',
  `folder_order` int(6) NOT NULL DEFAULT '0',
  `folder_active` int(1) NOT NULL DEFAULT '1',
  `admin_notifications` int(1) NOT NULL DEFAULT '0',
  `allow_public_read` int(1) NOT NULL DEFAULT '0',
  `allow_public_upload` int(1) NOT NULL DEFAULT '0',
  `allow_public_comment` int(1) NOT NULL DEFAULT '0',
  `allow_troll_read` int(1) NOT NULL DEFAULT '1',
  `allow_troll_upload` int(1) NOT NULL DEFAULT '0',
  `allow_troll_comment` int(1) NOT NULL DEFAULT '0',
  `allow_member_read` int(1) NOT NULL DEFAULT '1',
  `allow_member_upload` int(1) NOT NULL DEFAULT '1',
  `allow_member_comment` int(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cshare_id`),
  KEY `community_id` (`community_id`,`folder_order`,`allow_member_upload`,`allow_member_comment`),
  KEY `release_date` (`release_date`),
  KEY `release_until` (`release_until`),
  KEY `allow_member_read` (`allow_member_read`),
  KEY `allow_public_read` (`allow_public_read`),
  KEY `allow_troll_read` (`allow_troll_read`),
  KEY `allow_troll_upload` (`allow_troll_upload`),
  KEY `allow_troll_comments` (`allow_troll_comment`),
  KEY `allow_public_upload` (`allow_public_upload`),
  KEY `allow_public_comments` (`allow_public_comment`),
  KEY `folder_active` (`folder_active`),
  KEY `admin_notification` (`admin_notifications`),
  KEY `folder_icon` (`folder_icon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_templates` (
  `template_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(60) NOT NULL,
  `template_description` text,
  `organisation_id` int(12) unsigned DEFAULT NULL,
  `group` int(12) unsigned DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `community_templates` VALUES (1,'default','',NULL,NULL,NULL),(2,'committee','',NULL,NULL,NULL),(3,'virtualpatient','',NULL,NULL,NULL),(4,'learningmodule','',NULL,NULL,NULL),(5,'course','',NULL,NULL,NULL);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_type_page_options` (
  `ctpoption_id` int(12) NOT NULL AUTO_INCREMENT,
  `ctpage_id` int(12) NOT NULL,
  `option_title` varchar(32) NOT NULL,
  `option_value` int(12) NOT NULL DEFAULT '1',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ctpoption_id`,`ctpage_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `community_type_page_options` VALUES (1,39,'community_title',1,1,0),(2,49,'community_title',1,1,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_type_pages` (
  `ctpage_id` int(12) NOT NULL AUTO_INCREMENT,
  `type_id` int(12) NOT NULL DEFAULT '0',
  `type_scope` enum('organisation','global') NOT NULL DEFAULT 'global',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `page_order` int(3) NOT NULL DEFAULT '0',
  `page_type` varchar(16) NOT NULL DEFAULT 'default',
  `menu_title` varchar(48) NOT NULL,
  `page_title` text NOT NULL,
  `page_url` varchar(512) DEFAULT NULL,
  `page_content` longtext NOT NULL,
  `page_active` tinyint(1) NOT NULL DEFAULT '1',
  `page_visible` tinyint(1) NOT NULL DEFAULT '1',
  `allow_member_view` tinyint(1) NOT NULL DEFAULT '1',
  `allow_troll_view` tinyint(1) NOT NULL DEFAULT '1',
  `allow_public_view` tinyint(1) NOT NULL DEFAULT '0',
  `lock_page` tinyint(1) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ctpage_id`),
  KEY `type_id` (`type_id`,`type_scope`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `community_type_pages` VALUES (1,1,'global',0,0,'default','Home','Home','','',1,1,1,1,1,1,1362062187,1),(2,1,'global',0,1,'announcements','Announcements','Announcements','announcements','',1,1,1,1,0,0,1362062187,1),(3,1,'global',0,2,'discussions','Discussions','Discussions','discussions','',1,1,1,1,0,0,1362062187,1),(8,1,'global',0,3,'shares','Document Sharing','Document Sharing','shares','',1,1,1,1,0,0,1362062187,1),(4,1,'global',0,4,'events','Events','Events','events','',1,1,1,1,0,0,1362062187,1),(5,1,'global',0,5,'galleries','Galleries','Galleries','galleries','',1,1,1,1,0,0,1362062187,1),(6,1,'global',0,6,'polls','Polling','Polling','polls','',1,1,1,1,0,0,1362062187,1),(7,1,'global',0,7,'quizzes','Quizzes','Quizzes','quizzes','',1,1,1,1,0,0,1362062187,1),(9,2,'global',0,0,'course','Background','Background Information','',' ',1,1,1,0,1,1,1362062187,1),(10,2,'global',0,1,'course','Course Calendar','Course Calendar','course_calendar',' ',1,1,1,0,1,1,1362062187,1),(11,2,'global',0,2,'default','Prerequisites','Prerequisites (Foundational Knowledge)','prerequisites',' ',1,1,1,0,1,1,1362062187,1),(12,2,'global',0,3,'default','Course Aims','Aims of the Course','course_aims',' ',1,1,1,0,1,1,1362062187,1),(13,2,'global',0,4,'course','Learning Objectives','Learning Objectives','objectives',' ',1,1,1,0,1,1,1362062187,1),(14,2,'global',0,5,'course','MCC Presentations','MCC Presentations','mcc_presentations',' ',1,1,1,0,1,1,1362062187,1),(15,2,'global',0,6,'default','Teaching Strategies','Teaching and Learning Strategies','teaching_strategies',' ',1,1,1,0,1,1,1362062187,1),(16,2,'global',0,7,'default','Assessment Strategies','Assessment Strategies','assessment_strategies',' ',1,1,1,0,1,1,1362062187,1),(17,2,'global',0,8,'default','Resources','Resources','resources',' ',1,1,1,0,1,1,1362062187,1),(18,2,'global',0,9,'default','Expectations of Students','What is Expected of Students','expectations_of_students',' ',1,1,1,0,1,1,1362062187,1),(19,2,'global',0,10,'default','Expectations of Faculty','What is Expected of Course Faculty','expectations_of_faculty',' ',1,1,1,0,1,1,1362062187,1),(20,1,'organisation',0,0,'default','Home','Home','','',1,1,1,1,1,1,1362062187,1),(21,1,'organisation',0,0,'announcements','Announcements','Announcements','announcements','',1,1,1,1,0,0,1362062187,1),(22,1,'organisation',0,1,'discussions','Discussions','Discussions','discussions','',1,1,1,1,0,0,1362062187,1),(23,1,'organisation',0,3,'events','Events','Events','events','',1,1,1,1,0,0,1362062187,1),(24,1,'organisation',0,4,'galleries','Galleries','Galleries','galleries','',1,1,1,1,0,0,1362062187,1),(25,1,'organisation',0,5,'polls','Polling','Polling','polls','',1,1,1,1,0,0,1362062187,1),(26,1,'organisation',0,6,'quizzes','Quizzes','Quizzes','quizzes','',1,1,1,1,0,0,1362062187,1),(27,1,'organisation',0,2,'shares','Document Sharing','Document Sharing','shares','',1,1,1,1,0,0,1362062187,1),(28,2,'organisation',0,0,'course','Background','Background Information','',' ',1,1,1,0,1,1,1362062187,1),(29,2,'organisation',0,1,'course','Course Calendar','Course Calendar','course_calendar',' ',1,1,1,0,1,1,1362062187,1),(30,2,'organisation',0,2,'default','Prerequisites','Prerequisites (Foundational Knowledge)','prerequisites',' ',1,1,1,0,1,1,1362062187,1),(31,2,'organisation',0,3,'default','Course Aims','Aims of the Course','course_aims',' ',1,1,1,0,1,1,1362062187,1),(32,2,'organisation',0,4,'course','Learning Objectives','Learning Objectives','objectives',' ',1,1,1,0,1,1,1362062187,1),(33,2,'organisation',0,5,'course','MCC Presentations','MCC Presentations','mcc_presentations',' ',1,1,1,0,1,1,1362062187,1),(34,2,'organisation',0,6,'default','Teaching Strategies','Teaching and Learning Strategies','teaching_strategies',' ',1,1,1,0,1,1,1362062187,1),(35,2,'organisation',0,7,'default','Assessment Strategies','Assessment Strategies','assessment_strategies',' ',1,1,1,0,1,1,1362062187,1),(36,2,'organisation',0,8,'default','Resources','Resources','resources',' ',1,1,1,0,1,1,1362062187,1),(37,2,'organisation',0,9,'default','Expectations of Students','What is Expected of Students','expectations_of_students',' ',1,1,1,0,1,1,1362062187,1),(38,2,'organisation',0,10,'default','Expectations of Faculty','What is Expected of Course Faculty','expectations_of_faculty',' ',1,1,1,0,1,1,1362062187,1),(39,3,'global',0,0,'default','Community Title','Community Title','',' ',1,1,1,1,1,0,0,1),(40,3,'global',0,7,'default','Credits','Credits','credits','',1,1,1,1,1,0,0,1),(41,3,'global',0,4,'default','Formative Assessment','Formative Assessment','formative_assessment','',1,1,1,1,1,0,0,1),(42,3,'global',0,3,'default','Foundational Knowledge','Foundational Knowledge','foundational_knowledge','',1,1,1,1,1,0,0,1),(43,3,'global',0,1,'default','Introduction','Introduction','introduction','',1,1,1,1,1,0,0,1),(44,3,'global',0,2,'default','Objectives','Objectives','objectives','',1,1,1,1,1,0,0,1),(45,3,'global',0,8,'url','Print Version','Print Version','print_version','',1,1,1,1,1,0,0,1),(46,3,'global',0,6,'default','Summary','Summary','summary','',1,1,1,1,1,0,0,1),(47,3,'global',0,5,'default','Test your understanding','Test your understanding','test_your_understanding','',1,1,1,1,1,0,0,1),(49,3,'organisation',0,0,'default','Community Title','Community Title','',' ',1,1,1,1,1,0,0,1),(50,3,'organisation',0,7,'default','Credits','Credits','credits','',1,1,1,1,1,0,0,1),(51,3,'organisation',0,4,'default','Formative Assessment','Formative Assessment','formative_assessment','',1,1,1,1,1,0,0,1),(52,3,'organisation',0,3,'default','Foundational Knowledge','Foundational Knowledge','foundational_knowledge','',1,1,1,1,1,0,0,1),(53,3,'organisation',0,1,'default','Introduction','Introduction','introduction','',1,1,1,1,1,0,0,1),(54,3,'organisation',0,2,'default','Objectives','Objectives','objectives','',1,1,1,1,1,0,0,1),(55,3,'organisation',0,8,'url','Print Version','Print Version','print_version','',1,1,1,1,1,0,0,1),(56,3,'organisation',0,6,'default','Summary','Summary','summary','',1,1,1,1,1,0,0,1),(57,3,'organisation',0,5,'default','Test your understanding','Test your understanding','test_your_understanding','',1,1,1,1,1,0,0,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `community_type_templates` (
  `cttemplate_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(12) unsigned NOT NULL,
  `type_id` int(12) unsigned NOT NULL,
  `type_scope` enum('organisation','global') NOT NULL DEFAULT 'global',
  PRIMARY KEY (`cttemplate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `community_type_templates` VALUES (1,1,1,'global'),(2,2,1,'global'),(3,3,1,'global'),(4,4,1,'global'),(5,5,1,'global'),(6,5,2,'global'),(7,1,1,'organisation'),(8,2,1,'organisation'),(9,3,1,'organisation'),(10,4,1,'organisation'),(11,5,1,'organisation'),(12,5,2,'organisation'),(13,4,3,'global'),(14,3,3,'global'),(15,4,3,'organisation'),(16,3,3,'organisation');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_audience` (
  `caudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `audience_type` enum('proxy_id','group_id') NOT NULL,
  `audience_value` int(11) NOT NULL,
  `cperiod_id` int(11) NOT NULL,
  `ldap_sync_date` bigint(64) NOT NULL DEFAULT '0',
  `enroll_start` bigint(20) NOT NULL,
  `enroll_finish` bigint(20) NOT NULL,
  `audience_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`caudience_id`),
  KEY `course_id` (`course_id`),
  KEY `audience_type` (`audience_type`),
  KEY `audience_value` (`audience_value`),
  KEY `audience_active` (`audience_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_contacts` (
  `contact_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_type` varchar(12) NOT NULL DEFAULT 'director',
  `contact_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`),
  KEY `course_id` (`course_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `contact_type` (`contact_type`),
  KEY `contact_order` (`contact_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_files` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `file_category` varchar(32) NOT NULL DEFAULT 'other',
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_title` varchar(128) NOT NULL,
  `file_notes` longtext NOT NULL,
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  `access_method` int(1) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `required` (`required`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`),
  KEY `access_method` (`access_method`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_group_audience` (
  `cgaudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `entrada_only` int(1) DEFAULT '0',
  `start_date` bigint(64) NOT NULL,
  `finish_date` bigint(64) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`cgaudience_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_group_contacts` (
  `cgcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `cgroup_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cgcontact_id`),
  UNIQUE KEY `event_id_2` (`cgroup_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`cgroup_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_groups` (
  `cgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `group_name` varchar(30) NOT NULL,
  `active` int(1) DEFAULT NULL,
  PRIMARY KEY (`cgroup_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_keywords` (
  `ckeyword_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL,
  `keyword_id` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ckeyword_id`),
  KEY `course_id` (`course_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_links` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `proxify` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `link` text NOT NULL,
  `link_title` varchar(255) NOT NULL,
  `link_notes` text NOT NULL,
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `required` (`required`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL,
  `is_required` int(1) NOT NULL DEFAULT '0',
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lu_reports` (
  `course_report_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_report_title` varchar(250) NOT NULL DEFAULT '',
  `section` varchar(250) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`course_report_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `course_lu_reports` VALUES (1,'Report Card','report-card',1449685603,1),(2,'My Teachers','my-teachers',1449685603,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_objectives` (
  `cobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `importance` int(2) NOT NULL DEFAULT '1',
  `objective_type` enum('event','course') DEFAULT 'course',
  `objective_details` text,
  `objective_start` int(12) DEFAULT NULL,
  `objective_finish` int(12) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cobjective_id`),
  KEY `course_id` (`course_id`),
  KEY `objective_id` (`objective_id`),
  FULLTEXT KEY `ft_objective_details` (`objective_details`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_report_organisations` (
  `crorganisation_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL DEFAULT '0',
  `course_report_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`crorganisation_id`),
  KEY `organisation_id` (`organisation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `course_report_organisations` VALUES (1,1,1,1449685603,1),(2,1,2,1449685603,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_reports` (
  `creport_id` int(12) NOT NULL AUTO_INCREMENT,
  `course_id` int(12) NOT NULL DEFAULT '0',
  `course_report_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`creport_id`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_syllabi` (
  `syllabus_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(12) DEFAULT NULL,
  `syllabus_start` smallint(2) DEFAULT NULL,
  `syllabus_finish` smallint(2) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `repeat` tinyint(1) DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`syllabus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `course_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL DEFAULT '0',
  `curriculum_type_id` int(12) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `pcoord_id` int(12) unsigned NOT NULL DEFAULT '0',
  `evalrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `studrep_id` int(12) unsigned NOT NULL DEFAULT '0',
  `course_name` varchar(85) NOT NULL DEFAULT '',
  `course_code` varchar(16) NOT NULL DEFAULT '',
  `course_description` text,
  `course_objectives` text,
  `course_url` text,
  `course_message` text NOT NULL,
  `permission` enum('open','closed') NOT NULL DEFAULT 'closed',
  `sync_ldap` int(1) NOT NULL DEFAULT '0',
  `sync_ldap_courses` text,
  `sync_groups` tinyint(1) NOT NULL DEFAULT '0',
  `notifications` int(1) NOT NULL DEFAULT '1',
  `course_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`course_id`),
  KEY `notifications` (`notifications`),
  KEY `pcoord_id` (`pcoord_id`),
  KEY `evalrep_id` (`evalrep_id`),
  KEY `studrep_id` (`studrep_id`),
  KEY `parent_id` (`parent_id`),
  KEY `curriculum_type_id` (`curriculum_type_id`),
  KEY `course_code` (`course_code`),
  KEY `course_active` (`course_active`),
  FULLTEXT KEY `course_description` (`course_description`),
  FULLTEXT KEY `course_objectives` (`course_objectives`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_community_notifications` (
  `ccnotification_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `cnotification_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  PRIMARY KEY (`ccnotification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_level_organisation` (
  `cl_org_id` int(12) NOT NULL AUTO_INCREMENT,
  `org_id` int(12) NOT NULL,
  `curriculum_level_id` int(11) NOT NULL,
  PRIMARY KEY (`cl_org_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `curriculum_level_organisation` VALUES (1,1,1),(2,1,2);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_lu_levels` (
  `curriculum_level_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `curriculum_level` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`curriculum_level_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `curriculum_lu_levels` VALUES (1,'Undergraduate'),(2,'Postgraduate');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_lu_types` (
  `curriculum_type_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(12) unsigned NOT NULL DEFAULT '0',
  `curriculum_type_name` varchar(60) NOT NULL,
  `curriculum_type_description` text,
  `curriculum_type_order` int(12) unsigned NOT NULL DEFAULT '0',
  `curriculum_type_active` int(1) unsigned NOT NULL DEFAULT '1',
  `curriculum_level_id` int(12) DEFAULT NULL,
  `updated_date` bigint(64) unsigned NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`curriculum_type_id`),
  KEY `curriculum_type_order` (`curriculum_type_order`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `curriculum_lu_types` VALUES (1,0,'Term 1',NULL,0,1,NULL,1250538588,1),(2,0,'Term 2',NULL,1,1,NULL,1250538588,1),(3,0,'Term 3',NULL,2,1,NULL,1250538588,1),(4,0,'Term 4',NULL,3,1,NULL,1250538588,1),(5,0,'Term 5',NULL,4,1,NULL,1250538588,1),(6,0,'Term 6',NULL,5,1,NULL,1250538588,1),(7,0,'Term 7',NULL,6,1,NULL,1250538588,1),(8,0,'Term 8',NULL,7,1,NULL,1250538588,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_periods` (
  `cperiod_id` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_type_id` int(11) NOT NULL,
  `curriculum_period_title` varchar(200) DEFAULT '',
  `start_date` bigint(64) NOT NULL,
  `finish_date` bigint(64) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cperiod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curriculum_type_organisation` (
  `curriculum_type_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  PRIMARY KEY (`curriculum_type_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `curriculum_type_organisation` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_audience` (
  `daudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `eaudience_id` int(12) NOT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`daudience_id`),
  KEY `eaudience_id` (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`),
  KEY `audience_type` (`audience_type`,`audience_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_contacts` (
  `dcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `econtact_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL DEFAULT '0',
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('teacher','tutor','ta','auditor') NOT NULL,
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dcontact_id`),
  KEY `econtact_id` (`econtact_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_creators` (
  `create_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  PRIMARY KEY (`create_id`),
  KEY `DRAFT` (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_events` (
  `devent_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT NULL,
  `draft_id` int(11) DEFAULT NULL,
  `parent_id` int(12) DEFAULT NULL,
  `event_children` int(12) DEFAULT NULL,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `include_parent_description` tinyint(1) NOT NULL DEFAULT '1',
  `event_goals` text,
  `event_objectives` text,
  `objectives_release_date` bigint(64) DEFAULT '0',
  `event_message` text,
  `include_parent_message` tinyint(1) NOT NULL DEFAULT '1',
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `audience_visible` tinyint(1) NOT NULL DEFAULT '1',
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`devent_id`),
  KEY `event_id` (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  KEY `event_start_3` (`event_start`,`event_finish`,`release_date`,`release_until`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_eventtypes` (
  `deventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eeventtype_id` int(12) DEFAULT NULL,
  `devent_id` int(12) NOT NULL,
  `event_id` int(12) DEFAULT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  `order` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`deventtype_id`),
  KEY `eeventtype_id` (`eeventtype_id`),
  KEY `event_id` (`devent_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draft_options` (
  `draft_id` int(11) NOT NULL,
  `option` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drafts` (
  `draft_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` text,
  `name` text,
  `description` text,
  `created` int(11) DEFAULT NULL,
  `preserve_elements` binary(4) DEFAULT NULL,
  PRIMARY KEY (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('reviewer','tutor','author') NOT NULL DEFAULT 'reviewer',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`evaluation_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`evaluation_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_evaluator_exclusions` (
  `eeexclusion_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eeexclusion_id`),
  UNIQUE KEY `event_id_2` (`evaluation_id`,`proxy_id`),
  KEY `event_id` (`evaluation_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_evaluators` (
  `eevaluator_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `evaluator_type` enum('proxy_id','grad_year','cohort','organisation_id','cgroup_id') NOT NULL DEFAULT 'proxy_id',
  `evaluator_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eevaluator_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_form_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('reviewer','author') NOT NULL DEFAULT 'author',
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`eform_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`eform_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_form_question_objectives` (
  `efqobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `efquestion_id` int(12) NOT NULL,
  `objective_id` int(12) NOT NULL,
  `updated_date` bigint(64) DEFAULT NULL,
  `updated_by` int(12) DEFAULT NULL,
  PRIMARY KEY (`efqobjective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_form_questions` (
  `efquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(121) NOT NULL,
  `equestion_id` int(12) NOT NULL,
  `question_order` tinyint(3) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `send_threshold_notifications` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_forms` (
  `eform_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `target_id` int(12) NOT NULL,
  `form_parent` int(12) NOT NULL,
  `form_title` varchar(64) NOT NULL,
  `form_description` text NOT NULL,
  `form_active` tinyint(1) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eform_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_progress` (
  `eprogress_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `etarget_id` int(12) NOT NULL,
  `target_record_id` int(11) DEFAULT NULL,
  `proxy_id` int(12) NOT NULL,
  `progress_value` enum('inprogress','complete','cancelled') NOT NULL DEFAULT 'inprogress',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eprogress_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_progress_clerkship_events` (
  `epcevent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `preceptor_proxy_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`epcevent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_progress_patient_encounters` (
  `eppencounter_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `encounter_name` varchar(255) DEFAULT NULL,
  `encounter_complexity` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`eppencounter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_question_objectives` (
  `eqobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `equestion_id` int(12) NOT NULL,
  `objective_id` int(12) NOT NULL,
  `updated_date` bigint(64) DEFAULT NULL,
  `updated_by` int(12) DEFAULT NULL,
  PRIMARY KEY (`eqobjective_id`),
  KEY `equestion_id` (`equestion_id`),
  KEY `objective_id` (`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_question_response_descriptors` (
  `eqrdescriptor_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `eqresponse_id` int(12) unsigned NOT NULL,
  `erdescriptor_id` int(12) unsigned NOT NULL,
  PRIMARY KEY (`eqrdescriptor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_requests` (
  `erequest_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `request_expires` bigint(64) NOT NULL DEFAULT '0',
  `request_code` varchar(255) DEFAULT NULL,
  `evaluation_id` int(11) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `target_proxy_id` int(11) DEFAULT NULL,
  `request_created` bigint(64) NOT NULL DEFAULT '0',
  `request_fulfilled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`erequest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_responses` (
  `eresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `eprogress_id` int(12) NOT NULL,
  `eform_id` int(12) NOT NULL,
  `proxy_id` int(12) NOT NULL,
  `efquestion_id` int(12) NOT NULL,
  `eqresponse_id` int(12) NOT NULL,
  `comments` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_rubric_questions` (
  `efrquestion_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `erubric_id` int(11) DEFAULT NULL,
  `equestion_id` int(11) DEFAULT NULL,
  `question_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efrquestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_targets` (
  `etarget_id` int(12) NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(12) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_value` int(12) NOT NULL,
  `target_type` varchar(24) NOT NULL DEFAULT 'course_id',
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etarget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations` (
  `evaluation_id` int(12) NOT NULL AUTO_INCREMENT,
  `eform_id` int(12) NOT NULL,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `evaluation_title` varchar(128) NOT NULL,
  `evaluation_description` text NOT NULL,
  `evaluation_active` tinyint(1) NOT NULL,
  `evaluation_start` bigint(64) NOT NULL,
  `evaluation_finish` bigint(64) NOT NULL,
  `evaluation_completions` int(12) NOT NULL DEFAULT '0',
  `min_submittable` tinyint(3) NOT NULL DEFAULT '1',
  `max_submittable` tinyint(3) NOT NULL DEFAULT '1',
  `evaluation_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `allow_target_review` tinyint(1) NOT NULL DEFAULT '0',
  `allow_target_request` tinyint(1) NOT NULL DEFAULT '0',
  `allow_repeat_targets` tinyint(1) NOT NULL DEFAULT '0',
  `show_comments` tinyint(1) NOT NULL DEFAULT '0',
  `identify_comments` tinyint(1) NOT NULL DEFAULT '0',
  `require_requests` tinyint(1) NOT NULL DEFAULT '0',
  `require_request_code` tinyint(1) NOT NULL DEFAULT '0',
  `request_timeout` bigint(64) NOT NULL DEFAULT '0',
  `threshold_notifications_type` enum('reviewers','tutors','directors','pcoordinators','authors','disabled') NOT NULL DEFAULT 'disabled',
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` bigint(64) NOT NULL,
  PRIMARY KEY (`evaluation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_question_response_criteria` (
  `eqrcriteria_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eqresponse_id` int(11) DEFAULT NULL,
  `criteria_text` text,
  PRIMARY KEY (`eqrcriteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_question_responses` (
  `eqresponse_id` int(12) NOT NULL AUTO_INCREMENT,
  `efresponse_id` int(12) NOT NULL,
  `equestion_id` int(12) NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` tinyint(3) NOT NULL DEFAULT '0',
  `response_is_html` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_passing_level` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eqresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_questions` (
  `equestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `efquestion_id` int(12) NOT NULL DEFAULT '0',
  `question_parent_id` int(12) NOT NULL DEFAULT '0',
  `questiontype_id` int(12) NOT NULL,
  `question_code` varchar(48) DEFAULT NULL,
  `question_text` longtext NOT NULL,
  `question_description` longtext,
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `question_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`equestion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_shortname` varchar(32) NOT NULL,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`questiontype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `evaluations_lu_questiontypes` VALUES (1,'matrix_single','Horizontal Choice Matrix (single response)','The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).',1),(2,'descriptive_text','Descriptive Text','Allows you to add descriptive text information to your evaluation form. This could be instructions or other details relevant to the question or series of questions.',1),(3,'rubric','Rubric','The rating scale allows evaluators to rate each question based on the scale you provide, while also providing a short description of the requirements to meet each level on the scale (i.e. Level 1 to 4 of \\\"Professionalism\\\" for an assignment are qualified with what traits the learner is expected to show to meet each level, and while the same scale is used for \\\"Collaborator\\\", the requirements at each level are defined differently).',1),(4,'free_text','Free Text Comments','Allows the user to be asked for a simple free-text response. This can be used to get additional details about prior questions, or to simply ask for any comments from the evaluator regarding a specific topic.',1),(5,'selectbox','Drop Down (single response)','The dropdown allows evaluators to answer each question by choosing one of up to 100 options which have been provided to populate a select box.',1),(6,'vertical_matrix','Vertical Choice Matrix (single response)','The rating scale allows evaluators to rate each question based on the scale you provide (i.e. 1 = Not Demonstrated, 2 = Needs Improvement, 3 = Satisfactory, 4 = Above Average).',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_response_descriptors` (
  `erdescriptor_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) unsigned NOT NULL,
  `descriptor` varchar(256) NOT NULL DEFAULT '',
  `reportable` tinyint(1) NOT NULL DEFAULT '1',
  `order` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`erdescriptor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `evaluations_lu_response_descriptors` VALUES (1,1,'Opportunities for Growth',1,1,1449685604,1,1),(2,1,'Developing',1,2,1449685604,1,1),(3,1,'Achieving',1,3,1449685604,1,1),(4,1,'Not Applicable',0,4,1449685604,1,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_rubrics` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rubric_title` varchar(32) DEFAULT NULL,
  `rubric_description` text,
  `efrubric_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`erubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_lu_targets` (
  `target_id` int(11) NOT NULL AUTO_INCREMENT,
  `target_shortname` varchar(32) NOT NULL,
  `target_title` varchar(64) NOT NULL,
  `target_description` text NOT NULL,
  `target_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`target_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `evaluations_lu_targets` VALUES (1,'course','Course Evaluation','',1),(2,'teacher','Teacher Evaluation','',1),(3,'student','Student Assessment','',1),(4,'rotation_core','Clerkship Core Rotation Evaluation','',1),(5,'rotation_elective','Clerkship Elective Rotation Evaluation','',1),(6,'preceptor','Clerkship Preceptor Evaluation','',1),(7,'peer','Peer Assessment','',1),(8,'self','Self Assessment','',1),(9,'resident','Patient Encounter Assessment','',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations_related_questions` (
  `erubric_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `related_equestion_id` int(11) unsigned NOT NULL,
  `equestion_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`erubric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_attendance` (
  `eattendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`eattendance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_audience` (
  `eaudience_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `audience_type` enum('proxy_id','grad_year','cohort','organisation_id','group_id','course_id') NOT NULL,
  `audience_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eaudience_id`),
  KEY `event_id` (`event_id`),
  KEY `target_value` (`audience_value`),
  KEY `target_type` (`audience_type`),
  KEY `event_id_2` (`event_id`,`audience_type`,`audience_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_contacts` (
  `econtact_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `contact_role` enum('teacher','tutor','ta','auditor') NOT NULL,
  `contact_order` int(6) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`econtact_id`),
  UNIQUE KEY `event_id_2` (`event_id`,`proxy_id`),
  KEY `contact_order` (`contact_order`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_discussions` (
  `ediscussion_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `parent_id` int(12) NOT NULL DEFAULT '0',
  `discussion_title` varchar(128) NOT NULL,
  `discussion_comment` text NOT NULL,
  `discussion_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ediscussion_id`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `parent_id` (`parent_id`),
  FULLTEXT KEY `discussion_title` (`discussion_title`,`discussion_comment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_eventtypes` (
  `eeventtype_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `eventtype_id` int(12) NOT NULL,
  `duration` int(12) NOT NULL,
  PRIMARY KEY (`eeventtype_id`),
  KEY `event_id` (`event_id`),
  KEY `eventtype_id` (`eventtype_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_files` (
  `efile_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `file_category` varchar(32) NOT NULL DEFAULT 'other',
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_title` varchar(128) NOT NULL,
  `file_notes` longtext NOT NULL,
  `access_method` int(1) NOT NULL DEFAULT '0',
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`efile_id`),
  KEY `required` (`required`),
  KEY `access_method` (`access_method`),
  KEY `event_id` (`event_id`),
  KEY `release_date` (`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_history` (
  `ehistory_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `history_message` text NOT NULL,
  `history_display` int(1) NOT NULL DEFAULT '0',
  `history_timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ehistory_id`),
  KEY `history_timestamp` (`history_timestamp`),
  KEY `event_id` (`event_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_keywords` (
  `ekeyword_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `keyword_id` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`ekeyword_id`),
  KEY `event_id` (`event_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_links` (
  `elink_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `required` int(1) NOT NULL DEFAULT '0',
  `timeframe` varchar(64) NOT NULL DEFAULT 'none',
  `proxify` int(1) NOT NULL DEFAULT '0',
  `link` text NOT NULL,
  `link_title` varchar(255) NOT NULL,
  `link_notes` text NOT NULL,
  `accesses` int(12) NOT NULL DEFAULT '0',
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`elink_id`),
  KEY `lecture_id` (`event_id`),
  KEY `required` (`required`),
  KEY `release_date` (`release_date`,`release_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_lti_consumers` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `required` int(1) NOT NULL,
  `valid_from` bigint(64) NOT NULL,
  `valid_until` bigint(64) NOT NULL,
  `timeframe` varchar(64) NOT NULL,
  `launch_url` text NOT NULL,
  `lti_key` varchar(300) NOT NULL,
  `lti_secret` varchar(300) NOT NULL,
  `lti_params` text NOT NULL,
  `lti_title` varchar(300) NOT NULL,
  `lti_notes` text NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_lu_resource_types` (
  `event_resource_type_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(100) DEFAULT NULL,
  `description` text,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `event_lu_resource_types` VALUES (1,'Podcast','Attach a podcast to this learning event.',1449685604,1,1),(2,'Bring to Class','Attach a description of materials students should bring to class.',1449685604,1,0),(3,'Link','Attach links to external websites that relate to the learning event.',1449685604,1,1),(4,'Homework','Attach a description to indicate homework tasks assigned to students.',1449685604,1,0),(5,'Lecture Notes','Attach files such as documents, pdfs or images.',1449685604,1,1),(6,'Lecture Slides','Attach files such as documents, powerpoint files, pdfs or images.',1449685604,1,1),(7,'Online Learning Module','Attach links to external learning modules.',1449685604,1,1),(8,'Quiz','Attach an existing quiz to this learning event.',1449685604,1,1),(9,'Textbook Reading','Attach a reading list related to this learning event.',1449685604,1,0),(10,'LTI Provider','',1449685604,1,0),(11,'Other Files','Attach miscellaneous media files to this learning event.',1449685604,1,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_objectives` (
  `eobjective_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `objective_id` int(12) NOT NULL DEFAULT '0',
  `objective_details` text,
  `objective_type` enum('event','course') NOT NULL DEFAULT 'event',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eobjective_id`),
  KEY `event_id` (`event_id`),
  KEY `objective_id` (`objective_id`),
  FULLTEXT KEY `ft_objective_details` (`objective_details`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_related` (
  `erelated_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `related_type` enum('event_id') NOT NULL DEFAULT 'event_id',
  `related_value` varchar(16) NOT NULL,
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`erelated_id`),
  KEY `event_id` (`event_id`),
  KEY `related_type` (`related_type`),
  KEY `related_value` (`related_value`),
  KEY `event_id_2` (`event_id`,`related_type`,`related_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_resource_class_work` (
  `event_resource_class_work_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_class_work` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_class_work_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_resource_entities` (
  `event_resource_entity_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `entity_type` int(12) NOT NULL,
  `entity_value` int(12) NOT NULL,
  `release_date` bigint(64) NOT NULL DEFAULT '0',
  `release_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_resource_homework` (
  `event_resource_homework_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_homework` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_homework_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_resource_textbook_reading` (
  `event_resource_textbook_reading_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL,
  `resource_textbook_reading` text,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `timeframe` enum('none','pre','during','post') NOT NULL DEFAULT 'none',
  `release_required` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` int(12) NOT NULL DEFAULT '0',
  `release_until` int(12) NOT NULL DEFAULT '0',
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_resource_textbook_reading_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_resources` (
  `event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_resource_id` int(11) NOT NULL,
  `fk_event_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_topics` (
  `etopic_id` int(12) NOT NULL AUTO_INCREMENT,
  `event_id` int(12) NOT NULL DEFAULT '0',
  `topic_id` int(12) NOT NULL DEFAULT '0',
  `topic_coverage` enum('major','minor') NOT NULL,
  `topic_time` varchar(25) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`etopic_id`),
  KEY `event_id` (`event_id`),
  KEY `topic_id` (`topic_id`),
  KEY `topic_coverage` (`topic_coverage`),
  KEY `topic_time` (`topic_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `event_id` int(12) NOT NULL AUTO_INCREMENT,
  `parent_id` int(12) DEFAULT NULL,
  `event_children` int(12) DEFAULT NULL,
  `recurring_id` int(12) DEFAULT '0',
  `region_id` int(12) DEFAULT '0',
  `course_id` int(12) NOT NULL DEFAULT '0',
  `event_phase` varchar(12) DEFAULT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text,
  `include_parent_description` tinyint(1) NOT NULL DEFAULT '1',
  `event_goals` text,
  `event_objectives` text,
  `keywords_hidden` int(1) DEFAULT '0',
  `keywords_release_date` bigint(64) DEFAULT '0',
  `objectives_release_date` bigint(64) DEFAULT '0',
  `event_message` text,
  `include_parent_message` tinyint(1) NOT NULL DEFAULT '1',
  `event_location` varchar(64) DEFAULT NULL,
  `event_start` bigint(64) NOT NULL,
  `event_finish` bigint(64) NOT NULL,
  `event_duration` int(64) NOT NULL,
  `release_date` bigint(64) NOT NULL,
  `release_until` bigint(64) NOT NULL,
  `audience_visible` tinyint(1) NOT NULL DEFAULT '1',
  `draft_id` int(11) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `course_id` (`course_id`),
  KEY `region_id` (`region_id`),
  KEY `recurring_id` (`recurring_id`),
  KEY `release_date` (`release_date`,`release_until`),
  KEY `event_start` (`event_start`,`event_duration`),
  KEY `event_start_2` (`event_start`,`event_finish`),
  KEY `event_phase` (`event_phase`),
  FULLTEXT KEY `event_title` (`event_title`,`event_description`,`event_goals`,`event_objectives`,`event_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_lu_eventtypes` (
  `eventtype_id` int(12) NOT NULL AUTO_INCREMENT,
  `eventtype_title` varchar(32) NOT NULL,
  `eventtype_description` text NOT NULL,
  `eventtype_active` int(1) NOT NULL DEFAULT '1',
  `eventtype_order` int(6) NOT NULL,
  `eventtype_default_enrollment` varchar(50) DEFAULT NULL,
  `eventtype_report_calculation` varchar(100) DEFAULT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`eventtype_id`),
  KEY `eventtype_order` (`eventtype_order`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `events_lu_eventtypes` VALUES (1,'Lecture','Faculty member speaks to a whole group of students for the session. Ideally, the lecture is interactive, with brief student activities to apply learning within the talk or presentation. The focus, however, is on the faculty member speaking or presenting to a group of students.',1,0,NULL,NULL,1250877835,1),(2,'Lab','In this session, practical learning, activity and demonstration take place, usually with specialized equipment, materials or methods and related to a class, or unit of teaching.',1,1,NULL,NULL,1250877835,1),(3,'Small Group','In the session, students in small groups work on specific questions, problems, or tasks related to a topic or a case, using discussion and investigation. Faculty member facilitates. May occur in:\r\n<ul>\r\n<li><strong>Expanded Clinical Skills:</strong> demonstrations and practice of clinical approaches and assessments occur with students in small groups of 25 or fewer.</li>\r\n<li><strong>Team Based Learning Method:</strong> students are in pre-selected groups for the term to work on directed activities, often case-based. One-two faculty facilitate with all 100 students in small teams.</li>\r\n<li><strong>Peer Instruction:</strong> students work in partners on specific application activities throughout the session.</li>\r\n<li><strong>Seminars:</strong> Students are in small groups each with a faculty tutor or mentor to facilitate or coach each small group. Students are active in these groups, either sharing new information, working on tasks, cases, or problems. etc. This may include Problem Based Learning as a strategy where students research and explore aspects to solve issues raised by the case with faculty facilitating. Tutorials may also be incorporated here.</li>\r\n<li><strong>Clinical Skills:</strong> Students in the Clinical and Communication Skills courses work in small groups on specific tasks that allow application of clinical skills.</li>\r\n</ul>',1,2,NULL,NULL,1219434863,1),(4,'Patient Contact Session','The focus of the session is on the patient(s) who will be present to answer students\' and/or professor\'s questions and/or to offer a narrative about their life with a condition, or as a member of a specific population. Medical Science Rounds are one example.',1,4,NULL,NULL,1219434863,1),(5,'Symposium / Student Presentation','For one or more hours, a variety of speakers, including students, present on topics to teach about current issues, research, etc.',1,6,NULL,NULL,1219434863,1),(6,'Directed Independent Learning','Students work independently (in groups or on their own) outside of class sessions on specific tasks to acquire knowledge, and develop enquiry and critical evaluation skills, with time allocated into the timetable. Directed Independent Student Learning may include learning through interactive online modules, online quizzes, working on larger independent projects (such as Community Based Projects or Critical Enquiry), or completing reflective, research or other types of papers and reports. While much student independent learning is done on the students own time, for homework, in this case, directed student time is built into the timetable as a specific session and linked directly to other learning in the course.',1,3,NULL,NULL,1219434863,1),(7,'Review / Feedback Session','In this session faculty help students to prepare for future learning and assessment through de-briefing about previous learning in a quiz or assignment, through reviewing a week or more of learning, or through reviewing at the end of a course to prepare for summative examination.',1,5,NULL,NULL,1219434863,1),(8,'Examination','Scheduled course examination time, including mid-term as well as final examinations. <strong>Please Note:</strong> These will be identified only by the Curricular Coordinators in the timetable.',1,7,NULL,NULL,1219434863,1),(9,'Clerkship Seminars','Case-based, small-group sessions emphasizing more advanced and integrative topics. Students draw upon their clerkship experience with patients and healthcare teams to participate and interact with the faculty whose role is to facilitate the discussion.',1,8,NULL,NULL,1250878869,1),(10,'Other','These are sessions that are not a part of the UGME curriculum but are recorded in MEdTech Central. Examples may be: Course Evaluation sessions, MD Management. NOTE: these will be identified only by the Curricular Coordinators in the timetable.',1,9,NULL,NULL,1250878869,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_lu_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(11) DEFAULT '0',
  `resource` varchar(250) NOT NULL DEFAULT '',
  `description` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_lu_topics` (
  `topic_id` int(12) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(60) NOT NULL,
  `topic_description` text,
  `topic_type` enum('ed10','ed11','other') NOT NULL DEFAULT 'other',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_recurring` (
  `recurring_id` int(12) NOT NULL AUTO_INCREMENT,
  `recurring_date` bigint(64) NOT NULL,
  `recurring_until` bigint(64) NOT NULL,
  `recurring_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `recurring_frequency` int(12) NOT NULL,
  `recurring_number` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventtype_organisation` (
  `eventtype_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `eventtype_organisation` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filetypes` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ext` varchar(8) NOT NULL,
  `mime` varchar(64) NOT NULL,
  `english` varchar(64) NOT NULL,
  `image` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `filetypes` VALUES (1,'pdf','application/pdf','PDF Document','GIF89a\0\0Ê\0\0f\0\0¿¿≈ééíÇÇÇÎ\nfff‡~Ä’éìﬁﬁﬂˇfføDF◊…ŒˇKN¥¥¥Áqq°«≠≤ââãÔÔ’’÷ÿÎûü“åèÙ)+˛vy££ß˝©©ô\0\0ÃÃÃ¬ÉÜ˛¬√ÊÊËœ®≠Ó∞∞ôôôå\0˜˜˜˙rt•ˇ!%È≠Ø‚ÇÇÌ¡¬Ïß©pptÈ‘÷ˇ~ÄÍß®ººøÍìîË∆»¬¡«•˝ºæ·‡„ÈÈÏÎááÙ´´¨œœ‘‰\nˇ+.ˇˇˇúú°ˇOQˇôôÈçé∂∂∫÷÷‹ÊwxˇÃÃÎµµüˇÅÇﬂ√∆Íµ∂ÈÃÕÜÜãÏÊÁ„‡‚ƒƒ≈◊ìñˇprˇz|§Ω≈≈ÊﬁÊˇ),••≠ÁááÎ¬ƒÊ÷ﬁˇˇ33\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0>\0,\0\0\0\0\0\0\0—Ä>Ç>\rÜáà\rÉ>D$7PC?ì?CãZ-ù,äÉ;(Y[\"?Mó§72BP03ã7+GG/E Pã;ø781GLJ;…É6!*D‘Ω67N77ãµ66)6D30\n&˚44TTF\0X`HÉWíúp1H	FH˙—\0AÇ.IÇ`‡ÇA√t¯ ±√	§\0I“√ÉH∞ê‰°fç(r∞–A«ÑE§8pÿA¥÷¢@\0;'),(2,'gif','image/gif','GIF Image','GIF89a\0\0Ê\0\0#EÀ÷‹∏eôôôÇÇÇõp]±∏‹ÄòœππøOHL]Ö∑√π≠Nq¡2_î∑¸åããCWâµ§äˇˇˇB67fkzêö≥ﬁﬁﬁÃÃÃzúÚoé‹§àhpfcµµµ±úÉÆÆÆ˜˜˜wúËctúÆæ·¿¬ƒ) |vx∆«‘wÇù®®®µ≠≠ä¨˜ËËËØ¢å3Bh†•±ΩΩΩ≈…ÂôôÃdNP`s¨÷÷÷¡ª¥œΩßôªˆ••≠∏ø…L><€›È≈≈≈ΩΩ≈}}}≈—„ß°¥\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0≠ÄÖÜáÇÇ5Ç,êÇ åäî)? ìäî=)=ùîãù,ùØ©†ù0<0∏±5 5\n=,,ü5¨%&\')ëï=#\r.;21	âä$+3(\0$0üê@8A!4/	Ï5-\Z\">\\`áÅÅá\r7t ò\0‚ÿß5zHÏa„C√OÇ ÷®	∆áXä.àYc ∆@\0;'),(3,'jpg','image/jpeg','JPEG Image','GIF89a\0\0’\0\0#EÃÃÃÆÆÆåååCWâﬁﬁﬁpfcoé‹∏ø…˜˜˜ÇÇÇÆæ·3Bhêö≥±úÉõp]ä¨˜ctú¡º¥2_≈—„OHL]Ö∑B67ôôôNq¡L><§àhwúË√π≠ˇˇˇfkz) µµµ∆«‘ôôÃ÷÷÷wÇùÄòœ|vx≈≈≈∏eôªˆÁÁÁ†•±£££dNPØ¢å`s¨≈…Âî∑¸À÷‹œΩßzúÚ¿¡≈±∏‹€›ÈΩΩΩµµΩµ≠≠ß°¥µ§ä¿¬ƒ!˘\0\0,\0\0\0\0\0\0\0£¿œG§(\Zè\"°∞D˘)?âh¢Ù§fâå.Â L´ŸÅ8%∞æ “45¯È(∫\0ÖöéÍr¢7eO_&\n%8),,U\" !(#eâi)\Z $2:IJ?6/*&\r\07:O+=1-†B%0>436Æ5\'PîJ)%)’)	—“%›Ø:}O‰Â%«OA\0;'),(4,'zip','application/x-zip-compressed','Zip File','GIF89a\0\0ƒ\0\0ÊÊÊÃÃÃµ∂µççç{{{fff˜˜˜???÷÷÷≠≠≠ˇˇˇÔÔÔ]]]≈≈≈›››ôôô333uuuΩΩΩÖÖÖôôô\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0É‡≤Siûé(&´‚Lè  ‚CLîÙ«º.áGBêLxæã«l&Ãƒƒ¡à™î<∆—`\nÆ–√qtåØJê∏ËÄZ$@ßÊÄ>µ∂S\rz	Wu\nèÜzâã	ñW\\zäWöâ\ré°¢´\0ü¢!\0;'),(5,'exe','application/octet-stream','Windows Executable','GIF89a\0\0≥\0\0ôôôÔÔÔﬁﬁﬁÃÃÃµµµˇˇˇ˜˜˜ÊÊÊ’’’ΩΩΩôôô\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0S∞î™ΩIJ°ªÊüß∏ydj`Í\ZH\'ºo¸—i`oo‡áãÙ˚É¢h íö%3¯ÃDÅ«„†BX!≤G°\0Û|≥NBŸvB\rX@?à\"\0;'),(6,'html','text/html','HTML Document','GIF89a\0\0Ê\0\0.IÊÊÊª√≠}}}Äø5fô3÷÷÷ˇˇˇEÉBÄ¢VÃÃÃ¢¢¢ΩΩΩΩÓtHÇWè„.ÔÔÔ!VNoª:ôôôçﬁ,ôôôMavvvç∑Vﬁﬁﬁµµµq®.û‰8˜˜˜óœ@9rJ|†ãƒƒƒMT”€ á±j)aDÑ÷1{∏4QàE´´´^£8´ÁL[àaîΩY8Xà≠Z\"ZW%Wlµ≈≠ô„3¬Àµà«9H9ë·/}∏=∆Œæd™8t´1æ5ø»≤_ú<Ç•XîÊ1é∏Xå≠Z\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0ˇ\0,\0\0\0\0\0\0\0©ÄÖÜáÇÇÇ\n\nìäå--ïäí48%*75=°ñA06\0:¢ã>1\"<∫ó\'$ ,.&\'≈Æ/+\r\r/!ñ#;33(9âä#?	Ë!)ﬁÌ¯π)¸˜ˇˇ∏0¡òÄŸÉ†¿“ÅÉˇ1X– !Dâ)R[IAàè\0;'),(7,'mpg','video/mpeg','MPEG Movie','GIF89a\0\0’\0\0sss«”‹Æ∑¡>ÜƒÊÊÊôôôWí≈¡Ã‘˜˜˜Jôÿôπ–wéßÿ⁄‹tî±)ã‡µ¬À≠≠≠ÖÖÖM≠ÌæææÖß¿ˇˇˇååå2í„Æ≈’÷÷÷§§§=ìÿÃÃÃæ∆Õ√√√Bïﬁßø“Kõ‰r†¬,îÌWõŒ‡‚‰Fùﬂå≠…—‘ÿ∂∂∂≈…Õ>ãœEìÀ©ΩÕ“◊€ﬁﬁﬁÍÍÎJúﬁµ≈÷J≠˜©∆‹Ω…”fôÃ–÷€\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0°¿JE)\Zè\Z°êëap8$Åeî$(@\n)ãÁuUR!›àE√.ïÖbNüª±ôù /¡òx*%6$&4(x% 	!Nx(5|.Ox#(/&-)PJ(&\"\r**XO03,+\ZåJ|7\n\'∆«q%%N–XBO’/LoX/‚‚L/ŸA\0;'),(8,'pps','application/vnd.ms-powerpoint','Microsoft PowerPoint','GIF89a\0\0’\0\0√]xØ‡Ñ§Ãü°•ÉÑÑÙÛÔøã]ﬁäåËÎÓ‘…¿Û∞v‰‚‡Â©qÃÃÃﬁﬁﬁ“’êˇzøøø’’’¸°Q≠≤µ’¿≠µΩΩ¢›ıˇô3ˇˇˇˆyªí˜—©Ê‡…˛ÜÒøí›œ√ı∂}€ï§∆ƒ¡‹ËâˆˆˆÃäRπ√œˇÃô‹ÕøÚ≤zˇ∂UÌÎÍˇ|ÁÁÁß™Æˇ∂jÎ„øˇåÔÔÔµµµˇÇ≈ΩΩ·”≈˘æ~ÏÆv˜˜Ô◊À¿¶·¸«≈√\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0ô¿åpH,fí§rôl4êƒãUrìôí6#q¨Z-V€àDFËà#<.Õf\'Å†QxœJ…å‡]6n3^\rÅw$\",.,ÑR,31%.Jë/°3	8\n!Ø9*9è-ææ\Z\0,B+(05ÀÀ\Z&îB3))7’ ”);F‹›ﬁFA\0;'),(9,'ppt','application/vnd.ms-powerpoint','Microsoft PowerPoint','GIF89a\0\0Ê\0\0√]éº‚î™≈≠≠≠ÖÖÖÎÈÊ‰·ﬂ∫›Ûæä\\–‘ÿÔØw±Õ÷ﬂŒΩ°ΩﬁˇzÍ≠uˇˇˇﬁﬁﬁ¸°Q¢µÀÚøí°÷ıˇô3’¿≠ˆy∑’›˜˜˜…ıˇÃÃÃç¨÷˛Ü˘æ~≥≈Œ˜—©ÊÊÊ÷ÿﬂ◊»ª†Ωﬂò∫“÷÷÷Œ÷ﬁµŒﬁ§¬‘ëø‰ÔÔÔﬁ‡„Ô∞xˇ∂jÈÍÎÃäR“Ò¸ˇÃôˇ∂U≠ø–ˇ|ˇåîµ›ú¥…‰„·Ôµ{ﬁ÷÷ˇÇÔµsﬁÊÊΩﬁ˜øÃ”î≠÷ª√œúΩ◊ï∆ËÈÎÔ™¿Ÿˇˇˇ\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0H\0,\0\0\0\0\0\0\0™ÄHÇÉÑÖH	âä	çé\'ì\Zóò\Z\Z\'íö\Z,ãâ,,\Zùï\"ù )2A§\Zâï:	#9D&*5±âö+G00\Z\'†E\"”:÷:0	œ\rﬁ8C\"ëù⁄\ZF-\" 0\"Ë,$\n.˜¯0HBHp`£`A\0\"¯˚Gc∆ã\"FƒÉ≈ y2f$¡ëÑEC Cä,\0;'),(10,'png','image/png','PNG Image','GIF89a\0\0Ê\0\0#EÃÃÃÆÆÆ§àhCWâﬁﬁﬁfffoé‹ππøÑÑÑ˜˜˜ä¨˜3Bhêö≥õp]¢¢¢ctú∏ø…2_ÔÔÔ≈—„åããÆæ·OHL]Ö∑√π≠B67Nq¡L><wúËµµµfkzØ¢åˇˇˇ) ∆«‘ôôÃ÷÷÷ôôô±úÉwÇùËÈÈÄòœ|vx∏e¿¬ƒôªˆ†•±dNP`s¨≈…Â¡ª¥ΩΩΩpfcî∑¸À÷‹œΩßzúÚ±∏‹€›Èµ≠≠≈≈ƒ••≠ΩΩ≈µ§äß°¥\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0!\0,\0\0\0\0\0\0\0≠Ä!!	ÖÜáÇÇ%!\nå%==éä!å)	5%ïä%\nô	%°ã\n\ní)Æ≤å¢Æ4;4ª¥≠%:=))ñ%í\"5+#>…¢=$2âä-90,(\r\0-4ñ‘.\ZA1/Ô% @769@0!†¿ª\0N¿AE£jäz@ÍAqÜ\0#ñÿHçÜ\'Kä\nàY¬ »@\0;'),(11,'doc','application/msword','Microsoft Word','GIF89a\0\0’\0\0Dv ﬁﬁﬁÃÃÃØ∏«Ñ¶⁄ÍÏÓí∏ıΩÀﬂ•••`ïÓˆÙÚ—ÿ‡¢≤œ‚ÂÈTåÍ≠¬Ê÷÷÷ˇˇˇœ◊„züŸãØÔ¡¡¡≠≈ﬁºœÍËÒ˝vò‘÷ﬁÈÎÍÁ´√Ô¬–‰ÎÎÈﬂ‡„µµµËÍÏè•ÕTçÌ’⁄·™™™∫…⁄ ◊ÎÊÂ‚ÔÔ≈Œﬁ¬¬¬ì∞„≥«Î≥«‰Ez“ùΩÙÁÊÂÊﬁﬁ˜ÔÔˆˆˆ◊ÿ€»‘Ê{úﬁ¿—ÓYèÓXåËµ≈ﬁÆ»Ùœ€Ì»”·\0\0\0!˘\0\0,\0\0\0\0\0\0\0™¿Hƒ#ÄèGîP∏Y:óßáV(§O*Eã.=ëTØÖ+‡.ÖÆsC=Nﬂ√¶Qxô¥∆£±Ÿ<4()w\n1611.$(ÉÖä.É141$1$(Nõ6((¶KÅ($>5F+N¡ª %æ1}( O,#ÿ/O,0<	9:\0›.&Ì\"›A\0;'),(17,'mov','video/mpeg','MPEG Movie','GIF89a\0\0’\0\0fff«”‹Æ∑¡>ÜƒÊÊÊôôôWí≈¡Ã‘˜˜˜Jôÿôπ–wéßtî±ÿ⁄‹)ã‡ßßßµ¬ÀÇÇÇ√√√M≠ÌÖß¿ãããˇˇˇ2í„Æ≈’÷÷÷æææ=ìÿ≠≠≠ÃÃÃæ∆ÕÓÓÓBïﬁßø“Kõ‰r†¬,îÌWõŒ‡‚‰Fùﬂå≠…—‘ÿµµµ≈…Õ>ãœEìÀ©ΩÕ“◊€ﬁﬁﬁÍÍÎJúﬁµ≈÷J≠˜©∆‹Ω…”fôÃ–÷€\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0£@ãÂ)\Zè°∞aä5T©O\'”·HîñF•ÿ©p\"èB!ÉÕ\0ƒ‚J\'\"Óî	à∏<n\"+309ﬁlÿÖ+&7%\'5)e&!	\"\r\rnw)6{/òÄ$)0\'.*£)\r\'# ++\ZX¢14-,åJ\Z{8\n(»…{&&ó“XB¢◊0~\ZX0‰‰~0€A\0;'),(12,'xls','application/vnd.ms-excel','Microsoft Excel','GIF89a\0\0’\0\0ü¿Úø÷÷÷µµµtÿqO¡MI∂EˆˆˆßßßÓÈÓ-≈*ÃÃÃÂÂÂfÃfﬁ›ﬁˇˇˇö◊ô§¿§\nΩå¡ã`’\\∫∫∫JŒGq∫o®‘ßÔÔÔÊﬁÊæ\n≈≈≈ØØØÙÙˇKøHS¬OfÃfÔÔ˜ª¶2≈,\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0z¿«É±èGÜP®\\:Ög‚‡p2◊Î#∫dåY,ñÀ¸\nÆôÙÿÈã≥‰°9gg‹·ª›¯8¯˝	vbD{FHvUíêK	ú	\ZO\r\n\n©$MN\r≥\0OB∑A\0;'),(16,'mp3','audio/x-mp3','MP3 Audio','GIF89a\0\0’\0\0KëO’œ»•±µôôôååå··„±Ã–~~~ÔÔÔm≥ká≥àü≥óÃÃÃﬁ›ﬁ˜˜˜ΩΩΩ¥ÕÛ®∏ØÎ‰≥Éﬁsä©ãfôfÄ£Åºø◊•••∆›∂ˇˇˇµ∂µë∫ïú¿°÷÷÷∂≈πƒƒƒOßRÊÊÊz∑i¥€ —“ÿÈ·÷ÁËÏ≠≠≠ã∂ãa°\\ìØëµ∂Ωå¥ëú≤†ñ»íûƒ£U°Z\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\Z\0,\0\0\0\0\0\0\0£@çÊÅ)\Zè°–√Ù\Z(¢¡Pj<j†≈4Ñ§“ﬁPèçà\0≤z4Ñ` éí€—PçL/wzc,-!+	%Uc\'%.êV%\'\r\')\"\"ëBv$)w\n\"mJv\r1		\0\r\rb™\'¬\r0(VßŒ“ÃœOjV\Z ßO\r ⁄€ÂÊi\'€A\0;'),(13,'swf','application/x-shockwave-flash','Macromedia Flash','GIF89a\0\0’\0\0/Q‘÷ﬁµ∏ºåô•ÄéúnÉóÍÌÔUwêƒ –ú©∑˜˜˜ﬂ„Êzãõ3SmîüÆ–‘◊ôôô¡≈…vvvEhˇˇˇ´´´¢¢¢µΩƒﬁﬁﬁDa[á£Æ≤µôôôö•±‰ÁÎäñ£ΩΩΩ◊‹‚À—Ÿ÷÷÷Yxè}}}ƒƒƒHlõ¶≤ﬂ‡ÊÊÊÊÃÃÃÔÔÔÑì†≥ø µµµ∏º¿îú•ñ°Æÿ‹„ƒ»À\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\0,\0\0\0\0\0\0\0†@\n)\Zè °#T`V+åb T2ŸÂ\"€∞úJäF( B™Å\0le≈.çÃl˛aç¬*e\0ê\\32*wm\'	3*x\n-!\n\Z4ê-\"	4#a#,,ù&ß©¥v∏ßª** %ÑBææO≤\n+a∆»+ \nèÕO ƒaP+&„*ÕA\0;'),(14,'txt','text/plain','Plain Text File','GIF89a\0\0’\0\0Q=2ÿ—Õ≈≈≈ØÆ®lxyﬁﬁﬁiNFáôªØ∂¿àÅs™®•˜˜˘ù¥ﬁÃÃÃGH7ÇÇÇîQGÊÊÊôôô–°É≤≤≤‚◊–çB:WSIòñÖˇˇˇΩΩΩ÷÷÷PL.•µ—^D;¥∞†{{{^90èÖx‘◊‹⁄·‰GJBOB1÷÷Œ≠≠≠„÷Œ\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\Z\0,\0\0\0\0\0\0\0ë@ç¶‡èHÜP√Y\nJ\r√`Xr(…∆ X\\\"†BU»)òÕÉëË`BçôÖr£¯ú:é7G‚‡Ï\0&nVD}	\r\r$z|e}bÜf~ïyÜóï¢[†¢FzH!N•´|NL%qºM¡W*q¡Bq~÷“A\0;'),(15,'rtf','text/richtext','Rich Text File','GIF89a\0\0’\0\0Q=2ÿ—Õ≈≈≈ØÆ®lxyﬁﬁﬁiNFáôªØ∂¿àÅs™®•˜˜˘ù¥ﬁÃÃÃGH7ÇÇÇîQGÊÊÊôôô–°É≤≤≤‚◊–çB:WSIòñÖˇˇˇΩΩΩ÷÷÷PL.•µ—^D;¥∞†{{{^90èÖx‘◊‹⁄·‰GJBOB1÷÷Œ≠≠≠„÷Œ\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!˘\0\Z\0,\0\0\0\0\0\0\0ë@ç¶‡èHÜP√Y\nJ\r√`Xr(…∆ X\\\"†BU»)òÕÉëË`BçôÖr£¯ú:é7G‚‡Ï\0&nVD}	\r\r$z|e}bÜf~ïyÜóï¢[†¢FzH!N•´|NL%qºM¡W*q¡Bq~÷“A\0;');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_community_type_options` (
  `ctoption_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `option_shortname` varchar(32) NOT NULL DEFAULT '',
  `option_name` varchar(84) NOT NULL DEFAULT '',
  PRIMARY KEY (`ctoption_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_community_type_options` VALUES (1,'course_website','Course Website Functionality'),(2,'sequential_navigation','Learning Module Sequential Navigation');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_community_types` (
  `ctype_id` int(12) NOT NULL AUTO_INCREMENT,
  `community_type_name` varchar(84) DEFAULT NULL,
  `default_community_template` varchar(30) NOT NULL DEFAULT 'default',
  `default_community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `default_community_keywords` varchar(255) NOT NULL DEFAULT '',
  `default_community_protected` int(1) NOT NULL DEFAULT '1',
  `default_community_registration` int(1) NOT NULL DEFAULT '1',
  `default_community_members` text NOT NULL,
  `default_mail_list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `default_community_type_options` text NOT NULL,
  `community_type_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ctype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_community_types` VALUES (1,'Other','default','default','',1,0,'','inactive','{}',1),(2,'Course Website','course','course','',1,0,'','inactive','{\"course_website\":\"1\"}',1),(3,'Online Learning Module','learningmodule','default','',1,0,'','inactive','{\"sequential_navigation\":\"1\"}',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_countries` (
  `countries_id` int(6) NOT NULL AUTO_INCREMENT,
  `country` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`countries_id`)
) ENGINE=MyISAM AUTO_INCREMENT=242 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_countries` VALUES (1,'Afghanistan'),(2,'Aland Islands'),(3,'Albania'),(4,'Algeria'),(5,'American Samoa'),(6,'Andorra'),(7,'Angola'),(8,'Anguilla'),(9,'Antarctica'),(10,'Antigua and Barbuda'),(11,'Argentina'),(12,'Armenia'),(13,'Aruba'),(14,'Australia'),(15,'Austria'),(16,'Azerbaijan'),(17,'Bahamas'),(18,'Bahrain'),(19,'Bangladesh'),(20,'Barbados'),(21,'Belarus'),(22,'Belgium'),(23,'Belize'),(24,'Benin'),(25,'Bermuda'),(26,'Bhutan'),(27,'Bolivia'),(28,'Bosnia and Herzegovina'),(29,'Botswana'),(30,'Bouvet Island'),(31,'Brazil'),(32,'British Indian Ocean territory'),(33,'Brunei Darussalam'),(34,'Bulgaria'),(35,'Burkina Faso'),(36,'Burundi'),(37,'Cambodia'),(38,'Cameroon'),(39,'Canada'),(40,'Cape Verde'),(41,'Cayman Islands'),(42,'Central African Republic'),(43,'Chad'),(44,'Chile'),(45,'China'),(46,'Christmas Island'),(47,'Cocos (Keeling) Islands'),(48,'Colombia'),(49,'Comoros'),(50,'Congo'),(51,'Congo'),(52,'Democratic Republic'),(53,'Cook Islands'),(54,'Costa Rica'),(55,'Cote D\'Ivoire (Ivory Coast)'),(56,'Croatia (Hrvatska)'),(57,'Cuba'),(58,'Cyprus'),(59,'Czech Republic'),(60,'Denmark'),(61,'Djibouti'),(62,'Dominica'),(63,'Dominican Republic'),(64,'East Timor'),(65,'Ecuador'),(66,'Egypt'),(67,'El Salvador'),(68,'Equatorial Guinea'),(69,'Eritrea'),(70,'Estonia'),(71,'Ethiopia'),(72,'Falkland Islands'),(73,'Faroe Islands'),(74,'Fiji'),(75,'Finland'),(76,'France'),(77,'French Guiana'),(78,'French Polynesia'),(79,'French Southern Territories'),(80,'Gabon'),(81,'Gambia'),(82,'Georgia'),(83,'Germany'),(84,'Ghana'),(85,'Gibraltar'),(86,'Greece'),(87,'Greenland'),(88,'Grenada'),(89,'Guadeloupe'),(90,'Guam'),(91,'Guatemala'),(92,'Guinea'),(93,'Guinea-Bissau'),(94,'Guyana'),(95,'Haiti'),(96,'Heard and McDonald Islands'),(97,'Honduras'),(98,'Hong Kong'),(99,'Hungary'),(100,'Iceland'),(101,'India'),(102,'Indonesia'),(103,'Iran'),(104,'Iraq'),(105,'Ireland'),(106,'Israel'),(107,'Italy'),(108,'Jamaica'),(109,'Japan'),(110,'Jordan'),(111,'Kazakhstan'),(112,'Kenya'),(113,'Kiribati'),(114,'Korea (north)'),(115,'Korea (south)'),(116,'Kuwait'),(117,'Kyrgyzstan'),(118,'Lao People\'s Democratic Republic'),(119,'Latvia'),(120,'Lebanon'),(121,'Lesotho'),(122,'Liberia'),(123,'Libyan Arab Jamahiriya'),(124,'Liechtenstein'),(125,'Lithuania'),(126,'Luxembourg'),(127,'Macao'),(128,'Macedonia'),(129,'Madagascar'),(130,'Malawi'),(131,'Malaysia'),(132,'Maldives'),(133,'Mali'),(134,'Malta'),(135,'Marshall Islands'),(136,'Martinique'),(137,'Mauritania'),(138,'Mauritius'),(139,'Mayotte'),(140,'Mexico'),(141,'Micronesia'),(142,'Moldova'),(143,'Monaco'),(144,'Mongolia'),(145,'Montserrat'),(146,'Morocco'),(147,'Mozambique'),(148,'Myanmar'),(149,'Namibia'),(150,'Nauru'),(151,'Nepal'),(152,'Netherlands'),(153,'Netherlands Antilles'),(154,'New Caledonia'),(155,'New Zealand'),(156,'Nicaragua'),(157,'Niger'),(158,'Nigeria'),(159,'Niue'),(160,'Norfolk Island'),(161,'Northern Mariana Islands'),(162,'Norway'),(163,'Oman'),(164,'Pakistan'),(165,'Palau'),(166,'Palestinian Territories'),(167,'Panama'),(168,'Papua New Guinea'),(169,'Paraguay'),(170,'Peru'),(171,'Philippines'),(172,'Pitcairn'),(173,'Poland'),(174,'Portugal'),(175,'Puerto Rico'),(176,'Qatar'),(177,'Reunion'),(178,'Romania'),(179,'Russian Federation'),(180,'Rwanda'),(181,'Saint Helena'),(182,'Saint Kitts and Nevis'),(183,'Saint Lucia'),(184,'Saint Pierre and Miquelon'),(185,'Saint Vincent and the Grenadines'),(186,'Samoa'),(187,'San Marino'),(188,'Sao Tome and Principe'),(189,'Saudi Arabia'),(190,'Senegal'),(191,'Serbia and Montenegro'),(192,'Seychelles'),(193,'Sierra Leone'),(194,'Singapore'),(195,'Slovakia'),(196,'Slovenia'),(197,'Solomon Islands'),(198,'Somalia'),(199,'South Africa'),(200,'South Georgia and the South Sandwich Islands'),(201,'Spain'),(202,'Sri Lanka'),(203,'Sudan'),(204,'Suriname'),(205,'Svalbard and Jan Mayen Islands'),(206,'Swaziland'),(207,'Sweden'),(208,'Switzerland'),(209,'Syria'),(210,'Taiwan'),(211,'Tajikistan'),(212,'Tanzania'),(213,'Thailand'),(214,'Togo'),(215,'Tokelau'),(216,'Tonga'),(217,'Trinidad and Tobago'),(218,'Tunisia'),(219,'Turkey'),(220,'Turkmenistan'),(221,'Turks and Caicos Islands'),(222,'Tuvalu'),(223,'Uganda'),(224,'Ukraine'),(225,'United Arab Emirates'),(226,'United Kingdom'),(227,'United States of America'),(228,'Uruguay'),(229,'Uzbekistan'),(230,'Vanuatu'),(231,'Vatican City'),(232,'Venezuela'),(233,'Vietnam'),(234,'Virgin Islands (British)'),(235,'Virgin Islands (US)'),(236,'Wallis and Futuna Islands'),(237,'Western Sahara'),(238,'Yemen'),(239,'Zaire'),(240,'Zambia'),(241,'Zimbabwe');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_disciplines` (
  `discipline_id` int(11) NOT NULL AUTO_INCREMENT,
  `discipline` varchar(250) NOT NULL,
  PRIMARY KEY (`discipline_id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_disciplines` VALUES (1,'Adolescent Medicine'),(2,'Anatomical Pathology'),(3,'Anesthesiology'),(4,'Cardiac Surgery'),(5,'Cardiology'),(6,'Child & Adolescent Psychiatry'),(7,'Clinical Immunology and Allergy'),(8,'Clinical Pharmacology'),(9,'Colorectal Surgery'),(10,'Community Medicine'),(11,'Critical Care Medicine'),(12,'Dermatology'),(13,'Developmental Pediatrics'),(14,'Diagnostic Radiology'),(15,'Emergency Medicine'),(16,'Endocrinology and Metabolism'),(17,'Family Medicine'),(18,'Forensic Pathology'),(19,'Forensic Psychiatry'),(20,'Gastroenterology'),(21,'General Pathology'),(22,'General Surgery'),(23,'General Surgical Oncology'),(24,'Geriatric Medicine'),(25,'Geriatric Psychiatry'),(26,'Gynecologic Oncology'),(27,'Gynecologic Reproductive Endocrinology and Infertility'),(28,'Hematological Pathology '),(29,'Hematology'),(30,'Infectious Disease'),(31,'Internal Medicine'),(32,'Maternal-Fetal Medicine'),(33,'Medical Biochemistry'),(34,'Medical Genetics'),(35,'Medical Microbiology'),(36,'Medical Oncology'),(37,'Neonatal-Perinatal Medicine'),(38,'Nephrology'),(39,'Neurology'),(40,'Neuropathology'),(41,'Neuroradiology'),(42,'Neurosurgery'),(43,'Nuclear Medicine'),(44,'Obstetrics & Gynecology'),(45,'Occupational Medicine'),(46,'Ophthalmology'),(47,'Orthopedic Surgery'),(48,'Otolaryngology-Head and Neck Surgery'),(49,'Palliative Medicine'),(50,'Pediatric Emergency Medicine'),(51,'Pediatric General Surgery'),(52,'Pediatric Hematology/Oncology'),(53,'Pediatric Radiology'),(54,'Pediatrics'),(55,'Physical Medicine and Rehabilitation'),(56,'Plastic Surgery'),(57,'Psychiatry'),(58,'Radiation Oncology'),(59,'Respirology'),(60,'Rheumatology'),(61,'Thoracic Surgery'),(62,'Transfusion Medicine'),(63,'Urology'),(64,'Vascular Surgery');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_focus_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `focus_group` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`),
  KEY `focus_group` (`focus_group`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_focus_groups` VALUES (1,'Cancer'),(2,'Neurosciences'),(3,'Cardiovascular, Circulatory and Respiratory'),(4,'Gastrointestinal'),(5,'Musculoskeletal\n'),(6,'Health Services Research'),(15,'Other'),(7,'Protein Function and Discovery'),(8,'Reproductive Sciences'),(9,'Genetics'),(10,'Nursing'),(11,'Primary Care Studies'),(12,'Emergency'),(13,'Critical Care'),(14,'Nephrology'),(16,'Educational Research'),(17,'Microbiology and Immunology'),(18,'Urology'),(19,'Psychiatry'),(20,'Anesthesiology'),(22,'Obstetrics and Gynecology'),(23,'Rehabilitation Therapy'),(24,'Occupational Therapy');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_hospital_location` (
  `hosp_id` int(11) NOT NULL DEFAULT '0',
  `hosp_desc` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`hosp_id`),
  KEY `hosp_desc` (`hosp_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_objectives` (
  `objective_id` int(12) NOT NULL AUTO_INCREMENT,
  `objective_code` varchar(24) DEFAULT NULL,
  `objective_name` varchar(60) NOT NULL,
  `objective_description` text,
  `objective_parent` int(12) NOT NULL DEFAULT '0',
  `objective_order` int(12) NOT NULL DEFAULT '0',
  `objective_loggable` tinyint(1) NOT NULL DEFAULT '0',
  `objective_active` int(12) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`),
  KEY `objective_order` (`objective_order`),
  KEY `objective_code` (`objective_code`),
  FULLTEXT KEY `ft_objective_search` (`objective_code`,`objective_name`,`objective_description`)
) ENGINE=MyISAM AUTO_INCREMENT=2403 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_objectives` VALUES (1,NULL,'Curriculum Objectives','',0,2,0,1,0,0),(2,NULL,'Medical Expert','',1,0,0,1,0,0),(3,NULL,'Professionalism','',1,0,0,1,0,0),(4,NULL,'Scholar','',1,0,0,1,0,0),(5,NULL,'Communicator','',1,0,0,1,0,0),(6,NULL,'Collaborator','',1,0,0,1,0,0),(7,NULL,'Advocate','',1,0,0,1,0,0),(8,NULL,'Manager','',1,0,0,1,0,0),(9,NULL,'Application of Basic Sciences','The competent medical graduate articulates and uses the basic sciences to inform disease prevention, health promotion and the assessment and management of patients presenting with clinical illness.',2,0,0,1,0,0),(10,NULL,'Clinical Assessment','Is able to perform a complete and appropriate clinical assessment of patients presenting with clinical illness',2,1,0,1,0,0),(11,NULL,'Clinical Presentations','Is able to appropriately assess and provide initial management for patients presenting with clinical illness, as defined by the Medical Council of Canada Clinical Presentations',2,2,0,1,0,0),(12,NULL,'Health Promotion','Apply knowledge of disease prevention and health promotion to the care of patients',2,3,0,1,0,0),(13,NULL,'Professional Behaviour','Demonstrates appropriate professional behaviours to serve patients, the profession, and society',3,0,0,1,0,0),(14,NULL,'Principles of Professionalism','Apply knowledge of legal and ethical principles to serve patients, the profession, and society',3,1,0,1,0,0),(15,NULL,'Critical Appraisal','Critically evaluate medical information and its sources (the literature)',4,0,0,1,0,0),(16,NULL,'Research','Contribute to the process of knowledge creation (research)',4,1,0,1,0,0),(17,NULL,'Life Long Learning','Engages in life long learning',4,2,0,1,0,0),(18,NULL,'Effective Communication','Effectively communicates with colleagues, other health professionals, patients, families and other caregivers',5,0,0,1,0,0),(19,NULL,'Effective Collaboration','Effectively collaborate with colleagues and other health professionals',6,0,0,1,0,0),(20,NULL,'Determinants of Health','Articulate and apply the determinants of health and disease, principles of health promotion and disease prevention',7,0,0,1,0,0),(21,NULL,'Profession and Community','Effectively advocate for their patients, the profession, and community',7,1,0,1,0,0),(22,NULL,'Practice Options','Describes a variety of practice options and settings within the practice of Medicine',8,0,0,1,0,0),(23,NULL,'Balancing Health and Profession','Balances personal health and professional responsibilities',8,1,0,1,0,0),(24,NULL,'ME1.1 Homeostasis & Dysregulation','Applies knowledge of molecular, biochemical, cellular, and systems-level mechanisms that maintain homeostasis, and of the dysregulation of these mechanisms, to the prevention, diagnosis, and management of disease.',9,0,0,1,0,0),(25,NULL,'ME1.2 Physics and Chemistry','Apply major principles of physics and chemistry to explain normal biology, the pathobiology of significant diseases, and the mechanism of action of major technologies used in the prevention, diagnosis, and treatment of disease.',9,1,0,1,0,0),(26,NULL,'ME1.3 Genetics','Use the principles of genetic transmission, molecular biology of the human genome, and population genetics to guide assessments and clinical decision making.',9,2,0,1,0,0),(27,NULL,'ME1.4 Defense Mechanisms','Apply the principles of the cellular and molecular basis of immune and nonimmune host defense mechanisms in health and disease to determine the etiology of disease, identify preventive measures, and predict response to therapies.',9,3,0,1,0,0),(28,NULL,'ME1.5 Pathological Processes','Apply the mechanisms of general and disease-specific pathological processes in health and disease to the prevention, diagnosis, management, and prognosis of critical human disorders.',9,4,0,1,0,0),(29,NULL,'ME1.6 Microorganisms','Apply principles of the biology of microorganisms in normal physiology and disease to explain the etiology of disease, identify preventive measures, and predict response to therapies.',9,5,0,1,0,0),(30,NULL,'ME1.7 Pharmacology','Apply the principles of pharmacology to evaluate options for safe, rational, and optimally beneficial drug therapy.',9,6,0,1,0,0),(32,NULL,'ME2.1 History and Physical','Conducts a comprehensive and appropriate history and physical examination ',10,0,0,1,0,0),(33,NULL,'ME2.2 Procedural Skills','Demonstrate proficient and appropriate use of selected procedural skills, diagnostic and therapeutic',10,1,0,1,0,0),(34,NULL,'ME3.x Clinical Presentations','',11,0,0,1,0,0),(35,NULL,'ME4.1 Health Promotion & Maintenance','',12,0,0,1,0,0),(36,NULL,'P1.1 Professional Behaviour','Practice appropriate professional behaviours, including honesty, integrity, commitment, dependability, compassion, respect, an understanding of the human condition, and altruism in the educational  and clinical settings',13,0,0,1,0,0),(37,NULL,'P1.2 Patient-Centered Care','Learn how to deliver the highest quality patient-centered care, with commitment to patients\' well being.  ',13,1,0,1,0,0),(38,NULL,'P1.3 Self-Awareness','Is self-aware, engages consultancy appropriately and maintains competence',13,2,0,1,0,0),(39,NULL,'P2.1 Ethics','Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations, etc.)',14,0,0,1,0,0),(40,NULL,'P2.2 Law and Regulation','Apply profession-led regulation to serve patients, the profession and society. ',14,1,0,1,0,0),(41,NULL,'S1.1 Information Retrieval','Are able to retrieve medical information efficiently and effectively',15,0,0,1,0,0),(42,NULL,'S1.2 Critical Evaluation','Critically evaluate the validity and applicability of medical procedures and therapeutic modalities to patient care',15,1,0,1,0,0),(43,NULL,'S2.1 Research Methodology','Adopt rigorous research methodology and scientific inquiry procedures',16,0,0,1,0,0),(44,NULL,'S2.2 Sharing Innovation','Prepares and disseminates new medical information',16,1,0,1,0,0),(45,NULL,'S3.1 Learning Strategies','Implements effective personal learning experiences including the capacity to engage in reflective learning',17,0,0,1,0,0),(46,NULL,'CM1.1 Therapeutic Relationships','Demonstrate skills and attitudes to foster rapport, trust and ethical therapeutic relationships with patients and families',18,0,0,1,0,0),(47,NULL,'CM1.2 Eliciting Perspectives','Elicit and synthesize relevant information and perspectives of patients and families, colleagues and other professionals',18,1,0,1,0,0),(48,NULL,'CM1.3 Conveying Information','Convey relevant information and explanations appropriately to patients and families, colleagues and other professionals, orally and in writing',18,2,0,1,0,0),(49,NULL,'CM1.4 Finding Common Ground','Develop a common understanding on issues, problems, and plans with patients and families, colleagues and other professionals to develop a shared plan of care',18,3,0,1,0,0),(50,NULL,'CL 1.1 Working In Teams','Participate effectively and appropriately as part of a multiprofessional healthcare team.',19,0,0,1,0,0),(51,NULL,'CL1.2 Overcoming Conflict','Work with others effectively in order to prevent, negotiate, and resolve conflict.',19,1,0,1,0,0),(52,NULL,'CL1.3 Including Patients and Families','Includes patients and families in prevention and management of illness',19,2,0,1,0,0),(53,NULL,'CL1.4 Teaching and Learning','Teaches and learns from others consistently  ',19,3,0,1,0,0),(54,NULL,'A1.1 Applying Determinants of Health','Apply knowledge of the determinants of health for populations to medical encounters and problems.',20,0,0,1,0,0),(55,NULL,'A2.1 Community Resources','Identify and communicate about community resources to promote health, prevent disease, and manage illness in their patients and the communities they will serve.',21,0,0,1,0,0),(56,NULL,'A2.2 Responsibility and Service','Integrate the principles of advocacy into their understanding of their professional responsibility to patients and the communities they will serve. ',21,1,0,1,0,0),(57,NULL,'M1.1 Career Settings','Is aware of the variety of practice options and settings within the practice of Medicine, and makes informed personal choices regarding career direction',22,0,0,1,0,0),(58,NULL,'M2.1 Work / Life Balance','Identifies and implement strategies that promote care of one\'s self and one\'s colleagues to maintain balance between personal and educational/ professional commitments',23,0,0,1,0,0),(59,NULL,'ME1.1a','Apply knowledge of biological systems and their interactions to explain how the human body functions in health and disease. ',24,0,0,1,0,0),(60,NULL,'ME1.1b','Use the principles of feedback control to explain how specific homeostatic and reproductive systems maintain the internal environment and identify (1) how perturbations in these systems may result in disease and (2) how homeostasis may be changed by disease.',24,1,0,1,0,0),(61,NULL,'ME1.1c','Apply knowledge of the atomic and molecular characteristics of biological constituents to predict normal and pathological molecular function.',24,2,0,1,0,0),(62,NULL,'ME1.1d','Explain how the regulation of major biochemical energy production pathways and the synthesis/degradation of macromolecules function to maintain health and identify major forms of dysregulation in disease.',24,3,0,1,0,0),(63,NULL,'ME1.1e','Explain the major mechanisms of intra- and intercellular communication and their role in health and disease states.',24,4,0,1,0,0),(64,NULL,'ME1.1f','Apply an understanding of the morphological and biochemical events that occur when somatic or germ cells divide, and the mechanisms that regulate cell division and cell death, to explain normal and abnormal growth and development.',24,5,0,1,0,0),(65,NULL,'ME1.1g','Identify and describe the common and unique microscopic and three dimensional macroscopic structures of macromolecules, cells, tissues, organs, systems, and compartments that lead to their unique and integrated function from fertilization through senescence to explain how perturbations contribute to disease. ',24,6,0,1,0,0),(66,NULL,'ME1.1h','Predict the consequences of structural variability and damage or loss of tissues and organs due to maldevelopment, trauma, disease, and aging.',24,7,0,1,0,0),(67,NULL,'ME1.1i','Apply principles of information processing at the molecular, cellular, and integrated nervous system level and understanding of sensation, perception, decision making, action, and cognition to explain behavior in health and disease.',24,8,0,1,0,0),(68,NULL,'ME1.2a','Apply the principles of physics and chemistry, such as mass flow, transport, electricity, biomechanics, and signal detection and processing, to the specialized functions of membranes, cells, tissues, organs, and the human organism, and recognize how perturbations contribute to disease.',25,0,0,1,0,0),(69,NULL,'ME1.2b','Apply the principles of physics and chemistry to explain the risks, limitations, and appropriate use of diagnostic and therapeutic technologies.',25,1,0,1,0,0),(70,NULL,'ME1.3a','Describe the functional elements in the human genome, their evolutionary origins, their interactions, and the consequences of genetic and epigenetic changes on adaptation and health.',26,0,0,1,0,0),(71,NULL,'ME1.3b','Explain how variation at the gene level alters the chemical and physical properties of biological systems, and how this, in turn, influences health.',26,1,0,1,0,0),(72,NULL,'ME1.3c','Describe the major forms and frequencies of genetic variation and their consequences on health in different human populations.',26,2,0,1,0,0),(73,NULL,'ME1.3d','Apply knowledge of the genetics and the various patterns of genetic transmission within families in order to obtain and interpret family history and ancestry data, calculate risk of disease, and order genetic tests to guide therapeutic decision-making.',26,3,0,1,0,0),(74,NULL,'ME1.3e','Use to guide clinical action plans, the interaction of genetic and environmental factors to produce phenotypes and provide the basis for individual variation in response to toxic, pharmacological, or other exposures.',26,4,0,1,0,0),(75,NULL,'ME1.4a','Apply knowledge of the generation of immunological diversity and specificity to the diagnosis and treatment of disease.',27,0,0,1,0,0),(76,NULL,'ME1.4b','Apply knowledge of the mechanisms for distinction between self and nonself (tolerance and immune surveillance) to the maintenance of health, autoimmunity, and transplant rejection.',27,1,0,1,0,0),(77,NULL,'ME1.4c','Apply knowledge of the molecular basis for immune cell development to diagnose and treat immune deficiencies.',27,2,0,1,0,0),(78,NULL,'ME1.4d','Apply knowledge of the mechanisms used to defend against intracellular or extracellular microbes to the development of immunological prevention or treatment.',27,3,0,1,0,0),(79,NULL,'ME1.5a','Apply knowledge of cellular responses to injury, and the underlying etiology, biochemical and molecular alterations, to assess therapeutic interventions.',28,0,0,1,0,0),(80,NULL,'ME1.5b','Apply knowledge of the vascular and leukocyte responses of inflammation and their cellular and soluble mediators to the causation, resolution, prevention, and targeted therapy of tissue injury.',28,1,0,1,0,0),(81,NULL,'ME1.5c','Apply knowledge of the interplay of platelets, vascular endothelium, leukocytes, and coagulation factors in maintaining fluidity of blood, formation of thrombi, and causation of atherosclerosis to the prevention and diagnosis of thrombosis and atherosclerosis in various vascular beds, and the selection of therapeutic responses.',28,2,0,1,0,0),(82,NULL,'ME1.5d','Apply knowledge of the molecular basis of neoplasia to an understanding of the biological behavior, morphologic appearance, classification, diagnosis, prognosis, and targeted therapy of specific neoplasms.',28,3,0,1,0,0),(83,NULL,'ME1.6a','Apply the principles of host-pathogen and pathogen-population interactions and knowledge of pathogen structure, genomics, lifecycle, transmission, natural history, and pathogenesis to the prevention, diagnosis, and treatment of infectious disease.',29,0,0,1,0,0),(84,NULL,'ME1.6b','Apply the principles of symbiosis (commensalisms, mutualism, and parasitism) to the maintenance of health and disease.',29,1,0,1,0,0),(85,NULL,'ME1.6c','Apply the principles of epidemiology to maintaining and restoring the health of communities and individuals.',29,2,0,1,0,0),(86,NULL,'ME1.7a','Apply knowledge of pathologic processes, pharmacokinetics, and pharmacodynamics to guide safe and effective treatments.',30,0,0,1,0,0),(87,NULL,'ME1.7b','Select optimal drug therapy based on an understanding of pertinent research, relevant medical literature, regulatory processes, and pharmacoeconomics.',30,1,0,1,0,0),(88,NULL,'ME1.7c','Apply knowledge of individual variability in the use and responsiveness to pharmacological agents to selecting and monitoring therapeutic regimens and identifying adverse responses.',30,2,0,1,0,0),(89,NULL,'ME1.8a','Apply basic mathematical tools and concepts, including functions, graphs and modeling, measurement and scale, and quantitative reasoning, to an understanding of the specialized functions of membranes, cells, tissues, organs, and the human organism, in both health and disease.',31,0,0,1,0,0),(90,NULL,'ME1.8b','Apply the principles and approaches of statistics, biostatistics, and epidemiology to the evaluation and interpretation of disease risk, etiology, and prognosis, and to the prevention, diagnosis, and management of disease.',31,1,0,1,0,0),(91,NULL,'ME1.8c','Apply the basic principles of information systems, their design and architecture, implementation, use, and limitations, to information retrieval, clinical problem solving, and public health and policy.',31,2,0,1,0,0),(92,NULL,'ME1.8d','Explain the importance, use, and limitations of biomedical and health informatics, including data quality, analysis, and visualization, and its application to diagnosis, therapeutics, and characterization of populations and subpopulations. ',31,3,0,1,0,0),(93,NULL,'ME1.8e','Apply elements of the scientific process, such as inference, critical analysis of research design, and appreciation of the difference between association and causation, to interpret the findings, applications, and limitations of observational and experimental research in clinical decision making.',31,4,0,1,0,0),(94,NULL,'ME2.1a','Effectively identify and explore issues to be addressed in a patient encounter, including the patient\'s context and preferences.',32,0,0,1,0,0),(95,NULL,'ME2.1b','For purposes of prevention and health promotion, diagnosis and/or management, elicit a history that is relevant, concise and accurate to context and preferences.',32,1,0,1,0,0),(96,NULL,'ME2.1c','For the purposes of prevention and health promotion, diagnosis and/or management, perform a focused physical examination that is relevant and accurate.',32,2,0,1,0,0),(97,NULL,'ME2.1d','Select basic, medically appropriate investigative methods in an ethical manner.',32,3,0,1,0,0),(98,NULL,'ME2.1e','Demonstrate effective clinical problem solving and judgment to address selected common patient presentations, including interpreting available data and integrating information to generate differential diagnoses and management plans.',32,4,0,1,0,0),(99,NULL,'ME2.2a','Demonstrate effective, appropriate and timely performance of selected diagnostic procedures.',33,0,0,1,0,0),(100,NULL,'ME2.2b','Demonstrate effective, appropriate and timely performance of selected therapeutic procedures.',33,1,0,1,0,0),(101,NULL,'ME3.xa','Identify and apply aspects of normal human structure and physiology relevant to the clinical presentation.',34,0,0,1,0,0),(102,NULL,'ME3.xb','Identify pathologic or maladaptive processes that are active.',34,1,0,1,0,0),(103,NULL,'ME3.xc','Develop a differential diagnosis for the clinical presentation.',34,2,0,1,0,0),(104,NULL,'ME3.xd','Use history taking and physical examination relevant to the clinical presentation.',34,3,0,1,0,0),(105,NULL,'ME3.xe','Use diagnostic tests or procedures appropriately to establish working diagnoses.',34,4,0,1,0,0),(106,NULL,'ME3.xf','Provide appropriate initial management for the clinical presentation.',34,5,0,1,0,0),(107,NULL,'ME3.xg','Provide evidence for diagnostic and therapeutic choices.',34,6,0,1,0,0),(108,NULL,'ME4.1a','Demonstrate awareness and respect for the Determinants of Health in identifying the needs of a patient.',35,0,0,1,0,0),(109,NULL,'ME4.1b','Discover opportunities for health promotion and disease prevention as well as resources for patient care.',35,1,0,1,0,0),(110,NULL,'ME4.1c','Formulate preventive measures into their management strategies.',35,2,0,1,0,0),(111,NULL,'ME4.1d','Communicate with the patient, the patient\'s family and concerned others with regard to risk factors and their modification where appropriate.',35,3,0,1,0,0),(112,NULL,'ME4.1e','Describe programs for the promotion of health including screening for, and the prevention of, illness.',35,4,0,1,0,0),(113,NULL,'P1.1a','Defines the concepts of honesty, integrity, commitment, dependability, compassion, respect and altruism as applied to medical practice and correctly identifies examples of appropriate and inappropriate application.',36,0,0,1,0,0),(114,NULL,'P1.1b','Applies these concepts in medical and professional encounters.',36,1,0,1,0,0),(115,NULL,'P1.2a','Defines the concept of \"standard of care\".',37,0,0,1,0,0),(116,NULL,'P1.2b','Applies diagnostic and therapeutic modalities in evidence based and patient centred contexts.',37,1,0,1,0,0),(117,NULL,'P1.3a','Recognizes and acknowledges limits of personal competence.',38,0,0,1,0,0),(118,NULL,'P1.3b','Is able to acquire specific knowledge appropriately to assist clinical management.',38,1,0,1,0,0),(119,NULL,'P1.3c','Engages colleagues and other health professionals appropriately.',38,2,0,1,0,0),(120,NULL,'P2.1a','Analyze ethical issues encountered in practice (such as informed consent, confidentiality, truth telling, vulnerable populations etc).',39,0,0,1,0,0),(121,NULL,'P2.1b','Analyze legal issues encountered in practice (such as conflict of interest, patient rights and privacy, etc).',39,1,0,1,0,0),(122,NULL,'P2.1c','Analyze the psycho-social, cultural and religious issues that could affect patient management.',39,2,0,1,0,0),(123,NULL,'P2.1d','Define and implement principles of appropriate relationships with patients.',39,3,0,1,0,0),(124,NULL,'P2.2a','Recognize the professional, legal and ethical codes and obligations required of current practice in a variety of settings, including hospitals, private practice and health care institutions, etc.',40,0,0,1,0,0),(125,NULL,'P2.2b','Recognize and respond appropriately to unprofessional behaviour in colleagues.',40,1,0,1,0,0),(126,NULL,'S1.1a','Use objective parameters to assess reliability of various sources of medical information.',41,0,0,1,0,0),(127,NULL,'S1.1b','Are able to efficiently search sources of medical information in order to address specific clinical questions.',41,1,0,1,0,0),(128,NULL,'S1.2a','Apply knowledge of research and statistical methodology to the review of medical information and make decisions for health care of patients and society through scientifically rigourous analysis of evidence.',42,0,0,1,0,0),(129,NULL,'S1.2b','Apply to the review of medical literature the principles of research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.',42,1,0,1,0,0),(130,NULL,'S1.2c','Identify the nature and requirements of organizations contributing to medical education.',42,2,0,1,0,0),(131,NULL,'S1.2d','Balance scientific evidence with consideration of patient preferences and overall quality of life in therapeutic decision making.',42,3,0,1,0,0),(132,NULL,'S2.1a','Formulates relevant research hypotheses.',43,0,0,1,0,0),(133,NULL,'S2.1b','Develops rigorous methodologies.',43,1,0,1,0,0),(134,NULL,'S2.1c','Develops appropriate collaborations in order to participate in research projects.',43,2,0,1,0,0),(135,NULL,'S2.1d','Practice research ethics, including disclosure, conflicts of interest, research on human subjects and industry relations.',43,3,0,1,0,0),(136,NULL,'S2.1e','Evaluates the outcomes of research by application of rigorous statistical analysis.',43,4,0,1,0,0),(137,NULL,'S2.2a','Report to students and faculty upon new knowledge gained from research and enquiry, using a variety of methods.',44,0,0,1,0,0),(138,NULL,'S3.1a','Develop lifelong learning strategies through integration of the principles of learning.',45,0,0,1,0,0),(139,NULL,'S3.1b','Self-assess learning critically, in congruence with others\' assessment, and address prioritized learning issues.',45,1,0,1,0,0),(140,NULL,'S3.1c','Ask effective learning questions and solve problems appropriately.',45,2,0,1,0,0),(141,NULL,'S3.1d','Consult multiple sources of information.',45,3,0,1,0,0),(142,NULL,'S3.1e','Employ a variety of learning methodologies.',45,4,0,1,0,0),(143,NULL,'S3.1f','Learn with and enhance the learning of others through communities of practice.',45,5,0,1,0,0),(144,NULL,'S3.1g','Employ information technology (informatics) in learning, including, in clerkship, access to patient record data and other technologies.',45,6,0,1,0,0),(145,NULL,'CM1.1a','Apply the skills that develop positive therapeutic relationships with patients and their families, characterized by understanding, trust, respect, honesty and empathy.',46,0,0,1,0,0),(146,NULL,'CM1.1b','Respect patient confidentiality, privacy and autonomy.',46,1,0,1,0,0),(147,NULL,'CM1.1c','Listen effectively and be aware of and responsive to nonverbal cues.',46,2,0,1,0,0),(148,NULL,'CM1.1d','Communicate effectively with individuals regardless of their social, cultural or ethnic backgrounds, or disabilities.',46,3,0,1,0,0),(149,NULL,'CM1.1e','Effectively facilitate a structured clinical encounter.',46,4,0,1,0,0),(150,NULL,'CM1.2a','Gather information about a disease, but also about a patient\'s beliefs, concerns, expectations and illness experience.',47,0,0,1,0,0),(151,NULL,'CM1.2b','Seek out and synthesize relevant information from other sources, such as a patient\'s family, caregivers and other professionals.',47,1,0,1,0,0),(152,NULL,'CM1.3a','Provide accurate information to a patient and family, colleagues and other professionals in a clear, non-judgmental, and understandable manner.',48,0,0,1,0,0),(153,NULL,'CM1.3b','Maintain clear, accurate and appropriate records of clinical encounters and plans.',48,1,0,1,0,0),(154,NULL,'CM1.3c','Effectively present verbal reports of clinical encounters and plans.',48,2,0,1,0,0),(155,NULL,'CM1.4a','Effectively identify and explore problems to be addressed from a patient encounter, including the patient\'s context, responses, concerns and preferences.',49,0,0,1,0,0),(156,NULL,'CM1.4b','Respect diversity and difference, including but not limited to the impact of gender, religion and cultural beliefs on decision making.',49,1,0,1,0,0),(157,NULL,'CM1.4c','Encourage discussion, questions and interaction in the encounter.',49,2,0,1,0,0),(158,NULL,'CM1.4d','Engage patients, families and relevant health professionals in shared decision making to develop a plan of care.',49,3,0,1,0,0),(159,NULL,'CM1.4e','Effectively address challenging communication issues such as obtaining informed consent, delivering bad news, and addressing anger, confusion and misunderstanding.',49,4,0,1,0,0),(160,NULL,'CL1.1a','Clearly describe and demonstrate their roles and responsibilities under law and other provisions, to other professionals within a variety of health care settings.',50,0,0,1,0,0),(161,NULL,'CL1.1b','Recognize and respect the diversity of roles and responsibilities of other health care professionals in a variety of settings, noting  how these roles interact with their own.',50,1,0,1,0,0),(162,NULL,'CL1.1c','Work with others to assess, plan, provide and integrate care for individual patients.',50,2,0,1,0,0),(163,NULL,'CL1.1d','Respect team ethics, including confidentiality, resource allocation and professionalism.',50,3,0,1,0,0),(164,NULL,'CL1.1e','Where appropriate, demonstrate leadership in a healthcare team.',50,4,0,1,0,0),(165,NULL,'CL1.2a','Demonstrate a respectful attitude towards other colleagues and members of an interprofessional team members in a variety of settings.',51,0,0,1,0,0),(166,NULL,'CL1.2b','Respect differences, and work to overcome misunderstandings and limitations in others, that may contribute to conflict.',51,1,0,1,0,0),(167,NULL,'CL1.2c','Recognize one\'s own differences, and work to overcome one\'s own misunderstandings and limitations that may contribute to interprofessional conflict.',51,2,0,1,0,0),(168,NULL,'CL1.2d','Reflect on successful interprofessional team function.',51,3,0,1,0,0),(169,NULL,'CL1.3a','Identify the roles of patients and their family in prevention and management of illness.',52,0,0,1,0,0),(170,NULL,'CL1.3b','Learn how to inform and involve the patient and family in decision-making and management plans.',52,1,0,1,0,0),(171,NULL,'CL1.4a','Improve teaching through advice from experts in medical education.',53,0,0,1,0,0),(172,NULL,'CL1.4b','Accept supervision and feedback.',53,1,0,1,0,0),(173,NULL,'CL1.4c','Seek learning from others.',53,2,0,1,0,0),(174,NULL,'A1.1a','Explain factors that influence health, disease, disability and access to care including non-biologic factors (cultural, psychological, sociologic, familial, economic, environmental, legal, political, spiritual needs and beliefs).',54,0,0,1,0,0),(175,NULL,'A1.1b','Describe barriers to access to care and resources.',54,1,0,1,0,0),(176,NULL,'A1.1c','Discuss health issues for special populations, including vulnerable or marginalized populations.',54,2,0,1,0,0),(177,NULL,'A1.1d','Identify principles of health policy and implications.',54,3,0,1,0,0),(178,NULL,'A1.1e','Describe health programs and interventions at the population level.',54,4,0,1,0,0),(179,NULL,'A2.1a','Identify the role of and method of access to services of community resources.',55,0,0,1,0,0),(180,NULL,'A2.1b','Describe appropriate methods of communication about community resources to and on behalf of patients.',55,1,0,1,0,0),(181,NULL,'A2.1c','Locate and analyze a variety of health communities and community health networks in the local Kingston area and beyond.',55,2,0,1,0,0),(182,NULL,'A2.2a','Describe the role and examples of physicians and medical associations in advocating collectively for health and patient safety.',56,0,0,1,0,0),(183,NULL,'A2.2b','Analyze the ethical and professional issues inherent in health advocacy, including possible conflict between roles of gatekeeper and manager.',56,1,0,1,0,0),(184,NULL,'M1.1a','Outline strategies for effective practice in a variety of health care settings, including their structure, finance and operation.',57,0,0,1,0,0),(185,NULL,'M1.1b','Outline the common law and statutory provisions which govern practice and collaboration within hospital and other settings.',57,1,0,1,0,0),(186,NULL,'M1.1c','Recognizes one\'s own personal preferences and strengths and uses this knowledge in career decisions.',57,2,0,1,0,0),(187,NULL,'M1.1d','Identify career paths within health care settings.',57,3,0,1,0,0),(188,NULL,'M2.1a','Identify and balance personal and educational priorities to foster future balance between personal health and a sustainable practice.',58,0,0,1,0,0),(189,NULL,'M2.1b','Practice personal and professional awareness, insight and acceptance of feedback and peer review;  participate in peer review.',58,1,0,1,0,0),(190,NULL,'M2.1c','Implement plans to overcome barriers to health personal and professional behavior.',58,2,0,1,0,0),(191,NULL,'M2.1d','Recognize and respond to other educational/professional colleagues in need of support.',58,3,0,1,0,0),(200,NULL,'Clinical Learning Objectives',NULL,0,1,0,1,0,0),(201,NULL,'Pain, lower limb',NULL,200,113,0,1,1257353646,1),(202,NULL,'Pain, upper limb',NULL,200,112,0,1,1257353646,1),(203,NULL,'Fracture/disl\'n',NULL,200,111,0,1,1257353646,1),(204,NULL,'Scrotal pain',NULL,200,101,0,1,1257353646,1),(205,NULL,'Blood in urine',NULL,200,100,0,1,1257353646,1),(206,NULL,'Urinary obstruction/hesitancy',NULL,200,99,0,1,1257353646,1),(207,NULL,'Nausea/vomiting',NULL,200,98,0,1,1257353646,1),(208,NULL,'Hernia',NULL,200,97,0,1,1257353646,1),(209,NULL,'Abdominal injuries',NULL,200,96,0,1,1257353646,1),(210,NULL,'Chest injuries',NULL,200,95,0,1,1257353646,1),(211,NULL,'Breast disorders',NULL,200,94,0,1,1257353646,1),(212,NULL,'Anorectal pain',NULL,200,93,0,1,1257353646,1),(213,NULL,'Blood, GI tract',NULL,200,92,0,1,1257353646,1),(214,NULL,'Abdominal distension',NULL,200,91,0,1,1257353646,1),(215,NULL,'Subs abuse/addic/wdraw',NULL,200,90,0,1,1257353646,1),(216,NULL,'Abdo pain - acute',NULL,200,89,0,1,1257353646,1),(217,NULL,'Psychosis/disord thoughts',NULL,200,88,0,1,1257353646,1),(218,NULL,'Personality disorders',NULL,200,87,0,1,1257353646,1),(219,NULL,'Panic/anxiety',NULL,200,86,0,1,1257353646,1),(221,NULL,'Mood disorders',NULL,200,84,0,1,1257353646,1),(222,NULL,'XR-Wrist/hand',NULL,200,83,0,1,1257353646,1),(223,NULL,'XR-Chest',NULL,200,82,0,1,1257353646,1),(224,NULL,'XR-Hip/pelvis',NULL,200,81,0,1,1257353646,1),(225,NULL,'XR-Ankle/foot',NULL,200,80,0,1,1257353646,1),(226,NULL,'Skin ulcers-tumors',NULL,200,79,0,1,1257353646,1),(228,NULL,'Skin wound',NULL,200,77,0,1,1257353646,1),(233,NULL,'Dyspnea, acute',NULL,200,72,0,1,1257353646,1),(234,NULL,'Infant/child nutrition',NULL,200,71,0,1,1257353646,1),(235,NULL,'Newborn assessment',NULL,200,70,0,1,1257353646,1),(236,NULL,'Rash,child',NULL,200,69,0,1,1257353646,1),(237,NULL,'Ped naus/vom/diarh',NULL,200,68,0,1,1257353646,1),(238,NULL,'Ped EM\'s-acutely ill',NULL,200,67,0,1,1257353646,1),(239,NULL,'Ped dysp/resp dstres',NULL,200,66,0,1,1257353646,1),(240,NULL,'Ped constipation',NULL,200,65,0,1,1257353646,1),(241,NULL,'Fever in a child',NULL,200,64,0,1,1257353646,1),(242,NULL,'Ear pain',NULL,200,63,0,1,1257353646,1),(257,NULL,'Prolapse',NULL,200,48,0,1,1257353646,1),(258,NULL,'Vaginal bleeding, abn',NULL,200,47,0,1,1257353646,1),(259,NULL,'Postpartum, normal',NULL,200,46,0,1,1257353646,1),(260,NULL,'Labour, normal',NULL,200,45,0,1,1257353646,1),(261,NULL,'Labour, abnormal',NULL,200,44,0,1,1257353646,1),(262,NULL,'Infertility',NULL,200,43,0,1,1257353646,1),(263,NULL,'Incontinence-urine',NULL,200,42,0,1,1257353646,1),(264,NULL,'Hypertension, preg',NULL,200,41,0,1,1257353646,1),(265,NULL,'Dysmenorrhea',NULL,200,40,0,1,1257353646,1),(266,NULL,'Contraception',NULL,200,39,0,1,1257353646,1),(267,NULL,'Antepartum care',NULL,200,38,0,1,1257353646,1),(268,NULL,'Weakness',NULL,200,37,0,1,1257353646,1),(269,NULL,'Sodium-abn',NULL,200,36,0,1,1257353646,1),(270,NULL,'Renal failure',NULL,200,35,0,1,1257353646,1),(271,NULL,'Potassium-abn',NULL,200,34,0,1,1257353646,1),(272,NULL,'Murmur',NULL,200,33,0,1,1257353646,1),(273,NULL,'Joint pain, poly',NULL,200,32,0,1,1257353646,1),(274,NULL,'Impaired LOC (coma)',NULL,200,31,0,1,1257353646,1),(275,NULL,'Hypotension',NULL,200,30,0,1,1257353646,1),(276,NULL,'Hypertension',NULL,200,29,0,1,1257353646,1),(277,NULL,'H+ concentratn, abn',NULL,200,28,0,1,1257353646,1),(278,NULL,'Fever',NULL,200,27,0,1,1257353646,1),(279,NULL,'Edema',NULL,200,26,0,1,1257353646,1),(280,NULL,'Dyspnea-chronic',NULL,200,25,0,1,1257353646,1),(281,NULL,'Diabetes mellitus',NULL,200,24,0,1,1257353646,1),(282,NULL,'Dementia',NULL,200,23,0,1,1257353646,1),(283,NULL,'Delerium/confusion',NULL,200,22,0,1,1257353646,1),(284,NULL,'Cough',NULL,200,21,0,1,1257353646,1),(286,NULL,'Anemia',NULL,200,19,0,1,1257353646,1),(287,NULL,'Chest pain',NULL,200,18,0,1,1257353646,1),(288,NULL,'Abdo pain-chronic',NULL,200,17,0,1,1257353646,1),(289,NULL,'Wk-rel\'td health iss',NULL,200,16,0,1,1257353646,1),(290,NULL,'Weight loss/gain',NULL,200,15,0,1,1257353646,1),(291,NULL,'URTI',NULL,200,14,0,1,1257353646,1),(292,NULL,'Sore throat',NULL,200,13,0,1,1257353646,1),(293,NULL,'Skin rash',NULL,200,12,0,1,1257353646,1),(294,NULL,'Pregnancy',NULL,200,11,0,1,1257353646,1),(295,NULL,'Periodic health exam',NULL,200,10,0,1,1257353646,1),(296,NULL,'Pain, spinal',NULL,200,9,0,1,1257353646,1),(299,NULL,'Headache',NULL,200,6,0,1,1257353646,1),(300,NULL,'Fatigue',NULL,200,5,0,1,1257353646,1),(303,NULL,'Dysuria/pyuria',NULL,200,2,0,1,1257353646,1),(304,NULL,'Fracture/dislocation',NULL,200,114,0,1,1261414735,1),(305,NULL,'Pain',NULL,200,115,0,1,1261414735,1),(306,NULL,'Preop Assess - anesthesiology',NULL,200,116,0,1,1261414735,1),(307,NULL,'Preop Assess - surgery',NULL,200,117,0,1,1261414735,1),(308,NULL,'Pain - spinal',NULL,200,118,0,1,1261414735,1),(309,NULL,'MCC Presentations',NULL,0,3,0,1,1265296358,1),(310,'1-E','Abdominal Distension','Abdominal distention is common and may indicate the presence of serious intra-abdominal or systemic disease.',309,1,0,1,1271174177,1),(311,'2-E','Abdominal Mass','If hernias are excluded, most other abdominal masses represent a significant underlying disease that requires complete investigation.',309,2,0,1,1271174177,1),(312,'2-1-E','Adrenal Mass','Adrenal masses are at times found incidentally after CT, MRI, or ultrasound examination done for unrelated reasons.  The incidence is about 3.5 % (almost 10 % of autopsies).',311,1,0,1,1271174178,1),(313,'2-2-E','Hepatomegaly','True hepatomegaly (enlargement of the liver with a span greater than 14 cm in adult males and greater than 12 cm in adult females) is an uncommon clinical presentation, but is important to recognize in light of potentially serious causal conditions.',311,2,0,1,1271174178,1),(314,'2-4-E','Hernia (abdominal Wall And Groin)','A hernia is defined as an abnormal protrusion of part of a viscus through its containing wall.  Hernias, in particular inguinal hernias, are very common, and thus, herniorrphaphy is a very common surgical intervention.',311,3,0,1,1271174178,1),(315,'2-3-E','Splenomegaly','Splenomegaly, an enlarged spleen detected on physical examination by palpitation or percussion at Castell\'s point, is relatively uncommon.  However, it is often associated with serious underlying pathology.',311,4,0,1,1271174178,1),(316,'3-1-E','Abdominal Pain (children)','Abdominal pain is a common complaint in children.  While the symptoms may result from serious abdominal pathology, in a large proportion of cases, an identifiable organic cause is not found.  The causes are often age dependent.',309,3,0,1,1271174178,1),(317,'3-2-E','Abdominal Pain, Acute ','Abdominal pain may result from intra-abdominal inflammation or disorders of the abdominal wall.  Pain may also be referred from sources outside the abdomen such as retroperitoneal processes as well as intra-thoracic processes.  Thorough clinical evaluation is the most important \"test\" in the diagnosis of abdominal pain.',309,4,0,1,1271174178,1),(318,'3-4-E','Abdominal Pain, Anorectal','While almost all causes of anal pain are treatable, some can be destructive locally if left untreated.',309,5,0,1,1271174178,1),(319,'3-3-E','Abdominal Pain, Chronic','Chronic and recurrent abdominal pain, including heartburn or dyspepsia is a common symptom (20 - 40 % of adults) with an extensive differential diagnosis and heterogeneous pathophysiology.  The history and physical examination frequently differentiate between functional and more serious underlying diseases.',309,6,0,1,1271174178,1),(320,'4-E','Allergic Reactions/food Allergies Intolerance/atopy','Allergic reactions are considered together despite the fact that they exhibit a variety of clinical responses and are considered separately under the appropriate presentation.  The rationale for considering them together is that in some patients with a single response (e.g., atopic dermatitis), other atopic disorders such as asthma or allergic rhinitis may occur at other times.  Moreover, 50% of patients with atopic dermatitis report a family history of respiratory atopy. ',309,7,0,1,1271174178,1),(321,'5-E','Attention Deficit/hyperactivity Disorder (adhd)/learning Dis','Family physicians at times are the initial caregivers to be confronted by developmental and behavioural problems of childhood and adolescence (5 - 10% of school-aged population).  Lengthy waiting lists for specialists together with the urgent plight of patients often force primary-care physicians to care for these children.',309,8,0,1,1271174178,1),(322,'6-E','Blood From Gastrointestinal Tract','Both upper and lower gastrointestinal bleeding are common and may be life-threatening.  Upper intestinal bleeding usually presents with hematemesis (blood or coffee-ground material) and/or melena (black, tarry stools).  Lower intestinal bleeding usually manifests itself as hematochezia (bright red blood or dark red blood or clots per rectum).  Unfortunately, this difference is not consistent. Melena may be seen in patients with colorectal or small bowel bleeding, and hematochezia may be seen with massive upper gastrointestinal bleeding.  Occult bleeding from the gastrointestinal tract may also be identified by positive stool for occult blood or the presence of iron deficiency anemia.',309,9,0,1,1271174178,1),(323,'6-2-E','Blood From Gastrointestinal Tract, Lower/hematochezia','Although lower gastrointestinal bleeding (blood originating distal to ligament of Treitz) or hematochezia is less common than upper (20% vs. 80%), it is associated with 10 -20% morbidity and mortality since it usually occurs in the elderly.  Early identification of colorectal cancer is important in preventing cancer-related morbidity and mortality (colorectal cancer is second only to lung cancer as a cause of cancer-related death). ',322,1,0,1,1271174178,1),(324,'6-1-E','Blood From Gastrointestinal Tract, Upper/hematemesis','Although at times self-limited, upper GI bleeding always warrants careful and urgent evaluation, investigation, and treatment.  The urgency of treatment and the nature of resuscitation depend on the amount of blood loss, the likely cause of the bleeding, and the underlying health of the patient.',322,2,0,1,1271174178,1),(325,'7-E','Blood In Sputum (hemoptysis/prevention Of Lung Cancer)','Expectoration of blood can range from blood streaking of sputum to massive hemoptysis (&gt;200 ml/d) that may be acutely life threatening.  Bleeding usually starts and stops unpredictably, but under certain circumstances may require immediate establishment of an airway and control of the bleeding.',309,10,0,1,1271174178,1),(326,'8-E','Blood In Urine (hematuria)','Urinalysis is a screening procedure for insurance and routine examinations.  Persistent hematuria implies the presence of conditions ranging from benign to malignant.',309,11,0,1,1271174178,1),(327,'9-1-E','Hypertension','Hypertension is a common condition that usually presents with a modest elevation in either systolic or diastolic blood pressure.  Under such circumstances, the diagnosis of hypertension is made only after three separate properly measured blood pressures.  Appropriate investigation and management of hypertension is expected to improve health outcomes.',309,12,0,1,1271174178,1),(328,'9-1-1-E','Hypertension In Childhood','The prevalence of hypertension in children is&lt;1 %, but often results from identifiable causes (usually renal or vascular).  Consequently, vigorous investigation is warranted.',327,1,0,1,1271174178,1),(329,'9-1-2-E','Hypertension In The Elderly','Elderly patients (&gt;65 years) have hypertension much more commonly than younger patients do, especially systolic hypertension.  The prevalence of hypertension among the elderly may reach 60 -80 %.',327,2,0,1,1271174178,1),(330,'9-1-3-E','Malignant Hypertension','Malignant hypertension and hypertensive encephalopathies are two life-threatening syndromes caused by marked elevation in blood pressure.',327,3,0,1,1271174178,1),(331,'9-1-4-E','Pregnancy Associated Hypertension','Ten to 20 % of pregnancies are associated with hypertension.  Chronic hypertension complicates&lt;5%, preeclampsia occurs in slightly&gt;6%, and gestational hypertension arises in 6% of pregnant women.  Preeclampsia is potentially serious, but can be managed by treatment of hypertension and \'cured\' by delivery of the fetus.',327,4,0,1,1271174178,1),(332,'9-2-E','Hypotension/shock','All physicians must deal with life-threatening emergencies.  Regardless of underlying cause, certain general measures are usually indicated (investigations and therapeutic interventions) that can be life saving.',309,13,0,1,1271174178,1),(333,'9-2-1-E','Anaphylaxis','Anaphylaxis causes about 50 fatalities per year, and occurs in 1/5000-hospital admissions in Canada.  Children most commonly are allergic to foods.',332,1,0,1,1271174178,1),(334,'10-1-E','Breast Lump/screening','Complaints of breast lumps are common, and breast cancer is the most common cancer in women.  Thus, all breast complaints need to be pursued to resolution.  Screening women 50 - 69 years with annual mammography improves survival. ',309,14,0,1,1271174178,1),(335,'10-2-E','Galactorrhea/discharge','Although noticeable breast secretions are normal in&gt;50 % of reproductive age women, spontaneous persistent galactorrhea may reflect underlying disease and requires investigation.',309,15,0,1,1271174178,1),(336,'10-3-E','Gynecomastia','Although a definite etiology for gynecomastia is found in&lt;50% of patients, a careful drug history is important so that a treatable cause is detected.  The underlying feature is an increased estrogen to androgen ratio.',309,16,0,1,1271174178,1),(337,'11-E','Burns','Burns are relatively common and range from minor cutaneous wounds to major life-threatening traumas.  An understanding of the patho-physiology and treatment of burns and the metabolic and wound healing response will enable physicians to effectively assess and treat these injuries.',309,17,0,1,1271174178,1),(338,'12-1-E','Hypercalcemia','Hypercalcemia may be associated with an excess of calcium in both extracellular fluid and bone (e.g., increased intestinal absorption), or with a localised or generalised deficit of calcium in bone (e.g., increased bone resorption).  This differentiation by physicians is important for both diagnostic and management reasons.',309,18,0,1,1271174178,1),(339,'12-4-E','Hyperphosphatemia','Acute severe hyperphosphatemia can be life threatening.',309,19,0,1,1271174178,1),(340,'12-2-E','Hypocalcemia','Tetany, seizures, and papilledema may occur in patients who develop hypocalcemia acutely.',309,20,0,1,1271174178,1),(341,'12-3-E','Hypophosphatemia/fanconi Syndrome','Of hospitalised patients, 10-15% develop hypophosphatemia, and a small proportion have sufficiently profound depletion to lead to complications (e.g., rhabdomyolysis).',309,21,0,1,1271174178,1),(342,'13-E','Cardiac Arrest','All physicians are expected to attempt resuscitation of an individual with cardiac arrest. In the community, cardiac arrest most commonly is caused by ventricular fibrillation. However, heart rhythm at clinical presentation in many cases is unknown.  As a consequence, operational criteria for cardiac arrest do not rely on heart rhythm but focus on the presumed sudden pulse-less condition and the absence of evidence of a non-cardiac condition as the cause of the arrest.',309,22,0,1,1271174178,1),(343,'14-E','Chest Discomfort/pain/angina Pectoris','Chest pain in the primary care setting, although potentially severe and disabling, is more commonly of benign etiology.  The correct diagnosis requires a cost-effective approach.  Although coronary heart disease primarily occurs in patients over the age of 40, younger men and women can be affected (it is estimated that advanced lesions are present in 20% of men and 8% of women aged 30 to 34).  Physicians must recognise the manifestations of coronary artery disease and assess coronary risk factors.  Modifications of risk factors should be recommended as necessary.',309,23,0,1,1271174178,1),(344,'15-1-E','Bleeding Tendency/bruising','A bleeding tendency (excessive, delayed, or spontaneous bleeding) may signify serious underlying disease.  In children or infants, suspicion of a bleeding disorder may be a family history of susceptibility to bleeding.  An organised approach to this problem is essential.  Urgent management may be required.',309,24,0,1,1271174178,1),(345,'15-2-E','Hypercoagulable State','Patients may present with venous thrombosis and on occasion with pulmonary embolism. A risk factor for thrombosis can now be identified in over 80% of such patients.',309,25,0,1,1271174178,1),(346,'16-1-E',' Adult Constipation','Constipation is common in Western society, but frequency depends on patient and physician\'s definition of the problem.  One definition is straining, incomplete evacuation, sense of blockade, manual maneuvers, and hard stools at least 25% of the time along with&lt;3 stools/week for at least 12 weeks (need not be consecutive).  The prevalence of chronic constipation rises with age. In patients&gt;65 years, almost 1/3 complain of constipation.',309,26,0,1,1271174178,1),(347,'16-2-E','Pediatric Constipation','Constipation is a common problem in children.  It is important to differentiate functional from organic causes in order to develop appropriate management plans.',309,27,0,1,1271174178,1),(348,'17-E','Contraception','Ideally, the prevention of an unwanted pregnancy should be directed at education of patients, male and female, preferably before first sexual contact.  Counselling patients about which method to use, how, and when is a must for anyone involved in health care.',309,28,0,1,1271174178,1),(349,'18-E','Cough','Chronic cough is the fifth most common symptom for which patients seek medical advice.  Assessment of chronic cough must be thorough.  Patients with benign causes for their cough (gastro-esophageal reflux, post-nasal drip, two of the commonest causes) can often be effectively and easily managed.  Patients with more serious causes for their cough (e.g., asthma, the other common cause of chronic cough) require full investigation and management is more complex.',309,29,0,1,1271174178,1),(350,'19-E','Cyanosis/hypoxemia/hypoxia','Cyanosis is the physical sign indicative of excessive concentration of reduced hemoglobin in the blood, but at times is difficult to detect (it must be sought carefully, under proper lighting conditions).  Hypoxemia (low partial pressure of oxygen in blood), when detected, may be reversible with oxygen therapy after which the underlying cause requires diagnosis and management.',309,30,0,1,1271174178,1),(351,'19-1-E','Cyanosis/hypoxemia/hypoxia In Children','Evaluation of the patient with cyanosis depends on the age of the child.  It is an ominous finding and differentiation between peripheral and central is essential in order to mount appropriate management.',350,1,0,1,1271174178,1),(352,'20-E','Deformity/limp/pain In Lower Extremity, Child','\'Limp\' is a bumpy, rough, or strenuous way of walking, usually caused by weakness, pain, or deformity.  Although usually caused by benign conditions, at times it may be life or limb threatening. ',309,31,0,1,1271174178,1),(353,'21-E','Development Disorder/developmental Delay','Providing that normal development and behavior is readily recognized, primary care physicians will at times be the first physicians in a position to assess development in an infant, and recognize abnormal delay and/or atypical development.  Developmental surveillance and direct developmental screening of children, especially those with predisposing risks, will then be an integral part of health care.',309,32,0,1,1271174178,1),(354,'22-1-E','Acute Diarrhea','Diarrheal diseases are extremely common worldwide, and even in North America morbidity and mortality is significant.  One of the challenges for a physician faced with a patient with acute diarrhea is to know when to investigate and initiate treatment and when to simply wait for a self-limiting condition to run its course.',309,33,0,1,1271174178,1),(355,'22-2-E','Chronic Diarrhea','Chronic diarrhea is a decrease in fecal consistency lasting for 4 or more weeks.  It affects about 5% of the population.',309,34,0,1,1271174178,1),(356,'22-3-E','Pediatric Diarrhea','Diarrhea is defined as frequent, watery stools and is a common problem in infants and children.  In most cases, it is mild and self-limited, but the potential for hypovolemia (reduced effective arterial/extracellular volume) and dehydration (water loss in excess of solute) leading to electrolyte abnormalities is great.  These complications in turn may lead to significant morbidity or even mortality.',309,35,0,1,1271174178,1),(357,'23-E','Diplopia','Diplopia is the major symptom associated with dysfunction of extra-ocular muscles or abnormalities of the motor nerves innervating these muscles.  Monocular diplopia is almost always indicative of relatively benign optical problems whereas binocular diplopia is due to ocular misalignment.  Once restrictive disease or myasthenia gravis is excluded, the major cause of binocular diplopia is a cranial nerve lesion.  Careful clinical assessment will enable diagnosis in most, and suggest appropriate investigation and management.',309,36,0,1,1271174178,1),(358,'24-E','Dizziness/vertigo','\"Dizziness\" is a common but imprecise complaint.  Physicians need to determine whether it refers to true vertigo, \'dizziness\', disequilibrium, or pre-syncope/ lightheadedness. ',309,37,0,1,1271174178,1),(359,'25-E','Dying Patient/bereavement','Physicians are frequently faced with patients dying from incurable or untreatable diseases. In such circumstances, the important role of the physician is to alleviate any suffering by the patient and to provide comfort and compassion to both patient and family. ',309,38,0,1,1271174178,1),(360,'26-E','Dysphagia/difficulty Swallowing','Dysphagia should be regarded as a danger signal that indicates the need to evaluate and define the cause of the swallowing difficulty and thereafter initiate or refer for treatment.',309,39,0,1,1271174178,1),(361,'27-E','Dyspnea','Dyspnea is common and distresses millions of patients with pulmonary disease and myocardial dysfunction.  Assessment of the manner dyspnea is described by patients suggests that their description may provide insight into the underlying pathophysiology of the disease.',309,40,0,1,1271174178,1),(362,'27-1-E','Acute Dyspnea (minutes To Hours)','Shortness of breath occurring over minutes to hours is caused by a relatively small number of conditions.  Attention to clinical information and consideration of these conditions can lead to an accurate diagnosis.  Diagnosis permits initiation of therapy that can limit associated morbidity and mortality.',361,1,0,1,1271174178,1),(363,'27-2-E','Chronic Dyspnea (weeks To Months)','Since patients with acute dyspnea require more immediate evaluation and treatment, it is important to differentiate them from those with chronic dyspnea.  However, chronic dyspnea etiology may be harder to elucidate.  Usually patients have cardio-pulmonary disease, but symptoms may be out of proportion to the demonstrable impairment.',361,2,0,1,1271174178,1),(364,'27-3-E','Pediatric Dyspnea/respiratory Distress','After fever, respiratory distress is one of the commonest pediatric emergency complaints.',361,3,0,1,1271174178,1),(365,'28-E','Ear Pain','The cause of ear pain is often otologic, but it may be referred.  In febrile young children, who most frequently are affected by ear infections, if unable to describe the pain, a good otologic exam is crucial. (see also <a href=\"objectives.pl?lang=english&amp;loc=obj&amp;id=40-E\" title=\"Presentation 40-E\">Hearing Loss/Deafness)',309,41,0,1,1271174178,1),(366,'29-1-E',' Generalized Edema','Patients frequently complain of swelling.  On closer scrutiny, such swelling often represents expansion of the interstitial fluid volume.  At times the swelling may be caused by relatively benign conditions, but at times serious underlying diseases may be present.',309,42,0,1,1271174178,1),(367,'29-2-E',' Unilateral/local Edema','Over 90 % of cases of acute pulmonary embolism are due to emboli emanating from the proximal veins of the lower extremities.',309,43,0,1,1271174178,1),(368,'30-E','Eye Redness','Red eye is a very common complaint.  Despite the rather lengthy list of causal conditions, three problems make up the vast majority of causes: conjunctivitis (most common), foreign body, and iritis.  Other types of injury are relatively less common, but important because excessive manipulation may cause further damage or even loss of vision.',309,44,0,1,1271174178,1),(369,'31-1-E','Failure To Thrive, Elderly ','Failure to thrive for an elderly person means the loss of energy, vigor and/or weight often accompanied by a decline in the ability to function and at times associated with depression.',309,45,0,1,1271174178,1),(370,'31-2-E','Failure To Thrive, Infant/child','Failure to thrive is a phrase that describes the occurrence of growth failure in either height or weight in childhood.  Since failure to thrive is attributed to children&lt;2 years whose weight is below the 5th percentile for age on more than one occasion, it is essential to differentiate normal from the abnormal growth patterns.',309,46,0,1,1271174178,1),(371,'32-E','Falls','Falls are common (&gt;1/3 of people over 65 years; 80% among those with?4 risk factors) and 1 in 10 are associated with serious injury such as hip fracture, subdural hematoma, or head injury.  Many are preventable.  Interventions that prevent falls and their sequelae delay or reduce the frequency of nursing home admissions.',309,47,0,1,1271174178,1),(372,'33-E','Fatigue ','In a primary care setting, 20-30% of patients will report significant fatigue (usually not associated with organic cause).  Fatigue&lt;1 month is \'recent\';&gt;6 months, it is \'chronic\'.',309,48,0,1,1271174178,1),(373,'34-E','Fractures/dislocations ','Fractures and dislocations are common problems at any age and are related to high-energy injuries (e.g., motor accidents, sport injuries) or, at the other end of the spectrum, simple injuries such as falls or non-accidental injuries.  They require initial management by primary care physicians with referral for difficult cases to specialists.',309,49,0,1,1271174178,1),(374,'35-E','Gait Disturbances/ataxia ','Abnormalities of gait can result from disorders affecting several levels of the nervous system and the type of abnormality observed clinically often indicates the site affected.',309,50,0,1,1271174178,1),(375,'36-E','Genetic Concerns','Genetics have increased our understanding of the origin of many diseases.  Parents with a family history of birth defects or a previously affected child need to know that they are at higher risk of having a baby with an anomaly.  Not infrequently, patients considering becoming parents seek medical advice because of concerns they might have.  Primary care physicians must provide counseling about risk factors such as maternal age, illness, drug use, exposure to infectious or environmental agents, etc. and if necessary referral if further evaluation is necessary.',309,51,0,1,1271174178,1),(376,'36-1-E','Ambiguous Genitalia','Genetic males with 46, XY genotype but having impaired androgen sensitivity of varying severity may present with features that range from phenotypic females to \'normal\' males with only minor defects in masculinization or infertility.  Primary care physicians may be called upon to determine the nature of the problem.',375,1,0,1,1271174178,1),(377,'36-2-E','Dysmorphic Features','Three out of 100 infants are born with a genetic disorder or congenital defect.  Many of these are associated with long-term disability, making early detection and identification vital.  Although early involvement of genetic specialists in the care of such children is prudent, primary care physicians are at times required to contribute immediate care, and subsequently assist with long term management of suctients.',375,2,0,1,1271174178,1),(378,'37-1-E','Hyperglycemia/diabetes Mellitus','Diabetes mellitus is a very common disorder associated with a relative or absolute impairment of insulin secretion together with varying degrees of peripheral resistance to the action of insulin.  The morbidity and mortality associated with diabetic complications may be reduced by preventive measures.  Intensive glycemic control will reduce neonatal complications and reduce congenital malformations in pregnancy diabetes.',309,52,0,1,1271174178,1),(379,'37-2-E','Hypoglycemia','Maintenance of the blood sugar within normal limits is essential for health.  In the short-term, hypoglycemia is much more dangerous than hyperglycemia.  Fortunately, it is an uncommon clinical problem outside of therapy for diabetes mellitus. ',309,53,0,1,1271174178,1),(380,'38-1-E','Alopecia ','Although in themselves hair changes may be innocuous, they can be psychologically unbearable.  Frequently they may provide significant diagnostic hints of underlying disease.',309,54,0,1,1271174178,1),(381,'38-2-E','Nail Complaints ','Nail disorders (toenails more than fingernails), especially ingrown, infected, and painful nails, are common conditions.  Local nail problems may be acute or chronic.  Relatively simple treatment can prevent or alleviate symptoms.  Although in themselves nail changes may be innocuous, they frequently provide significant diagnostic hints of underlying disease.',309,55,0,1,1271174178,1),(382,'39-E','Headache','The differentiation of patients with headaches due to serious or life-threatening conditions from those with benign primary headache disorders (e.g., tension headaches or migraine) is an important diagnostic challenge.',309,56,0,1,1271174178,1),(383,'40-E','Hearing Loss/deafness ','Many hearing loss causes are short-lived, treatable, and/or preventable.  In the elderly, more permanent sensorineural loss occurs.  In pediatrics, otitis media accounts for 25% of office visits.  Adults/older children have otitis less commonly, but may be affected by sequelae of otitis.',309,57,0,1,1271174178,1),(384,'41-E','Hemiplegia/hemisensory Loss +/- Aphasia','Hemiplegia/hemisensory loss results from an upper motor neuron lesion above the mid-cervical spinal cord.  The concomitant finding of aphasia is diagnostic of a dominant cerebral hemisphere lesion.  Acute hemiplegia generally heralds the onset of serious medical conditions, usually of vascular origin, that at times are effectively treated by advanced medical and surgical techniques.</p>\r\n<p>If the sudden onset of focal neurologic symptoms and/or signs lasts&lt;24 hours, presumably it was caused by a transient decrease in blood supply rendering the brain ischemic but with blood flow restoration timely enough to avoid infarction.  This definition of transient ischemic attacks (TIA) is now recognized to be inadequate.  ',309,58,0,1,1271174178,1),(385,'42-1-E','Anemia','The diagnosis in a patient with anemia can be complex.  An unfocused or unstructured investigation of anemia can be costly and inefficient.  Simple tests may provide important information.  Anemia may be the sole manifestation of serious medical disease.',309,59,0,1,1271174178,1),(386,'42-2-E','Polycythemia/elevated Hemoglobin','The reason for evaluating patients with elevated hemoglobin levels (male 185 g/L, female 165 g/L) is to ascertain the presence or absence of polycythemia vera first, and subsequently to differentiate between the various causes of secondary erythrocytosis.',309,60,0,1,1271174178,1),(387,'43-E','Hirsutism/virilization','Hirsutism, terminal body hair where unusual (face, chest, abdomen, back), is a common problem, particularly in dark-haired, darkly pigmented, white women.  However, if accompanied by virilization, then a full diagnostic evaluation is essential because it is androgen-dependent.  Hypertrichosis on the other hand is a rare condition usually caused by drugs or systemic illness.',309,61,0,1,1271174178,1),(388,'44-E','Hoarseness/dysphonia/speech And Language Abnormalities','Patients with impairment in comprehension and/or use of the form, content, or function of language are said to have a language disorder.  Those who have correct word choice and syntax but have speech disorders may have an articulation disorder.  Almost any change in voice quality may be described as hoarseness.  However, if it lasts more than 2 weeks, especially in patients who use alcohol or tobacco, it needs to be evaluated.',309,62,0,1,1271174178,1),(389,'45-E','Hydrogen Ion Concentration Abnormal, Serum','Major adverse consequences may occur with severe acidemia and alkalemia despite absence of specific symptoms.  The diagnosis depends on the clinical setting and laboratory studies.  It is crucial to distinguish acidemia due to metabolic causes from that due to respiratory causes; especially important is detecting the presence of both.  Management of the underlying causes and not simply of the change in [H+] is essential.',309,63,0,1,1271174178,1),(390,'46-E','Infertility','Infertility, meaning the inability to conceive after one year of intercourse without contraception, affects about 15% of couples.  Both partners must be investigated; male-associated factors account for approximately half of infertility problems.  Although current emphasis is on treatment technologies, it is important to consider first the cause of the infertility and tailor the treatment accordingly.',309,64,0,1,1271174178,1),(391,'47-1-E','Incontinence, Stool','Fecal incontinence varies from inadvertent soiling with liquid stool to the involuntary excretion of feces.  It is a demoralizing disability because it affects self-assurance and can lead to social isolation.  It is the second leading cause of nursing home placement.',309,65,0,1,1271174178,1),(392,'47-2-E','Incontinence, Urine','Because there is increasing incidence of involuntary micturition with age, incontinence has increased in frequency in our ageing population.  Unfortunately, incontinence remains under treated despite its effect on quality of life and impact on physical and psychological morbidity.  Primary care physicians should diagnose the cause of incontinence in the majority of cases.',309,66,0,1,1271174178,1),(393,'47-3-E','Incontinence, Urine, Pediatric (enuresis)','Enuresis is the involuntary passage of urine, and may be diurnal (daytime), nocturnal (nighttime), or both.  The majority of children have primary nocturnal enuresis (20% of five-year-olds).  Diurnal and secondary enuresis is much less common, but requires differentiating between underlying diseases and stress related conditions.',309,67,0,1,1271174178,1),(394,'48-E','Impotence/erectile Dysfunction','Impotence is an issue that has a major impact on relationships.  There is a need to explore the impact with both partners, although many consider it a male problem.  Impotence is present when an erection of sufficient rigidity for sexual intercourse cannot be acquired or sustained&gt;75% of the time.',309,68,0,1,1271174178,1),(395,'49-E','Jaundice ','Jaundice may represent hemolysis or hepatobiliary disease.  Although usually the evaluation of a patient is not urgent, in a few situations it is a medical emergency (e.g., massive hemolysis, ascending cholangitis, acute hepatic failure).',309,69,0,1,1271174178,1),(396,'49-1-E','Neonatal Jaundice ','Jaundice, usually mild unconjugated bilirubinemia, affects nearly all newborns.  Up to 65% of full-term neonates have jaundice at 72 - 96 hours of age.  Although some causes are ominous, the majority are transient and without consequences.',395,1,0,1,1271174178,1),(397,'50-1-E','Joint Pain, Mono-articular (acute, Chronic)','Any arthritis can initially present as one swollen painful joint.  Thus, the early exclusion of polyarticular joint disease may be challenging.  In addition, pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.',309,70,0,1,1271174178,1),(398,'50-2-E','Joint Pain, Poly-articular (acute, Chronic)','Polyarticular joint pain is common in medical practice, and causes vary from some that are self-limiting to others which are potentially disabling and life threatening.',309,71,0,1,1271174178,1),(399,'50-3-E','Periarticular Pain/soft Tissue Rheumatic Disorders','Pain caused by a problem within the joint needs to be distinguished from pain arising from surrounding soft tissues.',309,72,0,1,1271174178,1),(400,'51-E','Lipids Abnormal, Serum ','Hypercholesterolemia is a common and important modifiable risk factor for ischemic heart disease (IHD) and cerebro-vascular disease.  The relationship of elevated triglycerides to IHD is less clear (may be a modest independent predictor) but very high levels predispose to pancreatitis.  HDL cholesterol is inversely related to IHD risk.',309,73,0,1,1271174178,1),(401,'52-E','Liver Function Tests Abnormal, Serum','Appropriate investigation can distinguish benign reversible liver disease requiring no treatment from potentially life-threatening conditions requiring immediate therapy.',309,74,0,1,1271174178,1),(402,'53-E','Lump/mass, Musculoskeletal ','Lumps or masses are a common cause for consultation with a physician.  The majority will be of a benign dermatologic origin. Musculoskeletal lumps or masses are not common, but they represent an important cause of morbidity and mortality, especially among young people.',309,75,0,1,1271174178,1),(403,'54-E','Lymphadenopathy','Countless potential causes may lead to lymphadenopathy.  Some of these are serious but treatable.  In a study of patients with lymphadenopathy, 84% were diagnosed with benign lymphadenopathy and the majority of these were due to a nonspecific (reactive) etiology.',309,76,0,1,1271174178,1),(404,'54-1-E','Mediastinal Mass/hilar Adenopathy','The mediastinum contains many vital structures (heart, aorta, pulmonary hila, esophagus) that are affected directly or indirectly by mediastinal masses.  Evaluation of such masses is aided by envisaging the nature of the mass from its location in the mediastinum.</p>\r\n<p>',403,1,0,1,1271174178,1),(405,'55-E','Magnesium Concentration Serum, Abnormal/hypomagnesemia ','Although hypomagnesemia occurs in only about 10% of hospitalized patients, the incidence rises to over 60% in severely ill patients.  It is frequently associated with hypokalemia and hypocalcemia.',309,77,0,1,1271174178,1),(406,'56-1-E','Amenorrhea/oligomenorrhea','The average age of onset of menarche in North America is 11 to 13 years and menopause is approximately 50 years.  Between these ages, absence of menstruation is a cause for investigation and appropriate management.',309,78,0,1,1271174178,1),(407,'56-2-E','Dysmenorrhea','Approximately 30 - 50% of post-pubescent women experience painful menstruation and 10% of women are incapacitated by pain 1 - 3 days per month.  It is the single greatest cause of lost working hours and school days among young women.',309,79,0,1,1271174178,1),(408,'56-3-E','Pre-menstrual Syndrome (pms)','Pre-menstrual syndrome is a combination of physical, emotional, or behavioral symptoms that occur prior to the menstrual cycle and are absent during the rest of the cycle.  The symproms, on occasion, are severe enough to intefere significantly with work and/or home activities.',309,80,0,1,1271174178,1),(409,'57-E','Menopause ','Women cease to have menstrual periods at about 50 years of age, although ovarian function declines earlier.  Changing population demographics means that the number of women who are menopausal will continue to grow, and many women will live 1/3 of their lives after ovarian function ceases.  Promotion of health maintenance in this group of women will enhance physical, emotional, and sexual quality of life.',309,81,0,1,1271174178,1),(410,'58-1-E','Coma','Patients with altered level of consciousness account for 5% of hospital admissions.  Coma however is defined as a state of pathologic unconsciousness (unarousable).',309,82,0,1,1271174178,1),(411,'58-2-E','Delirium/confusion ','An acute confusional state in patients with medical illness, especially among those who are older, is extremely common.  Between 10 - 15% of elderly patients admitted to hospital have delirium and up to a further 30% develop delirium while in hospital.  It represents a disturbance of consciousness with reduced ability to focus, sustain, or shift attention (DSM-IV).  This disturbance tends to develop over a short period of time (hours to days) and tends to fluctuate during the course of the day.  A clear understanding of the differential diagnosis enables rapid and appropriate management.',309,83,0,1,1271174178,1),(412,'58-3-E','Dementia','Dementia is a problem physicians encounter frequently, and causes that are potentially treatable require identification.  Alzheimer disease is the most common form of dementia in the elderly (about 70%), and primary care physicians will need to diagnose and manage the early cognitive manifestations.',309,84,0,1,1271174178,1),(413,'59-E','Mood Disorders ','Depression is one of the top five diagnoses made in the offices of primary care physicians.  Depressed mood occurs in some individuals as a normal reaction to grief, but in others it is considered abnormal because it interferes with the person\'s daily function (e.g., self-care, relationships, work, self-support).  Thus, it is necessary for primary care clinicians to detect depression, initiate treatment, and refer to specialists for assistance when required.',309,85,0,1,1271174178,1),(414,'60-E','Mouth Problems','Although many disease states can affect the mouth, the two most common ones are odontogenic infections (dental carries and periodontal infections) and oral carcinoma. Almost 15% of the population have significant periodontal disease despite its being preventable.  Such infections, apart from the discomfort inflicted, may result in serious complications.',309,86,0,1,1271174178,1),(415,'61-E','Movement Disorders,involuntary/tic Disorders','Movement disorders are regarded as either excessive (hyperkinetic) or reduced (bradykinetic) activity.  Diagnosis depends primarily on careful observation of the clinical features. ',309,87,0,1,1271174178,1),(416,'62-1-E','Diastolic Murmur','Although systolic murmurs are often \"innocent\" or physiological, diastolic murmurs are virtually always pathologic.',309,88,0,1,1271174178,1),(417,'62-2-E','Heart Sounds, Pathological','Pathological heart sounds are clues to underlying heart disease.',309,89,0,1,1271174178,1),(418,'62-3-E','Systolic Murmur','Ejection systolic murmurs are common, and frequently quite \'innocent\' (with absence of cardiac findings and normal splitting of the second sound).',309,90,0,1,1271174178,1),(419,'63-E','Neck Mass/goiter/thyroid Disease ','The vast majority of neck lumps are benign (usually reactive lymph nodes or occasionally of congenital origin).  The lumps that should be of most concern to primary care physicians are the rare malignant neck lumps.  Among patients with thyroid nodules, children, patients with a family history or history for head and neck radiation, and adults&lt;30 years or&gt;60 years are at higher risk for thyroid cancer.',309,91,0,1,1271174178,1),(420,'64-E','Newborn, Depressed','A call requesting assistance in the delivery of a newborn may be \"routine\" or because the neonate is depressed and requires resuscitation.  For any type of call, the physician needs to be prepared to manage potential problems.',309,92,0,1,1271174178,1),(421,'65-E','Non-reassuring Fetal Status (fetal Distress)','Non-reassuring fetal status occurs in 5 - 10% of pregnancies.  (Fetal distress, a term also used, is imprecise and has a low positive predictive value.  The newer term should be used.)  Early detection and pro-active management can reduce serious consequences and prepare parents for eventualities.',309,93,0,1,1271174178,1),(422,'66-E','Numbness/tingling/altered Sensation','Disordered sensation may be alarming and highly intrusive.  The physician requires a framework of knowledge in order to assess abnormal sensation, consider the likely site of origin, and recognise the implications.',309,94,0,1,1271174178,1),(423,'67-E','Pain','Because pain is considered a signal of disease, it is the most common symptom that brings a patient to a physician.  Acute pain is a vital protective mechanism.  In contrast, chronic pain (&gt;6 weeks or lasting beyond the ordinary duration of time that an injury needs to heal) serves no physiologic role and is itself a disease state.  Pain is an unpleasant somatic sensation, but it is also an emotion.  Although control of pain/discomfort is a crucial endpoint of medical care, the degree of analgesia provided is often inadequate, and may lead to complications (e.g., depression, suicide).  Physicians should recognise the development and progression of pain, and develop strategies for its control.',309,95,0,1,1271174178,1),(424,'67-1-2-1-E',' Generalized Pain Disorders','Fibromyalgia, a common cause of chronic musculoskeletal pain and fatigue, has no known etiology and is not associated with tissue inflammation.  It affects muscles, tendons, and ligaments.  Along with a group of similar conditions, fibromyalgia is controversial because obvious sign and laboratory/radiological abnormalities are lacking.</p>\r\n<p>Polymyalgia rheumatica, a rheumatic condition frequently linked to giant cell (temporal) arteritis, is a relatively common disorder (prevalence of about 700/100,000 persons over 50 years of age).  Synovitis is considered to be the cause of the discomfort.',423,1,0,1,1271174178,1),(425,'67-1-2-3-E','Local Pain, Hip/knee/ankle/foot','With the current interest in physical activity, the commonest cause of leg pain is muscular or ligamentous strain.  The knee, the most intricate joint in the body, has the greatest susceptibility to pain.',423,2,0,1,1271174178,1),(426,'67-1-2-2-E','Local Pain, Shoulder/elbow/wrist/hand','After backache, upper extremity pain is the most common type of musculoskeletal pain.',423,3,0,1,1271174178,1),(427,'67-1-2-4-E','Local Pain, Spinal Compression/osteoporosis','Spinal compression is one manifestation of osteoporosis, the prevalence of which increases with age.  As the proportion of our population in old age rises, osteoporosis becomes an important cause of painful fractures, deformity, loss of mobility and independence, and even death.  Although less common in men, the incidence of fractures increases exponentially with ageing, albeit 5 - 10 years later.  For unknown reasons, the mortality associated with fractures is higher in men than in women.',423,4,0,1,1271174178,1),(428,'67-1-2-6-E','Local Pain, Spine/low Back Pain','Low back pain is one of the most common physical complaints and a major cause of lost work time.  Most frequently it is associated with vocations that involve lifting, twisting, bending, and reaching.  In individuals suffering from chronic back pain, 5% will have an underlying serious disease.',423,5,0,1,1271174178,1),(429,'67-1-2-5-E','Local Pain, Spine/neck/thoracic','Approximately 10 % of the adult population have neck pain at any one time.  This prevalence is similar to low back pain, but few patients lose time from work and the development of neurologic deficits is&lt;1 %.',423,6,0,1,1271174178,1),(430,'67-2-2-E','Central/peripheral Neuropathic Pain','Neuropathic pain is caused by dysfunction of the nervous system without tissue damage.  The pain tends to be chronic and causes great discomfort.',423,7,0,1,1271174178,1),(431,'67-2-1-E','Sympathetic/complex Regional Pain Syndrome/reflex Sympatheti','Following an injury or vascular event (myocardial infarction, stroke), a disorder may develop that is characterized by regional pain and sensory changes (vasomotor instability, skin changes, and patchy bone demineralization).',423,8,0,1,1271174178,1),(432,'68-E','Palpitations (abnormal Ecg-arrhythmia)','Palpitations are a common symptom.  Although the cause is often benign, occasionally it may indicate the presence of a serious underlying problem.',309,96,0,1,1271174178,1),(433,'69-E','Panic And Anxiety ','Panic attacks/panic disorders are common problems in the primary care setting.  Although such patients may present with discrete episodes of intense fear, more commonly they complain of one or more physical symptoms.  A minority of such patients present to mental health settings, whereas 1/3 present to their family physician and another 1/3 to emergency departments.  Generalized anxiety disorder, characterized by excessive worry and anxiety that are difficult to control, tends to develop secondary to other psychiatric conditions.',309,97,0,1,1271174178,1),(434,'70-E','Pap Smear Screening','Carcinoma of the cervix is a preventable disease.  Any female patient who visits a physician\'s office should have current screening guidelines applied and if appropriate, a Pap smear should be recommended.',309,98,0,1,1271174178,1),(435,'71-E','Pediatric Emergencies  - Acutely Ill Infant/child','Although pediatric emergencies such as the ones listed below are discussed with the appropriate condition, the care of the patient in the pediatric age group demands special skills',309,99,0,1,1271174178,1),(436,'71-1-E','Crying/fussing Child','A young infant whose only symptom is crying/fussing challenges the primary care physician to distinguish between benign and organic causes.',435,1,0,1,1271174178,1),(437,'71-2-E','Hypotonia/floppy Infant/child','Infants/children with decreased resistance to passive movement differ from those with weakness and hyporeflexia.  They require detailed, careful neurologic evaluation. Management programs, often life-long, are multidisciplinary and involve patients, family, and community.',435,2,0,1,1271174178,1),(438,'72-E','Pelvic Mass','Pelvic masses are common and may be found in a woman of any age, although the possible etiologies differ among age groups.  There is a need to diagnose and investigate them since early detection may affect outcome.',309,100,0,1,1271174178,1),(439,'73-E','Pelvic Pain','Acute pelvic pain is potentially life threatening.  Chronic pelvic pain is one of the most common problems in gynecology.  Women average 2 - 3 visits each year to physicians\' offices with chronic pelvic pain.  At present, only about one third of these women are given a specific diagnosis.  The absence of a clear diagnosis can frustrate both patients and clinicians.  Once the diagnosis is established, specific and usually successful treatment may be instituted.',309,101,0,1,1271174178,1),(440,'74-E','Periodic Health Examination (phe) ','Periodically, patients visit physicians\' office not because they are unwell, but because they want a \'check-up\'.  Such visits are referred to as health maintenance or the PHE. The PHE is an opportunity to relate to an asymptomatic patient for the purpose of case finding and screening for undetected disease and risky behaviour.  It is also an opportunity for health promotion and disease prevention.  The decision to include or exclude a medical condition in the PHE should be based on the burden of suffering caused by the condition, the quality of the screening, and effectiveness of the intervention.',309,102,0,1,1271174178,1),(441,'74-2-E','Infant And Child Immunization ','Immunization has reduced or eradicated many infectious diseases and has improved overall world health.  Recommended immunization schedules are constantly updated as new vaccines become available.',440,1,0,1,1271174178,1),(442,'74-1-E','Newborn Assessment/nutrition ','Primary care physicians play a vital role in identifying children at risk for developmental and other disorders that are threatening to life or long-term health before they become symptomatic.  In most cases, parents require direction and reassurance regarding the health status of their newborn infant.  With respect to development, parental concerns regarding the child\'s language development, articulation, fine motor skills, and global development require careful assessment.',440,2,0,1,1271174178,1),(443,'74-3-E','Pre-operative Medical Evaluation','Evaluation of patients prior to surgery is an important element of comprehensive medical care.  The objectives of such an evaluation include the detection of unrecognized disease that may increase the risk of surgery and how to minimize such risk.',440,3,0,1,1271174178,1),(444,'74-4-E','Work-related Health Issues ','Physicians will encounter health hazards in their own work place, as well as in patients\' work place.  These hazards need to be recognised and addressed.  A patient\'s reported environmental exposures may prompt interventions important in preventing future illnesses/injuries.  Equally important, physicians can not only play an important role in preventing occupational illness but also in promoting environmental health.',440,4,0,1,1271174178,1),(445,'75-E','Personality Disorders ','Personality disorders are persistent and maladaptive patterns of behaviour exhibited over a wide variety of social, occupational, and relationship contexts and leading to distress and impairment.  They represent important risk factors for a variety of medical, interpersonal, and psychiatric difficulties.  For example, patients with personality difficulties may attempt suicide, or may be substance abusers.  As a group, they may alienate health care providers with angry outbursts, high-risk behaviours, signing out against medical advice, etc.',309,103,0,1,1271174178,1),(446,'76-E','Pleural Effusion/pleural Abnormalities',NULL,309,104,0,1,1271174178,1),(447,'77-E','Poisoning','Exposures to poisons or drug overdoses account for 5 - 10% of emergency department visits, and&gt;5 % of admissions to intensive care units.  More than 50 % of these patients are children less than 6 years of age.',309,105,0,1,1271174178,1),(448,'78-4-E','Administration Of Effective Health Programs At The Populatio','Knowing the organization of the health care and public health systems in Canada as well as how to determine the most cost-effective interventions are becoming key elements of clinical practice. Physicians also must work well in multidisciplinary teams within the current system in order to achieve the maximum health benefit for all patients and residents. ',309,106,0,1,1271174178,1),(449,'78-2-E','Assessing And Measuring Health Status At The Population Leve','Knowing the health status of the population allows for better planning and evaluation of health programs and tailoring interventions to meet patient/community needs. Physicians are also active participants in disease surveillance programs, encouraging them to address health needs in the population and not merely health demands.',309,107,0,1,1271174178,1),(450,'78-1-E','Concepts Of Health And Its Determinants','Concepts of health, illness, disease and the socially defined sick role are fundamental to understanding the health of a community and to applying that knowledge to the patients that a physician serves. With advances in care, the aspirations of patients for good health have expanded and this has placed new demands on physicians to address issues that are not strictly biomedical in nature. These concepts are also important if the physician is to understand health and illness behaviour. ',309,108,0,1,1271174178,1),(451,'78-6-E','Environment','Environmental issues are important in medical practice because exposures may be causally linked to a patient\'s clinical presentation and the health of the exposed population. A physician is expected to work with regulatory agencies to help implement the necessary interventions to prevent future illness.  Physician involvement is important in the promotion of global environmental health.',309,109,0,1,1271174178,1),(452,'78-7-E','Health Of Special Populations','Health equity is defined as each person in society having an equal opportunity for health. Each community is composed of diverse groups of individuals and sub-populations. Due to variations in factors such as physical location, culture, behaviours, age and gender structure, populations have different health risks and needs that must be addressed in order to achieve health equity.  Hence physicians need to be aware of the differing needs of population groups and must be able to adjust service provision to ensure culturally safe communications and care.',309,110,0,1,1271174178,1),(453,'78-3-E','Interventions At The Population Level','Many interventions at the individual level must be supported by actions at the community level. Physicians will be expected to advocate for community wide interventions and to address issues that occur to many patients across their practice. ',309,111,0,1,1271174178,1),(454,'78-5-E','Outbreak Management','Physicians are crucial participants in the control of outbreaks of disease. They must be able to diagnose cases, recognize outbreaks, report these to public health authorities and work with authorities to limit the spread of the outbreak. A common example includes physicians working in nursing homes and being asked to assist in the control of an outbreak of influenza or diarrhea.',309,112,0,1,1271174178,1),(455,'79-1-E','Hyperkalemia ','Hyperkalemia may have serious consequences (especially cardiac) and may also be indicative of the presence of serious associated medical conditions.',309,113,0,1,1271174178,1),(456,'79-2-E','Hypokalemia ','Hypokalemia, a common clinical problem, is most often discovered on routine analysis of serum electrolytes or ECG results.  Symptoms usually develop much later when depletion is quite severe.',309,114,0,1,1271174178,1),(457,'80-1-E','Antepartum Care ','The purpose of antepartum care is to help achieve as good a maternal and infant outcome as possible.  This means that psychosocial issues as well as biological issues need to be addressed.',309,115,0,1,1271174178,1),(458,'80-2-E','Intrapartum Care/postpartum Care ','Intrapartum and postpartum care means the care of the mother and fetus during labor and the six-week period following birth during which the reproductive tract returns to its normal nonpregnant state.  Of pregnant women, 85% will undergo spontaneous labor between 37 and 42 weeks of gestation.  Labor is the process by which products of conception are delivered from the uterus by progressive cervical effacement and dilatation in the presence of regular uterine contractions.',309,116,0,1,1271174178,1),(459,'80-3-E','Obstetrical Complications ','Virtually any maternal medical or surgical condition can complicate the course of a pregnancy and/or be affected by the pregnancy.  In addition, conditions arising in pregnancy can have adverse effects on the mother and/or the fetus.  For example, babies born prematurely account for&gt;50% of perinatal morbidity and mortality; an estimated 5% of women will describe bleeding of some extent during pregnancy, and in some patients the bleeding will endanger the mother.',309,117,0,1,1271174178,1),(460,'81-E','Pregnancy Loss','A miscarriage or abortion is a pregnancy that ends before the fetus can live outside the uterus.  The term also means the actual passage of the uterine contents.  It is very common in early pregnancy; up to 20% of pregnant women have a miscarriage before 20 weeks of pregnancy, 80% of these in the first 12 weeks.',309,118,0,1,1271174178,1),(461,'82-E','Prematurity','The impact of premature birth is best summarized by the fact that&lt;10% of babies born prematurely in North America account for&gt;50% of all perinatal morbidity and mortality.  Yet outcomes, although guarded, can be rewarding given optimal circumstances.',309,119,0,1,1271174178,1),(462,'83-E','Prolapse/pelvic Relaxation','Patients with pelvic relaxation present with a forward and downward drop of the pelvic organs (bladder, rectum).  In order to identify patients who would benefit from therapy, the physician should be familiar with the manifestations of pelvic relaxation (uterine prolapse, vaginal vault prolapse, cystocele, rectocele, and enterocele) and have an approach to management.',309,120,0,1,1271174178,1),(463,'84-E','Proteinuria ','Urinalysis is a screening procedure used frequently for insurance and routine examinations.  Proteinuria is usually identified by positive dipstick on routine urinalysis. Persistent proteinuria often implies abnormal glomerular function.',309,121,0,1,1271174178,1),(464,'85-E','Pruritus ','Itching is the most common symptom in dermatology.  In the absence of primary skin lesions, generalised pruritus can be indicative of an underlying systemic disorder.  Most patients with pruritus do not have a systemic disorder and the itching is due to a cutaneous disorder.',309,122,0,1,1271174178,1),(465,'86-E','Psychotic Patient/disordered Thought','Psychosis is a general term for a major mental disorder characterized by derangement of personality and loss of contact with reality, often with false beliefs (delusions), disturbances in sensory perception (hallucinations), or thought disorders (illusions). Schizophrenia is both the most common (1% of world population) and the classic psychotic disorder.  There are other psychotic syndromes that do not meet the diagnostic criteria for schizophrenia, some of them caused by general medical conditions or induced by a substance (alcohol, hallucinogens, steroids).  In the evaluation of any psychotic patient in a primary care setting all of these possibilities need to be considered.',309,123,0,1,1271174178,1),(466,'87-E','Pulse Abnormalities/diminished/absent/bruits','Arterial pulse characteristics should be assessed as an integral part of the physical examination.  Carotid, radial, femoral, posterior tibial, and dorsalis pedis pulses should be examined routinely on both sides, and differences, if any, in amplitude, contour, and upstroke should be ascertained.',309,124,0,1,1271174178,1),(467,'88-E','Pupil Abnormalities ','Pupillary disorders of changing degree are in general of little clinical importance.  If only one pupil is fixed to light, it is suspicious of the effect of mydriatics.  However, pupillary disorders with neurological symptoms may be of significance.',309,125,0,1,1271174178,1),(468,'89-1-E','Acute Renal Failure (anuria/oliguria/arf)','A sudden and rapid rise in serum creatinine is a common finding.  A competent physician is required to have an organised approach to this problem.',309,126,0,1,1271174178,1),(469,'89-2-E','Chronic Renal Failure ','Although specialists in nephrology will care for patients with chronic renal failure, family physicians will need to identify patients at risk for chronic renal disease, will participate in treatment to slow the progression of chronic renal disease, and will care for other common medical problems that afflict these patients.  Physicians must realise that patients with chronic renal failure have unique risks and that common therapies may be harmful because kidneys are frequently the main routes for excretion of many drugs.',309,127,0,1,1271174178,1),(470,'90-E','Scrotal Mass ','In children and adolescents, scrotal masses vary from incidental, requiring only reassurance, to acute pathologic events.  In adults, tumors of the testis are relatively uncommon (only 1 - 2 % of malignant tumors in men), but are considered of particular importance because they affect predominantly young men (25 - 34 years).  In addition, recent advances in management have resulted in dramatic improvement in survival rate.',309,128,0,1,1271174178,1),(471,'91-E','Scrotal Pain ','In most scrotal disorders, there is swelling of the testis or its adnexae.  However, some conditions are not only associated with pain, but pain may precede the development of an obvious mass in the scrotum.',309,129,0,1,1271174178,1),(472,'92-E','Seizures (epilepsy)','Seizures are an important differential diagnosis of syncope.  A seizure is a transient neurological dysfunction resulting from excessive/abnormal electrical discharges of cortical neurons.  They may represent epilepsy (a chronic condition characterized by recurrent seizures) but need to be differentiated from a variety of secondary causes.',309,130,0,1,1271174178,1),(473,'93-1-E','Sexual Maturation, Abnormal ','Sexual development is important to adolescent perception of self-image and wellbeing. Many factors may disrupt the normal progression to sexual maturation.',309,131,0,1,1271174178,1),(474,'94-E','Sexually Concerned Patient/gender Identity Disorder','The social appropriateness of sexuality is culturally determined.  The physician\'s own sexual attitude needs to be recognised and taken into account in order to deal with the patient\'s concern in a relevant manner.  The patient must be set at ease in order to make possible discussion of private and sensitive sexual issues.',309,132,0,1,1271174178,1),(475,'95-E','Skin Ulcers/skin Tumors (benign And Malignant)',NULL,309,133,0,1,1271174178,1),(476,'96-E','Skin Rash, Macules',NULL,309,134,0,1,1271174178,1),(477,'97-E','Skin Rash, Papules',NULL,309,135,0,1,1271174178,1),(478,'97-1-E','Childhood Communicable Diseases ','Communicable diseases are common in childhood and vary from mild inconveniences to life threatening disorders.  Physicians need to differentiate between these common conditions and initiate management.',477,1,0,1,1271174178,1),(479,'97-2-E','Urticaria/angioedema/anaphylaxis',NULL,477,2,0,1,1271174178,1),(480,'98-E','Sleep And Circadian Rhythm Disorders/sleep Apnea Syndrome/in','Insomnia is a symptom that affects 1/3 of the population at some time, and is a persistent problem in 10 % of the population.  Affected patients complain of difficulty in initiating and maintaining sleep, and this inability to obtain adequate quantity and quality of sleep results in impaired daytime functioning.',309,136,0,1,1271174178,1),(481,'99-1-E','Hypernatremia ','Although not extremely common, hypernatremia is likely to be encountered with increasing frequency in our ageing population.  It is also encountered at the other extreme of life, the very young, for the same reason: an inability to respond to thirst by drinking water.',309,137,0,1,1271174178,1),(482,'99-2-E','Hyponatremia ','Hyponatremia is detected in many asymptomatic patients because serum electrolytes are measured almost routinely.  In children with sodium depletion, the cause of the hyponatremia is usually iatrogenic.  The presence of hyponatremia may predict serious neurologic complications or be relatively benign.',309,138,0,1,1271174178,1),(483,'100-E','Sore Throat (rhinorrhea) ','Rhinorrhea and sore throat occurring together indicate a viral upper respiratory tract infection such as the \"common cold\".  Sore throat may be due to a variety of bacterial and viral pathogens (as well as other causes in more unusual circumstances).  Infection is transmitted from person to person and arises from direct contact with infected saliva or nasal secretions.  Rhinorrhea alone is not infective and may be seasonal (hay fever or allergic rhinitis) or chronic (vaso-motor rhinitis).',309,139,0,1,1271174178,1),(484,'100-1-E','Smell/taste Dysfunction ','In order to evaluate patients with smell or taste disorders, a multi-disciplinary approach is required.  This means that in addition to the roles specialists may have, the family physician must play an important role.',483,1,0,1,1271174178,1),(485,'101-E','Stature Abnormal (tall Stature/short Stature)','To define any growth point, children should be measured accurately and each point (height, weight, and head circumference) plotted.  One of the more common causes of abnormal growth is mis-measurement or aberrant plotting.',309,140,0,1,1271174178,1),(486,'102-E','Strabismus And/or Amblyopia ','Parental concern about children with a wandering eye, crossing eye, or poor vision in one eye makes it necessary for physicians to know how to manage such problems.',309,141,0,1,1271174178,1),(487,'103-E','Substance Abuse/drug Addiction/withdrawal','Alcohol and nicotine abuse is such a common condition that virtually every clinician is confronted with their complications.  Moreover, 10 - 15% of outpatient visits as well as 25 - 40% of hospital admissions are related to substance abuse and its sequelae.',309,142,0,1,1271174178,1),(488,'104-E','Sudden Infant Death Syndrome(sids)/acute Life Threatening Ev','SIDS and/or ALTE are a devastating event for parents, caregivers and health care workers alike.  It is imperative that the precursors, probable cause and parental concerns are extensively evaluated to prevent recurrence.',309,143,0,1,1271174178,1),(489,'105-E','Suicidal Behavior','Psychiatric emergencies are common and serious problems.  Suicidal behaviour is one of several psychiatric emergencies which physicians must know how to assess and manage.',309,144,0,1,1271174178,1),(490,'106-E','Syncope/pre-syncope/loss Of Consciousness  (fainting)','Syncopal episodes, an abrupt and transient loss of consciousness followed by a rapid and usually complete recovery, are common.  Physicians are required to distinguish syncope from seizures, and benign syncope from syncope caused by serious underlying illness.',309,145,0,1,1271174178,1),(491,'107-3-E','Fever In A Child/fever In A Child Less Than Three Weeks','Fever in children is the most common symptom for which parents seek medical advice.  While most causes are self-limited viral infections (febrile illness of short duration) it is important to identify serious underlying disease and/or those other infections amenable to treatment.',309,146,0,1,1271174178,1),(492,'107-4-E','Fever In The Immune Compromised Host/recurrent Fever','Patients with certain immuno-deficiencies are at high risk for infections.  The infective organism and site depend on the type and severity of immuno-suppression.  Some of these infections are life threatening.',309,147,0,1,1271174178,1),(493,'107-2-E','Fever Of Unknown Origin ','Unlike acute fever (&lt;2 weeks), which is usually either viral (low-grade, moderate fever) or bacterial (high grade, chills, rigors) in origin, fever of unknown origin is an illness of three weeks or more without an established diagnosis despite appropriate investigation.',309,148,0,1,1271174178,1),(494,'107-1-E','Hyperthermia ','Hyperthermia is an elevation in core body temperature due to failure of thermo-regulation (in contrast to fever, which is induced by cytokine activation).  It is a medical emergency and may be associated with severe complications and death.  The differential diagnosis is extensive (includes all causes of fever).',309,149,0,1,1271174178,1),(495,'107-5-E','Hypothermia ','Hypothermia is the inability to maintain core body temperature.  Although far less common than is elevation in temperature, hypothermia (central temperature ? 35√É‚Äö√Ç¬∞C) is of considerable importance because it can represent a medical emergency.  Severe hypothermia is defined as a core temperature of &lt;28√É‚Äö√Ç¬∞C.',309,150,0,1,1271174178,1),(496,'108-E','Tinnitus','Tinnitus is an awareness of sound near the head without an obvious external source.  It may involve one or both ears, be continuous or intermittent.  Although not usually related to serious medical problems, in some it may interfere with daily activities, affect quality of life, and in a very few be indicative of serious organic disease.',309,151,0,1,1271174178,1),(497,'109-E','Trauma/accidents','Management of patients with traumatic injuries presents a variety of challenges.  They require evaluation in the emergency department for triage and prevention of further deterioration prior to transfer or discharge.  Early recognition and management of complications along with aggressive treatment of underlying medical conditions are necessary to minimise morbidity and mortality in this patient population.',309,152,0,1,1271174178,1),(498,'109-1-E','Abdominal Injuries ','The major causes of blunt trauma are motor vehicles, auto-pedestrian injuries, and motorcycle/all terrain vehicle injuries.  In children, bicycle injuries, falls, and child abuse also contribute.  Assessment of a patient with an abdominal injury is difficult.  As a consequence, important injuries tend to be missed.  Rupture of a hollow viscus or bleeding from a solid organ may produce few clinical signs.',497,1,0,1,1271174178,1),(499,'109-2-E','Bites, Animal/insects ','Since so many households include pets, animal bite wounds are common.  Dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.</p>\r\n<p>Insect bites in Canada most commonly cause a local inflammatory reaction that subsides within a few hours and is mostly a nuisance.  In contrast, mosquitoes can transmit infectious disease to more than 700 million people in other geographic areas of the world (e.g., malaria, yellow fever, dengue, encephalitis and filariasis among others), as well as in Canada.  Tick-borne illness is also common.  On the other hand, systemic reactions to insect bites are extremely rare compared with insect stings.  The most common insects associated with systemic allergic reactions were blackflies, deerflies, and horseflies.',497,2,0,1,1271174178,1),(500,'109-3-E','Bone/joint Injury','Major fractures are at times associated with other injuries, and priorities must be set for each patient.  For example, hemodynamic stability takes precedence over fracture management, but an open fracture should be managed as soon as possible.  On the other hand, management of many soft tissue injuries is facilitated by initial stabilization of bone or joint injury. Unexplained fractures in children should alert physicians to the possibility of abuse.',497,3,0,1,1271174178,1),(501,'109-4-E','Chest Injuries ','Injury to the chest may be blunt (e.g., motor vehicle accident resulting in steering wheel blow to sternum, falls, explosions, crush injuries) or penetrating (knife/bullet).  In either instance, emergency management becomes extremely important to the eventual outcome.',497,4,0,1,1271174178,1),(502,'109-6-E','Drowning (near-drowning) ','Survival after suffocation by submersion in a liquid medium, including loss of consciousness, is defined as near drowning.  The incidence is uncertain, but likely it may occur several hundred times more frequently than drowning deaths (150,000/year worldwide).',497,5,0,1,1271174178,1),(503,'109-8-E','Facial Injuries ','Facial injuries are potentially life threatening because of possible damage to the airway and central nervous system.',497,6,0,1,1271174178,1),(504,'109-9-E','Hand/wrist Injuries ','Hand injuries are common problems presenting to emergency departments.  The ultimate function of the hand depends upon the quality of the initial care, the severity of the original injury and rehabilitation.',497,7,0,1,1271174178,1),(505,'109-10-E','Head Trauma/brain Death/transplant Donations','Most head trauma is mild and not associated with brain injury or long-term sequelae. Improved outcome after head trauma depends upon preventing deterioration and secondary brain injury.  Serious intracranial injuries may remain undetected due to failure to obtain an indicated head CT.',497,8,0,1,1271174178,1),(506,'109-11-E','Nerve Injury ','Peripheral nerve injuries often occur as part of more extensive injuries and tend to go unrecognized.  Evaluation of these injuries is based on an accurate knowledge of the anatomy and function of the nerve(s) involved.',497,9,0,1,1271174178,1),(507,'109-12-E','Skin Wounds/regional Anaesthesia','Skin and subcutaneous wounds tend to be superficial and can be repaired under local anesthesia.  Animal bite wounds are common and require special consideration.  Since so many households include pets, dog and cat bites account for about 1% of emergency visits, the majority in children.  Some can be serious and lead to limb damage, and at times permanent disability.',497,10,0,1,1271174178,1),(508,'109-13-E','Spinal Trauma','Most spinal cord injuries are a result of car accidents, falls, sports-related trauma, or assault with weapons.  The average age at the time of spinal injury is approximately 35 years, and men are four times more likely to be injured than are women.  The sequelae of such events are dire in terms of effect on patient, family, and community.  Initial immobilization and maintenance of ventilation are of critical importance.',497,11,0,1,1271174178,1),(509,'109-14-E','Urinary Tract Injuries ','Urinary tract injuries are usually closed rather than penetrating, and may affect the kidneys and/or the collecting system.',497,12,0,1,1271174178,1),(510,'109-15-E','Vascular Injury ','Vascular injuries are becoming more common.  Hemorrhage may be occult and require a high index of suspicion (e.g., fracture in an adjacent bone).',497,13,0,1,1271174178,1),(511,'110-1-E','Dysuria And/or Pyuria ','Patients with urinary tract infections, especially the very young and very old, may present in an atypical manner.  Appropriate diagnosis and management may prevent significant morbidity.  Dysuria may mean discomfort/pain on micturition or difficulty with micturition.  Pain usually implies infection whereas difficulty is usually related to distal mechanical obstruction (e.g., prostatic).',309,153,0,1,1271174178,1),(512,'110-2-E','Polyuria/polydipsia','Urinary frequency, a common complaint, can be confused with polyuria, a less common, but important complaint.  Diabetes mellitus is a common disorder with morbidity and mortality that can be reduced by preventive measures.  Intensive glycemic control during pregnancy will reduce neonatal complications.',309,154,0,1,1271174178,1),(513,'111-E','Urinary Obstruction/hesitancy/prostatic Cancer','Urinary tract obstruction is a relatively common problem.  The obstruction may be complete or incomplete, and unilateral or bilateral.  Thus, the consequences of the obstruction depend on its nature.',309,155,0,1,1271174178,1),(514,'112-E','Vaginal Bleeding, Excessive/irregular/abnormal','Vaginal bleeding is considered abnormal when it occurs at an unexpected time (before menarche or after menopause) or when it varies from the norm in amount or pattern (urinary tract and bowel should be excluded as a source).  Amount or pattern is considered outside normal when it is associated with iron deficiency anemia, it lasts&gt;7days, flow is&gt;80ml/clots, or interval is&lt;24 days.',309,156,0,1,1271174178,1),(515,'113-E','Vaginal Discharge/vulvar Itch/std ','Vaginal discharge, with or without pruritus, is a common problem seen in the physician\'s office.',309,157,0,1,1271174178,1),(516,'114-E','Violence, Family','There are a number of major psychiatric emergencies and social problems which physicians must be prepared to assess and manage.  Domestic violence is one of them, since it has both direct and indirect effects on the health of populations.  Intentional controlling or violent behavior (physical, sexual, or emotional abuse, economic control, or social isolation of the victim) by a person who is/was in an intimate relationship with the victim is domestic violence.  The victim lives in a state of constant fear, terrified about when the next episode of abuse will occur.  Despite this, abuse frequently remains hidden and undiagnosed because patients often conceal that they are in abusive relationships.  It is important for clinicians to seek the diagnosis in certain groups of patients.',309,158,0,1,1271174178,1),(517,'114-3-E','Adult Abuse/spouse Abuse ','The major problem in spouse abuse is wife abuse (some abuse of husbands has been reported).  It is the abuse of power in a relationship involving domination, coercion, intimidation, and the victimization of one person by another.  Ten percent of women in a relationship with a man have experienced abuse.  Of women presenting to a primary care clinic, almost 1/3 reported physical and verbal abuse.',516,1,0,1,1271174178,1),(518,'114-1-E','Child Abuse, Physical/emotional/sexual/neglect/self-induced ','Child abuse is intentional harm to a child by the caregiver.  It is part of the spectrum of family dysfunction and leads to significant morbidity and mortality (recently sexual attacks on children by groups of other children have increased).  Abuse causes physical and emotional trauma, and may present as neglect.  The possibility of abuse must be in the mind of all those involved in the care of children who have suffered traumatic injury or have psychological or social disturbances (e.g., aggressive behavior, stress disorder, depressive disorder, substance abuse, etc.).',516,2,0,1,1271174178,1),(519,'114-2-E','Elderly Abuse ','Abuse of the elderly may represent an act or omission that results in harm to the elderly person\'s health or welfare.  Although the incidence and prevalence in Canada has been difficult to quantitate, in one study 4 % of surveyed seniors report that they experienced abuse.  There are three categories of abuse: domestic, institutional, and self-neglect.',516,3,0,1,1271174178,1),(520,'115-1-E','Acute Visual Disturbance/loss','Loss of vision is a frightening symptom that demands prompt attention; most patients require an urgent ophthalmologic opinion.',309,159,0,1,1271174178,1),(521,'115-2-E','Chronic Visual Disturbance/loss ','Loss of vision is a frightening symptom that demands prompt attention on the part of the physician.',309,160,0,1,1271174178,1),(522,'116-E','Vomiting/nausea ','Nausea may occur alone or along with vomiting (powerful ejection of gastric contents), dyspepsia, and other GI complaints.  As a cause of absenteeism from school or workplace, it is second only to the common cold.  When prolonged or severe, vomiting may be associated with disturbances of volume, water and electrolyte metabolism that may require correction prior to other specific treatment.',309,161,0,1,1271174178,1),(523,'117-E','Weakness/paralysis/paresis/loss Of Motion','Many patients who complain of weakness are not objectively weak when muscle strength is formally tested.  A careful history and physical examination will permit the distinction between functional disease and true muscle weakness.',309,162,0,1,1271174178,1),(524,'118-3-E','Weight (low) At Birth/intrauterine Growth Restriction ','Intrauterine growth restriction (IUGR) is often a manifestation of congenital infections, poor maternal nutrition, or maternal illness.  In other instances, the infant may be large for the gestational age.  There may be long-term sequelae for both.  Low birth weight is the most important risk factor for infant mortality.  It is also a significant determinant of infant and childhood morbidity, particularly neuro-developmental problems and learning disabilities.',309,163,0,1,1271174178,1),(525,'118-1-E','Weight Gain/obesity ','Obesity is a chronic disease that is increasing in prevalence. The percentage of the population with a body mass index of&gt;30 kg/m2 is approximately 15%.',309,164,0,1,1271174178,1),(526,'118-2-E','Weight Loss/eating Disorders/anorexia ','Although voluntary weight loss may be of no concern in an obese patient, it could be a manifestation of psychiatric illness.  Involuntary clinically significant weight loss (&gt;5% baseline body weight or 5 kg) is nearly always a sign of serious medical or psychiatric illness and should be investigated.',309,165,0,1,1271174178,1),(527,'119-1-E','Lower Respiratory Tract Disorders ','Individuals with episodes of wheezing, breathlessness, chest tightness, and cough usually have limitation of airflow.  Frequently this limitation is reversible with treatment.  Without treatment it may be lethal.',309,166,0,1,1271174178,1),(528,'119-2-E','Upper Respiratory Tract Disorders ','Wheezing, a continuous musical sound&gt;1/4 seconds, is produced by vibration of the walls of airways narrowed almost to the point of closure.  It can originate from airways of any size, from large upper airways to intrathoracic small airways.  It can be either inspiratory or expiratory, unlike stridor (a noisy, crowing sound, usually inspiratory and resulting from disturbances in or adjacent to the larynx).',309,167,0,1,1271174178,1),(529,'120-E','White Blood Cells, Abnormalities Of','Because abnormalities of white blood cells (WBCs) occur commonly in both asymptomatic as well as acutely ill patients, every physician will need to evaluate patients for this common problem.  Physicians also need to select medications to be prescribed mindful of the morbidity and mortality associated with drug-induced neutropenia and agranulocytosis.',309,168,0,1,1271174178,1),(2328,'','AAMC Physician Competencies Reference Set','July 2013 *Source: Englander R, Cameron T, Ballard AJ, Dodge J, Bull J, and Aschenbrener CA. Toward a common taxonomy of competency domains for the health professions and competencies for physicians. Acad Med. 2013;88:1088-1094.',0,0,0,1,1391798786,1),(2329,'aamc-pcrs-comp-c0100','1 Patient Care','Provide patient-centered care that is compassionate, appropriate, and effective for the treatment of health problems and the',2328,0,0,1,1391798786,1),(2330,'aamc-pcrs-comp-c0200','2 Knowledge for Practice','Demonstrate knowledge of established and evolving biomedical, clinical, epidemiological and social-behavioral sciences, as well as the application of this knowledge to patient care',2328,1,0,1,1391798786,1),(2331,'aamc-pcrs-comp-c0300','3 Practice-Based Learning and Improvement','Demonstrate the ability to investigate and evaluate one√É¬¢??s care of patients, to appraise and assimilate scientific evidence, and to continuously improve patient care based on constant self-evaluation and life-long learning',2328,2,0,1,1391798786,1),(2332,'aamc-pcrs-comp-c0400','4 Interpersonal and Communication Skills','Demonstrate interpersonal and communication skills that result in the effective exchange of information and collaboration with patients, their families, and health professionals',2328,3,0,1,1391798786,1),(2333,'aamc-pcrs-comp-c0500','5 Professionalism','Demonstrate a commitment to carrying out professional responsibilities and an adherence to ethical principles',2328,4,0,1,1391798786,1),(2334,'aamc-pcrs-comp-c0600','6 Systems-Based Practice','Demonstrate an awareness of and responsiveness to the larger context and system of health care, as well as the ability to call effectively on other resources in the system to provide optimal health care',2328,5,0,1,1391798786,1),(2335,'aamc-pcrs-comp-c0700','7 Interprofessional Collaboration','Demonstrate the ability to engage in an interprofessional team in a manner that optimizes safe, effective patient- and population-centered care',2328,6,0,1,1391798786,1),(2336,'aamc-pcrs-comp-c0800','8 Personal and Professional Development','Demonstrate the qualities required to sustain lifelong personal and professional growth',2328,7,0,1,1391798786,1),(2337,'aamc-pcrs-comp-c0101','1.1','Perform all medical, diagnostic, and surgical procedures considered',2329,0,0,1,1391798786,1),(2338,'aamc-pcrs-comp-c0102','1.2','Gather essential and accurate information about patients and their conditions through history-taking, physical examination, and the use of laboratory data, imaging, and other tests',2329,1,0,1,1391798786,1),(2339,'aamc-pcrs-comp-c0103','1.3','Organize and prioritize responsibilities to provide care that is safe, effective, and efficient',2329,2,0,1,1391798786,1),(2340,'aamc-pcrs-comp-c0104','1.4','Interpret laboratory data, imaging studies, and other tests required for the area of practice',2329,3,0,1,1391798786,1),(2341,'aamc-pcrs-comp-c0105','1.5','Make informed decisions about diagnostic and therapeutic interventions based on patient information and preferences, up-to-date scientific evidence, and clinical judgment',2329,4,0,1,1391798786,1),(2342,'aamc-pcrs-comp-c0106','1.6','Develop and carry out patient management plans',2329,5,0,1,1391798786,1),(2343,'aamc-pcrs-comp-c0107','1.7','Counsel and educate patients and their families to empower them to participate in their care and enable shared decision making',2329,6,0,1,1391798786,1),(2344,'aamc-pcrs-comp-c0108','1.8','Provide appropriate referral of patients including ensuring continuity of care throughout transitions between providers or settings, and following up on patient progress and outcomes',2329,7,0,1,1391798786,1),(2345,'aamc-pcrs-comp-c0109','1.9','Provide health care services to patients, families, and communities aimed at preventing health problems or maintaining health',2329,8,0,1,1391798786,1),(2346,'aamc-pcrs-comp-c0110','1.10','Provide appropriate role modeling',2329,9,0,1,1391798786,1),(2347,'aamc-pcrs-comp-c0111','1.11','Perform supervisory responsibilities commensurate with one\'s roles, abilities, and qualifications',2329,10,0,1,1391798786,1),(2348,'aamc-pcrs-comp-c0199','1.99','Other patient care',2329,11,0,1,1391798786,1),(2349,'aamc-pcrs-comp-c0201','2.1','Demonstrate an investigatory and analytic approach to clinical situations',2330,0,0,1,1391798786,1),(2350,'aamc-pcrs-comp-c0202','2.2','Apply established and emerging bio-physical scientific principles fundamental to health care for patients and populations',2330,1,0,1,1391798786,1),(2351,'aamc-pcrs-comp-c0203','2.3','Apply established and emerging principles of clinical sciences to diagnostic and therapeutic decision-making, clinical problem-solving, and other aspects of evidence-based health care',2330,2,0,1,1391798786,1),(2352,'aamc-pcrs-comp-c0204','2.4','Apply principles of epidemiological sciences to the identification of health problems, risk factors, treatment strategies, resources, and disease prevention/health promotion efforts for patients and populations',2330,3,0,1,1391798786,1),(2353,'aamc-pcrs-comp-c0205','2.5','Apply principles of social-behavioral sciences to provision of patient care, including assessment of the impact of psychosocial and cultural influences on health, disease, care-seeking, care compliance, and barriers to and attitudes toward care',2330,4,0,1,1391798786,1),(2354,'aamc-pcrs-comp-c0206','2.6','Contribute to the creation, dissemination, application, and translation of new health care knowledge and practices',2330,5,0,1,1391798786,1),(2355,'aamc-pcrs-comp-c0299','2.99','Other knowledge for practice',2330,6,0,1,1391798786,1),(2356,'aamc-pcrs-comp-c0301','3.1','Identify strengths, deficiencies, and limits in one\'s knowledge and expertise',2331,0,0,1,1391798786,1),(2357,'aamc-pcrs-comp-c0302','3.2','Set learning and improvement goals',2331,1,0,1,1391798786,1),(2358,'aamc-pcrs-comp-c0303','3.3','Identify and perform learning activities that address one\'s gaps in knowledge, skills, and/or attitudes',2331,2,0,1,1391798786,1),(2359,'aamc-pcrs-comp-c0304','3.4','Systematically analyze practice using quality improvement methods, and implement changes with the goal of practice improvement',2331,3,0,1,1391798786,1),(2360,'aamc-pcrs-comp-c0305','3.5','Incorporate feedback into daily practice',2331,4,0,1,1391798786,1),(2361,'aamc-pcrs-comp-c0306','3.6','Locate, appraise, and assimilate evidence from scientific studies related to',2331,5,0,1,1391798786,1),(2362,'aamc-pcrs-comp-c0307','3.7','Use information technology to optimize learning',2331,6,0,1,1391798786,1),(2363,'aamc-pcrs-comp-c0308','3.8','Participate in the education of patients, families, students, trainees, peers and other health professionals',2331,7,0,1,1391798786,1),(2364,'aamc-pcrs-comp-c0309','3.9','Obtain and utilize information about individual patients, populations of patients, or communities from which patients are drawn to improve care',2331,8,0,1,1391798786,1),(2365,'aamc-pcrs-comp-c0310','3.10','Continually identify, analyze, and implement new knowledge, guidelines, standards, technologies, products, or services that have been demonstrated to improve outcomes',2331,9,0,1,1391798786,1),(2366,'aamc-pcrs-comp-c0399','3.99','Other practice-based learning and improvement',2331,10,0,1,1391798786,1),(2367,'aamc-pcrs-comp-c0401','4.1','Communicate effectively with patients, families, and the public, as appropriate, across a broad range of socioeconomic and cultural backgrounds',2332,0,0,1,1391798786,1),(2368,'aamc-pcrs-comp-c0402','4.2','Communicate effectively with colleagues within one\'s profession or specialty, other health professionals, and health related agencies (see also 7.3)',2332,1,0,1,1391798786,1),(2369,'aamc-pcrs-comp-c0403','4.3','Work effectively with others as a member or leader of a health care team or other professional group (see also 7.4)',2332,2,0,1,1391798786,1),(2370,'aamc-pcrs-comp-c0404','4.4','Act in a consultative role to other health professionals',2332,3,0,1,1391798786,1),(2371,'aamc-pcrs-comp-c0405','4.5','Maintain comprehensive, timely, and legible medical records',2332,4,0,1,1391798786,1),(2372,'aamc-pcrs-comp-c0406','4.6','Demonstrate sensitivity, honesty, and compassion in difficult conversations, including those about death, end of life, adverse events, bad news, disclosure of errors, and other sensitive topics',2332,5,0,1,1391798786,1),(2373,'aamc-pcrs-comp-c0407','4.7','Demonstrate insight and understanding about emotions and human responses to emotions that allow one to develop and manage interpersonal',2332,6,0,1,1391798786,1),(2374,'aamc-pcrs-comp-c0499','4.99','Other interpersonal and communication skills',2332,7,0,1,1391798786,1),(2375,'aamc-pcrs-comp-c0501','5.1','Demonstrate compassion, integrity, and respect for others',2333,0,0,1,1391798786,1),(2376,'aamc-pcrs-comp-c0502','5.2','Demonstrate responsiveness to patient needs that supersedes self-interest',2333,1,0,1,1391798786,1),(2377,'aamc-pcrs-comp-c0503','5.3','Demonstrate respect for patient privacy and autonomy',2333,2,0,1,1391798786,1),(2378,'aamc-pcrs-comp-c0504','5.4','Demonstrate accountability to patients, society, and the profession',2333,3,0,1,1391798786,1),(2379,'aamc-pcrs-comp-c0505','5.5','Demonstrate sensitivity and responsiveness to a diverse patient population, including but not limited to diversity in gender, age, culture, race, religion, disabilities, and sexual orientation',2333,4,0,1,1391798786,1),(2380,'aamc-pcrs-comp-c0506','5.6','Demonstrate a commitment to ethical principles pertaining to provision or withholding of care, confidentiality, informed consent, and business practices, including compliance with relevant laws, policies, and regulations',2333,5,0,1,1391798786,1),(2381,'aamc-pcrs-comp-c0599','5.99','Other professionalism',2333,6,0,1,1391798786,1),(2382,'aamc-pcrs-comp-c0601','6.1','Work effectively in various health care delivery settings and systems relevant to one\'s clinical specialty',2334,0,0,1,1391798786,1),(2383,'aamc-pcrs-comp-c0602','6.2','Coordinate patient care within the health care system relevant to one\'s clinical specialty',2334,1,0,1,1391798786,1),(2384,'aamc-pcrs-comp-c0603','6.3','Incorporate considerations of cost awareness and risk-benefit analysis in patient and/or population-based care',2334,2,0,1,1391798786,1),(2385,'aamc-pcrs-comp-c0604','6.4','Advocate for quality patient care and optimal patient care systems',2334,3,0,1,1391798786,1),(2386,'aamc-pcrs-comp-c0605','6.5','Participate in identifying system errors and implementing potential systems solutions',2334,4,0,1,1391798786,1),(2387,'aamc-pcrs-comp-c0606','6.6','Perform administrative and practice management responsibilities commensurate with one√É¬¢??s role, abilities, and qualifications',2334,5,0,1,1391798786,1),(2388,'aamc-pcrs-comp-c0699','6.99','Other systems-based practice',2334,6,0,1,1391798786,1),(2389,'aamc-pcrs-comp-c0701','7.1','Work with other health professionals to establish and maintain a climate of mutual respect, dignity, diversity, ethical integrity, and trust',2335,0,0,1,1391798786,1),(2390,'aamc-pcrs-comp-c0702','7.2','Use the knowledge of one√É¬¢??s own role and the roles of other health professionals to appropriately assess and address the health care needs of the patients and populations served',2335,1,0,1,1391798786,1),(2391,'aamc-pcrs-comp-c0703','7.3','Communicate with other health professionals in a responsive and responsible manner that supports the maintenance of health and the',2335,2,0,1,1391798786,1),(2392,'aamc-pcrs-comp-c0704','7.4','Participate in different team roles to establish, develop, and continuously enhance interprofessional teams to provide patient- and population-centered care that is safe, timely, efficient, effective, and equitable',2335,3,0,1,1391798786,1),(2393,'aamc-pcrs-comp-c0799','7.99','Other interprofessional collaboration',2335,4,0,1,1391798786,1),(2394,'aamc-pcrs-comp-c0801','8.1','Develop the ability to use self-awareness of knowledge, skills, and emotional limitations to engage in appropriate help-seeking behaviors',2336,0,0,1,1391798786,1),(2395,'aamc-pcrs-comp-c0802','8.2','Demonstrate healthy coping mechanisms to respond to stress',2336,1,0,1,1391798786,1),(2396,'aamc-pcrs-comp-c0803','8.3','Manage conflict between personal and professional responsibilities',2336,2,0,1,1391798786,1),(2397,'aamc-pcrs-comp-c0804','8.4','Practice flexibility and maturity in adjusting to change with the capacity to alter one\'s behavior',2336,3,0,1,1391798786,1),(2398,'aamc-pcrs-comp-c0805','8.5','Demonstrate trustworthiness that makes colleagues feel secure when one is responsible for the care of patients',2336,4,0,1,1391798786,1),(2399,'aamc-pcrs-comp-c0806','8.6','Provide leadership skills that enhance team functioning, the learning environment, and/or the health care delivery system',2336,5,0,1,1391798786,1),(2400,'aamc-pcrs-comp-c0807','8.7','Demonstrate self-confidence that puts patients, families, and members of the health care team at ease',2336,6,0,1,1391798786,1),(2401,'aamc-pcrs-comp-c0808','8.8','Recognize that ambiguity is part of clinical health care and respond by utilizing appropriate resources in dealing with uncertainty',2336,7,0,1,1391798786,1),(2402,'aamc-pcrs-comp-c0899','8.99','Other personal and professional development',2336,8,0,1,1391798786,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_provinces` (
  `province_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `province` varchar(200) NOT NULL,
  `abbreviation` varchar(200) NOT NULL,
  PRIMARY KEY (`province_id`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_provinces` VALUES (1,39,'Alberta','AB'),(2,39,'British Columbia','BC'),(3,39,'Manitoba','MB'),(4,39,'New Brunswick','NB'),(5,39,'Newfoundland and Labrador','NL'),(6,39,'Northwest Territories','NT'),(7,39,'Nova Scotia','NS'),(8,39,'Nunavut','NU'),(9,39,'Ontario','ON'),(10,39,'Prince Edward Island','PE'),(11,39,'Quebec','QC'),(12,39,'Saskatchewan','SK'),(13,39,'Yukon Territory','YT'),(14,227,'Alabama','AL'),(15,227,'Alaska','AK'),(16,227,'Arizona','AZ'),(17,227,'Arkansas','AR'),(18,227,'California','CA'),(19,227,'Colorado','CO'),(20,227,'Connecticut','CT'),(21,227,'Delaware','DE'),(22,227,'Florida','FL'),(23,227,'Georgia','GA'),(24,227,'Hawaii','HI'),(25,227,'Idaho','ID'),(26,227,'Illinois','IL'),(27,227,'Indiana','IN'),(28,227,'Iowa','IA'),(29,227,'Kansas','KS'),(30,227,'Kentucky','KY'),(31,227,'Louisiana','LA'),(32,227,'Maine','ME'),(33,227,'Maryland','MD'),(34,227,'Massachusetts','MA'),(35,227,'Michigan','MI'),(36,227,'Minnesota','MN'),(37,227,'Mississippi','MS'),(38,227,'Missouri','MO'),(39,227,'Montana','MT'),(40,227,'Nebraska','NE'),(41,227,'Nevada','NV'),(42,227,'New Hampshire','NH'),(43,227,'New Jersey','NJ'),(44,227,'New Mexico','NM'),(45,227,'New York','NY'),(46,227,'North Carolina','NC'),(47,227,'North Dakota','ND'),(48,227,'Ohio','OH'),(49,227,'Oklahoma','OK'),(50,227,'Oregon','OR'),(51,227,'Pennsylvania','PA'),(52,227,'Rhode Island','RI'),(53,227,'South Carolina','SC'),(54,227,'South Dakota','SD'),(55,227,'Tennessee','TN'),(56,227,'Texas','TX'),(57,227,'Utah','UT'),(58,227,'Vermont','VT'),(59,227,'Virginia','VA'),(60,227,'Washington','WA'),(61,227,'West Virginia','WV'),(62,227,'Wisconsin','WI'),(63,227,'Wyoming','WY');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_publication_type` (
  `type_id` int(11) NOT NULL DEFAULT '0',
  `type_description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`),
  KEY `type_description` (`type_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_publication_type` VALUES (1,'Peer-Reviewed Article'),(2,'Non-Peer-Reviewed Article'),(3,'Chapter'),(4,'Peer-Reviewed Abstract'),(5,'Non-Peer-Reviewed Abstract'),(6,'Complete Book'),(7,'Monograph'),(8,'Editorial'),(9,'Published Conference Proceeding'),(10,'Poster Presentations'),(11,'Technical Report');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_roles` (
  `role_id` int(11) NOT NULL DEFAULT '0',
  `role_description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`role_id`),
  KEY `role_description` (`role_description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_roles` VALUES (1,'Lead Author'),(2,'Contributing Author'),(3,'Editor'),(4,'Co-Editor'),(5,'Senior Author'),(6,'Co-Lead');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_lu_schools` (
  `schools_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_title` varchar(250) NOT NULL,
  PRIMARY KEY (`schools_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `global_lu_schools` VALUES (1,'University of Alberta'),(2,'University of British Columbia'),(3,'University of Calgary'),(4,'Dalhousie University'),(5,'Laval University'),(6,'University of Manitoba'),(7,'McGill University'),(8,'McMaster University'),(9,'Memorial University of Newfoundland'),(10,'Universite de Montreal'),(11,'Northern Ontario School of Medicine'),(12,'University of Ottawa'),(13,'Queen\'s University'),(14,'University of Saskatchewan'),(15,'Universite de Sherbrooke'),(16,'University of Toronto'),(17,'University of Western Ontario');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_members` (
  `gmember_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `start_date` bigint(64) NOT NULL DEFAULT '0',
  `finish_date` bigint(64) NOT NULL DEFAULT '0',
  `member_active` int(1) NOT NULL DEFAULT '1',
  `entrada_only` int(1) DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gmember_id`),
  KEY `group_id` (`group_id`,`proxy_id`,`updated_date`,`updated_by`),
  KEY `member_active` (`member_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_organisations` (
  `gorganisation_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`gorganisation_id`),
  KEY `group_id` (`group_id`,`organisation_id`,`updated_date`,`updated_by`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `group_organisations` VALUES (1,1,1,1,1449685604),(2,2,1,1,1449685604),(3,3,1,1,1449685604),(4,4,1,1,1449685604),(5,5,1,1,1449685604);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_type` enum('course_list','cohort') NOT NULL DEFAULT 'course_list',
  `group_value` int(12) DEFAULT NULL,
  `start_date` bigint(64) DEFAULT NULL,
  `expire_date` bigint(64) DEFAULT NULL,
  `group_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`group_id`),
  FULLTEXT KEY `group_title` (`group_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `groups` VALUES (1,'Class of 2015','cohort',NULL,NULL,NULL,1,1449685604,1),(2,'Class of 2016','cohort',NULL,NULL,NULL,1,1449685604,1),(3,'Class of 2017','cohort',NULL,NULL,NULL,1,1449685604,1),(4,'Class of 2018','cohort',NULL,NULL,NULL,1,1449685604,1),(5,'Class of 2019','cohort',NULL,NULL,NULL,1,1449685604,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learning_object_file_permissions` (
  `lo_file_permission_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lo_file_id` int(11) NOT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `permission` enum('read','write','delete') NOT NULL DEFAULT 'read',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learning_object_file_tags` (
  `lo_file_tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lo_file_id` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL DEFAULT '',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `learning_object_files` (
  `lo_file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(11) NOT NULL,
  `mime_type` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `proxy_id` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `updated_date` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lo_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `linked_objectives` (
  `linked_objective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` int(12) NOT NULL,
  `target_objective_id` int(12) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`linked_objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook_entries` (
  `lentry_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) unsigned NOT NULL,
  `encounter_date` int(12) NOT NULL,
  `updated_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `patient_info` varchar(30) NOT NULL,
  `agerange_id` int(12) unsigned NOT NULL DEFAULT '0',
  `gender` varchar(1) NOT NULL DEFAULT '0',
  `course_id` int(12) unsigned NOT NULL DEFAULT '0',
  `llocation_id` int(12) unsigned NOT NULL DEFAULT '0',
  `lsite_id` int(11) NOT NULL,
  `comments` text,
  `reflection` text NOT NULL,
  `entry_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lentry_id`),
  KEY `proxy_id` (`proxy_id`,`entry_active`),
  KEY `proxy_id_2` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook_entry_objectives` (
  `leobjective_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `lentry_id` int(12) unsigned NOT NULL DEFAULT '0',
  `objective_id` int(12) unsigned NOT NULL DEFAULT '0',
  `participation_level` int(12) NOT NULL DEFAULT '3',
  `updated_by` int(11) NOT NULL,
  `updated_date` int(11) DEFAULT NULL,
  `objective_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`leobjective_id`),
  KEY `lentry_id` (`lentry_id`,`objective_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook_lu_ageranges` (
  `agerange_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `agerange` varchar(8) DEFAULT NULL,
  `agerange_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`agerange_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `logbook_lu_ageranges` VALUES (1,'< 1',1),(2,'1 - 4',1),(3,'5 - 12',1),(4,'13 - 19',1),(5,'20 - 64',1),(6,'65 - 74',1),(7,'75+',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook_lu_locations` (
  `llocation_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(64) DEFAULT NULL,
  `location_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`llocation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `logbook_lu_locations` VALUES (1,'Clinic',1),(2,'Ward',1),(3,'Emergency',1),(4,'ICU',1),(5,'Private Office',1),(6,'OR',1),(7,'NICU',1),(8,'Nursing Home',1),(9,'Community Site',1),(10,'Computer Interactive Case',1),(11,'Other (provide details in additional comments field)',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logbook_lu_sites` (
  `lsite_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(64) NOT NULL,
  `site_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`lsite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `logbook_lu_sites` VALUES (1,'Brockville General Hospital',1),(2,'Brockville Pyschiatric Hospital',1),(3,'Hotel Dieu Hospital (Kingston)',1),(4,'Kingston General Hospital',1),(5,'Lakeridge Health',1),(6,'Markam Stouffville Hospital',1),(7,'Perth Family Health Team',1),(8,'Perth/Smiths Falls District Hospital',1),(9,'Peterborough Regional Health Centre',1),(10,'Providence Care Centre',1),(11,'Quinte Health Care',1),(12,'Weenebayko General Hospital',1),(13,'Other (provide details in additional comments field)',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map_assessments_meta` (
  `map_assessments_meta_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_assessment_method_id` int(11) NOT NULL,
  `fk_assessments_meta_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_assessments_meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map_event_resources` (
  `map_event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_medbiq_resource_id` int(11) DEFAULT NULL,
  `fk_resource_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map_events_eventtypes` (
  `map_events_eventtypes_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_instructional_method_id` int(11) NOT NULL,
  `fk_eventtype_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_events_eventtypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medbiq_assessment_methods` (
  `assessment_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `assessment_method` varchar(250) NOT NULL DEFAULT '',
  `assessment_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assessment_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `medbiq_assessment_methods` VALUES (1,'AM001','Clinical Documentation Review','The review and assessment of clinical notes and logs kept by learners as part of practical training in the clinical setting (Bowen & Smith, 2010; Irby, 1995)',1,0,0),(2,'AM002','Clinical Performance Rating/Checklist','A non-narrative assessment tool (checklist, Likert-type scale, other instrument) used to note completion or\rachievement of learning tasks (MacRae, Vu, Graham, Word-Sims, Colliver, & Robbs, 1995; Turnbull, Gray, & MacFadyen, 1998) also see ?Direct Observations or Performance Audits,? Institute for International Medical Education, 2002)',1,0,0),(3,'AM003','Exam - Institutionally Developed, Clinical Performance','Practical performance-based examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011) (Includes observation of learner or small group by instructor)',1,0,0),(4,'AM004','Exam - Institutionally Developed, Written/Computer-based','Examination utilizing various written question-and-answer formats (multiple-choice, short answer, essay, etc.) which may assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning (Cooke, Irby, & O?Brien, 2010b; LCME, 2011)',1,0,0),(5,'AM005','Exam - Institutionally Developed, Oral','Verbal examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011)',1,0,0),(6,'AM006','Exam - Licensure, Clinical Performance','Practical, performance-based examination developed by a professional licensing body to assess clinical skills such as problem solving, clinical reasoning, decision making, and communication, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a written/computer-based component (MCC, 2011a & 2011c; NBOME, 2010b; USMLE, n.d.); may also be used by schools to assess learners? achievement of certain curricular objectives',1,0,0),(7,'AM007','Exam - Licensure, Written/Computer-based','Standardized written examination administered to assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a clinical performance component (MCC, 2011a & 2011b; NBOME, 2010b; USMLE, n.d.); may also be used by schools or learners themselves to assess achievement of certain curricular objectives',1,0,0),(8,'AM008','Exam - Nationally Normed/Standardized, Subject','Standardized written examination administered to assess learners? achievement of nationally established educational expectations for various levels of training and/or specialized subject area(s) (e.g., NBME Subject or ?Shelf? Exam) (NBME, 2011; NBOME, 2010a)',1,0,0),(9,'AM009','Multisource Assessment','A formal assessment of performance by supervisors, peers, patients, and coworkers (Bowen & Smith, 2010; Institute for International Medical Education, 2002) (Also see Peer Assessment)',1,0,0),(10,'AM010','Narrative Assessment','An instructor\'s or observer\'s written subjective assessment of a learner\'s work or performance (Mennin, McConnell, & Anderson, 1997); May Include: Comments within larger assessment; Observation of learner or small group by instructor',1,0,0),(11,'AM011','Oral Patient Presentation','The presentation of clinical case (patient) findings, history and physical, differential diagnosis, treatment plan, etc., by a learner to an instructor or small group, and subsequent discussion with the instructor and/or small group for the purposes of learner demonstrating skills in clinical reasoning, problem-solving, etc.\r(Wiener, 1974)',1,0,0),(12,'AM012','Participation','Sharing or taking part in an activity (Education Resources Information Center, 1966b)',1,0,0),(13,'AM013','Peer Assessment','The concurrent or retrospective review by learners of the quality and efficiency of practices or services ordered or performed by fellow learners (based on MeSH Scope Note for \"Peer Review, Health Care,\" U.S. National Library of Medicine, 1992)',1,0,0),(14,'AM014','Portfolio-Based Assessment','Review of a learner\'s achievement of agreed-upon academic objectives or completion of a negotiated set of learning activities, based on a learner portfolio (Institute for International Medical Education, 2002) (\"a systematic collection of a student\'s work samples, records of observation, test results, etc., over a period of time\"? Education Resources Information Center, 1994)',1,0,0),(15,'AM015','Practical (Lab)','Learner engagement in hands-on or simulated exercises in which they collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),(16,'AM016','Research or Project Assessment','Assessment of activities and outcomes (e.g., posters, presentations, reports, etc.) of a project in which the learner participated or conducted research (Dyrbye, Davidson, & Cook, 2008)',1,0,0),(17,'AM017','Self-Assessment','The process of evaluating one?s own deficiencies, achievements, behavior or professional performance and competencies (Institute for International Medical Education, 2002); Assessment completed by the learner to reflect and critically assess his/her own performance against a set of established criteria (Gordon, 1991) (NOTE: Does not refer to NBME Self-Assessment)',1,0,0),(18,'AM018','Stimulated Recall','The use of various stimuli (e.g., written records, audio tapes, video tapes) to re-activate the experience of a learner during a learning activity or clinical encounter in order to reflect on task performance, reasoning, decision-making, interpersonal skills, personal thoughts and feelings, etc. (Barrows, 2000)',1,0,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medbiq_instructional_methods` (
  `instructional_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `instructional_method` varchar(250) NOT NULL DEFAULT '',
  `instructional_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`instructional_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `medbiq_instructional_methods` VALUES (1,'IM001','Case-Based Instruction/Learning','The use of patient cases (actual or theoretical) to stimulate discussion, questioning, problem solving, and reasoning on issues pertaining to the basic sciences and clinical disciplines (Anderson, 2010)',1,0,0),(2,'IM002','Clinical Experience - Ambulatory','Practical experience(s) in patient care and health-related services carried out in an ambulatory/outpatient\rsetting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),(3,'IM003','Clinical Experience - Inpatient','Practical experience(s) in patient care and health-related services carried out in an inpatient setting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),(4,'IM004','Concept Mapping','Technique [that] allows learners to organize and represent knowledge in an explicit interconnected network. Linkages between concepts are explored to make apparent connections that are not usually seen. Concept mapping also encourages the asking of questions about relationships between concepts that may not have been presented in traditional courses, standard texts, and teaching materials. It shifts the focus of learning away from rote acquisition of information to visualizing the underlying concepts that provide the cognitive\rframework of what the learner already knows, to facilitate the acquisition of new knowledge (Weiss & Levinson, 2000, citing Novak & Gowin, 1984)',1,0,0),(5,'IM005','Conference',NULL,1,0,0),(6,'IM006','Demonstration','A description, performance, or explanation of a process, illustrated by examples, observable action, specimens, etc. (Dictionary.com, n.d.)',1,0,0),(7,'IM007','Discussion, Large Group (>13)','An exchange (oral or written) of opinions, observations, or ideas among a Large Group [more than 12\rparticipants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),(8,'IM008','Discussion, Small Group (<12)','An exchange (oral or written) of opinions, observations, or ideas among a Small Group [12 or fewer participants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),(9,'IM009','Games','Individual or group games that have cognitive, social, behavioral, and/or emotional, etc., dimensions which are related to educational objectives (Education Resources Information Center, 1966a)',1,0,0),(10,'IM010','Independent Learning','Instructor-/ or mentor-guided learning activities to be performed by the learner outside of formal educational settings (classroom, lab, clinic) (Bowen & Smith, 2010); Dedicated time on learner schedules to prepare for specific learning activities, e.g., case discussions, TBL, PBL, clinical activities, research project(s)',1,0,0),(11,'IM011','Journal Club','A forum in which participants discuss recent research papers from field literature in order to develop\rcritical reading skills (comprehension, analysis, and critique) (Cooke, Irby, & O\'Brien, 2010a; Mann & O\'Neill, 2010; Woods & Winkel, 1982)',1,0,0),(12,'IM012','Laboratory','Hands-on or simulated exercises in which learners collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),(13,'IM013','Lecture','An instruction or verbal discourse by a speaker before a large group of learners (Institute for International Medical Education, 2002)',1,0,0),(14,'IM014','Mentorship','The provision of guidance, direction and support by senior professionals to learners or more junior professionals (U.S. National Library of Medicine, 1987)',1,0,0),(15,'IM015','Patient Presentation - Faculty','A presentation by faculty of patient findings, history and physical, differential diagnosis, treatment plan,\retc. (Wiener, 1974)',1,0,0),(16,'IM016','Patient Presentation - Learner','A presentation by a learner or learners to faculty, resident(s), and/or other learners of patient findings, history and physical, differential diagnosis, treatment plan, etc. (Wiener, 1974)',1,0,0),(17,'IM017','Peer Teaching','Learner-to-learner instruction for the mutual learning experience of both \"teacher\" and \"learner\"; may be \"peer-to-peer\" (same training level) or \"near-peer\" (higher-level learner teaching lower-level learner)\r(Soriano et al., 2010)',1,0,0),(18,'IM018','Preceptorship','Practical experience in medical and health-related services wherein the professionally-trained learner works\runder the supervision of an established professional in the particular field (U. S. National Library of Medicine, 1974)',1,0,0),(19,'IM019','Problem-Based Learning (PBL)','The use of carefully selected and designed patient cases that demand from the learner acquisition of critical\rknowledge, problem solving proficiency, self-directed learning strategies, and team participation skills as those needed in professional practice (Eshach & Bitterman, 2003; see also Major & Palmer, 2001; Cooke, Irby, & O\'Brien, 2010b;\rBarrows & Tamblyn, 1980)',1,0,0),(20,'IM020','Reflection','Examination by the learner of his/her personal experiences of a learning event, including the cognitive, emotional, and affective aspects; the use of these past experiences in combination with objective information\rto inform present clinical decision-making and problem-solving (Mann, Gordon, & MacLeod, 2009; Mann & O\'Neill, 2010)',1,0,0),(21,'IM021','Research','Short-term or sustained participation in research',1,0,0),(22,'IM022','Role Play/Dramatization','The adopting or performing the role or activities of another individual',1,0,0),(23,'IM023','Self-Directed Learning','Learners taking the initiative for their own learning: diagnosing needs, formulating goals, identifying resources, implementing appropriate activities, and evaluating outcomes (Garrison, 1997; Spencer & Jordan, 1999)',1,0,0),(24,'IM024','Service Learning Activity','A structured learning experience that combines community service with preparation and reflection (LCME, 2011)',1,0,0),(25,'IM025','Simulation','A method used to replace or amplify real patient encounters with scenarios designed to replicate real health care situations, using lifelike mannequins, physical models, standardized patients, or computers (Passiment,\rSacks, & Huang, 2011)',1,0,0),(26,'IM026','Team-Based Learning (TBL)','A form of collaborative learning that follows a specific sequence of individual work, group work and immediate feedback; engages learners in learning activities within a small group that works independently in classes with high learner-faculty ratios (Anderson, 2010; Team-Based Learning Collaborative, n.d.; Thompson, Schneider, Haidet, Perkowski, & Richards, 2007)',1,0,0),(27,'IM027','Team-Building','Workshops, sessions, and/or activities contributing to the development of teamwork skills, often as a foundation for group work in learning (PBL, TBL, etc.) and practice (interprofessional/-disciplinary, etc.)\r(Morrison, Goldfarb, & Lanken, 2010)',1,0,0),(28,'IM028','Tutorial','Instruction provided to a learner or small group of learners by direct interaction with an instructor (Education\rResources Information Center, 1966c)',1,0,0),(29,'IM029','Ward Rounds','An instructional session conducted in an actual clinical setting, using real patients or patient cases to demonstrate procedures or clinical skills, illustrate clinical reasoning and problem-solving, or stimulate discussion and analytical thinking among a group of learners (Bowen & Smith, 2010; Wiener, 1974)',1,0,0),(30,'IM030','Workshop','A brief intensive educational program for a relatively small group of people that focuses especially on techniques and skills related to a specific topic (U. S. National Library of Medicine, 2011)',1,0,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medbiq_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(250) NOT NULL DEFAULT '',
  `resource_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `medbiq_resources` VALUES (1,'Audience Response System','An electronic communication system that allows groups of people to vote on a topic or answer a question. Each person has a remote control (\"clicker\") with which selections can be made; Typically, the results are\rinstantly made available to the participants via a graph displayed on the projector. (Group on Information Resources, 2011; Stoddard & Piquette, 2010)',1,0,0),(2,'Audio','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using auditory delivery (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),(3,'Cadaver','A human body preserved post-mortem and \"used...to study anatomy, identify disease sites, determine causes of death, and provide tissue to repair a defect in a living human being\" (MedicineNet.com, 2004)',1,0,0),(4,'Clinical Correlation','The application and elaboration of concepts introduced in lecture, reading assignments, independent study, and other learning activities to real patient or case scenarios in order to promote knowledge retrieval in similar clinical situations at a later time (Euliano, 2001)',1,0,0),(5,'Distance Learning - Asynchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, and which \"does not occur in real time or involve simultaneous interaction on the part of participants. It is intermittent and generally characterized by a significant time delay or interval between sending and receiving or responding to messages\" (Education Resources Information Center, 1983; 2008a)',1,0,0),(6,'Distance Learning - Synchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, \"in real time, characterized by concurrent exchanges between participants. Interaction is simultaneous without a meaningful time delay between sending a message and receiving or responding to it. Occurs in electronic (e.g., interactive videoconferencing) and non-electronic environments (e.g., telephone conversations)\" (Education Resources Information Center, 1983; 2008c)',1,0,0),(7,'Educational Technology','Mobile or desktop technology (hardware or software) used for instruction/learning through audiovisual (A/V), multimedia, web-based, or online modalities (Group on Information Resources, 2011); Sometimes includes dedicated space (see Virtual/Computerized Lab)',1,0,0),(8,'Electronic Health/Medical Record (EHR/EMR)','An individual patient\'s medical record in digital format...usually accessed on a computer, often over a network...[M]ay be made up of electronic medical records (EMRs) from many locations and/or sources. An Electronic Medical Record (EMR) may be an inpatient or outpatient medical record in digital format that may or may not be linked to or part of a larger EHR (Group on Information Resources, 2011)',1,0,0),(9,'Film/Video','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using visual recordings (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),(10,'Key Feature','An element specific to a clinical case or problem that demands the use of particular clinical skills in order to achieve the problem\'s successful resolution; Typically presented as written exam questions, as in the Canadian Qualifying Examination in Medicine (Page & Bordage, 1995; Page, Bordage, & Allen, 1995)',1,0,0),(11,'Mannequin','A life-size model of the human body that mimics various anatomical functions to teach skills and procedures in health education; may be low-fidelity (having limited or no electronic inputs) or high-fidelity\r(connected to a computer that allows the robot to respond dynamically to user input) (Group on Information Resources, 2011; Passiment, Sacks, & Huang, 2011)',1,0,0),(12,'Plastinated Specimens','Organic material preserved by replacing water and fat in tissue with silicone, resulting in \"anatomical specimens [that] are safer to use, more pleasant to use, and are much more durable and have a much longer shelf life\" (University of Michigan Plastination Lab, n.d.); See also: Wet Lab',1,0,0),(13,'Printed Materials (or Digital Equivalent)','Reference materials produced or selected by faculty to augment course teaching and learning',1,0,0),(14,'Real Patient','An actual clinical patient',1,0,0),(15,'Searchable Electronic Database','A collection of information organized in such a way that a computer program can quickly select desired pieces of data (Webopedia, n.d.)',1,0,0),(16,'Standardized/Simulated Patient (SP)','Individual trained to portray a patient with a specific condition in a realistic, standardized and repeatable way (where portrayal/presentation varies based only on learner performance) (ASPE, 2011)',1,0,0),(17,'Task Trainer','A physical model that simulates a subset of physiologic function to include normal and abnormal anatomy (Passiment, Sacks, & Huang, 2011); Such models which provide just the key elements of the task or skill being learned (CISL, 2011)',1,0,0),(18,'Virtual Patient','An interactive computer simulation of real-life clinical scenarios for the purpose of medical training, education, or assessment (Smothers, Azan, & Ellaway, 2010)',1,0,0),(19,'Virtual/Computerized Laboratory','A practical learning environment in which technology- and computer-based simulations allow learners to engage in computer-assisted instruction while being able to ask and answer questions and also engage in discussion of content (Cooke, Irby, & O\'Brien, 2010a); also, to learn through experience by performing medical tasks, especially high-risk ones, in a safe environment (Uniformed Services University, 2011)',1,0,0),(20,'Wet Laboratory','Facilities outfitted with specialized equipment* and bench space or adjustable, flexible desktop space for working with solutions or biological materials (\"C.1 Wet Laboratories,\" 2006; Stanford University School of Medicine, 2007;\rWBDG Staff, 2010) *Often includes sinks, chemical fume hoods, biosafety cabinets, and piped services such as deionized or RO water, lab cold and hot water, lab waste/vents, carbon dioxide, vacuum, compressed air, eyewash, safety showers, natural gas, telephone, LAN, and power (\"C.1 Wet Laboratories,\" 2006)',1,0,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_type_relations` (
  `meta_data_relation_id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_type_id` int(10) unsigned DEFAULT NULL,
  `entity_type` varchar(63) NOT NULL,
  `entity_value` varchar(63) NOT NULL,
  PRIMARY KEY (`meta_data_relation_id`),
  UNIQUE KEY `meta_type_id` (`meta_type_id`,`entity_type`,`entity_value`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `meta_type_relations` VALUES (1,1,'organisation:group','1:student'),(2,7,'organisation:group','1:student'),(3,3,'organisation:group','1:student'),(4,4,'organisation:group','1:student'),(5,5,'organisation:group','1:student'),(6,8,'organisation:group','1:student'),(7,9,'organisation:group','1:student'),(8,10,'organisation:group','1:student'),(9,11,'organisation:group','1:student'),(10,12,'organisation:group','1:student'),(11,13,'organisation:group','1:student'),(12,14,'organisation:group','1:student'),(13,15,'organisation:group','1:student'),(14,16,'organisation:group','1:student'),(15,17,'organisation:group','1:student'),(16,18,'organisation:group','1:student'),(17,20,'organisation:group','1:student'),(18,21,'organisation:group','1:student');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_types` (
  `meta_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `parent_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`meta_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `meta_types` VALUES (1,'N95 Mask Fit','Make, Model, and size definition of required N95 masks.',NULL),(2,'Police Record Check','Police Record Checks to verify background as clear of events which could prevent placement in hospitals or clinics.',NULL),(3,'Full','Full record check. Due to differences in how police departments handle reporting of background checks, vulnerable sector screening (VSS) is a separate type of record',2),(4,'Vulnerable Sector Screening','Required for placement in hospitals or clinics. May be included in full police record checks or may be a separate document.',2),(5,'Assertion','Yearly or bi-yearly assertion that prior police background checks remain valid.',2),(6,'Immunization/Health Check','',NULL),(7,'Hepatitis B','',6),(8,'Tuberculosis','',6),(9,'Measles','',6),(10,'Mumps','',6),(11,'Rubella','',6),(12,'Tetanus/Diptheria','',6),(13,'Polio','',6),(14,'Varicella','',6),(15,'Pertussis','',6),(16,'Influenza','Each student is required to obtain an annual influenza immunization. The Ontario government provides the influenza vaccine free to all citizens during the flu season. Students will be required to follow Public Health guidelines put forward for health care professionals. Thia immunization must be received by December 1st each academic year and documentation forwarded to the UGME office by the student',6),(17,'Hepatitis C','',6),(18,'HIV','',6),(19,'Cardiac Life Support','',NULL),(20,'Basic','',19),(21,'Advanced','',19);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_values` (
  `meta_value_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meta_type_id` int(10) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL,
  `data_value` varchar(255) NOT NULL,
  `value_notes` text NOT NULL,
  `effective_date` bigint(20) DEFAULT NULL,
  `expiry_date` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`meta_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `migration` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  `success` int(4) NOT NULL DEFAULT '0',
  `fail` int(4) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `migrations` VALUES ('2015_01_28_143720_556',1,0,0,1450108251),('2015_10_05_115238_571',1,1,0,1450108251),('2015_10_07_140708_607',1,1,0,1450108251),('2015_11_09_114101_211',1,3,0,1450108251),('2015_11_19_141523_555',1,1,0,1450108251),('2015_12_14_100257_655',1,0,0,1450108251);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_categories` (
  `id` int(11) NOT NULL,
  `category_code` varchar(3) NOT NULL,
  `category_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_facilities` (
  `id` int(11) NOT NULL,
  `facility_code` int(3) NOT NULL,
  `facility_name` varchar(50) NOT NULL,
  `kingston` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_locale_duration` (
  `id` int(11) NOT NULL,
  `location_id` int(3) NOT NULL,
  `percent_time` int(3) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_moh_program_codes` (
  `id` int(11) NOT NULL,
  `program_code` varchar(3) NOT NULL,
  `program_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_moh_service_codes` (
  `id` int(11) NOT NULL,
  `service_code` varchar(3) NOT NULL,
  `service_description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_pgme_moh_programs` (
  `id` int(11) NOT NULL,
  `pgme_program_name` varchar(100) NOT NULL,
  `moh_service_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `service_id` int(3) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `creator_id` int(12) NOT NULL,
  `type_code` varchar(1) NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(12) NOT NULL,
  `category_id` int(3) DEFAULT NULL,
  `home_program_id` int(3) DEFAULT NULL,
  `home_school_id` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_schools` (
  `id` int(11) NOT NULL,
  `school_code` varchar(3) NOT NULL,
  `school_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtd_type` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `type_code` varchar(1) NOT NULL,
  `type_description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `mtd_type` VALUES (1,'I','in-patient/emergency'),(2,'O','out-patient');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notice_audience` (
  `naudience_id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_id` int(11) NOT NULL,
  `audience_type` varchar(20) NOT NULL,
  `audience_value` int(11) NOT NULL DEFAULT '0',
  `updated_by` int(11) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`naudience_id`),
  KEY `audience_id` (`notice_id`,`audience_type`,`audience_value`,`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notices` (
  `notice_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) DEFAULT NULL,
  `notice_summary` text NOT NULL,
  `notice_details` text NOT NULL,
  `display_from` bigint(64) NOT NULL DEFAULT '0',
  `display_until` bigint(64) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `created_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notice_id`),
  KEY `display_from` (`display_from`),
  KEY `display_until` (`display_until`),
  KEY `organisation_id` (`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_users` (
  `nuser_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `content_type` varchar(32) NOT NULL DEFAULT '',
  `record_id` int(11) NOT NULL,
  `record_proxy_id` int(11) DEFAULT NULL,
  `notify_active` tinyint(1) NOT NULL DEFAULT '0',
  `digest_mode` tinyint(1) NOT NULL DEFAULT '0',
  `next_notification_date` int(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nuser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nuser_id` int(11) NOT NULL,
  `notification_body` text NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_date` bigint(64) DEFAULT '0',
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objective_audience` (
  `oaudience_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL,
  `audience_type` enum('COURSE','EVENT') NOT NULL DEFAULT 'COURSE',
  `audience_value` varchar(12) NOT NULL DEFAULT '',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) NOT NULL,
  PRIMARY KEY (`oaudience_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `objective_audience` VALUES (1,1,1,'COURSE','all',0,0),(2,200,1,'COURSE','all',0,0),(3,309,1,'COURSE','all',0,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objective_organisation` (
  `objective_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL,
  PRIMARY KEY (`objective_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `objective_organisation` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1),(182,1),(183,1),(184,1),(185,1),(186,1),(187,1),(188,1),(189,1),(190,1),(191,1),(200,1),(201,1),(202,1),(203,1),(204,1),(205,1),(206,1),(207,1),(208,1),(209,1),(210,1),(211,1),(212,1),(213,1),(214,1),(215,1),(216,1),(217,1),(218,1),(219,1),(221,1),(222,1),(223,1),(224,1),(225,1),(226,1),(228,1),(233,1),(234,1),(235,1),(236,1),(237,1),(238,1),(239,1),(240,1),(241,1),(242,1),(257,1),(258,1),(259,1),(260,1),(261,1),(262,1),(263,1),(264,1),(265,1),(266,1),(267,1),(268,1),(269,1),(270,1),(271,1),(272,1),(273,1),(274,1),(275,1),(276,1),(277,1),(278,1),(279,1),(280,1),(281,1),(282,1),(283,1),(284,1),(286,1),(287,1),(288,1),(289,1),(290,1),(291,1),(292,1),(293,1),(294,1),(295,1),(296,1),(299,1),(300,1),(303,1),(304,1),(305,1),(306,1),(307,1),(308,1),(309,1),(310,1),(311,1),(312,1),(313,1),(314,1),(315,1),(316,1),(317,1),(318,1),(319,1),(320,1),(321,1),(322,1),(323,1),(324,1),(325,1),(326,1),(327,1),(328,1),(329,1),(330,1),(331,1),(332,1),(333,1),(334,1),(335,1),(336,1),(337,1),(338,1),(339,1),(340,1),(341,1),(342,1),(343,1),(344,1),(345,1),(346,1),(347,1),(348,1),(349,1),(350,1),(351,1),(352,1),(353,1),(354,1),(355,1),(356,1),(357,1),(358,1),(359,1),(360,1),(361,1),(362,1),(363,1),(364,1),(365,1),(366,1),(367,1),(368,1),(369,1),(370,1),(371,1),(372,1),(373,1),(374,1),(375,1),(376,1),(377,1),(378,1),(379,1),(380,1),(381,1),(382,1),(383,1),(384,1),(385,1),(386,1),(387,1),(388,1),(389,1),(390,1),(391,1),(392,1),(393,1),(394,1),(395,1),(396,1),(397,1),(398,1),(399,1),(400,1),(401,1),(402,1),(403,1),(404,1),(405,1),(406,1),(407,1),(408,1),(409,1),(410,1),(411,1),(412,1),(413,1),(414,1),(415,1),(416,1),(417,1),(418,1),(419,1),(420,1),(421,1),(422,1),(423,1),(424,1),(425,1),(426,1),(427,1),(428,1),(429,1),(430,1),(431,1),(432,1),(433,1),(434,1),(435,1),(436,1),(437,1),(438,1),(439,1),(440,1),(441,1),(442,1),(443,1),(444,1),(445,1),(446,1),(447,1),(448,1),(449,1),(450,1),(451,1),(452,1),(453,1),(454,1),(455,1),(456,1),(457,1),(458,1),(459,1),(460,1),(461,1),(462,1),(463,1),(464,1),(465,1),(466,1),(467,1),(468,1),(469,1),(470,1),(471,1),(472,1),(473,1),(474,1),(475,1),(476,1),(477,1),(478,1),(479,1),(480,1),(481,1),(482,1),(483,1),(484,1),(485,1),(486,1),(487,1),(488,1),(489,1),(490,1),(491,1),(492,1),(493,1),(494,1),(495,1),(496,1),(497,1),(498,1),(499,1),(500,1),(501,1),(502,1),(503,1),(504,1),(505,1),(506,1),(507,1),(508,1),(509,1),(510,1),(511,1),(512,1),(513,1),(514,1),(515,1),(516,1),(517,1),(518,1),(519,1),(520,1),(521,1),(522,1),(523,1),(524,1),(525,1),(526,1),(527,1),(528,1),(529,1),(2328,1),(2329,1),(2330,1),(2331,1),(2332,1),(2333,1),(2334,1),(2335,1),(2336,1),(2337,1),(2338,1),(2339,1),(2340,1),(2341,1),(2342,1),(2343,1),(2344,1),(2345,1),(2346,1),(2347,1),(2348,1),(2349,1),(2350,1),(2351,1),(2352,1),(2353,1),(2354,1),(2355,1),(2356,1),(2357,1),(2358,1),(2359,1),(2360,1),(2361,1),(2362,1),(2363,1),(2364,1),(2365,1),(2366,1),(2367,1),(2368,1),(2369,1),(2370,1),(2371,1),(2372,1),(2373,1),(2374,1),(2375,1),(2376,1),(2377,1),(2378,1),(2379,1),(2380,1),(2381,1),(2382,1),(2383,1),(2384,1),(2385,1),(2386,1),(2387,1),(2388,1),(2389,1),(2390,1),(2391,1),(2392,1),(2393,1),(2394,1),(2395,1),(2396,1),(2397,1),(2398,1),(2399,1),(2400,1),(2401,1),(2402,1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observership_reflections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `observership_id` int(11) NOT NULL,
  `physicians_role` text NOT NULL,
  `physician_reflection` text NOT NULL,
  `role_practice` text,
  `observership_challenge` text NOT NULL,
  `discipline_reflection` text NOT NULL,
  `challenge_predictions` text,
  `questions` text,
  `career` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `org_community_types` (
  `octype_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `community_type_name` varchar(84) NOT NULL DEFAULT '',
  `default_community_template` varchar(30) NOT NULL DEFAULT 'default',
  `default_community_theme` varchar(12) NOT NULL DEFAULT 'default',
  `default_community_keywords` varchar(255) NOT NULL DEFAULT '',
  `default_community_protected` int(1) NOT NULL DEFAULT '1',
  `default_community_registration` int(1) NOT NULL DEFAULT '1',
  `default_community_members` text NOT NULL,
  `default_mail_list_type` enum('announcements','discussion','inactive') NOT NULL DEFAULT 'inactive',
  `community_type_options` text NOT NULL,
  `community_type_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`octype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `org_community_types` VALUES (1,1,'Community','default','default','',1,0,'','inactive','{}',1),(2,1,'Course Website','course','course','',1,0,'','inactive','{\"course_website\":\"1\"}',1),(3,1,'Learning Module','learningmodule','default','',1,0,'','inactive','{\"sequential_navigation\":\"1\"}',1);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organisation_lu_restricted_days` (
  `orday_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `date_type` enum('specific','computed','weekly','monthly') NOT NULL DEFAULT 'specific',
  `offset` tinyint(1) DEFAULT NULL,
  `day` tinyint(2) DEFAULT NULL,
  `month` tinyint(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `updated_date` int(12) NOT NULL,
  `updated_by` int(12) NOT NULL,
  `day_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`orday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `permission_id` int(12) NOT NULL AUTO_INCREMENT,
  `assigned_by` int(12) NOT NULL DEFAULT '0',
  `assigned_to` int(12) NOT NULL DEFAULT '0',
  `valid_from` bigint(64) NOT NULL DEFAULT '0',
  `valid_until` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `assigned_to` (`assigned_to`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pg_blocks` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `block_name` varchar(8) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `year` varchar(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `pg_blocks` VALUES (1,'1','2010-07-01','2010-07-26','2010-2011'),(2,'2','2010-07-27','2010-08-23','2010-2011'),(3,'3','2010-08-24','2010-09-20','2010-2011'),(4,'4','2010-09-21','2010-10-18','2010-2011'),(5,'5','2010-10-19','2010-11-15','2010-2011'),(6,'6','2010-11-16','2010-12-13','2010-2011'),(7,'7','2010-12-14','2011-01-10','2010-2011'),(8,'8','2011-01-11','2011-02-07','2010-2011'),(9,'9','2011-02-08','2011-03-07','2010-2011'),(10,'10','2011-03-08','2011-04-04','2010-2011'),(11,'11','2011-04-05','2011-05-02','2010-2011'),(12,'12','2011-05-03','2011-05-30','2010-2011'),(13,'13','2011-05-31','2011-06-30','2010-2011'),(14,'1','2011-07-01','2011-08-01','2011-2012'),(15,'2','2011-08-02','2011-08-29','2011-2012'),(16,'3','2011-08-30','2011-09-26','2011-2012'),(17,'4','2011-09-27','2011-10-24','2011-2012'),(18,'5','2011-10-25','2011-11-21','2011-2012'),(19,'6','2011-11-22','2011-12-19','2011-2012'),(20,'7','2012-12-20','2012-01-16','2011-2012'),(21,'8','2012-01-17','2012-02-13','2011-2012'),(22,'9','2012-02-14','2012-03-12','2011-2012'),(23,'10','2012-03-13','2012-04-09','2011-2012'),(24,'11','2012-04-10','2012-05-07','2011-2012'),(25,'12','2012-05-08','2012-06-04','2011-2012'),(26,'13','2012-06-05','2012-06-30','2011-2012');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pg_eval_response_rates` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `response_type` varchar(20) NOT NULL,
  `completed` int(10) NOT NULL,
  `distributed` int(10) NOT NULL,
  `percent_complete` int(3) NOT NULL,
  `gen_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pg_one45_community` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `one45_name` varchar(50) NOT NULL,
  `community_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_answers` (
  `answer_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_id` int(12) NOT NULL DEFAULT '0',
  `answer_text` varchar(255) NOT NULL,
  `answer_order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`answer_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_order` (`answer_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_questions` (
  `poll_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_target_type` enum('group','grad_year','cohort') NOT NULL,
  `poll_target` varchar(32) NOT NULL DEFAULT 'all',
  `poll_question` text NOT NULL,
  `poll_from` bigint(64) NOT NULL DEFAULT '0',
  `poll_until` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `poll_target` (`poll_target`),
  KEY `poll_from` (`poll_from`),
  KEY `poll_until` (`poll_until`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poll_results` (
  `result_id` int(12) NOT NULL AUTO_INCREMENT,
  `poll_id` int(12) NOT NULL DEFAULT '0',
  `answer_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL,
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`result_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_id` (`answer_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio-advisors` (
  `padvisor_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int(11) NOT NULL,
  `portfolio_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`padvisor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_artifact_permissions` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(10) unsigned NOT NULL,
  `allow_to` int(10) unsigned NOT NULL COMMENT 'Who allowed to access',
  `proxy_id` int(10) unsigned NOT NULL COMMENT 'Who has created this permission',
  `view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  KEY `portfolio_user_permissions_pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_user_permissions_pentry_id` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_entries` (
  `pentry_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `submitted_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `reviewed_by` int(10) unsigned NOT NULL,
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `flagged_by` int(10) unsigned NOT NULL,
  `flagged_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text NOT NULL,
  `_class` varchar(200) NOT NULL,
  `order` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('file','reflection','url') NOT NULL DEFAULT 'reflection',
  PRIMARY KEY (`pentry_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to record portfolio entries made by learners.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_entry_comments` (
  `pecomment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pentry_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `comment` text NOT NULL,
  `submitted_date` bigint(64) unsigned NOT NULL,
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pecomment_id`),
  KEY `pentry_id` (`pentry_id`),
  CONSTRAINT `portfolio_entry_comments_ibfk_1` FOREIGN KEY (`pentry_id`) REFERENCES `portfolio_entries` (`pentry_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to store comments on particular portfolio entries.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_folder_artifact_reviewers` (
  `pfareviewer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfartifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfareviewer_id`),
  KEY `pfartifact_id` (`pfartifact_id`),
  CONSTRAINT `portfolio_folder_artifact_reviewers_ibfk_1` FOREIGN KEY (`pfartifact_id`) REFERENCES `portfolio_folder_artifacts` (`pfartifact_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List teachers responsible for reviewing an artifact.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_folder_artifacts` (
  `pfartifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pfolder_id` int(11) unsigned NOT NULL,
  `artifact_id` int(11) unsigned NOT NULL,
  `proxy_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `finish_date` bigint(64) unsigned NOT NULL DEFAULT '0',
  `allow_commenting` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `_edata` text,
  `handler_object` varchar(80) NOT NULL,
  PRIMARY KEY (`pfartifact_id`),
  KEY `pfolder_id` (`pfolder_id`),
  KEY `artifact_id` (`artifact_id`),
  CONSTRAINT `portfolio_folder_artifacts_ibfk_1` FOREIGN KEY (`pfolder_id`) REFERENCES `portfolio_folders` (`pfolder_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `portfolio_folder_artifacts_ibfk_2` FOREIGN KEY (`artifact_id`) REFERENCES `portfolios_lu_artifacts` (`artifact_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of artifacts within a particular portfolio folder.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_folders` (
  `pfolder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) unsigned NOT NULL,
  `title` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `allow_learner_artifacts` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`pfolder_id`),
  KEY `portfolio_id` (`portfolio_id`),
  CONSTRAINT `portfolio_folders_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios` (`portfolio_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The list of folders within each portfolio.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolios` (
  `portfolio_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(4) unsigned NOT NULL,
  `portfolio_name` varchar(100) NOT NULL,
  `start_date` bigint(64) unsigned NOT NULL,
  `finish_date` bigint(64) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  `organisation_id` int(11) NOT NULL,
  `allow_student_export` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`portfolio_id`),
  UNIQUE KEY `grad_year_unique` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The portfolio container for each class of learners.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolios_lu_artifacts` (
  `artifact_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `handler_object` varchar(80) NOT NULL COMMENT 'PHP class which handles displays form to user.',
  `allow_learner_addable` tinyint(1) NOT NULL DEFAULT '0',
  `order` int(3) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL COMMENT 'proxy_id of users from entrada_auth.user_data.id',
  PRIMARY KEY (`artifact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookup table that stores all available types of artifacts.';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_custom_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL DEFAULT '',
  `type` enum('TEXTAREA','TEXTINPUT','CHECKBOX','RICHTEXT','LINK') NOT NULL DEFAULT 'TEXTAREA',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `length` smallint(3) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_custom_responses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `proxy_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_contacts` (
  `qcontact_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qcontact_id`),
  KEY `quiz_id` (`quiz_id`,`proxy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_progress` (
  `qprogress_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `aquiz_id` int(12) unsigned NOT NULL,
  `content_type` enum('event','community_page') DEFAULT 'event',
  `content_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `progress_value` varchar(16) NOT NULL,
  `quiz_score` int(12) NOT NULL,
  `quiz_value` int(12) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`qprogress_id`),
  KEY `content_id` (`aquiz_id`,`content_id`,`proxy_id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_progress_responses` (
  `qpresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `qprogress_id` int(12) unsigned NOT NULL,
  `aquiz_id` int(12) unsigned NOT NULL,
  `content_type` enum('event','community_page') NOT NULL DEFAULT 'event',
  `content_id` int(12) unsigned NOT NULL,
  `quiz_id` int(12) unsigned NOT NULL,
  `proxy_id` int(12) unsigned NOT NULL,
  `qquestion_id` int(12) unsigned NOT NULL,
  `qqresponse_id` int(12) unsigned NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(12) unsigned NOT NULL,
  PRIMARY KEY (`qpresponse_id`),
  KEY `qprogress_id` (`qprogress_id`,`aquiz_id`,`content_id`,`quiz_id`,`proxy_id`,`qquestion_id`,`qqresponse_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_question_responses` (
  `qqresponse_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `qquestion_id` int(12) unsigned NOT NULL,
  `response_text` longtext NOT NULL,
  `response_order` int(3) unsigned NOT NULL,
  `response_correct` enum('0','1') NOT NULL DEFAULT '0',
  `response_is_html` enum('0','1') NOT NULL,
  `response_feedback` text NOT NULL,
  `response_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`qqresponse_id`),
  KEY `qquestion_id` (`qquestion_id`,`response_order`,`response_correct`),
  KEY `response_is_html` (`response_is_html`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_questions` (
  `qquestion_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(12) NOT NULL DEFAULT '0',
  `questiontype_id` int(12) NOT NULL DEFAULT '1',
  `question_text` longtext NOT NULL,
  `question_points` int(6) NOT NULL DEFAULT '0',
  `question_order` int(6) NOT NULL DEFAULT '0',
  `qquestion_group_id` int(12) unsigned DEFAULT NULL,
  `question_active` int(1) NOT NULL DEFAULT '1',
  `randomize_responses` int(1) NOT NULL,
  PRIMARY KEY (`qquestion_id`),
  KEY `quiz_id` (`quiz_id`,`questiontype_id`,`question_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `quiz_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiz_title` varchar(64) NOT NULL,
  `quiz_description` text NOT NULL,
  `quiz_active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  `created_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiz_id`),
  KEY `quiz_active` (`quiz_active`),
  FULLTEXT KEY `quiz_title` (`quiz_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes_lu_questiontypes` (
  `questiontype_id` int(12) NOT NULL AUTO_INCREMENT,
  `questiontype_title` varchar(64) NOT NULL,
  `questiontype_description` text NOT NULL,
  `questiontype_active` int(1) NOT NULL DEFAULT '1',
  `questiontype_order` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`questiontype_id`),
  KEY `questiontype_active` (`questiontype_active`,`questiontype_order`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quizzes_lu_questiontypes` VALUES (1,'Multiple Choice Question','',1,0),(2,'Descriptive Text','',1,0),(3,'Page Break','',1,0);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes_lu_quiztypes` (
  `quiztype_id` int(12) NOT NULL AUTO_INCREMENT,
  `quiztype_code` varchar(12) NOT NULL,
  `quiztype_title` varchar(64) NOT NULL,
  `quiztype_description` text NOT NULL,
  `quiztype_active` int(1) NOT NULL DEFAULT '1',
  `quiztype_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`quiztype_id`),
  KEY `quiztype_active` (`quiztype_active`,`quiztype_order`),
  KEY `quiztype_code` (`quiztype_code`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `quizzes_lu_quiztypes` VALUES (1,'delayed','Delayed Quiz Results','This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) until after the time release period has expired.',1,0),(2,'immediate','Immediate Quiz Results','This option will allow the learner to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback) immediately after they complete the quiz.',1,1),(3,'hide','Hide Quiz Results','This option restricts the learners ability to review the results of a quiz attempt (i.e. score, correct / incorrect responses, and question feedback), and requires either manual release of the results to the students, or use of a Gradebook Assessment to release the resulting score.',1,2);
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports_aamc_ci` (
  `raci_id` int(12) NOT NULL AUTO_INCREMENT,
  `organisation_id` int(12) NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `report_date` bigint(64) NOT NULL DEFAULT '0',
  `report_start` varchar(10) NOT NULL DEFAULT '',
  `report_finish` varchar(10) NOT NULL DEFAULT '',
  `collection_start` bigint(64) NOT NULL DEFAULT '0',
  `collection_finish` bigint(64) NOT NULL DEFAULT '0',
  `report_langauge` varchar(12) NOT NULL DEFAULT 'en-us',
  `report_description` text NOT NULL,
  `report_supporting_link` text NOT NULL,
  `report_params` text NOT NULL,
  `report_active` tinyint(1) NOT NULL DEFAULT '1',
  `report_status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `updated_date` bigint(64) NOT NULL DEFAULT '0',
  `updated_by` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`raci_id`),
  KEY `report_date` (`report_date`),
  KEY `report_active` (`organisation_id`,`report_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_id` int(12) NOT NULL AUTO_INCREMENT,
  `shortname` varchar(64) NOT NULL,
  `organisation_id` int(12) DEFAULT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `settings` VALUES (1,'version_db',NULL,'16100'),(2,'version_entrada',NULL,'1.6.1'),(3,'export_weighted_grade',NULL,'1'),(4,'export_calculated_grade',NULL,'{\"enabled\":0}'),(5,'course_webpage_assessment_cohorts_count',NULL,'4'),(6,'valid_mimetypes',NULL,'{\"default\":[\"image\\/jpeg\",\"image\\/gif\",\"image\\/png\",\"text\\/csv\",\"text\\/richtext\",\"application\\/rtf\",\"application\\/pdf\",\"application\\/zip\",\"application\\/msword\",\"application\\/vnd.ms-office\",\"application\\/vnd.ms-powerpoint\",\"application\\/vnd.ms-write\",\"application\\/vnd.ms-excel\",\"application\\/vnd.ms-access\",\"application\\/vnd.ms-project\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.template\",\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.sheet\",\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.template\",\"application\\/vnd.openxmlformats-officedocument.presentationml.presentation\",\"application\\/vnd.openxmlformats-officedocument.presentationml.slideshow\",\"application\\/vnd.openxmlformats-officedocument.presentationml.template\",\"application\\/vnd.openxmlformats-officedocument.presentationml.slide\",\"application\\/onenote\",\"application\\/vnd.apple.keynote\",\"application\\/vnd.apple.numbers\",\"application\\/vnd.apple.pages\"],\"lor\":[\"image\\/jpeg\",\"image\\/gif\",\"image\\/png\",\"text\\/csv\",\"text\\/richtext\",\"application\\/rtf\",\"application\\/pdf\",\"application\\/zip\",\"application\\/msword\",\"application\\/vnd.ms-office\",\"application\\/vnd.ms-powerpoint\",\"application\\/vnd.ms-write\",\"application\\/vnd.ms-excel\",\"application\\/vnd.ms-access\",\"application\\/vnd.ms-project\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.document\",\"application\\/vnd.openxmlformats-officedocument.wordprocessingml.template\",\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.sheet\",\"application\\/vnd.openxmlformats-officedocument.spreadsheetml.template\",\"application\\/vnd.openxmlformats-officedocument.presentationml.presentation\",\"application\\/vnd.openxmlformats-officedocument.presentationml.slideshow\",\"application\\/vnd.openxmlformats-officedocument.presentationml.template\",\"application\\/vnd.openxmlformats-officedocument.presentationml.slide\",\"application\\/onenote\",\"application\\/vnd.apple.keynote\",\"application\\/vnd.apple.numbers\",\"application\\/vnd.apple.pages\"]}');
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `statistic_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT 'undefined',
  `action` varchar(64) NOT NULL DEFAULT 'undefined',
  `action_field` varchar(64) DEFAULT NULL,
  `action_value` varchar(64) DEFAULT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`timestamp`,`module`,`action`,`action_field`,`action_value`),
  KEY `proxy_id_2` (`proxy_id`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`,`action`,`action_field`,`action_value`),
  KEY `action` (`action`),
  KEY `action_field` (`action_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics_archive` (
  `statistic_id` int(12) NOT NULL AUTO_INCREMENT,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT 'undefined',
  `action` varchar(64) NOT NULL DEFAULT 'undefined',
  `action_field` varchar(64) DEFAULT NULL,
  `action_value` varchar(64) DEFAULT NULL,
  `prune_after` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`statistic_id`),
  KEY `proxy_id` (`proxy_id`,`timestamp`,`module`,`action`,`action_field`,`action_value`),
  KEY `proxy_id_2` (`proxy_id`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`,`action`,`action_field`,`action_value`),
  KEY `action` (`action`),
  KEY `action_field` (`action_field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_awards_external` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `year` year(4) NOT NULL,
  `awarding_body` varchar(4096) NOT NULL,
  `award_terms` mediumtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_awards_internal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_awards_internal_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_terms` mediumtext NOT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title_unique` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_clineval_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(4096) NOT NULL,
  `comment` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_community_health_and_epidemiology` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(4096) NOT NULL,
  `org_event` varchar(256) NOT NULL DEFAULT '',
  `date` varchar(256) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `start_month` int(11) DEFAULT NULL,
  `start_year` int(11) DEFAULT NULL,
  `end_month` int(11) DEFAULT NULL,
  `end_year` int(11) DEFAULT NULL,
  `comment` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_critical_enquiries` (
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `supervisor` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_disciplinary_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_formal_remediations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `remediation_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_international_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `site` varchar(256) NOT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_leaves_of_absence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `absence_details` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_mspr` (
  `user_id` int(11) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `generated` bigint(64) DEFAULT NULL,
  `closed` bigint(64) DEFAULT NULL,
  `carms_number` int(10) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_mspr_class` (
  `year` int(11) NOT NULL DEFAULT '0',
  `closed` int(11) DEFAULT NULL,
  PRIMARY KEY (`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_observerships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL DEFAULT '',
  `city` varchar(32) DEFAULT NULL,
  `prov` varchar(32) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `postal_code` varchar(12) DEFAULT NULL,
  `address_l1` varchar(64) DEFAULT NULL,
  `address_l2` varchar(64) DEFAULT NULL,
  `observership_details` text,
  `activity_type` varchar(32) DEFAULT NULL,
  `clinical_discipline` varchar(32) DEFAULT NULL,
  `organisation` varchar(32) DEFAULT NULL,
  `order` int(3) DEFAULT '0',
  `reflection_id` int(11) DEFAULT NULL,
  `site` varchar(256) NOT NULL DEFAULT '',
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL,
  `preceptor_prefix` varchar(4) DEFAULT NULL,
  `preceptor_firstname` varchar(256) DEFAULT NULL,
  `preceptor_lastname` varchar(256) DEFAULT NULL,
  `preceptor_proxy_id` int(12) unsigned DEFAULT NULL,
  `preceptor_email` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','confirmed','denied') DEFAULT NULL,
  `unique_id` varchar(64) DEFAULT NULL,
  `notice_sent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_research` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `citation` varchar(4096) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(4) NOT NULL DEFAULT '0',
  `comment` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_student_run_electives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_month` tinyint(2) unsigned DEFAULT NULL,
  `start_year` smallint(4) unsigned DEFAULT NULL,
  `end_month` tinyint(2) unsigned DEFAULT NULL,
  `end_year` smallint(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_studentships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(4096) NOT NULL,
  `year` year(4) NOT NULL DEFAULT '0000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic_organisation` (
  `topic_id` int(12) NOT NULL,
  `organisation_id` int(12) NOT NULL,
  PRIMARY KEY (`topic_id`,`organisation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_online` (
  `session_id` varchar(32) NOT NULL,
  `ip_address` varchar(32) NOT NULL,
  `proxy_id` int(12) NOT NULL DEFAULT '0',
  `username` varchar(32) NOT NULL,
  `firstname` varchar(35) NOT NULL,
  `lastname` varchar(35) NOT NULL,
  `timestamp` bigint(64) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `ip_address` (`ip_address`),
  KEY `proxy_id` (`proxy_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

