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
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "create", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\">var SITE_URL = '".ENTRADA_URL."';</script>";
	$HEAD[] = "<script type=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/objectives_assessment.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	if (isset($_POST["mode"]) && $_POST["mode"] == "ajax") {

		ob_clear_open_buffers();

		$method = clean_input($_POST["method"], array("trim", "striptags"));

		switch ($method) {
			case "fetch-extended-options" :

				$current_type_id = clean_input($_POST["type"], "int");

				$query = "SELECT `type` FROM `assessments_lu_meta` WHERE `id` = ".$db->qstr($current_type_id);
				$current_type = $db->GetOne($query);

				if (in_array($current_type, array("reflection", "paper", "project"))) {
					$where = " WHERE `type` LIKE ".$db->qstr("%".$current_type."%");
				} else if (in_array($current_type, array("exam", "quiz"))) {
					$where = " WHERE `type` IS NULL ";
				} else {
					$where = " WHERE 'x' = 'y'";
				}
				$query = "SELECT `id`, `title` FROM `assessments_lu_meta_options`" . $where;
				$assessment_options = $db->GetAll($query);
				if ($assessment_options) {
					foreach ($assessment_options as $assessment_option) {
						echo "<label class=\"checkbox\" for=\"extended_option" . $assessment_option["id"] . "\"><input type=\"checkbox\" value=\"" . $assessment_option["id"] . "\" name=\"option[]\" id=\"extended_option" . $assessment_option["id"] . "\"/>" . $assessment_option["title"] . "</label>";
					}
				}

			break;
			default:
			continue;
		}

		exit;

	}

	if ($COURSE_ID) {

		$event = false;

		$query = "	SELECT * FROM `courses`
					WHERE `course_id` = ".$db->qstr($COURSE_ID)."
					AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);

		$m_query = "	SELECT * FROM `assessment_marking_schemes`
						WHERE `enabled` = 1;";
		$MARKING_SCHEMES = $db->GetAll($m_query);

		$assessment_options_query = "SELECT `id`, `title`, `active`
									 FROM `assessments_lu_meta_options`
									 WHERE `active` = '1'";
		$assessment_options = $db->GetAll($assessment_options_query);
		if ($course_details && $MARKING_SCHEMES && $ENTRADA_ACL->amIAllowed(new GradebookResource($course_details["course_id"], $course_details["organisation_id"]), "update")) {
			function return_id($arr) {
				return $arr["id"];
			}

			$MARKING_SCHEME_IDS = array_map("return_id", $MARKING_SCHEMES);
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), "title" => "Add Assessment");

			// Error Checking
			switch($STEP) {
				case 2 :
					$posted_objectives = array();

					$clinical_presentations = array();

					if (isset($_POST["clinical_presentations"])) {
						$tmp_input = $_POST["clinical_presentations"];
						foreach ($tmp_input as $presentation) {
							$PROCESSED["clinical_presentations"][] = clean_input($presentation, "int");
						}
					}

					if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
						foreach ($checked_objectives as $objective_id) {
							if ($objective_id = (int) $objective_id) {
								if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
									$objective_text = $tmp_input;
								} else {
									$objective_text = false;
								}
								$PROCESSED["curriculum_objectives"][$objective_id] = $objective_text;
							}
						}
					}

					if (isset($_POST["course_list"]) && $tmp_input = clean_input($_POST["course_list"], array("notags", "int"))) {
						$PROCESSED["cohort"] = $tmp_input;
					} else {
						if ((isset($_POST["cohort"])) && ($cohort = clean_input($_POST["cohort"], "credentials"))) {
							$PROCESSED["cohort"] = $cohort;
						} else {
							add_error("You must select an <strong>Audience</strong> for this assessment.");
						}
					}

					if((isset($_POST["name"])) && ($name = clean_input($_POST["name"], array("notags", "trim")))) {
						$PROCESSED["name"] = $name;
					} else {
						add_error("You must supply a valid <strong>Name</strong> for this assessment.");
					}

					if((isset($_POST["grade_weighting"])) && ($_POST["grade_weighting"] !== NULL)) {
						$PROCESSED["grade_weighting"] = clean_input($_POST["grade_weighting"], "float");
					} else {
						add_error("You must supply a <strong>Grade Weighting</strong> for this assessment.");
					}

					if((isset($_POST["grade_threshold"])) && ($_POST["grade_threshold"] !== NULL)) {
						$PROCESSED["grade_threshold"] = clean_input($_POST["grade_threshold"], "float");
					} else {
						add_error("You must supply a <strong>Grade Threshold</strong> for this assessment.");
					}

					if((isset($_POST["description"])) && ($description = clean_input($_POST["description"], array("notags", "trim")))) {
						$PROCESSED["description"] = $description;
					} else {
						$PROCESSED["description"] = "";
					}

					if((isset($_POST["type"])) && ($type = clean_input($_POST["type"], array("trim")))) {
						if((@in_array($type, $ASSESSMENT_TYPES))) {
							$PROCESSED["type"] = $type;
						} else {
							add_error("You must supply a valid <strong>Type</strong> for this assessment. The submitted type is invalid.");
						}
					} else {
						add_error("You must pick a valid <strong>Type</strong> for this assessment.");
					}

					if((isset($_POST["marking_scheme_id"])) && ($marking_scheme_id = clean_input($_POST["marking_scheme_id"], array("trim","int")))) {
						if (@in_array($marking_scheme_id, $MARKING_SCHEME_IDS)) {
							$PROCESSED["marking_scheme_id"] = $marking_scheme_id;
						} else {
							add_error("The <strong>Marking Scheme</strong> you selected does not exist or is not enabled.");
						}
					} else {
						add_error("The <strong>Marking Scheme</strong> field is a required field.");
					}

					//Show in learner gradebook check
					if ((isset($_POST["show_learner_option"]))) {
						switch ($show_learner_option = clean_input($_POST["show_learner_option"], array("trim", "int"))) {
							case 0 :
								$PROCESSED["show_learner"] = $show_learner_option;
								$PROCESSED["release_date"] = 0;
								$PROCESSED["release_until"] = 0;
							break;
							case 1 :
								$PROCESSED["show_learner"] = $show_learner_option;
								$release_dates = validate_calendars("show", false, false);
								if ((isset($release_dates["start"])) && ((int) $release_dates["start"])) {
									$PROCESSED["release_date"]	= (int) $release_dates["start"];
								} else {
									$PROCESSED["release_date"]	= 0;
								}
								if ((isset($release_dates["finish"])) && ((int) $release_dates["finish"])) {
									$PROCESSED["release_until"]	= (int) $release_dates["finish"];
								} else {
									$PROCESSED["release_until"]	= 0;
								}
							break;
							default :
								$PROCESSED["show_learner"] = 0;
							break;
						}
					}
					//narrative check
					if ((isset($_POST["narrative_assessment"])) && ($narrative = clean_input($_POST["narrative_assessment"], array("trim", "int")))) {
						$PROCESSED["narrative"] = $narrative;
					} else {
						$PROCESSED["narrative"] = 0;
					}

					if (isset($_POST["event_id"]) && $tmp_input = clean_input($_POST["event_id"], array("trim", "int"))) {
						$PROCESSED["event_id"] = $tmp_input;
						$event = Models_Event::get($PROCESSED["event_id"]);
					} else {
						$event = false;
					}

					//optional/required check
					if ((isset($_POST["assessment_required"]))) {
						switch (clean_input($_POST["assessment_required"], array("trim", "int"))) {
							case 0 :
								$PROCESSED["required"] = 0;
							break;
							case 1 :
								$PROCESSED["required"] = 1;
							break;
							default :
							break;
						}
					}
					//characteristic check
					if ((isset($_POST["assessment_characteristic"])) && ($assessment_characteristic = clean_input($_POST["assessment_characteristic"], array("trim", "int"))) == 0) {
						add_error("The <strong>Assessment Characteristic</strong> field is a required field.");
					} else if ((isset($_POST["assessment_characteristic"])) && ($assessment_characteristic = clean_input($_POST["assessment_characteristic"], array("trim", "int")))) {
						$PROCESSED["characteristic_id"] = $assessment_characteristic;
					}

					//extended options check
					if ((is_array($_POST["option"])) && (count($_POST["option"]))) {
						$assessment_options_selected = array();
						foreach ($_POST["option"] as $option_id) {
							if ($option_id = (int) $option_id) {
								$query = "SELECT * FROM `assessments_lu_meta_options` 
										  WHERE id = " .$db->qstr($option_id)."
										  AND `active` = '1'";
								$results = $db->GetAll($query);
								if ($results) {
									$assessment_options_selected[] = $option_id;
								}
							}
						}
					}
					// Sometimes requried field "number grade points total". Specifies what a numeric marking scheme assessment is "out of".
					// Only required when marking scheme is numeric, ID 3, hardcoded.
					if ((isset($_POST["numeric_grade_points_total"])) && ($points_total = clean_input($_POST["numeric_grade_points_total"], array("notags", "trim")))) {
						$PROCESSED["numeric_grade_points_total"] = $points_total;
					} else {
						$PROCESSED["numeric_grade_points_total"] = "";
						if(isset($PROCESSED["marking_scheme_id"])) {
							// Numberic marking scheme, hardcoded, lame
							if($PROCESSED["marking_scheme_id"] == 3) {
								add_error("The <strong>Maximum Points</strong> field is a required field when using the <strong>Numeric</strong> marking scheme.");
							}
						}
					}
					if (isset($_POST["post_action"])) {
						if(@in_array($_POST["post_action"], array("new", "index", "parent", "grade"))) {
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = $_POST["post_action"];
						} else {
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
						}
					} else {
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
					}

					if (!$ERROR) {
						$PROCESSED["order"]			= Models_Gradebook_Assessment::fetchNextOrder($COURSE_ID, $PROCESSED["cohort"]);
						$PROCESSED["created_date"]	= time();
						$PROCESSED["created_by"]	= $ENTRADA_USER->getID();
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
						$PROCESSED["course_id"]		= $COURSE_ID;
						$PROCESSED["active"]        = "1";

						$assessment = new Models_Gradebook_Assessment($PROCESSED);

						if ($assessment->insert()) {
							$ASSESSMENT_ID = $assessment->getAssessmentID();

							if ($ASSESSMENT_ID) {
								application_log("success", "Successfully added assessment ID [".$ASSESSMENT_ID."]");
							} else {
								application_log("error", "Unable to fetch the newly inserted assessment identifier for this assessment.");
							}

							if (isset($PROCESSED["event_id"])) {
								$assessment_event_array = array(
									"assessment_id" => $ASSESSMENT_ID,
									"event_id" => $PROCESSED["event_id"],
									"updated_by" => $PROCESSED["updated_by"],
									"updated_date" => $PROCESSED["updated_date"],
									"active" => 1
								);

								$assessment_event = new Models_Assessment_AssessmentEvent($assessment_event_array);

								if (!$assessment_event->insert()) {
									application_log("error", "Unable insert the attached learning event.Database said: ".$db->ErrorMsg());
								} else {
									application_log("success", "Successfully attached learning event [".$PROCESSED["event_id"]."] to assessment ID [".$ASSESSMENT_ID."]");
								}
							}

							if ((is_array($PROCESSED["clinical_presentations"])) && (count($PROCESSED["clinical_presentations"]))) {
								foreach ($PROCESSED["clinical_presentations"] as $objective_id) {
									if (!$db->AutoExecute("assessment_objectives", array("assessment_id" => $ASSESSMENT_ID, "objective_id" => $objective_id, "objective_type" => "clinical_presentation", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
										add_error("There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.");
										application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}

							if ((is_array($PROCESSED["curriculum_objectives"]) && count($PROCESSED["curriculum_objectives"]))) {
								foreach ($PROCESSED["curriculum_objectives"] as $objective_key => $objective_text) {
									if (!$db->AutoExecute("assessment_objectives", array("assessment_id" => $ASSESSMENT_ID, "objective_id" => $objective_key, "objective_details" => $objective_text, "objective_type" => "curricular_objective", "updated_date" => time(), "updated_by" => $ENTRADA_USER->getID()), "INSERT")) {
										add_error("There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.");
										application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
									}
								}
							}

							if ($assessment_options) {
								foreach ($assessment_options as $assessment_option) {
									$query = "SELECT * FROM `assessments`
									            WHERE `assessment_id`  = ".$db->qstr($ASSESSMENT_ID)."
									            AND `active` = '1'";
									$results = $db->GetRow($query);
									if ($results) {
										$PROCESSED["assessment_id"] = $results["assessment_id"];
										$PROCESSED["option_id"] = $assessment_option["id"];
										if (is_array($assessment_options_selected) && in_array($assessment_option["id"], $assessment_options_selected)) {
											$PROCESSED["option_active"] = 1;
										} else{
											$PROCESSED["option_active"] = 0;
										}
									}
									$db->AutoExecute("assessment_options", $PROCESSED, "INSERT");
								}
							}

							switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
								case "grade" :
									$url = ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("step" => false, "section" => "grade", "assessment_id" => $ASSESSMENT_ID));
									$msg = "You will now be redirected to the <strong>Grade Assessment</strong> page for \"<strong>".$PROCESSED["name"] . "</strong>\"; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "new" :
									$url = ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("step" => false, "section" => "add"));
									$msg = "You will now be redirected to another <strong>Add Assessment</strong> page for the ". $course_details["course_name"] . " gradebook; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "parent" :
									$url = ENTRADA_URL."/admin/".$MODULE;
									$msg = "You will now be redirected to the <strong>Gradebook</strong> index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "index" :
								default :
									$url = ENTRADA_URL."/admin/gradebook?".replace_query(array("step" => false, "section" => "view", "assessment_id" => false));
									$msg = "You will now be redirected to the <strong>assessment index</strong> page for ". $course_details["course_name"] . "; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
							}

							add_success($msg);
							$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
						} else {
							add_error("There was a problem inserting this assessment into the system. The administrators have been informed of this error; please try again later.");

							application_log("error", "There was an error inserting an assessment. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					continue;
				break;
			}

			// Display Content
			switch ($STEP) {
				case 2 :
					if ($SUCCESS) {
						echo display_success();
					}
					if ($NOTICE) {
						echo display_notice();
					}
					if ($ERROR) {
						echo display_error();
					}
				break;
				case 1 :
				default :
					/**
					 * Fetch the Clinical Presentation details.
					 */
					$clinical_presentations_list = array();
					$clinical_presentations = array();

					$results = fetch_clinical_presentations();
					if ($results) {
						foreach ($results as $result) {
							$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
						}
					} else {
						$clinical_presentations_list = false;
					}

					if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
						if (((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"])))) {
							foreach ($_POST["clinical_presentations"] as $objective_id) {
								if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
									$query	= "SELECT a.`objective_id`
												FROM `global_lu_objectives` AS a
												JOIN `course_objectives` AS b
												ON b.`course_id` = ".$COURSE_ID."
                                                AND a.`objective_id` = b.`objective_id`
                                                AND b.`active` = '1'
												JOIN `objective_organisation` AS c
												ON a.`objective_id` = c.`objective_id`
												WHERE a.`objective_id` = ".$db->qstr($objective_id)."
												AND c.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
												AND b.`objective_type` = 'event'
												AND a.`objective_active` = '1'";
									$result	= $db->GetRow($query);
									if ($result) {
										$clinical_presentations[$objective_id] = $clinical_presentations_list[$objective_id];
									}
								}
							}
						} else {
							$clinical_presentations = array();
						}
					} else {
						$query	 = "SELECT `objective_id`
									FROM `course_objectives`
									WHERE `course_id` = ".$COURSE_ID."
									AND `objective_type` = 'event'
                                    AND `active` = '1'";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
							}
						}
					}

					/**
					 * Fetch the Curriculum Objective details.
					 */
					list($curriculum_objectives_list, $top_level_id) = courses_fetch_objectives($ENTRADA_USER->getActiveOrganisation(), array($COURSE_ID), -1, 1, false, false, 0, true);

					$curriculum_objectives = array();

					if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
						foreach ($checked_objectives as $objective_id => $status) {
							if ($objective_id = (int) $objective_id) {
								if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
									$objective_text = $tmp_input;
								} else {
									$objective_text = false;
								}

								$curriculum_objectives[$objective_id] = $objective_text;
							}
						}
					}
					?>
					<h1>Add Assessment</h1>
					<?php
					if ($ERROR) {
						echo display_error();
					}
					?>
					<form id="assessment-form" action="<?php echo ENTRADA_URL; ?>/admin/gradebook/assessments?<?php echo replace_query(array("step" => 2)); ?>" method="post" class="form-horizontal">
						<h2 title="Assessment Details Section">Assessment Details</h2>
						<div id="assessment-details-section">
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding a Gradebook Assessment">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 23%" />
									<col style="width: 74%" />
								</colgroup>
								<tbody>
								<tr>
									<td></td>
									<td><label class="form-nrequired">Course Name:</label></td>
									<td>
										<a href="<?php echo ENTRADA_URL; ?>/admin/gradebook?<?php echo replace_query(array("step" => false, "section" => "view")); ?>"><?php echo html_encode($course_details["course_name"]); ?></a>
									</td>
								</tr>
								<?php
								$query = "	SELECT *
                                                FROM `groups`
                                                WHERE `group_type` = 'course_list'
                                                AND `group_value` = ".$db->qstr($COURSE_ID)."
                                                AND `group_active` = '1'
                                                ORDER BY `group_name`";
								$course_lists = $db->GetAll($query);
								if($course_lists) {
									if (count($course_lists) == 1) {
										?>
										<tr>
											<td></td>
											<td><label for="course_list" class="form-required">Course List:</label></td>
											<td>
												<span class="radio-group-title">All Learners in the <?php echo $course_details["course_code"];?> Course List Group</span>
												<div class="content-small">This assessment is intended for all learners that are members of the <?php echo $course_details["course_code"];?> Course List.</div>
												<input id="course_list" class="course-list" name="course_list" type="hidden" value="<?php echo $course_lists[0]["group_id"]; ?>">
											</td>
										</tr>
										<?php
									} else { ?>
										<tr>
											<td></td>
											<td><label for="course_list" class="form-required">Course List:</label></td>
											<td>
												<select id="course_list" class="course-list" name="course_list" style="width: 250px">
													<?php
													foreach ($course_lists as $course_list) {
														echo "<option value=\"".$course_list["group_id"]."\"".(($PROCESSED["cohort"] == $course_list["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($course_list["group_name"])."</option>\n";
													}
													?>
												</select>
											</td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr>
										<td></td>
										<td><label for="cohort" class="form-required">Cohort:</label></td>
										<td>
											<select id="cohort" class="course-list" name="cohort" style="width: 250px">
												<?php
												$active_cohorts = groups_get_all_cohorts($ENTRADA_USER->getActiveOrganisation());
												if (isset($active_cohorts) && !empty($active_cohorts)) {
													foreach ($active_cohorts as $cohort) {
														echo "<option value=\"".$cohort["group_id"]."\"".(($PROCESSED["cohort"] == $cohort["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
													}
												}
												?>
											</select>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="name" class="form-required">Assessment Name:</label></td>
									<td><input type="text" id="name" name="name" value="<?php echo html_encode($PROCESSED["name"]); ?>" maxlength="255" class="span11" /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="description" class="form-nrequired">Assessment Description:</label></td>
									<td><textarea id="description" name="description" class="expandable span11"><?php echo html_encode($PROCESSED["description"]); ?></textarea></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="grade_weighting" class="form-nrequired">Assessment Weighting</label></td>
									<td>
										<input type="text" id="grade_weighting" name="grade_weighting" value="<?php echo (float) html_encode($PROCESSED["grade_weighting"]); ?>" maxlength="5" style="width: 40px" autocomplete="off" />
										<span class="content-small"><strong>Tip:</strong> The percentage or numeric value of the final grade this assessment is worth.</span>
									</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="grade_threshold" class="form-nrequired">Assessment Threshold (%):</label></td>
									<td>
										<input type="text" id="grade_threshold" name="grade_threshold" value="<?php echo (float) html_encode($PROCESSED["grade_threshold"]); ?>" maxlength="5" style="width: 40px" autocomplete="off" />
										<span class="content-small"><strong>Tip:</strong> Student grades below the threshold will trigger coordinator / director flag.</span>
									</td>
								</tr>
								</tbody>
								<tbody id="assessment_required_options">
								<tr>
									<td>&nbsp;</td>
									<td colspan="2" style="padding-top: 10px">
										<label class="form-nrequired" for="assessment_required_0">Is this assessment <strong>optional</strong> or <strong>required</strong>?</label>
										<div style="margin: 5px 0 0 25px">
											<label class="form-nrequired radio" for="assessment_required_0">
												<input type="radio" name="assessment_required" value="0" id="assessment_required_0" <?php echo (($PROCESSED["required"] == 0)) ? " checked=\"checked\"" : "" ?> /> Optional
											</label>
											<label class="form-nrequired radio" for="assessment_required_1">
												<input type="radio" name="assessment_required" value="1" id="assessment_required_1" <?php echo (($PROCESSED["required"] == 1)) ? " checked=\"checked\"" : "" ?> /> Required
											</label>
										</div>
									</td>
								</tr>
								</tbody>
								<tbody>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="assessment_characteristic" class="form-required">Characteristic:</label></td>
									<td>
										<select id="assessment_characteristic" name="assessment_characteristic">
											<option value="">-- Select Assessment Characteristic --</option>
											<?php
											$query = "	SELECT *
                                                            FROM `assessments_lu_meta`
                                                            WHERE `organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
                                                            AND `active` = '1'
                                                            ORDER BY `type` ASC, `title` ASC";
											$assessment_characteristics = $db->GetAll($query);
											if ($assessment_characteristics) {
												$type = "";
												foreach ($assessment_characteristics as $key => $characteristic) {
													if ($type != $characteristic["type"]) {
														if ($key) {
															echo "</optgroup>";
														}
														echo "<optgroup label=\"".ucwords(strtolower($characteristic["type"]))."s\">";

														$type = $characteristic["type"];
													}

													echo "<option value=\"" . $characteristic["id"] . "\" assessmenttype=\"" . $characteristic["type"] . "\"".(($PROCESSED["characteristic_id"] == $characteristic["id"]) ? " selected=\"selected\"" : "").">".$characteristic["title"]."</option>";
													if ($PROCESSED["characteristic_id"] == $characteristic["id"]) {
														$current_type = $characteristic["type"];
													}
												}
												echo "</optgroup>";
											}
											?>
										</select>
									</td>
								</tr>
								</tbody>
								<tbody id="assessment_options" style="display:none;">
								<tr>
									<td></td>
									<td style="vertical-align: top;"><label for="extended_option1" class="form-nrequired">Extended Options:</label></td>
									<td class="options"></td>
								</tr>
								</tbody>
								<tbody>
								<tr>
									<td></td>
									<td><label for="marking_scheme_id" class="form-required">Marking Scheme:</label></td>
									<td>
										<select id="marking_scheme_id" name="marking_scheme_id">
											<?php
											foreach ($MARKING_SCHEMES as $scheme) {
												echo "<option value=\"".$scheme["id"]."\"".(($PROCESSED["marking_scheme_id"] == $scheme["id"]) ? " selected=\"selected\"" : "").">".$scheme["name"]."</option>";
											}
											?>
										</select>
									</td>
								</tr>
								<tr id="numeric_marking_scheme_details" style="display: none;">
									<td></td>
									<td><label for="numeric_grade_points_total" class="form-required">Maximum Points:</label></td>
									<td>
										<input type="text" id="numeric_grade_points_total" name="numeric_grade_points_total" value="<?php echo html_encode($PROCESSED["numeric_grade_points_total"]); ?>" maxlength="5" style="width: 50px" />
										<span class="content-small"><strong>Tip:</strong> Maximum points possible for this assessment (i.e. <strong>20</strong> for &quot;X out of 20).</span>
									</td>
								</tr>
								<tr>
									<td></td>
									<td><label for="type" class="form-required">Assessment Type:</label></td>
									<td>
										<select id="type" name="type">
											<?php
											foreach ($ASSESSMENT_TYPES as $type) {
												echo "<option value=\"".$type."\"".(($PROCESSED["type"] == $type) ? " selected=\"selected\"" : "").">".$type."</option>";
											}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3">
										<label class="checkbox form-nrequired" for="narrative_assessment">
											<input type="checkbox" id="narrative_assessment" name="narrative_assessment" value="1" <?php echo (($PROCESSED["narrative"] == 1)) ? " checked=\"checked\"" : ""?> /> This is a <strong>narrative assessment</strong>.
										</label>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td valign="top"><label class="form-nrequired">Assessment Event:</label></td>
									<td>
										<a id="attach-event-button" href="#event-modal" class="btn btn-success space-below" role="button" data-toggle="modal"><i class="icon-plus-sign icon-white"></i> Attach Learning Event </a>
										<ul id="attached-event-list">
											<li>
												<div class="well well-small content-small" id="no-learning-event">
													There are currently no learning events attached to this assessment. To attach a learning event to this assessment, use the attach event button.
												</div>
											</li>
										</ul>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3">
										<label class="radio form-nrequired" for="show_learner_option_0">
											<input type="radio" name="show_learner_option" value="0" id="show_learner_option_0" <?php echo (($PROCESSED["show_learner"] == 0)) ? " checked=\"checked\"" : "" ?> /> Don't Show this Assessment in Learner Gradebook
										</label>
										<label class="radio form-nrequired" for="show_learner_option_1">
											<input type="radio" name="show_learner_option" value="1" id="show_learner_option_1" <?php echo (($PROCESSED["show_learner"] == 1)) ? " checked=\"checked\"" : "" ?> /> Show this Assessment in Learner Gradebook
										</label>
									</td>
								</tr>
								</tbody>
								<tbody id="gradebook_release_options" style="display: none;">
								<?php echo generate_calendars("show", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0), true, false, " grades starting", " grades until"); ?>
								</tbody>
							</table>
						</div>

						<script type="text/javascript" charset="utf-8">
							var course_id = "<?php echo $COURSE_ID ?>";

							jQuery(function($) {
								//var search_type = "<?php echo (isset($PREFERENCES["assessment-event-search"]) ? $PREFERENCES["assessment-event-search"] : "calendar"); ?>";
								var timer;
								var done_interval = 600;

								if ($("#assessment-event").length > 0) {
									buildAttachedEventList();
								}

								$("#event-title-search").keyup(function () {
									var title = $(this).val();

									clearTimeout(timer);
									timer = setTimeout(function () {
										getEventsByTitle(title);
									}, done_interval);
								});

								$("#events-search-wrap").on("click", "#events-search-list li", function () {
									if ($("#events-search-list").children().hasClass("active")) {
										$("#events-search-list").children().removeClass("active");
									}

									if (!$(this).hasClass("active")) {
										$(this).addClass("active");
									}
								});

								$("#attach-learning-event").on("click", function (e) {
									e.preventDefault();

									if ($("#events-search-list").children().hasClass("active")) {
										var event_id = $("#events-search-list").children(".active").attr("data-id");
										var event_title = $("#events-search-list").children(".active").attr("data-title");
										var event_date = $("#events-search-list").children(".active").attr("data-date");

										buildEventInput(event_id, event_title, event_date);
										buildAttachedEventList ();

										$("#attach-event-button").addClass("hide");
										$("#event-modal").modal("hide");
									} else {
										alert("Please select a learning event to attach to this assessment. If you no longer wish to attach a learning event to this assessment, click close.");
									}
								});

								$("#assessment-form").on("click", "#remove-attched-assessment-event", function () {
									var event_well_li = document.createElement("li");
									var event_well = document.createElement("div");

									$(event_well).addClass("well well-small content-small").text("There are currently no learning events attached to this assessment. To attach a learning event to this assessment, use the attach event button. ");
									$(event_well_li).append(event_well);
									$("#attached-event-list").empty();
									$("#attached-event-list").append(event_well_li);
									$("#attach-event-button").removeClass("hide");
									$("#assessment-event").remove();
								});

								jQuery('#marking_scheme_id').change(function() {
									if(jQuery(':selected', this).val() == 3 || jQuery(':selected', this).text() == "Numeric") {
										jQuery('#numeric_marking_scheme_details').show();
									} else {
										jQuery('#numeric_marking_scheme_details').hide();
									}
								}).trigger('change');

								$("#close-event-modal").on("click", function (e) {
									e.preventDefault();
									if ($("#events-search-list").children().hasClass("active")) {
										$("#events-search-list").children().removeClass("active");
									}

									$("#event-modal").modal("hide");
								});

								$("#event-modal").on("hide", function () {
									if ($("#events-search-list").children().hasClass("active")) {
										$("#events-search-list").children().removeClass("active");
									}
									$("#events-search-list").empty();
									$("#event-title-search").val("");
								});

								$("#event-modal").on("show", function () {
									if (!jQuery("#event-search-msgs").children().length) {
										var msg = ["To search for learning events, begin typing the title of the event you wish to find in the search box."];
										display_notice(msg, "#event-search-msgs", "append");
									}
								});

								function fetchOptions(select, selected_options) {
									jQuery.ajax({
										url: "<?php echo ENTRADA_URL; ?>/admin/gradebook/assessments/?section=add&id=" + <?php echo $COURSE_ID; ?>,
										data: "mode=ajax&method=fetch-extended-options&type=" + select.val(),
										type: "POST",
										success: function(data) {
											if (data.length > 0) {
												jQuery("#assessment_options .options").append(data);

												if (typeof selected_options != "undefined") {
													for (var i = 0; i < selected_options.length; i++) {
														if (jQuery("#extended_option"+selected_options[i].length > 0)) {
															jQuery("#extended_option"+selected_options[i]).attr("checked", "checked");
														}
													}
												}

												if (!jQuery("#assessment_options").is(":visible")) {
													jQuery("#assessment_options").show();
												}
											} else {
												jQuery("#assessment_options").hide();
											}
										}
									});
								}

								jQuery('#assessment_characteristic').on("change", function(e) {
									var select = jQuery(this);
									jQuery("#assessment_options .options").html("");
									fetchOptions(select);
									e.preventDefault();
								});

								<?php if ($ERROR && isset($assessment_options_selected) && $assessment_options_selected) { ?>
								var selected_options;
								selected_options = JSON.parse("<?php echo json_encode($assessment_options_selected); ?>");
								fetchOptions(jQuery('#assessment_characteristic'), selected_options);
								<?php } ?>

								jQuery("input[name='show_learner_option']").change(function(){
									if (jQuery("input[name='show_learner_option']:checked").val() == 1) {
										jQuery('#gradebook_release_options').show();
									}
									else if (jQuery("input[name='show_learner_option']:checked").val() == 0) {
										jQuery('#gradebook_release_options').hide();
									}
								});

								jQuery(document).ready(function(){
									if (jQuery("input[name='show_learner_option']:checked").val() == 1) {
										jQuery('#gradebook_release_options').show();
									}
									else if (jQuery("input[name='show_learner_option']:checked").val() == 0) {
										jQuery('#gradebook_release_options').hide();
									}
								});
							});

							function getEventsByTitle (title) {
								var audience = jQuery(".course-list").val();
								jQuery.ajax({
									url: "<?php echo ENTRADA_URL; ?>/api/assessment-event.api.php",
									data: "method=title_search&course_id=" + course_id + "&title=" + title + "&audience=" + audience,
									type: "GET",
									beforeSend: function () {
										jQuery("#event-search-msgs").empty();
										jQuery("#events-search-list").empty();
										jQuery("#loading").removeClass("hide");
									},
									success: function(data) {
										jQuery("#loading").addClass("hide");
										var response = JSON.parse(data);
										if (response.status == "success") {
											jQuery.each(response.data, function (key, event) {
												buildEventList(event);
											});
										} else {
											display_notice(response.data, "#event-search-msgs", "append");
										}
									},
									error: function () {
										jQuery("#loading").addClass("hide");
									}
								});
							}

							function buildEventList (event) {
								var event_li = document.createElement("li");
								var event_div = document.createElement("div");
								var event_h3 = document.createElement("h3");
								var event_span = document.createElement("span");

								jQuery(event_h3).addClass("event-text").text(event.event_title).html();
								jQuery(event_span).addClass("event-text").addClass("muted").text(event.event_start).html();
								jQuery(event_div).addClass("event-container").append(event_h3).append(event_span);
								jQuery(event_li).attr({"data-id": event.event_id, "data-title": event.event_title, "data-date": event.event_start}).append(event_div);

								jQuery("#events-search-list").append(event_li);
							}

							function buildEventInput (event_id, event_title, event_date) {
								var event_input = document.createElement("input");

								if (jQuery("#assessment-event").length) {
									jQuery("#assessment-event").remove();
								}

								jQuery(event_input).attr({name: "event_id", id: "assessment-event", type: "hidden", value: event_id, "data-title": event_title, "data-date": event_date});
								jQuery("#assessment-form").append(event_input);
							}

							function buildAttachedEventList () {
								var event_title = jQuery("#assessment-event").attr("data-title");
								var event_date = jQuery("#assessment-event").attr("data-date");
								var event_li = document.createElement("li");
								var event_div = document.createElement("div");
								var remove_icon_div = document.createElement("div");
								var event_h3 = document.createElement("h3");
								var event_span = document.createElement("span");
								var remove_icon_span = document.createElement("span");
								var event_remove_icon = document.createElement("i");

								jQuery(remove_icon_span).attr({id: "remove-attched-assessment-event"}).addClass("label label-important");
								jQuery(event_remove_icon).addClass("icon-trash icon-white");
								jQuery(remove_icon_span).append(event_remove_icon);
								jQuery(remove_icon_div).append(remove_icon_span).attr({id: "remove-attched-assessment-event-div"});
								jQuery(event_h3).addClass("event-text").text(event_title).html();
								jQuery(event_span).addClass("event-text").addClass("muted").text(event_date).html();
								jQuery(event_div).append(event_h3).append(event_span);
								jQuery(event_li).append(event_div);
								jQuery(event_li).append(remove_icon_div).append(event_div);
								jQuery("#attached-event-list").empty();
								jQuery("#attached-event-list").append(event_li);
							}
						</script>

						<?php
						$query = "	SELECT a.* FROM `global_lu_objectives` a
									JOIN `objective_audience` b
									ON a.`objective_id` = b.`objective_id`
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									WHERE (
											(b.`audience_value` = 'all')
											OR
											(b.`audience_type` = 'course' AND b.`audience_value` = ".$db->qstr($COURSE_ID).")
											OR
											(b.`audience_type` = 'event' AND b.`audience_value` = ".$db->qstr($EVENT_ID).")
										)
									AND a.`objective_parent` = '0'
									AND a.`objective_active` = '1'";
						$objectives = $db->GetAll($query);
						if ($objectives) {
							?>
							<style type="text/css">
								.mapped-objective {
									padding-left: 30px!important;
								}
							</style>
							<a name="assessment-objectives-section"></a>
							<h2 title="Assessment Objectives Section" class="collapsed">Assessment Objectives</h2>
							<div id="assessment-objectives-section">
								<?php
								$objective_name = $translate->_("events_filter_controls");
								$hierarchical_name = $objective_name["co"]["global_lu_objectives_name"];
								?>
								<div class="objectives half left">
									<h3>Objective Sets</h3>
									<ul class="tl-objective-list" id="objective_list_0">
										<?php
										foreach ($objectives as $objective) {
											?>
											<li class = "objective-container objective-set assessment-objective"
												id = "objective_<?php echo $objective["objective_id"]; ?>"
												data-list="<?php echo $objective["objective_name"] == $hierarchical_name?'hierarchical':'flat'; ?>"
												data-id="<?php echo $objective["objective_id"]; ?>">
												<?php
												$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
												?>
												<div class="objective-title"
													 id="objective_title_<?php echo $objective["objective_id"]; ?>"
													 data-title="<?php echo $title;?>"
													 data-id = "<?php echo $objective["objective_id"]; ?>"
													 data-code = "<?php echo $objective["objective_code"]; ?>"
													 data-name = "<?php echo $objective["objective_name"]; ?>"
													 data-description = "<?php echo $objective["objective_description"]; ?>">
													<h4><?php echo $title; ?></h4>
												</div>
												<div class="objective-controls" id="objective_controls_<?php echo $objective["objective_id"];?>">
												</div>
												<div class="objective-children" id="children_<?php echo $objective["objective_id"]; ?>">
													<ul class="objective-list" id="objective_list_<?php echo $objective["objective_id"]; ?>"></ul>
												</div>
											</li>
											<?php
										}
										?>
									</ul>
								</div>

								<?php
								$query = "	SELECT a.*, COALESCE(b.`objective_details`,a.`objective_description`) AS `objective_description`, b.`objective_type` AS `objective_type`,
                                                    b.`importance`,
                                                    COALESCE(b.`cobjective_id`,0) AS `mapped_to_course`
                                                    FROM `global_lu_objectives` a
                                                    LEFT JOIN `course_objectives` b
                                                    ON a.`objective_id` = b.`objective_id`
                                                    AND b.`course_id` = ".$db->qstr($COURSE_ID)."
                                                    AND b.`active` = '1'
                                                    WHERE a.`objective_active` = '1'
                                                    AND b.`course_id` = ".$db->qstr($COURSE_ID)."
                                                    GROUP BY a.`objective_id`
                                                    ORDER BY a.`objective_id` ASC";
								$mapped_objectives = $db->GetAll($query);
								$primary = false;
								$secondary = false;
								$tertiary = false;
								$hierarchical_objectives = array();
								$flat_objectives = array();
								$explicit_assessment_objectives = false;
								$mapped_assessment_objectives = array();
								if ($mapped_objectives) {
									foreach ($mapped_objectives as $objective) {
										if (in_array($objective["objective_type"], array("curricular_objective","course"))) {
											$hierarchical_objectives[] = $objective;
										} else {
											$flat_objectives[] = $objective;
										}

										if ($objective["mapped"]) {
											$mapped_assessment_objectives[] = $objective;
										}
									}
								}
								?>
								<div class="mapped_objectives right droppable" id="mapped_objectives" data-resource-type="assessment" data-resource-id="<?php echo $ASSESSMENT_ID;?>">
									<h3>Mapped Objectives</h3>
									<div class="clearfix">
										<ul class="page-action" style="float: right">
											<li class="last">
												<a href="javascript:void(0)" class="mapping-toggle strong-green" data-toggle="show" id="toggle_sets">Map Additional Objectives</a>
											</li>
										</ul>
									</div>
									<?php
									if ($hierarchical_objectives) {
										assessment_objectives_display_leafs($hierarchical_objectives, $COURSE_ID, 0);
									}

									if ($flat_objectives) {
										?>
										<div id="clinical-list-wrapper">
											<a name="clinical-objective-list"></a>
											<h2 id="flat-toggle"  title="Clinical Objective List" class="collapsed list-heading">Other Objectives</h2>
											<div id="clinical-objective-list">
												<ul class="objective-list mapped-list" id="mapped_flat_objectives" data-importance="flat">
													<?php
													if ($flat_objectives) {
														foreach ($flat_objectives as $objective) {
															$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
															?>
															<li class = "mapped-objective"
																id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
																data-id = "<?php echo $objective["objective_id"]; ?>"
																data-title="<?php echo $title;?>"
																data-description="<?php echo htmlentities($objective["objective_description"]);?>">
																<strong><?php echo $title; ?></strong>
																<div class="objective-description">
																	<?php
																	$set = fetch_objective_set_for_objective_id($objective["objective_id"]);
																	if ($set) {
																		echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
																	}

																	echo $objective["objective_description"];
																	?>
																</div>

																<div class="assessment-objective-controls">
																	<input type="checkbox" class="checked-mapped" id="check_mapped_<?php echo $objective['objective_id'];?>" value="<?php echo $objective['objective_id'];?>" <?php echo $objective["mapped"]?' checked="checked"':''; ?>/>
																</div>
															</li>
															<?php
														}
													}
													?>
												</ul>
											</div>
										</div>
										<?php
									}
									?>

									<div id="assessment-list-wrapper" <?php echo ($explicit_assessment_objectives)?"":" style=\"display:none;\"";?>>
										<a name="assessment-objective-list"></a>
										<h2 id="assessment-toggle"  title="Assessment Objective List" class="collapsed list-heading">Assessment Specific Objectives</h2>
										<div id="assessment-objective-list">
											<ul class="objective-list mapped-list" id="mapped_assessment_objectives" data-importance="assessment">
												<?php
												if ($explicit_assessment_objectives) {
													foreach($explicit_assessment_objectives as $objective) {
														$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
														?>
														<li class = "mapped-objective"
															id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
															data-id = "<?php echo $objective["objective_id"]; ?>"
															data-title="<?php echo $title;?>"
															data-description="<?php echo htmlentities($objective["objective_description"]);?>"
															data-mapped="<?php echo $objective["mapped_to_course"]?1:0;?>">
															<strong><?php echo $title; ?></strong>
															<div class="objective-description">
																<?php
																$set = fetch_objective_set_for_objective_id($objective["objective_id"]);
																if ($set) {
																	echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
																}

																echo $objective["objective_description"];
																?>
															</div>

															<div class="assessment-objective-controls">
																<img 	src="<?php echo ENTRADA_URL;?>/images/action-delete.gif"
																		class="objective-remove list-cancel-image"
																		id="objective_remove_<?php echo $objective["objective_id"];?>"
																		data-id="<?php echo $objective["objective_id"];?>">
															</div>
														</li>
														<?php
													}
												}
												?>
											</ul>
										</div>
									</div>
									<select id="checked_objectives_select" name="checked_objectives[]" multiple="multiple" style="display:none;">
										<?php
										if ($mapped_assessment_objectives) {
											foreach ($mapped_assessment_objectives as $objective) {
												if (in_array($objective["objective_type"], array("curricular_objective","course"))) {
													$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
													?>
													<option value="<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
													<?php
												}
											}
										}
										?>
									</select>
									<select id="clinical_objectives_select" name="clinical_presentations[]" multiple="multiple" style="display:none;">
										<?php
										if ($mapped_assessment_objectives) {
											foreach ($mapped_assessment_objectives as $objective) {
												if (in_array($objective["objective_type"], array("clinical_presentation","event"))) {
													$title = ($objective["objective_code"]?$objective["objective_code"].': '.$objective["objective_name"]:$objective["objective_name"]);
													?>
													<option value = "<?php echo $objective["objective_id"]; ?>" selected="selected"><?php echo $title; ?></option>
													<?php
												}
											}
										}
										?>
									</select>
								</div>
							</div>
							<div style="clear:both;"></div>
							<?php
						}
						?>
						<table style="width: 100%; margin-top: 25px" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="width: 25%; text-align: left">
									<input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/gradebook?<?php echo replace_query(array("step" => false, "section" => "view", "assessment_id" => false)); ?>'" />
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<span class="content-small">After saving:</span>
									<select id="post_action" name="post_action">
										<option value="grade"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "grade") ? " selected=\"selected\"" : ""); ?>>Grade assessment</option>
										<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another assessment</option>
										<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to assessment list</option>
										<option value="parent"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "parent") ? " selected=\"selected\"" : ""); ?>>Return to all gradebooks list</option>
									</select>
									<input type="submit" class="btn btn-primary" value="Save" />
								</td>
							</tr>
						</table>
						<?php
						if ($event) {
							?>
							<input type="hidden" id="assessment-event" name="event_id" value="<?php echo $event->getID(); ?>" data-title="<?php echo $event->getEventTitle(); ?>" data-date="<?php echo date("D M d/y g:ia", $event->getEventStart()) ?>" />
							<?php
						}
						?>
					</form>
					<div id="event-modal" class="modal hide fade">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 id="event-modal-title">Search learning events to attach to this assessment</h3>
						</div>
						<div class="modal-body">
							<div id="title-search">
								<input type="text" placeholder="Search learning events by title" id="event-title-search" />
								<div id="event-search-msgs">
									<div class="alert alert-block">
										<ul>
											<li>To search for learning events, begin typing the title of the event you wish to find in the search box.</li>
										</ul>
									</div>
								</div>
								<div id="events-search-wrap">
									<ul id="events-search-list"></ul>
								</div>
							</div>
							<div id="loading" class="hide"><img src="<?php echo ENTRADA_URL ?>/images/loading.gif" /></div>
						</div>
						<div id="event-modal-footer">
							<a href="#" class="btn" id="close-event-modal">Close</a>
							<a href="#" id="attach-learning-event" class="btn btn-primary pull-right">Attach Learning Event</a>
						</div>
					</div>
					<?php
				break;
			}
		} else {
			add_error("In order to add an assignment to a gradebook you must provide a valid course identifier. The provided ID does not exist in this system.");

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifier when attempting to add an assessment.");
		}
	} else {
		add_error("In order to add an assignment to a gradebook you must provide a valid course identifier. The provided ID does not exist in this system.");

		echo display_error();

		application_log("notice", "Failed to provide course identifier when attempting to add an assessment.");
	}
}