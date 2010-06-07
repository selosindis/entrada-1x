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
 * $Id: communities.inc.php 1171 2010-05-01 14:39:27Z ad29 $
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

define("IN_COMMUNITIES", true);

$BREADCRUMB[] = array("url" => ENTRADA_URL."/".$MODULE, "title" => $translate->_("breadcrumb_communities_title"));

if (($router) && ($router->initRoute())) {
	$PREFERENCES = preferences_load($MODULE);

	if ((isset($_GET["id"])) && ($tmp_input = clean_input($_GET["id"], array("nows", "int")))) {
		$COMMUNITY_ID = $tmp_input;
	} else {
		$COMMUNITY_ID = 0;
	}

	$year_start		= (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
	$year_end		= ($year_start - 4);

	$GROUP_TARGETS						= array();
	for($i = $year_start; $i > $year_end; $i--) {
		$GROUP_TARGETS["student_".$i]		= "Students, Class of ".$i;
	}
	$GROUP_TARGETS["alumni"]				= "Student Alumni";
	$GROUP_TARGETS["faculty"]				= "Faculty Members";
	$GROUP_TARGETS["resident"]				= "Student Residents";
	$GROUP_TARGETS["staff"]					= "Staff Members";

	asort($GROUP_TARGETS);

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/communities.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/communities.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	$query = "	SELECT b.`community_id`, b.`community_url`, b.`community_title`
				FROM `community_members` AS a
				LEFT JOIN `communities` AS b
				ON b.`community_id` = a.`community_id`
				WHERE a.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
				AND a.`member_active` = '1'
				AND b.`community_active` = '1'
				AND b.`community_template` <> 'course'
				ORDER BY b.`community_title` ASC";
	$results = $db->GetAll($query);
	if ($results) {
		$sidebar_html  = "<ul class=\"menu\">\n";
		foreach ($results as $result) {
			$sidebar_html .= "<li class=\"community\"><a href=\"".ENTRADA_URL."/community".$result["community_url"]."\">".html_encode($result["community_title"])."</a></li>\n";
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("My Communities", $sidebar_html, "my-communities", "open");
	}

	$module_file = $router->getRoute();
	if ($module_file) {
		require_once($module_file);
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