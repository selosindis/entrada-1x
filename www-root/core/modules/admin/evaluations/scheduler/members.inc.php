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
		$HEAD[]		= "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[]		= "<link href=\"".ENTRADA_URL."/css/tree.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[]		= "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";

		$BREADCRUMB[]		= array("url" => ENTRADA_URL."/community".$evaluation_details["community_url"], "title" => limit_chars($evaluation_details["community_title"], 50));
		$BREADCRUMB[]		= array("url" => ENTRADA_URL."/communities?".replace_query(), "title" => "Manage Members");

			echo "<h1>".html_encode($evaluation_details["community_title"])."</h1>\n";

			// Error Checking
			switch($STEP) {
				case 3 :
				case 2 :
					switch($ACTION_TYPE) {
						case "addguest":
							$PROCESSED = array();
							$GUEST_PROXY_ID = 0;
							$GUEST_ACCESS = false;
							$GUEST_NEW_ACCESS = false;
							$member_add_success	= 0;
							$member_add_failure	= 0;
							/**
							 * Required field "firstname" / Firstname.
							 */
							if((isset($_POST["firstname"])) && ($firstname = clean_input($_POST["firstname"], "trim"))) {
								$PROCESSED["firstname"] = $firstname;
							} else {
								$ERROR++;
								$ERRORSTR[] = "The firstname of the user is a required field.";
							}

							/**
							 * Required field "lastname" / Lastname.
							 */
							if((isset($_POST["lastname"])) && ($lastname = clean_input($_POST["lastname"], "trim"))) {
								$PROCESSED["lastname"] = $lastname;
							} else {
								$ERROR++;
								$ERRORSTR[] = "The lastname of the user is a required field.";
							}

							/**
							 * Required field "email" / Primary E-Mail.
							 */
							if((isset($_POST["email"])) && ($email = clean_input($_POST["email"], "trim", "lower"))) {
								if(@valid_address($email)) {
									$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data`
												LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `user_access`.`user_id` = `user_data`.`id`
												WHERE `user_data`.`email` = ".$db->qstr($email)."
												AND (`user_access`.`group` != 'guest' && `user_access`.`role` != 'communityinvite');";
									$result	= $db->GetRow($query);
									if($result) {
										$ERROR++;
										$ERRORSTR[] = "The e-mail address <strong>".html_encode($email)."</strong> already exists in the system for username <strong>".html_encode($result["username"])."</strong>. Please provide a unique e-mail address for this user or select the existing user on the <strong>Add Members</strong> tab.";
									} else {
										$PROCESSED["email"] = $email;
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The primary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "The primary e-mail address is a required field.";
							}

							if(!$ERROR) {
							//Check to see if this user is already in the system.

								$query	= "SELECT `user_data`.`id` as `proxy_id`, `user_data`.`username` FROM `".AUTH_DATABASE."`.`user_data`
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `user_access`.`user_id` = `user_data`.`id`
										WHERE `user_data`.`email` = ".$db->qstr($PROCESSED["email"]);
								$result	= $db->GetRow($query);

								if($result) {
								//User exists already, just grant them access to the community
									$GUEST_PROXY_ID = $result['proxy_id'];
									$username = $result['username'];
								} else {
									$pieces = explode('@', $PROCESSED['email']);
									$username = $pieces[0];
									$i = '';
									do {
										$username = $pieces[0].$i;
										$i++;
									} while($db->GetRow("SELECT `username` FROM `".AUTH_DATABASE."`.`user_data` WHERE `username` = ".$db->qstr($username)));


									$PROCESSED['username']			= $username;
									$PROCESSED['number']			= 0;
									$PROCESSED['password']			= '';
									$PROCESSED['organisation_id']	= $_SESSION['details']['organisation_id'];
									$PROCESSED['prefix']			= '';
									$PROCESSED['email_alt']			= '';
									$PROCESSED['telephone']			= '';
									$PROCESSED['fax']				= '';
									$PROCESSED['address']			= '';
									$PROCESSED['city']				= '';
									$PROCESSED['province']			= '';
									$PROCESSED['postcode']			= '';
									$PROCESSED['country']			= '';
									$PROCESSED['notes']				= 'Guest created by proxy id '.$_SESSION['details']['id'];

									if(($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "INSERT")) && ($PROCESSED_ACCESS["user_id"] = $db->Insert_Id())) {
										$GUEST_PROXY_ID = $PROCESSED_ACCESS["user_id"];
									} else {
										$ERROR++;
										$ERRORSTR[] = "Unable to create a new user account at this time. An administrator has been informed of this error, so please try again later.";

										application_log("error", "Unable to create new user account. Database said: ".$db->ErrorMsg());
									}
								}
								if($GUEST_PROXY_ID > 0) {
									$query = "SELECT `account_active`, `access_starts`, `access_expires` FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($GUEST_PROXY_ID)." AND `app_id` IN (".AUTH_APP_IDS_STRING.")";
									$result = $db->GetRow($query);
									if($result) {
										if($result['account_active'] == 'false') {
											$ERROR++;
											$ERRORSTR[] = "Unable to create a new user account at this time because the account you are trying to create already exists and is deactivated. Contact an administrator for more information.";
										} else if($result['access_starts'] > time()) {
												$ERROR++;
												$ERRORSTR[] = "Unable to create a new user account at this time because the account you are trying to create already exists but it's access hasn't started yet. Contact an administrator for more information.";
											} else if($result['access_expires'] != 0 && $result['access_expires'] < time()) {
													$ERROR++;
													$ERRORSTR[] = "Unable to create a new user account at this time because the account you are trying to create already exists but it's access has expired. Contact an administrator for more information.";
												} else {
													$GUEST_ACCESS = true;
												}
									} else {
									//User needs access
										$GUEST_NEW_ACCESS = true;
										$PROCESSED_ACCESS['app_id']				= AUTH_APP_ID;
										$PROCESSED_ACCESS['acount_active']		= 'true';
										$PROCESSED_ACCESS['access_starts']		= time();
										$PROCESSED_ACCESS['access_expires']		= 0;
										$PROCESSED_ACCESS['last_login']			= 0;
										$PROCESSED_ACCESS['last_ip']			= 0;
										$PROCESSED_ACCESS['role']				= 'communityinvite';
										$PROCESSED_ACCESS['group']				= 'guest';
										$PROCESSED_ACCESS['extras']				= '';
										$PROCESSED_ACCESS['notes']				= 'Guest created by proxy id '.$_SESSION['details']['id'];

										if($db->AutoExecute(AUTH_DATABASE.".user_access", $PROCESSED_ACCESS, "INSERT")) {
											$GUEST_ACCESS = true;
										} else {
											$ERROR++;
											$ERRORSTR[] = "Unable to create a new user account at this time. An administrator has been informed of this error, so please try again later.";
											application_log("error", "Unable to enter new user access record. User data already exists. Database said: ".$db->ErrorMsg());
										}
									}

									if($GUEST_ACCESS) {
										if($GUEST_NEW_ACCESS) {
										//Mail the user
											$PROCESSED_EMAIL				= array();
											$PROCESSED_EMAIL["ip"]		= $_SERVER["REMOTE_ADDR"];
											$PROCESSED_EMAIL["date"]		= time();
											$PROCESSED_EMAIL["user_id"]	= $GUEST_PROXY_ID;
											$HASH			= get_hash();
											$PROCESSED_EMAIL["hash"]		= $HASH;
											$PROCESSED_EMAIL["complete"]	= 0;
											if($db->AutoExecute("`".AUTH_DATABASE."`.`password_reset`", $PROCESSED_EMAIL, "INSERT")) {

												$message  = "Hello ".$PROCESSED['firstname']." ".$PROCESSED['lastname'].",\n\n";
												$message .= $_SESSION['details']['firstname'].' '.$_SESSION['details']['lastname']." has invited you to join their community: ".$evaluation_details['community_title']."!\n\n";
												$message .= "This is an automated e-mail informing you that you have been invited to join ".$evaluation_details['community_title']." at ".APPLICATION_NAME.".\n";
												$message .= "An account has been created for you within ".APPLICATION_NAME." however you must activate it by resetting your password before you can use it.\n\n";
												$message .= "Your ".APPLICATION_NAME." Username is: ".$username."\n\n";
												$message .= "Please visit the following link to assign a new password to your account:\n";
												$message .= str_replace("http://", "https://", PASSWORD_RESET_URL)."?hash=".rawurlencode($PROCESSED_ACCESS["user_id"].":".$HASH)."\n\n";
												$message .= "Please Note:\n";
												$message .= "This password link will be valid for the next 3 days. If you do set your\n";
												$message .= "password within this time period, you will need to receive another invitiation to restart this process.\n\n";
												$message .= "If you did not request a password for this account and you believe\n";
												$message .= "there has been a mistake, DO NOT click the above link. Please forward this\n";
												$message .= "message along with a description of the problem to: ".$AGENT_CONTACTS["administrator"]["email"]."\n\n";
												$message .= "Best Regards,\n";
												$message .= $AGENT_CONTACTS["administrator"]["name"]."\n";
												$message .= $AGENT_CONTACTS["administrator"]["email"]."\n";
												$message .= ENTRADA_URL."\n\n";
												$message .= "Requested By:\t".$_SERVER["REMOTE_ADDR"]."\n";
												$message .= "Requested At:\t".date("r", time())."\n";

											} else {
												$ERROR++;
												$ERRORSTR[]	= "Unable to give this new user a password. An administrator has been notified, so please try again later.";

												application_log("error", "Error inserting new password_reset. Database said: ".$db->ErrorMsg());

											}
										} else {
												$message  = "Hello ".$PROCESSED['firstname']." ".$PROCESSED['lastname'].",\n\n";
												$message .= $_SESSION['details']['firstname'].' '.$_SESSION['details']['lastname']." has invited you to join their community: ".$evaluation_details['community_title']."!\n\n";
												$message .= "This is an automated e-mail informing you that you have been invited to join ".$evaluation_details['community_title']." at ".APPLICATION_NAME.".\n";
												$message .= "Your account within ".APPLICATION_NAME." is already active and you can log in whenever you like.\n\n";
												$message .= "Your ".APPLICATION_NAME." Username is: ".$username."\n\n";
												$message .= "If you do not know about this application or the community and you believe\n";
												$message .= "there has been a mistake, DO NOT log in to the application. Please forward this\n";
												$message .= "message along with a description of the problem to: ".$AGENT_CONTACTS["administrator"]["email"]."\n\n";
												$message .= "Best Regards,\n";
												$message .= $AGENT_CONTACTS["administrator"]["name"]."\n";
												$message .= $AGENT_CONTACTS["administrator"]["email"]."\n";
												$message .= ENTRADA_URL."\n\n";
												$message .= "Requested By:\t".$_SERVER["REMOTE_ADDR"]."\n";
												$message .= "Requested At:\t".date("r", time())."\n";
										}

										if(@mail($PROCESSED['email'], APPLICATION_NAME." Community Invitation from ".$PROCESSED['firstname']." ".$PROCESSED['lastname'], $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">")) {
											if ($MAILING_LISTS["active"]) {
												$mail_list = new MailingList($EVALUATION_ID);
											}
										} else {
											$ERROR++;
											$ERRORSTR[] = "We were unable to send an invitation e-mail to the guest at this time due to an unrecoverable error. The administrator has been notified of this error and will investigate the issue shortly.<br /><br />Please try again later, we apologize for any inconvenience this may have caused.";

											application_log("error", "Unable to send password reset notice as PHP's mail() function failed to initialize.");
										}

										$query	= "
												SELECT a.`id` AS `proxy_id`, c.`cmember_id`, c.`member_acl`
												FROM `".AUTH_DATABASE."`.`user_data` AS a
												LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
												ON b.`user_id` = ".$db->qstr($GUEST_PROXY_ID)."
												AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
												LEFT JOIN `evaluator_members` AS c
												ON c.`proxy_id` = ".$db->qstr($GUEST_PROXY_ID)."
												AND c.`community_id` = ".$db->qstr($EVALUATION_ID)."
												WHERE a.`id` = ".$db->qstr($GUEST_PROXY_ID);
										$result	= $db->GetRow($query);

										if($result) {
											if((int) $result["cmember_id"]) {
												if(@$db->AutoExecute("evaluator_members", array("member_active" => 1), "UPDATE", "`cmember_id` = ".$db->qstr((int) $result["cmember_id"]))) {
													if ($MAILING_LISTS["active"]) {
														$mail_list->add_member($GUEST_PROXY_ID, ((bool)$result["member_acl"]));
													}
													$member_add_success++;
												} else {
													$member_add_failure++;
													application_error("error", "Unable to activate a deactivated member. Database said: ".$db->ErrorMsg());
												}
											} else {
												$PROCESSED = array();
												$PROCESSED["community_id"]	= $EVALUATION_ID;
												$PROCESSED["proxy_id"]		= $GUEST_PROXY_ID;
												$PROCESSED["member_active"]	= 1;
												$PROCESSED["member_joined"]	= time();
												$PROCESSED["member_acl"]	= 0;

												if(@$db->AutoExecute("evaluator_members", $PROCESSED, "INSERT")) {
													if ($MAILING_LISTS["active"]) {
														$mail_list->add_member($GUEST_PROXY_ID);
													}
													$member_add_success++;
												} else {
													$member_add_failure++;
													application_error("error", "Unable to insert a new community member. Database said: ".$db->ErrorMsg());
												}
											}
										}


										if($member_add_success) {
											$url = ENTRADA_URL."admin/evaluations/scheduler?section=members&community=".$EVALUATION_ID;
											$SUCCESS++;
											if($GUEST_NEW_ACCESS) {
												$SUCCESSSTR[]	= "You have successfully created a new guest user in system, and have given them access to this community.<br /><br />You will now be redirected back to the user manager; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$SUCCESSSTR[]	= "You have successfully given a guest access to this community.<br /><br />You will now be redirected back to the user manager; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
											}
											$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
											communities_log_history($EVALUATION_ID, 0, $member_add_success, "community_history_add_members", 1);
											application_log("success", "Gave [".$PROCESSED_ACCESS["group"]." / ".$PROCESSED_ACCESS["role"]."] permissions to user id [".$PROCESSED_ACCESS["user_id"]."] as a guest for community $EVALUATION_ID.");
										} else {
											$ERROR++;
											$ERRORSTR[] = "The guest user has been added to the system however they have not joined your community. An administrator has been informed of this issue, please try again later.";

											application_log("error", "Unable to make the user a community member. Database said: ".$db->ErrorMsg());
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The guest user could not be added to your community. An administrator has been informed of this issue, please try again later.";

									application_log("error", "Unable to make the user a community member. Database said: ".$db->ErrorMsg());

								}
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
								$SUCCESSSTR[] = "You have successfully added ".$member_add_success." new evaluators".(($member_add_success != 1) ? "s" : "")." to this evaluation.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";

								communities_log_history($EVALUATION_ID, 0, $member_add_success, "community_history_add_members", 1);
							}
							if($member_add_failure) {
								$NOTICE++;
								$NOTICESTR[] = "Failed to add or update".$member_add_failure." member".(($member_add_failure != 1) ? "s" : "")." during this process. The MEdTech Unit has been informed of this error, please try again later.<br /><br />You will now be redirected back to the Manage Evaluators page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
							}
							break;
						case "admins" :
							if((isset($_POST["admin_action"])) && (@in_array(strtolower($_POST["admin_action"]), array("delete", "deactivate", "demote")))) {
								if((isset($_POST["admin_proxy_ids"])) && (is_array($_POST["admin_proxy_ids"])) && (count($_POST["admin_proxy_ids"]))) {
									foreach($_POST["admin_proxy_ids"] as $proxy_id) {
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

								if((is_array($PROXY_IDS)) && (count($PROXY_IDS))) {
									switch(strtolower($_POST["admin_action"])) {
										case "delete" :
											$query	= "DELETE FROM `evaluator_members` WHERE `community_id` = ".$db->qstr($EVALUATION_ID)." AND `proxy_id` IN ('".implode("', '", $PROXY_IDS)."') AND `member_acl` = '1'";
											$result	= $db->Execute($query);
											if(($result) && ($total_deleted = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->deactivate_member($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully removed <strong>".$total_deleted." administrator".(($total_deleted != 1) ? "s" : "")."</strong> from the <strong>".html_encode($evaluation_details["community_title"])."</strong> community.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem removing these community administrators from the system; the MEdTech Unit has been informed of this error, please try again later.";

												application_log("error", "Unable to remove admins from community_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
											}
											break;
										case "deactivate" :
											if(($db->AutoExecute("evaluator_members", array("member_active" => 0, "member_acl" => 0), "UPDATE", "`community_id` = ".$db->qstr($EVALUATION_ID)." AND `proxy_id` IN ('".implode("', '", $PROXY_IDS)."') AND `member_active` = '1' AND `member_acl` = '1'")) && ($total_updated = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->deactivate_member($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully deactivated <strong>".$total_updated." administrator".(($total_updated != 1) ? "s" : "")."</strong> in the <strong>".html_encode($evaluation_details["community_title"])."</strong> community.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem deactivating these community administrators in the system; the MEdTech Unit has been informed of this error, please try again later.";

												application_log("error", "Unable to deactivate admins from community_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
											}
											break;
										case "demote" :
											if(($db->AutoExecute("evaluator_members", array("member_acl" => 0), "UPDATE", "`community_id` = ".$db->qstr($EVALUATION_ID)." AND `proxy_id` IN ('".implode("', '", $PROXY_IDS)."') AND `member_active` = '1' AND `member_acl` = '1'")) && ($total_updated = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->demote_administrator($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully demoted <strong>".$total_updated." administrator".(($total_updated != 1) ? "s" : "")."</strong> to regular member status in the  <strong>".html_encode($evaluation_details["community_title"])."</strong> community. They will no longer be able to perform administrative functions.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
											} else {
												$ERROR++;
												$ERRORSTR[] = "There was a problem demoting these community administrators; the MEdTech Unit has been informed of this error, please try again later.";

												application_log("error", "Unable to demote admins from community_id [".$EVALUATION_ID."]. Database said: ".$db->ErrorMsg());
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
									$ERRORSTR[] = "In order to complete this action, you will need to select at least 1 administrator from the list.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "Unrecognized Admin Action error; the MEdTech Unit has been informed of this error. Please try again later.";

								application_log("error", "The provided action_type [".$ACTION_TYPE."] is invalid.");
							}
							break;
						case "members" :
							if((isset($_POST["member_action"])) && (@in_array(strtolower($_POST["member_action"]), array("delete", "deactivate", "promote")))) {
								if((isset($_POST["member_proxy_ids"])) && (is_array($_POST["member_proxy_ids"])) && (count($_POST["member_proxy_ids"]))) {
									foreach($_POST["member_proxy_ids"] as $proxy_id) {
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

								if((is_array($PROXY_IDS)) && (count($PROXY_IDS))) {
									switch(strtolower($_POST["member_action"])) {
										case "delete" :
											$query	= "DELETE FROM `evaluation_evaluators` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID)." AND `evaluator_value` IN ('".implode("', '", $PROXY_IDS)."')";
											$result	= $db->Execute($query);
											if(($result) && ($total_deleted = $db->Affected_Rows())) {
												if ($MAILING_LISTS["active"]) {
													$mail_list = new MailingList($EVALUATION_ID);
													foreach ($PROXY_IDS as $proxy_id) {
														$mail_list->deactivate_member($proxy_id);
													}
												}
												$SUCCESS++;
												$SUCCESSSTR[] = "You have successfully removed <strong>".$total_deleted." evaluator".(($total_deleted != 1) ? "s" : "")."</strong> from the <strong>".html_encode($evaluation_details["community_title"])."</strong> evaluation.<br /><br />You will now be redirected back to the Manage Members page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\" style=\"font-weight: bold\">click here</a> to continue.";
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
					$ONLOAD[]		= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/evaluations/scheduler?section=members&evaluation=".$EVALUATION_ID."\\'', 5000)";

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
							$SORT_BY	= "CONCAT_WS(', ', b.`lastname`, b.`firstname`) ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
						case "type" :
							$SORT_BY	= "CASE c.`group` WHEN 'guest' THEN 1 WHEN '%' THEN 2 END ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
						case "date" :
						default :
							$SORT_BY	= "a.`member_joined` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
							break;
					}

					if($NOTICE) {
						echo display_notice();
					}
					if($ERROR) {
						echo display_error();
					}
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
									$member_pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $TOTAL_ROWS, ENTRADA_URL."/".$MODULE, replace_query(), "mpv");
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

							$query		= sprintf($query, $SORT_BY, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
							$results	= $db->GetAll($query);
                                                        //echo "______log______"."query: ".$query."<br>";
                                                        //echo "______log______"."total_rows: ".$results["total_rows"]."<br>";

							if($results) {
								if(($TOTAL_PAGES > 1) && ($member_pagination)) {
									echo "<div id=\"pagination-links\">\n";
									echo "Pages: ".$member_pagination->GetPageLinks();
									echo "</div>\n";
								}
								?>
								<form action="<?php echo ENTRADA_URL."/admin/evaluations/scheduler?".replace_query(array("section" => "members", "type" => "members", "step" => 2)); ?>" method="post">
								<table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Community Members">
								<colgroup>
									<col class="modified" />
									<col class="date" />
									<col class="title" />
									<col class="list-status" />
								</colgroup>
								<thead>
									<tr>
										<td class="modified">&nbsp;</td>
										<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("date", "Evaluator Since"); ?></td>
										<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "name") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("name", "Evaluator Name"); ?></td>
										<td class="type<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "type") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("type", "Evaluator Type"); ?></td>
										<td class="list-status<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "list-status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>">Mail List Status</td>
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
									echo "	<td><input type=\"checkbox\" name=\"member_proxy_ids[]\" value=\"".(int) $result["evaluator_value"]."\" /></td>\n";
									echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["member_joined"])."</td>\n";
									echo "	<td><a href=\"".ENTRADA_URL."/people?profile=".html_encode($result["username"])."\"".(($result["proxy_id"] == $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]) ? " style=\"font-weight: bold" : "")."\">".html_encode($result["firstname"]." ".$result["lastname"])."</a></td>\n";
									echo "	<td>".date(DEFAULT_DATE_FORMAT, $result["evaluator_type"])."</td>\n";
									echo "	<td class=\"list-status\"><img src=\"images/".(($MAILING_LISTS["active"]) && $mail_list->users[($result["proxy_id"])]["member_active"] ? "list-status-online.gif" : "list-status-offline.gif")."\" /></td>\n";
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
		<h2 class="tab">Add Evaluators</h2>
		<h2 style="margin-top: 0px">Add Evaluators</h2>
		<form action="<?php echo ENTRADA_URL."/admin/evaluations/scheduler?".replace_query(array("section" => "members", "type" => "add", "step" => 2)); ?>" method="post">
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

															echo lp_multiple_select_inline('evaluator_members', $members, array(
															'width'	=>'100%',
															'ajax'=>true,
															'selectboxname'=>'group and role',
															'default-option'=>'-- Select Group & Role --',
															'category_check_all'=>true));
														} else {
															echo "No One Available [1]";
														}
													} else {
														echo "No One Available [2]";
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
</div>
<script type="text/javascript">
	setupAllTabs(true);

	var people = [[]];
	var ids = [[]];
	//Updates the People Being Added div with all the options
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
			var updater = new Ajax.Updater('evaluator_members_scroll', '<?php echo ENTRADA_URL."/admin/evaluations/scheduler?section=membersadd&action=memberlist";?>',{
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

	header("Location: ".ENTRADA_URL."/admin/evaluations/scheduler");
	exit;
}
?>