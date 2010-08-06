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


			if ($ERROR) {
				echo display_error();
			}

			if ($NOTICE) {
				echo display_notice();
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