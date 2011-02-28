<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for notifying clerks that they are behind in their logging.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/
@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");
$query 	= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS a
		LEFT JOIN `courses` AS b
		ON a.`course_id` = b.`course_id`
		WHERE a.`rotation_id` < ".$db->qstr(MAX_ROTATION);
$rotations = $db->GetAll($query);
if ($rotations) {
	foreach ($rotations as $rotation) {
		$query		= "SELECT a.*, b.`etype_id` as `proxy_id`, c.*, CONCAT_WS(' ', e.`firstname`, e.`lastname`) as `fullname`, e.`email`, MIN(a.`event_start`) as `start`, MAX(a.`event_finish`) AS `finish`
					FROM `".CLERKSHIP_DATABASE."`.`events` AS a
					JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
					ON b.`event_id` = a.`event_id`
					JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS c
					ON a.`rotation_id` = c.`rotation_id`
					JOIN `".AUTH_DATABASE."`.`user_data` AS e
					ON b.`etype_id` = e.`id`
					JOIN `".AUTH_DATABASE."`.`user_access` AS f
					ON e.`id` = f.`user_id`
					AND f.`app_id` = '".AUTH_APP_ID."'
					WHERE b.`econtact_type` = 'student'
					AND f.`group` >= 'student'
					AND f.`role` >= ".$db->qstr(CLERKSHIP_FIRST_CLASS)."
					AND c.`rotation_id` = ".$db->qstr($rotation["rotation_id"])."
					GROUP BY b.`etype_id`, a.`rotation_id`
					ORDER BY `fullname` ASC";
		$results = $db->GetAll($query);
		if ($results) {
			$query = "DELETE FROM `".CLERKSHIP_DATABASE."`.`logbook_overdue` WHERE `rotation_id` = ".$db->qstr($rotation["rotation_id"]);
			$db->Execute($query);
			$count = 0;
			foreach ($results as $clerk) {
				if (((int)$clerk["proxy_id"]) != 1788 && ((int)$clerk["proxy_id"]) != 1738 && ((int)$clerk["proxy_id"]) != 1760 && ((int)$clerk["proxy_id"]) != 1739 && ((int)$clerk["proxy_id"]) != 1543) { 
					if ($clerk["rotation_id"] && ($clerk["start"] > strtotime("February 14th, 2010") || ((array_search($clerk["rotation_id"], array("3", "9")) !== false))) && (!array_search(((int)$clerk["proxy_id"]), $excused) || $clerk["rotation_id"] != 3)) {
						if ($clerk["start"] < time()) {
							if (time() >= ($clerk["finish"] + ONE_WEEK)) {
								clerkship_progress_send_notice(CLERKSHIP_ONE_WEEK_PAST, $rotation, $clerk);
							} elseif (time() >= $clerk["finish"]) {
								clerkship_progress_send_notice(CLERKSHIP_ROTATION_ENDED, $rotation, $clerk);
							} elseif ((time() - $clerk["start"]) >= (($clerk["finish"] - $clerk["start"]) - ONE_WEEK)) {
								clerkship_progress_send_notice(CLERKSHIP_ONE_WEEK_PRIOR, $rotation, $clerk);
							} elseif ((time() - $clerk["start"]) >= (($clerk["finish"] - $clerk["start"]) * $rotation["percent_period_complete"] / 100)) {
								clerkship_progress_send_notice(CLERKSHIP_ROTATION_PERIOD, $rotation, $clerk);
							}
						}
					}
				}
			}
		}
		clerkship_send_queued_notifications($rotation["rotation_id"], $rotation["rotation_title"], $rotation["pcoord_id"]);
	}
}
