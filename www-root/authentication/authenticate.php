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
 * @version    $Id: authenticate.php 1156 2010-04-30 00:50:23Z simpson $
 * @link       http://entrada-project.org/package/Authentication
 * @since      Available since Entrada 0.6.0
 * 
 * Changes:
 * =============================================================================
 * 1.2.0 - October 24th, 2008
 * [*]  Ported to PHP5 code.
 * [*]  Major changes to the structure of the code.
 * [+]  Added auth_method variable.
 * [+]  Added ability to authenticate against LDAP servers.
 * 
 * 1.1.2 - July 11th, 2008
 * [-]	Removed the checkslashes function.
 * [+]	Added clean_input() function.
 * [*]	Used $db->qstr to prevent SQL injection.
 * 
 * 1.1.1 - November 30th, 2004
 * [+]	Added magic_quotes detection to provided variables.
 * [*]	Moved configuration options to seperate config file.
 * 
 * 1.1.0 - September 16th, 2004
 * [+]	Added organisation and department returns.
 * [*]	Updated documentation
 * 
 * 1.0.0 - April 1st, 2004
 * [+]	First release of this application.
 * 
 * Available Variables:
 * =============================================================================
 * $_POST["auth_app_id"]		- REQ	- int(12)
 * $_POST["auth_username"]		- REQ	- varchar(32)	- plain text.
 * $_POST["auth_password"]		- REQ	- varchar(32)	- md5 encrypted.
 * $_POST["auth_method"]		- OPT   - varchar(32)   - plain text.
 * 
 * $_POST["action"]				- REQ   - varchar(32)   - plain text.
 * 
 * $_POST["username"]			- REQ	- varchar(32)	- plain text.
 * $_POST["password"]			- REQ	- varchar(32)	- md5 encrypted.
 * $_POST["requested_info"]		- OPT	- serialized array() of what information you would like returned.
 *
 * $_SERVER["REMOTE_ADDR"]
 * $_SERVER["HTTP_REFERER"]
 * 
*/
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    realpath(dirname(__FILE__) . "/includes"),
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

header("Content-type: text/xml");

/**
 * Register the Zend autoloader so we use any part of Zend Framework without
 * the need to require the specific Zend Framework files.
 */
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();

require_once("classes/adodb/adodb.inc.php");
require_once("functions.inc.php");
require_once("settings.inc.php");

$db = NewADOConnection(DATABASE_TYPE);
$db->Connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
$db->SetFetchMode(ADODB_FETCH_ASSOC);

$ERROR				= 0;
$tokens_returned	= 0;

$AUTH_METHOD		= ((isset($_POST["auth_method"])) ? $_POST["auth_method"] : "local");
$SYSTEM_ACTION		= ((isset($_POST["action"])) ? $_POST["action"] : "");

$USER_DATA			= array();
$USER_ACCESS		= array();
$REGISTERED_APPS	= array();

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
echo "<authenticate xmlns:authenticate=\"".AUTH_URL."/authenticate.dtd\">\n";
echo "\t<result>\n";

/**
 * Validate the provided application credentials.
 */
if(!$ERROR) {
	if ((isset($_POST["auth_app_id"])) && (isset($_POST["auth_username"])) && (isset($_POST["auth_password"]))) {
		
		$AUTH_APP_ID	= (int) clean_input($_POST["auth_app_id"], "trim");
		$AUTH_USERNAME	= clean_input($_POST["auth_username"], "credentials");
		$AUTH_PASSWORD	= clean_input($_POST["auth_password"], "trim");
	
		$USERNAME		= clean_input($_POST["username"], "credentials");
		$PASSWORD		= clean_input($_POST["password"], "trim");
	
		$query	= "SELECT * FROM `registered_apps` WHERE `script_id` = ".$db->qstr($AUTH_USERNAME)." AND `script_password` = ".$db->qstr($AUTH_PASSWORD);
		$result = $db->GetRow($query);
		if (($result) && ($result["id"] == $AUTH_APP_ID)) {
			if ((($result["server_ip"] == "%") || ($result["server_ip"] == $_SERVER["REMOTE_ADDR"])) && (($result["server_url"] == "%") || ($result["server_url"] == $_SERVER["HTTP_REFERER"]))) {
	
				/**
				 * The provided application credentials are considered valid.
				 */
				$REGISTERED_APPS = $result;
	
			} else {
				$ERROR++;
	
				echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
				echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
				
				application_log("auth_error", "The IP address [".$_SERVER["REMOTE_ADDR"]."] or the server URL [".$_SERVER["HTTP_REFERER"]."] that was provided does not match the values specified for this application ID.");
			}
		} else {
			$ERROR++;
	
			echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
			echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
			
			application_log("auth_error", "There was a problem with the application login information (i.e. AUTH_APP_ID, AUTH_USERNAME or AUTH_PASSWORD) that was provided.");
		}
	} else {
		$ERROR++;
	
		echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
		echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
	
		application_log("auth_error", "The application login information (i.e. AUTH_APP_ID, AUTH_USERNAME or AUTH_PASSWORD) was missing from the request.");
	}
}

/**
 * Validate the provided user credentials against the requested source.
 */
if(!$ERROR) {
	switch($AUTH_METHOD) {
		case "local" :
			$query	= "SELECT * FROM `user_data` WHERE `username` = ".$db->qstr($USERNAME)." AND `password` = ".$db->qstr($PASSWORD);
			$result	= $db->GetRow($query);
			if ($result) {
				
				/**
				 * The provided user credentials are considered valid.
				 */
				$USER_DATA = $result;
				
			} else {
				$ERROR ++;
				
				echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
				echo "\t\t<message>".encrypt("The username or password you have provided is incorrect.", $AUTH_PASSWORD)."</message>\n";
				
				application_log("auth_error", "The user supplied an invalid username or password according to the local database [".DATABASE_NAME."].");
			}
		break;
		case "ldap" :
			$query			= "SELECT * FROM `user_data` WHERE `username` = ".$db->qstr($USERNAME);
			$local_result	= $db->GetRow($query);
			if ($local_result) {

				$LDAP_CONNECT_OPTIONS = array(
					array ("OPTION_NAME" => LDAP_OPT_PROTOCOL_VERSION, "OPTION_VALUE" => 3)
				);
			
				$ldap = NewADOConnection("ldap");
				$ldap->SetFetchMode(ADODB_FETCH_ASSOC);
				$ldap->debug = false;
				
				if($ldap->Connect(LDAP_HOST, LDAP_SEARCH_DN, LDAP_SEARCH_DN_PASS, LDAP_BASE_DN)) {
					if(($result	= $ldap->GetRow("uid=".$USERNAME)) && (is_array($result)) && (isset($result[LDAP_MEMBER_ATTR]))) {
						$ldap->Close();
						
						$user_dn = LDAP_MEMBER_ATTR."=".$result[LDAP_MEMBER_ATTR].",".LDAP_BASE_DN;

						if($ldap->Connect(LDAP_HOST, $user_dn, $PASSWORD, LDAP_BASE_DN)) {
							/**
							 * The provided user credentials are considered valid.
							 */
							$USER_DATA = $local_result;
							
						} else {
							$ERROR ++;
							
							echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
							echo "\t\t<message>".encrypt("The username or password you have provided is incorrect.", $AUTH_PASSWORD)."</message>\n";
							
							application_log("auth_error", "The user supplied an invalid username or password according to the LDAP server [".LDAP_HOST."]. LDAP Said: ".$ldap->ErrorMsg());
						}
					} else {
						$ERROR ++;
						
						echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
						echo "\t\t<message>".encrypt("The username or password you have provided is incorrect.", $AUTH_PASSWORD)."</message>\n";
						
						application_log("auth_error", "The user supplied an username which could not be found in the LDAP server [".LDAP_HOST."].");
					}
				} else {
					$ERROR++;
				
					echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
					echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
				
					application_log("auth_error", "Unable to establish a connection to the LDAP server [".LDAP_HOST."] to authenticate username [".$USERNAME."].");
				}				
			} else {
				$ERROR ++;
				
				echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
				echo "\t\t<message>".encrypt("Your account has not yet been setup for access to this application. Please contact a system administrator if you require further assistance.", $AUTH_PASSWORD)."</message>\n";
			
				application_log("auth_notice", "Username [".$USERNAME."] attempted to log into application_id [".$AUTH_APP_ID."], and their account has not been created in the local database [".DATABASE_NAME."].");
			}
		break;
		default :
			$ERROR ++;
			
			echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
			echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
			
			application_log("auth_error", "The requested authentication method [".$AUTH_METHOD."] was invalid.");
		break;
	}
}

/**
 * Validate the users application access.
 */
if(!$ERROR) {
	$query	= "SELECT * FROM `user_access` WHERE `user_id` = ".$db->qstr($USER_DATA["id"])." AND `app_id` = ".$db->qstr($REGISTERED_APPS["id"]);
	$result	= $db->GetRow($query);
	if ($result) {
		if ($result["account_active"] == "true") {
			if (($result["access_starts"] == 0) || ($result["access_starts"] < time())) {
				if (($result["access_expires"] == 0) || ($result["access_expires"] > time())) {
					
					/**
					 * The users application access is considered valid.
					 */
					$USER_ACCESS = $result;
					
				} else {
					$ERROR ++;

					echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
					echo "\t\t<message>".encrypt("Your account has expired for this application. Please contact a system administrator if you require further assistance.", $AUTH_PASSWORD)."</message>\n";
					
					application_log("auth_notice", "Username [".$USERNAME."] attempted to log into an expired account under application_id [".$AUTH_APP_ID."].");
				}
			} else {
				$ERROR ++;

				echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
				echo "\t\t<message>".encrypt("Your account is not yet active for this application.", $AUTH_PASSWORD)."</message>\n";
	
				application_log("auth_notice", "Username [".$USERNAME."] attempted to log into an account that is not yet active under application_id [".$AUTH_APP_ID."].");
			}
		} else {
			$ERROR ++;
			
			echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
			echo "\t\t<message>".encrypt("Your account is not currently active for this application. Please contact a system administrator if you require further assistance.", $AUTH_PASSWORD)."</message>\n";
	
			application_log("auth_notice", "Username [".$USERNAME."] attempted to log into an account was marked inactive under application_id [".$AUTH_APP_ID."].");
		}
	} else {
		$ERROR ++;
		
		echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
		echo "\t\t<message>".encrypt("Your account has not yet been setup for access to this application. Please contact a system administrator if you require further assistance.", $AUTH_PASSWORD)."</message>\n";
	
		application_log("auth_notice", "Username [".$USERNAME."] attempted to log into application_id [".$AUTH_APP_ID."], and their account has not yet been provisioned.");
	}
}

/**
 * Proceed with the requested system action.
 */
if(!$ERROR) {
	switch($SYSTEM_ACTION) {
		case "Authenticate" :

			/**
			 * Output a successfully authenticated message.
			 */
			echo "\t\t<status>".encrypt("success", $AUTH_PASSWORD)."</status>\n";
			echo "\t\t<message>".encrypt("You were successfully authenticated into this application.", $AUTH_PASSWORD)."</message>\n";
			
			if ((isset($_POST["requested_info"])) && ($REQUESTED_INFO = @unserialize(base64_decode($_POST["requested_info"]))) && (is_array($REQUESTED_INFO)) && (count($REQUESTED_INFO) > 0)) {
				$APPLICATION_SPECIFIC	= unserialize(base64_decode($USER_ACCESS["extras"]));
				$tokens_returned		= count($REQUESTED_INFO);
				
				foreach($REQUESTED_INFO as $value) {
					$type = explode("-", $value);
					switch($type[0]) {
						case "id" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["id"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "number" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["number"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "prefix" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["prefix"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "firstname" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["firstname"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "lastname" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["lastname"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "organisation_id":
							echo "\t\t<".$value.">".encrypt($USER_DATA["organisation_id"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "acl" :
						//@todo: insert ACL generation code here.
							echo "\t\t<".$value.">".encrypt($USER_DATA["organisation_id"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "department" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["department"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "email" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["email"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "email_alt" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["email_alt"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "telephone" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["telephone"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "fax" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["fax"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "address" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["address"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "city" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["city"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "province" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["province"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "postcode" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["postcode"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "country" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["country"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "privacy_level" :
							echo "\t\t<".$value.">".encrypt($USER_DATA["privacy_level"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "access_starts" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["access_starts"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "access_expires" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["access_expires"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "last_login" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["last_login"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "last_ip" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["last_ip"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "role" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["role"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "group" :
							echo "\t\t<".$value.">".encrypt($USER_ACCESS["group"], $AUTH_PASSWORD)."</".$value.">\n";
						break;
						case "private" :
							if ($type[1]) {
								echo "\t\t<private-".$type[1].">".(($APPLICATION_SPECIFIC[$type[1]]) ? encrypt($APPLICATION_SPECIFIC[$type[1]], $AUTH_PASSWORD) : "")."</private-".$type[1].">\n";
							}
						break;
						default :
							continue;
						break;
					}
				}
				
				application_log("auth_success", "Username [".$USERNAME."] was successfully authenticated into application_id [".$AUTH_APP_ID."]. ".$tokens_returned." token".(($tokens_returned != 1) ? "s were" : "was")." returned.");
			}
		break;
		case "updateLastLogin" :
			if ((isset($_POST["last_login"])) && ($_POST["last_login"] != "")) {
				$LAST_LOGIN = (int) $_POST["last_login"];
			} else {
				$LAST_LOGIN = time();
			}
			
			if ((isset($_POST["last_ip"])) && ($_POST["last_ip"] != "") && (preg_match("^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}^", $_POST["last_ip"]))) {
				$LAST_IP	= $_POST["last_ip"];
			} else {
				$LAST_IP	= 0;
			}
			
			if (!@$db->Execute("UPDATE `user_access` SET `last_login` = ".$db->qstr($LAST_LOGIN).", `last_ip` = ".$db->qstr($LAST_IP)." WHERE `user_id` = ".$db->qstr($USER_DATA["id"])." AND `app_id` = ".$db->qstr($REGISTERED_APPS["id"]))) {
				application_log("auth_error", "Unabled to update the user_access table for the last login information action. Database said: ".$db->ErrorMsg());
			}
		break;
		default :
			$ERROR++;

			echo "\t\t<status>".encrypt("failed", $AUTH_PASSWORD)."</status>\n";
			echo "\t\t<message>".encrypt("A problem occurred during the authentication process and we were unable to complete the request. A system administrator has been notified of the error, please try again later.", $AUTH_PASSWORD)."</message>\n";
			
			application_log("auth_error", "An unrecognized authentication action [".$SYSTEM_ACTION."] was used against the authentication system.");
		break;
	}
}

echo "\t</result>\n";
echo "</authenticate>\n";
?>