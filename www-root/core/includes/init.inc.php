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
 * The default init file that includes all common Entrada includes.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

/**
 * Register the Zend autoloader so we use any part of Zend Framework without
 * the need to require the specific Zend Framework files.
 */
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();
//$loader->registerNamespace('Entrada_');

require_once("config/settings.inc.php");

require_once("Entrada/adodb/adodb.inc.php");

require_once("functions.inc.php");

require_once("dbconnection.inc.php");

require_once("Entrada/pagination/pagination.class.php");
require_once("Entrada/router/router.class.php");

require_once("cache.inc.php");
require_once("acl.inc.php");

require_once("Models/users/User.class.php");
if (isset($_SESSION["isAuthorized"]) && (bool) $_SESSION["isAuthorized"]) {
	$ENTRADA_USER = User::get($_SESSION["details"]["id"]);
} else {
	$ENTRADA_USER = false;
}

@ini_set("filter.default_flags", FILTER_FLAG_NO_ENCODE_QUOTES);

/**
 * If Entrada is in development mode and the user is not a developer send them to the
 * notavailable.html file.
 */
if ((defined("DEVELOPMENT_MODE")) && ((bool) DEVELOPMENT_MODE)) {
	if ((!is_array($DEVELOPER_IPS)) || (!in_array($_SERVER["REMOTE_ADDR"], $DEVELOPER_IPS))) {
		header("Location: ".ENTRADA_URL."/notavailable.html");
		exit;
	}
}

if ((defined("AUTH_ALLOW_CAS")) && (AUTH_ALLOW_CAS == true)) {
	require_once("Entrada/cas/CAS.php");

	phpCAS::client(CAS_VERSION_2_0, AUTH_CAS_HOSTNAME, AUTH_CAS_PORT, AUTH_CAS_URI, false);
}

$ENTRADA_ACTIVE_TEMPLATE = "";

if ($ENTRADA_USER) {

	/**
	 * System groups define which system groups & role combinations are allowed to
	 * access this system. Note the student and alumni groups have many roles.
	 */
	$query = "SELECT a.*
			  FROM `" . AUTH_DATABASE . "`.`system_groups` a,
			  `" . AUTH_DATABASE . "`.`system_group_organisation` c
			  WHERE a.`id` = c.`groups_id`			  
			  AND c.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
			  ORDER BY a.`group_name` ASC";
	$results = $db->getAll($query);
	if ($results) {
		foreach($results as $result) {
			$SYSTEM_GROUPS[$result["group_name"]] = array();
			$query = "SELECT a.*
			  FROM `" . AUTH_DATABASE . "`.`system_roles` a
			  WHERE a.`groups_id` = " . $result["id"] . "
			  ORDER BY a.`role_name` ASC";
			$roles = $db->getAll($query);
			if ($roles) {
				foreach($roles as $role) {
					$SYSTEM_GROUPS[$result["group_name"]][] = $role["role_name"];
				}
			}
		}
	}

	if (!$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"]) {
		$query = "SELECT a.`group`, a.`role`, a.`id`
						  FROM `" . AUTH_DATABASE . "`.`user_access` a
						  WHERE a.`user_id` = " . $db->qstr($ENTRADA_USER->getProxyId()) . "
						  AND a.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
						  ORDER BY a.`id` ASC";
		$result = $db->getRow($query);
		if ($result) {
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"] = $result["id"];
			$ENTRADA_USER->setActiveGroupRole($result["id"]);		
			$_SESSION["permissions"] = load_org_group_role($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"]);
		}
	}

	if (isset($_GET["organisation_id"])) {
		$organisation = clean_input($_GET["organisation_id"], array("trim", "notags", "int"));
		
		$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"] = $organisation;
		$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]["organisation_id"] = $organisation;
		$ENTRADA_USER->setActiveOrganisation($organisation);

		$query = "SELECT a.`group`, a.`role`, a.`id`
					  FROM `" . AUTH_DATABASE . "`.`user_access` a
					  WHERE a.`user_id` = " . $ENTRADA_USER->getProxyId() . "
					  AND a.`organisation_id` = " . $db->qstr($organisation) . "
					  ORDER BY a.`id` ASC";

		$result = $db->getRow($query);
		if ($result) {
			$ENTRADA_USER->setActiveGroupRole($result["id"]);
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"] = $result["id"];
			$_SESSION["permissions"] = load_org_group_role($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"]);
		}
	}

	if (isset($_GET["ua_id"])) {
		$ua_id = clean_input($_GET["ua_id"], array("trim", "notags", "int"));
		$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"] = $ua_id;
		$ENTRADA_USER->setActiveGroupRole($ua_id);
		$_SESSION["permissions"] = load_org_group_role($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["ua_id"]);
	}

 	$query = "SELECT `template` FROM `" . AUTH_DATABASE . "`.`organisations` WHERE `organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation());
	$ENTRADA_ACTIVE_TEMPLATE = $db->CacheGetOne(CACHE_TIMEOUT, $query);
}

if (!$ENTRADA_ACTIVE_TEMPLATE) {
	$ENTRADA_ACTIVE_TEMPLATE = DEFAULT_TEMPLATE;
}

global $ENTRADA_ACTIVE_TEMPLATE;

define("TEMPLATE_URL", ENTRADA_URL."/templates/".$ENTRADA_ACTIVE_TEMPLATE);
define("TEMPLATE_ABSOLUTE", ENTRADA_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE);
define("TEMPLATE_RELATIVE", ENTRADA_RELATIVE."/templates/".$ENTRADA_ACTIVE_TEMPLATE);


/**
 * Setup Zend_Translate for language file support.
 */
if ($ENTRADA_CACHE) Zend_Translate::setCache($ENTRADA_CACHE);
$translate = new Zend_Translate("array", ENTRADA_ABSOLUTE."/templates/".$ENTRADA_ACTIVE_TEMPLATE."/languages/".DEFAULT_LANGUAGE.".lang.php", DEFAULT_LANGUAGE);

$ADODB_CACHE_DIR = CACHE_DIRECTORY;
$time_start = getmicrotime();

$ERROR = 0;
$ERRORSTR = array();

$NOTICE = 0;
$NOTICESTR = array();

$SUCCESS = 0;
$SUCCESSSTR = array();

$BREADCRUMB = array();
$HEAD = array();
$ONLOAD = array();
$ONUNLOAD = array();
$JQUERY = array();
$SIDEBAR = array();
$PAGE_META = array();

$CAS_AUTHENTICATED = false;

$MODULE = "login";
$SECTION = "index";
$ACTION = "";
$STEP = 1;
$PROCESSED = array();

if (isset($_SESSION["isAuthorized"]) && (bool) $_SESSION["isAuthorized"]) {
	$PROXY_ID = $_SESSION["details"]["id"];
	$GROUP = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"];
	$ROLE = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"];
} else {
	$PROXY_ID = 0;
	$GROUP = "";
	$ROLE = "";
}

/**
 * Allows you to specify via get or post, which component of the particular
 * module you would like to load (i.e. index, add, edit, delete, etc).
 */
if ((isset($_GET["section"])) && ($tmp_input = clean_input($_GET["section"], array("nows", "url")))) {
	$SECTION = $tmp_input;
} elseif ((isset($_POST["section"])) && ($tmp_input = clean_input($_POST["section"], array("nows", "url")))) {
	$SECTION = $tmp_input;
}

/**
 * Additional variable which allows allows you to specify via get or post,
 * which action within a particular module component you would like to run
 * (i.e. http:// ... /admin/events?section=add&action=faculty)
 */
if ((isset($_GET["action"])) && ($tmp_input = clean_input($_GET["action"], array("nows", "url")))) {
	$ACTION = $tmp_input;
} elseif ((isset($_POST["action"])) && ($tmp_input = clean_input($_POST["action"], array("nows", "url")))) {
	$ACTION = $tmp_input;
}

/**
 * Allows you to specify which step you are on within a particular module
 * component (i.e. http:// ... /admin/events?section=add&step=2).
 */
if ((isset($_GET["step"])) && ($tmp_input = clean_input($_GET["step"], array("nows", "int")))) {
	$STEP = $tmp_input;
} elseif ((isset($_POST["step"])) && ($tmp_input = clean_input($_POST["step"], array("nows", "int")))) {
	$STEP = $tmp_input;
}