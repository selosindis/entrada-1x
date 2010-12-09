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
 * This file is used to edit existing events in the entrada.events table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer:  Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$EVALUATION_ID = 0;

	if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("trim", "int")))) {
		$EVALUATION_ID = $tmp_input;
	} elseif (isset($_POST["id"]) && ($tmp_input = clean_input($_POST["id"], array("trim", "int")))) {
		$EVALUATION_ID = $tmp_input;
	}

	if ($EVALUATION_ID) {
		$query = "	SELECT *
					FROM `evaluations`
					WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)."
					AND `evaluation_active` = '1'";
		$evaluation_info = $db->GetRow($query);
		if ($evaluation_info) {
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $EVALUATION_ID)), "title" => "Editing Evaluation");

			echo "<div class=\"no-printing\">\n";
			echo "	<div style=\"float: right; margin-top: 8px\">\n";
			echo "		<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "evaluation" => $EVALUATION_ID))."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage evaluation content\" title=\"Manage evaluation content\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "evaluation" => $EVALUATION_ID, "step" => false))."\" style=\"font-size: 10px; margin-right: 8px\">Manage evaluation content</a>\n";
			echo "	</div>\n";
			echo "</div>\n";

			echo "<h1>Edit Evaluation</h1>\n";

			// Error Checking
			switch($STEP) {
				case 2 :
					/**
					 * Required field "eform_id" / Evaluation Form
					 */
					if (isset($_POST["eform_id"]) && ($eform_id = clean_input($_POST["eform_id"], "int"))) {
						$query = "SELECT * FROM `evaluation_forms` WHERE `eform_id` = ".$db->qstr($eform_id)." AND `form_active` = '1'";
						$result = $db->GetRow($query);
						if ($result) {
							$PROCESSED["eform_id"] = $eform_id;
						} else {
							add_error("The <strong>Evaluation Form</strong> that you selected is not currently available for use.");
						}
					} else {
						add_error("You must select an <strong>Evaluation Form</strong> to use during this evaluation.");
					}
					
					/**
					 * Required field "evaluation_title" / Evaluation Title.
					 */
					if ((isset($_POST["evaluation_title"])) && ($evaluation_title = clean_input($_POST["evaluation_title"], array("notags", "trim")))) {
						$PROCESSED["evaluation_title"] = $evaluation_title;
					} else {
						add_error("The <strong>Evaluation Title</strong> field is required.");
					}

					/**
					 * Non-required field "evaluation_description" / Special Instructions.
					 */
					if ((isset($_POST["evaluation_description"])) && ($evaluation_description = clean_input($_POST["evaluation_description"], array("notags", "trim")))) {
						$PROCESSED["evaluation_description"] = $evaluation_description;
					} else {
						$PROCESSED["evaluation_description"] = "";
					}

					/**
					 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
					 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
					 */
					$viewable_date = validate_calendars("evaluation", false, false);
					if ((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
						$PROCESSED["evaluation_start"] = (int) $viewable_date["start"];
					} else {
						$PROCESSED["evaluation_start"] = 0;
					}
					if ((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
						$PROCESSED["evaluation_finish"] = (int) $viewable_date["finish"];
					} else {
						$PROCESSED["evaluation_finish"] = 0;
					}

					/**
					 * Required field "min_submittable" / Min Submittable
					 */
					if (isset($_POST["min_submittable"]) && ($min_submittable = clean_input($_POST["min_submittable"], "int")) && ($min_submittable >= 1)) {
						$PROCESSED["min_submittable"] = $min_submittable;
					} else {
						add_error("The evaluation <strong>Min Submittable</strong> field is required and must be greater than 1.");
					}

					/**
					 * Required field "max_submittable" / Max Submittable
					 */
					if (isset($_POST["max_submittable"]) && ($max_submittable = clean_input($_POST["max_submittable"], "int")) && ($max_submittable <= 99)) {
						$PROCESSED["max_submittable"] = $max_submittable;
					} else {
						add_error("The evaluation <strong>Max Submittable</strong> field is required and must be less than 99.");
					}

					if ($PROCESSED["min_submittable"] > $PROCESSED["max_submittable"]) {
						add_error("Your <strong>Min Submittable</strong> value may not be greater than your <strong>Max Submittable</strong> value.");
					}

					/**
					 * Required field "max_submittable" / Max Submittable
					 */
					if (isset($_POST["max_submittable"]) && ($max_submittable = clean_input($_POST["max_submittable"], "int")) && ($max_submittable <= 99)) {
						$PROCESSED["max_submittable"] = $max_submittable;
					} else {
						add_error("The evaluation <strong>Max Submittable</strong> field is required and must be less than 99.");
					}

					/**
					 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
					 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
					 */
					$viewable_date = validate_calendars("viewable", false, false);
					if ((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
						$PROCESSED["release_date"] = (int) $viewable_date["start"];
					} else {
						$PROCESSED["release_date"] = 0;
					}
					if ((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
						$PROCESSED["release_until"] = (int) $viewable_date["finish"];
					} else {
						$PROCESSED["release_until"] = 0;
					}

					if (isset($_POST["post_action"])) {
						switch($_POST["post_action"]) {
							case "content" :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
							break;
							case "new" :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
							break;
							case "index" :
							default :
								$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
							break;
						}
					} else {
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
					}

					if (!$ERROR) {
						$PROCESSED["updated_date"] = time();
						$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

						if ($db->AutoExecute("evaluations", $PROCESSED, "UPDATE", "`evaluation_id` = ".$db->qstr($EVALUATION_ID))) {
							switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
								case "content" :
									$url = ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID;
									$msg = "You will now be redirected to the evaluation content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "new" :
									$url = ENTRADA_URL."/admin/evaluations?section=add";
									$msg = "You will now be redirected to add another new evaluation; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "index" :
								default :
									$url = ENTRADA_URL."/admin/evaluations";
									$msg = "You will now be redirected to the evaluation index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
							}

							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
							add_success("You have successfully added <strong>".html_encode($PROCESSED["evaluation_title"])."</strong> to the system.<br /><br />".$msg);

							application_log("success", "New evaluation [".$EVALUATION_ID."] added to the system.");
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $evaluation_info;
				break;
			}

			// Display Content
			switch($STEP) {
				case 2 :
					display_status_messages();
				break;
				case 1 :
				default :
					if (has_error() || has_notice()) {
						echo display_status_messages();
					}
					?>
					<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=edit&amp;id=<?php echo $EVALUATION_ID; ?>&amp;step=2" method="post" name="editEvaluationForm" id="editEvaluationForm">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing an Evaluation">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 20%" />
								<col style="width: 77%" />
							</colgroup>
							<tfoot>
								<tr>
									<td colspan="3" style="padding-top: 50px">
										<input type="button" class="fleft" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/evaluations'" />
										<input type="submit" class="fright" value="Save" />
										<div class="clear"></div>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td colspan="3"><h2>Evaluation Details</h2></td>
								</tr>
								<tr>
									<td></td>
									<td><label for="eform_id" class="form-required">Evaluation Form</label></td>
									<td>
										<select id="eform_id" name="eform_id" style="width:205px">
										<?php
										$query	= "	SELECT a.*, b.`target_shortname`, b.`target_title`
													FROM `evaluation_forms` AS a
													LEFT JOIN `evaluations_lu_targets` AS b
													ON b.`target_id` = a.`target_id`
													WHERE a.`form_active` = '1'
													ORDER BY b.`target_title` ASC";
										$results = $db->GetAll($query);
										if ($results) {
											$total_forms = count($results);
											$optgroup_label = "";

											foreach ($results as $key => $result) {
												if ($result["target_title"] != $optgroup_label) {
													$optgroup_label = $result["target_title"];
													if ($key > 0) {
														echo "</optgroup>";
													}
													echo "<optgroup label=\"".html_encode($optgroup_label)." Forms\">";
												}
												echo "<option value=\"".(int) $result["eform_id"].(($PROCESSED["eform_id"] == $result["eform_id"]) ? " selected=\"selected\"" : "")."\"> ".html_encode($result["form_title"])."</option>";
											}
											echo "</optgroup>";
										}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="evaluation_title" class="form-required">Evaluation Title</label></td>
									<td><input type="text" id="evaluation_title" name="evaluation_title" value="<?php echo html_encode($PROCESSED["evaluation_title"]); ?>" maxlength="255" style="width: 95%" /></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td style="vertical-align: top">
										<label for="evaluation_description" class="form-nrequired">Special Instructions</label>
										<div class="content-small" style="margin-right:3px"><strong>Note:</strong> Special instructions will appear at the top of the evaluation form.</div>
									</td>
									<td>
										<textarea id="evaluation_description" name="evaluation_description" class="expandable" style="width: 94%; height:50px"><?php echo html_encode($PROCESSED["evaluation_description"]); ?></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<?php echo generate_calendars("evaluation", "Evaluation", true, true, ((isset($PROCESSED["evaluation_start"])) ? $PROCESSED["evaluation_start"] : 0), true, true, ((isset($PROCESSED["evaluation_finish"])) ? $PROCESSED["evaluation_finish"] : 0)); ?>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="min_submittable" class="form-required">Min Submittable</label></td>
									<td>
										<input type="text" id="min_submittable" name="min_submittable" value="<?php echo (isset($PROCESSED["min_submittable"]) ? $PROCESSED["min_submittable"] : 1); ?>" maxlength="2" style="width: 30px; margin-right: 10px" />
										<span class="content-small"><strong>Tip:</strong> The minimum number of times an evaluator must complete this evaluation.</span>
									</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="max_submittable" class="form-required">Max Submittable</label></td>
									<td>
										<input type="text" id="max_submittable" name="max_submittable" value="<?php echo (isset($PROCESSED["max_submittable"]) ? $PROCESSED["max_submittable"] : 1); ?>" maxlength="2" style="width: 30px; margin-right: 10px" />
										<span class="content-small"><strong>Tip:</strong> The maximum number of times evaluator is able complete this evaluation.</span>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3"><h2>Time Release Options</h2></td>
								</tr>
								<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
							</tbody>
						</table>
					</form>
					<?php
				break;
			}

		} else {
			add_error("In order to edit a evaluation you must provide a valid evaluation identifier. The provided ID does not exist in this system.");

			echo display_error();

			application_log("notice", "Failed to provide a valid evaluation identifer when attempting to edit an evaluation.");
		}
	} else {
		add_error("In order to edit an evaluation you must provide the evaluation identifier.");

		echo display_error();

		application_log("notice", "Failed to provide an evaluation identifer when attempting to edit an evaluation.");
	}
}