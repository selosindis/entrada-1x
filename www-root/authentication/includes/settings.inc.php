<?php
/**
 * Entrada Authenticator - Server
 *
 * This server portion of the Entrada Authenticatior.
 * 
 * @todo
 *
 * LICENSE: TBD
 *
 * @copyright  2008 Queen's University, Medical Education Technology
 * @author     Matt Simpson <matt.simpson@queensu.ca>
 * @license    http://entrada-project.org/legal/licence
 * @version    $Id: dist-config.inc.php 243 2008-10-27 18:17:44Z simpson $
 * @link       http://entrada-project.org/package/Authentication
 * @since      Available since Entrada 0.6.0
 * 
*/

$config = new Zend_Config(require "config/config.inc.php");

define("AUTH_URL", $config->entrada_url."/authentication");	// Full URL to the directory where authenticate.php lives without a trailing slash.

define("DATABASE_TYPE", "mysqli");																				// MySQL is currently only supported.
define("DATABASE_HOST", $config->database->host);		// The host address of your MySQL server.
define("DATABASE_NAME",	$config->database->auth_database);		// The name of the database to connect to.
define("DATABASE_USER",	$config->database->username);	// A username that can access this database.
define("DATABASE_PASS",	$config->database->password);	// The password for the username to connect to the database.

define("LDAP_HOST", "ldap.yourschool.ca");							// The hostname of your LDAP server.
define("LDAP_BASE_DN", "ou=people,dc=queensu,dc=ca");					// The BaseDN of your LDAP server.
define("LDAP_SEARCH_DN", "uid=readonly,ou=people,dc=yourschool,dc=ca");	// The LDAP username that is used to search LDAP tree for the member attribute.
define("LDAP_SEARCH_DN_PASS", "");											// The LDAP password for the SearchDN above. These fields are optional.
define("LDAP_MEMBER_ATTR", "queensuCaUniUid");								// The member attribute used to identify the users unique LDAP ID.

define("ALLOW_LOCAL", true);													// true | false : whether you want to allow local database authentication or not.
define("ALLOW_LDAP", false);													// true | false : whether you want to allow LDAP authentication or not.

define("NOTIFY_ADMIN_ON_ERROR",	true);

$AGENT_CONTACTS = array();
$AGENT_CONTACTS["administrator"] = array("name" => $config->admin->firstname." ".$config->admin->lastname, "email" => $config->admin->email);

define("LOG_DIRECTORY",	$config->entrada_storage."/logs");										// Full directory path to the logs directory without a trailing slash.