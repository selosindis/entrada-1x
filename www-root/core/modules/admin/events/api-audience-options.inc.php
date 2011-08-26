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
 * This API file returns an HTML table of the possible audience information
 * based on the selected course.
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
	if (isset($_POST["ajax"]) && ($_POST["ajax"] == 1)) {
		$use_ajax = true;
	} else {
		$use_ajax = false;
	}

	if ($use_ajax) {
		/**
		 * Clears all open buffers so we can return a plain response for the Javascript.
		 */
		ob_clear_open_buffers();

		$PROCESSED = array();
		$PROCESSED["course_id"] = 0;

		if (isset($_POST["course_id"]) && ($tmp_input = clean_input($_POST["course_id"], "int"))) {
			$PROCESSED["course_id"] = $tmp_input;
		}
	}
	
	if ($PROCESSED["course_id"]) {
		$query = "SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($PROCESSED["course_id"]);
		$course_info = $db->GetRow($query);
		if ($course_info) {
			$permission = $course_info["permission"];
			if ($permission == "open") {
				$query = "SELECT * FROM `groups` WHERE `group_type` = 'course_list' AND `group_value` = ".$db->qstr($PROCESSED["course_id"]);
				$course_list = $db->GetRow($query);
				?>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Associated Learners</label></td>
					<td>
						<table>
							<tbody>
								<?php
								if ($course_list) {
									?>
									<tr>
										<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_course" value="course" onclick="selectEventAudienceOption('course')" style="vertical-align: middle"<?php echo ((($PROCESSED["event_audience_type"] == "course") || !isset($PROCESSED["event_audience_type"])) ? " checked=\"checked\"" : ""); ?> /></td>
										<td colspan="2" style="padding-bottom: 15px">
											<label for="event_audience_type_course" class="radio-group-title">All Learners Enrolled in <?php echo html_encode($course_info["course_code"]); ?></label>
											<div class="content-small">This event is intended for all learners enrolled in the course.</div>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_custom" value="custom" onclick="selectEventAudienceOption('custom')" style="vertical-align: middle"<?php echo ((($PROCESSED["event_audience_type"] == "custom") || (!$course_list)) ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2" style="padding-bottom: 15px">
										<label for="event_audience_type_custom" class="radio-group-title">A Custom Event Audience</label>
										<div class="content-small">This event is intended for a custom selection of learners.</div>

										<div id="event_audience_type_custom_options" style="<?php echo ($course_list ? "display: none; " : ""); ?>position: relative; margin-top: 10px;">
											<select id="audience_type" onchange="showMultiSelect();" style="width: 275px;">
												<option value="">-- Select an audience type --</option>
												<option value="cohorts">Cohorts of learners</option>
												<option value="course_groups">Course specific small groups</option>
												<option value="students">Individual learners</option>
											</select>

											<span id="options_loading" style="display:none; vertical-align: middle"><img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please Wait" title="" style="vertical-align: middle" /> Loading ... </span>
											<span id="options_container"></span>

											<input type="hidden" id="multifilter" name="filter" value="" />
											<ul class="menu multiselect" id="audience_list" style="margin-top: 5px"></ul>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<?php
			}
		}
	}

	/**
	 * If we are return this via Javascript,
	 * exit now so we don't get the entire page.
	 */
	if ($use_ajax) {
		exit;
	}
}