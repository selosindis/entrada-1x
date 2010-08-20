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
} elseif (!$ENTRADA_ACL->amIAllowed('objective', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($COURSE_ID && $COMPETENCY_ID) {
		?>
		<style type="text/css">
		li.pad-top {
			height: 50px;
		}
		ul.pad {
			padding-top: 10px;
			padding-bottom: 10px;
		}
		</style>
		<?php
		$objective_ids_string = objectives_build_course_objectives_id_string($COURSE_ID);
		$competency_ids_string = objectives_build_objective_descendants_id_string($COMPETENCY_ID);
		$query = "	SELECT * FROM `course_objectives` AS a
					JOIN `global_lu_objectives` AS b
					ON a.`objective_id` = b.`objective_id`
					WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
					AND (
						b.`objective_id` IN (".$competency_ids_string.")
						AND b.`objective_parent` NOT IN (".$objective_ids_string.")
					)
					AND a.`objective_type` = 'course'
					ORDER BY a.`importance` ASC";
		$objectives = $db->GetAll($query);
		$primary = $secondary = $tertiary = array();
		foreach ($objectives AS $objective) {
			$query = "	SELECT a.*, b.`objective_details` FROM `global_lu_objectives` AS a
						LEFT JOIN `course_objectives` AS b
						ON a.`objective_id` = b.`objective_id`
						AND b.`course_id` = ".$db->qstr($COURSE_ID)."
						AND b.`objective_type` = 'course'
						WHERE a.`objective_parent` = ".$db->qstr($objective["objective_id"])."
						ORDER BY a.`objective_order` ASC";
			$child_objectives = $db->GetAll($query);
			if ($objective["importance"] == 1) {
				$primary[$objective["objective_id"]]["children"] = array();
			} elseif ($objective["importance"] == 2) {
				$secondary[$objective["objective_id"]]["children"] = array();
			} elseif ($objective["importance"] == 3) {
				$tertiary[$objective["objective_id"]]["children"] = array();
			}
			foreach ($child_objectives as $child_objective) {
				if ($objective["importance"] == 1) {
					$primary[$objective["objective_id"]]["children"][] = $child_objective;
				} elseif ($objective["importance"] == 2) {
					$secondary[$objective["objective_id"]]["children"][] = $child_objective;
				} elseif ($objective["importance"] == 3) {
					$tertiary[$objective["objective_id"]]["children"][] = $child_objective;
				}
			}
			if ($objective["importance"] == 1) {
				$primary[$objective["objective_id"]]["objective"] = $objective;
			} elseif ($objective["importance"] == 2) {
				$secondary[$objective["objective_id"]]["objective"] = $objective;
			} elseif ($objective["importance"] == 3) {
				$tertiary[$objective["objective_id"]]["objective"] = $objective;
			}
		}
		$competency = $db->GetRow("SELECT * FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($COMPETENCY_ID));
		$course = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
		echo "<h2>[".$competency["objective_name"]."] objectives in [".$course["course_name"]." - ".$course["course_code"]."]</h2>\n";
		if ($primary) {
			echo "<h3>Primary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($primary as $objective) {
				echo "<li>\n<a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a>".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? " - <span class=\"content-small\">".$objective["objective"]["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective["objective"]["objective_description"]."</span>" )."\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a>".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? " - <span class=\"content-small\">".$objective_child["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective_child["objective_description"]."</span>" )."</li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
		if ($secondary) {
			echo "<h3>Secondary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($secondary as $objective) {
				echo "<li>\n<a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a>".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? " - <span class=\"content-small\">".$objective["objective"]["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective["objective"]["objective_description"]."</span>" )."\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a>".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? " - <span class=\"content-small\">".$objective_child["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective_child["objective_description"]."</span>" )."</li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
		if ($tertiary) {
			echo "<h3>Tertiary Objectives</h3>\n";
			echo "<ul>\n";
			foreach ($tertiary as $objective) {
				echo "<li>\n<a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\"><a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective["objective"]["objective_id"]."\">".$objective["objective"]["objective_name"]."</a>"."</a>".(isset($objective["objective"]["objective_details"]) && $objective["objective"]["objective_details"] ? " - <span class=\"content-small\">".$objective["objective"]["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective["objective"]["objective_description"]."</span>" )."\n";
				if (isset($objective["children"]) && count($objective["children"])) {
					echo "<ul class=\"pad\">\n";
					foreach ($objective["children"] as $objective_child) {
						echo "<li class=\"pad-top\"><a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\"><a href=\"".ENTRADA_URL."/objectives?cid=".$COURSE_ID."&oid=".$objective_child["objective_id"]."\">".$objective_child["objective_name"]."</a>"."</a>".(isset($objective_child["objective_details"]) && $objective_child["objective_details"] ? " - <span class=\"content-small\">".$objective_child["objective_details"]."</span>" : " - <span class=\"content-small\">".$objective_child["objective_description"]."</span>" )."</li>\n";
					}
					echo "</ul>\n";
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	} elseif ($COURSE_ID && $OBJECTIVE_ID) {
		$query = "	SELECT CONCAT_WS(' - ',`course_name`, `course_code`) FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
		$course_name = $db->GetOne($query);
		$query = "	SELECT `objective_name` FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($OBJECTIVE_ID);
		$objective_name = $db->GetOne($query);
		if (isset($course_name) && $course_name && isset($objective_name) && $objective_name) {
			echo "<h2>Events in [".$course_name."] associated with [".$objective_name."]</h2>\n";
			$query = "	SELECT * FROM `event_objectives` AS a
						JOIN `events` AS b
						ON a.`event_id` = b.`event_id`
						JOIN `global_lu_objectives` AS c
						ON a.`objective_id` = c.`objective_id`
						WHERE a.`objective_id` = ".$db->qstr($OBJECTIVE_ID)."
						AND a.`event_id` IN (
							SELECT `event_id` FROM `events`
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
						)
						AND a.`objective_type` = 'course'
						ORDER BY b.`event_start`, b.`event_id`";
			$event_objectives = $db->GetAll($query);
			if ($event_objectives) {
				$last_event_id = 0;
				foreach ($event_objectives as $event_objective) {
					if ($event_objective["event_id"] != $last_event_id) {
						if ($last_event_id) {
							echo "</ul>\n";
						}
						echo "<br/>\n";
						echo "<h3>".html_encode($event_objective["event_title"])." - <span class=\"content-small\">".date(DEFAULT_DATE_FORMAT, $event_objective["event_start"])."</span></h3>\n";
						echo "<ul>\n";
					}
					echo "<li>".$event_objective["objective_name"]." - <span class=\"content-small\">".(isset($event_objective["objective_details"]) && $event_objective["objective_details"] ? $event_objective["objective_details"] : "No additional details in this event.")."</span></li>\n";
				}
			} else {
				$query = "	SELECT * FROM `event_objectives` AS a
							JOIN `events` AS b
							ON a.`event_id` = b.`event_id`
							JOIN `global_lu_objectives` AS c
							ON a.`objective_id` = c.`objective_id`
							WHERE c.`objective_parent` = ".$db->qstr($OBJECTIVE_ID)."
							AND a.`event_id` IN (
								SELECT `event_id` FROM `events`
								WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							)
							AND a.`objective_type` = 'course'
							ORDER BY b.`event_start`, b.`event_id`";
				$event_objectives = $db->GetAll($query);
				if ($event_objectives) {
					$last_event_id = 0;
					foreach ($event_objectives as $event_objective) {
						if ($event_objective["event_id"] != $last_event_id) {
							if ($last_event_id) {
								echo "</ul>\n";
							}
							echo "<br/>\n";
							echo "<h3>".html_encode($event_objective["event_title"])." - <span class=\"content-small\">".date(DEFAULT_DATE_FORMAT, $event_objective["event_start"])."</span></h3>\n";
							echo "<ul>\n";
						}
						echo "<li>".$event_objective["objective_name"]." - <span class=\"content-small\">".(isset($event_objective["objective_details"]) && $event_objective["objective_details"] ? $event_objective["objective_details"] : "No additional details in this event.")."</span></li>\n";
					}
				} else {
					$NOTICE++;
					$NOTICESTR[] = "There were no events found to be linked with the selected objective in the selected course.";
					echo display_notice();
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "Valid objective and course identifiers are required to view this page, please ensure you have selected a valid objective and try again.";
			echo display_error();
		}
	} else {
		echo "<h1>Competencies by Course</h1>";
		$objectives = objectives_build_course_competencies_array();
		?>
		<style type="text/css">
		.title {
			vertical-align: bottom;
		}
		.vertical {
			-moz-transform:rotate(-90deg); 
			-webkit-transform: rotate(-90deg);
			-o-transform: rotate(-90deg);
			writing-mode: tb-rl;
			filter: flipv fliph;
			overflow: visible;
			white-space: nowrap;
		}
		.middle {
			vertical-align: middle;
			width: 25px;
		}
		table.tableList tbody tr td.bottom {
			vertical-align: bottom;
			padding-bottom: 15px;
		}
		.title {
			border-right: 1px solid #EBEBEB;
		}
		.objectives {
			border-right: 1px solid #EBEBEB;
		}
		table.tableList {
			border-collapse: separate;
		}
		li {
			margin-top: 10px;
		}
		</style>
		<table class="tableList" cellspacing="0" summary="List of Objectives">
			<tbody>
				<tr style="height: 200px;">
					<td class="modified">&nbsp;</td>
					<td class="title" style="padding-bottom: 20px; border-right: none;"><h3>Courses</h3></td>
					<td class="title" style="padding: 0 0 80px 50px;"><h3 class="vertical">Competencies</h3></td>
					<?php
						foreach ($objectives["competencies"] as $competency) {
							?>
							<td class="title middle bottom"><div class="vertical"><?php echo $competency ?></div></td>
							<?php
						}
					?>
				</tr>
				<?php
				foreach ($objectives["courses"] as $course_id => $course) {
					?>
					<tr>
						<td>&nbsp;</td>
						<td class="objectives" colspan="2"><?php echo html_encode($course["course_name"]); ?></td>
						<?php
						foreach ($course["competencies"] as $COMPETENCY_ID => $competency) {
							?>
							<td class="objectives" style="text-align: center;">
							<?php
							if ($competency) {
								echo "<a href=\"".ENTRADA_URL."/objectives?id=".$COMPETENCY_ID."&cid=".$course_id."\" style=\"text-decoration: none;\">X</a>";
							} else {
								echo "&nbsp;";
							}
							?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
}