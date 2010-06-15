<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Allows administrators to close user incidents in the entrada_auth.user_incidents table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: close.inc.php 1187 2010-05-06 13:44:57Z finglanj $
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("incident", "delete", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if (isset($_POST["delete_id"]) && ($incident_ids = $_POST["delete_id"]) && is_array($incident_ids) && count($incident_ids)) {
		foreach ($incident_ids as $incident_id) {
			$incident_id = clean_input($incident_id, array("nows", "int"));
			if ($incident_id) {
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_incidents` WHERE `incident_id` = ".$db->qstr($incident_id);
				$incident_record = $db->GetRow($query);
				if ($incident_record) {
					$query = "UPDATE `".AUTH_DATABASE."`.`user_incidents` SET `incident_status` = '0' WHERE `incident_id` = ".$db->qstr($incident_id);
					if ($db->Execute($query)) {
						$SUCCESS++;
						$SUCCESSSTR[] = "You have successfully closed the incident [".$incident_id."] titled \"".$incident_record["incident_title"]."\" for the user [".$incident_record["proxy_id"]."].";
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was an issue closing the incident [".$incident_id."] titled \"".$incident_record["incident_title"]."\" for the user [".$incident_record["proxy_id"]."].";
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "In order to edit a user incident you must provide a valid identifier. The provided ID does not exist in this system.";
					application_log("notice", "Failed to provide a valid incident identifer when attempting to edit a user incident.");
				}
			}
		}
		
		if ($SUCCESS) {
			$url = ENTRADA_URL."/admin/users?section=edit&id=".$incident_record["proxy_id"];

			$SUCCESS++;
			$SUCCESSSTR[] = "You have successfully updated the incidents in the system.<br /><br />You will now be redirected to the user edit page for user id [".$incident_record["proxy_id"]."]; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
			
			$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

			application_log("success", "Proxy ID [".$_SESSION["details"]["id"]."] successfully closed incidents for the user [".$incident_record["proxy_id"]."].");
		}

		if ($ERROR) {
			echo display_error();
		}

		if ($SUCCESS) {
			echo display_success();
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a user incident you must provide a incident identifier.";

		echo display_error();

		application_log("notice", "Failed to provide incident identifer when attempting to edit a user incident.");
	}
}