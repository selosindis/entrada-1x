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
 * This file displays the list of objectives pulled 
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_OBJECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif (!$ENTRADA_ACL->amIAllowed($MODULES["objectives"]["resource"], "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ((isset($COURSE_ID) && $COURSE_ID)) {
		$BREADCRUMB[] = array("url" => "", "title" => "Course Objectives");
		?>
		<style type="text/css">
		li.pad-top {
			margin-bottom: 10px;
		}

		ul.pad {
			padding-top: 10px;
			padding-bottom: 10px;
		}
		</style>
		<?php
		$objective_ids_string = objectives_build_course_objectives_id_string($COURSE_ID);

		$primary = $secondary = $tertiary = array();

		$query = "	SELECT * FROM `global_lu_objectives`
					WHERE `objective_parent` IN (
						SELECT `objective_id` FROM `global_lu_objectives`
						WHERE `objective_parent` = ".$db->qstr(CURRICULAR_OBJECTIVES_PARENT_ID)."
					)
					ORDER BY `objective_order` ASC";
		$competencies = $db->GetAll($query);
		foreach ($competencies as $competency) {
			$query = "	SELECT `objective_id` FROM `global_lu_objectives`
						WHERE `objective_parent` = ".$db->qstr($competency["objective_id"])."
						UNION
						SELECT a.`objective_id` AS `objective_id` FROM `global_lu_objectives` AS a
						JOIN `global_lu_objectives` AS b
						ON a.`objective_parent` = b.`objective_parent`
						WHERE b.`objective_parent` = ".$db->qstr($competency["objective_id"]);
			$competency_objectives = $db->GetAll($query);
			$competency_objective_ids_string = false;
			if ($competency_objectives) {
				foreach ($competency_objectives as $objective) {
					if (!$competency_objective_ids_string) {
						$competency_objective_ids_string = $db->qstr($objective["objective_id"]);
					} else {
						$competency_objective_ids_string .= ", ".$db->qstr($objective["objective_id"]);
					}
				}
			}
			$query = "	SELECT * FROM `course_objectives` AS a
						JOIN `global_lu_objectives` AS b
						ON a.`objective_id` = b.`objective_id`
						WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
						AND b.`objective_parent` NOT IN (".$objective_ids_string.")
						".($competency_objective_ids_string ? "AND b.`objective_id` IN (".$competency_objective_ids_string.")" : "")."
						AND a.`objective_type` = 'course'
						ORDER BY a.`importance` ASC, b.`objective_order` ASC";
			$objectives_array = $db->GetAll($query);
			if ($objectives_array) {
				foreach ($objectives_array AS $objective) {
					$query = "	SELECT a.*, b.`objective_details` FROM `global_lu_objectives` AS a
								LEFT JOIN `course_objectives` AS b
								ON a.`objective_id` = b.`objective_id`
								AND b.`course_id` = ".$db->qstr($COURSE_ID)."
								AND b.`objective_type` = 'course'
								WHERE a.`objective_parent` = ".$db->qstr($objective["objective_id"])."
								ORDER BY a.`objective_order` ASC";
					$child_objectives = $db->GetAll($query);
					$objectives[$objective["objective_id"]]["children"] = array();
	
					foreach ($child_objectives as $child_objective) {
						$objectives[$objective["objective_id"]]["children"][] = $child_objective;
					}
					$objectives[$objective["objective_id"]]["objective"] = $objective;				
				}
			}
		}
		
		foreach ($competencies as $competency) {
			$competencies_array[$competency["objective_id"]] = $competency;
		}
		$last_competency = false;
		$last_importance = false;
		$course = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));

		echo "<h1>".html_encode($course["course_name"])."</h1>";
		foreach ($objectives as $objective_id => $objective) {
			if (array_key_exists($objective["objective"]["objective_parent"], $competencies_array)) {
				$query = "	SELECT * FROM `global_lu_objectives`
							WHERE `objective_id` = ".$db->qstr($objective["objective"]["objective_parent"]);
				$competency = $db->GetRow($query);
			} else {
				$query = "	SELECT b.* FROM `global_lu_objectives` AS a
							JOIN `global_lu_objectives` AS b
							ON a.`objective_parent` = b.`objective_id`
							WHERE `objective_id` = ".$db->qstr($objective["objective"]["objective_parent"]);
				$competency = $db->GetRow($query);
			}
			if (!$last_competency || ($competency["objective_id"] != $last_competency)) {
				echo "<h2>".html_encode($competency["objective_name"])." Objectives</h2>\n";
				$last_importance = false;
			}
			$last_competency = $competency["objective_id"];
			if (!$last_importance || $last_importance != $objective["objective"]["importance"]) {
				echo "<h3>".($objective["objective"]["importance"] == 3 ? "Tertiary" : ($objective["objective"]["importance"] == 2 ? "Secondary" : "Primary"))." Objectives</h3>\n";
			}
			echo "<ul>\n";
			echo "<li>\n<a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]." <img style=\"border: none; margin-left: 5px;\" src=\"".ENTRADA_URL."/images/ics-enabled.gif\" /></a><div class=\"content-small\">".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? $objective["objective"]["objective_details"] : $objective["objective"]["objective_description"])."</div>\n";
			if (isset($objective["children"]) && count($objective["children"])) {
				echo "<ul class=\"pad\">\n";
				foreach ($objective["children"] as $objective_child) {
					echo "<li class=\"pad-top\"><a title=\"View events in this course related to this objective.\" href=\"".ENTRADA_URL."/courses/objectives?section=course-objective-events&cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]." <img style=\"border: none; margin-left: 5px;\" src=\"".ENTRADA_URL."/images/ics-enabled.gif\" /></a><div class=\"content-small\">".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? $objective_child["objective_details"] : $objective_child["objective_description"])."</div></li>\n";
				}
				echo "</ul>\n";
			}
			echo "</li>\n";
			echo "</ul>\n";
			$last_importance = $objective["objective"]["importance"];			
		}
	}
}