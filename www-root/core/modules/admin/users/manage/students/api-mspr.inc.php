<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if ($ENTRADA_ACL->amIAllowed("mspr", "create", false)) {

		ob_clear_open_buffers();
	
		require_once(dirname(__FILE__)."/includes/functions.inc.php");
		
		$user = User::get($user_record["id"]);
		$controller = new MSPRAdminController($translate, $user);
		$controller->process();		
	}
	exit;
}
?>
