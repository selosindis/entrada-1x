<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves profile information for users or a specific user
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../core",
    dirname(__FILE__) . "/../../core/includes",
    dirname(__FILE__) . "/../../core/library",
    dirname(__FILE__) . "/../../core/library/vendor",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

$valid_app = false;

if (isset($_GET["app"]) && $tmp_input = clean_input($_GET["app"],array("trim","notags"))) {
	$APP_ID = $tmp_input;
	$query = "	SELECT * FROM `".AUTH_DATABASE."`.`registered_apps` WHERE `script_id` = ".$db->qstr($APP_ID);
	$valid_app = $db->GetRow($query);
}

if (!$valid_app) {
	application_log("error","API accessed with invalid App ID");
	exit;		
}


$PROXY_ID = (int)isset($_GET["uid"])?$_GET["uid"]:0;

$PROFILE = (int)isset($_GET["dept"])?$_GET["dept"]:0;

if(isset($_GET["uids"]) && $tmp_input = clean_input($_GET["uids"],array("trim","notags"))){
	$PROXY_IDS = $tmp_input;	
}else{
	$PROXY_IDS = false;
}

if (!$PROXY_ID && !$PROFILE) {
	error_log("Failed at request checking step");
	application_log("error","API accessed with no proxy ID and no profile ID provided");
	exit;
}



if ($PROXY_ID) {
	$query = "SELECT a.`id`, a.`firstname`, a.`lastname` FROM `".AUTH_DATABASE."`.`user_data` a WHERE a.`id` = ".$db->qstr($PROXY_ID);
	$user = $db->GetRow($query);
	if ($user){
		$users[] = $user;
	} else {
		$users = false;
	}
} elseif($PROXY_IDS) {
	$query = "SELECT a.`id`, a.`firstname`, a.`lastname` FROM `".AUTH_DATABASE."`.`user_data` a WHERE a.`id` IN(".$PROXY_IDS.")";
	$users = $db->GetAll($query);	
} else {
	$query = "	SELECT a.`id`, a.`firstname`, a.`lastname` FROM `".AUTH_DATABASE."`.`user_data` a 
				JOIN `".AUTH_DATABASE."`.`user_departments` b
				ON a.`id` = b.`user_id` 
				WHERE b.`dep_id` = ".$db->qstr($PROFILE)."
				ORDER BY a.`lastname` ASC";
	$users = $db->GetAll($query);	
}

if(!$users) {
	echo json_encode(array('error'=>"No users matched request."));
	exit;
}

if ($PROFILE) {
	foreach($users as $key=>$user) {
		$query = "	SELECT a.*, COALESCE(b.`value`,'') AS `value` ,b.`proxy_id` FROM `profile_custom_fields` a
					LEFT JOIN `profile_custom_responses` b
					ON a.`id` = b.`field_id`
					AND b.`proxy_id` = ".$db->qstr($user["id"])."
					WHERE a.`department_id` = ".$db->qstr($PROFILE)."
					AND b.`proxy_id` = ".$db->qstr($user["id"])."
					AND a.`active` = '1'";
		$fields = $db->GetAll($query);
		$users[$key]["fields"]['firstname'] = array('title'=>'Firstname', 'value'=>$user["firstname"]);
		$users[$key]["fields"]['lastname'] = array('title'=>'Lastname', 'value'=>$user["lastname"]);
		if ($fields) {
			foreach($fields as $field){
				$users[$key]["fields"][$field["name"]] = array('title'=>$field["title"], 'value'=>$field["value"],'type'=>$field["type"]);
			}
		}
	}
}

if ($PROXY_ID) {
	echo json_encode($users[0]);
} else {
	echo json_encode($users);
}
exit;