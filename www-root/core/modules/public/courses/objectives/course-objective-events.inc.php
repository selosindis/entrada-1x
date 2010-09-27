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
	if ((isset($COURSE_ID) && $COURSE_ID) && (isset($OBJECTIVE_ID) && $OBJECTIVE_ID)) {
		$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/objectives", "title" => "Course Objectives");
		$BREADCRUMB[] = array("url" => "", "title" => "Learning Events");
		
		/**
		 * Update requested length of time to display.
		 * Valid: day, week, month, year
		 */
		if (isset($_GET["dtype"])) {
			if (in_array(trim($_GET["dtype"]), array("day", "week", "month", "year"))) {
				$_SESSION[APPLICATION_IDENTIFIER]["objectives"]["dtype"] = trim($_GET["dtype"]);
			}
	
			$_SERVER["QUERY_STRING"] = replace_query(array("dtype" => false));
		} else {
			if (!isset($_SESSION[APPLICATION_IDENTIFIER]["objectives"]["dtype"])) {
				$_SESSION[APPLICATION_IDENTIFIER]["objectives"]["dtype"] = "week";
			}
		}
	
		/**
		 * Update requested timestamp to display.
		 * Valid: Unix timestamp
		 */
		if (isset($_GET["dstamp"])) {
			$integer = (int) trim($_GET["dstamp"]);
			if ($integer) {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = $integer;
			}
	
			$_SERVER["QUERY_STRING"] = replace_query(array("dstamp" => false));
		} else {
			if (!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])) {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = time();
			}
		}
		
		/**
		 * Update requsted number of rows per page.
		 * Valid: any integer really.
		 */
		if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
			$integer = (int) trim($_GET["pp"]);
	
			if (($integer > 0) && ($integer <= 250)) {
				$_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"] = $integer;
			}
	
			$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
		} else {
			if (!isset($_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"])) {
				$_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"] = DEFAULT_ROWS_PER_PAGE;
			}
		}
		
		$display_duration = fetch_timestamps($_SESSION[APPLICATION_IDENTIFIER]["objectives"]["dtype"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"]);
		
		$query = "	SELECT `course_name` FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
		$course_name = $db->GetOne($query);
		$query = "	SELECT `objective_name` FROM `global_lu_objectives` WHERE `objective_id` = ".$db->qstr($OBJECTIVE_ID);
		$objective_name = $db->GetOne($query);
		if (isset($course_name) && $course_name && isset($objective_name) && $objective_name) {
			echo "<h1>".html_encode($course_name)."</h2>";

			echo "<h2>Learning Events Associated with ".$objective_name."</h2>\n";
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
						AND `event_start` >= ".$db->qstr($display_duration["start"])."
						AND `event_start` <= ".$db->qstr($display_duration["end"])."
						AND a.`objective_type` = 'course'
						ORDER BY b.`event_start`, b.`event_id`";
			$event_objectives = $db->GetAll($query);
			if (!$event_objectives) {
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
							AND `event_start` >= ".$db->qstr($display_duration["start"])."
							AND `event_start` <= ".$db->qstr($display_duration["end"])."
							AND a.`objective_type` = 'course'
							ORDER BY b.`event_start`, b.`event_id`";
				$event_objectives = $db->GetAll($query);
			}
			$total_pages = (int)(count($event_objectives) / $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"]) + (count($event_objectives) % $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"] > 0 ? 1 : 0);
			/**
			 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
			 */
			if (isset($_GET["pv"])) {
				$page_current = (int) trim($_GET["pv"]);
		
				if (($page_current < 1) || ($page_current > $total_pages)) {
					$page_current = 1;
				}
			} else {
				$page_current = 1;
			}
			
			/**
			 * Sidebar item that will provide another method for sorting, ordering, etc.
			 */
			$sidebar_html = "<ul class=\"menu\">\n";
			$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/courses/objectives?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
			$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/courses/objectives?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
			$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/courses/objectives?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
			$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/courses/objectives?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
			$sidebar_html .= "</ul>\n";
	
			new_sidebar_item("Rows per page", $sidebar_html, "sort-results", "open");
			
			objectives_output_calendar_controls();
			?>
			<div class="tableListTop">
				<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
				<?php
				switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
					case "day" :
						echo "Found ".count($event_objectives)." event".((count($event_objectives) != 1) ? "s" : "")." that take place on <strong>".date("D, M jS, Y", $display_duration["start"])."</strong>.\n";
					break;
					case "month" :
						echo "Found ".count($event_objectives)." event".((count($event_objectives) != 1) ? "s" : "")." that take place during <strong>".date("F", $display_duration["start"])."</strong> of <strong>".date("Y", $display_duration["start"])."</strong>.\n";
					break;
					case "year" :
						echo "Found ".count($event_objectives)." event".((count($event_objectives) != 1) ? "s" : "")." that take place during <strong>".date("Y", $display_duration["start"])."</strong>.\n";
					break;
					default :
					case "week" :
						echo "Found ".count($event_objectives)." event".((count($event_objectives) != 1) ? "s" : "")." from <strong>".date("D, M jS, Y", $display_duration["start"])."</strong> to <strong>".date("D, M jS, Y", $display_duration["end"])."</strong>.\n";
					break;
				}
				?>
			</div>
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
				<colgroup>
					<col class="modified" />
					<col class="date" />
					<col class="date-smallest" />
					<col class="title" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="date">Event Date</td>
						<td class="date-smallest">Objective Name</td>
						<td class="title">Event Title</td>
					</tr>
				</thead>
				<tbody>
			<?php
			if ($event_objectives) {
				for ($i = (($page_current - 1) * $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"]); $i < (($page_current * $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"]) < count($event_objectives) ? ($page_current * $_SESSION[APPLICATION_IDENTIFIER]["objectives"]["pp"]) : count($event_objectives)); $i++) {
					echo "<tr>\n";
					echo "	<td>&nbsp;</td>\n";
					echo "	<td>".date(DEFAULT_DATE_FORMAT, $event_objectives[$i]["event_start"])."</td>\n";
					echo "	<td>".$event_objectives[$i]["objective_name"]."</td>\n";
					echo "	<td><a href=\"".ENTRADA_URL."/events?id=".$event_objectives[$i]["event_id"]."\">".html_encode($event_objectives[$i]["event_title"])."</a></td>\n";
					echo "</tr>\n";
				}
				?>
				</tbody>
			</table>					
			<?php
			} else {
				?>
					<tr>
						<td colspan="4">	
							<div class="display-notice" style="white-space: normal">
								<h3>No Matching Events</h3>
								There are no learning events scheduled
								<?php
								switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
									case "day" :
										echo "that take place on <strong>".date(DEFAULT_DATE_FORMAT, $display_duration["start"])."</strong>";
									break;
									case "month" :
										echo "that take place during <strong>".date("F", $display_duration["start"])."</strong> of <strong>".date("Y", $display_duration["start"])."</strong>";
									break;
									case "year" :
										echo "that take place during <strong>".date("Y", $display_duration["start"])."</strong>";
									break;
									default :
									case "week" :
										echo "from <strong>".date(DEFAULT_DATE_FORMAT, $display_duration["start"])."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, $display_duration["end"])."</strong>";
									break;
								}
								?> which are also in the [<?php echo $course_name; ?>] course and are linked to the [<?php echo $objective_name; ?>] objective.
								<br /><br />
								If this is unexpected, you can check to make sure that you are browsing the intended time period. For example, if you trying to browse <?php echo date("F", time()); ?> of <?php echo date("Y", time()); ?>, make sure that the results bar above says &quot;... takes place in <strong><?php echo date("F", time()); ?></strong> of <strong><?php echo date("Y", time()); ?></strong>
							</div>
						</td>
					</tr>
				</tbody>
			</table>				
			<?php
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "Valid objective and course identifiers are required to view this page, please ensure you have selected a valid objective and try again.";
			echo display_error();
		}
	}
}