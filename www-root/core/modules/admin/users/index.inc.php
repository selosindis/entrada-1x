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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED") || !defined("IN_USERS")) {
	exit;
} elseif (!isset($_SESSION["isAuthorized"]) || !$_SESSION["isAuthorized"]) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	add_manage_user_sidebar();
	/**
	 * Add this for the tabs.
	 */
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";

	$search_type		= "browse-newest";
	$browse_number		= 25;
	$results_per_page	= 25;
	$search_query		= "";
	$search_query_text	= "";
	$query_counter		= "";
	$query_search		= "";
	$show_results		= false;

	/**
	 * Determine the type of search that is requested.
	 */
	if (isset($_GET["type"]) && in_array(trim($_GET["type"]), array("search", "browse-group", "browse-dept"))) {
		$search_type = clean_input($_GET["type"], "trim");
	}

    $HEAD[] = "<script type=\"text/javascript\" >var ENTRADA_URL = '". ENTRADA_URL ."';</script>\n";
    $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".  ENTRADA_URL ."/javascript/jquery/jquery.advancedsearch.js\"></script>\n";
	$HEAD[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".  ENTRADA_URL ."/css/jquery/jquery.advancedsearch.css\" />\n";

	$i = count($HEAD);
	$HEAD[$i]  = "<script type=\"text/javascript\">\n";
	$HEAD[$i] .= "addListGroup('account_type', 'cs-top');\n";
	if (is_array($SYSTEM_GROUPS)) {
		$item = 1;
		foreach ($SYSTEM_GROUPS as $group => $roles) {
			$HEAD[$i] .= "addList('cs-top', '".ucwords($group)."', '".$group."', 'cs-sub-".$item."', ".(((isset($_GET["g"])) && ($_GET["g"] == $group)) ? "1" : "0").");\n";
			$HEAD[$i] .= "addOption('cs-sub-".$item."', '-- Any --', 'any', ".(((!isset($_GET["r"])) || ((isset($_GET["r"])) && ($_GET["r"] == 'any'))) ? "1" : "0").");\n";
			if (is_array($roles) && count($roles)) {
				foreach ($roles as $role) {
					$HEAD[$i] .= "addOption('cs-sub-".$item."', '".ucwords($role)."', '".$role."', ".(((isset($_GET["r"])) && ($_GET["r"] == $role)) ? "1" : "0").");\n";
				}
			}
			$item++;
		}
	}
	$HEAD[$i] .= "</script>\n";

	$ONLOAD[] = "initListGroup('account_type', $('group'), $('role'))";
	
	/**
	 * Set default sort values
	 */
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "fullname";
	}
	if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
	}

	/**
	 * Update with custom sort values if given
	 */
	if (isset($_GET["sb"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = $_GET["sb"];
	}
	if (isset($_GET["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = $_GET["so"];
	}
	
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "fullname":
			$order_by = "ORDER BY `fullname` ";
		break;
		case "username":
			$order_by = "ORDER BY a.`username` ";
		break;
		case "role":
			$order_by = "ORDER BY CONCAT(b.`group`, b.`role`) ";
		break;
		case "login":
			$order_by = "ORDER BY b.`last_login` ";
		break;
		default:
			$order_by = "ORDER BY a.`id` ";
		break;
	}
	
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) {
		case "desc":
			$order_by .= "DESC";
		break;
		case "asc":
		default:
			$order_by .= "ASC";
		break;
	}
	
	switch ($search_type) {
		case "browse-group" :
			$browse_group	= false;
			$browse_role	= false;

			if ((isset($_GET["g"])) && (isset($SYSTEM_GROUPS[$group = clean_input($_GET["g"], "credentials")]))) {
				$browse_group = $group;
				$search_query_text	= html_encode(ucwords($group));
				if ((isset($_GET["r"])) && (@in_array($role = clean_input($_GET["r"], "credentials"), $SYSTEM_GROUPS[$browse_group]))) {
					$browse_role = $role;
					$search_query_text.= " &rarr; ".html_encode(ucwords($role));
				} else {
					$search_query_text.= " &rarr; Any Class";
				}
			} else {
				add_error("To browse a group, you must select a group from the group select list.");
			}

			if (!$ERROR) {
				$query_counter	= "	SELECT COUNT(DISTINCT(a.`id`)) AS `total_rows`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`group` = ".$db->qstr($browse_group)."
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									".(($browse_role) ? "AND b.`role` = ".$db->qstr($browse_role) : "");
				$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND b.`group` = ".$db->qstr($browse_group)."
									AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									".(($browse_role) ? "AND b.`role` = ".$db->qstr($browse_role) : "")."
									GROUP BY a.`id`
									$order_by
									LIMIT %d, %d";
			}
		break;
		case "browse-dept" :
			$browse_dept = 0;

			if ((isset($_GET["d"])) && ($department = clean_input($_GET["d"], array("trim", "int")))) {
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`departments` WHERE `department_id` = ".$db->qstr($department);
				$result = $db->GetRow($query);
				if ($result) {
					$browse_department = $department;
					$search_query_text = html_encode($result["department_title"]);
				} else {
					add_error("The department you have provided does not exist. Please ensure that you select a valid department from the department list.");
				}
			} elseif (isset($_GET["browse_departments"])) {
				add_error("To browse a department, you must select a department from the department selection list.");
			}

			if (!$ERROR) {
				$query_counter = "	SELECT COUNT(DISTINCT(a.`id`)) AS `total_rows`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
									ON c.`user_id` = a.`id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND c.`dep_id` = ".$db->qstr($browse_department);
				$query_search = "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									LEFT JOIN `".AUTH_DATABASE."`.`user_departments` AS c
									ON c.`user_id` = a.`id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND c.`dep_id` = ".$db->qstr($browse_department)."
									GROUP BY a.`id`
									$order_by
									LIMIT %d, %d";
			} else {
                echo display_error();
            }
		break;
		case "browse-newest" :
            if ((isset($_GET["n"])) && ($number = clean_input($_GET["n"], array("trim", "int"))) && ($number > 0) && ($number <= 100)) {
                $browse_number = $number;
                $results_per_page = $browse_number;
            }

            if (!$ERROR) {
                $search_query_text = "Newest ".(int) $browse_number." User".(($browse_number != 1) ? "s" : "");
                $query_counter = "	SELECT
				                        IF(
				                            COUNT(DISTINCT(a.`id`)) <= ".((int)$browse_number).",
				                                COUNT(DISTINCT(a.`id`)),
				                                ".$db->qstr((int)$browse_number)."
                                        ) AS `total_rows`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									ORDER BY a.`id` DESC
									LIMIT 0, ".((int)$browse_number);
                $query_search = "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
                                    GROUP BY a.`id`
									ORDER BY a.`id` DESC
									LIMIT 0, ".((int)$browse_number);
            }
		break;
		case "search" :
		default :
			if ((isset($_GET["q"])) && ($query = clean_input($_GET["q"], array("trim", "notags")))) {
				$search_query = $query;
				$search_query_text = html_encode($query);
			}

			if (isset($_GET["search-type"]) && $_GET["search-type"]) {
				if ($_GET["search-type"] == "all") {
					$query_counter	= "	SELECT count(*) as `total_rows` FROM (SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`) as t";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE (a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`
										$order_by
										LIMIT %d, %d";
				} elseif ($_GET["search-type"] == "active") {
					$query_counter	= "	SELECT count(*) as `total_rows` FROM (SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND b.`account_active` = 'true'
										AND b.`access_starts` < ".$db->qstr(time())."
										AND (b.`access_expires` > ".$db->qstr(time())." OR b.`access_expires` = 0) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`) as t";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND b.`account_active` = 'true'
										AND b.`access_starts` < ".$db->qstr(time())."
										AND (b.`access_expires` > ".$db->qstr(time())." OR b.`access_expires` = 0) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`
										$order_by
										LIMIT %d, %d";
				} elseif ($_GET["search-type"] == "inactive") {
					$query_counter	= "	SELECT count(*) as `total_rows` FROM (SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND (b.`account_active` = 'false'
										OR (b.`access_starts` > ".$db->qstr(time())."
										OR (b.`access_expires` < ".$db->qstr(time())." AND b.`access_expires` != 0))) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`) as t";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND (b.`account_active` = 'false'
										OR (b.`access_starts` > ".$db->qstr(time())."
										OR (b.`access_expires` < ".$db->qstr(time())." AND b.`access_expires` != 0))) AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`
										$order_by
										LIMIT %d, %d";
				} elseif ($_GET["search-type"] == "new") {
					$query_counter	= "	SELECT count(*) as `total_rows` FROM (SELECT COUNT(a.`id`) AS `total_rows`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` IS NULL AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`) as t";
					$query_search	= "	SELECT a.*, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`account_active`, b.`access_starts`, b.`access_expires`, b.`last_login`, b.`role`, b.`group`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										WHERE b.`app_id` IS NULL AND
										(a.`number` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`username` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`email` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`firstname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%")."
										OR a.`lastname` LIKE ".$db->qstr("%%".str_replace("%", "", $search_query)."%%").")
										GROUP BY a.`id`
										$order_by
										LIMIT %d, %d";
				}

				$sidebar_html  = "<div style=\"margin: 2px 0px 10px 3px; font-size: 10px\">\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-active-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Active Member</div>\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-inactive-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Inactive Member</div>\n";
				$sidebar_html .= "	<div><img src=\"".ENTRADA_URL."/images/legend-non-member.gif\" width=\"14\" height=\"14\" alt=\"\" title=\"\" style=\"vertical-align: middle\" /> Non-Member</div>\n";
				$sidebar_html .= "</div>\n";

				new_sidebar_item("Members Legend", $sidebar_html, "member-legend", "open");
			}
		break;
	}

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */
	$result = ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query_counter) : $db->GetRow($query_counter));

	if ($result) {
		$total_rows	= $result["total_rows"];

		if ($total_rows <= $results_per_page) {
			$total_pages = 1;
		} elseif (($total_rows % $results_per_page) == 0) {
			$total_pages = (int) ($total_rows / $results_per_page);
		} else {
			$total_pages = (int) ($total_rows / $results_per_page) + 1;
		}
	} else {
		$total_rows = 0;
		$total_pages = 1;
	}

	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$page_current = (int) trim($_GET["pv"]);

		if (($page_current < 1) || ($page_current > $total_pages)) {
			$page_current = 1;
		}
	} else {
		$page_current = 1;
	}

	$page_previous = (($page_current > 1) ? ($page_current - 1) : false);
	$page_next = (($page_current < $total_pages) ? ($page_current + 1) : false);
	?>

	<h1><?php echo $MODULES[strtolower($MODULE)]["title"]; ?></h1>

	<div style="float: right">
        <a href="<?php echo ENTRADA_URL; ?>/admin/users?section=add" class="btn btn-primary">Add New User</a>
	</div>
	<div style="clear: both"></div>

	<style type="text/css">
		.dynamic-tab-pane-control .tab-page {
            min-height: 150px;
        }

        .departments-advanced-search {
            width: 75%;
            text-align: left;
            margin-left: 2%;
        }

        .departments-advanced-search .fa-chevron-down {
            padding-top: 4px;
        }
	</style>
	<div class="tab-pane" id="user-tabs">
		<div class="tab-page">
			<h3 class="tab">Newest Users</h3>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="browse-newest" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse Newest Users">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="btn" value="Show" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="number" class="form-required">Number of Newest Users:</label></td>
					<td>
						<select id="number" name="n" style="width: 100px">
						<option value="25"<?php echo (isset($browse_number) && $browse_number == 25 ? " selected=\"selected\"" : ""); ?>>25</option>
						<option value="50"<?php echo (isset($browse_number) && $browse_number == 50 ? " selected=\"selected\"" : ""); ?>>50</option>
						<option value="75"<?php echo (isset($browse_number) && $browse_number == 75 ? " selected=\"selected\"" : ""); ?>>75</option>
						<option value="100"<?php echo (isset($browse_number) && $browse_number == 100 ? " selected=\"selected\"" : ""); ?>>100</option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h3 class="tab">User Search</h3>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="search" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Search For User">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="btn btn-default" value="Search" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="q" class="form-required">User Search:</label></td>
					<td>
						<input type="text" id="q" name="q" value="<?php echo html_encode($search_query); ?>" style="width: 350px" />
						<div class="content-small" style="margin-top: 10px">
							<strong>Note:</strong> You can search for name, username, e-mail address or staff / student number.
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="vertical-align: top"><label for="search-type" class="form-required">Search Type:</label></td>
					<td>
						<select name="search-type">
							<option value="all" <?php echo ((isset($_GET["search-type"]) && $_GET["search-type"] == "all") || (!isset($_GET["search-type"])) ? "selected=\"true\" " : ""); ?>>All Users</option>
							<option value="active" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "active" ? "selected=\"true\" " : ""); ?>>Users With Active Membership</option>
							<option value="inactive" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "inactive" ? "selected=\"true\" " : ""); ?>>Users With Inactive Membership </option>
							<option value="new" <?php echo (isset($_GET["search-type"]) && $_GET["search-type"] == "new" ? "selected=\"true\" " : ""); ?>>Users With No Membership</option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h3 class="tab">Browse Groups</h3>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
			<input type="hidden" name="type" value="browse-group" />
			<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="Browse By Groups">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 25%" />
				<col style="width: 72%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
						<input type="submit" class="btn btn-default" value="Browse" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label for="group" class="form-required">Browse Group:</label></td>
					<td>
						<select id="group" name="g" style="width: 209px"></select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><label for="role" class="form-nrequired">Browse Role:</label></td>
					<td>
						<select id="role" name="r" style="width: 209px"></select>
					</td>
				</tr>
			</tbody>
			</table>
			</form>
		</div>
		<div class="tab-page">
			<h3 class="tab">Browse Departments</h3>

			<form id="browse-departments-form" class="form-inline" action="<?php echo ENTRADA_URL; ?>/admin/users" method="get">
				<input type="hidden" name="type" value="browse-dept" />

				<div>
					<label for="departments-advanced-search" class="form-required">Department:</label>

					<button id="departments-advanced-search" class="btn btn-search-filter departments-advanced-search">
                        <?php echo $translate->_("Browse Departments"); ?>
                        <i class="fa fa-chevron-down pull-right"></i>
                    </button>

                    <input type="submit" class="btn btn-default pull-right" name="browse_departments" value="Browse" />

                    <div id="advanced-search-departments-list"></div>
				</div>

                <input id="department-id" type="hidden" name="d">
			</form>
		</div>
	</div>

	<script type="text/javascript">
        setupAllTabs(true);

        jQuery(document).ready(function($) {
            var current_height = parseInt($(".tab-page").css("height"));
            var organisations = <?php echo json_encode(Models_Organisation::fetchOrganisationsWithDepartments()); ?>;
            var filters = {};

            for (var i = 0; i < organisations.length; i++) {
                var filter_name = organisations[i].organisation_title.split(" ").join("_");

                filters[filter_name] = {
                    data_source: "get-organisation-departments",
                    label: organisations[i].organisation_title,
                    mode: "radio",
                    set_button_text_to_selected_option: true,
                    api_params: {
                        organisation_id: organisations[i].organisation_id
                    }
                };
            }
            
            $("#departments-advanced-search").advancedSearch({
                api_url: "<?php echo ENTRADA_URL . "/admin/" . $MODULE . "?section=api-departments"; ?>",
                async: true,
                resource_url: ENTRADA_URL,
                filters: filters,
				filter_component_label: "Departments",
                selected_list_container: $("#advanced-search-departments-list"),
                parent_form: $("#browse-departments-form"),
                width: 487
            });

            $("#departments-advanced-search").on("click", function () {
				if ($(".entrada-search-widget .filter-menu").length) {
					var menu_height = parseInt($(".entrada-search-widget .filter-menu").css("height"));

					$(".tab-page").css("min-height", current_height + menu_height + "px");
				} else {
					var overlay_height = parseInt($(".entrada-search-widget .search-overlay").css("height"));

					$(".tab-page").css("min-height", current_height + overlay_height + "px");				}
            });

            $(".entrada-search-widget").on("click", ".filter-list-item", function () {
                var overlay_height = parseInt($(".entrada-search-widget .search-overlay").css("height"));

                $(".tab-page").css("min-height", current_height + overlay_height + "px");
            });

            $("#browse-departments-form").on("change", ".search-target-input-control", function () {
                $("#department-id").val($(this).val());

				$(".tab-page").css("min-height", current_height + "px");

				var current_filter = $(this).attr("data-filter");
                
                $("#advanced-search-departments-list").find(".search-target-control").not("." + current_filter + "_search_target_control").remove();
            });
        });
    </script>
	<?php
	if ($search_type && !$ERROR) {
		if ($total_pages > 1) {
			echo "<br />\n";
			echo "<div style=\"text-align: right\">\n";
			echo "	<form action=\"".ENTRADA_URL."/admin/".$MODULE."\" method=\"get\" id=\"pageSelector\" style=\"display: inline\">\n";
			echo "	<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
			if ($page_previous) {
				echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $page_previous))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$page_previous.".\" title=\"Back to page ".$page_previous.".\" style=\"vertical-align: middle\" /></a>\n";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "	</span>";
			echo "	<span style=\"vertical-align: middle\">\n";
			echo "	<select name=\"pv\" onchange=\"window.location = '".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => false))."&amp;pv='+this.options[this.selectedIndex].value;\"".(($total_pages <= 1) ? " disabled=\"disabled\"" : "").">\n";
			for($i = 1; $i <= $total_pages; $i++) {
				echo "<option value=\"".$i."\"".(($i == $page_current) ? " selected=\"selected\"" : "").">".(($i == $page_current) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
			}
			echo "	</select>\n";
			echo "	</span>\n";
			echo "	<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
			if ($page_current < $total_pages) {
				echo "<a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("pv" => $page_next))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$page_next.".\" title=\"Forward to page ".$page_next.".\" style=\"vertical-align: middle\" /></a>";
			} else {
				echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
			}
			echo "	</span>\n";
			echo "	</form>\n";
			echo "</div>\n";
		}
		/**
		 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
		 */
		$limit_parameter = (int) (($results_per_page * $page_current) - $results_per_page);
		$query	= sprintf($query_search, $limit_parameter, $results_per_page);

		$results	= $db->GetAll($query);

		if ($results) {
			?>
			<div style="margin-top: 10px; background-color: #FAFAFA; padding: 3px; border-bottom: none;font-size:11px;">
				<img src="<?php echo ENTRADA_URL; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
				<?php echo "Found ".$total_rows." user".(($total_rows != 1) ? "s" : "")." matching &quot;<strong>".($search_query_text)."</strong>&quot; in the user management system."; ?>
			</div>
			<form action="<?php echo ENTRADA_URL; ?>/admin/users?section=delete" method="post">
			<table class="tableList" cellspacing="0" summary="List of Users">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="general" />
				<col class="general" />
				<col class="date" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<?php if ($search_type == "browse-newest"): ?>
						<td class="title" style="font-size: 12px">Full Name</td>
						<td class="username" style="font-size: 12px">Username</td>
						<td class="role" style="font-size: 12px">Group &amp; Role</td>
						<td class="last-login" style="font-size: 12px">Last Login</td>
					<?php else: ?>
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "fullname") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>" style="font-size: 12px"><?php echo admin_order_link("fullname", "Full Name"); ?></td>
						<td class="username<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "username") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>" style="font-size: 12px"><?php echo admin_order_link("username", "Username"); ?></td>
						<td class="role<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "role") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>" style="font-size: 12px"><?php echo admin_order_link("role", "Group &amp; Role"); ?></td>
						<td class="last-login<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "login") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>" style="font-size: 12px"><?php echo admin_order_link("login", "Last Login"); ?></td>
					<?php endif; 
					if ($ENTRADA_ACL->amIAllowed("masquerade", "read")) {
						echo "<td style=\"font-size: 12px\">Login As</td>\n";
					}                    
                    ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="4" style="padding-top: 10px">
						<input type="submit" class="btn btn-danger" value="Delete Selected" />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach ($results as $result) {
					$can_login	= true;
					$url		= ENTRADA_URL."/admin/users/manage?id=".$result["id"];
                    $add_url	= ENTRADA_URL."/admin/users?section=add&amp;id=".$result["id"];

                    $permission_to_delete = false;

					if ($result["account_active"] == "false") {
						$can_login = false;
					}

					if (($access_starts = (int) $result["access_starts"]) && ($access_starts > time())) {
						$can_login = false;
					}
					if (($access_expires = (int) $result["access_expires"]) && ($access_expires < time())) {
						$can_login = false;
					}
					if ($result["account_active"]) {
                        $query = "SELECT * FROM `".AUTH_DATABASE."`.`user_access`
                                    WHERE `user_id` = ".$db->qstr($result["id"])."
                                    AND `app_id` = ".$db->qstr(AUTH_APP_ID)."
                                    AND `account_active` = 'true'";
                        $access_records = $db->getAll($query);
                        if ($access_records) {
                            $permission_to_delete = true;
                            foreach ($access_records as $access_record) {
                                if (!$ENTRADA_ACL->amIAllowed(new UserResource($result["id"], $access_record["organisation_id"]), 'delete')) {
                                    $permission_to_delete = false;
                                    break;
                                }
                            }
                        }
						echo "<tr class=\"user".((!$can_login) ? " na" : "")."\">\n";
						echo "	<td class=\"modified\">".($permission_to_delete ? "<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["id"]."\" />" : '')."</td>\n";
						echo "	<td class=\"title\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").html_encode($result["fullname"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"general\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").html_encode($result["username"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"general\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").ucwords($result["group"])." &rarr; ".ucwords($result["role"]).(($url) ? "</a>" : "")."</td>\n";
						echo "	<td class=\"date\">".(($url) ? "<a href=\"".$url."\" title=\"Edit Account: ".html_encode($result["fullname"])."\">" : "").(((int) $result["last_login"]) ? date(DEFAULT_DATE_FORMAT, (int) $result["last_login"]) : "Never Logged In").(($url) ? "</a>" : "")."</td>\n";
						if ($ENTRADA_ACL->amIAllowed("masquerade", "read")) {
							if ($result["id"] != $_SESSION["details"]["id"]) {
								echo "	<td><a href=\"".ENTRADA_URL."/admin/users?section=masquerade&id=".$result["id"]."\">Login as</a></td>\n";
							} else {
								echo "	<td>&nbsp;</td>\n";
							}
						}
						echo "</tr>\n";
					} else {
						echo "<tr class=\"user disabled\">\n";
						echo "	<td class=\"modified\">".($ENTRADA_ACL->amIAllowed(new UserResource($result["id"], $ENTRADA_USER->getActiveOrganisation()), 'create') ? "<a class=\"strong-green\" href=\"".$add_url."\" ><img style=\"border: none;\" src=\"".ENTRADA_URL."/images/btn_add.gif\" /></a>" : '')."</td>\n";
						echo "	<td class=\"title content-small\">".html_encode($result["fullname"])."</td>\n";
						echo "	<td class=\"general content-small\">".html_encode($result["username"])."</td>\n";
						echo "	<td class=\"general\">&nbsp;</td>\n";
						echo "	<td class=\"date\">&nbsp;</td>\n";
                        if ($ENTRADA_ACL->amIAllowed("masquerade", "read")) {
                            echo "	<td>&nbsp;</td>\n";
                        }
						echo "</tr>\n";
					}
				}
				?>
			</tbody>
			</table>
			</form>
			<?php
		} else {
			echo "<div class=\"display-notice\">\n";
			echo "	<h3>No Matching People</h3>\n";
			echo "	There are no people in the system found which contain matches to &quot;<strong>".($search_query_text)."</strong>&quot;.<br /><br />";
			echo "	You can add a new users by clicking the <strong>Add New User</strong> link.\n";
			echo "</div>\n";
		}
	}
}