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

if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "create", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	/**
	 * Clears all open buffers so we can return a plain response for the Javascript.
	 */
	ob_clear_open_buffers();

	$eform_id = 0;
	$evaluation_id = 0;

	if (isset($_POST["form_id"]) && ($tmp_input = clean_input($_POST["form_id"], "int"))) {
		$eform_id = $tmp_input;
	}

	if (isset($_POST["evaluation_id"]) && ($tmp_input = clean_input($_POST["evaluation_id"], "int"))) {
		$evaluation_id = $tmp_input;
	}

	if ($eform_id) {
		$query = "	SELECT b.*
					FROM `evaluation_forms` AS a
					LEFT JOIN `evaluations_lu_targets` AS b
					ON b.`target_id` = a.`target_id`
					WHERE a.`form_active` = '1'
					AND b.`target_active` = '1'
					AND a.`eform_id` = ".$db->qstr($eform_id);
		$target_details = $db->GetRow($query);
		if ($target_details) {
			/**
			 * *sigh* This should be a class.
			 */
			switch ($target_details["target_shortname"]) {
				case "course" :
					$courses = array();
					$courses_list = array();
					$evaluator_type = "grad_year";
					$evaluators = array();

					$query = "	SELECT `course_id`, `organisation_id`, `course_code`, `course_name`
								FROM `courses`
								WHERE `organisation_id`=".$db->qstr($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"])."
								AND `course_active` = '1'
								ORDER BY `course_code` ASC, `course_name` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							if ($ENTRADA_ACL->amIAllowed(new CourseResource($result["course_id"], $result["organisation_id"]), "read")) {
								$courses_list[$result["course_id"]] = ($result["course_code"]." - ".$result["course_name"]);
							}
						}
					}

					if ($evaluation_id) {
						$query = "	SELECT b.`course_id`, b.`organisation_id`, b.`course_name`
									FROM `evaluation_targets` AS a
									LEFT JOIN `courses` AS b
									ON b.`course_id` = a.`target_value`
									LEFT JOIN `evaluations_lu_targets` AS c
									ON c.`target_id` = a.`target_id`
									WHERE a.`evaluation_id` = ".$db->qstr($evaluation_id)."
									AND b.`course_active` = '1'
									AND c.`target_shortname` = 'course'
									ORDER BY b.`course_code` ASC, b.`course_name` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$courses[$result["course_id"]] = $courses_list[$result["course_id"]];
							}
						}

						$query = "	SELECT *
									FROM `evaluation_evaluators`
									WHERE `evaluation_id` = ".$db->qstr($evaluation_id);
						$results = $db->GetAll($query);
						if ($results) {
							$evaluator_type = $results[0]["evaluator_type"];

							foreach ($results as $result) {
								$evaluators[] = $result;
							}
						}
					}
					?>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="PickList" class="form-required">Select Courses</label>
							<div class="content-small"><strong>Hint:</strong> Select the course or courses you would like to have evaluated.</div>
						</td>
						<td style="vertical-align: top">
							<select class="multi-picklist" id="PickList" name="course_ids[]" multiple="multiple" size="4" style="width: 100%; margin-bottom: 5px">
							<?php
							if ((is_array($courses)) && (!empty($courses))) {
								foreach ($courses as $course_id => $course_name) {
									echo "<option value=\"".(int) $course_id."\">".html_encode($course_name)."</option>\n";
								}
							}
							?>
							</select>
							<div style="float: left; display: inline">
								<input type="button" id="courses_list_state_btn" class="button" value="Show List" onclick="toggle_list('courses_list')" />
							</div>
							<div style="float: right; display: inline">
								<input type="button" id="courses_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
								<input type="button" id="courses_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
							</div>
							<div id="courses_list" style="clear: both; padding-top: 3px; display: none">
								<h2>Course List</h2>
								<select class="multi-picklist" id="SelectList" name="other_event_objectives_list" multiple="multiple" size="15" style="width: 100%">
								<?php
								foreach ($courses_list as $course_id => $course_name) {
									if (!array_key_exists($course_id, $courses)) {
										echo "<option value=\"".(int) $course_id."\">".html_encode($course_name)."</option>\n";
									}
								}
								?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="PickList" class="form-required">Select Students</label>
							<div class="content-small"><strong>Hint:</strong> Select the student or students you would like to evaluate the courses above.</div>
						</td>
						<td style="vertical-align: top">
							<table style="width: 100%" cellspacing="0" cellpadding="0">
								<colgroup>
									<col style="width: 4%" />
									<col style="width: 21%" />
									<col style="width: 75%" />
								</colgroup>
								<tbody>
									<tr>
										<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_grad_year" value="grad_year" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
										<td colspan="2" style="padding-bottom: 15px">
											<label for="target_group_type_grad_year" class="radio-group-title">Entire class must complete this evaluation</label>
											<div class="content-small">This evaluation must be completed by everyone in the selected class.</div>
										</td>
									</tr>
									<tr class="target_group grad_year_audience">
										<td></td>
										<td><label for="associated_grad_year" class="form-required">Graduating Year</label></td>
										<td>
											<select id="associated_grad_year" name="associated_grad_year" style="width: 203px">
												<?php
												for($year = (date("Y", time()) + 4); $year >= (date("Y", time()) - 1); $year--) {
													echo "<option value=\"".(int) $year."\"".(($PROCESSED["associated_grad_year"] == $year) ? " selected=\"selected\"" : "").">Class of ".html_encode($year)."</option>\n";
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="3">&nbsp;</td>
									</tr>
									<tr >
										<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_proxy_id" value="proxy_id" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
										<td colspan="2" style="padding-bottom: 15px">
											<label for="target_group_type_proxy_id" class="radio-group-title">Selected students must complete this evaluation</label>
											<div class="content-small">This evaluation must be completed by the selected individuals.</div>
										</td>
									</tr>
									<tr class="target_group proxy_id_audience">
										<td></td>
										<td style="vertical-align: top; padding-top: 3px"><label for="associated_proxy_ids" class="form-required">Student Name</label></td>
										<td>
											<input type="text" id="student_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
											<div class="autocomplete" id="student_name_auto_complete"></div>

											<input type="hidden" id="associated_student" name="associated_student" />
											<input type="button" class="button-sm" id="add_associated_student" value="Add" style="vertical-align: middle" />
											<span class="content-small" style="margin-left: 3px; padding-top: 5px"><strong>e.g.</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?></span>
											<ul id="student_list" class="menu" style="margin-top: 15px">
												<?php
												if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
													foreach ($PROCESSED["associated_proxy_ids"] as $student) {
														if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
															?>
															<li class="community" id="student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="student_list.removeItem('<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
															<?php
														}
													}
												}
												?>
											</ul>
											<input type="hidden" id="student_ref" name="student_ref" value="" />
											<input type="hidden" id="student_id" name="student_id" value="" />
										</td>
									</tr>
								</tbody>
							</table>
							<script type="text/javascript">
							selectTargetGroupOption('grad_year');
							new AutoCompleteList({ type: 'student', url: '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=student', remove_image: '<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif' });
							</script>
						</td>
					</tr>
					<?php
				break;
				case "teacher" :
					?>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="" class="form-required">Select Teachers</label></td>
						<td style="vertical-align: top">
							Select the teachers you wish to evaluate.
							<script type="text/javascript">
							console.log("Your momma");
							</script>
						</td>
					</tr>
					<?php
				break;
				case "student" :
				case "rotation_core" :
				case "rotation_elective" :
				case "preceptor" :
				case "peer" :
				case "self" :
					?>
					<tr>
						<td colspan="2">&nbsp;</td>
						<td>
							<?php echo display_notice("The target that you have selected is not currently available."); ?>
						</td>
					</tr>
					<?php
				break;
				default :
					application_log("error", "Unaccounted for target_shortname [".$target_details["target_shortname"]."] encountered. An update to api-targets.inc.php is required.");
				break;
			}
		}
	}
	exit;
}