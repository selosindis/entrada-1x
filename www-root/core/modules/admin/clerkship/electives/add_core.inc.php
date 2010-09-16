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
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_CLERKSHIP")) || (!defined("IN_ELECTIVES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('electives', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	// ERROR CHECKING
	switch($STEP) {
		case "2" :
			if($_POST) {
				// Required
				if((!@is_array($_POST["ids"])) && (@count($_POST["ids"]) == 0)) {
					$ERROR++;
					$ERRORSTR[] = "You must select a user to add this event to. Please be sure that you select at least one user to add this event to from the interface.";
				}
				
				if(strlen(trim($_POST["category_id"])) < 1) {
					$ERROR++;
					$ERRORSTR[] = "You must select a child category for this event to take place in.";	
				} else {
					if(clerkship_categories_children_count(trim($_POST["category_id"])) > 0) {
						$ERROR++;
						$ERRORSTR[] = "The category that you have selected for this event to take place in is a parent category, meaning it has further categories underneath it (see -- Select Category -- box). Please make sure the category that you select is a child category.";
					} else {
						$PROCESSED["category_id"] = trim($_POST["category_id"]);
					}
				}
				
				if($_POST["region_id"] == "new") {
					if(trim($_POST["new_region"]) != "") {
						$query	= "SELECT `region_id` FROM `".CLERKSHIP_DATABASE."`.`regions` WHERE UPPER(`region_name`)=".$db->qstr(strtoupper(trim($_POST["new_region"])), get_magic_quotes_gpc());
						$result	= $db->GetRow($query);
						if($result) {
							$PROCESSED["region_id"] = (int) $result["region_id"];
						} else {
							if($db->AutoExecute("`".CLERKSHIP_DATABASE."`.`regions`", array("region_name" => trim($_POST["new_region"])), "INSERT")) {
								$PROCESSED["region_id"] = (int) $db->Insert_Id();
							} else {
								$ERROR++;
								$ERRORSTR[] = "Unable to insert your new region information into the database. Please notify the MEdTech Unit of this error.";	
							}
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You selected that you were adding a new region; however, you did not enter a name for this new region.";	
					}
				} elseif(trim($_POST["region_id"]) != "") {
					$PROCESSED["region_id"] = (int) trim($_POST["region_id"]);
				} else {
					$ERROR++;
					$ERRORSTR[] = "You must select a region that this event resides in.";
				}
				
				if(strlen(trim($_POST["event_title"])) < 1) {
					$ERROR++;
					$ERRORSTR[] = "You must enter a title for this event or choose the auto generated one.";	
				} else {
					$PROCESSED["event_title"] = trim($_POST["event_title"]);
				}

				// Not Required
				if(strlen(trim($_POST["event_desc"])) > 0) {
					$PROCESSED["event_desc"] = trim($_POST["event_desc"]);
				} else {
					$PROCESSED["event_desc"] = "";
				}

				$event_dates = validate_calendars("event", true, true);
				if ((isset($event_dates["start"])) && ((int) $event_dates["start"])) {
					$PROCESSED["event_start"] = (int) $event_dates["start"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Start</strong> field is required if this is to appear on the calendar.";
				}
		
				if ((isset($event_dates["finish"])) && ((int) $event_dates["finish"])) {
					$PROCESSED["event_finish"] = (int) $event_dates["finish"];
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Finish</strong> field is required if this is to appear on the calendar.";
				}

				if(strlen(trim($_POST["event_status"])) < 1) {
					$ERROR++;
					$ERRORSTR[] = "Please select the status of this category after you have saved it.";
				} else {
					if(!@array_key_exists($_POST["event_status"], $CLERKSHIP_FIELD_STATUS)) {
						$ERROR++;
						$ERRORSTR[] = "The category &quot;Save State&quot; that you've selected no longer exists as an acceptable state. Please choose a new state for this category.";
					} else {
						$PROCESSED["event_status"] = $_POST["event_status"];
					}
				}
				if(!$ERROR) {
					$PROCESSED["modified_last"]	= time();
					$PROCESSED["modified_by"]	= $_SESSION["details"]["id"];

					switch($_POST["add_type"]) {
						case "single" :	// Adds all selected users to a single event.
							if(!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`events`", $PROCESSED, "INSERT")) {
								$ERROR++;
								$ERRORSTR[]	= "Failed insert this event into the database. Please contact a system administrator if this problem persists.";
								application_log("error", "Error while inserting clerkship event into database. Database server said: ".$db->ErrorMsg());
								$STEP		= 1;
							} else {
								$EVENT_ID = $db->Insert_Id();
								if($EVENT_ID) {
									foreach($_POST["ids"] as $user_id) {
										if(!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`event_contacts`", array("event_id" => $EVENT_ID, "econtact_type" => "student", "etype_id" => $user_id), "INSERT")) {
											$ERROR++;
											$ERRORSTR[]	= "Failed to assign this event to user ID ".$user_id.". Please contact a system administrator if this problem persists.";
											application_log("error", "Error while inserting clerkship event contact into database. Database server said: ".$db->ErrorMsg());
											$STEP		= 1;
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[]	= "Failed insert this event into the database. Please contact a system administrator if this problem persists.";
									application_log("error", "Error while inserting clerkship event into database. Database server said: ".$db->ErrorMsg());
									$STEP		= 1;
								}
							}
						break;
						case "multiple" :	// Adds a new event for every user.
						default :
							foreach($_POST["ids"] as $user_id) {
								if(!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`events`", $PROCESSED, "INSERT")) {
									$ERROR++;
									$ERRORSTR[]	= "Failed insert this event into the database. Please contact a system administrator if this problem persists.";
									application_log("error", "Error while inserting clerkship event into database. Database server said: ".$db->ErrorMsg());
									$STEP		= 1;
								} else {
									$EVENT_ID = $db->Insert_Id();
									if($EVENT_ID) {
										if(!$db->AutoExecute("`".CLERKSHIP_DATABASE."`.`event_contacts`", array("event_id" => $EVENT_ID, "econtact_type" => "student", "etype_id" => $user_id), "INSERT")) {
											$ERROR++;
											$ERRORSTR[]	= "Failed to assign this event to user ID ".$user_id.". Please contact a system administrator if this problem persists.";
											application_log("error", "Error while inserting clerkship event contact into database. Database server said: ".$db->ErrorMsg());
											$STEP		= 1;
										}
									} else {
										$ERROR++;
										$ERRORSTR[]	= "Failed insert this event into the database. Please contact a system administrator if this problem persists.";
										application_log("error", "Error while inserting clerkship event into database. Database server said: ".$db->ErrorMsg());
										$STEP		= 1;
									}
								}
							}
						break;
					}
				} else {
					$STEP = 1;
				}
			}
		break;
		default :
			// No error checking for step 1.
		break;	
	}
	
	// PAGE DISPLAY
	switch($STEP) {
		case "2" :			// Step 2
			$ONLOAD[] = "setTimeout('window.location=\'".ENTRADA_URL."/admin/clerkship/clerk?ids=".$_POST["ids"][0]."\'', 5000)";

			$SUCCESS++;
			$SUCCESSSTR[] = "You have successfully added this event to ".@count($_POST["ids"])." student".((@count($_POST["ids"]) != "1") ? "s calendars.<br /><br />You will now be redirected to the first students calendar." : " and you're being redirected back to their calendar.")."<br /><br />If you do not wish to wait, please <a href=\"".ENTRADA_URL."/admin/clerkship/clerk?ids=".$_POST["ids"][0]."\">click here</a>.";

			echo display_success($SUCCESSSTR);
		break;
		default :				// Step 1
			$user_ids = array();
			if(isset($_GET["ids"])) {
				if(strlen(trim($_GET["ids"])) > 0) {
					$tmp_ids = explode(",", trim($_GET["ids"]));
					foreach($tmp_ids as $tmp_id) {
						$user_ids[] = (int) trim($tmp_id);	
					}
				}
			} elseif(@is_array($_POST["ids"])) {
				foreach($_POST["ids"] as $tmp_id) {
					$user_ids[] = (int) trim($tmp_id);	
				}
			}
			if (count($user_ids) == 1) {
				$student_name	= get_account_data("firstlast", $user_ids[0]);
			} else {
				$student_name	= "Multiple Students";
			}
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/clerkship".(count($user_ids) == 1 ? "/clerk?ids=".$user_ids[0] : ""), "title" => $student_name);
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/clerkship/electives?section=add_core&ids=".(isset($_GET["ids"]) && $_GET["ids"] ? $_GET["ids"] : (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : $_POST["ids"])), "title" => "Add Core");
			if(isset($_POST["category_id"])) {
				$CATEGORY_ID	= (int) trim($_POST["category_id"]);
			} elseif(isset($_COOKIE["calendar_management"]["category_id"])) {
				$CATEGORY_ID	= (int) trim($_COOKIE["calendar_management"]["category_id"]);
			} else {
				$CATEGORY_ID	= 0;
			}
			
			$HEAD[]	= "
<script type=\"text/javascript\">
function selectCategory(category_id) {
	new Ajax.Updater('selectCategoryField', '".ENTRADA_URL."/api/category-list.api.php', {parameters: {'cid': category_id}});
	new Ajax.Updater('hidden_event_title', '".ENTRADA_URL."/api/category-title.api.php', {parameters: {'cid': category_id}, onComplete: function(){ $('event_title').value = $('hidden_event_title').innerHTML.unescapeHTML(); }});
	return;
}
</script>";

			$DECODE_HTML_ENTITIES	= true;
			
			$ONLOAD[]	= "selectCategory(".($CATEGORY_ID ? $CATEGORY_ID : "0").")";
			
			?>
			<span class="content-heading">Adding Core Rotation</span>
			<br /><br />
			<?php echo (($ERROR) ? display_error($ERRORSTR) : ""); ?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/clerkship/electives?section=add_core&step=2" method="post" id="addEventForm">
			<input type="hidden" id="step" name="step" value="1" />
			<input type="hidden" id="category_id" name="category_id" value="" />
			<table width="100%" cellspacing="0" cellpadding="2" border="0">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tr>
				<td style="vertical-align: top; border-right: 10px #CCCCCC solid" colspan="2"><span class="form-nrequired">Student Name<?= ((@count($user_ids) != 1) ? "s" : "") ?>:</span></td>
				<td style="width: 75%; padding-left: 5px">
					<?php
					foreach($user_ids as $user_id) {
						$query	= "SELECT CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname` FROM `".AUTH_DATABASE."`.`user_data` LEFT JOIN `".AUTH_DATABASE."`.`user_access` ON `".AUTH_DATABASE."`.`user_access`.`user_id`=`".AUTH_DATABASE."`.`user_data`.`id` WHERE `".AUTH_DATABASE."`.`user_data`.`id`='".$user_id."' AND `group`='student'";
						$result	= $db->GetRow($query);
						if($result) {
							echo "<a href=\"".ENTRADA_URL."/admin/clerkship/clerk?ids=".$user_id."\" style=\"font-weight: bold\">".html_encode($result["fullname"])."</a><br />";
							echo "<input type=\"hidden\" name=\"ids[]\" value=\"".$user_id."\" />\n";
						}
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><label for="region_id" class="form-required">Event Region:</label></td>
				<td>
					<select id="region_id" name="region_id" style="width: 75%" onchange="checkForNewRegion()">
					<option value="">-- Select Region --</option>
					<?php
					$region_query	= "SELECT * FROM `".CLERKSHIP_DATABASE."`.`regions` WHERE `is_core` = '1' ORDER BY `region_name` ASC";
					$region_results	= $db->GetAll($region_query);
					if($region_results) {
						foreach($region_results as $region_result) {
							echo "<option value=\"".$region_result["region_id"]."\"".(($_POST["region_id"] == $region_result["region_id"]) ? " SELECTED" : "").">".html_encode($region_result["region_name"])."</option>\n";	
						}
					}
					?>
					<option value="">----</option>
					<option value="new"<?= (($_POST["region_id"] == "new") ? " SELECTED" : "") ?>>New Region</option>
					</select>
				</td>
			</tr>
			<tbody id="new_region_layer" style="display: none">
			<tr>
				<td colspan="2"><label for="new_region" class="form-required">New Region Name:</label></td>
				<td><input type="text" id="new_region" name="new_region" style="width: 75%" value="<?= html_encode(trim($_POST["new_region"])) ?>" /></td>
			</tr>
			</tbody>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><label for="event_title" class="form-required">Event Title:</label></td>
				<td><input type="text" id="event_title" name="event_title" style="width: 75%" value="<?php echo html_decode($PROCESSED["event_title"]) ?>" /><div style="display: none;" id="hidden_event_title"><?php echo html_decode($PROCESSED["event_title"]) ?></div></td>
			</tr>
			<tr>
				<td style="vertical-align: top; padding-top: 15px" colspan="2"><label for="category_id" class="form-required">Event Takes Place In:</label></td>
				<td style="vertical-align: top"><div id="selectCategoryField"></div></td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<?php
				if(isset($_POST)) {
					$event_start	= $PROCESSED["event_start"];
					$event_finish	= $PROCESSED["event_finish"];
				}
				echo generate_calendars("event", "", true, true, ((isset($event_start)) ? $event_start : time()), true, true, ((isset($event_finish)) ? $event_finish : 0));				
			?>
			<tr>
				<td colspan="3" style="vertical-align: top"><label for="event_desc" class="form-nrequired">Private notes on this student:</label></td>
			</tr>
			<tr>
				<td colspan="3"><textarea id="event_desc" name="event_desc" style="width: 82%; height: 75px"><?= trim(checkslashes($_POST["event_desc"], "display")) ?></textarea></td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><label for="event_status" class="form-required">Save State:</label></td>
				<td>
					<select id="event_status" name="event_status" style="width: 150px">
						<?php
						foreach($CLERKSHIP_FIELD_STATUS as $key => $status) {
							echo (($status["visible"]) ? "<option value=\"".$key."\"".(($_POST["event_status"] == $key) ? " SELECTED" : "").">".$status["name"]."</option>\n" : "");
						}
						?>
					</select> 
				</td>
			</tr>
			<?php if(@count($_POST["ids"]) > 1) : ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td style="vertical-align: top" colspan="2"><span class="form-required">Addition Style:</span></td>
				<td>
					<input type="radio" id="add_type_m" name="add_type" value="multiple" style="vertical-align: middle" CHECKED /> <label for="add_type_m" class="form-nrequired">Add new event for every student.</label><br />
					<input type="radio" id="add_type_s" name="add_type" value="single" style="vertical-align: middle" /> <label for="add_type_s" class="form-nrequired">Add all students to the same event.</label>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" style="text-align: right">
					<input type="button" value="Cancel" class="button" style="background-image: url('<?php echo ENTRADA_URL; ?>/images/btn_bg.gif');" onClick="window.location='<?php echo ENTRADA_URL; ?>/admin/clerkship/clerk?ids=<?= $user_ids[0] ?>'" />
					<input type="submit" value="Save" class="button" style="background-image: url('<?php echo ENTRADA_URL; ?>/images/btn_bg.gif');" />
				</td>
			</tr>
			</table>
			</form>
			<?php
		break;	
	}
}
?>
