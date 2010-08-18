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

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_MSPR_ADMIN"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("mspr", "create", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	if (isset($_GET['id']) && is_int($_GET['id'])) {
		$user_id = $_GET['id'];
		
		//single user generation
		$mode = "user_mode";
	} elseif (isset($_POST['year']) && isset($_POST['user_id'])) {
		$year = $_POST['year'];
		$mode = "group_mode";
		$user_ids = $_POST['user_id'];
	} else {
		$ERROR++;
		$ERRORSTR[] = "Insufficient data provided to generate report(s)";
		display_status_messages();
	}
	if (!$ERROR) {
		require_once("Models/MSPRs.class.php");
		switch($mode) {
			case "user_mode":
				$user = User::get($user_id);
				$mspr = MSPR::get($user);
				$name = $user->getFirstname() . " " . $user->getLastname();
				
				if ($mspr->saveMSPRFiles()) {
					$SUCCESS++;
					$SUCCESSSTR[] = "Report successfully generated. You will be redirected to the student's MSPR page in 5 seconds.";
				} else {
					$ERROR++;
					$ERRORSTR[] = "Error generating report for $name. You will be redirected to the student's MSPR page in 5 seconds.";
				}
				header( "refresh:5;url=".ENTRADA_URL."/admin/users/manage?section=mspr&id=$user_id" );
				break;
			case "group_mode";
				$has_error = false;
				$timestamp = time();
				foreach ($user_ids as $user_id) {
					
					$user = User::get($user_id);
					$mspr = MSPR::get($user);
					$name = $user->getFirstname() . " " . $user->getLastname();
					
					if (!$mspr->saveMSPRFiles($timestamp)) {
						$ERROR++;
						$ERRORSTR[] = "Error generating report for $name.";
					}
											
				}
				if (!$ERROR) {
					$SUCCESS++;
					$SUCCESSSTR[] = "Reports successfully generated. You will be redirected to the class MSPR page in 5 seconds."; 
				} else {
					$ERROR++;
					$ERRORSTR[] = "You will be redirected to the class MSPR page in 5 seconds.";
				}
				header( "refresh:5;url=".ENTRADA_URL."/admin/mspr?year=".$year );
				break;
		}
	}
	display_status_messages();
}