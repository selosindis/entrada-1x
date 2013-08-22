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
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	ob_clear_open_buffers();
	if ($COURSE_ID) {
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		
		if ($course_details && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "read")) {
						
			if (isset($_GET["cohort"]) && ($tmp_input = clean_input($_GET["cohort"], "int"))) {
				$COHORT = $tmp_input;
			}			
				
			?>
			<div id="toolbar" style="display: none;">
				<select id="filter_cohort" name="filter_cohort" style="width: 203px; float: left;">
				<?php
                $query =  "SELECT a.`course_id`, b.`group_name`, b.`group_id` 
                            FROM `assessments` AS a
                            JOIN `groups` AS b
                            ON a.`cohort` = b.`group_id`
                            JOIN `group_organisations` AS c
                            ON b.`group_id` = c.`group_id`
                            WHERE a.`course_id` =". $db->qstr($COURSE_ID)."
                            AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                            GROUP BY b.`group_id`
                            ORDER BY b.`group_name`";
                $cohorts = $db->GetAll($query);
				if (isset($cohorts) && !empty($cohorts)) {
                    $cohort_found = false;
                    foreach ($cohorts as $key => $cohort) {
                        if (!$cohort_found) {
                            if (isset($COHORT) && $COHORT && $COHORT == $cohort["group_id"]) {
                                $cohort_found = true;
                            }
                            if ($key == (count($cohorts) - 1) && !$cohort_found) {
                                $COHORT = $cohort["group_id"];
                            }
                        }
                    }
					foreach ($cohorts as $cohort) {
						echo "<option value=\"".$cohort["group_id"]."\"".(($COHORT == $cohort["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
					}
				}
				?>
				</select>
			</div>
			<?php
			$query = "	SELECT a.*, b.`handler`, GROUP_CONCAT(c.`aoption_id`) AS `options`
						FROM `assessments` AS a
						LEFT JOIN `assessment_marking_schemes` AS b
						ON b.`id` = a.`marking_scheme_id`
						LEFT JOIN `assessment_options` AS c
						ON a.`assessment_id` = c.`assessment_id`
						AND (c.`option_id` = 5 OR c.`option_id` = 6)
						WHERE a.`course_id` = ".$db->qstr($COURSE_ID)."
						AND a.`cohort` = ".$db->qstr($COHORT)."
						GROUP BY a.`assessment_id`";
			$assessments = $db->GetAll($query);
			if($assessments) {
				
				$student_query = "SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`number`, c.`group_name` AS `cohort`
                                    FROM `group_members` AS a 
                                    JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                    ON a.`proxy_id` = b.`id`
                                    JOIN `groups` AS c
                                    ON a.`group_id` = c.`group_id`
                                    WHERE a.`group_id` = ".$db->qstr($COHORT)." 
                                    AND a.`member_active` = 1
                                    ORDER BY b.`lastname`, b.`firstname`";
				$students = $db->GetAll($student_query);
							
				$editable = $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update") ? "gradebook_editable" : "gradebook_not_editable";
                $assessment_ids_string = "";
                foreach($assessments as $assessment) {
					if (!is_null($assessment["options"])) {
						$assessment_options[$assessment["assessment_id"]] = explode(",", $assessment["options"]);
					}
                    $assessment_ids_string .= ($assessment_ids_string ? ", " : "").$db->qstr($assessment["assessment_id"]);
                    echo "<input type=\"hidden\" id=\"assessment_ids\" name=\"assessment_ids[]\" value=\"".$assessment["assessment_id"]."\" />\n";
                }
				?>
                <input type="hidden" id="gradebook_export_url" value="<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "io", "download" => "csv", "assessment_ids" => false)); ?>&assessment_ids=" />
				<table class="gradebook <?php echo $editable; ?>">
					<thead>
						<tr>
							<th style="width: 175px;">Student Name</th>
							<th style="width: 100px;">Student Number</th>
							<th style="width: 100px;">Graduating Class</th>
							<?php
							foreach($assessments as $assessment) {
								echo "<th>{$assessment["name"]}</th>\n";
							}
							?>
							<?php if (isset($assessment_options)) { ?>
							<th>Lates</th>
							<th>Resubmits</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach($students as $key => $student) {
                        $student_grades = array();
                        $query	= "SELECT `grade_id`, `value` AS `grade_value`, `assessment_id`
                                    FROM `".DATABASE_NAME."`.`assessment_grades`
                                    WHERE `assessment_id` IN (".$assessment_ids_string.")
                                    AND `proxy_id` = ".$db->qstr($student["proxy_id"]);
                        $temp_grades = $db->GetAll($query);
                        if ($temp_grades) {
                            foreach ($temp_grades as $temp_grade) {
                                $student_grades[$student["proxy_id"]."-".$temp_grade["assessment_id"]] = $temp_grade;
                            }
                        }
                        ?>
						<tr id="grades<?php echo $student["proxy_id"]; ?>">
							<td><?php echo $student["fullname"]; ?></td>
							<td><?php echo $student["number"]; ?></td>
							<td><?php echo $student["cohort"]; ?></td>
							<?php
							$lates = 0;
							$resubmits = 0;
							foreach($assessments as $key2 => $assessment) {
								if(isset($student_grades[$student["proxy_id"]."-".$assessment["assessment_id"]]["grade_id"])) {
									$grade_id = $student_grades[$student["proxy_id"]."-".$assessment["assessment_id"]]["grade_id"];
								} else {
									$grade_id = "";
								}

								if(isset($student_grades[$student["proxy_id"]."-".$assessment["assessment_id"]]["grade_value"])) {
									$grade_value = format_retrieved_grade($student_grades[$student["proxy_id"]."-".$assessment["assessment_id"]]["grade_value"], $assessment);
								} else {
									$grade_value = "-";
								}
								
								if ($assessment_options[$assessment["assessment_id"]]) {
									foreach ($assessment_options[$assessment["assessment_id"]] as $aoption_id) {
										$query = "	SELECT a.`value`, b.`option_id`
													FROM `assessment_option_values` AS a
													JOIN `assessment_options` AS b
													ON a.`aoption_id` = b.`aoption_id`
													WHERE a.`aoption_id` = ".$db->qstr($aoption_id). " 
													AND a.`proxy_id` = ".$db->qstr($student["proxy_id"]). " 
													AND a.`value` != 0 
													AND a.`value` IS NOT NULL";
										$result = $db->GetRow($query);
										if ($result && $result["option_id"] == "5") {
											$lates += $result["value"];
										} else if ($result && $result["option_id"] == "6") {
											$resubmits ++;
										}
									}
								}
								
								?>
								<td>
									<span class="grade"
										id="grade_<?php echo $assessment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>"  
										data-grade-id="<?php echo $grade_id; ?>"
										data-assessment-id="<?php echo $assessment["assessment_id"]; ?>"
										data-proxy-id="<?php echo $student["proxy_id"] ?>"
									><?php echo $grade_value; ?></span>
									<span class="gradesuffix" <?php echo (($grade_value === "-") ? "style=\"display: none;\"" : "") ?>>
										<?php echo assessment_suffix($assessment); ?>
									</span>
									<span class="gradesuffix" style="float:right;">
												<img src="<?php echo ENTRADA_URL;?>/images/action-edit.gif" class="edit_grade" id ="edit_grade_<?php echo $assessment["assessment_id"]; ?>_<?php echo $student["proxy_id"] ?>" style="cursor:pointer;"/>
									</span>
								</td>
								<?php
							}
							?>
							<?php if (isset($assessment_options)) { ?>
							<td><?php echo $lates; ?></td>
							<td><?php echo $resubmits; ?></td>
							<?php } ?>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<?php if(count($students) === 0):	?>
				<div class="display-notice">There are no students in the system for this cohort [<strong><?php echo groups_get_name($COHORT); ?></strong>].</div>
				<?php endif; ?>
			<?php
			} else {
				echo "<table class=\"gradebook\"></table>";
				$NOTICE++;
				$NOTICESTR[] = "No assessments could be found for this gradebook for this cohort [".groups_get_name($COHORT)."].";

				echo display_notice();
			}

		} else {
			echo "<table class=\"gradebook\"></table>";
			
			$ERROR++;
			$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to view a gradebook");
		}
	} else {
		echo "<table class=\"gradebook\"></table>";
		
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the courses identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to view a gradebook");
	}
}

exit;