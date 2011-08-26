<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This API file returns an HTML table of the possible targets for the selected
 * evaluation form. For instance, if the selected form is a course evaluation
 * it will return HTML used by the administrator to select which course / courses
 * they wish to evaluate.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if (!defined("IN_EVENTS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "create", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	/**
	 * Clears all open buffers so we can return a plain response for the Javascript.
	 */
	ob_clear_open_buffers();
	
	$options_for = false;
	$course_id = 0;
	
	if (isset($_POST["options_for"]) && ($tmp_input = clean_input($_POST["options_for"], array("trim")))) {
		$options_for = $tmp_input;
	}
	
	if (isset($_POST["course_id"]) && ($tmp_input = clean_input($_POST["course_id"], array("int")))) {
		$course_id = $tmp_input;
	}
	
	if (isset($_POST["event_id"]) && ($tmp_input = clean_input($_POST["event_id"], array("int")))) {
		$event_id = $tmp_input;
	}
	
	if ($options_for && $ENTRADA_USER->getActiveOrganisation()) {
		
		$organisation[$ENTRADA_USER->getActiveOrganisation()] = array("text" => fetch_organisation_title($ENTRADA_USER->getActiveOrganisation()), "value" => "organisation_" . $ENTRADA_USER->getActiveOrganisation(), "category" => true);

		switch ($options_for) {
			case "cohorts" : // Classes
				$groups = $organisation;
				
				$query = "	SELECT a.*
							FROM `groups` AS a
							JOIN `group_organisations` AS b
							ON b.`group_id` = a.`group_id`
							WHERE a.`group_active` = '1'
							AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
							ORDER BY a.`group_name` ASC";
				$groups_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($groups_results) {
					$cohort_ids = array();
					if ($event_id) {
						$query = "SELECT `audience_value` FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id)." AND `audience_type` = 'cohort'";
						$cohorts = $db->GetAll($query);
						foreach ($cohorts as $cohort) {
							$cohort_ids[] = $cohort["audience_value"];
						}
					}
					
					foreach ($groups_results as $group) {
						if (isset($event_id) && in_array($group["group_id"], $cohort_ids)) {
							$checked = "checked=\"checked\"";
						} else {
							$checked = "";
						}

						$groups[$ENTRADA_USER->getActiveOrganisation()]["options"][] = array("text" => $group["group_name"], "value" => "group_" . $group["group_id"], "checked" => $checked);
					}

					echo lp_multiple_select_popup("cohorts", $groups, array("title" => "Select Cohorts of Learners:", "submit_text" => "Close", "submit" => true));
				} else {
					echo display_notice("There are no cohorts of learners available.");
				}
			break;
			case "course_groups" :
				$groups = $organisation;
				
				$query = "	SELECT a.*
							FROM `course_groups` AS a
							WHERE a.`active` = '1'
							AND a.`course_id` = ".$db->qstr($course_id)."
							ORDER BY a.`group_name` ASC";
				$groups_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($groups_results) {
					
					$course_group_ids = array();
					if ($event_id) {
						$query = "SELECT `audience_value` FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id)." AND `audience_type` = 'group_id'";
						$course_groups = $db->GetAll($query);
						foreach ($course_groups as $course_group) {
							$course_group_ids[] = $course_group["audience_value"];
						}
					}
					foreach ($groups_results as $group) {
						if (isset($event_id) && in_array($group["group_id"], $course_group_ids)) {
							$checked = "checked=\"checked\"";
						} else {
							$checked = "";
						}

						$groups[$ENTRADA_USER->getActiveOrganisation()]["options"][] = array("text" => $group["group_name"], "value" => "cgroup_" . $group["cgroup_id"], "checked" => $checked);
					}

					echo lp_multiple_select_popup("course_groups", $groups, array("title" => "Select Course Specific Small Groups:", "submit_text" => "Close", "submit" => true));
				} else {
					//echo display_notice("There are no small groups in the course you have selected.");
				}
			break;
			case "students" : // Students
				$students = $organisation;

				$query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
							FROM `".AUTH_DATABASE."`.`user_data` AS a
							JOIN `".AUTH_DATABASE."`.`user_access` AS b
							ON a.`id` = b.`user_id`
							WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
							AND a.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
							AND b.`account_active` = 'true'
							AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
							AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
							AND b.`group` = 'student'
							AND a.`grad_year` >= ".$db->qstr((fetch_first_year() - 4)).
							(($ENTRADA_USER->getGroup() == "student") ? " AND a.`id` = ".$db->qstr($ENTRADA_USER->getProxyId()) : "")."
							GROUP BY a.`id`
							ORDER BY a.`grad_year` DESC, a.`lastname` ASC, a.`firstname` ASC";
				$student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				if ($student_results) {
					
					$proxy_ids = array();
					if ($event_id) {
						$query = "SELECT `audience_value` FROM `event_audience` WHERE `event_id` = ".$db->qstr($event_id)." AND `audience_type` = 'proxy_id'";
						$associated_students = $db->GetAll($query);
						foreach ($associated_students as $associated_student) {
							$proxy_ids[] = $associated_student["audience_value"];
						}
					}
					foreach ($student_results as $student) {
						if (isset($event_id) && in_array($student["proxy_id"], $proxy_ids)) {
							$checked = "checked=\"checked\"";
						} else {
							$checked = "";
						}

						$students[$ENTRADA_USER->getActiveOrganisation()]["options"][] = array("text" => $student["fullname"], "value" => "student_".$student["proxy_id"], "checked" => $checked);
					}

					echo lp_multiple_select_popup("students", $students, array("title" => "Select Individual Learners:", "submit_text" => "Close", "submit" => true));
				} else {
					echo display_notice("There are no students available.");
				}
			break;

			default :
				application_log("notice", "Unknown learning event filter type [" . $options_for . "] provided to events_filters API.");
			break;
		}
	}
}
exit;