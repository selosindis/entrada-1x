<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Secondary controller file used by the forms module within the evaluations module.
 * /admin/evaluations/forms
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/


if (!defined("IN_EVALUATIONS")) {
        //echo "______log______1"."<br>";
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
        //echo "______log______2"."<br>";
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update")) {
        //echo "______log______3"."<br>";
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
        //echo "______log______4"."<br>";
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations", "title" => "Manage Evaluation");

	if (($router) && ($router->initRoute())) {
        //echo "______log______5"."<br>";
		$module_file = $router->getRoute();
		if ($module_file) {
                        require_once("Entrada/evaluations/scheduler_inc.php");
			require_once($module_file);
		}
	} else {
        //echo "______log______6"."<br>";
		$url = ENTRADA_URL."/admin/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}
}