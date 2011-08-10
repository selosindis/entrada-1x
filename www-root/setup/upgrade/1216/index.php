<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: community-quota.php 1103 2010-04-05 15:20:37Z simpson $
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");
require_once("dbconnection.inc.php");

$query = "INSERT INTO `curriculum_type_organisation` SELECT a.`curriculum_type_id`, b.`organisation_id` FROM `curriculum_lu_types` AS a JOIN `".AUTH_DATABASE."`.`organisations` AS b ON 1=1";

if ($db->Execute($query)) {
	echo 'Successfully inserted values.';
} else {
	echo 'Error while inserting values';
}
 echo 'here';