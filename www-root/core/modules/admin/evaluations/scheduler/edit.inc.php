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
 * This file is used to edit existing events in the entrada.events table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer:  Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/


if((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('event', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

        if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
                $EVALUATION_ID	= (int) trim($_GET["id"]);
        } elseif((isset($_POST["id"])) && ((int) trim($_POST["id"]))) {
                $EVALUATION_ID	= (int) trim($_POST["id"]);
        }

	if($EVALUATION_ID) {
		$query		= "	SELECT * FROM `evaluations`
						WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
		$evaluation_info	= $db->GetRow($query);
		if($evaluation_info) {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/scheduler?".replace_query(array("section" => "edit", "id" => $EVALUATION_ID)), "title" => "Editing Evaluation");

				$PROCESSED["associated_faculty"]	= array();
				$PROCESSED["event_audience_type"]	= "grad_year";
				$PROCESSED["associated_grad_year"]	= "";
				$PROCESSED["associated_group_ids"]	= array();
				$PROCESSED["associated_proxy_ids"]	= array();
				$PROCESSED["event_types"]			= array();

				echo "<div class=\"no-printing\">\n";
				echo "	<div style=\"float: right; margin-top: 8px\">\n";
				echo "		<a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?".replace_query(array("section" => "members", "id" => $EVALUATION_ID))."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage evaluation content\" title=\"Manage evaluation content\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?".replace_query(array("section" => "members", "id" => $EVALUATION_ID, "step" => false))."\" style=\"font-size: 10px; margin-right: 8px\">Manage evaluation content</a>\n";
				echo "	</div>\n";
				echo "</div>\n";

				echo "<h1>Editing Evaluation</h1>\n";

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

                                                if((isset($_POST["evaluation_active"])) && ($evaluation_active = clean_input($_POST["evaluation_active"], array("notags", "trim")))) {
                                                        $PROCESSED["evaluation_active"] = $evaluation_active;
                                                } else {
                                                        $ERROR++;
                                                        $ERRORSTR[] = "The <strong>Evaluation Active</strong> field is required.";
                                                }
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
                                                                case "member" :
                                                                        $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "member";
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
                                                        $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "member";
                                                }

						if(!$ERROR) {
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

							$PROCESSED["event_finish"] = $PROCESSED["event_start"];
							$PROCESSED["event_duration"] = 0;
							foreach($PROCESSED["event_types"] as $event_type) {
								$PROCESSED["event_finish"] += $event_type[1]*60;
								$PROCESSED["event_duration"] += $event_type[1];
							}

							$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0][0];

							if($db->AutoExecute("events", $PROCESSED, "UPDATE", "`evaluation_id` = ".$db->qstr($EVALUATION_ID))) {
                                                                    switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "member" :
										$url	= ENTRADA_URL."/admin/evaluations/scheduler?section=members&id=".$EVALUATION_ID;
										$msg	= "You will now be redirected to the evaluation content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
									case "new" :
										$url	= ENTRADA_URL."/admin/evaluations/scheduler?section=add";
										$msg	= "You will now be redirected to add a new evaluation; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
									case "index" :
									default :
										$url	= ENTRADA_URL."/admin/evaluations/scheduler";
										$msg	= "You will now be redirected to the evaluation index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
										break;
                                                                        }
								
                                                                        $SUCCESS++;
                                                                        $SUCCESSSTR[]	= "You have successfully edited <strong>".html_encode($PROCESSED["evaluation_title"])."</strong> in the system.<br /><br />".$msg;
                                                                        $ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

                                                                        application_log("success", "Event [".$EVALUATION_ID."] has been modified.");

                                                            } else {
                                                                    $ERROR++;
                                                                    $ERRORSTR[] = "There was a problem updating this evaluation in the system. The system administrator was informed of this error; please try again later.";

                                                                    application_log("error", "There was an error updating evaluation_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
                                                            }
						}

						if($ERROR) {
							$STEP = 1;
						}
						break;
					case 1 :
					default :
						$PROCESSED	= $evaluation_info;

						/**
						 * Add existing event type segments to the processed array.
						 */
						$query		= "	SELECT *
										FROM `event_eventtypes` AS `types`
										LEFT JOIN `events_lu_eventtypes` AS `lu_types`
										ON `lu_types`.`eventtype_id` = `types`.`eventtype_id`
										WHERE `event_id` = ".$db->qstr($EVALUATION_ID)."
										ORDER BY `types`.`eeventtype_id` ASC";
						$results	= $db->GetAll($query);
						if ($results) {
							foreach ($results as $contact_order => $result) {
								$PROCESSED["event_types"][] = array($result["eventtype_id"], $result["duration"], $result["eventtype_title"]);
							}
						}

						/**
						 * Add any existing associated faculty from the event_contacts table
						 * into the $PROCESSED["associated_faculty"] array.
						 */
						$query		= "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($EVALUATION_ID)." ORDER BY `contact_order` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $contact_order => $result) {
								$PROCESSED["associated_faculty"][(int) $contact_order] = $result["proxy_id"];
							}
						}

						$query		= "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVALUATION_ID);
						$results	= $db->GetAll($query);
						if($results) {
						/**
						 * Set the audience_type.
						 */
							$PROCESSED["event_audience_type"] = $results[0]["audience_type"];

							foreach($results as $result) {
								if($result["audience_type"] == $PROCESSED["event_audience_type"]) {
									switch($result["audience_type"]) {
										case "grad_year" :
											$PROCESSED["associated_grad_year"]		= (int) $result["audience_value"];
											break;
										case "group_id" :
											$PROCESSED["associated_group_ids"][]	= (int) $result["audience_value"];
											break;
										case "proxy_id" :
											$PROCESSED["associated_proxy_ids"][]	= (int) $result["audience_value"];
											break;
										case "organisation_id" :
											$PROCESSED["associated_organisation_id"]		= (int) $result["audience_value"];
											break;

									}
								}
							}
						}
						break;
				}

				// Display Content
				switch($STEP) {
					case 2 :
						if($SUCCESS) {
							echo display_success();
						}
						if($NOTICE) {
							echo display_notice();
						}
						if($ERROR) {
							echo display_error();
						}
					break;
					case 1 :
					default :
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
						
						$LASTUPDATED	= $result["updated_date"];

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
						<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/scheduler?<?php echo replace_query(array("step" => 2)); ?>" method="post" name="editEvaluationForm">
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Evaluation">
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
						<td><label for="evaluation_description" class="form-nrequired">Evaluation Description</label></td>
						<td><input type="text" id="evaluation_description" name="evaluation_description" value="<?php echo html_encode($PROCESSED["evaluation_description"]); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td><label for="evaluation_active" class="form-required">Evaluation Active</label></td>
						<td><input type="text" id="evaluation_active" name="evaluation_active" value="<?php echo html_encode($PROCESSED["evaluation_active"]); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php echo generate_calendars("evaluation", "Evaluation Date & Time", true, true, ((isset($PROCESSED["evaluation_start"])) ? $PROCESSED["evaluation_start"] : 0), true, true, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>

					<?php //echo generate_calendars("evaluation", "Evaluation Date & Time", true, true, ((isset($PROCESSED["evaluation_end"])) ? $PROCESSED["evaluation_end"] : 0)); ?>
					<tr>
						<td></td>
						<td><label for="min_submittable" class="form-required">Min Submittable</label></td>
						<td><input type="text" id="min_submittable" name="min_submittable" value="<?php echo $PROCESSED["min_submittable"]; ?>" maxlength="255" style="width: 203px" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td><label for="max_submittable" class="form-required">Max Submittable</label></td>
						<td><input type="text" id="max_submittable" name="max_submittable" value="<?php echo $PROCESSED["max_submittable"]; ?>" maxlength="255" style="width: 203px" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Evaluation Form</label></td>
						<td>
							<select id="eventtype_ids" name="eventtype_ids">
								<option id="-1"> -- Pick a type to add -- </option>
								<?php
								$query		= "SELECT * FROM `events_lu_eventtypes` WHERE `eventtype_active` = '1' ORDER BY `eventtype_order` ASC";
								$results	= $db->GetAll($query);
								if($results) {
									$event_types = array();
									foreach($results as $result) {
										$title = html_encode($result["eventtype_title"]);
										echo "<option value=\"".$result["eventtype_id"]."\">".$title."</option>";
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
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
														<option value="member"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "member")) ? " selected=\"selected\"" : ""); ?>>Add content to evaluation</option>
														<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another evaluation</option>
														<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to evaluation list</option>
													</select>
													<input type="submit" class="button" value="Save" />
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</form>
						<br /><br />
						<?php
					break;
				}
		} else {
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

			$ERROR++;
			$ERRORSTR[] = "In order to edit a evaluation you must provide a valid evaluation identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid evaluation identifer when attempting to edit a evaluation.");
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[] = "In order to edit a evaluation you must provide the evaluation identifier.";

		echo display_error();

		application_log("notice", "Failed to provide evaluation identifer when attempting to edit a evaluation.");
	}
}
?>
