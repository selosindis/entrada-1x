<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 * 
 * $Id: profile.api.php 1103 2010-04-05 15:20:37Z simpson $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if((isset($_POST["u"])) && ($username = clean_input($_POST["u"], array("trim", "credentials")))) {
		$query	= "
				SELECT a.*, b.`group`, b.`role`
				FROM `".AUTH_DATABASE."`.`user_data` AS a
				LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
				ON b.`user_id` = a.`id`
				WHERE a.`privacy_level` >= '1'
				AND a.`username` = ".$db->qstr($username)."
				AND b.`app_id` = ".$db->qstr(AUTH_APP_ID);
		$result	= $db->GetRow($query);
		if($result) {
			$privacy = (int) $result["privacy_level"];
			$PROXY_ID = $result["id"];
			if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
				$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
			} else {
				$size_official[0] = 75;
				$size_official[1] = 98;
			}
			
			if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
				$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload"); 
			} else {
				$size_upload[0] = 75;
				$size_upload[1] = 98;
			}
					
			if ($ENTRADA_ACL->amIAllowed('photo', 'create')) {
				$photo_type = "official";
			} else {
				$photo_type = "upload";
			}
			
			echo "<h2>".html_encode($result["prefix"])." ".html_encode($result["firstname"])." ".html_encode($result["lastname"])."</h2>\n";

			echo "<table style=\"width: 100%\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">\n";
			echo "<tr>\n";
			echo "	<td style=\"vertical-align: top; margin-top: 10px;\" colspan=\"2\">\n";
			$uploaded_file_active = $db->GetOne("SELECT `photo_active` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `photo_type` = 1 AND `proxy_id` = ".$db->qstr($PROXY_ID));

			echo "<img onclick=\"($('profile_pic').src == '".webservice_url("photo", array($PROXY_ID, "upload", "thumbnail"))."' ? photoShow(". "'".webservice_url("photo", array($PROXY_ID, "upload")) . "', ".$size_upload[0].", ".$size_upload[1].") : photoShow("."'".webservice_url("photo", array($PROXY_ID, "official")) . "', ".$size_official[0].", ".$size_official[1].") );\" src=\"".webservice_url("photo", array($PROXY_ID, (isset($uploaded_file_active) && $uploaded_file_active && !$ENTRADA_ACL->amIAllowed('photo', 'create') ? "upload" : (!file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official") && file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload") ? "upload" : "official")), "thumbnail"))."\" width=\"75\" height=\"98\" alt=\"".$user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"]."\" title=\"".$user_record["prefix"]." ".$user_record["firstname"]." ".$user_record["lastname"]."\" class=\"\" id=\"profile_pic\" name=\"profile_pic\" style=\"border: 1px #666666 solid\" />";

			if (isset($uploaded_file_active) && $uploaded_file_active) {
				echo "<div style=\"width: 75px; text-align: center\"><a class=\"content-small\" onclick=\"$('profile_pic').src = ( $('profile_pic').src == '".webservice_url("photo", array($PROXY_ID, "upload", "thumbnail"))."' ? '".webservice_url("photo", array($PROXY_ID, "official", "thumbnail"))."' : '".webservice_url("photo", array($PROXY_ID, "upload", "thumbnail"))."')\" style=\"cursor: pointer\">Toggle Photo</a></div>";
			}
			
			echo "	</td>\n";
			echo "	<td style=\"vertical-align: top; margin-top: 10px;\" colspan=\"2\">\n";
			echo "		<table style=\"width: 100%\" cellspacing=\"2\" cellpadding=\"2\" border=\"0\">\n";
			echo "		<colgroup>\n";
			echo "			<col style=\"width: 33%\" />\n";
			echo "			<col style=\"width: 67%\" />\n";
			echo "		</colgroup>\n";
			echo "		<tbody>\n";
			echo "		<tr>\n";
			echo "			<td>Official E-Mail:</td>\n";
			echo "			<td><a href=\"mailto:".html_encode($result["email"])."\">".html_encode($result["email"])."</a></td>\n";
			echo "		</tr>\n";

			if($result["email_alt"] != "") {
				echo "	<tr>\n";
				echo "		<td>Alternate E-Mail:</td>\n";
				echo "		<td><a href=\"mailto:".html_encode($result["email_alt"])."\">".html_encode($result["email_alt"])."</a></td>\n";
				echo "	</tr>\n";
			}

			echo "		<tr>\n";
			echo "			<td>Status:</td>\n";
			echo "			<td>\n";
				switch($result["group"]) {
					case "student" :
						echo "Student, Class of ".$result["role"];
					break;
					case "resident" :
						echo "Resident";
					break;
					case "alumni" :
						echo "Alumni";
						if((int) $result["role"]) {
							echo ", Class of ".$result["role"];
						}
					break;
					case "faculty" :
						echo "Faculty Member";
					break;
					case "staff" :
						echo "Staff Member";
					break;
					case "medtech" :
						echo "Staff Member";
					break;
					default :
						echo "Unspecified Role";
					break;
				}
			echo "			</td>\n";
			echo "		</tr>\n";
			
			if($privacy > 2) {
				if($result["telephone"]) {
					echo "<tr>\n";
					echo "	<td>Telephone:</td>\n";
					echo "	<td>".html_encode($result["telephone"])."</td>\n";
					echo "</tr>\n";
				}
				if($result["fax"]) {
					echo "<tr>\n";
					echo "	<td>Fax Number:</td>\n";
					echo "	<td>".html_encode($result["fax"])."</td>\n";
					echo "</tr>\n";
				}
				if(($result["address"]) && ($result["city"])) {
					echo "<tr>\n";
					echo "	<td style=\"vertical-align: top\">Address:</td>\n";
					echo "	<td style=\"vertical-align: top\">";
					echo 		html_encode($result["address"])."<br />\n";
					echo 		html_encode($result["city"]).", ".html_encode($result["province"])."<br />\n";
					echo 		html_encode($result["country"]).", ".html_encode($result["postcode"])."<br />\n";
					echo "	</td>\n";
					echo "</tr>\n";
				}
			}
			$query	= " SELECT CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
						FROM `permissions` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
						ON b.`id` = a.`assigned_to`
						WHERE a.`assigned_by`=".$db->qstr($PROXY_ID)."
						ORDER BY `valid_until` ASC";
			$assistants	= $db->GetAll($query);
			if ($assistants) {
				echo "<tr>";
				echo "	<td colspan=\"2\">&nbsp;</td>";
				echo "</tr>";
				echo "<tr>\n";
				echo "	<td style=\"vertical-align: top\">Assistants:</td>\n";
				echo "	<td style=\"vertical-align: top\">";
				echo "		<ul class=\"assistant-list\">";
				foreach ($assistants as $assistant) {
					echo "			<li>";
					echo "				<a href=\"mailto:".$assistant["email"]."\">".$assistant["fullname"]."</a>";
					echo "			</li>";
				}
				echo "		</ul>";
				echo "	</td>\n";
				echo "</tr>\n";
			}
			if ($_SESSION["details"]["id"] == $result["id"]) {
				echo "		<tr>\n";
				echo "			<td colspan=\"2\" style=\"padding-top: 20px; text-align: center;\">\n";
				echo "				<a href=\"".ENTRADA_URL."/profile?tab=photo\">Upload a new photo</a>\n";
				echo "			</td>\n";
				echo "		</tr>\n";				
			}
			echo "		</tbody>\n";
			echo "		</table>\n";
			echo "	</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
		} else {
			echo "The requested username does not exist or does not have a privacy level high enough to view.\n";
		}
	} else {
		echo "The username was not provided to lookup.\n";
	}
} else {
	application_log("error", "Profile API accessed without valid session_id.");
}
?>