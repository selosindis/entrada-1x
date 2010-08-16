
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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: edit.inc.php 1169 2010-05-01 14:18:49Z simpson $
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Add PlotKit to the beginning of the $HEAD array.
	 */
	array_unshift($HEAD,
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/MochiKit/MochiKit.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/excanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Base.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Layout.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/Canvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/SweetCanvas.js\"></script>",
		"<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/PlotKit/EasyPlot.js\"></script>"
		);
		
	if ($COURSE_ID) {
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => "Grading Assessment");
			
			$query = "	SELECT `assessments`.*,`assessment_marking_schemes`.`id` as `marking_scheme_id`, `assessment_marking_schemes`.`handler`
						FROM `assessments`
						LEFT JOIN `assessment_marking_schemes` ON `assessment_marking_schemes`.`id` = `assessments`.`marking_scheme_id`
						WHERE `assessments`.`assessment_id` = ".$db->qstr($ASSESSMENT_ID);
						
			$assessment = $db->GetRow($query);
			
			if($assessment) {
				$GRAD_YEAR = $assessment["grad_year"];
				
				courses_subnavigation($course_details);

				?>
				<h1><?php echo $course_details["course_name"]; ?> Gradebook: <?php echo $assessment["name"]; ?> (Class of <?php echo $assessment["grad_year"]; ?>)</h1>
			
				<div style="float: right; text-align: right;">
					<ul class="page-action">
						<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE . "/assessments/?" . replace_query(array("section" => "edit", "step" => false)); ?>" class="strong-green">Edit Assessment</a></li>
					</ul>
				</div>
				<div style="clear: both"><br/></div>
			
				<?php
				$query = "	SELECT b.`id` AS `proxy_id`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, b.`number`, g.`grade_id` AS `grade_id`, g.`value` AS `grade_value`
							FROM `".AUTH_DATABASE."`.`user_data` AS b
							LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
							ON c.`user_id` = b.`id` AND c.`app_id`=".$db->qstr(AUTH_APP_ID)."
							AND c.`account_active`='true'
							AND (c.`access_starts`='0' OR c.`access_starts`<=".$db->qstr(time()).")
							AND (c.`access_expires`='0' OR c.`access_expires`>=".$db->qstr(time()).")
							LEFT JOIN `".DATABASE_NAME."`.`assessment_grades` AS g ON b.`id` = g.`proxy_id` AND g.`assessment_id` = ".$db->qstr($assessment["assessment_id"])."
							WHERE c.`group` = 'student'
							AND c.`role` = ".$db->qstr($GRAD_YEAR)."
							ORDER BY b.`lastname` ASC, b.`firstname` ASC";
				
				$students = $db->GetAll($query);
				$editable = $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update") ? "gradebook_editable" : "gradebook_not_editable";
				if(count($students) >= 1): ?>
					<span id="assessment_name" style="display: none;"><?php echo $assessment["name"]; ?></span>
					<div id="gradebook_grades">
						<h2>Grades</h2>
						<table style="width: 440px" class="gradebook single <?php echo $editable; ?>">
							<tbody>
								<?php
								foreach ($students as $key => $student) {
									if (isset($student["grade_id"])) {
										$grade_id = $student["grade_id"];
									} else {
										$grade_id = "";
									}
									
									if (isset($student["grade_value"])) {
										$grade_value = format_retrieved_grade($student["grade_value"], $assessment);
									} else {
										$grade_value = "-";
									}
									?>
									<tr id="grades<?php echo $student["proxy_id"]; ?>">
										<td><?php echo $student["fullname"]; ?></td>
										<td><?php echo $student["number"]; ?></td>
										<td>
											<span class="grade"
												data-grade-id="<?php echo $grade_id; ?>"
												data-assessment-id="<?php echo $assessment["assessment_id"]; ?>"
												data-proxy-id="<?php echo $student["proxy_id"] ?>"
											><?php echo $grade_value; ?></span>
											<span class="gradesuffix" <?php echo (($grade_value === "-") ? "style=\"display: none;\"" : "") ?>>
												<?php echo assessment_suffix($assessment); ?>
											</span>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>
					<div id="gradebook_stats">
						<h2>Statistics</h2>
						<div id="graph"></div>
					 	<?php 
						switch($assessment["marking_scheme_id"]) {
							case 1:
							case 4:
								//pass/fail
								$grades = array(0,0,0);
								$unentered = 0;

								foreach ($students as $key => $student) {
									if ($student["grade_value"] == "") {
										$unentered++;
									} elseif ($student["grade_value"] > 50){
										$grades[0]++;
									} else {
										$grades[1]++;
									}
								}

								$grade_data = array();
								foreach($grades as $key => $grade) {
									$grade_data[] = "[$key, $grade]";
								}
								?>
								<script type="text/javascript" charset="utf-8">
									var data = [<?php echo implode(", ", $grade_data); ?>];
									var plotter = PlotKit.EasyPlot(
										"pie",
										{
											"xTicks": [{v:0, label:"<?php echo $assessment["marking_scheme_id"] == 4 ? "Complete" : "Pass" ?>"},
														{v:1, label:"<?php echo $assessment["marking_scheme_id"] == 4 ? "Incomplete" : "Fail" ?>"}]
										},
										$("graph"),
										[data]
									);
								</script>
								<br/>
								<p>Unentered grades: <?php echo (int) $unentered; ?></p>
								<?php
							break;
							case 2:
							case 3:
								// Percentage (numeric interpreted as percentage)
								$grades = array(0,0,0,0,0,0,0,0,0,0,0);

								$sum = 0;

								$entered = 0;
								$unentered = 0;

								$grade_values = array();
								foreach($students as $key => $student) {
									if ($student["grade_value"] == "") {
										//$grades[11]++;
										$unentered++;
									} else {
										$sum += $student["grade_value"];
										$entered++;
										$grade_values[] = $student["grade_value"];

										$key = floor($student["grade_value"] / 10);
										$grades[$key]++;
									}
								}

								$grade_data = array();
								foreach ($grades as $key => $grade) {
									$grade_data[] = "[$key, $grade]";
								}
								sort($grade_values);
								?>
								<script type="text/javascript" charset="utf-8">
									var data = [<?php echo implode(", ", $grade_data); ?>];
									var plotter = PlotKit.EasyPlot(
										"bar",
										{
											"xTicks": [{v:0, label:"0s"},
														{v:1, label:"10s"},
														{v:2, label:"20s"},
														{v:3, label:"30s"},
														{v:4, label:"40s"},
														{v:5, label:"50s"},
														{v:6, label:"60s"},
														{v:7, label:"70s"},
														{v:8, label:"80s"},
														{v:9, label:"90s"},
														{v:10, label:"100"}]
										},
										$("graph"),
										[data]
									);
								</script>
								<br/>
								<p>Unentered grades: <?php echo (int) $unentered; ?></p>
								<p>Mean grade: <?php echo number_format(($entered > 0 ? $sum / $entered : ""), 0); ?>%</p>
								<p>Median grade: <?php echo $grade_values[floor(count($grade_values) / 2)]; ?>%</p>
								<?php
							break;
							default:
								echo "No statistics for this marking scheme.";
							break;
						}
						?>
						<button onclick="window.location='<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => $ASSESSMENT_ID)); ?>'">Download CSV</button>
						<button onclick="location.reload(true)">Refresh</button>
					</div>
				<?php
				else:
				?>
				<div class="display-notice">There are no students in the system for this assessment's Graduating Year <strong><?php echo $GRAD_YEAR; ?></strong>.</div>
				<?php endif;
			} else {
				$ERROR++;
				$ERRORSTR[] = "In order to edit an assessment's grades you must provide a valid assessment identifier.";

				echo display_error();

				application_log("notice", "Failed to provide a valid assessment identifier when attempting to edit an assessment's grades.");
			}

		} else {
			$ERROR++;
			$ERRORSTR[] = "You don't have permission to edit this gradebook.";

			echo display_error();

			application_log("error", "User tried to edit gradebook without permission.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifier when attempting to edit an assessment's grades.");
	}
}
?>