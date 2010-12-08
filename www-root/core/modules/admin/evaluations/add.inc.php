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
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
//echo "______log______".time()."<br>";
//echo "______log______STEP: ".$STEP."<br>";
//echo "______log______ACTION_TYPE: ".$ACTION_TYPE."<br>";
print_r($_POST["ids"]);
if((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('evaluations', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "add")), "title" => "Adding Evaluation");

	$PROCESSED["associated_faculty"]	= array();
	$PROCESSED["event_audience_type"]	= "grad_year";
	$PROCESSED["associated_grad_year"]	= "";
	$PROCESSED["associated_group_ids"]	= array();
	$PROCESSED["associated_proxy_ids"]	= array();
	$PROCESSED["event_types"]			= array();


        if((isset($_GET["type"])) && ($tmp_action_type = clean_input(trim($_GET["type"]), "alphanumeric"))) {
                $ACTION_TYPE	= $tmp_action_type;
        } elseif((isset($_POST["type"])) && ($tmp_action_type = clean_input(trim($_POST["type"]), "alphanumeric"))) {
                $ACTION_TYPE	= $tmp_action_type;
        }
        unset($tmp_action_type);


	echo "<h1>Adding Evaluation</h1>\n";

        //Added by Howard


	// Error Checking
	switch($STEP) {
		case 2 :
		/**
		 * Required field "evaluation_title" / Evaluation Title.
		 */
			if((isset($_POST["evaluation_title"])) && ($evaluation_title = clean_input($_POST["evaluation_title"], array("notags", "trim")))) {
				$PROCESSED["evaluation_title"] = $evaluation_title;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Evaluation Title</strong> field is required.";
			}
			if((isset($_POST["evaluation_description"])) && ($evaluation_description = clean_input($_POST["evaluation_description"], array("notags", "trim")))) {
				$PROCESSED["evaluation_description"] = $evaluation_description;
			}

			/**
                        if((isset($_POST["evaluation_active"])) && ($evaluation_active = clean_input($_POST["evaluation_active"], array("notags", "trim")))) {
				$PROCESSED["evaluation_active"] = $evaluation_active;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Evaluation Active</strong> field is required.";
			}
                         *
                         */
			if((isset($_POST["min_submittable"])) && ($min_submittable = clean_input($_POST["min_submittable"], array("notags", "trim")))) {
				$PROCESSED["min_submittable"] = $min_submittable;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Evaluation Min Submittable</strong> field is required.";
			}
			if((isset($_POST["max_submittable"])) && ($max_submittable = clean_input($_POST["max_submittable"], array("notags", "trim")))) {
				$PROCESSED["max_submittable"] = $max_submittable;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Evaluation Max Submittable</strong> field is required.";
			}

			/**
			 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
			 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
			 */
			$viewable_date = validate_calendars("evaluation", false, false);
			if((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
				$PROCESSED["evaluation_start"] = (int) $viewable_date["start"];
			} else {
				$PROCESSED["evaluation_start"] = 0;
			}
			if((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
				$PROCESSED["evaluation_finish"] = (int) $viewable_date["finish"];
			} else {
				$PROCESSED["evaluation_finish"] = 0;
			}
                        //echo "______log______evaluation_start: ".$PROCESSED["evaluation_start"]."<br>";
                        //echo "______log______evaluation_finish: ".$PROCESSED["evaluation_finish"]."<br>";


			/**
			 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
			 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
			 */
			$viewable_date = validate_calendars("viewable", false, false);
			if((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
				$PROCESSED["release_date"] = (int) $viewable_date["start"];
			} else {
				$PROCESSED["release_date"] = 0;
			}
			if((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
				$PROCESSED["release_until"] = (int) $viewable_date["finish"];
			} else {
				$PROCESSED["release_until"] = 0;
			}

			if(isset($_POST["post_action"])) {
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

			if(!$ERROR) {
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

				$PROCESSED["event_duration"] = 0;
				foreach($PROCESSED["event_types"] as $event_type) {
					$PROCESSED["event_finish"] += $event_type[1]*60;
					$PROCESSED["event_duration"] += $event_type[1];
				}
				$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0][0];
				//if($db->AutoExecute("evaluations", array("evaluation_title" => $_POST["evaluation_title"], "evaluation_description" => $_POST["evaluation_description"], "evaluation_active" => $PROCESSED["evaluation_active"], "evaluation_start" => $PROCESSED["evaluation_start"], "evaluation_finish" => $PROCESSED["evaluation_finish"], "min_submittable" => $_POST["min_submittable"], "max_submittable" => $_POST["max_submittable"], "release_date" => $PROCESSED["release_date"], "release_until" => $PROCESSED["release_until"]), "INSERT")) {
                                if($db->AutoExecute("evaluations", array("evaluation_title" => $_POST["evaluation_title"], "evaluation_description" => $_POST["evaluation_description"], "evaluation_active" => 1, "evaluation_start" => $PROCESSED["evaluation_start"], "evaluation_finish" => $PROCESSED["evaluation_finish"], "min_submittable" => $_POST["min_submittable"], "max_submittable" => $_POST["max_submittable"]), "INSERT")) {
                                                if($EVALUATION_ID = $db->Insert_Id()){
						switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
							case "content" :
								$url	= ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID;
								$msg	= "You will now be redirected to the evaluation content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
							case "new" :
								$url	= ENTRADA_URL."/admin/evaluations?section=add";
								$msg	= "You will now be redirected to add another new evaluation; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
							case "index" :
							default :
								$url	= ENTRADA_URL."/admin/evaluations";
								$msg	= "You will now be redirected to the evaluation index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
						}

                                                $SUCCESS++;
						$SUCCESSSTR[]	= "You have successfully added <strong>".html_encode($PROCESSED["evaluation_title"])."</strong> to the system.<br /><br />".$msg;
						$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
                                                application_log("success", "New evaluation [".$EVALUATION_ID."] added to the system.");
                                                }
                                        } else {
								$ERROR++;
								$ERRORSTR[] = "There was an error while trying to save this evaluation.<br /><br />The system administrator was informed of this error; please try again later.";

								application_log("error", "Unable to insert a new evaluation record while adding a new evaluation. Database said: ".$db->ErrorMsg());
							}
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this evaluation into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a evaluation. Database said: ".$db->ErrorMsg());
				}

			if($ERROR) {
				$STEP = 1;
			}
			break;
		case 1 :
		default :
                        if((isset($_POST["evaluation_title"])) && ($evaluation_title = clean_input($_POST["evaluation_title"], array("notags", "trim")))) {
				$PROCESSED["evaluation_title"] = $evaluation_title;
			}
			continue;
			break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
			break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$ONLOAD[]	= "selectEventAudienceOption('".$PROCESSED["event_audience_type"]."')";

			/**
			 * Compiles the full list of faculty members.
			 */
			$FACULTY_LIST	= array();
			$query			= "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON b.`user_id` = a.`id`
								WHERE b.`app_id` = '".AUTH_APP_ID."'
								AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
								ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$results		= $db->GetAll($query);
			if($results) {
				foreach($results as $result) {
					$FACULTY_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
				}
			}

			/**
			 * Compiles the list of students.
			 */
			$STUDENT_LIST	= array();
			$query			= "
							SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
							FROM `".AUTH_DATABASE."`.`user_data` AS a
							LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
							ON a.`id` = b.`user_id`
							WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
							AND b.`account_active` = 'true'
							AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
							AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
							AND b.`group` = 'student'
							AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
										ORDER BY b.`role` ASC, a.`lastname` ASC, a.`firstname` ASC";
			$results		= $db->GetAll($query);
			if($results) {
				foreach($results as $result) {
					$STUDENT_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
				}
			}

			if($ERROR) {
				echo display_error();
			}

			$query					= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
			$organisation_results	= $db->GetAll($query);
			if ($organisation_results) {
				$organisations = array();
				foreach ($organisation_results as $result) {
					if ($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'create')) {
						$organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
					}
				}
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=add&amp;step=2" method="post" name="addEvaluationForm" id="addEvaluationForm">
                                    <table cellspacing="0" cellpadding="0" border="0">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tr>
						<td colspan="3"><h2>Evaluation Details</h2></td>
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
						<td><label for="evaluation_description" class="form-nrequired">Description</label></td>
						<td><input type="text" id="evaluation_description" name="evaluation_description" value="<?php echo html_encode($PROCESSED["evaluation_description"]); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php echo generate_calendars("evaluation", "Evaluation", true, true, ((isset($PROCESSED["evaluation_start"])) ? $PROCESSED["evaluation_start"] : 0), true, true, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>

					<?php //echo generate_calendars("evaluation", "Evaluation Date & Time", true, true, ((isset($PROCESSED["evaluation_end"])) ? $PROCESSED["evaluation_end"] : 0)); ?>
					<tr>
						<td></td>
						<td><label for="min_submittable" class="form-required">Min Submittable</label></td>
						<td><input type="text" id="min_submittable" name="min_submittable" value="1" maxlength="25" style="width: 40px" />&nbsp;&nbsp;&nbsp;(Minimum number of times evaluator must complete the evaluation)</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td><label for="max_submittable" class="form-required">Max Submittable</label></td>
						<td><input type="text" id="max_submittable" name="max_submittable" value="1" maxlength="25" style="width: 40px" />&nbsp;&nbsp;&nbsp;(Maximum number of times evaluator must complete the evaluation)</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="eform_id" class="form-required">Evaluation Form</label></td>
						<td>
							<select id="eform_id" name="eform_id">
								<option id="-1"> -- Pick a type to add -- </option>
								<?php
								$query		= "SELECT * FROM `evaluation_forms` WHERE `form_active` = '1' ORDER BY `updated_date` ASC";
								$results	= $db->GetAll($query);
								if($results) {
									foreach($results as $result) {
										$title = html_encode($result["form_title"]);
										$eform_id = html_encode($result["eform_id"]);
                                                                                //echo $eform_id."--";
										echo "<option value=\"".$result["eform_id"].(($PROCESSED["eform_id"] == $result["eform_id"]) ? " selected=\"selected\"" : "")."\"> ".$title."</option>";
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
                                <div>
					<tr>
						<td colspan="3"><h2>Time Release Options</h2></td>
					</tr>
					<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
					<tr>
						<td colspan="3" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/events'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<span class="content-small">After saving:</span>
										<select id="post_action" name="post_action">
											<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to evaluation</option>
											<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another evaluation</option>
											<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to evaluation list</option>
										</select>
										<input type="submit" class="button" value="Save" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
                                </div>
				</table>
			</form>
				<?php //eval_sche_evaluators_filter_controls("admin"); ?>
			<script type="text/javascript">
                                function search_grad_year(){
                                    alert("Fly....");
                                    //document.addEvaluationForm.action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=add";
                                    //document.addEvaluationForm.submit();
                                }

				function selectEventAudienceOption(type) {
					$$('.event_audience').invoke('hide');
					$$('.'+type+'_audience').invoke('show');
				}
			</script>
			<br /><br />
			<?php
		break;
	}
}
?>
