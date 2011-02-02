#!/usr/local/zend/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * Run this script to copy a community (specified by community id) to a new
 * community.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("display_errors", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if ((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only.";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

require_once("classes/adodb/adodb.inc.php");
require_once("config.inc.php");
require_once("dbconnection.inc.php");
require_once("auth_dbconnection.inc.php");
require_once("functions.inc.php");

$ERROR = false;

output_notice("This script is used to copy an existing community.");
print "\nStep 1 - Please enter the Community ID of the Community to copy: ";
fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN

print "\nPlease enter the username that the new community will belong to: ";
fscanf(STDIN, "%s\n", $user_name);

$query = "	SELECT *
			FROM `user_data`
			WHERE `username` = '" . $user_name . "'";

$user_data = $auth_db->GetRow($query);

while (!$user_data) {
	print "\nDatabase error: " . $db->ErrorMsg();
	print "\nPlease ensure you enter a valid username: ";
	fscanf(STDIN, "%d\n", $user_name);
	$query = "	SELECT *
				FROM `medtech_auth`.`user_data`
				WHERE `username` = '" . $user_name . "'";

	$user_data = $db->GetRow($query);
}

output_notice("The proxy id for the entered username is: " . $user_data["id"]);

$site_names = array();

$COMMUNITY_ID = intval($COMMUNITY_ID);
$query = "	SELECT *
			FROM `communities`
			WHERE `community_id` = " . $db->qstr($COMMUNITY_ID);
$community = $db->GetRow($query);
while (!$community) {
	print "\nPlease ensure you enter a valid Community ID: " . $db->ErrorMsg();
	fscanf(STDIN, "%d\n", $COMMUNITY_ID); // reads number from STDIN
	$community = $db->GetRow("SELECT * FROM `communities` WHERE `community_id` = " . $db->qstr($COMMUNITY_ID));
}

$site_names = array(
						"John's Community Site");

$site_name_prefix = "pgme";
$template = "pgcourse";
$theme = "default";

output_notice("Step 2: The pages from the given Community ID are inserted as pages for the new Community Site.");

foreach ($site_names as $s_name) {

	output_notice("Current site name: " . $s_name);

	//format the program name for use as an URL and community short name
	$s_name_url = clean_input($s_name, array("page_url", "lowercase"));

	output_notice("Site URL formatted name: " . $s_name_url);

	$public_view = 0;
	$troll_view = 0;
	$time = time();

	$COMM_INSERT = array();
//These are top level communities, i.e. they are not child communities.
	$COMM_INSERT["community_parent"] = 0;
//e.g. PGME Category = 22
	$COMM_INSERT["category_id"] = $community["category_id"];
	$COMM_INSERT["community_url"] = "/" . $site_name_prefix ."_" . $s_name_url;
	$COMM_INSERT["community_template"] = $template;
	$COMM_INSERT["community_theme"] = $theme;
	$COMM_INSERT["community_shortname"] = $site_name_prefix . "_" . $s_name_url;
	$COMM_INSERT["community_title"] = $s_name;
	$COMM_INSERT["community_description"] = "This is the " . $s_name . " website and is under construction at this time.";
	$COMM_INSERT["community_keyword"] = $s_name;
	$COMM_INSERT["community_email"] = "";
	$COMM_INSERT["community_website"] = "";
	$COMM_INSERT["community_protected"] = 1;
	$COMM_INSERT["community_registration"] = 4;
	$COMM_INSERT["community_members"] = "";
	$COMM_INSERT["community_active"] = 1;
	$COMM_INSERT["community_opened"] = $time;
	$COMM_INSERT["community_notifications"] = 0;
	$COMM_INSERT["sub_communities"] = 0;
	$COMM_INSERT["storage_usage"] = 3923656;
	$COMM_INSERT["storage_max"] = 104857600;
	$COMM_INSERT["updated_date"] = $time;
	$COMM_INSERT["updated_by"] = $user_data["id"];

	if (!(($db->AutoExecute("communities", $COMM_INSERT, "INSERT")) && ($new_community_id = $db->Insert_Id()))) {
		output_error("There was a problem while inserting the new community. Database said: " . $db->ErrorMsg());
		exit;
	}

	output_notice("\n\nThe new community ID is: " . $new_community_id);

	/*
	 * Activate each community module (all 7 that are currently available).
	 * module_id - Module Name - page_type
	 * 1 - Announcements - announcements
	 * 2 - Discussions - discussions
	 * 3 - Document Sharing - shares
	 * 4 - Events - events
	 * 5 - Galleries - galleries
	 * 6 - Polling - polls
	 * 7 - Quizzes - quizzes...need to confirm as many Quizzes in the database are of page_type = default
	 *
	 */
	$community_modules = array(1, 2, 3, 4, 5, 6, 7);
	foreach ($community_modules as $module_id) {
		if (!communities_module_activate($new_community_id, $module_id)) {
			output_error("Unable to active module [" . (int) $module_id . "] for new community id [" . (int) $new_community_id . "]. Database said: " . $db->ErrorMsg());
		}
	}

	output_success("Activated all community modules.");

//Add _this_ user as a member
	if (!$db->AutoExecute("community_members", array("community_id" => $new_community_id, "proxy_id" => 4264, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
		output_error("Failed to insert you as a member of the new community. Database said: " . $db->ErrorMsg());
	}

//Set the Community Page options
	$query = "	INSERT INTO `community_page_options` (`community_id`, `option_title`)
			VALUES (" . $db->qstr($new_community_id) . ", 'show_announcements')";
	if (!$db->Execute($query)) {
		output_error("Could not add 'show_announcement` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
	}
	$query = "	INSERT INTO `community_page_options` (`community_id`, `option_title`)
			VALUES (" . $db->qstr($new_community_id) . ", 'show_events')";
	if (!$db->Execute($query)) {
		output_error("Could not add 'show_event` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
	}
	$query = "	INSERT INTO `community_page_options` (`community_id`, `option_title`) VALUES
			(" . $db->qstr($new_community_id) . ", 'show_history')";
	if (!$db->Execute($query)) {
		output_error("Could not add 'show_history` option for community [" . $new_community_id . "]. Database said: " . $db->ErrorMsg());
	}

	output_success("Inserted all community page options.");

//fetch all of the top level parent community pages from the community to copy
	$query = "	SELECT *
			FROM `community_pages`
			WHERE `community_id` = " . $COMMUNITY_ID . "
			AND `parent_id` = '0'
			ORDER BY cpage_id ASC";
	$community_pages_arr = $db->GetAll($query);

//insert each community page with the new community id.
	if ($community_pages_arr) {
		foreach ($community_pages_arr as $page) {
			//Insert new page if it is active
			$new_parent_id = insert_community_page($db, $page, $new_community_id, $user_data["id"]);
			//add all of the child pages for this page.
			create_child_pages($db, $page["cpage_id"], $COMMUNITY_ID, $new_parent_id, $new_community_id, $user_data["id"]);
		}
	}

//Validate that all pages were copied by comparing the given community ID to the new community ID.
	$query = "	SELECT *
			FROM `community_pages`
			WHERE `community_id` = " . $COMMUNITY_ID . "
			AND page_active = 1";
	$old_community_pages_arr = $db->GetAll($query);

	$query = "	SELECT *
			FROM `community_pages`
			WHERE `community_id` = " . $new_community_id;
	$new_community_pages_arr = $db->GetAll($query);

	$old_num_of_pages = sizeof($old_community_pages_arr);
	$new_num_of_pages = sizeof($new_community_pages_arr);
	if ($old_num_of_pages != $new_num_of_pages) {
		output_notice("The number of pages copied did not match (old = " . $old_num_of_pages . " new = " . $new_num_of_pages . ")");
	} else {
		output_success("Succesfully copied Community ID: [" . $COMMUNITY_ID . "] to new Community ID: [" . $new_community_id . "].");
	}
	print "\n\n";
}

/**
 * This function recursively creates the children pages for the given $cpage_id.
 *
 * @param ADONewConnection $db The database connection
 * @param integer $cpage_id
 * @param integer $COMMUNITY_ID The community ID of the community to copy.
 * @param integer $new_parent_id
 * @param integer $new_community_id
 * @param integer the proxy id for the user who owns the community.
 */
function create_child_pages($db, $cpage_id, $COMMUNITY_ID, $new_parent_id, $new_community_id, $proxy_id) {
	$query = "	SELECT *
				FROM `community_pages`
				WHERE `community_id` = " . $COMMUNITY_ID . "
				AND `parent_id` = " . $db->qstr($cpage_id) . "
				ORDER BY cpage_id ASC";

	$community_pages_arr = $db->GetAll($query);

	if ($community_pages_arr) {
		//insert every child page.
		foreach ($community_pages_arr as $page) {
			$page["parent_id"] = $new_parent_id;
			$new_cpage_id = insert_community_page($db, $page, $new_community_id, $proxy_id);
			create_child_pages($db, $page["cpage_id"], $COMMUNITY_ID, $new_cpage_id, $new_community_id, $proxy_id);
		}
	}
}

/**
 * This function will insert a single page into the community_pages table if
 * it is an active page.
 * @param ADONewConnection $db The database connection
 * @param array $page An array containing a single row from the community_pages table.
 * @param integer $new_community_id The id of the new community.
 * @param integer $proxy_id The integer representation of the user inserting the page
 * 							(found in the medtech_auth.user_data table.
 * @return the cpage_id of the inserted page.
 */
function insert_community_page($db, $page, $new_community_id, $proxy_id) {
	if ($page["page_active"] != 0) {
		$query = "	INSERT INTO `community_pages`
						(`community_id`,
						`parent_id`,
						`page_order`,
						`page_type`,
						`menu_title`,
						`page_title`,
						`page_url`,
						`page_content`,
						`page_active`,
						`page_visible`,
						`allow_member_view`,
						`allow_troll_view`,
						`allow_public_view`,
						`updated_date`,
						`updated_by`)
						VALUES(
						" . $db->qstr($new_community_id) . ",
						" . $db->qstr($page["parent_id"]) . ",
						" . $db->qstr($page["page_order"]) . ",
						" . $db->qstr($page["page_type"]) . ",
						" . $db->qstr($page["menu_title"]) . ",
						" . $db->qstr($page["page_title"]) . ",
						" . $db->qstr($page["page_url"]) . ",
						" . $db->qstr($page["page_content"]) . ",
						" . $db->qstr($page["page_active"]) . ",
						" . $db->qstr($page["page_visible"]) . ",
						" . $db->qstr($page["allow_member_view"]) . ",
						" . $db->qstr($page["allow_troll_view"]) . ",
						" . $db->qstr($page["allow_public_view"]) . ",
						" . $db->qstr(time()) . ",
						" . $db->qstr($proxy_id) . ")";
		if (!$db->Execute($query)) {
			output_error("There was a problem while inserting page " . $page["cpage_id"] . " into the new community where Community ID is (" . $new_community_id . "). Database said: " . $db->ErrorMsg());
		}
		$new_cpage_id = $db->Insert_Id();
		return $new_cpage_id;
	}
}
?>