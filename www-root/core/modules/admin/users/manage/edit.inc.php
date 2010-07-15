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
 * @version $Id: index.inc.php 1200 2010-06-10 19:07:17Z simpson $
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
					$ONLOAD[] = "provStateFunction(\$F($('user-edit')['country_id']))";	?>
					<h1 style="margin-top: 0px">Edit Profile Details</h1>
					<form name="user-edit" id="user-edit" action="<?php echo ENTRADA_URL; ?>/admin/users/manage?section=edit&id=<?php echo $PROXY_ID; ?>&amp;step=2" method="post" onsubmit="selIt()">
						<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Edit MEdTech Profile">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 25%" />
								<col style="width: 72%" />
							</colgroup>
							<tfoot>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
										<input type="submit" class="button" value="Save" />
									</td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td colspan="3">
										<h2>Account Details</h2>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="number" class="form-nrequired">Staff / Student Number:</label></td>
									<td>
										<input type="text" id="number" name="number" value="<?php echo ((isset($PROCESSED["number"])) ? html_encode($PROCESSED["number"]) : ""); ?>" style="width: 250px" maxlength="25" />
										<span class="content-small">(<strong>Important:</strong> Required when ever possible)</span>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="username" class="form-required">MEdTech Username:</label></td>
									<td>
										<input type="text" id="username" name="username" value="<?php echo ((isset($PROCESSED["username"])) ? html_encode($PROCESSED["username"]) : ""); ?>" style="width: 250px" maxlength="25" />
										<span class="content-small">(<strong>Important:</strong> Should be the Queen's NetID)</span>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="password" class="form-required">MEdTech Password:</label></td>
									<td>
										<input type="text" id="password" name="password" value="" style="width: 250px" maxlength="25" />
										<div class="content-small" style="margin-top: 5px">
											<strong>Important:</strong> Enter a new password only if you want to change the current password.
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="group" class="form-required">Account Type:</label></td>
									<td>
										<select id="group" name="group" style="width: 209px"></select> <span class="content-small"><strong>Account Group</strong></span><br />
										<select id="role" name="role" style="width: 209px; margin-top: 5px"></select> <span class="content-small"><strong>Account Role</strong></span>
									</td>
								</tr>
								<tr>
									<td colspan="3">
										<h2>Account Options</h2>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="account_active" class="form-required">Account Status:</label></td>
									<td>
										<select id="account_active" name="account_active" style="width: 209px">
											<option value="true"<?php echo (((!isset($PROCESSED_ACCESS["account_active"])) || ($PROCESSED_ACCESS["account_active"] == "true")) ? " selected=\"selected\"" : ""); ?>>Active</option>
											<option value="false"<?php echo (($PROCESSED_ACCESS["account_active"] == "false") ? " selected=\"selected\"" : ""); ?>>Disabled</option>
										</select>
									</td>
								</tr>
								<?php echo generate_calendars("access", "Access", true, true, ((isset($PROCESSED_ACCESS["access_starts"])) ? $PROCESSED_ACCESS["access_starts"] : time()), true, false, ((isset($PROCESSED_ACCESS["access_expires"])) ? $PROCESSED_ACCESS["access_expires"] : 0)); ?>
								<tr>
									<td colspan="3">
										<h2>Personal Information</h2>
									</td>
								</tr>
								<?php
								$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type`='1' AND `proxy_id` = ".$db->qstr($PROXY_ID);
								$uploaded_photo = $db->GetRow($query);
								if ($uploaded_photo && @file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
									?>
									<tr>
										<td>&nbsp;</td>
										<td style="vertical-align: bottom; padding-bottom: 15px;"><label for="photo_active" class="photo_active">Uploaded Photo:</label></td>
										<td>
											<div style="position: relative">
												<?php
												echo "		<div style=\"position: relative; width: 74px; height: 102px;\" id=\"img-holder-".$user_record["id"]."\" class=\"img-holder\">\n";
												echo "			<img src=\"".webservice_url("photo", array($user_record["id"], "upload"))."\" width=\"72\" height=\"100\" alt=\"".$user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"]."\" title=\"".$user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"]."\" class=\"current-".$user_record["id"]."\" id=\"uploaded_profile_pic_".$user_record["id"]."\" name=\"uploaded_profile_pic_".$user_record["id"]."\" style=\"border: 1px solid #999999; position: absolute; z-index: 5;\"/>\n";
												if (($uploaded_file_active)) {
													echo "		<a id=\"zoomin_profile_photo_".$user_record["id"]."\" class=\"zoomin\" onclick=\"growPic($('uploaded_profile_pic_".$user_record["id"]."'), $('uploaded_profile_pic_".$user_record["id"]."'), null, null, $('zoomout_profile_photo_".$user_record["id"]."'));\">+</a>";
													echo "		<a id=\"zoomout_profile_photo_".$user_record["id"]."\" class=\"zoomout\" onclick=\"shrinkPic($('uploaded_profile_pic_".$user_record["id"]."'), $('uploaded_profile_pic_".$user_record["id"]."'), null, null, $('zoomout_profile_photo_".$user_record["id"]."'));\"></a>";
												}
												echo "		</div>\n";
												?>
											</div>
											<br/><input style="margin-bottom: 15px" type="checkbox" id="photo_active" name="photo_active" value="1" <?php echo ($uploaded_photo["photo_active"] == 1 ? " checked=\"true\"" : "") ?> /> <?php echo ( $uploaded_photo["photo_active"] == 1  ? "<span class=\"content-small\">Uncheck this to deactivate the uploaded photo for this user.</span>" : "<span class=\"content-small\">Check this to activate the uploaded photo of this user.</span>" ); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td>&nbsp;</td>
									<td><label for="prefix" class="form-nrequired">Prefix:</label></td>
									<td>
										<select id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
										<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
										<?php
										if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
											foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
												echo "<option value=\"".html_encode($prefix)."\"".(((isset($PROCESSED["prefix"])) && ($PROCESSED["prefix"] == $prefix)) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
											}
										}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="firstname" class="form-required">Firstname:</label></td>
									<td><input type="text" id="firstname" name="firstname" value="<?php echo ((isset($PROCESSED["firstname"])) ? html_encode($PROCESSED["firstname"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="35" /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="lastname" class="form-required">Lastname:</label></td>
									<td><input type="text" id="lastname" name="lastname" value="<?php echo ((isset($PROCESSED["lastname"])) ? html_encode($PROCESSED["lastname"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="35" /></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="email" class="form-required">Primary E-Mail:</label></td>
									<td>
										<input type="text" id="email" name="email" value="<?php echo ((isset($PROCESSED["email"])) ? html_encode($PROCESSED["email"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
										<span class="content-small">(<strong>Important:</strong> Official e-mail accounts only)</span>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="email_alt" class="form-nrequired">Alternative E-Mail:</label></td>
									<td><input type="text" id="email_alt" name="email_alt" value="<?php echo ((isset($PROCESSED["email_alt"])) ? html_encode($PROCESSED["email_alt"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="128" /></td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="telephone" class="form-nrequired">Telephone Number:</label></td>
									<td>
										<input type="text" id="telephone" name="telephone" value="<?php echo ((isset($PROCESSED["telephone"])) ? html_encode($PROCESSED["telephone"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
										<span class="content-small">(<strong>Example:</strong> 613-533-6000 x74918)</span>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="fax" class="form-nrequired">Fax Number:</label></td>
									<td>
										<input type="text" id="fax" name="fax" value="<?php echo ((isset($PROCESSED["fax"])) ? html_encode($PROCESSED["fax"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
										<span class="content-small">(<strong>Example:</strong> 613-533-3204)</span>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="country_id" class="form-nrequired">Country</label></td>
									<td>
										<?php
										$countries = fetch_countries();
										if ((is_array($countries)) && (count($countries))) {
											echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
											echo "<option value=\"0\"".((!$PROCESSED["country_id"]) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
											foreach ($countries as $country) {
												echo "<option value=\"".(int) $country["countries_id"]."\"".(($PROCESSED["country_id"] == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
											}
											echo "</select>\n";
										} else {
											echo "<input type=\"hidden\" id=\"countries_id\" name=\"countries_id\" value=\"0\" />\n";
											echo "Country information not currently available.\n";
										}
										?>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label id="prov_state_label" for="prov_state_div" class="form-nrequired">Province / State</label></td>
									<td>
										<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="city" class="form-nrequired">City:</label></td>
									<td>
										<input type="text" id="city" name="city" value="<?php echo ((isset($PROCESSED["city"])) ? html_encode($PROCESSED["city"]) : "Kingston"); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="address" class="form-nrequired">Address:</label></td>
									<td>
										<input type="text" id="address" name="address" value="<?php echo ((isset($PROCESSED["address"])) ? html_encode($PROCESSED["address"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="255" />
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="postcode" class="form-nrequired">Postal Code:</label></td>
									<td>
										<input type="text" id="postcode" name="postcode" value="<?php echo ((isset($PROCESSED["postcode"])) ? html_encode($PROCESSED["postcode"]) : "K7L 3N6"); ?>" style="width: 250px; vertical-align: middle" maxlength="7" />
										<span class="content-small">(<strong>Example:</strong> K7L 3N6)</span>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="office_hours">Office Hours:</label></td>
									<td>
										<textarea id="office_hours" name="office_hours" style="width: 254px; height: 40px;" maxlength="100"><?php echo html_encode($PROCESSED["office_hours"]); ?></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td style="vertical-align: top"><label for="notes" class="form-nrequired">General Comments:</label></td>
									<td>
										<textarea id="notes" name="notes" class="expandable" style="width: 246px; height: 75px"><?php echo ((isset($PROCESSED["notes"])) ? html_encode($PROCESSED["notes"]) : ""); ?></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="3">
										<h2>Departmental Options</h2>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label for="organisation_id" class="form-required">Organisation</label></td>
									<td><select id="organisation_id" name="organisation_id" style="width: 250px" onchange="organisationChange();">
										<?php
										$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations`";
										$results	= $db->GetAll($query);
										if ($results) {
											foreach ($results as $result) {
												if ($ENTRADA_ACL->amIAllowed(new CourseResource(null, $result['organisation_id']), 'create')) {
													$organisation_categories[$result["organisation_id"]] = array($result["organisation_title"]);
													echo "<option value=\"".(int) $result["organisation_id"]."\"".(((isset($PROCESSED["organisation_id"])) && ($PROCESSED["organisation_id"] == $result["organisation_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["organisation_title"])."</option>\n";
												}
											}
										}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>Departments</td>
									<td>
										<?php
										$ONLOAD[] = "organisationChange()";
										if (isset($DEPARTMENT_LIST) && is_array($DEPARTMENT_LIST) && !empty($DEPARTMENT_LIST)) {
											$departments = array();

											foreach ($DEPARTMENT_LIST as $organisation_id => $dlist) {
												foreach ($dlist as $d) {

													if (in_array($d["department_id"], $PROCESSED_DEPARTMENTS)) {
														$checked = "checked=\"checked\"";
													} else {
														$checked = "";
													}

													if (isset($organisation_categories[$organisation_id])) {
														$departments[$organisation_id][] = array('text' => $d['department_title'], 'value' => $d['department_id'], 'checked' => $checked);
													}
												}
											}

											foreach ($departments as $organisation_id => $dlist) {
												echo lp_multiple_select_popup("departments_".$organisation_id, $dlist, array("title" => "Select Departments:", "class" => "department_multi", "submit_text" => "Done", "cancel" => false, "submit" => true));
											}
										}
										?>
										<input class="multi-picklist" id="in_departments" name="in_departments" style="display: none;">
										<div id="in_departments_list"></div>
										<input type="button" onclick="$('departments_'+$F('organisation_id')+'_options').show();" value="Select Multiple" >

										<script type="text/javascript">

										function provStateFunction(country_id) {
											var url='<?php echo webservice_url("province"); ?>';
											<?php
											    if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
													$source_arr = $PROCESSED;
											        $province = $source_arr["province"];
											    	$province_id = $source_arr["province_id"];
											    	$prov_state = ($province) ? $province : $province_id;
											    }
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

										
										var multiselect = new Array();
										function organisationChange() {
											$$('.department_multi').invoke('hide');
											$('in_departments').value = "";
											if (multiselect[$F('organisation_id')]) {
												multiselect[$F('organisation_id')].scanCheckBoxes();
											} else {
												$('in_departments_list').immediateDescendants().invoke('remove');
											}
										}

										$$('.department_multi').each(function(element) {
											var id = element.id;
											numeric_id = id.substring(12,13);
											generic = element.id.substring(0, element.id.length - 8);
											this[numeric_id] = new Control.SelectMultiple('in_departments',id,{
												labelSeparator: '; ',
												checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
												nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
												overflowLength: 70,
												filter: generic+'_select_filter',
												resize: generic+'_scroll',
												afterCheck: apresCheck,
												updateDiv: updateDepartmentList
											});

											$(generic+'_close').observe('click',function(event){
												this.container.hide();
												return false;
											}.bindAsEventListener(this[numeric_id]));
										}, multiselect);

										function apresCheck(element) {
											var tr = $(element.parentNode.parentNode);
											tr.removeClassName('selected');
											if (element.checked) {
												tr.addClassName('selected');
											}
										}

										function updateDepartmentList(options) {
											ul = options.inject(new Element('ul', {'class':'associated_list'}), function(list, option) {
												list.appendChild(new Element('li').update(option));
												return list;
											});
											$('in_departments_list').update(ul);
										}
										</script>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
					<?php
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