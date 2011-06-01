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
 * This file is used to add groups.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2011 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('group', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	ini_set('auto_detect_line_endings',true);

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/groups?".replace_query(array("section" => "add")), "title" => "Adding Group");

	$group_type = "individual";
	$group_populate = "group_number";
	$group_active = "true";
	$number_of_groups ="";
	$associated_grad_year = "";
	$populate = 0;
	$GROUP_IDS = array();

	echo "<h1>Add Group</h1>\n";
	
	// Error Checking
	switch($STEP) {
		case 2 :
			/*
			 *  CSV file format "group_name, first_name, last_name, status, entrada_id"
			 */
			$PROCESSED["updated_date"]	= time();
			$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

			if (!empty($_FILES)) {
				if ($_FILES["file"]["error"] || !$_FILES["file"]["size"]) {
					$ERROR++;
					$ERRORSTR[] = "There was a problem <strong>upleading</strong>.";
					$STEP = 1;					
				} else {
					$PROCESSED["group_id"]	= 0;
					$PROCESSED["group_name"]	= "";
					$PROCESSED["group_active"]	= 1;

					$fh = fopen($_FILES["file"]["tmp_name"], 'r');
					$line = 0;
				    while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
						if ($line++) {
							if ($num != count($data)) {
								$ERROR++;
								$ERRORSTR[] = "The file appears as an<strong>inconsistent</strong> csv file: varying field number.";
								$STEP = 1;
								break 2;
							}
						}
				        $num = count($data);
					}
					if ($line < 2) {
						$ERROR++;
						$ERRORSTR[] = "The file has <strong>no data</strong> or only a header line.";
						$STEP = 1;
						break;
					}
					fclose($fh);
					
					$fh = fopen($_FILES["file"]["tmp_name"], 'r');
					$line = $count = $group_count = 0;
					while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
						if (!$line++) {  // Skip header
							continue;
						}
						if (!strlen($data[0]) || ((!strlen($data[1]) || !strlen($data[2])) && !strlen($data[4]))) {
							continue;
						}
						if (strcmp($data[0], $PROCESSED["group_name"])) { // A new or different group
							$PROCESSED["group_name"] = $data[0];
							$result = $db->GetOne("SELECT `group_id` FROM `groups` WHERE `group_name` = ".$db->qstr($PROCESSED["group_name"]));
							if ($result) {
								$PROCESSED["group_id"] = $result;
							} else {
								unset($PROCESSED["group_id"]);
								if ($db->AutoExecute("groups", $PROCESSED, "INSERT")) {
									$PROCESSED["group_id"] = $db->Insert_Id();
								} else {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
									application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
									$STEP = 1;
									break 2;
								}
							}
							if (!in_array($PROCESSED["group_id"],$GROUP_IDS)) {
								$GROUP_IDS[] = $PROCESSED["group_id"];
								$group_count++;
							}
						}
						if ($id = (int)$data[4]) {  // Add the member
							$PROCESSED["proxy_id"] = $id; // Use a given Olser id
							$result = $db->GetRow("SELECT * FROM `group_members` WHERE `proxy_id` = ".$db->qstr($PROCESSED["proxy_id"])." AND `group_id` = ".$PROCESSED["group_id"]);
							if ($result) {
								$continue ;
							} else {
								$PROCESSED["member_active"] =  $data[3];
								if ($db->AutoExecute("group_members", $PROCESSED, "INSERT")) {
									$count++;
								} else {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to add the <strong>Group Member</strong> ".$PROCESSED["proxy_id"]." into ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
									application_log("error", "Unable to insert a new group member ".$PROCESSED["proxy_id"]." into ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
									$STEP = 1;
									break 2;
								}
							}
						} else {  // Try the names
							$result = $db->GetOne("	SELECT `id` FROM `".AUTH_DATABASE."`.`user_data`
													WHERE `firstname` LIKE ".$db->qstr($data[1])." AND `lastname` LIKE ".$db->qstr($data[2]));
							if (!$result) {
								print_r($data);
								continue ;
							}
							$PROCESSED["proxy_id"] = $result;
							$result = $db->GetRow("SELECT * FROM `group_members` WHERE `group_name` = ".$db->qstr($PROCESSED["proxy_id"]));
							if ($result) {
								$continue ;
							} else {
								$PROCESSED["member_active"] =  $data[3];
								if ($db->AutoExecute("group_members", $PROCESSED, "INSERT")) {
									$count++;
								} else {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to add the <strong>Group Member</strong> ".$PROCESSED["proxy_id"]." into ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
									application_log("error", "Unable to insert a new group member ".$PROCESSED["proxy_id"]." into ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
									$STEP = 1;
									break 2;
								}
							}
						}
					}
					fclose($fh);
				}
				if ($count) {
					$url	= ENTRADA_URL."/admin/groups?section=edit&ids=".implode(",", $GROUP_IDS);
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 0)";
				}
			} else { 
			
				/**
				 * Required field "prefix" / Group Name.
				 */
				if ((isset($_POST["prefix"])) && ($group_prefix = clean_input($_POST["prefix"], array("notags", "trim")))) {
					$PROCESSED["group_name"] = $group_prefix;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Group Prefix</strong> field is required.";
				}

				/**
				 * Required field "status" / Group Status.
				 */
				if (!((isset($_POST["group_active"])) && ($group_active = clean_input($_POST["group_active"], array("notags", "trim"))))) {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Group Status</strong> value is required.";
				}

				/**
				 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
				 * This is actually accomplished after the event is inserted below.
				 */
				if (isset($_POST["group_type"])) {
					$group_type = clean_input($_POST["group_type"], array("page_url"));

					switch($group_type) {
						case "individual" :
							if (!((isset($_POST["number_of_groups"])) && ($number_of_groups = clean_input($_POST["number_of_groups"], array("trim", "int"))))) {
								$ERROR++;
								$ERRORSTR[] = "A <strong># of Groups</strong> value is required.";
							}
						break;
						case "grad_year" :
							/**
							 * Required field "associated_grad_year" / Graduating Year
							 * This data is inserted into the event_audience table as grad_year.
							 */
							if (!((isset($_POST["associated_grad_year"])) && ($associated_grad_year = clean_input($_POST["associated_grad_year"], "alphanumeric")))) {
								$ERROR++;
								$ERRORSTR[] = "You have chosen <strong>Entire Class Event</strong> as an <strong>Event Audience</strong> type, but have not selected a graduating year.";
							}

							if (!((isset($_POST["number"])) && ($number_of_groups = clean_input($_POST["number"], array("trim", "int"))))) {
								$number_of_groups = 0;
							}

							if (isset($_POST["group_populate"])) {
								$group_populate = clean_input($_POST["group_populate"], array("page_url"));
								switch($group_populate) {
									case "group_number" :
										if (!$number_of_groups) {
											$ERROR++;
											$ERRORSTR[] = "A value for <strong># of Groups</strong> is required.";
										}
									break;
									case "group_size" :
										if (!$number_of_groups) {
											$number_of_groups = -$number_of_groups;
											$ERROR++;
											$ERRORSTR[] = "A value for <strong>Group size</strong> is required.";
										}
									break;
									default:
										$ERROR++;
										$ERRORSTR[] = "Unable to proceed because the <strong>Groups</strong> style is unrecognized.";
									break;
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "Unable to proceed because the <strong>Groups</strong> style is unrecognized.";
							}
							if (isset($_POST["populate"])) {
								$populate = 1;
							}
						break;
						default :
							$ERROR++;
							$ERRORSTR[] = "Unable to proceed because the <strong>Grouping</strong> type is unrecognized.";

							application_log("error", "Unrecognized group_type [".$_POST["group_type"]."] encountered.");
						break;
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "Unable to proceed because the <strong>Grouping</strong> type is unrecognized.";

					application_log("error", "The group_type field has not been set.");
				}

				if (isset($_POST["post_action"])) {
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

				if (!$ERROR) {
					$PROCESSED["group_active"] 	= ($group_active == "true") ? 1 : 0;

					if ($number_of_groups==1) {
						$result = $db->GetRow("SELECT `group_id` FROM `groups` WHERE `group_name` = ".$db->qstr($PROCESSED["group_name"]));
						if ($result) {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Group name</strong> already exits. The group was not created";
						} else {
							if (!$db->AutoExecute("groups", $PROCESSED, "INSERT")) {
								$ERROR++;
								$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
								application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
							}
							$GROUP_IDS[] = $db->Insert_Id();
						}

					} else {
						$prefix = $PROCESSED["group_name"].' ';

						if ($group_populate == "group_size") {
							$query	= "	SELECT COUNT(a.`id`)
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND b.`account_active` = 'true'
										AND b.`group` = 'student'
										AND b.`role` >= ".$db->qstr($associated_grad_year);
							$students = $db->GetOne($query);
							$number_of_groups = ceil($students / $number_of_groups) ;
						}
						$dfmt = "%0".strlen((string) $number_of_groups)."d";

						$result = false;
						for ($i=1;$i<=$number_of_groups&&!$result;$i++){
							$result = $db->GetRow("SELECT `group_id` FROM `groups` WHERE `group_name` = ".$db->qstr($prefix.sprintf($dfmt,$i)));	
						}
						if ($result) {
							$ERROR++;
							$ERRORSTR[] = "A <strong>Group name</strong> already exits. The groups were not created";
						} else {
							for ($i=1;$i<=$number_of_groups;$i++) {
								$PROCESSED["group_name"] = $prefix.sprintf($dfmt,$i);
								if (!$db->AutoExecute("groups", $PROCESSED, "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.";
									application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
									break;
								}
								$GROUP_IDS[] = $db->Insert_Id();
							}
						}
					}	
					if ($populate) {
						unset($PROCESSED["group_name"]);
						unset($PROCESSED["group_active"]);
						$PROCESSED["member_active"] = 1;

						$query	= "	SELECT a.`id`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON a.`id` = b.`user_id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`account_active` = 'true'
									AND b.`group` = 'student'
									AND b.`role` >= ".$db->qstr($associated_grad_year)."
									ORDER By RAND()";
						$results = $db->GetAll($query);

						$i = 0;
						foreach($results as $result) {
							$PROCESSED["proxy_id"] =  $result["id"];
							$PROCESSED["group_id"] =  $GROUP_IDS[$i++];
							if (!$db->AutoExecute("group_members", $PROCESSED, "INSERT")) {
								$ERROR++;
								$ERRORSTR[] = "There was an error while trying to add the <strong>Group member</strong> ".$PROCESSED["proxy_id"].".<br /><br />The system administrator was informed of this error; please try again later.";
								application_log("error", "Unable to insert a new group member ".$PROCESSED["proxy_id"].". Database said: ".$db->ErrorMsg());
								break;
							}
							if ($i==$number_of_groups) {
								$i = 0;
							}
						}
					}

					switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
						case "content" :
							$url	= ENTRADA_URL."/admin/groups?section=edit&ids=".implode(",", $GROUP_IDS);
							$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "new" :
								$url	= ENTRADA_URL."/admin/groups?section=add";
								$msg	= "You will now be redirected to add more group(s); this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
							case "index" :
							default :
								$url	= ENTRADA_URL."/admin/groups";
								$msg	= "You will now be redirected to the group index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
							break;
						}

						$SUCCESS++;
						$SUCCESSSTR[] = "You have successfully added <strong>".html_encode($PROCESSED["event_title"])."</strong> to the system.<br /><br />".$msg;
						$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

						application_log("success", "New event [".$EVENT_ID."] added to the system.");
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting group into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a group. Database said: ".$db->ErrorMsg());
				}

				if ($ERROR) {
					$STEP = 1;
				}
			}
		break;
		case 1 :
		default :
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
			$ONLOAD[] = "selectgroupOption('".$group_type."',0)";

			if ($ERROR) {
				echo display_error();
			}

			?>
			<form id="frmSubmit" action="<?php echo ENTRADA_URL; ?>/admin/groups?section=add&amp;step=2" method="post" id="addGroupForm">
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Group">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tr>
						<td colspan="3"><h2>Group Details</h2></td>
					</tr>
					<tr class="prefixR">
						<td></td>
						<td><label for="prefix" class="form-required">Group Prefix</label></td>
						<td><input type="text" id="prefix" name="prefix" value="<?php echo html_encode($PROCESSED["group_name"]); ?>" maxlength="255" style="width: 45%" /></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td style="vertical-align: top"><label for="group_active" class="form-required">Group Status:</label></td>
						<td>
							<select id="group_active" name="group_active" style="width: 109px">
								<option value="true"<?php echo (((!isset($group_active)) || ($group_active == "true")) ? " selected=\"selected\"" : ""); ?>>Active</option>
								<option value="false"<?php echo (($group_active == "false") ? " selected=\"selected\"" : ""); ?>>Disabled</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="group_type" id="group_type_individual" value="individual" onclick="selectgroupOption('individual',0)" style="vertical-align: middle"<?php echo (($group_type == "individual") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="group_type_individual" class="radio-group-title">Individual Groups</label>
							<div class="content-small">This grouping is intended for any users.</div>
						</td>
					</tr>
					<tr class="group_members individual_members">
						<td></td>
						<td style="vertical-align: top; text-align: center"><label for="number_of_groups" class="form-required"># of Groups</label></td>
						<td><input type="text" id="prefix" name="number_of_groups" value="<?php echo html_encode($number_of_groups); ?>" maxlength="10" style="width: 15%" />
							<span class="content-small">   For a single group the Group Prefix is the Group name.</span>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="group_type" id="group_type_grad_year" value="grad_year" onclick="selectgroupOption('grad_year',0)" style="vertical-align: middle"<?php echo (($group_type == "grad_year") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="group_type_grad_year" class="radio-group-title">Class Groups</label>
							<div class="content-small">This grouping is intended for an entire class.</div>
						</td>
					</tr>
					<tr class="group_members grad_year_members">
						<td></td>
						<td><label for="associated_grad_year" class="form-required">Graduating Year</label></td>
						<td>
							<table>
								<colgroup>
									<col style="width: 25%" />
									<col style="width: 8%" />
									<col style="width: 10%" />
									<col style="width: 5%" />
									<col style="width: 3%" />
									<col style="width: 17%" />
									<col style="width: 3%" />
									<col style="width: 29%" />
								</colgroup>
								<tr>
									<td>
										<select id="associated_grad_year" name="associated_grad_year" style="width: 100%">
										<?php
										$cut_off_year = (fetch_first_year() - 3);
										if (isset($SYSTEM_GROUPS["student"]) && !empty($SYSTEM_GROUPS["student"])) {
											foreach ($SYSTEM_GROUPS["student"] as $class) {
												if (clean_input($class, "numeric") >= $cut_off_year) {
													echo "<option value=\"".$class."\"".(($associated_grad_year == $class) ? " selected=\"selected\"" : "").">Class of ".html_encode($class)."</option>\n";
												}
											}
										}
										?>
										</select>
									</td>
									<td />
									<td><input type="text" id="prefix" name="number" value="<?php echo html_encode($number_of_groups); ?>"  style="width: 100%"/></td>
									<td />
									<td ><input type="radio" name="group_populate" id="group_populate_group_number" value="group_number" <?php echo (($group_populate == "group_number") ? " checked=\"checked\"" : ""); ?> /></td>
									<td><label for="group_number" class="form-required"># of Groups</label></td>
									<td ><input type="radio" name="group_populate" id="group_populate_group_size" value="group_size" <?php echo (($group_populate == "grad_size") ? " checked=\"checked\"" : ""); ?> /></td>
									<td><label for="group_size" class="form-required">Group Size</label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="group_members grad_year_members">
						<td colspan="2" />
						<td>
							<table>
								<colgroup>
									<col style="width: 70%" />
									<col style="width: 10%" />
									<col style="width: 20%" />
								</colgroup>
								<tr>
									<td />
									<td class="modified"><input type="checkbox" name="populate" value="1" <?php if($populate==1) echo "checked"; ?> /></td>
									<td class="form-required">Populate</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="group_type" id="group_type_file" value="file" onclick="selectgroupOption('file',1)" style="vertical-align: middle"<?php echo (($group_type == "file") ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="group_type_file" class="radio-group-title">Upload Groups</label>
							<div class="content-small">Load groups from CSV (excel) file.</div>
						</td>
					</tr>
					<tr class="group_members file_members">
						<td></td>
						<td style="vertical-align: top"><label for="file" class="form-required">Filename:</label></td>	
						<td>
							<input type="file" id="file" name="file" />
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/groups'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<span class="content-small">After saving:</span>
										<select id="post_action" name="post_action">
											<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add members to group</option>
											<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another group</option>
											<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to group list</option>
										</select>
										<input type="submit" class="button" value="Proceed" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
			<script type="text/javascript">
				function selectgroupOption(type,files) {
					$$('.group_members').invoke('hide');
					$$('.'+type+'_members').invoke('show');
					if (files) {
						$$('.prefixR').invoke('hide');
						$('file').style.display = 'block';
						$('file').disabled = false;
						$('frmSubmit').enctype="multipart/form-data";
					} else {
						$$('.prefixR').invoke('show');
						$('frmSubmit').enctype="application/x-www-form-urlencoded";
						$('file').style.display = 'none';
						$('file').disabled = true;
					}
				}
			</script>
			<br /><br />
			<?php
		break;
	}
}
?>
