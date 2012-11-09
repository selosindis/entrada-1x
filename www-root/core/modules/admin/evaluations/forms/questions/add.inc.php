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
 * This file is used by administrators to add questions to an evaluation form.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationformquestion", "create", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($ALLOW_QUESTION_MODIFICATIONS) {
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&amp;section=add", "title" => "Add Question");

		/**
		 * Required field "questiontype_id" / Question Type
		 * Currently only multile choice questions are supported, although
		 * this is something we will be expanding on shortly.
		 */
		if ((isset($_POST["questiontype_id"])) && ($tmp_input = clean_input($_POST["questiontype_id"], array("trim", "int")))) {
			$PROCESSED["questiontype_id"] = $tmp_input;
		} elseif ((isset($_GET["qtype_id"])) && ($tmp_input = clean_input($_GET["qtype_id"], array("trim", "int")))) {
			$PROCESSED["questiontype_id"] = $tmp_input;
		} else {
			$PROCESSED["questiontype_id"] = 1;
		}
		// Error Checking
		switch ($STEP) {
			case 2 :
				/**
				 * Add the eform_id of the form this question will be added to.
				 */
				$PROCESSED["eform_id"] = $FORM_ID;
				//If Rubric question type.
				switch ($PROCESSED["questiontype_id"]) {
					case 3 :
						/**
						 * Required field "rubric_title" / Rubric Title.
						 */
						if ((isset($_POST["rubric_title"])) && ($tmp_input = clean_input($_POST["rubric_title"], array("trim")))) {
							$PROCESSED["rubric_title"] = $tmp_input;
						} else {
							$PROCESSED["rubric_title"] = "";
						}
						
						/**
						 * Non-required field "question_text" / Form Question.
						 */
						if ((isset($_POST["rubric_description"])) && ($tmp_input = clean_input($_POST["rubric_description"], array("trim", "allowedtags")))) {
							$PROCESSED["rubric_description"] = $tmp_input;
						} else {
							$PROCESSED["rubric_description"] = "";
						}

						if ((isset($_POST["categories_count"])) && ($tmp_input = clean_input($_POST["categories_count"], array("int")))) {
							$PROCESSED["categories_count"] = $tmp_input;
						} else {
							$PROCESSED["categories_count"] = "";
						}

						if ((isset($_POST["columns_count"])) && ($tmp_input = clean_input($_POST["columns_count"], array("int")))) {
							$PROCESSED["columns_count"] = $tmp_input;
						} else {
							$PROCESSED["columns_count"] = "";
						}
						/**
						 * Required field "question_text" / Form Question.
						 */
						if ((isset($_POST["allow_comments"])) && clean_input($_POST["allow_comments"], array("bool"))) {
							$PROCESSED["allow_comments"] = true;
						} else {
							$PROCESSED["allow_comments"] = false;
						}
					break;
					case 1 :
					default :
						/**
						 * Required field "question_text" / Form Question.
						 */
						if ((isset($_POST["allow_comments"])) && clean_input($_POST["allow_comments"], array("bool"))) {
							$PROCESSED["allow_comments"] = true;
						} else {
							$PROCESSED["allow_comments"] = false;
						}
					case 2 :
					case 4 :
					/**
					 * Required field "question_text" / Form Question.
					 */
					if ((isset($_POST["question_text"])) && ($tmp_input = clean_input($_POST["question_text"], array("trim", "allowedtags")))) {
						$PROCESSED["question_text"] = $tmp_input;
					} else {
						add_error("The <strong>Question Text</strong> field is required.");
					}
					break;
				}
				
				if ($PROCESSED["questiontype_id"] != 2 && $PROCESSED["questiontype_id"] != 4) {
					/**
					 * Required field "response_text" / Available Responses.
					 *
					 */
					$minimum_passing_level_found = false;
					$PROCESSED["evaluation_form_responses"] = array();
					$PROCESSED["evaluation_form_categories"] = array();
					$PROCESSED["evaluation_form_category_criteria"] = array();
					if ((isset($_POST["response_text"])) && (is_array($_POST["response_text"]))) {
						$i = 1;
						foreach ($_POST["response_text"] as $response_key => $response_text) {
							$response_key = clean_input($response_key, "int");
							$response_is_html = 0;

							/**
							 * Check if this is response is in HTML or just plain text.
							 */
							if ((isset($_POST["response_is_html"])) && (is_array($_POST["response_is_html"])) && (isset($_POST["response_is_html"][$response_key])) && ($_POST["response_is_html"][$response_key] == 1)) {
								$response_is_html = 1;
							}

							if ($response_is_html) {
								$response_text = clean_input($response_text, array("trim", "allowedtags"));
							} else {
								$response_text = clean_input($response_text, array("trim"));
							}

							if (($response_key) && ($response_text != "")) {
								if (is_array($PROCESSED["evaluation_form_responses"]) && !empty($PROCESSED["evaluation_form_responses"])) {
									foreach ($PROCESSED["evaluation_form_responses"] as $value) {
										if ($value["response_text"] == $response_text) {
											add_error("You cannot have more than one <strong>identical response</strong> in a question.");
										}
									}
								}

								$PROCESSED["evaluation_form_responses"][$i]["response_text"] = $response_text;
								$PROCESSED["evaluation_form_responses"][$i]["response_order"] = $i;

								/**
								 * Check if this is the selected minimum passing level or not.
								 */
								if ((isset($_POST["minimum_passing_level"])) && ($minimum_passing_level = clean_input($_POST["minimum_passing_level"], array("trim", "int"))) && ($response_key == $minimum_passing_level)) {
									$minimum_passing_level_found = true;
									$PROCESSED["evaluation_form_responses"][$i]["minimum_passing_level"] = 1;
								} else {
									$PROCESSED["evaluation_form_responses"][$i]["minimum_passing_level"] = 0;
								}

								$PROCESSED["evaluation_form_responses"][$i]["response_is_html"] = $response_is_html;

								$i++;
							}
						}
					}
				}
				if ((isset($_POST["category"])) && (is_array($_POST["category"]))) {
					$i = 1;
					foreach ($_POST["category"] as $category_key => $category) {
						$category_key = clean_input($category_key, "int");
						
						$category = clean_input($category, array("trim"));

						if (($category_key) && ($category != "")) {
							if (is_array($PROCESSED["evaluation_form_categories"]) && !empty($PROCESSED["evaluation_form_categories"])) {
								foreach ($PROCESSED["evaluation_form_categories"] as $value) {
									if ($value["category"] == $category) {
										add_error("You cannot have more than one <strong>identical category</strong> in a rubric.");
									}
								}
							}

							$PROCESSED["evaluation_form_categories"][$i]["category"] = $category;
							$PROCESSED["evaluation_form_categories"][$i]["category_order"] = $i;
							if ((isset($_POST["criteria"][$i])) && (is_array($_POST["criteria"][$i]))) {
								$j = 1;
								foreach ($_POST["criteria"][$i] as $criteria_key => $criteria) {
									$criteria_key = clean_input($criteria_key, "int");

									$criteria = clean_input($criteria, array("trim", "notags"));

									if (($criteria_key)) {
										if ($criteria != "" && is_array($PROCESSED["evaluation_form_category_criteria"][$i]) && !empty($PROCESSED["evaluation_form_category_criteria"][$i])) {
											foreach ($PROCESSED["evaluation_form_category_criteria"][$i] as $value) {
												if ($value["criteria"] == $criteria) {
													add_error("You cannot have more than one <strong>identical criteria</strong> in a category.");
												}
											}
										}

										$PROCESSED["evaluation_form_category_criteria"][$i][$j]["criteria"] = $criteria;
										$PROCESSED["evaluation_form_category_criteria"][$i][$j]["criteria_order"] = $j;

										$j++;
									}
								}
							}

							$i++;
						}
					}
				}
				
				/**
				 * There must be at least 2 possible responses to proceed.
				 */
				if (count($PROCESSED["evaluation_form_responses"]) < 2 && $PROCESSED["questiontype_id"] != 2 && $PROCESSED["questiontype_id"] != 4) {
					add_error("You must provide at least 2 responses in the <strong>Available Responses</strong> section.");
				}

				/**
				 * Get the next order of this question from the evaluation_form_questions table.
				 */
				$query	= "SELECT MAX(`question_order`) AS `next_order` FROM `evaluation_form_questions` WHERE `eform_id` = ".$db->qstr($FORM_ID);
				$result = $db->GetRow($query);
				if ($result) {
					$PROCESSED["question_order"] = ($result["next_order"] + 1);
				} else {
					$PROCESSED["question_order"] = 0;
				}

				if (isset($_POST["post_action"])) {
					switch ($_POST["post_action"]) {
						case "new" :
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
						break;
						case "index" :
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
						break;
						case "content" :
						default :
							$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
						break;
					}
				} else {
					$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
				}

				if (!has_error()) {
					if($ENTRADA_ACL->amIAllowed(new EvaluationFormQuestionResource(null, $FORM_RECORD["eform_id"]), "create")) {
						if ($PROCESSED["questiontype_id"] == 3) {
							if ($db->AutoExecute("evaluation_form_rubrics", $PROCESSED, "INSERT") && ($efrubric_id = $db->Insert_Id())) {
								$PROCESSED["question_order"]--;
								foreach ($PROCESSED["evaluation_form_categories"] as $index => $category) {
									$PROCESSED["question_order"]++;
									$PROCESSED_QUESTION = array("eform_id" => $FORM_ID,
																"questiontype_id" => 3,
																"question_text" => $category["category"],
																"question_order" => $PROCESSED["question_order"],
																"allow_comments" => $PROCESSED["allow_comments"]);
									$efquestion_id = 0;
									if ($db->AutoExecute("evaluation_form_questions", $PROCESSED_QUESTION, "INSERT") && ($efquestion_id = $db->Insert_Id()) &&
											$db->AutoExecute("evaluation_form_rubric_questions", array("efrubric_id" => $efrubric_id, "efquestion_id" => $efquestion_id), "INSERT")) {
										/**
										 * Add the question responses to the evaluation_form_responses table.
										 * Ummm... we really need to switch to InnoDB tables to get transaction support.
										 */
										if ((is_array($PROCESSED["evaluation_form_responses"])) && (count($PROCESSED["evaluation_form_responses"]))) {
											foreach ($PROCESSED["evaluation_form_responses"] as $subindex => $form_question_response) {
												$PROCESSED_RESPONSE = array (
																"efquestion_id" => $efquestion_id,
																"response_text" => $form_question_response["response_text"],
																"response_order" => $form_question_response["response_order"],
																"response_is_html" => $form_question_response["response_is_html"],
																"minimum_passing_level"	=> $form_question_response["minimum_passing_level"]
																);
												$efresponse_id = 0;
												if ($db->AutoExecute("evaluation_form_responses", $PROCESSED_RESPONSE, "INSERT") && ($efresponse_id = $db->Insert_Id())) {
													/**
													 * Add the responses criteria to the evaluation_form_rubric_criteria table.
													 */
													if ((isset($PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"])) && ($PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"] || $PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"] === "")) {
														$PROCESSED_CRITERIA = array (
																		"efresponse_id" => $efresponse_id,
																		"criteria_text" => $PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"],
																		);

														if (!$db->AutoExecute("evaluation_form_response_criteria", $PROCESSED_CRITERIA, "INSERT")) {
															add_error("There was an error while trying to attach a <strong>Criteria</strong> to this form question.<br /><br />The system administrator was informed of this error; please try again later.");

															application_log("error", "Unable to insert a new evaluation_form_rubric_criteria record while adding a new evaluation form question [".$efquestion_id."] to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
														}
													}
												} else {
													add_error("There was an error while trying to attach a <strong>Question Response</strong> to this form question.<br /><br />The system administrator was informed of this error; please try again later.");

													application_log("error", "Unable to insert a new evaluation_form_responses record while adding a new evaluation form question [".$efquestion_id."] to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
												}
											}
										}

										switch ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
											case "new" :
												$url = ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&section=add";
												$msg = "You will now be redirected to add another question to this evaluation form; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
											break;
											case "index" :
												$url = ENTRADA_URL."/admin/evaluations/forms";
												$msg = "You will now be redirected back to the evaluation form index page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
											break;
											case "content" :
											default :
												$url = ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$FORM_ID;
												$msg = "You will now be redirected back to the evaluation form; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
											break;
										}
									} else {
										add_error("There was a problem inserting this evaluation form question. The system administrator was informed of this error; please try again later.");

										application_log("error", "There was an error inserting an evaluation form question to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
									}
								}
								if (!has_error()) {
									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully added this question to the <strong>".html_encode($FORM_RECORD["form_title"])."</strong> evaluation form.<br /><br />".$msg;
									$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";


									application_log("success", "New evaluation form question [".$efquestion_id."] added to eform_id [".$FORM_ID."].");
								}
							}
						} else {
							if ($db->AutoExecute("evaluation_form_questions", $PROCESSED, "INSERT") && ($efquestion_id = $db->Insert_Id())) {
								/**
								 * Add the question responses to the evaluation_form_responses table.
								 * Ummm... we really need to switch to InnoDB tables to get transaction support.
								 */
								if ((is_array($PROCESSED["evaluation_form_responses"])) && (count($PROCESSED["evaluation_form_responses"]))) {
									foreach ($PROCESSED["evaluation_form_responses"] as $form_question_response) {
										$PROCESSED = array (
														"efquestion_id" => $efquestion_id,
														"response_text" => $form_question_response["response_text"],
														"response_order" => $form_question_response["response_order"],
														"response_is_html" => $form_question_response["response_is_html"],
														"minimum_passing_level"	=> $form_question_response["minimum_passing_level"]
														);

										if (!$db->AutoExecute("evaluation_form_responses", $PROCESSED, "INSERT")) {
											add_error("There was an error while trying to attach a <strong>Question Response</strong> to this form question.<br /><br />The system administrator was informed of this error; please try again later.");

											application_log("error", "Unable to insert a new evaluation_form_responses record while adding a new evaluation form question [".$efquestion_id."] to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
										}
									}
								}

								switch ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "new" :
										$url = ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&section=add";
										$msg = "You will now be redirected to add another question to this evaluation form; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
										$url = ENTRADA_URL."/admin/evaluations/forms";
										$msg = "You will now be redirected back to the evaluation form index page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "content" :
									default :
										$url = ENTRADA_URL."/admin/evaluations/forms?section=edit&id=".$FORM_ID;
										$msg = "You will now be redirected back to the evaluation form; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}

								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully added this question to the <strong>".html_encode($FORM_RECORD["form_title"])."</strong> evaluation form.<br /><br />".$msg;
								$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

								/**
								 * Unset the arrays used to construct this error checking.
								 */
								unset($PROCESSED);

								application_log("success", "New evaluation form question [".$efquestion_id."] added to eform_id [".$FORM_ID."].");
							} else {
								add_error("There was a problem inserting this evaluation form question. The system administrator was informed of this error; please try again later.");

								application_log("error", "There was an error inserting an evaluation form question to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
							}
						}
					} else {
						add_error("You do not have permission to create this evaluation form question. The system administrator was informed of this error; please try again later.");

						application_log("error", "There was an error inserting an evaluation form question to eform_id [".$FORM_ID."] because the user [".$ENTRADA_USER->getID()."] didn't have permission to create a form question.");
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
				if (has_error() || has_notice()) {
					echo display_status_messages();
				}
				require_once("javascript/evaluations.js.php");
				?>
				<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions?id=<?php echo $FORM_ID; ?>&amp;section=add&amp;step=2" method="post" id="addEvaluationFormQuestionForm">
				<table style="width: 100%; margin-bottom: 25px" cellspacing="0" cellpadding="2" border="0" summary="Add Evaluation Form Question">
				<colgroup>
					<col style="width: 20%" />
					<col style="width: 80%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="2" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="width: 25%; text-align: left">
									<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=edit&id=<?php echo $FORM_ID; ?>'" />
								</td>
								<td style="width: 75%; text-align: right; vertical-align: middle">
									<span class="content-small">After saving:</span>
									<select id="post_action" name="post_action">
										<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Return to the evaluation form</option>
										<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another question</option>
										<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to evaluation form index</option>
									</select>

									<input type="submit" class="button" value="Proceed" />
								</td>
							</tr>
							</table>
						</td>
					</tr>
				</tfoot>
				<tbody id="form-content-add-question">
					<tr>
						<td style="vertical-align: top">
							<label for="questiontype_id" class="form-required">Question Type</label>
						</td>
						<td>
							<select onchange="window.location = '<?php echo ENTRADA_URL ?>/admin/evaluations/forms/questions?id=<?php echo $FORM_ID; ?>&section=add&qtype_id='+this.options[this.selectedIndex].value" name="questiontype_id" id="questiontype_id">
								<option value="0"> --- Choose a question type --- </option>
								<?php
									$query = "SELECT * FROM `evaluations_lu_questiontypes`
												WHERE `questiontype_active` = 1";
									$questiontypes = $db->GetAll($query);
									if ($questiontypes) {
										foreach ($questiontypes as $questiontype) {
											echo "<option ".((isset($PROCESSED["questiontype_id"]) && $PROCESSED["questiontype_id"] == $questiontype["questiontype_id"]) || ((!isset($PROCESSED["questiontype_id"]) || !$PROCESSED["questiontype_id"]) && $questiontype["questiontype_id"] == 1) ? "selected=\"selected\" " : "")."value=\"".$questiontype["questiontype_id"]."\">".$questiontype["questiontype_title"]."</option>\n";
										}
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
						</td>
					</tr>
					<?php
						echo Evaluation::getEditQuestionControls($PROCESSED);
					?>
				</tbody>
				</table>
				</form>
				<?php
			break;
		}
	} else {
		add_error("You cannot add a question to an evaluation form that has already been used. This precaution exists to protect the integrity of the data in the database.<br /><br />If you would like to add questions to this form you can <strong>copy the form</strong> from the <strong>Manage Forms</strong> index.");

		echo display_error();

		application_log("error", "Attempted to add a question to an evaluation form [".$FORM_ID."] that has already been used.");
	}
}