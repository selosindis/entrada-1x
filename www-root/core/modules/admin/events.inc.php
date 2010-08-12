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
 * Primary controller file for the Events module.
 * /admin/events
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed($MODULES["events"]["resource"], $MODULES["events"]["permission"], false)) {
	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_EVENTS",	true);
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events", "title" => $MODULES[strtolower($MODULE)]["title"]);
	
	?>
	<script type="text/javascript">
		var DELETE_IMAGE_URL = "<?php echo ENTRADA_URL."/images/action-delete.gif"; ?>";
	</script>
	<?php
	
	
	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);

		if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("nows", "int")))) {
			$EVENT_ID = $tmp_input;
		} else {
			$EVENT_ID = 0;
		}

		/**
		 * Check for groups which have access to the administrative side of this module
		 * and add the appropriate toggle sidebar item.
		 */
		if ($ENTRADA_ACL->amIAllowed("eventcontent", "update", false)) {
			switch ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]) {
				case "admin" :
					$admin_wording = "Administrator View";
				break;
				case "pcoordinator" :
					$admin_wording = "Coordinator View";
				break;
				case "director" :
					$admin_wording = "Director View";
				break;
				case "teacher" :
				case "lecturer" :
					$admin_wording = "Teacher View";
				break;
				default :
					$admin_wording = "";
				break;
			}

			$sidebar_html  = "<ul class=\"menu\">\n";
			$sidebar_html .= "	<li class=\"off\"><a href=\"".ENTRADA_URL."/events".(($EVENT_ID) ? "?".replace_query(array("id" => $EVENT_ID, "action" => false, "section" => false)) : "")."\">Student View</a></li>\n";
			if($admin_wording) {
				$sidebar_html .= "<li class=\"on\"><a href=\"".ENTRADA_URL."/admin/events".(($EVENT_ID) ? "?".replace_query(array("id" => $EVENT_ID, "action" => "edit")) : "")."\">".html_encode($admin_wording)."</a></li>\n";
			}
			$sidebar_html .= "</ul>\n";

			new_sidebar_item("Display Style", $sidebar_html, "display-style", "open");
		}

		$ORGANISATION_LIST	= array();
		$query		= "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
		$results	= $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				if ($ENTRADA_ACL->amIAllowed("resourceorganisation".$result["organisation_id"], "read")) {
					$ORGANISATION_LIST[$result["organisation_id"]] = html_encode($result["organisation_title"]);
				}
			}
		}
		
		if (isset($_GET["org"]) && ($organisation = ((int)$_GET["org"])) && array_key_exists($organisation, $ORGANISATION_LIST)) {
			$ORGANISATION_ID = $organisation;
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"] = $ORGANISATION_ID;
		} else {
			if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"]) {
				$ORGANISATION_ID = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"];
			} else {
				$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"] = $ORGANISATION_ID;
			}
		}
		
		if ($ORGANISATION_LIST && count($ORGANISATION_LIST) > 1) {
			$sidebar_html  = "<ul class=\"menu\">\n";
			foreach ($ORGANISATION_LIST as $key => $organisation_title) {
				if ($key == $ORGANISATION_ID) {
					$sidebar_html .= "	<li class=\"on\"><a href=\"".ENTRADA_URL."/events?".replace_query(array("org" => $key))."\">".html_encode($organisation_title)."</a></li>\n";
				} else {
					$sidebar_html .= "<li class=\"off\"><a href=\"".ENTRADA_URL."/events?".replace_query(array("org" => $key))."\">".html_encode($organisation_title)."</a></li>\n";
				}
			}
			$sidebar_html .= "</ul>\n";
	
			new_sidebar_item("Organisations", $sidebar_html, "display-style", "open");
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