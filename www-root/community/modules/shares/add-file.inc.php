<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Used to upload files to a specific folder of a community.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_SHARES"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

$HEAD[] = "<link href=\"".ENTRADA_URL."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
$HEAD[] = "<script type=\"text/javascript\" src=\"".COMMUNITY_URL."/javascript/shares.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

echo "<h1>Upload File</h1>\n";

if ($RECORD_ID) {
	$query			= "SELECT * FROM `community_shares` WHERE `cshare_id` = ".$db->qstr($RECORD_ID)." AND `cpage_id` = ".$db->qstr($PAGE_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID);
	$folder_record	= $db->GetRow($query);
	if ($folder_record) {
		
		$query = "SELECT COUNT(*) FROM `community_share_files` WHERE `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." AND `proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])." AND `file_active` = 1";
		
		
		if ( !$db->GetOne($query) || ($COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || (!$COMMUNITY_MEMBER && $folder_record["allow_troll_read"]) || $COMMUNITY_ADMIN){
			if (shares_module_access($RECORD_ID, "add-file")) {
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$folder_record["cshare_id"], "title" => limit_chars($folder_record["folder_title"], 32));
				$BREADCRUMB[] = array("url" => COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=add-file&id=".$RECORD_ID, "title" => "Upload File");
	
				// Error Checking
				switch($STEP) {
					case 2 :
						if (isset($_FILES["uploaded_file"])) {
							switch($_FILES["uploaded_file"]["error"]) {
								case 0 :
									if (($file_filesize = (int) trim($_FILES["uploaded_file"]["size"])) <= $VALID_MAX_FILESIZE) {
										$PROCESSED["file_version"]		= 1;
										$PROCESSED["file_mimetype"]		= strtolower(trim($_FILES["uploaded_file"]["type"]));
										$PROCESSED["file_filesize"]		= $file_filesize;
										$PROCESSED["file_filename"]		= useable_filename(trim($_FILES["uploaded_file"]["name"]));
	
										if ((!defined("COMMUNITY_STORAGE_DOCUMENTS")) || (!@is_dir(COMMUNITY_STORAGE_DOCUMENTS)) || (!@is_writable(COMMUNITY_STORAGE_DOCUMENTS))) {
											$ERROR++;
											$ERRORSTR[] = "There is a problem with the document storage directory on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
											application_log("error", "The community document storage path [".COMMUNITY_STORAGE_DOCUMENTS."] does not exist or is not writable.");
										}
									}
								break;
								case 1 :
								case 2 :
									$ERROR++;
									$ERRORSTR[] = "The file that was uploaded is larger than ".readable_size($VALID_MAX_FILESIZE).". Please make the file smaller and try again.";
								break;
								case 3 :
									$ERROR++;
									$ERRORSTR[]	= "The file that was uploaded did not complete the upload process or was interrupted; please try again.";
								break;
								case 4 :
									$ERROR++;
									$ERRORSTR[]	= "You did not select a file from your computer to upload. Please select a local file and try again.";
								break;
								case 6 :
								case 7 :
									$ERROR++;
									$ERRORSTR[]	= "Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
									application_log("error", "Community file upload error: ".(($_FILES["filename"]["error"] == 6) ? "Missing a temporary folder." : "Failed to write file to disk."));
								break;
								default :
									application_log("error", "Unrecognized file upload error number [".$_FILES["filename"]["error"]."].");
								break;
							}
						} else {
							$ERROR++;
							$ERRORSTR[]	 = "To upload a file to this folder you must select a local file from your computer.";
						}
	
						/**
						 * Required field "title" / File Title.
						 */
						if ((isset($_POST["file_title"])) && ($title = clean_input($_POST["file_title"], array("notags", "trim")))) {
							$PROCESSED["file_title"] = $title;
						} elseif ((isset($PROCESSED["file_filename"])) && ($PROCESSED["file_filename"])) {
							$PROCESSED["file_title"] = $PROCESSED["file_filename"];
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>File Title</strong> field is required.";
						}
	
						/**
						 * Non-Required field "description" / File Description.
						 *
						 */
						if ((isset($_POST["file_description"])) && ($description = clean_input($_POST["file_description"], array("notags", "trim")))) {
							$PROCESSED["file_description"] = $description;
						} else {
							$PROCESSED["file_description"] = "";
						}
	
						/**
						 * Non-Required field "access_method" / View Method.
						 */
						if ((isset($_POST["access_method"])) && (clean_input($_POST["access_method"], array("int")) == 1)) {
							$PROCESSED["access_method"]	= 1;
						} else {
							$PROCESSED["access_method"]	= 0;
						}
	
						/**
						 * Permission checking for member access.
						 */
						if ((isset($_POST["allow_member_revision"])) && (clean_input($_POST["allow_member_revision"], array("int")) == 1)) {
							$PROCESSED["allow_member_revision"]	= 1;
						} else {
							$PROCESSED["allow_member_revision"]	= 0;
						}
	
						/**
						 * Permission checking for troll access.
						 * This can only be done if the community_registration is set to "Open Community"
						 */
						if (!(int) $community_details["community_registration"]) {
							if ((isset($_POST["allow_troll_revision"])) && (clean_input($_POST["allow_troll_revision"], array("int")) == 1)) {
								$PROCESSED["allow_troll_revision"]	= 1;
							} else {
								$PROCESSED["allow_troll_revision"]	= 0;
							}
						} else {
							$PROCESSED["allow_troll_revision"]		= 0;
						}
	
						/**
						 * Required field "release_from" / Release Start (validated through validate_calendars function).
						 * Non-required field "release_until" / Release Finish (validated through validate_calendars function).
						 */
						if (($LOGGED_IN && $folder_record["allow_troll_read"]) || ($LOGGED_IN && $COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || $COMMUNITY_ADMIN){
							$release_dates = validate_calendars("release", true, false);
							if ((isset($release_dates["start"])) && ((int) $release_dates["start"])) {
								$PROCESSED["release_date"]	= (int) $release_dates["start"];
							} else {
								$ERROR++;
								$ERRORSTR[] = "The <strong>Release Start</strong> field is required.";
							}
							if ((isset($release_dates["finish"])) && ((int) $release_dates["finish"])) {
								$PROCESSED["release_until"]	= (int) $release_dates["finish"];
							} else {
								$PROCESSED["release_until"]	= 0;
							}
						}else{
							$PROCESSED["release_date"] = time();
						}
						
						if (!$ERROR) {
							$PROCESSED["cshare_id"]		= $RECORD_ID;
							$PROCESSED["community_id"]	= $COMMUNITY_ID;
							$PROCESSED["proxy_id"]		= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
							$PROCESSED["file_active"]	= 1;
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];
	
							if ($db->AutoExecute("community_share_files", $PROCESSED, "INSERT")) {
								if ($FILE_ID = $db->Insert_Id()) {
									$PROCESSED["csfile_id"]	= $FILE_ID;
	
									if ($db->AutoExecute("community_share_file_versions", $PROCESSED, "INSERT")) {
										if ($VERSION_ID = $db->Insert_Id()) {
											if (communities_shares_process_file($_FILES["uploaded_file"]["tmp_name"], $VERSION_ID)) {
												
												if ($LOGGED_IN) {
													if ($COMMUNITY_MEMBER) {
														if (($COMMUNITY_ADMIN) || ($folder_record["allow_member_read"] == 1)) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$RECORD_ID;
														} elseif ($folder_record["allow_member_upload"] == 1){
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
														}
													} else {
														if ($folder_record["allow_troll_read"] == 1) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-folder&id=".$RECORD_ID;
														} elseif ($folder_record["allow_troll_upload"] == 1) {
															$url = COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL;
														}
													}
												}
												$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
												$SUCCESS++;
												$SUCCESSSTR[]	= "You have successfully uploaded ".html_encode($PROCESSED["file_filename"])." (version 1).<br /><br />You will now be redirected to this files page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
	
												communities_log_history($COMMUNITY_ID, $PAGE_ID, $FILE_ID, "community_history_add_file", 1, $RECORD_ID);
											}
										}
									}
								}
							}
	
							if (!$SUCCESS) {
								/**
								 * Because there was no success, check if the file_id was set... if it
								 * was we need to delete the database record :( In the future this will
								 * be handled with transactions like it's supposed to be.
								 */
								if ($FILE_ID) {
									$query	= "DELETE FROM `community_share_files` WHERE `csfile_id` = ".$db->qstr($FILE_ID)." AND `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." LIMIT 1";
									@$db->Execute($query);
	
									/**
									 * Also delete the file version, again, hello transactions.
									 */
									if ($VERSION_ID) {
										$query	= "DELETE FROM `community_share_file_versions` WHERE `csfversion_id` = ".$db->qstr($VERSION_ID)." AND `csfile_id` = ".$db->qstr($FILE_ID)." AND `cshare_id` = ".$db->qstr($RECORD_ID)." AND `community_id` = ".$db->qstr($COMMUNITY_ID)." LIMIT 1";
										@$db->Execute($query);
									}
								}
	
	
								$ERROR++;
								$ERRORSTR[]	= "Unable to store the new file on the server; the MEdTech Unit has been informed of this error, please try again later.";
	
								application_log("error", "Failed to move the uploaded Community file to the storage directory [".COMMUNITY_STORAGE_DOCUMENTS."/".$VERSION_ID."].");
							}
						}
	
						if ($ERROR) {
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
						if ($NOTICE) {
							echo display_notice();
						}
						if ($SUCCESS) {
							echo display_success();
							if (COMMUNITY_NOTIFICATIONS_ACTIVE) {
								community_notify($COMMUNITY_ID, $FILE_ID, "file", COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL."?section=view-file&id=".$FILE_ID, $RECORD_ID, $PROCESSES["release_date"]);
							}
						}
					break;
					case 1 :
					default :
						if ($ERROR) {
							echo display_error();
						}
						if ($NOTICE) {
							echo display_notice();
						}
						?>
						<form id="upload-file-form" action="<?php echo COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL; ?>?section=add-file&amp;id=<?php echo $RECORD_ID; ?>&amp;step=2" method="post" enctype="multipart/form-data">
						<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="<?php echo $VALID_MAX_FILESIZE; ?>" />
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Upload File">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 20%" />
							<col style="width: 77%" />
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3" style="padding-top: 15px; text-align: right">
									<div id="display-upload-button">
										<input type="button" class="button" value="Upload" onclick="uploadFile()" />
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td colspan="3"><h2>File Details</h2></td>
							</tr>
							<tr>
								<td colspan="2" style="vertical-align: top"><label for="uploaded_file" class="form-required">Select Local File</label></td>
								<td style="vertical-align: top">
									<input type="file" id="uploaded_file" name="uploaded_file" onchange="fetchFilename()" />
									<div class="content-small" style="margin-top: 5px">
									<strong>Notice:</strong> You may upload files under <?php echo readable_size($VALID_MAX_FILESIZE); ?>.
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2"><label for="file_title" class="form-required">File Title</label></td>
								<td><input type="text" id="file_title" name="file_title" value="<?php echo ((isset($PROCESSED["file_title"])) ? html_encode($PROCESSED["file_title"]) : ""); ?>" maxlength="64" style="width: 95%" /></td>
							</tr>
							<tr>
								<td colspan="2" style="vertical-align: top"><label for="file_description" class="form-nrequired">File Description</label></td>
								<td style="vertical-align: top">
									<textarea id="file_description" name="file_description" style="width: 95%; height: 60px" cols="50" rows="5"><?php echo ((isset($PROCESSED["file_description"])) ? html_encode($PROCESSED["file_description"]) : ""); ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<?php 
							if (($LOGGED_IN && $folder_record["allow_troll_read"]) || ($LOGGED_IN && $COMMUNITY_MEMBER && $folder_record["allow_member_read"]) || $COMMUNITY_ADMIN) {
								?>
								<tr>
									<td colspan="2" style="vertical-align: top"><label for="access_method" class="form-nrequired">Access Method</label></td>
									<td>
										<input type="radio" id="access_method_0" name="access_method" value="0" style="vertical-align: middle"<?php echo (((!isset($PROCESSED["access_method"])) || ((isset($PROCESSED["access_method"])) && (!(int) $PROCESSED["access_method"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_0" style="vertical-align: middle" class="content-small">Download this file to their computer first, then open it.</label><br />
										<input type="radio" id="access_method_1" name="access_method" value="1" style="vertical-align: middle"<?php echo (((isset($PROCESSED["access_method"])) && ((int) $PROCESSED["access_method"])) ? " checked=\"checked\"" : ""); ?> /> <label for="access_method_1" style="vertical-align: middle" class="content-small">Attempt to view it directly in the web-browser.</label><br />
									</td>
								</tr>
								<tr>
									<td colspan="3"><h2>File Permissions</h2></td>
								</tr>
								<tr>
									<td><input type="checkbox" id="allow_member_revision" name="allow_member_revision" value="1"<?php echo (((isset($PROCESSED["allow_member_revision"])) && ($PROCESSED["allow_member_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2"><label for="allow_member_revision" class="form-nrequired">Allow <strong>other community members</strong> to upload newer versions of this file.</label></td>
								</tr>
								<?php if (!(int) $community_details["community_registration"]) :  ?>
								<tr>
									<td><input type="checkbox" id="allow_troll_revision" name="allow_troll_revision" value="1"<?php echo (((isset($PROCESSED["allow_troll_revision"])) && ($PROCESSED["allow_troll_revision"] == 1)) ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2"><label for="allow_troll_revision" class="form-nrequired">Allow <strong>non-community members</strong> to upload newer versions of this file.</label></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td colspan="3"><h2>Time Release Options</h2></td>
								</tr>
								<?php
								echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : time()), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0));
							}
							?>
						</tbody>
						</table>
						</form>
						<div id="display-upload-status" style="display: none">
							<div style="text-align: left; background-color: #EEEEEE; border: 1px #666666 solid; padding: 10px">
								<div style="color: #003366; font-size: 18px; font-weight: bold">
									<img src="<?php echo ENTRADA_URL; ?>/images/loading.gif" width="32" height="32" alt="File Uploading" title="Please wait while this file is being uploaded." style="vertical-align: middle" /> Please Wait: this file is being uploaded.
								</div>
								<br /><br />
								This can take time depending on your connection speed and the filesize.
							</div>
						</div>
						<?php
					break;
				}
			} else {
				if ($ERROR) {
					echo display_error();
				}
				if ($NOTICE) {
					echo display_notice();
				}
			}
		}else{
			
			$ERROR++;
			$ERRORSTR[] = "Your access level only allows you to upload one file and revisions of it. Any additional files can be uploaded as a new revision of that file without overwriting the current file.";
			
			if ($ERROR) {
				echo display_error();
			}
			if ($NOTICE) {
				echo display_notice();
			}
		}
	} else {
		application_log("error", "The provided folder id was invalid [".$RECORD_ID."] (Upload File).");

		header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
		exit;
	}
} else {
	application_log("error", "No folder id was provided to upload into. (Upload File)");

	header("Location: ".COMMUNITY_URL.$COMMUNITY_URL.":".$PAGE_URL);
	exit;
}
?>
