<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Allows administrators to edit users from the entrada_auth.user_data table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($PROXY_ID) {
		$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROXY_ID);
		$user_record = $db->GetRow($query);
		if ($user_record) {
			$BREADCRUMB[] = array("url" => "", "title" => html_encode($user_record["firstname"]." ".$user_record["lastname"]));
			
			$PROCESSED_ACCESS = array();
			$PROCESSED_DEPARTMENTS = array();

			echo "<h1>Manage: <strong>".html_encode($user_record["firstname"]." ".$user_record["lastname"])."</strong></h1>\n";

			// Error Checking
			switch ($STEP) {
				case 2 :
					/**
					 * Non-required (although highly recommended) field for staff / student number.
					 */
					if ((isset($_POST["number"])) && ($number = clean_input($_POST["number"], array("trim", "int")))) {
						if ($number != $user_record["number"]) {
							$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `number` = ".$db->qstr($number);
							$result	= $db->GetRow($query);
							if ($result) {
								$ERROR++;
								$ERRORSTR[] = "You are attempting to update the staff / student number; however, the new number already exists in the database under ".html_encode($result["firstname"]." ".$result["lastname"]).".";
							} else {
								$PROCESSED["number"] = $number;
							}
						}
					} else {
						$NOTICE++;
						$NOTICESTR[] = "There was no faculty, staff or student number attached to this profile. If this user is a affiliated with the University, please make sure you add this information.";

						$PROCESSED["number"] = 0;
					}

					/**
					 * Required field "username" / Username.
					 */
					if ((isset($_POST["username"])) && ($username = clean_input($_POST["username"], "credentials"))) {
						if ($username != $user_record["username"]) {
							$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `username` = ".$db->qstr($username);
							$result	= $db->GetRow($query);
							if ($result) {
								$ERROR++;
								$ERRORSTR[] = "You are attempting to update the username; however, this username already exists in the database under ".html_encode($result["firstname"]." ".$result["lastname"]).".";
							} else {
								if ((strlen($username) >= 3) && (strlen($username) <= 24)) {
									$PROCESSED["username"] = $username;
								} else {
									$ERROR++;
									$ERRORSTR[] = "The new username must be between 3 and 24 characters.";
								}
							}
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must provide a valid username for this user to login with. We suggest that you use their University NetID if at all possible.";
					}

					/**
					 * Non-Required field "password" / Password.
					 * This is not required in the edit screen because the password is only changed
					 * if there is an entry made here.
					 */
					if ((isset($_POST["password"])) && ($password = clean_input($_POST["password"], "trim"))) {
						if ((strlen($password) >= 6) && (strlen($password) <= 24)) {
							$PROCESSED["password"] = md5($password);
						} else {
							$ERROR++;
							$ERRORSTR[] = "The password field must be between 6 and 24 characters.";
						}
					}

					/**
					 * Required field "group" / Account Type (Group).
					 * Required field "role" / Account Type (Role).
					 */
					if ((isset($_POST["group"])) && (isset($SYSTEM_GROUPS[$group = clean_input($_POST["group"], "credentials")]))) {
						$PROCESSED_ACCESS["group"] = $group;

						if ((isset($_POST["role"])) && (@in_array($role = clean_input($_POST["role"], "credentials"), $SYSTEM_GROUPS[$PROCESSED_ACCESS["group"]]))) {
							$PROCESSED_ACCESS["role"] = $role;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must provide a valid	Account Type &gt; Role which this persons account will live under.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must provide a valid	Account Type &gt; Group which this persons account will live under.";
					}

					/*
					 * Required field "account_active" / Account Status.
					 */
					if ((isset($_POST["account_active"])) && ($_POST["account_active"] == "true")) {
						$PROCESSED_ACCESS["account_active"] = "true";
					} else {
						$PROCESSED_ACCESS["account_active"] = "false";
					}

					/**
					 * Required field "access_starts" / Access Start (validated through validate_calendar function).
					 * Non-required field "access_finish" / Access Finish (validated through validate_calendar function).
					 */
					$access_date = validate_calendar("access", true, false);
					if ((isset($access_date["start"])) && ((int) $access_date["start"])) {
						$PROCESSED_ACCESS["access_starts"] = (int) $access_date["start"];
					}

					if ((isset($access_date["finish"])) && ((int) $access_date["finish"])) {
						$PROCESSED_ACCESS["access_expires"] = (int) $access_date["finish"];
					} else {
						$PROCESSED_ACCESS["access_expires"] = 0;
					}

					/**
					 * Required field "photo_active" / Uploaded Photo Active
					 */
					if (isset($_POST["photo_active"]) && $_POST["photo_active"] == 1) {
						$PROCESSED_PHOTO = Array();
						$PROCESSED_PHOTO["photo_active"] = 1;
					} else {
						$PROCESSED_PHOTO = Array();
						$PROCESSED_PHOTO["photo_active"] = 0;
					}

					/**
					 * Non-required field "prefix" / Prefix.
					 */
					if ((isset($_POST["prefix"])) && (@in_array($prefix = clean_input($_POST["prefix"], "trim"), $PROFILE_NAME_PREFIX))) {
						$PROCESSED["prefix"] = $prefix;
					} else {
						$PROCESSED["prefix"] = "";
					}

					/**
					 * Required field "firstname" / Firstname.
					 */
					if ((isset($_POST["firstname"])) && ($firstname = clean_input($_POST["firstname"], "trim"))) {
						$PROCESSED["firstname"] = $firstname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The firstname of the user is a required field.";
					}

					/**
					 * Required field "lastname" / Lastname.
					 */
					if ((isset($_POST["lastname"])) && ($lastname = clean_input($_POST["lastname"], "trim"))) {
						$PROCESSED["lastname"] = $lastname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The lastname of the user is a required field.";
					}

					/**
					 * Required field "email" / Primary E-Mail.
					 */
					if ((isset($_POST["email"])) && ($email = clean_input($_POST["email"], "trim", "lower"))) {
						if (@valid_address($email)) {
							$PROCESSED["email"] = $email;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The primary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The primary e-mail address is a required field.";
					}

					/**
					 * Non-required field "office_hours" / Office Hours.
					 */
					if ((isset($_POST["office_hours"])) && ($office_hours = clean_input($_POST["office_hours"], array("notags","encode", "trim")))) {
						$PROCESSED["office_hours"] = ((strlen($office_hours) > 100) ? substr($office_hours, 0, 97)."..." : $office_hours);
					} else {
						$PROCESSED["office_hours"] = "";
					}
				
					/**
					 * Non-required field "email_alt" / Alternative E-Mail.
					 */
					if ((isset($_POST["email_alt"])) && ($email_alt = clean_input($_POST["email_alt"], "trim", "lower"))) {
						if (@valid_address($email_alt)) {
							$PROCESSED["email_alt"] = $email_alt;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The alternative e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address or leave this field empty if you do not wish to display one.";
						}
					} else {
						$PROCESSED["email_alt"] = "";
					}

					/**
					 * Non-required field "telephone" / Telephone Number.
					 */
					if ((isset($_POST["telephone"])) && ($telephone = clean_input($_POST["telephone"], "trim")) && (strlen($telephone) >= 10) && (strlen($telephone) <= 25)) {
						$PROCESSED["telephone"] = $telephone;
					} else {
						$PROCESSED["telephone"] = "";
					}

					/**
					 * Non-required field "fax" / Fax Number.
					 */
					if ((isset($_POST["fax"])) && ($fax = clean_input($_POST["fax"], "trim")) && (strlen($fax) >= 10) && (strlen($fax) <= 25)) {
						$PROCESSED["fax"] = $fax;
					} else {
						$PROCESSED["fax"] = "";
					}

					/**
					 * Non-required field "address" / Address.
					 */
					if ((isset($_POST["address"])) && ($address = clean_input($_POST["address"], array("trim", "ucwords"))) && (strlen($address) >= 6) && (strlen($address) <= 255)) {
						$PROCESSED["address"] = $address;
					} else {
						$PROCESSED["address"] = "";
					}

					/**
					 * Non-required field "city" / City.
					 */
					if ((isset($_POST["city"])) && ($city = clean_input($_POST["city"], array("trim", "ucwords"))) && (strlen($city) >= 3) && (strlen($city) <= 35)) {
						$PROCESSED["city"] = $city;
					} else {
						$PROCESSED["city"] = "";
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

					/**
					 * Non-required field "postcode" / Postal Code.
					 */
					if ((isset($_POST["postcode"])) && ($postcode = clean_input($_POST["postcode"], array("trim", "uppercase"))) && (strlen($postcode) >= 5) && (strlen($postcode) <= 12)) {
						$PROCESSED["postcode"] = $postcode;
					} else {
						$PROCESSED["postcode"] = "";
					}

					
					/**
					 * Non-required field "notes" / General Comments.
					 */
					if ((isset($_POST["notes"])) && ($notes = clean_input($_POST["notes"], array("trim", "notags")))) {
						$PROCESSED["notes"] = $notes;
					} else {
						$PROCESSED["notes"] = "";
					}

					/**
					 * Required field "organisation_id" / Organisation Name.
					 */
					if ((isset($_POST["organisation_id"])) && ($organisation_id = clean_input($_POST["organisation_id"], array("int")))) {
						if ($ENTRADA_ACL->amIAllowed(new CourseResource(null, $organisation_id), 'create')) {
							$PROCESSED["organisation_id"] = $organisation_id;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You do not have permission to add a course for this organisation. This error has been logged and will be investigated.";
							application_log("Proxy id [".$_SESSION['details']['proxy_id']."] tried to eicreate a course within an organisation [".$organisation_id."] they didn't have permissions on. ");
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Organisation Name</strong> field is required.";
					}

					if (!$ERROR && $ENTRADA_ACL->amIAllowed(new UserResource(null, $PROCESSED["organisation_id"]), "update")) {
						if ($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "UPDATE", "id = ".$db->qstr($PROXY_ID))) {
							/**
							 * Send notice if any administrator account is updated.
							 */
							if (($PROCESSED_ACCESS["group"] == "medtech") || ($PROCESSED_ACCESS["role"] == "admin")) {
								application_log("error", "USER NOTICE: Existing user (".$PROCESSED["firstname"]." ".$PROCESSED["lastname"].") was changed in ".APPLICATION_NAME.": ".$PROCESSED_ACCESS["group"]." > ".$PROCESSED_ACCESS["role"].".");
							}

							/**
							 * This section of code handles updating the user_access table.
							 */
							if (!$db->AutoExecute(AUTH_DATABASE.".user_access", $PROCESSED_ACCESS, "UPDATE", "user_id = ".$db->qstr($PROXY_ID)." AND app_id = ".$db->qstr(AUTH_APP_ID))) {
								$ERROR++;
								$ERRORSTR[] = "We were unable to properly update your <strong>Account Options</strong> settings. The MEdTech Unit has been informed of this error, please try again later.";

								application_log("error", "Unable to update data in the user_access table. Database said: ".$db->ErrorMsg());
							}

							if (is_array($PROCESSED_PHOTO)) {
							/**
							 * This section of code handles updating the user_photos table.
							 */
								if (!$db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "UPDATE", "proxy_id = ".$db->qstr($PROXY_ID)." AND photo_type = '1'")) {
									$ERROR++;
									$ERRORSTR[] = "We were unable to properly update your <strong>Account Options</strong> settings. The MEdTech Unit has been informed of this error, please try again later.";

									application_log("error", "Unable to update data in the user_access table. Database said: ".$db->ErrorMsg());
								}
							}

							/**
							 * This section of code handles updating the users departmental data.
							 */
							/**
							 * Handle the inserting of user data into the user_departments table
							 * if departmental information exists in the form.
							 */
							if ((isset($_POST["in_departments"]))) {
								$in_departments = explode(',',$_POST['in_departments']);
								foreach ($in_departments as $department_id) {
									if ($department_id = (int) $department_id) {
										$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_departments` WHERE `user_id` = ".$db->qstr($PROCESSED_ACCESS["user_id"])." AND `dep_id` = ".$db->qstr($department_id);
										$result	= $db->GetRow($query);
										if (!$result) {
											$PROCESSED_DEPARTMENTS[] = $department_id;
										}
									}
								}
							}

							$query = "DELETE FROM `".AUTH_DATABASE."`.`user_departments` WHERE `user_id` = ".$db->qstr($PROXY_ID);
							if (($db->Execute($query)) && (count($PROCESSED_DEPARTMENTS))) {
								foreach ($PROCESSED_DEPARTMENTS as $department_id) {
									if (!$db->AutoExecute(AUTH_DATABASE.".user_departments", array("user_id" => $PROXY_ID, "dep_id" => $department_id), "INSERT")) {
										application_log("error", "Unable to insert proxy_id [".$PROCESSED_ACCESS["user_id"]."] into department [".$department_id."]. Database said: ".$db->ErrorMsg());
									}
								}
							}

							$url = ENTRADA_URL."/admin/users";

							$SUCCESS++;
							$SUCCESSSTR[] = "You have successfully updated the <strong>".html_encode($PROCESSED["firstname"]." ".$PROCESSED["lastname"])."</strong> account in the authentication system.<br /><br />You will now be redirected to the users index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

							$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

							application_log("success", "Proxy ID [".$_SESSION["details"]["id"]."] successfully updated the proxy id [".$PROXY_ID."] user profile.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "Unable to update this user account at this time. The MEdTech Unit has been informed of this error, please try again later.";

							application_log("error", "Unable to update user account [".$PROXY_ID."]. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $user_record;

					$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($PROXY_ID)." AND `app_id` = ".$db->qstr(AUTH_APP_ID);
					$PROCESSED_ACCESS = $db->GetRow($query);

					$query = "SELECT `dep_id` FROM `".AUTH_DATABASE."`.`user_departments` WHERE `user_id` = ".$db->qstr($PROXY_ID);
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$PROCESSED_DEPARTMENTS[] = (int) $result["dep_id"];
						}
					}
					
					$gender = @file_get_contents(webservice_url("gender", $user_record["number"]));
				break;
			}
			// Display Page.
			switch ($STEP) {
				case 2 :
					if ($NOTICE) {
						echo display_notice();
					}

					if ($SUCCESS) {
						echo display_success();
					}
				break;
				case 1 :
				default :
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
					$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
					$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
					$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";

					$i = count($HEAD);
					$HEAD[$i]  = "<script type=\"text/javascript\">\n";
					$HEAD[$i] .= "addListGroup('account_type', 'cs-top');\n";
					if (is_array($SYSTEM_GROUPS)) {
						$item = 1;
						foreach ($SYSTEM_GROUPS as $group => $roles) {
							$HEAD[$i] .= "addList('cs-top', '".ucwords($group)."', '".$group."', 'cs-sub-".$item."', ".(((isset($PROCESSED_ACCESS["group"])) && ($PROCESSED_ACCESS["group"] == $group)) ? "1" : "0").");\n";
							if (is_array($roles)) {
								foreach ($roles as $role) {
									$HEAD[$i] .= "addOption('cs-sub-".$item."', '".ucwords($role)."', '".$role."', ".(((isset($PROCESSED_ACCESS["role"])) && ($PROCESSED_ACCESS["role"] == $role)) ? "1" : "0").");\n";
								}
							}
							$item++;
						}
					}
					$HEAD[$i] .= "</script>\n";

					$ONLOAD[] = "initListGroup('account_type', document.getElementById('group'), document.getElementById('role'))";
					$ONLOAD[] = "setMaxLength()";

					$DEPARTMENT_LIST = array();
					$query = "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`
								FROM `".AUTH_DATABASE."`.`departments` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
								ON a.`entity_id` = b.`entity_id`
								ORDER BY a.`department_title`";
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $key => $result) {
							$DEPARTMENT_LIST[$result["organisation_id"]][] = array("department_id"=>$result['department_id'], "department_title" => $result["department_title"], "entity_title" => $result["entity_title"]);
						}
					}

					$ONLOAD[] = "toggle_visibility_checkbox($('send_notification'), 'send_notification_msg')";

					if ($ERROR) {
						echo display_error();
					}

					if ($NOTICE) {
						echo display_notice();
					}

					if (@file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
						$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
					}

					if (@file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
						$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload");
					}
					?>
					<h1 style="margin-top: 0px">User Overview</h1>
					<div style="display: block" id="opened_details">
						<table style="width: 100%; border: 1px #CCCCCC solid" cellspacing="0" cellpadding="1">
							<tr>
								<td style="height: 15px; background-image: url('<?php echo APPLICATION_URL; ?>/images/table-head-on.gif'); background-color: #EEEEEE; border-bottom: 1px #CCCCCC solid; padding-left: 5px">
									User Profile
								</td>
							</tr>
							<tr>
								<td style="padding: 5px">
									<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td style="width: 110px; vertical-align: top; padding-left: 10px">
												<div style="position: relative">
												<?php
												$uploaded_file_active = $db->GetOne("SELECT `photo_active` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type` = 1 AND `proxy_id` = ".$db->qstr($user_record["id"]));
												echo "		<div style=\"position: relative; width: 74px; height: 102px;\" id=\"img-holder-".$user_record["id"]."\" class=\"img-holder\">\n";

												$offical_file_active	= false;
												$uploaded_file_active	= false;

												/**
												 * If the photo file actually exists
												 */
												if (@file_exists(STORAGE_USER_PHOTOS."/".$user_record["id"]."-official")) {
													$offical_file_active	= true;
												}

												/**
												 * If the photo file actually exists, and
												 * If the uploaded file is active in the user_photos table, and
												 * If the proxy_id has their privacy set to "Basic Information" or higher.
												 */
												if ((@file_exists(STORAGE_USER_PHOTOS."/".$user_record["id"]."-upload")) && ($db->GetOne("SELECT `photo_active` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type` = '1' AND `photo_active` = '1' AND `proxy_id` = ".$db->qstr($user_record["id"]))) && ((int) $user_record["privacy_level"] >= 2)) {
													$uploaded_file_active = true;
												}

												if ($offical_file_active) {
													echo "		<img id=\"official_photo_".$user_record["id"]."\" class=\"official\" src=\"".webservice_url("photo", array($user_record["id"], "official"))."\" width=\"72\" height=\"100\" alt=\"".html_encode($user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"])."\" title=\"".html_encode($user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"])."\" />\n";
												}

												if ($uploaded_file_active) {
													echo "		<img id=\"uploaded_photo_".$user_record["id"]."\" class=\"uploaded\" src=\"".webservice_url("photo", array($user_record["id"], "upload"))."\" width=\"72\" height=\"100\" alt=\"".html_encode($user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"])."\" title=\"".html_encode($user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"])."\" />\n";
												}

												if (($offical_file_active) || ($uploaded_file_active)) {
													echo "		<a id=\"zoomin_photo_".$user_record["id"]."\" class=\"zoomin\" onclick=\"growPic($('official_photo_".$user_record["id"]."'), $('uploaded_photo_".$user_record["id"]."'), $('official_link_".$user_record["id"]."'), $('uploaded_link_".$user_record["id"]."'), $('zoomout_photo_".$user_record["id"]."'));\">+</a>";
													echo "		<a id=\"zoomout_photo_".$user_record["id"]."\" class=\"zoomout\" onclick=\"shrinkPic($('official_photo_".$user_record["id"]."'), $('uploaded_photo_".$user_record["id"]."'), $('official_link_".$user_record["id"]."'), $('uploaded_link_".$user_record["id"]."'), $('zoomout_photo_".$user_record["id"]."'));\"></a>";
												} else {
													echo "		<img src=\"".ENTRADA_URL."/images/headshot-male.gif\" width=\"72\" height=\"100\" alt=\"No Photo Available\" title=\"No Photo Available\" />\n";
												}

												if (($offical_file_active) && ($uploaded_file_active)) {
													echo "		<a id=\"official_link_".$user_record["id"]."\" class=\"img-selector one\" onclick=\"showOfficial($('official_photo_".$user_record["id"]."'), $('official_link_".$user_record["id"]."'), $('uploaded_link_".$user_record["id"]."'));\" href=\"javascript: void(0);\">1</a>";
													echo "		<a id=\"uploaded_link_".$user_record["id"]."\" class=\"img-selector two\" onclick=\"hideOfficial($('official_photo_".$user_record["id"]."'), $('official_link_".$user_record["id"]."'), $('uploaded_link_".$user_record["id"]."'));\" href=\"javascript: void(0);\">2</a>";
												}
												echo "		</div>\n";
												?>
												</div>
											</td>
											<td style="width: 100%; vertical-align: top; padding-left: 5px">
												<table width="100%" cellspacing="0" cellpadding="1" border="0">
													<tr>
														<td style="width: 20%">Full Name:</td>
														<td style="width: 80%"><?php echo $user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"]; ?></td>
													</tr>
													<tr>
														<td>Gender:</td>
														<td><?php echo $gender;?></td>
													</tr>
													<tr>
														<td>Student Number:</td>
														<td><?php echo $user_record["number"]; ?></td>
													</tr>
													<tr>
														<td>E-Mail Address:</td>
														<td><a href="mailto:<?php echo $user_record["email"]; ?>"><?php echo $user_record["email"]; ?></a></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<?php
					$query		= "SELECT a.*, CONCAT_WS(', ', b.lastname, b.firstname) as `reported_by` FROM `".AUTH_DATABASE."`.`user_incidents` as a LEFT JOIN `".AUTH_DATABASE."`.`user_data` as b ON `incident_author_id` = `id` WHERE a.`proxy_id` = ".$db->qstr($PROXY_ID)." AND `incident_status` > 0 ORDER BY `incident_date` ASC";
					$results	= $db->GetAll($query);
					if ($results) {
						?>
						<h2 style="padding-top: 25px;">Open Incidents</h2>
						<div style="padding-top: 15px; clear: both">
							<table class="tableList" cellspacing="0" summary="List of Open Incidents">
								<colgroup>
									<col class="title" />
									<col class="date" />
									<col class="date" />
								</colgroup>
								<thead>
									<tr>
										<td class="title" style="border-left: 1px #999999 solid">Incident Title</td>
										<td class="date sortedASC" style="border-left: none"><a>Incident Date</a></td>
										<td class="date" style="border-left: none">Follow-up Date</td>
									</tr>
								</thead>
								<tbody>
								<?php
								foreach ($results as $result) {
									$url = ENTRADA_URL."/admin/users/manage/incidents?section=edit&id=".$result["proxy_id"]."&incident-id=".$result["incident_id"];
									echo "<tr ".(!$result["incident_status"] ? " class=\"closed\"" : "").">\n";
									echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Incident Title: ".html_encode($result["incident_title"])."\">[".html_encode($result["incident_severity"])."] ".html_encode(limit_chars($result["incident_title"], 75))."</a></td>\n";
									echo "	<td class=\"date\"><a href=\"".$url."\" title=\"Incident Date\">".date(DEFAULT_DATE_FORMAT, $result["incident_date"])."</a></td>\n";
									echo "	<td class=\"date\"><a href=\"".$url."\" title=\"Incident Follow-Up Date\">".(isset($result["follow_up_date"]) && ((int)$result["follow_up_date"]) ? date(DEFAULT_DATE_FORMAT, $result["follow_up_date"]) : "")."</a></td>\n";
									echo "</tr>\n";
								}
								?>
								</tbody>
							</table>
						</div>
						<?php
					} else {
						echo "<div style=\"height: 120px;\">&nbsp;</div>";
					}
			
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a user profile you must provide a valid identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid user identifer when attempting to edit a user profile.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a user profile you must provide a user identifier.";

		echo display_error();

		application_log("notice", "Failed to provide user identifer when attempting to edit a user profile.");
	}
}