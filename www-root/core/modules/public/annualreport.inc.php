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
 * 
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @version 3.0
 * @copyright Copyright 2010 Queen's University, MEdTech Unit
 *
 * $Id: annualreport.inc.php 391 2009-01-05 14:16:18Z ad29 $
*/

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('annualreport', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["publicistrator"]["email"])."\">".html_encode($AGENT_CONTACTS["publicistrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	define("IN_ANNUAL_REPORT", true);
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/annualreport", "title" => "Annual Report");
	
	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);
		/**
		 * Include required js files and css files for use with jquery and flexigrid.
		 */
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js\"></script>\n";
		$HEAD[] = "<link href=\"".ENTRADA_URL."/css/annualreport.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[] = "<link href=\"".ENTRADA_URL."/css/calendar.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.min.js\"></script>\n";
		$JQUERY[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery-ui.min.js\"></script>\n";
		$JQUERY[] = "<link href=\"".ENTRADA_URL."/css/jquery/jquery-ui.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		
		$JQUERY[] = "<link href=\"".ENTRADA_URL."/css/jquery/flexigrid.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
		$JQUERY[] = "<script language=\"javascript\" type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/flexigrid.pack.js\"></script>\n";
		$JQUERY[] = "<script type=\"text/javascript\">jQuery.noConflict();</script>";
		
		/**
		 * Add the Annual Report module secondary navigation.
		 */
		$sidebar_html  = "<ul class=\"menu\">";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/education\" title=\"Education\">Education</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/research\" title=\"Scholarship, Research and Other Creative Activity\">Scholarship, Research and Other Creative Activity</a></li>\n";
		// Only include this link for clinical members
		if($_SESSION["details"]["clinical_member"]) {
			$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/clinical\" title=\"Clinical\">Clinical</a></li>\n";
		}
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/academic\" title=\"Service\">Service</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/selfeducation\" title=\"Self Education/Faculty Development\">Self Education / Faculty Development</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/prizes\" title=\"Prizes, Honours and Awards\">Prizes, Honours and Awards</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/activityprofile\" title=\"Activity Profile\">Activity Profile</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/generate\" title=\"Annual Report Generator\">Annual Report Generator</a></li>\n";
		$sidebar_html .= "<li class=\"link\"><a href=\"".ENTRADA_URL."/annualreport/reports\" title=\"Annual Report Generator\">My Reports</a></li>\n";
		$sidebar_html .= "</div>\n";
		
		new_sidebar_item("Annual Report Sections", $sidebar_html, "annual-report-nav", "open");
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
			$exploded = explode("/", $module_file);
			$explodedPosition = count($exploded) - 2;
			$explodedFile = $exploded[$explodedPosition];
			if ($explodedFile == "annualreport") {
				header("Location: ".ENTRADA_URL."/annualreport/education");
				exit;
			} else {
				require_once($module_file);
			}
		}

		/**
		 * Check if preferences need to be updated on the server at this point.
		 */
		preferences_update($MODULE, $PREFERENCES);
	} else {
		$url = ENTRADA_URL."/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}
}