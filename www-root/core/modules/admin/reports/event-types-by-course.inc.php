<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * Module:	Reports
 * Area:		Admin
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2007 Queen's University, MEdTech Unit
 *
 * $Id: report-by-event-types.inc.php 992 2009-12-22 16:26:26Z simpson $
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_REPORTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]." and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => "", "title" => "Learning Event Types by Course");

	/**
	 * Add PlotKit to the beginning of the $HEAD array.
	 */
	array_unshift($HEAD,
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/MochiKit/MochiKit.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/excanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Base.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Layout.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/Canvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/PlotKit/SweetCanvas.js\"></script>"
		);

	$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
	$ONLOAD[]	= "$('courses_list').style.display = 'none'";

	$organisation_id_changed = false;

	/**
	 * Fetch the organisation_id that has been selected.
	 */
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] = -1;
	} elseif ((isset($_GET["org_id"])) && ($tmp_input = clean_input($_GET["org_id"], "int"))) {
		$organisation_id_changed = true;
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] = $tmp_input;
	} else {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] = (int) $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"];
	}

	/**
	 * Preference: Include Event Type Graph
	 */
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_graph"]) || (!in_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_graph"], array(0, 1)))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_graph"] = 1;
	} elseif (isset($_POST["event_type_graph"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_graph"] = (($_POST["event_type_graph"] == 0) ? 0 : 1);
	}

	/**
	 * Preference: Include Event Type Chart
	 */
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_chart"]) || (!in_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_chart"], array(0, 1)))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_chart"] = 1;
	} elseif (isset($_POST["event_type_chart"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_type_chart"] = (($_POST["event_type_chart"] == 0) ? 0 : 1);
	}

	/**
	 * Preference: Include Appendix Data
	 */
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"]) || (!in_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"], array(0, 1, 2)))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] = 1;
	} elseif (isset($_POST["event_appendix"]) && in_array((int) $_POST["event_appendix"], array(0, 1, 2))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] = (int) $_POST["event_appendix"];
	}

	/**
	 * Fetch all courses into an array that will be used.
	 */
	$query = "SELECT * FROM `courses`".(($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] > 0) ? " WHERE `organisation_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"]) : "")." ORDER BY `course_code` ASC";
	$courses = $db->GetAll($query);
	if ($courses) {
		foreach ($courses as $course) {
			$course_list[$course["course_id"]] = array("code" => $course["course_code"], "name" => $course["course_name"]);
		}
	}

	/**
	 * Fetch selected course_ids.
	 */
	if ((isset($_POST["course_ids"])) && (is_array($_POST["course_ids"]))) {
		$course_ids = array();

		foreach ($_POST["course_ids"] as $course_id) {
			if ($course_id = (int) $course_id) {
				$course_ids[] = $course_id;
			}
		}

		if (count($course_ids)) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"] = $course_ids;
		} else {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"] = array_keys($course_list);
		}
	} elseif (($organisation_id_changed) || (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"]))) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"] = array_keys($course_list);
	}

	if (isset($_POST["event_title_search"]) && $_POST["event_title_search"]) {
		$event_title_search = clean_input($_POST["event_title_search"], "notags");
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
		<form action="<?php echo ENTRADA_RELATIVE; ?>/admin/reports?section=<?php echo $SECTION; ?>&step=2" method="post" onsubmit="selIt()">
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
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td style="vertical-align: top;"><input id="organisation_checkbox" type="checkbox" disabled="disabled" checked="checked"></td>
						<td style="vertical-align: top;"><label for="organisation_id" class="form-required">Organisation</label></td>
						<td style="vertical-align: top;">
							<select id="organisation_id" name="organisation_id" style="width: 215px" onchange="window.location = '<?php echo ENTRADA_RELATIVE; ?>/admin/reports?section=<?php echo $SECTION; ?>&org_id=' + $F('organisation_id')">
							<?php
							$query = "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations`";
							$results = $db->GetAll($query);
							$all_organisations = false;
							if ($results) {
								$all_organisations = true;
								foreach ($results as $result) {
									if ($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "read")) {
										echo "<option value=\"".(int) $result["organisation_id"]."\"".(($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == $result["organisation_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["organisation_title"])."</option>\n";
									} else {
										$all_organisations = false;
									}
								}
							}

							if ($all_organisations) {
								?>
								<option value="-1" <?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] == -1) ? " selected=\"selected\"" : ""); ?>>All organisations</option>
								<?php
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top;"><label class="form-required">Courses Included</label></td>
						<td style="vertical-align: top;">
							<?php
							echo "<select class=\"multi-picklist\" id=\"PickList\" name=\"course_ids[]\" multiple=\"multiple\" size=\"5\" style=\"width: 100%; margin-bottom: 5px\">\n";
									if ((is_array($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"])) && (count($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"]))) {
										foreach ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"] as $course_id) {
											echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
										}
									}
							echo "</select>\n";
							echo "<div style=\"float: left; display: inline\">\n";
							echo "	<input type=\"button\" id=\"courses_list_state_btn\" class=\"button\" value=\"Show List\" onclick=\"toggle_list('courses_list')\" />\n";
							echo "</div>\n";
							echo "<div style=\"float: right; display: inline\">\n";
							echo "	<input type=\"button\" id=\"courses_list_remove_btn\" class=\"button-remove\" onclick=\"delIt()\" value=\"Remove\" />\n";
							echo "	<input type=\"button\" id=\"courses_list_add_btn\" class=\"button-add\" onclick=\"addIt()\" style=\"display: none\" value=\"Add\" />\n";
							echo "</div>\n";
							echo "<div id=\"courses_list\" style=\"clear: both; padding-top: 3px; display: none\">\n";
							echo "	<h2>Courses List</h2>\n";
							echo "	<select class=\"multi-picklist\" id=\"SelectList\" name=\"other_courses_list\" multiple=\"multiple\" size=\"15\" style=\"width: 100%\">\n";
									if ((is_array($course_list)) && (count($course_list))) {
										foreach ($course_list as $course_id => $course) {
											if (!in_array($course_id, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"])) {
												echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
											}
										}
									}
							echo "	</select>\n";
							echo "	</div>\n";
							echo "	<script type=\"text/javascript\">\n";
							echo "	\$('PickList').observe('keypress', function(event) {\n";
							echo "		if (event.keyCode == Event.KEY_DELETE) {\n";
							echo "			delIt();\n";
							echo "		}\n";
							echo "	});\n";
							echo "	\$('SelectList').observe('keypress', function(event) {\n";
							echo "	    if (event.keyCode == Event.KEY_RETURN) {\n";
							echo "			addIt();\n";
							echo "		}\n";
							echo "	});\n";
							echo "	</script>\n";
							?>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><label for="event_appendix" class="form-nrequired">Include <strong>Appendix</strong> Data</label></td>
						<td>
							<select id="event_appendix" name="event_appendix" style="width: 215px">
								<option value="0"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] == 0) ? " selected=\"selected\"" : ""); ?>>No, do not display this data.</option>
								<option value="1"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] == 1) ? " selected=\"selected\"" : ""); ?>>Yes, after each course.</option>
								<option value="2"<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] == 2) ? " selected=\"selected\"" : ""); ?>>Yes, at the end of the report.</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="vertical-align: top; padding-top: 6px"><label for="event_title_search" class="form-nrequired">Search <strong>Event Titles</strong> for</label></td>
						<td style="vertical-align: top;">
							<input type="text" value="<?php echo (isset($event_title_search) && $event_title_search ? $event_title_search : ""); ?>" name="event_title_search" id="event_title_search" style="width: 70%" />
							<div class="content-small" style="width: 70%">
								<strong>Please Note:</strong> You can leave this blank to include all events, or provide a search term (i.e. Unit 1) to include only those events in the report.
							</div>
						</td>
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
		$statistics = array();
		
		$summary = array();

		$courses_included = array();
		$eventtype_legend = array();

		if ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"] != -1) {
			$organisation_where = " AND (b.`organisation_id` = ".$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["organisation_id"].") ";
		} else {
			$organisation_where = "";
		}

		$query = "SELECT * FROM `events_lu_eventtypes` ORDER BY `eventtype_order` ASC";
		$event_types = $db->GetAll($query);
		if ($event_types) {
			foreach ($event_types as $event_type) {
				$eventtype_legend[$event_type["eventtype_id"]] = $event_type["eventtype_title"];

				foreach ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["course_ids"] as $course_id) {
					$query = "	SELECT a.`event_id`, b.`course_name`, a.`event_title`, a.`event_start`, c.`duration`, d.`eventtype_title`
								FROM `events` AS a
								LEFT JOIN `courses` AS b
								ON b.`course_id` = a.`course_id`
								LEFT JOIN `event_eventtypes` AS c
								ON c.`event_id` = a.`event_id`
								LEFT JOIN `events_lu_eventtypes` AS d
								ON d.`eventtype_id` = c.`eventtype_id`
								WHERE c.`eventtype_id` = ".$db->qstr($event_type["eventtype_id"])."
								AND (a.`event_start` BETWEEN ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." AND ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).")
								".(isset($event_title_search) && $event_title_search ? "AND a.`event_title` LIKE ".$db->qstr("%".$event_title_search."%") : "")."
								AND a.`course_id` = ".$db->qstr($course_id).
								$organisation_where."
								AND (a.`parent_id` = '0' OR a.`parent_id` IS NULL)
								ORDER BY d.`eventtype_order` ASC, b.`course_name` ASC, a.`event_start` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						$courses_included[$course_id] = $course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"];

						foreach ($results as $result) {
							$statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["duration"] += $result["duration"];
							$statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["events"] += 1;

							// Increment total number of unique events in this course.
							if (!array_key_exists($result["event_id"], $statistics[$course_id]["event_appendix"])) {
								$statistics[$course_id]["totals"]["unique_events"] += 1;
								$summary["unique_events"] += 1;
							}

							$statistics[$course_id]["event_appendix"][$result["event_id"]][] = $result;
						}

						$statistics[$course_id]["totals"]["duration"] += $statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["duration"];
						$statistics[$course_id]["totals"]["events"] += $statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["events"];

						$summary["duration"] += $statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["duration"];
						$summary["events"] += $statistics[$course_id]["event_types"][$event_type["eventtype_id"]]["events"];
					}
				}
			}
		}

		echo "<h1 style=\"page-break-before: avoid\">Learning Event Types by Course</h1>";
		echo "<div class=\"content-small\" style=\"margin-bottom: 10px\">\n";
		echo "	<strong>Date Range:</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_start"])." <strong>to</strong> ".date(DEFAULT_DATE_FORMAT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["reporting_finish"]).".";
		echo "</div>\n";

		if (count($statistics)) {
			echo "<h2>Table Of Contents</h2>\n";
			?>
			<table id="table_of_contents" class="tableList" cellspacing="0" summary="Table of Contents">
			<colgroup>
				<col class="title" />
				<col class="report-hours large" style="background-color: #F3F3F3" />
				<col class="report-hours large" />
			</colgroup>
			<thead>
				<tr>
					<td class="title borderl">Course Title</td>
					<td class="report-hours large">Total Events</td>
					<td class="report-hours large">Total Hours</td>
				</tr>
			</thead>
				<tbody>
				<?php
				foreach ($statistics as $course_id => $course) {
					echo "<tr>\n";
					echo "	<td class=\"title\"><a href=\"#section_".$course_id."\" style=\"font-weight: strong\">".html_encode($courses_included[$course_id])."</a></td>\n";
					echo "	<td class=\"report-hours large\">".$events = $course["totals"]["unique_events"]." event".(($events != 1) ? "s" : "")."</td>\n";
					echo "	<td class=\"report-hours large\">".$hours = display_hours($course["totals"]["duration"])." hr".(($hours != 1) ? "s" : "")."</td>\n";
					echo "</tr>\n";
				}
				?>
				</tbody>
				<tbody>
					<tr class="na">
						<td>Course Totals</td>
						<td class="report-hours large"><?php echo $summary["unique_events"]; ?></td>
						<td class="report-hours large"><?php echo display_hours($summary["duration"]); ?> hrs</td>
					</tr>
				</tbody>
			</table>

			<?php
			foreach ($statistics as $course_id => $course) {
				?>
				<a name="section_<?php echo $course_id; ?>"></a>
				<h1><?php echo html_encode($courses_included[$course_id]); ?></h1>
				<?php
				$plotkit = array();
				$plotkit["labels"] = array();
				$plotkit["legend"] = array();
				$plotkit["results"] = array();
				?>
				<h2>Learning Event Type Breakdown</h2>

				<div style="text-align: center">
					<canvas id="graph_1_<?php echo $course_id; ?>" width="750" height="450"></canvas>
				</div>
				<table id="data_table_<?php echo $course_id; ?>" class="tableList" style="width: 750px" cellspacing="0" summary="Event Types of <?php echo html_encode($courses_included[$course_id]); ?>">
					<colgroup>
						<col class="modified" />
						<col class="title" />
						<col class="report-hours" style="background-color: #F3F3F3" />
						<col class="report-hours" />
					</colgroup>
					<thead>
						<tr>
							<td class="modified">&nbsp;</td>
							<td class="title">Event Type</td>
							<td class="report-hours large">Event Type Count</td>
							<td class="report-hours large">Hour Count</td>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($course["event_types"] as $eventtype_id => $event) {
						$plotkit["labels"][$eventtype_id] = $eventtype_legend[$eventtype_id];
						$plotkit["legend"][$eventtype_id] = $eventtype_legend[$eventtype_id];
						$plotkit["display"][$eventtype_id] = display_hours($event["duration"]);

						if ($course["totals"]["events"] > 0) {
							$percent_events = round((($event["events"] / $course["totals"]["events"]) * 100));
						} else {
							$percent_events = 0;
						}

						if ($course["totals"]["duration"] > 0) {
							$percent_duration = round((($event["duration"] / $course["totals"]["duration"]) * 100));
						} else {
							$percent_duration = 0;
						}

						echo "<tr>\n";
						echo "	<td>&nbsp;</td>\n";
						echo "	<td>".html_encode($eventtype_legend[$eventtype_id])."</td>\n";
						echo "	<td class=\"report-hours large\" style=\"text-align: left\">".$event["events"]." (~ ".$percent_events."%)</td>\n";
						echo "	<td class=\"report-hours large\" style=\"text-align: left\">".display_hours($event["duration"])." hrs (~ ".$percent_duration."%)</td>\n";
						echo "</tr>\n";
					}
					?>
					</tbody>
					<tbody>
						<tr class="na">
							<td>&nbsp;</td>
							<td>Event Type Totals</td>
							<td class="report-hours large"><?php echo $course["totals"]["events"]; ?></td>
							<td class="report-hours large"><?php echo display_hours($course["totals"]["duration"]); ?> hrs</td>
						</tr>
						<?php if ($course["totals"]["events"] != $course["totals"]["unique_events"]) : ?>
						<tr class="resubmit">
							<td>&nbsp;</td>
							<td colspan="3">Total of <strong><?php echo $course["totals"]["unique_events"]; ?></strong> events with <strong><?php echo $course["totals"]["events"]; ?></strong> event type segments.</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
				<script type="text/javascript">
				var options = {
				   'IECanvasHTC': '<?php echo ENTRADA_RELATIVE; ?>/javascript/plotkit/iecanvas.htc',
				   'yTickPrecision': 1,
				   'xTicks': [<?php echo plotkit_statistics_lables($plotkit["legend"]); ?>]
				};

			    var layout	= new PlotKit.Layout('pie', options);
			    layout.addDataset('results', [<?php echo plotkit_statistics_values($plotkit["display"]); ?>]);
			    layout.evaluate();

			    var canvas	= MochiKit.DOM.getElement('graph_1_<?php echo $course_id; ?>');
			    var plotter	= new PlotKit.SweetCanvasRenderer(canvas, layout, options);
			    plotter.render();

			    var canvas	= MochiKit.DOM.getElement('graph_2_<?php echo $course_id; ?>');
			    var plotter	= new PlotKit.SweetCanvasRenderer(canvas, layout, options);
			    plotter.render();
				</script>

				<?php
				// If the appendix is set to display after each course.
				if ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] == 1) {
					echo "<a name=\"section_\"".$course_id."_appendix\"></a>\n";
					echo "<h2>Learning Event Appendix Data</h2>\n";
					$appendix_data = display_appendix_data($course);
					if ($appendix_data) {
						echo $appendix_data;
					} else {
						echo display_notice(array("There is no appendix information available for this course."));
					}
				}
			}

			// If the appendix is set to display at the end of the document.
			if ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["event_appendix"] == 2) {
				foreach ($statistics as $course_id => $course) {
					echo "<a name=\"section_\"".$course_id."_appendix\"></a>\n";
					echo "<h1>Appendix: ".html_encode($courses_included[$course_id])." Data</h1>\n";
					$appendix_data = display_appendix_data($course);
					if ($appendix_data) {
						echo $appendix_data;
					} else {
						echo display_notice(array("There is no appendix information available for this course."));
					}
				}
			}
		} else {
			echo display_notice(array("There are no learning events in the system during the timeframe you have selected."));
		}
	}
}

function display_appendix_data(&$course = array()) {
	global $courses_included;

	ob_start();
	?>
	<table class="tableList" cellspacing="0" summary="Appendix: <?php echo html_encode($courses_included[$course_id]); ?> Data">
		<colgroup>
			<col class="title" />
			<col class="date" />
			<col class="date" style="background-color: #F3F3F3" />
			<col class="report-hours" />
		</colgroup>
		<thead>
			<tr>
				<td class="title" style="border-left: 1px #666 solid">Event Title</td>
				<td class="date">Event Type</td>
				<td class="date">Date</td>
				<td class="report-hours">Duration</td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($course["event_appendix"] as $event_id => $events) {
			foreach ($events as $event) {
				$total_duration += $event["duration"];

				echo "<tr>\n";
				echo "	<td class=\"title\"><a href=\"".ENTRADA_URL."/events?id=".$event["event_id"]."\" target=\"_blank\">".html_encode($event["event_title"])."</a></td>\n";
				echo "	<td class=\"date\">".html_encode($event["eventtype_title"])."</td>\n";
				echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $event["event_start"])."</td>\n";
				echo "	<td class=\"report-hours\">".$hours = display_hours($event["duration"])." hr".(($hours != 1) ? "s" : "")."</td>\n";
				echo "</tr>\n";
			}
		}

		echo "<tr class=\"na\" style=\"font-weight: bold\">\n";
		echo "	<td colspan=\"2\" style=\"padding-left: 10px\">Total of ".$course["totals"]["unique_events"]." events with ".$course["totals"]["events"]." event type segments.</td>\n";
		echo "	<td class=\"date\" style=\"text-align: right\">Total Hours:</td>\n";
		echo "	<td class=\"report-hours\">".$hours = display_hours($course["totals"]["duration"])." hr".(($hours != 1) ? "s" : "")."</td>\n";
		echo "</tr>\n";
		?>
		</tbody>
	</table>
	<?php
	return ob_get_clean();
}