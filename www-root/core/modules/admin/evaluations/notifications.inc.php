<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Secondary controller file used by the notifications module within the evaluations module.
 * /admin/evaluations/notifications
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Ilya Sorokin <isorokin@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
 * @version $Id: notifications.inc.php 1109 2010-04-07 16:47:21Z simpson $
 */

if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update")) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/evaluations/notifications", "title" => "Manage Notifications");

	if (($router) && ($router->initRoute())) {
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}
	} else {
		$url = ENTRADA_URL."/admin/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}
}