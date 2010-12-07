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
 * @author Developer:  Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

if ($MAILING_LISTS["active"]) {
	require_once("Entrada/mail-list/mail-list.class.php");
}

$EVALUATION_ID		= 0;
$PREFERENCES		= preferences_load($MODULE);
$PROXY_IDS			= array();

/**
 * Check for a community category to proceed.
 */
if((isset($_GET["evaluation"])) && ((int) trim($_GET["evaluation"]))) {
	$EVALUATION_ID	= (int) trim($_GET["evaluation"]);
} elseif((isset($_POST["evaluation_id"])) && ((int) trim($_POST["evaluation_id"]))) {
	$EVALUATION_ID	= (int) trim($_POST["evaluation_id"]);
} elseif((isset($_POST["evaluation"])) && ((int) trim($_POST["evaluation"]))) {
	$EVALUATION_ID	= (int) trim($_POST["evaluation"]);
}


if((isset($_GET["type"])) && ($tmp_action_type = clean_input(trim($_GET["type"]), "alphanumeric"))) {
	$ACTION_TYPE	= $tmp_action_type;
} elseif((isset($_POST["type"])) && ($tmp_action_type = clean_input(trim($_POST["type"]), "alphanumeric"))) {
	$ACTION_TYPE	= $tmp_action_type;
}
unset($tmp_action_type);


/**
 * Ensure that the selected community is editable by you.
 */
if($EVALUATION_ID) {
	$query				= "SELECT * FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
	$evaluation_details	= $db->GetRow($query);
	if($evaluation_details) {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
		$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
		$HEAD[]	= "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
                $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
                $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

		$HEAD[]	= "<link href=\"".ENTRADA_URL."/css/tree.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[]	= "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";

		$BREADCRUMB[]		= array("url" => ENTRADA_URL."/community".$evaluation_details["community_url"], "title" => limit_chars($evaluation_details["community_title"], 50));
		$BREADCRUMB[]		= array("url" => ENTRADA_URL."/communities?".replace_query(), "title" => "Manage Members");

			echo "<h1>".html_encode($evaluation_details["community_title"])."</h1>\n";

			// Error Checking
			switch($STEP) {
				case 3 :
				case 2 :
					switch($ACTION_TYPE) {
						case "addgroup" :
                                                    if (isset($_POST["eval_evaluator_type"])) {
                                                        $PROCESSED["eval_evaluator_type"] = clean_input($_POST["eval_evaluator_type"], array("page_url"));

                                                        switch($PROCESSED["eval_evaluator_type"]) {
                                                                case "grad_year" :
                                                                        /**
                                                                         * Required field "associated_grad_year" / Graduating Year
                                                                         * This data is inserted into the eval_evaluator table as grad_year.
                                                                         */
                                                                        if ((isset($_POST["associated_grad_year"])) && ($associated_grad_year = clean_input($_POST["associated_grad_year"], array("trim", "int")))) {
                                                                                $PROCESSED["associated_grad_year"] = $associated_grad_year;
                                                                        } else {
                                                                                $ERROR++;
                                                                                $ERRORSTR[] = "You have chosen <strong>Entire Class Evaluation</strong> as an <strong>Evaluation Evaluator</strong> type, but have not selected a graduating year.";
                                                                        }

                                                                        if ((isset($_POST["random_number"])) && ($random_number = clean_input($_POST["random_number"], array("trim", "int")))) {
                                                                                $PROCESSED["random_number"] = $random_number;
                                                                        } else {
                                                                                $ERROR++;
                                                                                $ERRORSTR[] = "You have chosen <strong>Entire Class Evaluation</strong> as an <strong>Evaluation Evaluator</strong> type, but have not selected a random number.";
                                                                        }
                                                                break;
                                                                case "organisation_id":
                                                                        if ((isset($_POST["associated_organisation_id"])) && ($associated_organisation_id = clean_input($_POST["associated_organisation_id"], array("trim", "int")))) {
                                                                                if ($ENTRADA_ACL->amIAllowed('resourceorganisation'.$associated_organisation_id, 'create')) {
                                                                                        $PROCESSED["associated_organisation_id"] = $associated_organisation_id;
                                                                                } else {
                                                                                        $ERROR++;
                                                                                        $ERRORSTR[] = "You do not have permission to add an evaluation for this organisation, please select a different one.";
                                                                                }
                                                                        } else {
                                                                                $ERROR++;
                                                                                $ERRORSTR[] = "You have chosen <strong>Entire Organisation </strong> as an <strong>Evaluation Evaluator</strong> type, but have not selected an organisation.";
                                                                        }
                                                                break;
                                                                default :
                                                                        $ERROR++;
                                                                        $ERRORSTR[] = "Unable to proceed because the <strong>Event Audience</strong> type is unrecognized.";

                                                                        application_log("error", "Unrecognized eval_evaluator_type [".$_POST["eval_evaluator_type"]."] encountered.");
                                                                break;
                                                                }
                                                            } else {
                                                                $ERROR++;
                                                                $ERRORSTR[] = "Unable to proceed because the <strong>Evaluation Group</strong> type is unrecognized.";

                                                                application_log("error", "The eval_evaluator_type field has not been set.");
                                                            }
                                                        $member_add_success	= 0;
							$member_add_failure	= 0;
                                                        switch($PROCESSED["eval_evaluator_type"]) {
                                                                case "grad_year" :
                                                                        /**
                                                                         * If there are any graduating years associated with this event,
                                                                         * add it to the eval_evaluator table.
                                                                         */
                                                                        if ($PROCESSED["associated_grad_year"]) {
                                                                            if ($PROCESSED["random_number"]==100){
                                                                                if ($db->AutoExecute("evaluation_evaluators", array("evaluation_id" => $EVALUATION_ID, "evaluator_type" => "grad_year", "evaluator_value" => $PROCESSED["associated_grad_year"], "member_joined" => 1, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
                                                                                    $member_add_success++;
										} else {
                                                                                    $member_add_failure++;
                                                                                    application_error("error", "Unable to insert a new evaluator. Database said: ".$db->ErrorMsg());
										}
                                                                            }else{

                                                                            }
                                                                            $snd_stm= " FROM `".AUTH_DATABASE."`.`user_data`
                                                                                LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `".AUTH_DATABASE."`.`user_access`.`user_id`=`".AUTH_DATABASE."`.`user_data`.`id`
                                                                                    WHERE `".AUTH_DATABASE."`.`user_access`.`app_id`='".AUTH_APP_ID."'
                                                                                        AND `role`=".$db->qstr($PROCESSED["associated_grad_year"])."
                                                                                            AND `group`='student'";
                                                                            $query	= "SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`".$snd_stm;

                                                                            $query_count = "SELECT count(*) AS `total_rows`".$snd_stm;
                                                                            $result_count = $db->GetRow($query_count);
                                                                            $i_total_rows= (int) $result_count["total_rows"];
                                                                            $i_random_number= (int) $PROCESSED["random_number"];

                                                                            $random_percentage= round($i_total_rows*$i_random_number/100);
                                                                            $query= $query." ORDER BY RAND() limit ".$random_percentage;

                                                                            $results = $db->GetAll($query);
                                                                            foreach($results as $result) {
                                                                                if(($proxy_id = (int) trim($result["proxy_id"]))) {
                                                                                        if ($db->AutoExecute("evaluation_evaluators", array("evaluation_id" => $EVALUATION_ID, "evaluator_type" => "proxy_id", "evaluator_value" => $proxy_id, "member_joined" => 1, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
                                                                                            $member_add_success++;
                                                                                        } else {
                                                                                            $member_add_failure++;
                                                                                            application_error("error", "Unable to insert a new evaluator. Database said: ".$db->ErrorMsg());
                                                                                        }
                                                                                    }
                                                                            }
                                                                        }

                                                                        if($member_add_success) {
                                                                                $SUCCESS++;
                                                                                $SUCCESSSTR[] = "You have successfully attached ".$member_add_success." Graduating Year".(($member_add_success != 1) ? "s" : "")." to this evaluation.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";

                                                                                communities_log_history($EVALUATION_ID, 0, $member_add_success, "community_history_add_members", 1);
                                                                        }
                                                                        if($member_add_failure) {
                                                                                $NOTICE++;
                                                                                $NOTICESTR[] = "Failed to add or update".$member_add_failure." member".(($member_add_failure != 1) ? "s" : "")." during this process. The MEdTech Unit has been informed of this error, please try again later.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
                                                                        }
                                                                break;
                                                                case "organisation_id":
                                                                        if (isset($PROCESSED["associated_organisation_id"])) {
                                                                                if (!$db->AutoExecute("eval_evaluator", array("event_id" => $EVENT_ID, "audience_type" => "organisation_id", "audience_value" => $PROCESSED["associated_organisation_id"], "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
                                                                                        $ERROR++;
                                                                                        $ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
                                                                                        application_log("error", "Unable to insert a new eval_evaluator, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
                                                                                }
                                                                        }
                                                                break;
                                                                default :
                                                                        application_log("error", "Unrecognized eval_evaluator_type [".$_POST["eval_evaluator_type"]."] encountered, no audience added for event_id [".$EVENT_ID."].");
                                                                break;
                                                        }
							break;
						case "add" :
							$member_add_success	= 0;
							$member_add_failure	= 0;
							if((isset($_POST["acc_evaluator_members"])) && ($proxy_ids = explode(',', $_POST["acc_evaluator_members"])) && (count($proxy_ids))) {
								if ($MAILING_LISTS["active"]) {
									$mail_list = new MailingList($EVALUATION_ID);
								}

								foreach($proxy_ids as $proxy_id) {
									if(($proxy_id = (int) trim($proxy_id))) {
												$PROCESSED = array();
												$PROCESSED["evaluation_id"]	= $EVALUATION_ID;
												$PROCESSED["evaluator_type"]	= "proxy_id";
												$PROCESSED["evaluator_value"]	= $proxy_id;

												if(@$db->AutoExecute("evaluation_evaluators", $PROCESSED, "INSERT")) {
													$member_add_success++;
												} else {
													$member_add_failure++;
													application_error("error", "Unable to insert a new evaluator. Database said: ".$db->ErrorMsg());
												}
									}
								}
							}

							if($member_add_success) {
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully added ".$member_add_success." new evaluator".(($member_add_success != 1) ? "s" : "")." to this evaluation.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";

								communities_log_history($EVALUATION_ID, 0, $member_add_success, "community_history_add_members", 1);
							}
							if($member_add_failure) {
								$NOTICE++;
								$NOTICESTR[] = "Failed to add or update".$member_add_failure." member".(($member_add_failure != 1) ? "s" : "")." during this process. The MEdTech Unit has been informed of this error, please try again later.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
							}
							break;
						case "addtarget" :
							$member_add_success	= 0;
							$member_add_failure	= 0;
                                                        if ((isset($_POST["target_type_added"])) && ($random_number = clean_input($_POST["target_type_added"], array("trim", "int")))) {
                                                                $target_type_added = $random_number;
                                                        } else {
                                                                $ERROR++;
                                                                $ERRORSTR[] = "Target type is invalid.";
                                                        }
							if((isset($_POST["acc_target_members"])) && ($proxy_ids = explode(',', $_POST["acc_target_members"])) && (count($proxy_ids))) {
								if ($MAILING_LISTS["active"]) {
									$mail_list = new MailingList($EVALUATION_ID);
								}

								foreach($proxy_ids as $proxy_id) {
									if(($proxy_id = (int) trim($proxy_id))) {
												$PROCESSED = array();
												$PROCESSED["evaluation_id"]	= $EVALUATION_ID;
												$PROCESSED["target_id"]	= $target_type_added;
												$PROCESSED["target_value"]	= $proxy_id;
												$PROCESSED["target_active"]	= "1";
                                                                                                $PROCESSED["updated_date"]	= time();
                                                                                                $PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

												if(@$db->AutoExecute("evaluation_targets", $PROCESSED, "INSERT")) {
													$member_add_success++;
												} else {
													$member_add_failure++;
													application_error("error", "Unable to insert a new target. Database said: ".$db->ErrorMsg());
												}
									}
								}
							}

							if($member_add_success) {
								$SUCCESS++;
								$SUCCESSSTR[] = "You have successfully added ".$member_add_success." new targets".(($member_add_success != 1) ? "s" : "")." to this evaluation.<br /><br />You will now be redirected back to the Manage Targets page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";

								communities_log_history($EVALUATION_ID, 0, $member_add_success, "community_history_add_members", 1);
							}
							if($member_add_failure) {
								$NOTICE++;
								$NOTICESTR[] = "Failed to add or update".$member_add_failure." member".(($member_add_failure != 1) ? "s" : "")." during this process. The MEdTech Unit has been informed of this error, please try again later.<br /><br />You will now be redirected back to the Manage Targets page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
							}
							break;
						case "members" :

							if((isset($_POST["member_action"])) && (@in_array(strtolower($_POST["member_action"]), array("delete", "deactivate", "promote")))) {
								if((isset($_POST["member_proxy_ids"])) && (is_array($_POST["member_proxy_ids"])) && (count($_POST["member_proxy_ids"]))) {
                                                                    /***
									foreach($_POST["member_proxy_ids"] as $proxy_id) {
                                                                            if(strlen(trim($proxy_id))>7 && substr(trim($proxy_id),0,6)="student"){
                                                                                echo "step 02<br>";

                                                                            }else{
                                                                                echo "step 01<br>";
										if($proxy_id = (int) trim($proxy_id)) {
											$query	= "
													SELECT a.*
													FROM `".AUTH_DATABASE."`.`user_data` AS a
													LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
													ON b.`user_id` = ".$db->qstr($proxy_id)."
													AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
													WHERE a.`id` = ".$db->qstr($proxy_id);
											$result	= $db->GetRow($query);
											if($result) {
												$PROXY_IDS[] = $proxy_id;
											}
										}
                                                                            }
									}
                                                                     *
                                                                     */
                                                                    foreach($_POST["member_proxy_ids"] as $proxy_id) {
                                                                        $PROXY_IDS[] = (int) trim($proxy_id);
                                                                    }
								}

								if((is_array($PROXY_IDS)) && (count($PROXY_IDS))) {
									switch(strtolower($_POST["member_action"])) {
										case "delete" :
											$query	= "DELETE FROM `evaluation_evaluators` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)." AND `eevaluator_id` IN ('".implode("', '", $PROXY_IDS)."')";
											$result	= $db->Execute($query);
											if(($result) && ($total_deleted = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->deactivate_member($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully removed <strong>".$total_deleted." evaluator".(($total_deleted != 1) ? "s" : "")."</strong> from the <strong>".html_encode($evaluation_details["evaluation_title"])."</strong> evaluation.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem removing these evaluation evaluators from the system; the MEdTech Unit has been informed of this error, please try again later.";

												application_log("error", "Unable to remove evaluators from evaluation_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
											}
											break;
										default :
										/**
										 * This should never happen, as I'm checking the member_action above.
										 */
											continue;
											break;
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "In order to complete this action, you will need to select at least 1 user from the list.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "Unrecognized Evaluator Action error; the MEdTech Unit has been informed of this error. Please try again later.";

								application_log("error", "The provided action_type [".$ACTION_TYPE."] is invalid.");
							}
							break;
						case "targets" :
							if((isset($_POST["target_action"])) && (@in_array(strtolower($_POST["target_action"]), array("delete", "deactivate", "promote")))) {
								if((isset($_POST["member_proxy_ids"])) && (is_array($_POST["member_proxy_ids"])) && (count($_POST["member_proxy_ids"]))) {
                                                                    foreach($_POST["member_proxy_ids"] as $proxy_id) {
                                                                        $PROXY_IDS[] = (int) trim($proxy_id);
                                                                    }
								}

								if((is_array($PROXY_IDS)) && (count($PROXY_IDS))) {
									switch(strtolower($_POST["target_action"])) {
										case "delete" :
											$query	= "DELETE FROM `evaluation_targets` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)." AND `etarget_id` IN ('".implode("', '", $PROXY_IDS)."')";
											$result	= $db->Execute($query);
											if(($result) && ($total_deleted = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->deactivate_member($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully removed <strong>".$total_deleted." target".(($total_deleted != 1) ? "s" : "")."</strong> from the <strong>".html_encode($evaluation_details["evaluation_title"])."</strong> evaluation.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem removing these evaluation targets from the system; the MEdTech Unit has been informed of this error, please try again later.";

												application_log("error", "Unable to remove targets from evaluation_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
											}
											break;
										default :
										/**
										 * This should never happen, as I'm checking the member_action above.
										 */
											continue;
											break;
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "In order to complete this action, you will need to select at least 1 user from the list.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "Unrecognized Target Action error; the MEdTech Unit has been informed of this error. Please try again later.";

								application_log("error", "The provided action_type [".$ACTION_TYPE."] is invalid.");
							}
							break;
						default :
							$ERROR++;
							$ERRORSTR[] = "Unrecognized Action Type selection; the MEdTech Unit has been informed of this error. Please try again later.";

							application_log("error", "The provided action_type [".$ACTION_TYPE."] is invalid.");
							break;
					}

					if($ERROR) {
						$STEP = 1;
					}
					break;
				case 1 :
				default :
					continue;
					break;
			}

			// Display Content
			switch($STEP) {
				case 3 :

					break;
				case 2 :
					$ONLOAD[]		= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\\'', 5000)";

					if($SUCCESS) {
						echo display_success();
					}
					if($NOTICE) {
						echo display_notice();
					}
					break;
				case 1 :
				default :
				/**
				 * Update requested sort column.
				 * Valid: date, title
				 */
					if(isset($_GET["sb"])) {
						if(@in_array(trim($_GET["sb"]), array("date", "name", "type"))) {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = trim($_GET["sb"]);
						}

						$_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
					} else {
						if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "date";
						}
					}

					/**
					 * Update requested order to sort by.
					 * Valid: asc, desc
					 */
					if(isset($_GET["so"])) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

						$_SERVER["QUERY_STRING"]	= replace_query(array("so" => false));
					} else {
						if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
						}
					}

					/**
					 * Update requsted number of rows per page.
					 * Valid: any integer really.
					 */
					if((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
						$integer = (int) trim($_GET["pp"]);

						if(($integer > 0) && ($integer <= 250)) {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
						}

						$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
					} else {
						if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
							$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = 15;
						}
					}

					/**
					 * Provide the queries with the columns to order by.
					 */
					switch($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
						case "name" :
							$SORT_BY	= "CONCAT_WS(', ', `last_name`, `first_name`) ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
						case "type" :
							$SORT_BY	= "CASE c.`group_role` WHEN 'guest' THEN 1 WHEN '%' THEN 2 END ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
						case "date" :
						default :
							$SORT_BY	= "a.`updated_date` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
					}

					if($NOTICE) {
						echo display_notice();
					}
					if($ERROR) {
						echo display_error();
					}

                                            if ($ENTRADA_ACL->amIAllowed(new EventResource($evaluation_details["evaluation_id"]), 'update')) {
                                            }
                                                    echo "<div class=\"no-printing\">\n";
                                                    echo "	<div style=\"float: right; margin-top: 8px\">\n";
                                                    echo "		<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\"><img src=\"".ENTRADA_URL."/images/event-details.gif\" width=\"16\" height=\"16\" alt=\"Edit evaluation details\" title=\"Edit evaluation details\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\" style=\"font-size: 10px; margin-right: 8px\">Edit evaluation details</a>\n";
                                                    echo "	</div>\n";
                                                    echo "</div>\n";

                                            echo "<h1 class=\"evaluation-title\">".html_encode($evaluation_details["evaluation_title"])."</h1>\n";
					?>
<div class="tab-pane" id="evaluator_members_div">
	<div class="tab-page members">
		<h2 class="tab">Evaluators</h2>
		<h2 style="margin-top: 0px">Evaluators</h2>
							<?php
							/**
							 * Get the total number of results using the generated queries above and calculate the total number
							 * of pages that are available based on the results per page preferences.
							 */
							$query	= "SELECT COUNT(*) AS `total_rows` FROM `evaluation_evaluators` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
							$result	= $db->GetRow($query);
							if($result) {
								$TOTAL_ROWS	= $result["total_rows"];

								if($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
									$TOTAL_PAGES = 1;
								} elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
								} else {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
								}

								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}

								if($TOTAL_PAGES > 1) {
									$member_pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $TOTAL_ROWS, ENTRADA_URL."/admin/evaluations", replace_query(), "mpv");
								} else {
									$member_pagination = false;
								}
							} else {
								$TOTAL_ROWS		= 0;
								$TOTAL_PAGES	= 1;
							}
							if (!isset($PAGE_CURRENT) || !$PAGE_CURRENT) {
								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}
							}

							$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
							$PAGE_NEXT		= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

							/**
							 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
							 */
							$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
							$query		= "
										SELECT a.*, b.`username`, b.`firstname`, b.`lastname`, b.`email`, c.`group`
										FROM `evaluation_evaluators` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`evaluator_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
										GROUP BY b.`id`
										ORDER BY %s
										LIMIT %s, %s";

                                                        $query1		= "
                                                                          select * from (
										(
                                                                                SELECT a.updated_date, a.evaluator_type, a.eevaluator_id, b.`username` as user_name, b.`firstname` as first_name, b.`lastname` as last_name, concat(concat(c.`group`,' > '),c.`role`) as group_role
										FROM `entrada`.`evaluation_evaluators` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`evaluator_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type='proxy_id'
										ORDER BY %s
                                                                                )
                                                                                union
										(
                                                                                SELECT updated_date, evaluator_type, eevaluator_id, `evaluator_value` as user_name, `evaluator_value` as first_name, '' as last_name,'student' as group_role
										FROM `entrada`.`evaluation_evaluators`
										WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type!='proxy_id'
										ORDER BY updated_date
										)
                                                                            ) as t
										LIMIT %s, %s
                                                                            ";

							$query		= sprintf($query1, $SORT_BY, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
                                                        $results	= $db->GetAll($query);
                                                        //echo "______log______"."query1: ".$query1."<br>";
                                                        //echo "______log______"."total_rows: ".$results["total_rows"]."<br>";

							if($results) {
								if(($TOTAL_PAGES > 1) && ($member_pagination)) {
									echo "<div id=\"pagination-links\">\n";
									echo "Pages: ".$member_pagination->GetPageLinks();
									echo "</div>\n";
								}
								?>
								<form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "members", "step" => 2)); ?>" method="post">
								<table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Members">
								<colgroup>
									<col class="modified" />
									<col class="date" />
									<col class="title" />
									<col class="list-status" />
									<col class="type" />
									<col class="status" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("date", "Evaluator Since"); ?></td>
										<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("name", "Evaluator Name"); ?></td>
										<td class="list-status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "list-status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>">Group & Role</td>
                                                                                <td class="type<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("type", "Evaluator Type"); ?></td>
                                                                                <td class="status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"> Status </td>

									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="2" style="padding-top: 15px">&nbsp;</td>
										<td style="padding-top: 15px; text-align: right" colspan="3">
											<select id="member_action" name="member_action" style="vertical-align: middle; width: 200px">
												<option value="">-- Select Evaluator Action --</option>
												<option value="delete">1. Remove evaluators</option>
											</select>
											<input type="submit" class="button-sm" value="Proceed" style="vertical-align: middle" />
										</td>
									</tr>
								</tfoot>
								<tbody>
								<?php
								foreach($results as $result) {
									echo "<tr>\n";
									echo "	<td><input type=\"checkbox\" name=\"member_proxy_ids[]\" value=\"".(int) $result["eevaluator_id"]."\" /></td>\n";
									echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</td>\n";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".html_encode($result["user_name"])."\"".(($result["proxy_id"] == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) ? " style=\"font-weight: bold" : "")."\">".html_encode($result["first_name"]." ".$result["last_name"])."</a></td>\n";
									echo "	<td>".$result["group_role"]."</td>\n";
									echo "	<td>".$result["evaluator_type"]."</td>\n";
									echo "	<td></td>\n";
									echo "</tr>\n";
								}
								?>
								</tbody>
								</table>
								</form>
								<?php
							} else {
								echo display_notice(array("Your evaluation has no evaluators at this time, you should add some people by clicking the &quot;<strong>Add Evaluators</strong>&quot; tab."));
							}
							?>
	</div>
	<div class="tab-page members">
		<h2 class="tab">Targets</h2>
		<h2 style="margin-top: 0px">Targets</h2>
							<?php
							/**
							 * Get the total number of results using the generated queries above and calculate the total number
							 * of pages that are available based on the results per page preferences.
							 */
							$query	= "SELECT COUNT(*) AS `total_rows` FROM `evaluation_targets` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
							$result	= $db->GetRow($query);
							if($result) {
								$TOTAL_ROWS	= $result["total_rows"];

								if($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
									$TOTAL_PAGES = 1;
								} elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
								} else {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
								}

								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}

								if($TOTAL_PAGES > 1) {
									$member_pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $TOTAL_ROWS, ENTRADA_URL."/admin/evaluations", replace_query(), "mpv");
								} else {
									$member_pagination = false;
								}
							} else {
								$TOTAL_ROWS		= 0;
								$TOTAL_PAGES	= 1;
							}
							if (!isset($PAGE_CURRENT) || !$PAGE_CURRENT) {
								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}
							}

							$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
							$PAGE_NEXT		= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

							/**
							 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
							 */
							$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
							$query		= "
										SELECT a.*, b.`username`, b.`firstname`, b.`lastname`, b.`email`, c.`group`
										FROM `evaluation_targets` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`evaluator_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
										GROUP BY b.`id`
										ORDER BY %s
										LIMIT %s, %s";

                                                        $query1		= "
                                                                          select * from (
										(
                                                                                SELECT a.updated_date, d.target_shortname as target_type, a.etarget_id, b.`username` as user_name, b.`firstname` as first_name, b.`lastname` as last_name, concat(concat(c.`group`,' > '),c.`role`) as group_role
										FROM `evaluation_targets` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`target_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										LEFT JOIN `evaluations_lu_targets` AS d
										ON a.`target_id` = d.`target_id`
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND a.target_id in (2, 3, 6, 7, 8)
										GROUP BY b.`id`
                                                                                )
                                                                                union
										(
                                                                                SELECT a.updated_date, 'course' target_type, a.etarget_id, `course_name` as user_name, `course_name` as first_name, '' as last_name,'course' as group_role
										FROM `evaluation_targets` AS a
										LEFT JOIN `courses` AS b
										ON a.`target_value` = b.`course_id`
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND a.target_id= '1'
										)
                                                                            ) as t
										LIMIT %s, %s
                                                                            ";

							$query		= sprintf($query1, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
                                                        $results	= $db->GetAll($query);

							if($results) {
								if(($TOTAL_PAGES > 1) && ($member_pagination)) {
									echo "<div id=\"pagination-links\">\n";
									echo "Pages: ".$member_pagination->GetPageLinks();
									echo "</div>\n";
								}
								?>
								<form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "targets", "step" => 2)); ?>" method="post">
								<table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Targets">
								<colgroup>
									<col class="modified" />
									<col class="date" />
									<col class="title" />
									<col class="list-status" />
									<col class="type" />
									<col class="status" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("date", "Target Since"); ?></td>
										<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("name", "Target Name"); ?></td>
										<td class="list-status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "list-status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>">Group & Role</td>
                                                                                <td class="type<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("type", "Target Type"); ?></td>
                                                                                <td class="status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"> Status </td>

									</tr>
								</thead>
								<tfoot>
									<tr>
										<td colspan="2" style="padding-top: 15px">&nbsp;</td>
										<td style="padding-top: 15px; text-align: right" colspan="3">
											<select id="target_action" name="target_action" style="vertical-align: middle; width: 200px">
												<option value="ssss">-- Select Target Action --</option>
												<option value="delete">1. Remove targets</option>
											</select>
											<input type="submit" class="button-sm" value="Proceed" style="vertical-align: middle" />
										</td>
									</tr>
								</tfoot>
								<tbody>
								<?php
								foreach($results as $result) {
									echo "<tr>\n";
									echo "	<td><input type=\"checkbox\" name=\"member_proxy_ids[]\" value=\"".(int) $result["etarget_id"]."\" /></td>\n";
									echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</td>\n";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".html_encode($result["user_name"])."\"".(($result["proxy_id"] == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) ? " style=\"font-weight: bold" : "")."\">".html_encode($result["first_name"]." ".$result["last_name"])."</a></td>\n";
									echo "	<td>".$result["group_role"]."</td>\n";
									echo "	<td>".$result["target_type"]."</td>\n";
									echo "	<td></td>\n";
									echo "</tr>\n";
								}
								?>
								</tbody>
								</table>
								</form>
								<?php
							} else {
								echo display_notice(array("Your evaluation has no targets at this time, you should add some targets by clicking the &quot;<strong>Add Targets</strong>&quot; tab."));
							}
							?>
	</div>
	<div class="tab-page members">
		<h2 class="tab">Add Evaluators</h2>
		<h2 style="margin-top: 0px">Add Evaluators</h2>
		<form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "add", "step" => 2)); ?>" method="post">
			<table style="margin-top: 10px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Evaluators">
				<colgroup>
					<col style="width: 45%" />
					<col style="width: 10%" />
					<col style="width: 45%" />
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="3" style="padding-top: 15px; text-align: right">
							<input type="submit" class="button" value="Add Evaluators" style="vertical-align: middle" />
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td colspan="3" style="vertical-align: top">
								<p>
									If you would like to add users that already exist in the system to this evaluation yourself, you can do so by clicking the checkbox beside their name from the list below.
									Once you have reviewed the list at the bottom and are ready, click the <strong>Proceed</strong> button at the bottom to complete the process.
								</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align: top">
							<div class="member-add-type" id="existing-member-add-type">
													<?php
													$nmembers_query			= "";
													$nmembers_results		= false;

													/**
													 * Check registration requirements for this community.
													 */
													switch($evaluation_details["community_registration"]) {
														case 2 :	// Selected Group Registration
														/**
														 * List everyone in the specific groups with the specific role combination. What a PITA.
														 */
															if(($evaluation_details["evaluator_members"] != "") && ($evaluator_members = @unserialize($evaluation_details["evaluator_members"])) && (is_array($evaluator_members)) && (count($evaluator_members))) {
																$role_group_combinations = array();
																foreach($evaluator_members as $member_group) {
																	if($member_group != "") {
																		$tmp_build	= array();
																		$role	= "";
																		$pieces = explode("_", $member_group);

																		if(isset($pieces[1]) && (isset($pieces[0]) && $pieces[0] == "student")) {
																			$tmp_build["role"]	= "b.`role` = ".$db->qstr(clean_input($pieces[1], "alphanumeric"));
																		}
																		if(isset($pieces[0])) {
																			$tmp_build["group"]	= "b.`group` = ".$db->qstr(clean_input($pieces[0], "alphanumeric"));
																		}

																		$role_group_combinations[] = "(".implode(" AND ", $tmp_build).")";
																	}
																}
																if(@count($role_group_combinations)) {
																	$nmembers_query	= "
																		SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																		FROM `".AUTH_DATABASE."`.`user_data` AS a
																		LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																		ON a.`id` = b.`user_id`
																		WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
																		AND b.`account_active` = 'true'
																		AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																		AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																		AND (".implode(" OR ", $role_group_combinations).")
																		GROUP BY a.`id`
																		ORDER BY a.`lastname` ASC, a.`firstname` ASC";
																}
															}
															break;
														case 3 :	// Selected Community Registration
															if(($evaluation_details["evaluator_members"] != "") && ($evaluator_members = @unserialize($evaluation_details["evaluator_members"])) && (is_array($evaluator_members)) && (count($evaluator_members))) {
																$tmp_community_member_list = array();
																$query		= "SELECT `proxy_id` FROM `evaluator_members` WHERE `member_active` = '1' AND `community_id` IN ('".implode("', '", $evaluator_members)."')";
																$results	= $db->GetAll($query);
																if($results) {
																	foreach($results as $result) {
																		if($proxy_id = (int) $result["proxy_id"]) {
																			$tmp_community_member_list[] = $proxy_id;
																		}
																	}
																}
																if(@count($tmp_community_member_list)) {
																	$nmembers_query	= "
																		SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																		FROM `".AUTH_DATABASE."`.`user_data` AS a
																		LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																		ON a.`id` = b.`user_id`
																		WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
																		AND b.`account_active` = 'true'
																		AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																		AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																		AND a.`id` IN ('".implode("', '", $tmp_community_member_list)."')
																		GROUP BY a.`id`
																		ORDER BY a.`lastname` ASC, a.`firstname` ASC";
																}
															}
															break;
														case 0 :	// Open Community
														case 1 :	// Open Registration
														case 4 :	// Private Community
														default :
															$nmembers_query	= "
																SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
																AND b.`account_active` = 'true'
																AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																GROUP BY a.`id`
																ORDER BY a.`lastname` ASC, a.`firstname` ASC";
															break;
													}
													//Fetch list of categories
													$query	= "SELECT `organisation_id`,`organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
													$organisation_results	= $db->GetAll($query);
													if($organisation_results) {
														$organisations = array();
														foreach($organisation_results as $result) {
															if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'create')) {
																$member_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
															}
														}
													}

													$current_member_list	= array();
													$query		= "SELECT `proxy_id` FROM `evaluator_members` WHERE `community_id` = ".$db->qstr($EVALUATION_ID)." AND `member_active` = '1'";
													$results	= $db->GetAll($query);
													if($results) {
														foreach($results as $result) {
															if($proxy_id = (int) $result["proxy_id"]) {
																$current_member_list[] = $proxy_id;
															}
														}
													}

													if($nmembers_query != "") {
														$nmembers_results = $db->GetAll($nmembers_query);
														if($nmembers_results) {
															$members = $member_categories;

															foreach($nmembers_results as $member) {

																$organisation_id = $member['organisation_id'];
																$group = $member['group'];
																$role = $member['role'];

																if($group == "student" && !isset($members[$organisation_id]['options'][$group.$role])) {
																	$members[$organisation_id]['options'][$group.$role] = array('text' => $group. ' > '.$role, 'value' => $organisation_id.'|'.$group.'|'.$role);
																} elseif ($group != "guest" && $group != "student" && !isset($members[$organisation_id]['options'][$group."all"])) {
																	$members[$organisation_id]['options'][$group."all"] = array('text' => $group. ' > all', 'value' => $organisation_id.'|'.$group.'|all');
																}
															}

															foreach($members as $key => $member) {
																if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
																	sort($members[$key]['options']);
																}
															}
                                                                                                                        //var_export($member_categories);
                                                                                                                        //echo "<br>";
                                                                                                                        //var_export($members);
															echo lp_multiple_select_inline('evaluator_members', $members, array(
															'width'	=>'100%',
															'ajax'=>true,
															'selectboxname'=>'group and role',
															'default-option'=>'-- Select Group & Role --',
															'category_check_all'=>true));
														} else {
															echo "No One Available for evaluators[1]";
														}
													} else {
														echo "No One Available for evaluators[2]";
													}
													?>

								<input class="multi-picklist" id="evaluator_members" name="evaluator_members" style="display: none;">
							</div>
						</td>
						<td style="vertical-align: top; padding-left: 20px;">
							<input id="acc_evaluator_members" style="display: none;" name="acc_evaluator_members"/>
							<h3>Evaluators to be Added on Submission</h3>
							<div id="evaluator_members_list"></div>
						</td>
				</tbody>
			</table>
		</form>
	</div>
	<div class="tab-page members">
		<h2 class="tab">Add Evaluator Group</h2>
		<h2 style="margin-top: 0px">Add Evaluator Group</h2>
                        <form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "addgroup", "step" => 2)); ?>" method="post" name="addEvaluationForm" id="addEvaluationForm">
			<input type="hidden" name="evaluation_id" value="<?php echo $EVALUATION_ID;?>" />
			<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="vertical-align: top"><input type="radio" name="eval_evaluator_type" id="eval_evaluator_type_grad_year" value="grad_year" onclick="selectEventAudienceOption('grad_year')" style="vertical-align: middle" checked=checked/></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="eval_evaluator_type_grad_year" class="radio-group-title">Entire Class Evaluation</label>
							<div class="content-small">This evaluation is intended for an entire class.</div>
						</td>
					</tr>
                                        <tr class="eval_evaluator grad_year_audience">
                                            <td></td>
                                            <td><label for="random_number" class="form-required">Random Percentage Number</label></td>
                                            <td><input type="text" id="random_number" name="random_number" value="100" maxlength="3" style="width: 60px" />%</td>
                                        </tr>
					<tr class="eval_evaluator grad_year_audience">
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
                                        <!--
					<tr>
						<td style="vertical-align: top"><input type="radio" name="eval_evaluator_type" id="eval_evaluator_type_organisation_id" value="organisation_id" onclick="selectEventAudienceOption('organisation_id')" style="vertical-align: middle"<?php echo (($PROCESSED["eval_evaluator_type"] == "organisation_id") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="eval_evaluator_type_organisation_id" class="radio-group-title">Entire Organisation Event</label>
							<div class="content-small">This event is intended for every member of an organisation.</div>
						</td>
					</tr>
					<tr class="eval_evaluator organisation_id_audience">
						<td></td>
						<td><label for="associated_organisation_id" class="form-required">Organisation</label></td>
						<td>
							<select id="associated_organisation_id" name="associated_organisation_id" style="width: 203px">
								<?php
								if (is_array($organisation_categories) && count($organisation_categories)) {
									foreach($organisation_categories as $organisation_id => $organisation_info) {
										echo "<option value=\"".$organisation_id."\"".(($PROCESSED["associated_organisation_id"] == $year) ? " selected=\"selected\"" : "").">".$organisation_info['text']."</option>\n";
									}
								}
								?>
							</select>
						</td>
					</tr>
                                        -->
                                        <tfoot>
                                                <tr>
                                                        <td colspan="3" style="padding-top: 15px; text-align: right">
                                                                <input type="submit" class="button" value="Add Evaluators" style="vertical-align: middle" />
                                                        </td>
                                                </tr>
                                        </tfoot>
					</table>
                                        <br><br>
                                        </form>
                                </div>
	<div class="tab-page members">
		<h2 class="tab">Add Targets</h2>
		<h2 style="margin-top: 0px">Add Targets</h2>
		<form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "addtarget", "step" => 2)); ?>" method="post">
			<table style="margin-top: 10px; width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Add Targets">
				<colgroup>
					<col style="width: 45%" />
					<col style="width: 10%" />
					<col style="width: 45%" />
				</colgroup>
				<tbody>
					<tr>
						<td colspan="3" style="vertical-align: top">
								<p>
									If you would like to add targets that already exist in the system to this evaluation yourself, you can do so by clicking the checkbox beside their name from the list below.
									Once you have reviewed the list at the bottom and are ready, click the <strong>Proceed</strong> button at the bottom to complete the process.
								</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="vertical-align: top">
                                                    <div class="target-add-type" id="existing-target-add-type">
							<?php
							$nmembers_query			= "";
							$nmembers_results		= false;
							$sql_groupname		= "";
                                                        $target_id = 0;

                                                        $query		= "SELECT a.`eform_id`, `target_id`
                                                                            FROM `evaluations` AS a
                                                                            LEFT JOIN `evaluation_forms` AS b
                                                                            ON a.eform_id= b.eform_id
                                                                            WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID);
                                                        $form_result	= $db->GetAll($query);
                                                        //var_export ($form_result);
                                                        if($form_result) {
                                                            $target_id = (int) $form_result[0]["target_id"];
                                                            $eform_id = (int) $form_result[0]["eform_id"];
                                                        }
                                                   if($target_id>0){
                                                        //echo "<br>".$EVALUATION_ID."||".$form_result["target_id"]."||".$target_id."<br>";
                                                        $ppl_query_1st = " SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`username`, a.`organisation_id`, b.`group`, b.`role`
                                                                            FROM `".AUTH_DATABASE."`.`user_data` AS a
                                                                            LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
                                                                            ON a.`id` = b.`user_id`
                                                                            WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
                                                                            AND b.`account_active` = 'true'
                                                                            AND b.`group` = '";
                                                        $ppl_query_2nd = "' AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
                                                                            AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
                                                                            GROUP BY a.`id`
                                                                            ORDER BY a.`lastname` ASC, a.`firstname` ASC";
                                                        $course_query = " SELECT `course_id` AS `proxy_id`, CONCAT_WS(', ', `course_name`, `course_code`) AS `fullname`, `course_name` as username, `organisation_id`, 'course' as `group`, 'course' as `role`
                                                                            FROM `courses`";

                                                        $default_option = '-- Select Group & Role --';

                                                        switch($target_id) {
                                                                case 1 :
                                                                    $nmembers_query = $course_query;
                                                                    $default_option = '-- Select Course --';
                                                                break;
                                                                case 2 :
                                                                case 6 :
                                                                    $nmembers_query = $ppl_query_1st."faculty".$ppl_query_2nd;
                                                                break;
                                                                case 3 :
                                                                case 7 :
                                                                case 8 :
                                                                    $nmembers_query = $ppl_query_1st."student".$ppl_query_2nd;
                                                                default :
                                                                break;
                                                        }


                                                        if($nmembers_query != "") {
                                                                $nmembers_results = $db->GetAll($nmembers_query);
                                                                //var_export ($nmembers_results);
                                                                if($nmembers_results) {
                                                                        $members = $member_categories;

                                                                        foreach($nmembers_results as $member) {

                                                                                $organisation_id = $member['organisation_id'];
                                                                                $group = $member['group'];
                                                                                $role = $member['role'];

                                                                                if($group == "student" && !isset($members[$organisation_id]['options'][$group.$role])) {
                                                                                        $members[$organisation_id]['options'][$group.$role] = array('text' => $group. ' > '.$role, 'value' => $organisation_id.'|'.$group.'|'.$role);
                                                                                } elseif ($group != "guest" && $group != "student" && !isset($members[$organisation_id]['options'][$group."all"])) {
                                                                                        $members[$organisation_id]['options'][$group."all"] = array('text' => $group. ' > all', 'value' => $organisation_id.'|'.$group.'|all');
                                                                                }
                                                                        }

                                                                        foreach($members as $key => $member) {
                                                                                if(isset($member['options']) && is_array($member['options']) && !empty($member['options'])) {
                                                                                        sort($members[$key]['options']);
                                                                                }
                                                                        }
                                                                        //var_export($nmembers_results);
                                                                        //echo "<br>";
                                                                        //var_export($members);
                                                                        echo lp_multiple_select_inline('target_members', $members, array(
                                                                        'width'	=>'100%',
                                                                        'ajax'=>true,
                                                                        'selectboxname'=>'group and role',
                                                                        'default-option'=>$default_option,
                                                                        'category_check_all'=>true));
                                                                } else {
                                                                        echo "No One Available [11]";
                                                                }
                                                        } else {
                                                                echo "No One Available [2]";
                                                        }
                                                        ?>
                                                        <input class="multi-picklist" id="target_members" name="target_members" style="display: none;">
                                                        <input type="hidden" id="target_type_added"  name="target_type_added" value="<?php echo $target_id; ?>"/>
                                                        <?php
                                                    }else{
							echo display_notice(array("Your evaluation has no form at this time, you should choose form in this evaluation before you add targets."));
                                                    }
                                                        ?>

                                                    </div>
						</td>

						<td style="vertical-align: top; padding-left: 20px;">
							<input id="acc_target_members" style="display: none;" name="acc_target_members"/>
							<h3>Targets to be Added on Submission</h3>
							<div id="target_members_list"></div>
						</td>
				</tbody>
                                                <?php if($target_id>0){?>
				<tfoot>
					<tr>
						<td colspan="3" style="padding-top: 15px; text-align: right">
							<input type="submit" class="button" value="Add Targets" style="vertical-align: middle" />
						</td>
					</tr>
				</tfoot>
                                                <?php } ?>
			</table>
		</form>
	</div>
	<div class="tab-page members">
		<h2 class="tab">Progress</h2>
		<h2 style="margin-top: 0px">Progress</h2>
							<?php
							/**
							 * Get the total number of results using the generated queries above and calculate the total number
							 * of pages that are available based on the results per page preferences.
							 */
							$query	= "SELECT COUNT(*) AS `total_rows` FROM `evaluation_evaluators` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
							$result	= $db->GetRow($query);
							if($result) {
								$TOTAL_ROWS	= $result["total_rows"];

								if($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
									$TOTAL_PAGES = 1;
								} elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
								} else {
									$TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
								}

								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}

								if($TOTAL_PAGES > 1) {
									$member_pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $TOTAL_ROWS, ENTRADA_URL."/admin/evaluations", replace_query(), "mpv");
								} else {
									$member_pagination = false;
								}
							} else {
								$TOTAL_ROWS		= 0;
								$TOTAL_PAGES	= 1;
							}
							if (!isset($PAGE_CURRENT) || !$PAGE_CURRENT) {
								if(isset($_GET["mpv"])) {
									$PAGE_CURRENT = (int) trim($_GET["mpv"]);

									if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
										$PAGE_CURRENT = 1;
									}
								} else {
									$PAGE_CURRENT = 1;
								}
							}

							$PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
							$PAGE_NEXT		= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

							/**
							 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
							 */
							$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);

                                                        $query1		= "
                                                                          select * from (
										(
                                                                                SELECT a.updated_date, a.evaluator_type, a.eevaluator_id, b.`username` as user_name, b.`firstname` as first_name, b.`lastname` as last_name, concat(concat(c.`group`,' > '),c.`role`) as group_role
										FROM `entrada`.`evaluation_evaluators` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`evaluator_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type='proxy_id'
										ORDER BY %s
                                                                                )
                                                                                union
										(
                                                                                SELECT updated_date, evaluator_type, eevaluator_id, `evaluator_value` as user_name, `evaluator_value` as first_name, '' as last_name,'student' as group_role
										FROM `entrada`.`evaluation_evaluators`
										WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type!='proxy_id'
										ORDER BY updated_date
										)
                                                                            ) as t
										LIMIT %s, %s
                                                                            ";
                                                        $query1		= "
                                                                          select * from (
										(
                                                                                SELECT a.updated_date, a.evaluator_type, a.eevaluator_id, b.`username` as user_name, b.`firstname` as first_name, b.`lastname` as last_name, concat(concat(c.`group`,' > '),c.`role`) as group_role
										FROM `entrada`.`evaluation_evaluators` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
										ON a.`evaluator_value` = b.`id`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
										ON c.`user_id` = b.`id`
										AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
										WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type='proxy_id'
										ORDER BY %s
                                                                                )
                                                                                union
										(
                                                                                SELECT updated_date, evaluator_type, eevaluator_id, `evaluator_value` as user_name, `evaluator_value` as first_name, '' as last_name,'student' as group_role
										FROM `entrada`.`evaluation_evaluators`
										WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)."
                                                                                AND evaluator_type!='proxy_id'
										ORDER BY updated_date
										)
                                                                            ) as t
										LIMIT %s, %s
                                                                            ";

							$query		= sprintf($query, $SORT_BY, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
                                                        $results	= $db->GetAll($query);
                                                        //echo "______log______"."query1: ".$query1."<br>";
                                                        //echo "______log______"."total_rows: ".$results["total_rows"]."<br>";

							if($results) {
								if(($TOTAL_PAGES > 1) && ($member_pagination)) {
									echo "<div id=\"pagination-links\">\n";
									echo "Pages: ".$member_pagination->GetPageLinks();
									echo "</div>\n";
								}
								?>
								<form action="<?php echo ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "members", "type" => "members", "step" => 2)); ?>" method="post">
								<table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Members">
								<colgroup>
									<col class="date" />
									<col class="title" />
									<col class="list-status" />
									<col class="type" />
									<col class="status" />
								</colgroup>
								<thead>
									<tr>
										<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("date", "Evaluator Since"); ?></td>
										<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("name", "Evaluator Name"); ?></td>
										<td class="list-status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "list-status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>">Group & Role</td>
                                                                                <td class="type<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("type", "Evaluator Type"); ?></td>
                                                                                <td class="status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"> Status </td>

									</tr>
								</thead>
								<tbody>
								<?php
								foreach($results as $result) {
									echo "<tr>\n";
									echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</td>\n";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".html_encode($result["user_name"])."\"".(($result["proxy_id"] == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) ? " style=\"font-weight: bold" : "")."\">".html_encode($result["first_name"]." ".$result["last_name"])."</a></td>\n";
									echo "	<td>".$result["group_role"]."</td>\n";
									echo "	<td>".$result["evaluator_type"]."</td>\n";
									echo "	<td></td>\n";
									echo "</tr>\n";
								}
								?>
								</tbody>
                                                                <tfoot>
                                                                    <tr><td>&nbsp;</td></tr>
                                                                </tfoot>
								</table>
								</form>
								<?php
							} else {
								echo display_notice(array("Your evaluation has no evaluators at this time, you should add some people by clicking the &quot;<strong>Add Evaluators</strong>&quot; tab."));
							}
							?>
	</div>

</div>
<script type="text/javascript">
        function search_grad_year(){
            document.addEvaluationForm.action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=members";
            document.addEvaluationForm.submit();
        }
	setupAllTabs(true);

	var people = [[]];
	var ids = [[]];
	//Updates the Evaluators Being Added div with all the options
	function updatePeopleList(newoptions, index) {
		people[index] = newoptions;
		table = people.flatten().inject(new Element('table', {'class':'member-list'}), function(table, option, i) {
			if(i%2 == 0) {
				row = new Element('tr');
				table.appendChild(row);
			}
			row.appendChild(new Element('td').update(option));
			return table;
		});
		$('evaluator_members_list').update(table);
		ids[index] = $F('evaluator_members').split(',').compact();
		$('acc_evaluator_members').value = ids.flatten().join(',');
	}


	$('evaluator_members_select_filter').observe('keypress', function(event){
	    if(event.keyCode == Event.KEY_RETURN) {
	        Event.stop(event);
	    }
	});

	//Reload the multiselect every time the category select box changes
	var multiselect;

	$('evaluator_members_category_select').observe('change', function(event) {
		if ($('evaluator_members_category_select').selectedIndex != 0) {
			$('evaluator_members_scroll').update(new Element('div', {'style':'width: 100%; height: 100%; background: transparent url(<?php echo ENTRADA_URL;?>/images/loading.gif) no-repeat center'}));

			//Grab the new contents
			var updater = new Ajax.Updater('evaluator_members_scroll', '<?php echo ENTRADA_URL."/admin/evaluations?section=membersadd&action=memberlist";?>',{
				method:'post',
				parameters: {
					'ogr':$F('evaluator_members_category_select'),
					'evaluation_id':'<?php echo $EVALUATION_ID;?>'
				},
				onSuccess: function(transport) {
					//onSuccess fires before the update actually takes place, so just set a flag for onComplete, which takes place after the update happens
					this.makemultiselect = true;
				},
				onFailure: function(transport){
					$('evaluator_members_scroll').update(new Element('div', {'class':'display-error'}).update('There was a problem communicating with the server. An administrator has been notified, please try again later.'));
				},
				onComplete: function(transport) {
					//Only if successful (the flag set above), regenerate the multiselect based on the new options
					if(this.makemultiselect) {
						if(multiselect) {
							multiselect.destroy();
						}
						multiselect = new Control.SelectMultiple('evaluator_members','evaluator_members_options',{
							labelSeparator: '; ',
							checkboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox input[type=checkbox]',
							categoryCheckboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox_category input[type=checkbox]',
							nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
							overflowLength: 70,
							filter: 'evaluator_members_select_filter',
							afterCheck: function(element) {
								var tr = $(element.parentNode.parentNode);
								tr.removeClassName('selected');
								if(element.checked) {
									tr.addClassName('selected');
								}
							},
							updateDiv: function(options, isnew) {
								updatePeopleList(options, $('evaluator_members_category_select').selectedIndex);
							}
						});
					}
				}
			});
		}
	});


	//Updates the Targets Being Added div with all the options
	function updateTargetList(newoptions, index) {
		people[index] = newoptions;
		table = people.flatten().inject(new Element('table', {'class':'member-list'}), function(table, option, i) {
			if(i%2 == 0) {
				row = new Element('tr');
				table.appendChild(row);
			}
			row.appendChild(new Element('td').update(option));
			return table;
		});
		$('target_members_list').update(table);
		ids[index] = $F('target_members').split(',').compact();
		$('acc_target_members').value = ids.flatten().join(',');
	}


	$('target_members_select_filter').observe('keypress', function(event){
	    if(event.keyCode == Event.KEY_RETURN) {
	        Event.stop(event);
	    }
	});

	//Reload the multiselect every time the category select box changes
	var multiselect;

	$('target_members_category_select').observe('change', function(event) {
		if ($('target_members_category_select').selectedIndex != 0) {
			$('target_members_scroll').update(new Element('div', {'style':'width: 100%; height: 100%; background: transparent url(<?php echo ENTRADA_URL;?>/images/loading.gif) no-repeat center'}));

			//Grab the new contents
			var updater = new Ajax.Updater('target_members_scroll', '<?php echo ENTRADA_URL."/admin/evaluations?section=targetsadd&action=memberlist";?>',{
				method:'post',
				parameters: {
					'ogr':$F('target_members_category_select'),
					'evaluation_id':'<?php echo $EVALUATION_ID;?>'
				},
				onSuccess: function(transport) {
					//onSuccess fires before the update actually takes place, so just set a flag for onComplete, which takes place after the update happens
					this.makemultiselect = true;
				},
				onFailure: function(transport){
					$('target_members_scroll').update(new Element('div', {'class':'display-error'}).update('There was a problem communicating with the server. An administrator has been notified, please try again later.'));
				},
				onComplete: function(transport) {
					//Only if successful (the flag set above), regenerate the multiselect based on the new options
					if(this.makemultiselect) {
						if(multiselect) {
							multiselect.destroy();
						}
						multiselect = new Control.SelectMultiple('target_members','target_members_options',{
							labelSeparator: '; ',
							checkboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox input[type=checkbox]',
							categoryCheckboxSelector: 'table.select_multiple_table tr td.select_multiple_checkbox_category input[type=checkbox]',
							nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
							overflowLength: 70,
							filter: 'target_members_select_filter',
							afterCheck: function(element) {
								var tr = $(element.parentNode.parentNode);
								tr.removeClassName('selected');
								if(element.checked) {
									tr.addClassName('selected');
								}
							},
							updateDiv: function(options, isnew) {
								updateTargetList(options, $('target_members_category_select').selectedIndex);
							}
						});
					}
				}
			});
		}
	});
</script>
<br /><br />
					<?php
					break;
			}
	} else {
		application_log("error", "User tried to manage members of a community id [".$EVALUATION_ID."] that does not exist or is not active in the system.");

		$ERROR++;
		$ERRORSTR[] = "The community you are trying to manage members either does not exist in the system or has been deactived by an administrator.<br /><br />If you feel you are receiving this message in error, please contact the MEdTech Unit (page feedback on left) and we will investigate. The MEdTech Unit has automatically been informed that this error has taken place.";

		echo display_error();
	}
} else {
	application_log("error", "User tried to manage members a evaluation without providing a evaluation_id.");

	header("Location: ".ENTRADA_URL."/admin/evaluations");
	exit;
}
?>