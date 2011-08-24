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
		?>
		<table style="width: 100%" cellspacing="0" cellpadding="0">
			<colgroup>
				<col style="width: 4%" />
				<col style="width: 96%" />
			</colgroup>
			<tbody>
			<?php
			$query = "	SELECT b.*
						FROM `courses` AS a
						JOIN `groups` AS b
						ON b.`group_type` = 'course_list'
						AND b.`group_value` = ".$db->qstr($PROCESSED["course_id"])."
						WHERE b.`group_active` = '1'
						AND a.`course_active` = '1'
						AND a.`course_id` = ".$db->qstr($PROCESSED["course_id"]);
			$target_details = $db->GetRow($query);
			if ($target_details) {
				?>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="audience_group_type" id="audience_group_type_enrollment" value="enrollment" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
					<td style="padding-bottom: 15px">
						<label for="target_group_type_enrolled" class="radio-group-title">Defined course enrollment list</label>
						<div class="content-small">This event is for all students actively registered in this course.</div>
					</td>
				</tr>
				<?php
			}
			?>
				<tr >
					<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_proxy_id" value="proxy_id" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
					<td style="padding-bottom: 15px">
						<label for="target_group_type_proxy_id" class="radio-group-title">Selected students must complete this evaluation</label>
						<div class="content-small">This evaluation must be completed only by the selected individuals.</div>
					</td>
				</tr>
				<tr class="target_group proxy_id_audience">
					<td>&nbsp;</td>
					<td style="vertical-align: middle" class="content-small">
						<label for="student_name" class="form-required">Student Name</label>

						<input type="text" id="student_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<div class="autocomplete" id="student_name_auto_complete"></div>

						<input type="hidden" id="associated_student" name="associated_student" />
						<input type="button" class="button-sm" id="add_associated_student" value="Add" style="vertical-align: middle" />
						<span class="content-small" style="margin-left: 3px; padding-top: 5px"><strong>e.g.</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?></span>
						<ul id="student_list" class="menu" style="margin-top: 15px">
							<?php
							if (($PROCESSED["evaluation_evaluators"][0]["evaluator_type"] == "proxy_id") && is_array($PROCESSED["evaluation_evaluators"]) && !empty($PROCESSED["evaluation_evaluators"])) {
								foreach ($PROCESSED["evaluation_evaluators"] as $evaluator) {
									$proxy_id = (int) $evaluator["evaluator_value"];
									?>
									<li class="community" id="student_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo get_account_data("fullname", $proxy_id); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="student_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
									<?php
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
		<script type="text/javascript" defer="defer">
		selectTargetGroupOption('<?php echo (isset($PROCESSED["evaluation_evaluators"][0]["evaluator_type"]) ? $PROCESSED["evaluation_evaluators"][0]["evaluator_type"] : 'cohort'); ?>');
		student_list = new AutoCompleteList({ type: 'student', url: '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=student', remove_image: '<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif' });
		</script>
		<?php
	}

	/**
	 * If we are return this via Javascript,
	 * exit now so we don't get the entire page.
	 */
	if ($use_ajax) {
		exit;
	}
}