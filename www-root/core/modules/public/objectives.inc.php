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
 * Primary controller file for the Objectives module.
 * /admin/objectives
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed($MODULES['objectives']['resource'], "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]." and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_OBJECTIVES",	true);

	$STEP			= 1;
	$PROCESSED		= array();
	$SECTION		= "index";
	$ACTION			= "";


	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);
		
		if(isset($_GET["action"])) {
			if(trim($_GET["action"]) != "") {
				$ACTION = clean_input($_GET["action"], "url");
			}
		}
	
		if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
			$STEP = (int) trim($_GET["step"]);
		} elseif((isset($_POST["step"])) && ((int) trim($_POST["step"]))) {
			$STEP = (int) trim($_POST["step"]);
		}
	
		if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
			$COMPETENCY_ID = (int) trim($_GET["id"]);
		}
	
		if((isset($_GET["oid"])) && ((int) trim($_GET["oid"]))) {
			$OBJECTIVE_ID = (int) trim($_GET["oid"]);
		}
	
		if((isset($_GET["cid"])) && ((int) trim($_GET["cid"]))) {
			$COURSE_ID = (int) trim($_GET["cid"]);
		}
		
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}
		
		/**
		 * Check if preferences need to be updated on the server at this point.
		 */
		preferences_update($MODULE, $PREFERENCES);
	}
}
?>