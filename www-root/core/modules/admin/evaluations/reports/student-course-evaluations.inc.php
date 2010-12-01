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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Students' Course Evaluations" );

	$query = "	SELECT distinct e.`evaluation_id`, e.`eform_id`, e.`evaluation_title`, e.`evaluation_description`, 
				c.`course_name`, e.`evaluation_start`, e.`evaluation_finish`
				FROM `evaluations` e 
				INNER JOIN `evaluation_evaluators` ev ON e.`evaluation_id` = ev.`evaluation_id`
				INNER JOIN `evaluation_targets` t ON e.`evaluation_id` = t.`evaluation_id`
				INNER JOIN `evaluations_lu_targets` elt ON t.`target_id` = elt.`target_id`
				INNER JOIN `courses` c ON t.`target_value` = c.`course_id`
				INNER JOIN `".AUTH_DATABASE."`.`user_data` u ON ev.`evaluator_value` = u.`id`
				INNER JOIN `".AUTH_DATABASE."`.`user_access` a ON u.`id` = a.`user_id`
				WHERE elt.`target_shortname` = 'course' and elt.`target_active` = 1 
				and (ev.`evaluator_type` = 'grad_year' or a.`group`= 'student' and ev.`evaluator_type` = 'proxy_id') ";
	$results	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);			
	?>

	<h1>Students' Course Evaluations</h1>
	
	<div class="no-printing">
		<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=<?php echo $SECTION; ?>&step=2" method="post">
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 23%" />
				<col style="width: 32%" />
				<col style="width: 16%" />
				<col style="width: 16%" />
				<col style="width: 10%" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified" />
					<td class="title">Evaluation Title</td>
					<td class="phase">Course</td>
					<td class="date"><div class="noLink">Start Date</div></td>
					<td class="date"><div class="noLink">Finish Date</div></td>
					<td class="date"><div class="noLink">Completion</div></td>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($results as $result) {
/*				$query = "	SELECT distinct e.`evaluation_id`, e.`eform_id`, e.`evaluation_title`, e.`evaluation_description`, 
							c.`course_name`, e.`evaluation_start`, e.`evaluation_finish`
							FROM `evaluations` e 
							INNER JOIN `evaluation_evaluators` ev ON e.`evaluation_id` = ev.`evaluation_id`
							INNER JOIN `evaluation_targets` t ON e.`evaluation_id` = t.`evaluation_id`
							INNER JOIN `evaluations_lu_targets` elt ON t.`target_id` = elt.`target_id`
							INNER JOIN `courses` c ON t.`target_value` = c.`course_id`
							INNER JOIN `".AUTH_DATABASE."`.`user_data` u ON ev.`evaluator_value` = u.`id`
							INNER JOIN `".AUTH_DATABASE."`.`user_access` a ON u.`id` = a.`user_id`
							WHERE elt.`target_shortname` = 'course' and elt.`target_active` = 1 
							and (ev.`evaluator_type` = 'grad_year' or a.`group`= 'student' and ev.`evaluator_type` = 'proxy_id') ";
				$completion	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);			
*/				$completion = '15%';
				$url = "---";
				echo "	<tr><td class=\"modified\" />";
				echo "	<td class=\"title".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Evaluation Title: ".html_encode($result["evaluation_title"])."\">" : "").html_encode($result["evaluation_title"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"teacher".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Course: ".html_encode($result["course_name"])."\">" : "").html_encode($result["course_name"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Start Date\">" : "").date("M j, Y", $result["evaluation_start"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Finish Date\">" : "").date("M j, Y", $result["evaluation_finish"]).(($url) ? "</a>" : "")."</td>\n";
				echo "	<td class=\"date".((!$url) ? " np" : "")."\">".(($url) ? "<a href=\"".$url."\" title=\"Completion\">" : ""). $completion.(($url) ? "</a>" : "")."</td></tr>\n";
			}
			?>
		</tbody>
		</table>
		</form>
	<?php
	if ($STEP == 2) {
// To be changed		
	$int_use_cache	= true;

	$report_results	= array();

	if (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] != -1) {
		$organisation_where = " AND (a.`organisation_id` = ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"].") ";
	} else {
		$organisation_where = "";
	}

	$query	= "	SELECT a.`id` AS `proxy_id`, a.`number` AS `staff_number`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`email`
				FROM `".AUTH_DATABASE."`.`user_data` AS a
				LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
				ON b.`user_id` = a.`id`
				AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
				WHERE  b.`app_id` = ".$db->qstr(AUTH_APP_ID).$organisation_where."
				AND b.`group` = 'faculty'
				ORDER BY `fullname`";
	if ($int_use_cache) {
		$results	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
	} else {
		$results	= $db->GetAll($query);
	}
	if ($results) {
		$event_ids = array();
		$report_results["courses"]["events"] = array("total_events" => 0, "total_minutes" => 0, "events_calculated" => 0, "events_minutes" => 0);
		foreach ($results as $result) {
			$query	= "	SELECT a.`event_id`, a.`event_title`, a.`course_id`, a.`event_duration`
						FROM `events` AS a
						LEFT JOIN `event_contacts` AS b
						ON b.`event_id` = a.`event_id`
						WHERE b.`proxy_id` = ".$db->qstr($result["proxy_id"])."
						AND (a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")";
			if ($int_use_cache) {
				$sresults	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
			} else {
				$sresults	= $db->GetAll($query);
			}
			if ($sresults) {
				$i = @count($report_results["people"]);
				$report_results["people"][$i]["fullname"] = $result["fullname"];
				$report_results["people"][$i]["number"] = $result["staff_number"];
				$report_results["people"][$i]["events"]	= array("total_events" => 0, "total_minutes" => 0);

				foreach ($sresults as $sresult) {
					if (!in_array($sresult["event_id"], $event_ids)) {
						$event_ids[] = $sresult["event_id"];
						$increment_total = true;
					} else {
						$increment_total = false;
					}

                    $report_results["people"][$i]["events"]["total_events"] += 1;
                    $report_results["people"][$i]["events"]["total_minutes"] += (int) $sresult["event_duration"];

					if ($increment_total) {
                        $report_results["courses"]["events"]["total_events"] += 1;
                        $report_results["courses"]["events"]["total_minutes"] += (int) $sresult["event_duration"];
                    }

                    $report_results["courses"]["events"]["events_calculated"] += 1;
                    $report_results["courses"]["events"]["events_minutes"] += (int) $sresult["event_duration"];
				}
			}
		}
	}

	echo "<h1>Students' Evaluation of Courses</h1>";
	echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
	echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]);
	echo "</div>";
	?>
	<table class="tableList" cellspacing="0" summary="System Report">
	<colgroup>
		<col class="general" />
		<col class="report-hours" />
		<col class="report-hours" />
	</colgroup>
	<thead>
		<tr>
			<td class="general borderl">Course Title</td>
			<td class="report-hours">Class Year</td>
			<td class="report-hours">Course Period</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3" style="padding-top: 10px">
				<input type="button" class="button" value="Display Report(s)" onclick="window.location.href = window.location" />
			</td>
		</tr>
	</tfoot>
	<tbody>
				<td colspan="3" align=center><i>Selection List</i></td></tr>
	<?php
	if ((is_array($report_results["people"])) && (count($report_results["people"]))) {
		$i = 0;
		foreach ($report_results["people"] as $result) {
			$duration_event = $result["events"]["total_minutes"];
			if ($duration_event) {
				?>
				<tr<?php echo (($i % 2) ? " class=\"odd\"" : ""); ?>>
					<td class="general"><?php echo html_encode($result["fullname"]); ?></td>
					<td class="report-hours"><?php echo $result["events"]["total_events"]; ?></td>
					<td class="report-hours"><?php echo display_hours($duration_event); ?></td>
				</tr>
				<?php
			}
			$i++;
		}
	}

    if ((is_array($report_results["courses"])) && (count($report_results["courses"]))) {
		$total_event = $report_results["courses"]["events"]["events_minutes"];
		if ($total_event) {
			?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr class="modified">
				<td class="general">Final Totals:</td>
				<td class="report-hours"><?php echo $report_results["courses"]["events"]["total_events"]; ?></td>
				<td class="report-hours"><?php echo display_hours($total_event); ?></td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
	</table>
	<?php
	}
}