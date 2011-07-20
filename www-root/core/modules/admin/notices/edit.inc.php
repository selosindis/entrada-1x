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
} elseif($ENTRADA_ACL->amIAllowed('notices', 'update')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if($NOTICE_ID) {
		$query	= "SELECT * FROM `notices` WHERE `notice_id`=".$db->qstr($NOTICE_ID);
		$result	= $db->GetRow($query);
		if($result) {
			if ($ENTRADA_ACL->amIAllowed(new NoticeResource($result["organisation_id"]), "update")) {
				$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/notices?".replace_query(array("section" => "editing")), "title" => "Editing Notice");
	
				echo "<h1>Editing Notice</h1>\n";
	
				// Error Checking
				switch($STEP) {
					case 2 :
						if ($organisation_id = $user->getActiveOrganisation()) {
							if ($ENTRADA_ACL->amIAllowed(new NoticeResource($organisation_id), 'create')) {
								$PROCESSED["organisation_id"] = $organisation_id;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You do not have permission to add a notice for this organisation. This error has been logged and will be investigated.";
								application_log("Proxy id [" . $_SESSION['details']['proxy_id'] . "] tried to eicreate a course within an organisation [" . $organisation_id . "] they didn't have permissions on. ");
							}
						} else if ($_POST["organisation_id"] == 'all') {
							$PROCESSED["organisation_id"] = null;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You do not have permission to add a notice for this organisation. This error has been logged and will be investigated.";
							application_log("Proxy id [" . $_SESSION['details']['proxy_id'] . "] tried to eicreate a course within an organisation [" . $organisation_id . "] they didn't have permissions on. ");
						}
	
						if((isset($_POST["target"])) && ($target_audience = clean_input($_POST["target"], "alphanumeric"))) {
							$PROCESSED["target"] = $target_audience;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must select a valid target audience from the select box.";
						}
	
						if((isset($_POST["notice_summary"])) && ($notice_summary = strip_tags(clean_input($_POST["notice_summary"], "trim"), "<a><br><p>"))) {
							$PROCESSED["notice_summary"] = $notice_summary;
						} else {
							$ERROR++;
							$ERRORSTR[] = "You must provide a notice summary.";
						}
	
						$display_date = validate_calendars("display", true, true);
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
							$ERRORSTR[] = "You must select a valid display start date.";
						}
	
						if(!$ERROR) {
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]		= $_SESSION["details"]["id"];
	
							if($db->AutoExecute("notices", $PROCESSED, "UPDATE", "notice_id=".$db->qstr($NOTICE_ID))) {
								application_log("success", "Successfully updated notice ID [".$NOTICE_ID."]");
	
								$url			= ENTRADA_URL."/admin/notices";
								$SUCCESS++;
								$SUCCESSSTR[]  = "You have successfully updated this notice in the system.<br /><br />You will now be redirected to the notice index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
							} else {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this notice in the system. The MEdTech Unit was informed of this error; please try again later.";
	
								application_log("error", "There was an error updating a notice. Database said: ".$db->ErrorMsg());
							}
						}
	
						if($ERROR) {
							$STEP = 1;
						}
						break;
					case 1 :
					default :
						$PROCESSED = $result;
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
	<form action="<?php echo ENTRADA_URL; ?>/admin/notices?section=edit&amp;id=<?php echo $NOTICE_ID; ?>&amp;step=2" method="post">
		<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Notice">
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
				<td><label for="target" class="form-required">Target Audience</label></td>
				<td>
					<select id="target" name="target" style="width: 300px">
											<?php
											if(is_array($NOTICE_TARGETS)) {
												if (preg_match("/^proxy_id:[\d]+$/",$PROCESSED["target"])) {
													preg_match("/[\d]+/", $PROCESSED["target"], $id);
													if (isset($id) && array_key_exists(0, $id)) {
														$NOTICE_TARGETS[$PROCESSED["target"]] = "Visible to ".get_account_data("firstlast", $id[0]);
													} else {
														$NOTICE_TARGETS[$PROCESSED["target"]] = "Visible to specfic user.";
													}
												}
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
			} else {
				$ERROR++;
				$ERRORSTR[] = "The notice you are attempting to edit exists in an organisation which you do not have permission to edit.";
	
				echo display_error();
	
				application_log("notice", "This notice [".$result["notice_id"]."] exists in an organisation which this user has no permissions to edit.");
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a notice you must provide a valid notice identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid notice identifer when attempting to edit a notice.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a notice you must provide the notices identifier.";

		echo display_error();

		application_log("notice", "Failed to provide notice identifer when attempting to edit a notice.");
	}
}
?>