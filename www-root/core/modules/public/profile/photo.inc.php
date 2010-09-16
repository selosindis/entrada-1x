<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "Profile Photo";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $_SESSION["details"]["id"];
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile?section=photo", "title" => "Profile Photo");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	if (isset($ACTION)) {
		switch(trim(strtolower($ACTION))) {
			case "privacy-update" :
				/**
				 * This actually changes the privacy settings in their profile.
				 * Note: The sessions variable ($_SESSION["details"]["privacy_level"]) is actually being
				 * changed in index.php on line 268, so that the proper tabs are displayed.
				 */
				if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
					if ($privacy_level > MAX_PRIVACY_LEVEL) {
						$privacy_level = MAX_PRIVACY_LEVEL;
					}
					if ($db->AutoExecute(AUTH_DATABASE.".user_data", array("privacy_level" => $privacy_level), "UPDATE", "`id` = ".$db->qstr($_SESSION["details"]["id"]))) {
						if ((isset($_POST["redirect"])) && (trim($_POST["redirect"]) != "")) {
							header("Location: ".((isset($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"].clean_input(rawurldecode($_POST["redirect"]), array("nows", "url")));
							exit;
						} else {
							header("Location: ".ENTRADA_URL);
							exit;
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "We were unfortunately unable to update your privacy settings at this time. The system administrator has been informed of the error, please try again later.";

						application_log("error", "Unable to update privacy setting. Database said: ".$db->ErrorMsg());
					}

				}
			break;
			case "google-update" :
				if ((bool) $GOOGLE_APPS["active"]) {
					/**
					 * This actually creates a Google Hosted Apps account associated with their profile.
					 * Note: The sessions variable ($_SESSION["details"]["google_id"]) is being
					 * changed in index.php on line 242 to opt-in, which is merely used in the logic
					 * of the first-login page, but only if the user has no google id and hasn't opted out.
					 */
					if (isset($_POST["google_account"])) {
						if ((int) trim($_POST["google_account"])) {
							if (google_create_id()) {
								$SUCCESS++;
								$SUCCESSSTR[] = "<strong>Your new ".$GOOGLE_APPS["domain"]."</strong> account has been created!</strong><br /><br />An e-mail will be sent to ".$_SESSION["details"]["email"]." shortly, containing further instructions regarding account activation.";

								if ((isset($_POST["ajax"])) && ($_POST["ajax"] == "1")) {
									// Clear any open buffers and push through only the success message.
									ob_clear_open_buffers();
									echo display_success($SUCCESSSTR);
									exit;
								}
							} else {
								if ((isset($_POST["ajax"])) && ($_POST["ajax"] == "1")) {
									// $ERRORSTR is set by the google_create_id() function.
									// Clear any open buffers and push through only the error message.
									ob_clear_open_buffers();
									echo display_error($ERRORSTR);
									exit;
								}
							}
						} else {
							$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `google_id` = 'opt-out' WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]));
						}
					}
				}
			break;
			case "privacy-google-update" :
				if ((bool) $GOOGLE_APPS["active"]) {
					/**
					 * This actually creates a Google Hosted Apps account associated with their profile.
					 * Note: The sessions variable ($_SESSION["details"]["google_id"]) is being
					 * changed in index.php on line 242 to opt-in, which is merely used in the logic
					 * of the first-login page, but only if the user has no google id and hasn't opted out.
					 */
					if (isset($_POST["google_account"])) {
						if ((int) trim($_POST["google_account"])) {
							if (google_create_id()) {
								$SUCCESS++;
								$SUCCESSSTR[] = "<strong>Your new ".$GOOGLE_APPS["domain"]."</strong> account has been created!</strong><br /><br />An e-mail will be sent to ".$_SESSION["details"]["email"]." shortly, containing further instructions regarding account activation.";
							}
						} else {
							$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `google_id` = 'opt-out' WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]));
						}
					}
				}
			
				/**
				 * This actually changes the privacy settings in their profile.
				 * Note: The sessions variable ($_SESSION["details"]["privacy_level"]) is actually being
				 * changed in index.php on line 268, so that the proper tabs are displayed.
				 */
				if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
					if ($privacy_level > MAX_PRIVACY_LEVEL) {
						$privacy_level = MAX_PRIVACY_LEVEL;
					}
					if (!$db->AutoExecute(AUTH_DATABASE.".user_data", array("privacy_level" => $privacy_level), "UPDATE", "`id` = ".$db->qstr($_SESSION["details"]["id"]))){
						$ERROR++;
						$ERRORSTR[] = "We were unfortunately unable to update your privacy settings at this time. The system administrator has been informed of the error, please try again later.";

						application_log("error", "Unable to update privacy setting. Database said: ".$db->ErrorMsg());
					}
				}
			break;	
			case "profile-update" :
				if (isset($_POST["tab"]) && $_POST["tab"] == "personal-info") {
					if ((isset($_POST["prefix"])) && (@in_array(trim($_POST["prefix"]), $PROFILE_NAME_PREFIX))) {
						$PROCESSED["prefix"] = trim($_POST["prefix"]);
					} else {
						$PROCESSED["prefix"] = "";
					}

					if ((isset($_POST["office_hours"])) && ($office_hours = clean_input($_POST["office_hours"], array("notags","encode", "trim"))) && ($_SESSION["details"]["group"] != "student")) {
						$PROCESSED["office_hours"] = ((strlen($office_hours) > 100) ? substr($office_hours, 0, 97)."..." : $office_hours);
					} else {
						$PROCESSED["office_hours"] = "";
					}
					
					if ((isset($_POST["email_alt"])) && ($_POST["email_alt"] != "")) {
						if (@valid_address(trim($_POST["email_alt"]))) {
							$PROCESSED["email_alt"] = strtolower(trim($_POST["email_alt"]));
						} else {
							$ERROR++;
							$ERRORSTR[] = "The secondary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address or leave this field empty if you do not wish to display one.";
						}
					} else {
						$PROCESSED["email_alt"] = "";
					}
		
					if ((isset($_POST["telephone"])) && (strlen(trim($_POST["telephone"])) >= 10) && (strlen(trim($_POST["telephone"])) <= 25)) {
						$PROCESSED["telephone"] = strtolower(trim($_POST["telephone"]));
					} else {
						$PROCESSED["telephone"] = "";
					}
		
					if ((isset($_POST["fax"])) && (strlen(trim($_POST["fax"])) >= 10) && (strlen(trim($_POST["fax"])) <= 25)) {
						$PROCESSED["fax"] = strtolower(trim($_POST["fax"]));
					} else {
						$PROCESSED["fax"] = "";
					}
		
					if ((isset($_POST["address"])) && (strlen(trim($_POST["address"])) >= 6) && (strlen(trim($_POST["address"])) <= 255)) {
						$PROCESSED["address"] = ucwords(strtolower(trim($_POST["address"])));
					} else {
						$PROCESSED["address"] = "";
					}
		
					if ((isset($_POST["city"])) && (strlen(trim($_POST["city"])) >= 3) && (strlen(trim($_POST["city"])) <= 35)) {
						$PROCESSED["city"] = ucwords(strtolower(trim($_POST["city"])));
					} else {
						$PROCESSED["city"] = "";
					}
		
					if ((isset($_POST["postcode"])) && (strlen(trim($_POST["postcode"])) >= 5) && (strlen(trim($_POST["postcode"])) <= 12)) {
						$PROCESSED["postcode"] = strtoupper(trim($_POST["postcode"]));
					} else {
						$PROCESSED["postcode"] = "";
					}
		
					if ((isset($_POST["country_id"])) && ($tmp_input = clean_input($_POST["country_id"], "int"))) {
						$query = "SELECT * FROM `global_lu_countries` WHERE `countries_id` = ".$db->qstr($tmp_input);
						$result = $db->GetRow($query);
						if ($result) {
							$PROCESSED["country_id"] = $tmp_input;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The selected country does not exist in our countries database. Please select a valid country.";
		
							application_log("error", "Unknown countries_id [".$tmp_input."] was selected. Database said: ".$db->ErrorMsg());
						}
					} else {
						$ERROR++;
						$ERRORSTR[]	= "You must select a country.";
					}
		
					if ((isset($_POST["prov_state"])) && ($tmp_input = clean_input($_POST["prov_state"], array("trim", "notags")))) {
						$PROCESSED["province_id"] = 0;
						$PROCESSED["province"] = "";
		
						if (ctype_digit($tmp_input) && ($tmp_input = (int) $tmp_input)) {
							if ($PROCESSED["country_id"]) {
								$query = "SELECT * FROM `global_lu_provinces` WHERE `province_id` = ".$db->qstr($tmp_input)." AND `country_id` = ".$db->qstr($PROCESSED["country_id"]);
								$result = $db->GetRow($query);
								if (!$result) {
									$ERROR++;
									$ERRORSTR[] = "The province / state you have selected does not appear to exist in our database. Please selected a valid province / state.";
								}
							}
		
							$PROCESSED["province_id"] = $tmp_input;
						} else {
							$PROCESSED["province"] = $tmp_input;
						}
		
						$PROCESSED["prov_state"] = ($PROCESSED["province_id"] ? $PROCESSED["province_id"] : ($PROCESSED["province"] ? $PROCESSED["province"] : ""));
					}
				} elseif (isset($_POST["tab"]) && $_POST["tab"] == "privacy-level") {
	
					/**
					 * This actually changes the privacy settings in their profile.
					 * Note: The sessions variable ($_SESSION["details"]["privacy_level"]) is actually being
					 * changed in index.php on line 268, so that the proper tabs are displayed.
					 */
					if ((isset($_POST["privacy_level"])) && ($privacy_level = (int) trim($_POST["privacy_level"]))) {
						if ($privacy_level > MAX_PRIVACY_LEVEL) {
							$privacy_level = MAX_PRIVACY_LEVEL;
						}

						$PROCESSED["privacy_level"] = $privacy_level;
					} else {
						$PROCESSED["privacy_level"] = 1;
					}
				} elseif (isset($_POST["tab"]) && $_POST["tab"] == "profile-photo") {
					if ((!$ERROR) && (isset($_FILES["photo_file"])) && ($_FILES["photo_file"]["error"] != 4)) {
						switch($_FILES["photo_file"]["error"]) {
							case 0 :
								$photo_mimetype = clean_input($_FILES["photo_file"]["type"], array("trim", "lowercase"));
								if (in_array($photo_mimetype, array_keys($VALID_MIME_TYPES))) {
									if (($photo_filesize = (int) trim($_FILES["photo_file"]["size"])) <= $VALID_MAX_FILESIZE) {
										$PROCESSED_PHOTO["photo_mimetype"]	= $photo_mimetype;
										$PROCESSED_PHOTO["photo_filesize"]	= $photo_filesize;

										$photo_file_extension				= strtoupper($VALID_MIME_TYPES[strtolower(trim($_FILES["photo_file"]["type"]))]);

										if ((!defined("STORAGE_USER_PHOTOS")) || (!@is_dir(STORAGE_USER_PHOTOS)) || (!@is_writable(STORAGE_USER_PHOTOS))) {
											$ERROR++;
											$ERRORSTR[] = "There is a problem with the gallery storage directory on the server; the system administrator has been informed of this error, please try again later.";

											application_log("error", "The community gallery storage path [".STORAGE_USER_PHOTOS."] does not exist or is not writable.");
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The file that you have uploaded does not appear to be a valid image. Please ensure that you upload a JPEG, GIF or PNG file.";
								}
							break;
							case 1 :
							case 2 :
								$ERROR++;
								$ERRORSTR[] = "The photo that was uploaded is larger than ".readable_size($VALID_MAX_FILESIZE).". Please make the photo smaller and try again.";
							break;
							case 3 :
								$ERROR++;
								$ERRORSTR[]	= "The photo that was uploaded did not complete the upload process or was interrupted; please try again.";
							break;
							case 6 :
							case 7 :
								$ERROR++;
								$ERRORSTR[]	= "Unable to store the new photo file on the server; the system administrator has been informed of this error, please try again later.";

								application_log("error", "Community photo file upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
							break;
							default :
								application_log("error", "Unrecognized photo file upload error number [".$_FILES["filename"]["error"]."].");
							break;
						}

						if (!$ERROR) {
							$PROCESSED_PHOTO["proxy_id"]			= $_SESSION["details"]["id"];
							$PROCESSED_PHOTO["photo_active"]		= 1;
							$PROCESSED_PHOTO["photo_type"]			= 1;
							$PROCESSED_PHOTO["updated_date"]		= time();

							if ($photo_id = $db->GetOne("SELECT `photo_id` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `photo_type` = 1")) {
								if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "UPDATE", "photo_id = ".$photo_id)) {
									if ($photo_id) {
										if (process_user_photo($_FILES["photo_file"]["tmp_name"], $photo_id)) {
											$SUCCESS++;
											$SUCCESSSTR[]	= "You have successfully uploaded a new profile photo.";
										}
									}
								}
							} else {
								if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "INSERT")) {
									if ($photo_id = $db->Insert_Id()) {
										if (process_user_photo($_FILES["photo_file"]["tmp_name"], $photo_id)) {

											$SUCCESS++;
											$SUCCESSSTR[]	= "You have successfully uploaded a new profile photo.";
										}
									}
								}
							}
						}
					}

					if ($_FILES["photo_file"]["error"] == 4 && isset($_POST["deactivate_photo"])) {
						$PROCESSED_PHOTO_STATUS = array();
						$PROCESSED_PHOTO_STATUS["photo_active"] = 0;
					} elseif ($_FILES["photo_file"]["error"] == 4 && !isset($_POST["deactivate_photo"])) {
						$PROCESSED_PHOTO_STATUS = array();
						$PROCESSED_PHOTO_STATUS["photo_active"] = 1;
					} elseif ($_FILES["photo_file"]["error"] != 4 && isset($_POST["deactivate_photo"]) && $_POST["deactivate_photo"]) {
						$NOTICE++;
						$NOTICESTR[] = "You cannot deactivate a newly uploaded photo, please try again without uploading a new photo.";
					}
				} elseif (isset($_POST["tab"]) && $_POST["tab"] == "notifications") {
					if ($_POST["enable-notifications"] == 1) {
						if ($_POST["notify_announcements"] && is_array($_POST["notify_announcements"])) {
							$notify_announcements = $_POST["notify_announcements"]; 
						} else {
							$notify_announcements = array();
						}
						if ($_POST["notify_events"] && is_array($_POST["notify_events"])) {
							$notify_events = $_POST["notify_events"];
						} else {
							$notify_events = array();
						}
						if ($_POST["notify_polls"] && is_array($_POST["notify_polls"])) {
							$notify_polls = $_POST["notify_polls"];
						} else {
							$notify_polls = array();
						}
						if ($_POST["notify_members"] && is_array($_POST["notify_members"])) {
							$notify_members = $_POST["notify_members"];
						} else {
							$notify_members = array();
						}
						
						$user_notifications = $db->GetOne("SELECT `notifications` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]));
						if (((int)$user_notifications) != 1) {
							if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `notifications` = '1' WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]))) {
								$ERROR++;
								application_log("error", "Notification settings for the Proxy ID [".$_SESSION["details"]["id"]."] could not be activated. Database said: ".$db->ErrorMsg());
							}
						}
						
						$query = "SELECT `community_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `member_active` = '1'";
						$communities = $db->GetAll($query);
						if ($communities) {
							foreach ($communities as $community) {
								$PROCESSED_NOTIFICATIONS[$community["community_id"]]["announcements"] = (isset($notify_announcements[$community["community_id"]]) && $notify_announcements[$community["community_id"]] ? 1 : 0);
								$PROCESSED_NOTIFICATIONS[$community["community_id"]]["events"] = (isset($notify_events[$community["community_id"]]) && $notify_events[$community["community_id"]] ? 1 : 0);
								$PROCESSED_NOTIFICATIONS[$community["community_id"]]["polls"] = (isset($notify_polls[$community["community_id"]]) && $notify_polls[$community["community_id"]] ? 1 : 0);
								$PROCESSED_NOTIFICATIONS[$community["community_id"]]["members"] = (isset($notify_members[$community["community_id"]]) && $notify_members[$community["community_id"]] ? 1 : 0);
							}
						}
						if ($PROCESSED_NOTIFICATIONS && is_array($PROCESSED_NOTIFICATIONS)) {
							if ($db->Execute("DELETE FROM `community_notify_members` WHERE `proxy_id` = ".$db->qstr($_SESSION["details"]["id"])." AND `notify_type` IN ('announcement', 'event', 'poll', 'members')")) {
								foreach ($PROCESSED_NOTIFICATIONS as $community_id => $notify) {
									if (!$ERROR) {
										if (!$db->Execute("	INSERT INTO `community_notify_members` 
															(`proxy_id`, `community_id`, `record_id`, `notify_type`, `notify_active`) VALUES 
															(".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'announcement', ".$notify["announcements"]."),
															(".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'event', ".$notify["events"]."),
															(".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'members', ".$notify["members"]."),
															(".$db->qstr($_SESSION["details"]["id"]).", ".$db->qstr($community_id).", ".$db->qstr($community_id).", 'poll', ".$notify["polls"].")")) {
											$ERROR++;
											application_log("error", "Community notifications settings for proxy ID [".$_SESSION["details"]["id"]."] could not be updated. Database said: ".$db->ErrorMsg());
										}
									}
								}
								if (!$ERROR) {
									$SUCCESS++;
									$SUCCESSSTR[] = "Your community notification settings have been successfully updated.";
								}
							} else {
								$ERROR++;
								application_log("error", "Community notifications settings for proxy ID [".$_SESSION["details"]["id"]."] could not be deleted. Database said: ".$db->ErrorMsg());
							}
						}
						if ($ERROR) {
							$ERRORSTR[] = "There was an issue while attempting to set your notification settings. The system administrator has been informed of the problem, please try again later.";	
						}
					} else {
						$user_notifications = $db->GetOne("SELECT `notifications` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]));
						if (((int)$user_notifications) != 0) {
							if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_data` SET `notifications` = '0' WHERE `id` = ".$db->qstr($_SESSION["details"]["id"]))) {
								$ERROR++;
								application_log("error", "Notification settings for the Proxy ID [".$_SESSION["details"]["id"]."] could not be deactivated. Database said: ".$db->ErrorMsg());
							}
						}
					}
				}

				if (isset($PROCESSED_PHOTO_STATUS) && !$db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO_STATUS, "UPDATE", "proxy_id=".$db->qstr($_SESSION["details"]["id"])." AND photo_type = 1")) {
					$ERROR++;
					$ERRORSTR[] = "There was an issue trying to deactivate your current photo.";
				}

				if (isset($_POST["tab"]) && $_POST["tab"] != "profile-photo" && $_POST["tab"] != "notifications") {
					if (!$ERROR) {
						if ($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "UPDATE", "`id` = ".$db->qstr($_SESSION["details"]["id"]))) {
							$SUCCESS++;
							$SUCCESSSTR[] = "Your profile has been successfully updated. Thank-you.";

							application_log("success", "User successfully updated their profile.");

						} else {
							$ERROR++;
							$ERRORSTR[] = "We were unfortunately unable to update your profile at this time. The system administrator has been informed of the problem, please try again later.";

							application_log("error", "Unable to update user profile. Database said: ".$db->ErrorMsg());
						}
					}
				}
				
			break;
			case "assistant-add" :
				if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'create')) {
					$access_timeframe = validate_calendars("valid", true, true);

					if (!$ERROR) {
						if ((isset($access_timeframe["start"])) && ((int) $access_timeframe["start"])) {
							$PROCESSED["valid_from"]	= (int) $access_timeframe["start"];
						}

						if ((isset($access_timeframe["finish"])) && ((int) $access_timeframe["finish"])) {
							$PROCESSED["valid_until"] = (int) $access_timeframe["finish"];
						}

						if ((isset($_POST["assistant_id"])) && ($proxy_id = (int) trim($_POST["assistant_id"]))) {
							if ($proxy_id != $_SESSION["details"]["id"]) {
								$query	= "
									SELECT a.`id` AS `proxy_id`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id` AND b.`app_id`='1' AND b.`account_active`='true' AND b.`group`<>'student'
									WHERE a.`id`=".$db->qstr($proxy_id);

								$result	= $db->GetRow($query);
								if ($result) {
									$PROCESSED["assigned_by"]	= $_SESSION["details"]["id"];
									$PROCESSED["assigned_to"]	= $result["proxy_id"];
									$fullname					= $result["fullname"];

									$query	= "SELECT * FROM `permissions` WHERE `assigned_by`=".$db->qstr($PROCESSED["assigned_by"])." AND `assigned_to`=".$db->qstr($PROCESSED["assigned_to"]);
									$result	= $db->GetRow($query);
									if ($result) {
										if ($db->AutoExecute("permissions", $PROCESSED, "UPDATE", "permission_id=".$db->qstr($result["permission_id"]))) {
											$SUCCESS++;
											$SUCCESSSTR[] = "You have successfully updated <strong>".html_encode($fullname)."'s</strong> access permissions to your account.";

											application_log("success", "Updated permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account from ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_from"])." until ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_until"]));
										} else {
											$ERROR++;
											$ERRORSTR[] = "We were unable to update <strong>".html_encode($fullname)."'s</strong> access permissions to your account at this time. The system administrator has been informed of this, please try again later.";

											application_log("error", "Unable to update permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account. Database said: ".$db->ErrorMsg());
										}
									} else {
										if ($db->AutoExecute("permissions", $PROCESSED, "INSERT")) {
											$SUCCESS++;
											$SUCCESSSTR[] = "You successfully gave <strong>".html_encode($fullname)."</strong> access permissions to your account.";

											application_log("success", "Added permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account from ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_from"])." until ".date(DEFAULT_DATE_FORMAT, $PROCESSED["valid_until"]));
										} else {
											$ERROR++;
											$ERRORSTR[] = "We were unable to give <strong>".html_encode($fullname)."</strong> access permissions to your account at this time. The system administrator has been informed of this, please try again later.";

											application_log("error", "Unable to insert permissions for proxy_id [".$PROCESSED["assigned_by"]."] who is allowing [".$PROCESSED["assigned_by"]."] accecss to their account. Database said: ".$db->ErrorMsg());
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "The person that have selected to add as an assistant either does not exist in this system, or their account is not currently active.<br /><br />Please contact Denise Jones in the Undergrad office (613-533-6000 x77804) to get an account for the requested individual.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "You cannot add yourself as your own assistant, there is no need to do so.";
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must enter, then select the name of the person you wish to give access to your account permissions.";
						}
					}
				} else {
					$ERROR++;
				$ERRORSTR[] = "Your account does not have the required access levels to add assistants to your profile.";

				application_log("error", "User tried to add assistants to profile without an acceptable group & role.");
				}
			break;
			case "assistant-remove" :
				if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'delete')) {
					if ((isset($_POST["remove"])) && (@is_array($_POST["remove"])) && (@count($_POST["remove"]))) {
						foreach ($_POST["remove"] as $assigned_to => $permission_id) {
							$permission_id = (int) trim($permission_id);
							if ($permission_id) {
								if ($db->Execute("DELETE FROM `permissions` WHERE `permission_id`=".$db->qstr($permission_id)." AND `assigned_by`=".$db->qstr($_SESSION["details"]["id"]))) {

									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully removed ".get_account_data("fullname", (int) $assigned_to)." from to accessing your permission levels.";

									application_log("success", "Removed assigned_to [".$assigned_to."] permissions from proxy_id [".$_SESSION["details"]["id"]."] account.");
								} else {
									$ERROR++;
									$ERRORSTR[] = "Unable to remove ".get_account_data("fullname", (int) $assigned_to)." from to accessing your permission levels. The system administrator has been informed of this error; however, if this is urgent, please contact us be telephone at: 613-533-6000 x74918.";

									application_log("error", "Failed to remove assigned_to [".$assigned_to."] permissions from proxy_id [".$_SESSION["details"]["id"]."] account. Database said: ".$db->ErrorMsg());
								}
							}
						}
					} else {

					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "Your account does not have the required access levels to remove assistants from your profile.";

					application_log("error", "User tried to remove assistants from profile without an acceptable group & role.");
				}
			break;
			default :
				continue;
			break;
		}
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";

	if (isset($_GET["tab"]) && $_GET["tab"] == "photo") {
		$load_photo_tab = true;
	}

	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
		$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload");
	}
	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
		$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
	}
	
	?>

	<h1>My Profile</h1>

	<?php
	if ($ERROR) {
		fade_element("out", "display-error-box");
		echo display_error();
	}

	if ($SUCCESS) {
		fade_element("out", "display-success-box");
		echo display_success();
	}

	if ($NOTICE) {
		fade_element("out", "display-notice-box");
		echo display_notice();
	}

	$sidebar_profile_menu = "";
	new_sidebar_item("Profile Menu", $sidebar_profile_menu, "profile-menu", "open",SIDEBAR_PREPEND);
	
	
	$ONLOAD[] = "provStateFunction(\$F($('profile-update')['country_id']))";
	
	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($_SESSION["details"]["id"]);
	$result	= $db->GetRow($query);
	if ($result) {
			
			?>
			
			<script type="text/javascript">

			function provStateFunction(country_id) {
				var url='<?php echo webservice_url("province"); ?>';
				<?php
				    if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
						$source_arr = $PROCESSED;
				    } else {
				    	$source_arr = $result;
				    }
				    $province = $source_arr["province"];
				    $province_id = $source_arr["province_id"];
				    $prov_state = ($province) ? $province : $province_id;
				?>
				
				url = url + '?countries_id=' + country_id + '&prov_state=<?php echo $prov_state; ?>';
				new Ajax.Updater($('prov_state_div'), url,
					{
						method:'get',
						onComplete: function (init_run) {
							
							if ($('prov_state').type == 'select-one') {
								$('prov_state_label').removeClassName('form-nrequired');
								$('prov_state_label').addClassName('form-required');
								if (!init_run) 
									$("prov_state").selectedIndex = 0;
								
								
							} else {
								
								$('prov_state_label').removeClassName('form-required');
								$('prov_state_label').addClassName('form-nrequired');
								if (!init_run) 
									$("prov_state").clear();
								
								
							}
						}.curry(!provStateFunction.initialzed)
					});
				provStateFunction.initialzed = true;
				
			}
			provStateFunction.initialzed = false;

			</script>
		
		<div class="tab-pane" id="profile-tabs">
			<div class="tab-page">
				<h2 class="tab">Personal Information</h2>

				<h1 style="margin-top: 0px">Personal Information</h1>
				This section allows you to update your <?php echo APPLICATION_NAME; ?> user profile information. Please note that this information does not necessarily reflect any information stored at the main University. <span style="background-color: #FFFFCC; padding-left: 5px; padding-right: 5px">This is not your official university contact information.</span>
				<br /><br />

				<form name="profile-update" id="profile-update" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
					<input type="hidden" name="action" value="profile-update" />
					<input type="hidden" name="tab" value="personal-info" />
					<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="My <?php echo APPLICATION_NAME;?> Profile Information">
						<colgroup>
							<col style="width: 25%" />
							<col style="width: 75%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" class="button" value="Update Profile" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td><strong>Last Login:</strong></td>
								<td><?php echo ((!$_SESSION["details"]["lastlogin"]) ? "Your first login" : date(DEFAULT_DATE_FORMAT, $_SESSION["details"]["lastlogin"])); ?></td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td><strong>Username:</strong></td>
								<td><?php echo html_encode($_SESSION["details"]["username"]); ?></td>
							</tr>
							<tr>
								<td><strong>Password:</strong></td>
								<td><a href="<?php echo PASSWORD_CHANGE_URL; ?>">Click here to change password</a></td>
							</tr>
							<tr>
								<td><strong>Account Type:</strong></td>
								<td><?php echo ucwords($_SESSION["details"]["group"])." &rarr; ".ucwords($_SESSION["details"]["role"]); ?></td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td><strong>Organisation:</strong></td>
								<td>
									<?php
									$query		= "SELECT `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$_SESSION['details']['organisation_id'];
									$oresult	= $db->GetRow($query);
									if($oresult) {
										echo $oresult['organisation_title'];
									}
									?>
								</td>
							</tr>
							<?php if (isset($_SESSION["details"]["grad_year"])) : ?>
							<tr>
								<td><strong>Graduating Year:</strong></td>
								<td>Class of <?php echo html_encode($_SESSION["details"]["grad_year"]); ?></td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<?php endif; ?>
							<tr>
								<td><label for="prefix"><strong>Full Name:</strong></label></td>
								<td>
									<select id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
										<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
										<?php
										if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
											foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
												echo "<option value=\"".html_encode($prefix)."\"".(($result["prefix"] == $prefix) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
											}
										}
										?>
									</select>
									<?php echo html_encode($result["firstname"]." ".$result["lastname"]); ?>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td><strong>Primary E-Mail:</strong></td>
								<td><a href="mailto:<?php echo html_encode($result["email"]); ?>"><?php echo html_encode($result["email"]); ?></a></td>
							</tr>
							<tr>
								<td><label for="email_alt"><strong>Secondary E-Mail:</strong></label></td>
								<td>
									<input type="text" id="email_alt" name="email_alt" value="<?php echo html_encode($result["email_alt"]); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<?php
							if (((bool) $GOOGLE_APPS["active"]) && (isset($GOOGLE_APPS["groups"])) && (is_array($GOOGLE_APPS["groups"])) && (in_array($_SESSION["details"]["group"], $GOOGLE_APPS["groups"]))) {
								?>
								<tr>
									<td style="vertical-align: top"><label for="email_alt" style="font-weight: bold">Google Account:</label></td>
									<td style="vertical-align: top">
										<div id="google-account-details">
											<?php
											if (($result["google_id"] == "") || ($result["google_id"] == "opt-out") || ($result["google_id"] == "opt-in") || ($_SESSION["details"]["google_id"] == "opt-in")) {
												echo "Your ".$GOOGLE_APPS["domain"]." account is <strong>not active</strong>. ( <a href=\"javascript: create_google_account()\" class=\"action\">create my account</a> )";
											} else {
												$google_address = html_encode($result["google_id"]."@".$GOOGLE_APPS["domain"]);
												echo "<a href=\"mailto:".$google_address."\">".$google_address."</a> ( <a href=\"http://webmail.".$GOOGLE_APPS["domain"]."\" class=\"action\" target=\"_blank\">".$GOOGLE_APPS["domain"]." webmail</a>)";
											}
											?>
										</div>
										<script type="text/javascript">
										function create_google_account() {
											$('google-account-details').update('<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif\" width=\"16\" height=\"16\" alt=\"Please wait\" border=\"0\" style=\"margin-right: 2px; vertical-align: middle\" /> <span class=\"content-small\">Please wait while your account is created ...</span>');
											new Ajax.Updater('google-account-details', '<?php echo ENTRADA_URL; ?>/profile', { method: 'post', parameters: { 'action' : 'google-update', 'google_account' : 1, 'ajax' : 1 }});
										}
										</script>
									</td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<?php
							}
							?>
							<tr>
								<td><label for="telephone"><strong>Telephone Number:</strong></label></td>
								<td>
									<input type="text" id="telephone" name="telephone" value="<?php echo html_encode($result["telephone"]); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
									<span class="content-small">(<strong>Example:</strong> 613-533-6000 x74918)</span>
								</td>
							</tr>
							<tr>
								<td><label for="fax"><strong>Fax Number:</strong></label></td>
								<td>
									<input type="text" id="fax" name="fax" value="<?php echo html_encode($result["fax"]); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
									<span class="content-small">(<strong>Example:</strong> 613-533-3204)</span>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							
							<tr>
								<td><label for="country_id" class="form-required">Country</label></td>
								<td>
									<?php
									$countries = fetch_countries();
									if ((is_array($countries)) && (count($countries))) {
										
										$country_id = ($PROCESSED["country_id"])?$PROCESSED["country_id"]:$result["country_id"];
										
										echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
										echo "<option value=\"0\"".((!country_id) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
										foreach ($countries as $country) {
											echo "<option value=\"".(int) $country["countries_id"]."\"".(($country_id == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
										}
										echo "</select>\n";
									} else {
										echo "<input type=\"hidden\" id=\"country_id\" name=\"country_id\" value=\"0\" />\n";
										echo "Country information not currently available.\n";
									}
									?>
								</td>
							</tr>
							<tr>
								<td><label id="prov_state_label" for="prov_state_div" class="form-nrequired">Province / State</label></td>
								<td>
									<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
								</td>
							</tr>
							<tr>
								<td><label for="city"><strong>City:</strong></label></td>
								<td>
									<input type="text" id="city" name="city" value="<?php echo html_encode($result["city"]); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
								</td>
							</tr>
							<tr>
								<td><label for="address"><strong>Address:</strong></label></td>
								<td>
									<input type="text" id="address" name="address" value="<?php echo html_encode($result["address"]); ?>" style="width: 250px; vertical-align: middle" maxlength="255" />
								</td>
							</tr>
						 	<tr>
								<td><label for="postcode"><strong>Postal Code:</strong></label></td>
								<td>
									<input type="text" id="postcode" name="postcode" value="<?php echo html_encode($result["postcode"]); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
									<span class="content-small">(<strong>Example:</strong> K7L 3N6)</span>
								</td>
							</tr>
							<?php
							if ($_SESSION["details"]["group"] != "student") {
								$ONLOAD[] = "setMaxLength()";
								?>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td style="vertical-align: top"><label for="hours"><strong>Office Hours:</strong></label></td>
									<td>
										<textarea id="office_hours" name="office_hours" style="width: 254px; height: 40px;" maxlength="100"><?php echo html_encode($result["office_hours"]); ?></textarea>
									</td>
								</tr>
								<?php
							}
							?>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div class="tab-page">
				<h2 class="tab">Profile Photo</h2>

				<h1 style="margin-top: 0px;">Profile Photo</h1>
				<form action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
					<input type="hidden" name="action" value="profile-update" />
					<input type="hidden" name="tab" value="profile-photo" />
					<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="My <?php echo APPLICATION_NAME;?> Profile Photo">
						<colgroup>
							<col style="width: 25%" />
							<col style="width: 75%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" class="button" value="Update Photo" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td style="vertical-align: top; text-align: center">
									<table>
										<tr>
											<td>
												<div style="position: relative; width: 74px; height: 103px;">
													<img src="<?php echo webservice_url("photo", array($_SESSION["details"]["id"], "official"))."/".time(); ?>" width="72" height="100" class="cursor" id="profile_pic_<?php echo $result["id"] ?>" name="profile_pic" style="border: 1px #666666 solid; position: relative;"/>
												</div>
											</td>
											<td>
												<div style="position: relative; width: 74px; height: 103px;">
													<img src="<?php echo webservice_url("photo", array($_SESSION["details"]["id"], "upload"))."/".time(); ?>" width="72" height="100" class="cursor" id="alt_profile_pic_<?php echo $result["id"]; ?>" name="profile_pic" style="border: 1px #666666 solid; position: relative;"/>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<span class="content-small">Official</span>
											</td>
											<td>
												<span class="content-small">Uploaded</span>
											</td>
										</tr>
									</table>
								</td>
								<td style="vertical-align: top">
									<label for="photo_file" class="form-nrequired" style="margin-right: 5px">Upload New Photo:</label>
									<input type="file" id="photo_file" name="photo_file" />

									<div class="content-small" style="margin-top: 10px; width: 435px">
										<strong>Notice:</strong> You may upload JPEG, GIF or PNG images under <?php echo readable_size($VALID_MAX_FILESIZE); ?> only and any image larger than <?php echo $VALID_MAX_DIMENSIONS["photo-width"]."px by ".$VALID_MAX_DIMENSIONS["photo-height"]; ?>px (width by height) will be automatically resized.
									</div>

									<?php
									$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($result["id"])." AND `photo_active` = '1'";
									$uploaded_photo = $db->GetRow($query);
									if ($uploaded_photo) {
										?>
										<div style="margin-top: 20px">
											<input type="checkbox" id="deactivate_photo" name="deactivate_photo" value="1" style="vertical-align: middle" />
											<label for="deactivate_photo" class="form-nrequired" style="vertical-align: middle">Deactivate your uploaded photo.</label>
										</div>
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div class="tab-page">
				<h2 class="tab">Privacy Level Setting</h2>
				<h1 style="margin-top: 0px">Privacy Level Setting</h1>
				<form action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
					<input type="hidden" name="action" value="profile-update" />
					<input type="hidden" name="tab" value="privacy-level" />
					<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="My <?php echo APPLICATION_NAME;?> Profile Privacy">
						<colgroup>
							<col style="width: 25%" />
							<col style="width: 75%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="2" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" class="button" value="Update Privacy" />
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td colspan="2">
									<table style="width: 100%" cellspacing="4" cellpadding="2" border="0">
										<colgroup>
											<col style="width: 3%" />
											<col style="width: 97%" />
										</colgroup>
										<tbody>
											<tr>
												<td style="vertical-align: top"><input type="radio" id="privacy_level_3" name="privacy_level" value="3"<?php echo (($result["privacy_level"] == "3") ? " checked=\"checked\"" : ""); ?> /></td>
												<td style="vertical-align: top">
													<label for="privacy_level_3"><strong>Complete Profile</strong>: show the information I choose to provide.</label><br />
													<span class="content-small">This means that normal logged in users will be able to view any information you provide in the <strong>My Profile</strong> section. You can provide as much or as little information as you would like; however, whatever you provide will be displayed.</span>
												</td>
											</tr>
											<tr>
												<td style="vertical-align: top"><input type="radio" id="privacy_level_2" name="privacy_level" value="2"<?php echo (($result["privacy_level"] == "2") ? " checked=\"checked\"" : ""); ?> /></td>
												<td style="vertical-align: top">
													<label for="privacy_level_2"><strong>Typical Profile</strong>: show only basic information about me.</label><br />
													<span class="content-small">This means that normal logged in users will only be able to view your name, email address, role, official photo and uploaded photo if you have added one, regardless of how much information you provide in the <strong>My Profile</strong> section.</span>
												</td>
											</tr>
											<tr>
												<td style="vertical-align: top"><input type="radio" id="privacy_level_1" name="privacy_level" value="1"<?php echo (($result["privacy_level"] == "1") ? " checked=\"checked\"" : ""); ?> /></td>
												<td style="vertical-align: top">
													<label for="privacy_level_1"><strong>Minimal Profile</strong>: show minimal information about me.</label><br />
													<span class="content-small">This means that normal logged in users will only be able to view your name and role. In other words, people will not be able to get your e-mail address or other contact information.</span>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<?php
			if ((defined("COMMUNITY_NOTIFICATIONS_ACTIVE")) && ((bool) COMMUNITY_NOTIFICATIONS_ACTIVE)) {
				?>
				<div class="tab-page">
					<h2 class="tab">Notifications</h2>
					<h1 style="margin-top: 0px">Community Notifications</h1>
					<form action="<?php echo ENTRADA_URL; ?>/profile" method="post">
					<input type="hidden" name="action" value="profile-update" />
					<input type="hidden" name="tab" value="notifications" />
					<table style="width: 100%;" cellspacing="1" cellpadding="1" border="0" summary="My MEdTech Profile">
					<thead>
						<tr>
							<td>
								<table style="width: 100%" cellspacing="4" cellpadding="2" border="0">
									<colgroup>
										<col style="width: 3%" />
										<col style="width: 97%" />
									</colgroup>
									<tbody>
										<tr>
											<td style="vertical-align: top"><input type="radio" id="enabled-notifications" name="enable-notifications" onclick="$('notifications-toggle').show()" value="1"<?php echo ($result["notifications"] ? " checked=\"checked\"" : ""); ?> /></td>
											<td style="vertical-align: top">
												<label for="enabled-notifications"><strong>Enable</strong> Community Notifications</label><br />
												<span class="content-small">You will be able to receive notifications from communities and enable notifications for different types of content.</span>
											</td>
										</tr>
										<tr>
											<td style="vertical-align: top"><input type="radio" id="disabled-notifications" name="enable-notifications" onclick="$('notifications-toggle').hide()" value="0"<?php echo (!$result["notifications"] ? " checked=\"checked\"" : ""); ?> /></td>
											<td style="vertical-align: top">
												<label for="disabled-notifications"><strong>Disable</strong> Community Notifications</label><br />
												<span class="content-small">You will no longer receive notifications from any communities and will not be able to enable notifications for any content.</span>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="submit" class="button" value="Save Changes" />
							</td>
						</tr>
					</tfoot>
					<tbody id="notifications-toggle"<?php echo (($result["notifications"]) ? "" : " style=\"display: none\""); ?>>
						<tr>
							<td>
								<h2>Notification Options</h2>
								Please select which notifications you would like to receive for each community you are a member of. If you are a community administrator, then you will also have the option of being notified when members join or leave your community.
								<?php
								$query = "	SELECT DISTINCT(a.`community_id`), a.`member_acl`, e.`community_title`, b.`notify_active` AS `announcements`, c.`notify_active` AS `events`, d.`notify_active` AS `polls`, f.`notify_active` AS `members`
											FROM `community_members` AS a
											LEFT JOIN `community_notify_members` AS b
											ON a.`community_id` = b.`community_id`
											AND a.`proxy_id` = b.`proxy_id`
											AND b.`notify_type` = 'announcement'
											LEFT JOIN `community_notify_members` AS c
											ON a.`community_id` = c.`community_id`
											AND a.`proxy_id` = c.`proxy_id`
											AND c.`notify_type` = 'event'
											LEFT JOIN `community_notify_members` AS d
											ON a.`community_id` = d.`community_id`
											AND a.`proxy_id` = d.`proxy_id`
											AND d.`notify_type` = 'poll'
											LEFT JOIN `communities` AS e
											ON a.`community_id` = e.`community_id`
											LEFT JOIN `community_notify_members` AS f
											ON a.`community_id` = f.`community_id`
											AND a.`proxy_id` = f.`proxy_id`
											AND f.`notify_type` = 'members'
											WHERE a.`proxy_id` = ".$db->qstr($PROXY_ID)."
											AND a.`member_active` = '1'";
								$community_notifications = $db->GetAll($query);
								if ($community_notifications) {
									?>
									<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
									<tbody>
										<tr>
											<td style="width: 50%; vertical-align: top;">
												<ul class="notify-communities">
												<?php
												$count = 0;
												foreach ($community_notifications as $key => $community) {
													$count++;
													if (($count != ((int)(round(count($community_notifications)/2))+1))) {
														?>
														<li>
															<strong><?php echo $community["community_title"]; ?></strong>
															<ul class="notifications">
																<li><label><input type="checkbox" name="notify_announcements[<?php echo $community["community_id"]; ?>]" value="1"<?php echo (!isset($community["announcements"]) || $community["announcements"] == 1 ? " checked=\"checked\"" : ""); ?> /> Announcements</label></li>
																<li><label><input type="checkbox" name="notify_events[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["events"]) || $community["events"] == 1 ? " checked=\"checked\"" : ""); ?> /> Events</label></li>
																<li><label><input type="checkbox" name="notify_polls[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["polls"]) || $community["polls"] == 1 ? " checked=\"checked\"" : ""); ?> /> Polls</label></li>
																<?php
																if ($community["member_acl"]) {
																	?>
																	<li><label><input type="checkbox" name="notify_members[<?php echo $community["community_id"]; ?>]" value="1" <?php echo ($community["members"] == 1 ? " checked=\"checked\"" : ""); ?> /> Members Joining / Leaving (Admin Only)</label></li>
																	<?php
																}
																?>
															</ul>
														</li>
														<?php
													} else {
														?>
															</ul>
														</td>
														<td style="width: 50%; vertical-align: top">
															<ul class="notify-communities">
																<li>
																	<strong><?php echo $community["community_title"]; ?></strong>
																	<ul class="notifications">
																		<li><label><input type="checkbox" name="notify_announcements[<?php echo $community["community_id"]; ?>]" value="1"<?php echo (!isset($community["announcements"]) || $community["announcements"] == 1 ? " checked=\"checked\"" : ""); ?> /> Announcements</label></li>
																		<li><label><input type="checkbox" name="notify_events[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["events"]) || $community["events"] == 1 ? " checked=\"checked\"" : ""); ?> /> Events</label></li>
																		<li><label><input type="checkbox" name="notify_polls[<?php echo $community["community_id"]; ?>]" value="1" <?php echo (!isset($community["polls"]) || $community["polls"] == 1 ? " checked=\"checked\"" : ""); ?> /> Polls</label></li>
																		<?php
																		if ($community["member_acl"]) {
																			?>
																			<li><label><input type="checkbox" name="notify_members[<?php echo $community["community_id"]; ?>]" value="1" <?php echo ($community["members"] == 1 ? " checked=\"checked\"" : ""); ?> /> Members Joining / Leaving (Admin Only)</label></li>
																			<?php
																		}
																		?>
																	</ul>
																</li>
																<?php
													}
												}
												?>
												</ul>
											</td>
										</tr>
									</tbody>
									</table>
									<?php
								} else {
									$NOTICE++;
									$NOTICESTR[] = "You are not currently a member of any communities, so community e-mail notifications will not be sent to you.";

									echo display_notice();
								}
								?>
							</td>
						</tr>
					</tbody>
					</table>
					</form>
				</div>
				<?php
			}
			$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js\"></script>";
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js\"></script>";
			if ($ENTRADA_ACL->isLoggedInAllowed('assistant_support', 'create')) {
				?>
				<script type="text/javascript">
				function addAssistant() {
					if ((document.getElementById('assistant_id') != null) && (document.getElementById('assistant_id').value != '')) {
						document.getElementById('assisant_add_form').submit();
					} else {
							alert('You can only add people as assistants to your profile if they exist in this system.\n\nIf you are typing in their name properly (Lastname, Firstname) and their name does not show up in the list, then chances are that they do not exist in our system.\n\nPlease contact Denise Jones in the Undergrad office (613-533-6000 x77804) to get an account for the requested individual.\n\nImportant: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');

						return false;
					}
				}

				function copyAssistant() {
					if ((document.getElementById('assistant_name') != null) && (document.getElementById('assistant_ref') != null)) {
						document.getElementById('assistant_ref').value = document.getElementById('assistant_name').value;
					}

					return true;
				}

				function checkAssistant() {
					if ((document.getElementById('assistant_name') != null) && (document.getElementById('assistant_ref') != null) && (document.getElementById('assistant_id') != null)) {
						if (document.getElementById('assistant_name').value != document.getElementById('assistant_ref').value) {
							document.getElementById('assistant_id').value = '';
						}
					}

					return true;
				}

				function confirmRemoval() {
					ask_user = confirm("Press OK to confirm that you would like to remove the ability for the selected individuals to access your permission levels, otherwise press Cancel.");

					if (ask_user == true) {
						document.getElementById('assisant_remove_form').submit();
					} else {
						return false;
					}
				}

				function selectAssistant(id) {
					if ((id != null) && (document.getElementById('assistant_id') != null)) {
						document.getElementById('assistant_id').value = id;
					}
				}
				</script>
				<div class="tab-page">
					<h2 class="tab">My Admin Assistants</h2>

					<h1 style="margin-top: 0px">My Admin Assistants</h1>
					This section allows you to assign other <?php echo APPLICATION_NAME; ?> users access privileges to <strong>your</strong> <?php echo APPLICATION_NAME; ?> account permissions. This powerful feature should be used very carefully because when you assign someone privileges to your account, they will be able to do <strong>everything in this system</strong> that you are able to do using their own account.
					<br /><br />
					<form action="<?php echo ENTRADA_URL; ?>/profile" method="post" id="assisant_add_form">
					<input type="hidden" name="action" value="assistant-add" />
					<input type="hidden" id="assistant_ref" name="assistant_ref" value="" />
					<input type="hidden" id="assistant_id" name="assistant_id" value="" />
					<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Event">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="button" class="button" value="Add Assistant" onclick="addAssistant()" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td>&nbsp;</td>
								<td><label for="assistant_name" class="form-required">Assistants Fullname:</label></td>
							<td>
								<input type="text" id="assistant_name" name="fullname" size="30" value="" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkAssistant()" />
								<div class="autocomplete" id="assistant_name_auto_complete"></div><script type="text/javascript">new Ajax.Autocompleter('assistant_name', 'assistant_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectAssistant(li.id); copyAssistant();}});</script>
								<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							</td>
						</tr>
									<?php echo generate_calendars("valid", "Access", true, true, $start_time = ((isset($PROCESSED["valid_from"])) ? $PROCESSED["valid_from"] : mktime(0, 0, 0, date("n", time()), date("j", time()), date("Y", time()))), true, true, ((isset($PROCESSED["valid_until"])) ? $PROCESSED["valid_until"] : strtotime("+1 week 23 hours 59 minutes 59 seconds", $start_time))); ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					</table>
					</form>
					<br /><br />
					<?php
					$query		= "	SELECT a.`permission_id`, a.`assigned_to`, a.`valid_from`, a.`valid_until`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`
									FROM `permissions` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
									ON b.`id` = a.`assigned_to`
									WHERE a.`assigned_by`=".$db->qstr($_SESSION["details"]["id"])."
									ORDER BY `valid_until` ASC";
					$results	= $db->GetAll($query);
					if ($results) {
						?>
						<form action="<?php echo ENTRADA_URL; ?>/profile" method="post" id="assisant_remove_form">
						<input type="hidden" name="action" value="assistant-remove" />
						<table class="tableList" cellspacing="0" summary="List of Assistants">
						<colgroup>
							<col class="modified" />
							<col class="title" />
							<col class="date" />
							<col class="date" />
						</colgroup>
						<thead>
							<tr>
								<td class="modified">&nbsp;</td>
											<td class="title">Assistants Fullname</td>
								<td class="date">Access Starts</td>
								<td class="date sortedASC"><div class="noLink">Access Finishes</div></td>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="button" class="button" value="Remove Assistant" onclick="confirmRemoval()" />
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php
						foreach ($results as $result) {
							echo "<tr>\n";
							echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"remove[".$result["assigned_to"]."]\" value=\"".$result["permission_id"]."\" /></td>\n";
							echo "	<td class=\"title\">".html_encode($result["fullname"])."</td>\n";
							echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $result["valid_from"])."</td>\n";
							echo "	<td class=\"date\">".date(DEFAULT_DATE_FORMAT, $result["valid_until"])."</td>\n";
							echo "</tr>\n";
						}
						?>
						</tbody>
						</table>
						</form>
						<?php
					} else {
						$NOTICE++;
						$NOTICESTR[] = "You currently have no assistants / administrative support staff setup for access to your permissions.";

						echo display_notice();
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
		<script type="text/javascript">
		setupAllTabs(true);
		<?php echo ($load_photo_tab ? "tabPaneObj.setSelectedIndex(1);" : ""); ?>
		</script>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.";

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}
?>