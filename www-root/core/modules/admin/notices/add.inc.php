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

if((!defined("PARENT_INCLUDED")) || (!defined("IN_NOTICES"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->amIAllowed('notice', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/notices?".replace_query(array("section" => "add")), "title" => "Adding Notice");

	echo "<h1>Adding Notice</h1>\n";

	// Error Checking
	switch($STEP) {
		case 2 :
			if((isset($_POST["target"])) && ($target_audience = clean_input($_POST["target"], array("lower", "notags", "nows")))) {
				$PROCESSED["target"] = $target_audience;
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must select a valid target audience from the select box.";
			}

			if((isset($_POST["notice_summary"])) && ($notice_summary = strip_tags(clean_input($_POST["notice_summary"], array("trim")), "<a><br><p>"))) {
				$PROCESSED["notice_summary"] = $notice_summary;
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must provide a notice summary.";
			}

			$display_date = validate_calendar("display", true, true);
			if((isset($display_date["start"])) && ((int) $display_date["start"])) {
				$PROCESSED["display_from"] = (int) $display_date["start"];
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must select a valid display start date.";
			}
			if((isset($display_date["finish"])) && ((int) $display_date["finish"])) {
				$PROCESSED["display_until"] = (int) $display_date["finish"];
			} else {
				$ERROR++;
				$ERRORSTR[] = "You must select a valid display finish date.";
			}
			/**
			 *
			 * Required field "organisation_id" / Organisation Name.
			 */
			if(isset($_POST["organisation_id"])) {
				if($organisation_id = clean_input($_POST["organisation_id"], array("int"))) {
					if($ENTRADA_ACL->amIAllowed(new NoticeResource($organisation_id), 'create')) {
						$PROCESSED["organisation_id"] = $organisation_id;
					} else {
						$ERROR++;
						$ERRORSTR[] = "You do not have permission to add a notice for this organisation. This error has been logged and will be investigated.";
						application_log("error", "Proxy id [".$_SESSION['details']['proxy_id']."] tried to create a notice within an organisation [".$organisation_id."] they didn't have permissions on. ");
					}
				} else if($_POST["organisation_id"] == 'all') {
					$PROCESSED["organisation_id"] = null;
				} else {
					$ERROR++;
					$ERRORSTR[] = "You do not have permission to add a course for this organisation. This error has been logged and will be investigated.";
					application_log("error","Proxy id [".$_SESSION['details']['proxy_id']."] tried to create a notice within an organisation [".$organisation_id."] they didn't have permissions on. ");
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Organisation Name</strong> field is required.";
			}

			if(!$ERROR) {
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];

				if($db->AutoExecute("notices", $PROCESSED, "INSERT")) {
					if($NOTICE_ID = $db->Insert_Id()) {
						application_log("success", "Successfully added notice ID [".$NOTICE_ID."]");
					} else {
						application_log("error", "Unable to fetch the newly inserted notice identifier for this notice.");
					}

					$url			= ENTRADA_URL."/admin/notices";
					$SUCCESS++;
					$SUCCESSSTR[]  = "You have successfully added a new notice to the system.<br /><br />You will now be redirected to the notice index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
					$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this notice into the system. The MEdTech Unit was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a notice. Database said: ".$db->ErrorMsg());
				}
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

	// Page Display
	switch($STEP) {
		case 2 :
			if($SUCCESS) {
				echo display_success();
			}
			if($NOTICE) {
				echo display_notice();
			}
			if($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			$buttons	= array();
			$buttons[1] = array("link", "separator", "undo", "redo", "pasteword", "cleanup", "save");
			$buttons[2] = array();
			$buttons[3] = array();
			
			/**
			 * Load the rich text editor.
			 */
			load_rte($buttons);
			
			if($ERROR) {
				echo display_error();
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/notices?section=add&amp;step=2" method="post">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Notice">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tr>
				<td colspan="3"><h2>Notice Details</h2></td>
			</tr>
			<tr>
				<td></td>
				<td><label for="organisation_id" class="form-required">Target Organisation</label></td>
				<td><select id="organisation_id" name="organisation_id" style="width: 250px">
					<?php
					$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations`";
					$results	= $db->GetAll($query);
					$all_organisations = false;
					if($results) {
						$all_organisations = true;
						foreach($results as $result) {
							if($ENTRADA_ACL->amIAllowed(new NoticeResource($result['organisation_id']), 'create')) {
								echo "<option value=\"".(int) $result["organisation_id"]."\"".(((isset($PROCESSED["organisation_id"])) && ($PROCESSED["organisation_id"] == $result["organisation_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["organisation_title"])."</option>\n";
							} else {
								$all_organisations = false;
							}
						}
					}
					if($all_organisations) { ?>
						<option value="all">All Organisations</option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><label for="target" class="form-required">Target Audience</label></td>
				<td>
					<select id="target" name="target" style="width: 300px">
					<?php
					if(is_array($NOTICE_TARGETS)) {
						foreach($NOTICE_TARGETS as $key => $target_name) {
							echo "<option value=\"".$key."\"".((isset($PROCESSED["target"]) && $PROCESSED["target"] == $key) ? " selected=\"selected\"" : "").">".$target_name."</option>\n";
						}
					} else {
						echo "<option value=\"all\" selected=\"selected\">-- Visible to everyone --</option>\n";
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align: top"><label for="notice_summary" class="form-required">Notice Summary</label></td>
				<td style="vertical-align: top">
					<textarea id="notice_summary" name="notice_summary" cols="60" rows="10" style="width: 100%; height: 200px"><?php echo ((isset($PROCESSED["notice_summary"])) ? html_encode(trim($PROCESSED["notice_summary"])) : ""); ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="3"><h2>Time Release Options</h2></td>
			</tr>
			<?php echo generate_calendars("display", "", true, true, ((isset($PROCESSED["display_from"])) ? $PROCESSED["display_from"] : time()), true, true, ((isset($PROCESSED["display_until"])) ? $PROCESSED["display_until"] : strtotime("+5 days 23:59:59"))); ?>
			<tr>
				<td colspan="3" style="padding-top: 25px">
					<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td style="width: 25%; text-align: left">
							<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>'" />
						</td>
						<td style="width: 75%; text-align: right; vertical-align: middle">
							<input type="submit" class="button" value="Save" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			</form>
			<?php
		break;
	}
}
?>