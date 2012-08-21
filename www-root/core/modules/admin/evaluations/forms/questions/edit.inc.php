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
 * This file is used by quiz authors to add / edit or remove quiz questions
 * from a particular quiz.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/
ini_set("display_errors", 1);
if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationformquestion", "update", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$RECORD_ID = 0;
	if (isset($_GET["record"]) && ($tmp_input = clean_input($_GET["record"], "int"))) {
		$RECORD_ID = $tmp_input;
	} elseif (isset($_POST["record"]) && ($tmp_input = clean_input($_POST["record"], "int"))) {
		$RECORD_ID = $tmp_input;
	}
	if ($FORM_ID && $RECORD_ID) {
		$query = "	SELECT a.`form_title`, a.`form_description`, b.*
					FROM `evaluation_forms` AS a
					LEFT JOIN `evaluation_form_questions` AS b
					ON b.`eform_id` = b.`eform_id`
					WHERE a.`eform_id` = ".$db->qstr($FORM_ID)."
					AND a.`form_active` = '1'
					AND b.`efquestion_id` = ".$db->qstr($RECORD_ID);
		$question_record = $db->GetRow($query);
		if ($question_record && $ENTRADA_ACL->amIAllowed(new EvaluationFormResource($FORM_ID), "update")) {
			if ($question_record["questiontype_id"] == 3) {
				$query = "SELECT `efrubric_id` FROM `evaluation_form_rubric_questions`
							WHERE `efquestion_id` = ".$db->qstr($RECORD_ID);
				$efrubric_id = $db->GetOne($query);
				if ($efrubric_id) {
					$query = "SELECT * FROM `evaluation_form_rubrics`
								WHERE `efrubric_id` = ".$db->qstr($efrubric_id);
					$rubric = $db->GetRow($query);
					if ($rubric) {
						$question_record["question_title"] = $rubric["rubric_title"];
						$PROCESSED["rubric_title"] = $rubric["rubric_title"];
						$PROCESSED["rubric_description"] = $rubric["rubric_description"];
					}
					$query = "SELECT * FROM `evaluation_form_rubric_questions` AS a
								JOIN `evaluation_form_questions` AS b
								ON a.`efquestion_id` = b.`efquestion_id`
								WHERE a.`efrubric_id` = ".$db->qstr($efrubric_id)."
								AND b.`questiontype_id` = 3
								ORDER BY b.`question_order` ASC";
					$categories = $db->GetAll($query);
					if ($categories) {
						$PROCESSED["evaluation_form_categories"] = array();
						$PROCESSED["evaluation_form_category_criteria"] = array();
						foreach ($categories as $index => $category) {
							$PROCESSED["evaluation_form_categories"][$index + 1] = array();
							$PROCESSED["evaluation_form_categories"][$index + 1]["category"] = $category["question_text"];

							$query = "SELECT * FROM `evaluation_form_responses`
										WHERE `efquestion_id` = ".$db->qstr($category["efquestion_id"])."
										ORDER BY `response_order` ASC";
							$columns = $db->GetAll($query);
							if ($columns) {
								$PROCESSED["evaluation_form_responses"] = array();
								$PROCESSED["evaluation_form_category_criteria"][$index + 1] = array();
								$PROCESSED["columns_count"] = count($columns);
								foreach ($columns as $cindex => $column) {

									$PROCESSED["evaluation_form_responses"][$cindex + 1] = $column;

									$query = "SELECT * FROM `evaluation_form_response_criteria`
												WHERE `efresponse_id` = ".$db->qstr($column["efresponse_id"]);
									$criteria = $db->GetRow($query);
									if ($criteria) {
										$PROCESSED["evaluation_form_category_criteria"][$index + 1][$cindex + 1] = array();
										$PROCESSED["evaluation_form_category_criteria"][$index + 1][$cindex + 1]["criteria"] = $criteria["criteria_text"];
									}
								}
							}
						}
						$PROCESSED["categories_count"] = count($categories);
					}
				}
			}
			if ($ALLOW_QUESTION_MODIFICATIONS) {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$question_record["efquestion_id"], "title" => limit_chars($question_record["question_title"], 32));
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=edit-question&id=".$FORM_ID, "title" => "Edit Evaluation Question");
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
					$PROCESSED["questiontype_id"] = $question_record["questiontype_id"];
				}
				// Error Checking
				switch ($STEP) {
					case 2 :
						/**
						 * Add the eform_id of the form this question will be added to.
						 */
						$PROCESSED["eform_id"] = $question_record["eform_id"];
						//If Rubric question type.
						if ($PROCESSED["questiontype_id"] == 3) {
							/**
							 * Required field "rubric_title" / Rubric Title.
							 */
							if ((isset($_POST["rubric_title"])) && ($tmp_input = clean_input($_POST["rubric_title"], array("trim")))) {
								$PROCESSED["rubric_title"] = $tmp_input;
							} else {
								add_error("The <strong>Rubric Title</strong> field is required.");
							}
							/**
							 * Required field "rubric_description" / Rubric Description.
							 */
							if ((isset($_POST["rubric_description"])) && ($tmp_input = clean_input($_POST["rubric_description"], array("trim", "allowedtags")))) {
								$PROCESSED["rubric_description"] = $tmp_input;
							} else {
								$PROCESSED["rubric_description"] = "";
							}
							
							if ((isset($_POST["categories_count"])) && ($tmp_input = clean_input($_POST["categories_count"], array("int")))) {
								$PROCESSED["categories_count"] = $tmp_input;
							}
						} else {
							/**
							 * Required field "question_text" / Form Question.
							 */
							if ((isset($_POST["question_text"])) && ($tmp_input = clean_input($_POST["question_text"], array("trim", "allowedtags")))) {
								$PROCESSED["question_text"] = $tmp_input;
							} else {
								add_error("The <strong>Form Question</strong> field is required.");
							}
						}

						/**
						 * Required field "response_text" / Available Responses.
						 *
						 */
						$minimum_passing_level_found = false;
						$PROCESSED["evaluation_form_responses"] = array();
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
						if ((isset($_POST["category"])) && (is_array($_POST["category"]))) {
							$i = 1;
							$PROCESSED["evaluation_form_categories"] = array();
							foreach ($_POST["category"] as $category_key => $category) {
								$PROCESSED["evaluation_form_category_criteria"][$i] = array();
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

											$criteria = clean_input($criteria, array("trim"));

											if (($criteria_key) && ($criteria != "")) {
												if (is_array($PROCESSED["evaluation_form_category_criteria"][$i]) && !empty($PROCESSED["evaluation_form_category_criteria"][$i])) {
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
							add_error("You must provide 2 responses in the <strong>Available Responses</strong> section.");
						}

						/**
						 * You must specify the minimum passing level
						 */
						if (!$minimum_passing_level_found) {
							add_error("You must specify which of the responses is the <strong>minimum passing level</strong>.");
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
							if($ENTRADA_ACL->amIAllowed(new EvaluationFormQuestionResource(null, $question_record["eform_id"]), "create")) {
								if ($PROCESSED["questiontype_id"] == 3) {
									/**
									 * Get the next order of this question from the evaluation_form_questions table.
									 */
									$query	= "SELECT MIN(b.`question_order`) AS `first_order` FROM `evaluation_form_rubric_questions` AS a
												JOIN `evaluation_form_questions` AS b
												ON a.`efquestion_id` = b.`efquestion_id`
												WHERE a.`efrubric_id` = ".$db->qstr($efrubric_id);
									$result = $db->GetRow($query);
									if ($result) {
										$PROCESSED["question_order"] = ($result["first_order"]);
									} else {
										$PROCESSED["question_order"] = 1;
									}
									$starting_order = $PROCESSED["question_order"];
									$updated_question_ids_string = "";

									if ($db->AutoExecute("evaluation_form_rubrics", $PROCESSED, "UPDATE", "efrubric_id = ".$efrubric_id)) {
										$query = "SELECT b.* FROM `evaluation_form_rubric_questions` AS a
													JOIN `evaluation_form_questions` AS b
													ON a.`efquestion_id` = b.`efquestion_id`
													WHERE a.`efrubric_id` = ".$db->qstr($efrubric_id)."
													ORDER BY b.`question_order` ASC";
										$categories = $db->GetAll($query);
										foreach ($categories as $category) {
											$db->Execute("DELETE FROM `evaluation_form_questions` WHERE `efquestion_id` = ".$db->qstr($category["efquestion_id"]));
											$db->Execute("DELETE FROM `evaluation_form_rubric_questions` WHERE `efquestion_id` = ".$db->qstr($category["efquestion_id"]));
										}
										foreach ($PROCESSED["evaluation_form_categories"] as $index => $category) {
											$PROCESSED_QUESTION = array("eform_id" => $FORM_ID,
																		"questiontype_id" => 3,
																		"question_text" => $PROCESSED["evaluation_form_categories"][$index]["category"],
																		"question_order" => $PROCESSED["question_order"]);
											$efquestion_id = 0;
											$PROCESSED["question_order"]++;
											if ($db->AutoExecute("evaluation_form_questions", $PROCESSED_QUESTION, "INSERT") && ($efquestion_id = $db->Insert_Id()) &&
													$db->AutoExecute("evaluation_form_rubric_questions", array("efrubric_id" => $efrubric_id, "efquestion_id" => $efquestion_id), "INSERT")) {
												$updated_question_ids_string .= ($updated_question_ids_string ? ", " : "").$db->qstr($efquestion_id);
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
															if ((isset($PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"])) && ($PROCESSED["evaluation_form_category_criteria"][$index][$subindex]["criteria"])) {
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
											} else {
												add_error("There was a problem inserting this evaluation form question. The system administrator was informed of this error; please try again later.");

												application_log("error", "There was an error inserting an evaluation form question to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
											}
										}
										if (!has_error()) {
											$query = "SELECT * FROM `evaluation_form_questions` 
														WHERE `efquestion_id` NOT IN (".$updated_question_ids_string.") 
														AND `question_order` >= ".$db->qstr($starting_order)." 
														AND `eform_id` = ".$db->qstr($FORM_ID)."
														ORDER BY `question_order` ASC";
											$moving_questions = $db->GetAll($query);
											if ($moving_questions) {
												foreach ($moving_questions as $question) {
													$question["question_order"] = $PROCESSED["question_order"];
													$PROCESSED["question_order"]++;
													$db->AutoExecute("evaluation_form_questions", $question, "UPDATE", "efquestion_id = ".$question["efquestion_id"]);
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


											application_log("success", "New evaluation form question [".$efquestion_id."] added to eform_id [".$FORM_ID."].");
										}
									} else {
										add_error("There was a problem inserting this evaluation form rubric. The system administrator was informed of this error; please try again later.");

										application_log("error", "There was an error inserting an evaluation form rubric to eform_id [".$FORM_ID."]. Database said: ".$db->ErrorMsg());
									}
								} else {
									if ($db->AutoExecute("evaluation_form_questions", $PROCESSED, "UPDATE", "`efquestion_id` = ".$db->qstr($RECORD_ID))) {
										/**
										 * Add the question responses to the evaluation_form_responses table.
										 * Ummm... we really need to switch to InnoDB tables to get transaction support.
										 */
										if ((is_array($PROCESSED["evaluation_form_responses"])) && (count($PROCESSED["evaluation_form_responses"]))) {
											/**
											 * Delete the old responses and add the updated ones.
											 */
											$db->Execute("DELETE FROM `evaluation_form_responses` WHERE `efquestion_id` = ".$db->qstr($RECORD_ID));

											foreach ($PROCESSED["evaluation_form_responses"] as $form_question_response) {
												$PROCESSED = array (
																"efquestion_id" => $RECORD_ID,
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
										$SUCCESSSTR[] = "You have successfully added this question to the <strong>".html_encode($question_record["form_title"])."</strong> evaluation form.<br /><br />".$msg;
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
						switch ($question_record["questiontype_id"]) {
							case 3 :
							break;
							case 2 :
							break;
							case 1 :
							default :
								$PROCESSED = $question_record;
								$PROCESSED["evaluation_form_responses"] = array();

								$query = "	SELECT a.*
											FROM `evaluation_form_responses` AS a
											WHERE a.`efquestion_id` = ".$db->qstr($RECORD_ID)."
											ORDER BY a.`response_order` ASC";
								$results = $db->GetAll($query);
								if ($results) {
									$i = 1;

									foreach ($results as $result) {
										$PROCESSED["evaluation_form_responses"][$i]["response_order"] = $result["response_order"];
										$PROCESSED["evaluation_form_responses"][$i]["response_correct"] = $result["response_correct"];
										$PROCESSED["evaluation_form_responses"][$i]["response_is_html"] = $result["response_is_html"];
										$PROCESSED["evaluation_form_responses"][$i]["minimum_passing_level"] = $result["minimum_passing_level"];

										if ($result["response_is_html"]) {
											$response_text = clean_input($result["response_text"], array("trim", "allowedtags"));
										} else {
											$response_text = clean_input($result["response_text"], array("trim"));
										}

										$PROCESSED["evaluation_form_responses"][$i]["response_text"] = $response_text;

										$i++;
									}
								}
							break;
						}
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
						<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions?id=<?php echo $FORM_ID; ?>&amp;section=edit&amp;record=<?php echo $RECORD_ID; ?>&amp;step=2" method="post" id="editEvaluationFormQuestionForm">
						<table style="width: 100%; margin-bottom: 25px" cellspacing="0" cellpadding="2" border="0" summary="Edit Evaluation Form Question">
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
											<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=edit&amp;id=<?php echo $FORM_ID; ?>'" />
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
				add_error("You cannot edit a question in an evaluation form that has already been used. This precaution exists to protect the integrity of the data in the database.<br /><br />If you would like to modify questions in this evaluation form you can <strong>copy the form</strong> from the <strong>Manage Forms</strong> index.");
	
				echo display_error();
	
				application_log("error", "Attempted to edit a question in an evaluation form [".$FORM_ID."] that has already been used.");
			}
		} else {
			add_error("In order to edit a question in an evaluation form you must provide a valid identifier.");

			echo display_error();

			application_log("notice", "Failed to provide a valid identifer [".$FORM_ID."] when attempting to edit an evaluation form question.");
		}
	} else {
		add_error("In order to edit a question in an evaluation form you must provide an identifier.");

		echo display_error();

		application_log("notice", "Failed to provide an identifier when attempting to edit an evaluation form question.");
	}
}