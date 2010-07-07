#!/usr/bin/php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.	If not, see <http://www.gnu.org/licenses/>.
 *
 * Tools: Planetary Collision. Used for merging two previously independent 
 * Entrada installations into one big clusterfuck of a database. 
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors", true);
require_once("classes/adodb/adodb.inc.php");
$ADODB_QUOTE_FIELDNAMES = true;

$COLLIDE_ID			= "collisionconstant";
$DBUSER 			= "hbrundage";
$PASSWORD 			= "balls";
$HOST 				= "developer.qmed.ca";
$DATABASE_TYPE		= "mysql";

$PRIMARY_DB_CONF		= array("host" => $HOST, "database_prefix" => "shared_test_", "user" => $DBUSER, "pass"=>$PASSWORD);
$SECONDARY_DB_CONF		= array("host" => $HOST, "database_prefix" => "rehab_test_"	, "user" => $DBUSER, "pass"=>$PASSWORD);
$DESTINATION_DB_CONF	= array("host" => $HOST, "database_prefix" => "destination_", "user" => $DBUSER, "pass"=>$PASSWORD);

$DB_CONNECTIONS = array();

$MERGE = array("users" => false, "auth" => false, "clerkship" => false, "communities" => true);

function get_db_connection($which, $database_name) {
	global $DB_CONNECTIONS, $DATABASE_TYPE;
	$key = $which.$database_name;
	if(isset($DB_CONNECTIONS[$key])) {
		return $DB_CONNECTIONS[$key];
	} else {
		$conf_name = strtoupper($which) . "_DB_CONF";
		global $$conf_name;
		$pref = $$conf_name;
		// Set up new connection
		$db = &NewADOConnection($DATABASE_TYPE);
		$db->NConnect($pref["host"], $pref["user"], $pref["pass"], $pref["database_prefix"].$database_name);
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$DB_CONNECTIONS[$key] = $db;
		return $db;
	}
}

function transform_values($fields, $transforms) {
	global $PROXY_ID_MAP, $COLLIDE_ID;
	foreach($fields as $name => &$field) {
		if(isset($transforms[$name])) {
			$t = $transforms[$name];
			if($t === false) {
				unset($fields[$name]);
			}
			if(is_numeric($field) && $field != 0) {
				$field = $field+$t;
			}
			if($t == $COLLIDE_ID) {
				$field = get_primary_proxy_id($field);
			}
		}
	}
	return $fields;
}

function process_transforms($transforms) {
	if(isset($transforms["secondary"])) {
		return $transforms;
	} else {
		return array("secondary" => $transforms, "primary" => array());
	}
}

function process_skip_ids($skip_ids) {
	if(empty($skip_ids)) {
		return array("primary" => array(), "secondary" => array(), "field_name" => "id");
	}
	if(isset($skip_ids["primary"])) {
		return $skip_ids;
	} else {
		return array("primary" => array(), "secondary" => $skip_ids, "field_name" => (isset($skip_ids["field_name"]) ? $skip_ids["field_name"] : "id"));
	}
}

function process_record($rs, $field_name, $skip_ids) {
	if(isset($rs->fields[$field_name])) {
		return ! in_array($rs->fields[$field_name], $skip_ids);
	} else {
		return true;
	}
}

// Move table:
function move_table($source, $which_db, $table, $transforms = array(), $skip_id_fieldname = "", $skip_ids = array(), $only = array()) {	
	// Get DBS from global pool
	$source_db = get_db_connection($source, $which_db);
 	$destination_db = get_db_connection("destination", $which_db);

	// Find all the records to be moved
	$sql = "SELECT * FROM $table;";
	$rs = $source_db->Execute($sql);

	echo "Moving $source records in $table from ".$source_db->database." to ".$destination_db->database.".\n";

	$i = 0;
	while (!$rs->EOF) {
		//Decide if this record is to be processed 
		if(process_record($rs, $skip_id_fieldname, $skip_ids)) {
			$sql = $source_db->GetInsertSQL($rs, transform_values($rs->fields, $transforms));
			$success = $destination_db->Execute($sql);
			if(!$success) {
				echo "---------Error!! \n";
				var_dump($sql);
				echo "DB Said: " . $destination_db->ErrorMsg() . "\n";
				break;
			}
			//Count up
			$i++;
			if($i % 500 == 0) {
				echo $i." ";
			}
		}
		$rs->MoveNext();	
	}
	echo "\n $i records moved. \n";
	$rs->Close();
}

function merge_tables($which_db, $table, $transforms = array(), $skip_ids = array(), $only = array()) {	
	$transforms = process_transforms($transforms);
	$skip_ids = process_skip_ids($skip_ids);
	foreach(array("primary", "secondary") as $source) {
		move_table($source, $which_db, $table, $transforms[$source], $skip_ids["field_name"], $skip_ids[$source], $only);
	}	
}

function copy_all_tables($source, $which_db) {
	foreach(get_all_tables($source, $which_db) as $table) {
		move_table($source, $which_db, $table);
	}
}

function truncate_all_tables($set, $which_db) {
	$db = get_db_connection($set, $which_db);
	foreach(get_all_tables($set, $which_db) as $table) {
		$db->Execute("TRUNCATE TABLE `".$table."`");
	}
}

function get_all_tables($source, $which_db) {
	$conf_name = strtoupper($source) . "_DB_CONF";
	global $$conf_name;
	$pref = $$conf_name;
	$source_db_name = $pref["database_prefix"].$which_db;

	$source_db = get_db_connection($source, $which_db);
	$tables_raw = $source_db->GetAll("SHOW TABLES");
	$tables = array();
	foreach($tables_raw as $array) {
		$tables[] = $array["Tables_in_".$source_db_name];
	}
	return $tables;
}

function build_proxy_id_map() {
	// $PROXY_ID_MAP[secondary] = primary
	global $PRIMARY_DB_CONF, $SECONDARY_DB_CONF;
	$primary_db = $PRIMARY_DB_CONF["database_prefix"]."auth";
	$secondary_db = $SECONDARY_DB_CONF["database_prefix"]."auth";
	$db = get_db_connection("destination", "auth");
		
	$sql = "SELECT a . id as primary_id , b . id as secondary_id 
			FROM $primary_db.user_data AS a
			LEFT JOIN $secondary_db.user_data AS b ON b.username = a.username
			WHERE b.username IS NOT NULL";
			
	$ids = $db->GetAll($sql);
	
	$map = array();
	foreach($ids as $key => $fields) {
		$map[$fields["secondary_id"]] = $fields["primary_id"];
	}
	return $map;
}

function get_primary_proxy_id($secondary_id) {
	global $PROXY_ID_DELTA, $PROXY_ID_MAP;
	if(isset($PROXY_ID_MAP[$secondary_id])) {
		return $PROXY_ID_MAP[$secondary_id];
	} else {
		return $secondary_id + $PROXY_ID_DELTA;
	}
}

function get_max($set, $which_db, $table, $field) {
	$data = get_db_connection($set, $which_db)->GetRow("SELECT MAX($field) FROM $table");
	return $data["MAX($field)"];
}

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 


// General Structure:
// 1. Find out user id offset by getting destination db auto_increment
// 2. Merge user data tables by pulling all users out of secondary and into primary. Update user ID by adding $X amount.
// 3. Merge courses, events and other stuff, ensuring all proxy ids have $X added to them.

// 1. Proxy ID offset

$data = get_db_connection("primary", "auth")->GetRow("SELECT MAX(id) FROM user_data");
$PROXY_ID_DELTA = $data["MAX(id)"];

foreach(array("auth", "clerkship", "entrada") as $which) {
	echo "Truncating tables in $which \n";
	truncate_all_tables("destination", $which);
}

// 2. Merge user data tables
if($MERGE["users"] == true) {
	echo " =============   Merging users   =========== \n";
	$PROXY_ID_MAP = build_proxy_id_map($PROXY_ID_DELTA);
	merge_tables("auth", "user_data", array("id" => $COLLIDE_ID), array_keys($PROXY_ID_MAP));
	merge_tables("auth", "user_access", array("id" => false, "user_id" => $COLLIDE_ID));
}

if($MERGE["auth"] == true) {
	echo " =============    Merging auth   ============ \n";
	// Merge departments
	$data = get_db_connection("primary", "auth")->GetRow("SELECT MAX(department_id) FROM departments");
	$DEPARTMENT_ID_DELTA = $data["MAX(department_id)"];

	merge_tables("auth", "departments", array("department_id" => $DEPARTMENT_ID_DELTA));
	merge_tables("auth", "user_departments", array("udep_id" => false, "user_id" => $COLLIDE_ID, "dep_id" => $DEPARTMENT_ID_DELTA));

	merge_tables("auth", "organisations");
	merge_tables("auth", "registered_apps");
	merge_tables("auth", "statistics");			// No statistic records in Rehab
	merge_tables("auth", "user_incidents");			// No incident records in Rehab

	// Merge user photos
	merge_tables("auth", "user_photos", array("photo_id" => false, "proxy_id" => $COLLIDE_ID));

	//Merge user preferences
	merge_tables("auth", "user_preferences", array("preference_id" => false, "proxy_id" => $COLLIDE_ID));
}

if($MERGE["clerkship"] == true) {
	echo " ==========   Merging clerkship   ========== \n";
	copy_all_tables("primary", "clerkship"); // Rehab has no clerkship tables at all
}


if($MERGE["communities"] == true) {
	echo " ============   Merging entrada   ========== \n";
	# 	Constant to assign delta to	,						table,								field,		transform all, basic merge
	$schema = array(
		"COMMUNITY_ID_DELTA"						=> array("communities", 					"community_id", 	true, false),
		"COMMUNITY_PAGE_ID_DELTA" 					=> array("community_pages", 				"cpage_id", 		true, false),
		"COMMUNITY_ANNOUNCEMENT_ID_DELTA" 			=> array("community_announcements", 		"cannouncement_id", true, true),
		"COMMUNITY_DISCUSSION_ID_DELTA"				=> array("community_discussions", 			"cdiscussion_id", 	true, true),
		"COMMUNITY_DISCUSSION_TOPIC_ID_DELTA" 		=> array("community_discussion_topics", 	"cdtopic_id", 		true, true),
		"COMMUNITY_EVENT_ID_DELTA" 					=> array("community_events", 				"cevent_id", 		true, true),
		"COMMUNITY_GALLERY_ID_DELTA" 				=> array("community_galleries", 			"cgallery_id", 		true, true),
		"COMMUNITY_GALLERY_PHOTO_ID_DELTA" 			=> array("community_gallery_photos", 		"cgphoto_id", 		true, true),
		"COMMUNITY_GALLERY_COMMENT_ID_DELTA" 		=> array("community_gallery_comments", 		"cgcomment_id", 	true, true),
		"COMMUNITY_HISTORY_ID_DELTA" 				=> array("community_history",	 			"chistory_id", 		true, true),
		"COMMUNITY_MAILING_LIST_MEMBER_ID_DELTA"	=> array("community_mailing_list_members", 	"cmlmember_id", 	true, true),
		"COMMUNITY_MAILING_LIST_ID_DELTA"	 		=> array("community_mailing_lists",			"cmlist_id", 		true, true),
		"COMMUNITY_MEMBER_ID_DELTA" 				=> array("community_members", 				"cmember_id", 		true, true),
		"COMMUNITY_MODULE_ID_DELTA"					=> array("community_modules", 				"cmodule_id", 		true, false),
		"COMMUNITY_NOTIFICATION_ID_DELTA"			=> array("community_notifications", 		"cnotification_id", true, true),
		"COMMUNITY_MEMBER_NOTIFICATION_ID_DELTA"	=> array("community_notify_members", 		"cnmember_id", 		true, true),
		"COMMUNITY_PAGE_OPTION_DELTA"				=> array("community_page_options", 			"cpoption_id", 		true, true),
		"COMMUNITY_POLL_ID_DELTA"					=> array("community_polls", 				"cpolls_id", 		true, true),
		"COMMUNITY_POLL_ACCESS_ID_DELTA"			=> array("community_polls_access", 			"cpaccess_id",		true, true),
		"COMMUNITY_POLL_QUESTION_ID_DELTA"			=> array("community_polls_questions", 		"cpquestion_id", 	true, true),
		"COMMUNITY_POLL_RESPONSE_ID_DELTA"			=> array("community_polls_responses", 		"cpresponses_id",	true, true),
		"COMMUNITY_POLL_RESULT_ID_DELTA"			=> array("community_polls_results", 		"cpresults_id", 	true, true),
		"COMMUNITY_SHARE_ID_DELTA"					=> array("community_shares", 				"cshare_id", 		true, true),
		"COMMUNITY_SHARE_COMMENT_ID_DELTA"			=> array("community_share_comments",		"cscomment_id", 	true, true),
		"COMMUNITY_SHARE_FILE_ID_DELTA"				=> array("community_share_files", 			"csfile_id", 		true, true),
		"COMMUNITY_SHARE_FILE_VERSION_ID_DELTA"		=> array("community_share_file_versions",	"csfversion_id",	true, true),
		"COMMUNITY_SHARE_FILE_VERSION_ID_DELTA"		=> array("community_share_file_versions",	"csfversion_id",	true, true),
	);
	
	$community_transforms = array("proxy_id" => $COLLIDE_ID, "updated_by" => $COLLIDE_ID);
	$to_transform = array();
	foreach($schema as $var => $details) {
		$$var = get_max("primary", "entrada", $details[0], $details[1]);
		
		if(!isset($$var)) {
			echo "Error getting delta.\n";
			var_dump($var);
			var_dump($$var);
			var_dump($details);
		}
		
		if($details[2] == true) {
			$community_transforms[$details[1]] = $$var;
		}
		
		if($details[3] == true) {
			$to_transform[] = $details[0];
		}
	}
	
	foreach($to_transform as $table) {
		merge_tables("entrada", $table, $community_transforms);
	}
	
	//Parents in primary remain in tact, rehab has no parents. Categories are consistent across installations, so pluck the complete set from primary.
	merge_tables("entrada", "communities", array("community_id" => $COMMUNITY_ID_DELTA, "updated_by" => $COLLIDE_ID));
	merge_tables("entrada", "community_pages", array_merge($community_transforms, array("parent_id" => $COMMUNITY_PAGE_ID_DELTA)));
	
	move_table("primary", "entrada", "communities_categories");
	move_table("primary", "entrada", "communities_modules");
	
	move_table("primary", "entrada", "community_courses"); // Rehab has no community courses
		
	// Needs Polymorphic proc
	// merge_tables("entrada", "community_history", array_merge());
		
	// Needs Polymorphic proc	
	// merge_tables("entrada", "community_notifications", array_merge($community_transforms, array("author_id" => $COLLIDE_ID)));
	// merge_tables("entrada", "community_notify_members", array_merge($community_transforms);

	
	// Skip community permissions?
		
}

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 
echo "Finished in $totaltime seconds.\n"; 
