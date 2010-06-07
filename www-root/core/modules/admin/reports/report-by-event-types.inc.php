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
 * @version $Id: report-by-event-types.inc.php 1169 2010-05-01 14:18:49Z simpson $
 */

if((!defined("PARENT_INCLUDED")) || (!defined("IN_REPORTS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('report', 'read', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]." and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Learning Event Type Report (by Term)");
	
	if ((isset($_POST["clinicalskills"])) && ((int) $_POST["clinicalskills"] == 1)) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["clinicalskills"] = 1;
	} else {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["clinicalskills"] = 0;
	}
	
	?>
	<style type="text/css">
	h1 {
		page-break-before:	always;
		border-bottom:		2px #CCCCCC solid;
		font-size:			24px;
	}
	
	h2 {
		font-weight:		normal;
		border:				0px;
		font-size:			18px;
	}
	
	div.top-link {
		float: right;
	}
	</style>	
	<div class="no-printing">
		<form action="<?php echo ENTRADA_URL; ?>/admin/reports?section=<?php echo $SECTION; ?>&step=2" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
				<colgroup>
					<col style="width: 3%" />
					<col style="width: 20%" />
					<col style="width: 77%" />
				</colgroup>
				<tbody>
					<tr>
						<td colspan="3"><h2>Reporting Dates</h2></td>
					</tr>
					<?php echo generate_calendars("reporting", "Reporting Date", true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"], true, true, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]); ?>
					<tr>
						<td style="vertical-align: top;"><input id="organisation_checkbox" type="checkbox" disabled="disabled" checked="checked"></td>
						<td style="vertical-align: top; padding-top: 4px;"><label for="organisation_id" class="form-required">Organisation</label></td>
						<td style="vertical-align: top;">
							<select id="organisation_id" name="organisation_id" style="width: 177px">
								<?php
								$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations`";
								$results	= $db->GetAll($query);
								$all_organisations = false;
								if($results) {
									$all_organisations = true;
									foreach($results as $result) {
										if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'read')) {
											echo "<option value=\"".(int) $result["organisation_id"]."\"".(((isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"])) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == $result["organisation_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["organisation_title"])."</option>\n";
										} else {
											$all_organisations = false;
										}
									}
								}
								if($all_organisations) {
									?>
									<option value="-1" <?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) && ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == -1) ? " selected=\"selected\"" : ""); ?>>All organisations</option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top; padding-top: 10px;"><input id="clinicalskills_checkbox" type="checkbox" name="clinicalskills" value="1" <?php echo (isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["clinicalskills"]) && ((int) $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["clinicalskills"] == 1) ? " checked=\"checked\"" : ""); ?>></td>
						<td style="vertical-align: top; padding-top: 10px;" colspan="2"><label for="clinicalskills_checkbox" class="form-nrequired">Include Clinical Skills course hours in results.</label></td>
					</tr>
					<tr>
						<td colspan="3" style="text-align: right; padding-top: 10px"><input type="submit" class="button" value="Create Report" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<?php
	if ($STEP == 2) {
		$output		= array();
		$appendix	= array();
		
		$terms_included		= array("1" => "Term 1", "2" => "Term 2", "2A" => "Phase 2A", "2B" => "Phase 2B", "2C" => "Phase 2C", "2E" => "Phase 2E", "3" => "Phase 3");
		
		if ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["clinicalskills"]) {
			// Include all courses
			$courses_excluded	= array();
		} else {
			 // Exclude all Clinical Skills Course IDS
			$courses_excluded	= array(62, 63, 64, 65, 66, 67, 72, 95, 99);
		}
		
		$eventtype_legend	= array();
		
		echo "<h1 style=\"page-break-before: avoid\">Learning Event Type Report (by Term)</h1>";
		echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
		echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).".";
		echo "</div>\n";

		if(isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] != -1) {
			$organisation_where = " AND (b.`organisation_id` = ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"].") ";
		} else {
			$organisation_where = "";
		}
		
		$query			= "SELECT * FROM `events_lu_eventtypes` ORDER BY `eventtype_order` ASC";
		$event_types	= $db->GetAll($query);
		if ($event_types) {
			foreach ($event_types as $event_type) {
				$eventtype_legend[$event_type["eventtype_id"]] = $event_type["eventtype_title"];
				
				foreach ($terms_included as $term => $term_title) {
					$query		= "	SELECT a.`event_id`, b.`course_name`, a.`event_title`, a.`event_start`, a.`event_duration`, d.`eventtype_title`
									FROM `events` AS a
									LEFT JOIN `courses` AS b
									ON b.`course_id` = a.`course_id`
									LEFT JOIN `event_audience` AS c
									ON a.`event_id` = c.`event_id`
									LEFT JOIN `events_lu_eventtypes` AS d
									ON d.`eventtype_id` = a.`eventtype_id`
									WHERE a.`eventtype_id` = ".$db->qstr($event_type["eventtype_id"])."
									AND b.`course_active` = '1'
									AND (a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
									AND a.`event_phase` = ".$db->qstr($term)."
									AND c.`audience_type` = 'grad_year'
									".((count($courses_excluded)) ? " AND a.`course_id` NOT IN (".implode(", ", $courses_excluded).")" : "").
									$organisation_where."
									GROUP BY a.`event_id`
									ORDER BY d.`eventtype_order` ASC, b.`course_name` ASC, a.`event_start` ASC";
					$results	= $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$output[$term]["events"][$event_type["eventtype_id"]]["duration"] += $result["event_duration"];
							$output[$term]["events"][$event_type["eventtype_id"]]["events"] += 1;
							
							$appendix[$term][$result["event_id"]] = $result;
						}
					} else {
						$output[$term]["events"][$event_type["eventtype_id"]] = array("duration" => 0, "events" => 0);
					}
					
					$output[$term]["total_duration"] += $output[$term]["events"][$event_type["eventtype_id"]]["duration"];
					$output[$term]["total_events"] += $output[$term]["events"][$event_type["eventtype_id"]]["events"];
				}
			}
		}
		
		if (count($output)) {
			?>
			<table class="tableList" cellspacing="0" summary="Event Type Report (Hourly)">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="report-hours" style="background-color: #F3F3F3" />
				<col class="report-hours" />
				<col class="report-hours" style="background-color: #F3F3F3" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td class="title">Event Type</td>
					<td class="report-hours">Event Count</td>
					<td class="report-hours">Hour Count</td>
					<td class="report-hours">Percent (hr)</td>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($output as $term => $result) {
				
				echo "<tr>\n";
				echo "	<td colspan=\"5\"><h2>".html_encode($terms_included[$term])."</h2></td>\n";
				echo "</tr>\n";
				
				foreach ($result["events"] as $eventtype_id => $event) {
					if ($result["total_duration"] > 0) {
						$percent = round((($event["duration"] / $result["total_duration"]) * 100));
					} else {
						$percent = 0;
					}
					echo "<tr>\n";
					echo "	<td>&nbsp;</td>\n";
					echo "	<td>".html_encode($eventtype_legend[$eventtype_id])."</td>\n";
					echo "	<td class=\"report-hours\">".$event["events"]."</td>\n";
					echo "	<td class=\"report-hours\">".display_hours($event["duration"])." hrs</td>\n";
					echo "	<td class=\"report-hours\">~ ".$percent." %</td>\n";
					echo "</tr>\n";
				}
				
				echo "<tr class=\"na\">\n";
				echo "	<td>&nbsp;</td>\n";
				echo "	<td>Event Type Totals</td>\n";
				echo "	<td class=\"report-hours\">".$result["total_events"]."</td>\n";
				echo "	<td class=\"report-hours\">".display_hours($result["total_duration"])." hrs</td>\n";
				echo "	<td class=\"report-hours\"></td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
			</table>
			<?php
		} else {
			echo display_notice(array("There are no learning events in the system during the timeframe you have selected."));	
		}
		
		if (count($courses_excluded)) {
			echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
			$course_names = array();
			$query		= "	SELECT `course_name` FROM `courses` 
							WHERE `course_id` IN (".implode(", ", $courses_excluded).")
							AND `course_active` = '1'";
			$results	= $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
					$course_names[] = html_encode($result["course_name"]);
				}
			}

			if (count($course_names)) {
				echo "<br />";
				echo "<strong>Courses excluded from report:</strong> ".implode(", ", $course_names);
			}
			echo "</div>\n";		
		}

		if (count($output)) {
			foreach ($output as $term => $result) {
				$total_duration = 0;
				?>
				<h1>Appendix: Phase / Term <?php echo html_encode($term); ?> Data</h1>
				<?php
				if ($appendix[$term]) {
					?>
					<table class="tableList" cellspacing="0" summary="Appendix: Phase / Term <?php echo html_encode($term); ?> Data">
					<colgroup>
						<col class="general" />
						<col class="report-hours" style="background-color: #F3F3F3" />
						<col class="title" />
						<col class="date" style="background-color: #F3F3F3" />
						<col class="report-hours" />
					</colgroup>
					<thead>
						<tr>
							<td class="report-hours" style="border-left: 1px #666 solid" >Event Type</td>
							<td class="title">Course</td>
							<td class="title">Event Title</td>
							<td class="date">Date</td>
							<td class="report-hours">Duration</td>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($appendix[$term] as $event_id => $event) {
						$total_duration += $event["event_duration"];

						echo "<tr>\n";
						echo "	<td class=\"report-hours\">".html_encode($event["eventtype_title"])."</td>\n";
						echo "	<td class=\"title\">".html_encode($event["course_name"])."</td>\n";
						echo "	<td class=\"title\">".html_encode($event["event_title"])."</td>\n";
						echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $event["event_start"])."</td>\n";
						echo "	<td class=\"report-hours\">".display_hours($event["event_duration"])."</td>\n";
						echo "</tr>\n";
					}

					echo "<tr class=\"na\" style=\"font-weight: bold\">\n";
					echo "	<td class=\"report-hours\" style=\"text-align: right\">Total Events:</td>\n";
					echo "	<td class=\"title\" colspan=\"2\">".count($appendix[$term])."</td>\n";
					echo "	<td class=\"date\" style=\"text-align: right\">Total Hours:</td>\n";
					echo "	<td>".display_hours($total_duration)."</td>\n";
					echo "</tr>\n";
					?>
					</tbody>
					</table>
					<?php
				} else {
					echo display_notice(array("There are no learning events in this phase / term during the selected duration."));
				}
			}
		}
	}
}
?>