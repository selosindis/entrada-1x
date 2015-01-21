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

$PROFILE = (int)isset($_GET["dept"])?$_GET["dept"]:0;
$query = "SELECT `organisation_id` FROM `".AUTH_DATABASE."`.`departments` WHERE `department_id` = ".$db->qstr($PROFILE);
$ORG_ID = $db->GetOne($query);

if (!$ORG_ID) {
	application_log("error","Department is not associated with any organisation");
	exit;			
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	//GET requests return existing set of fields for QWeb or other CMS platforms to display and edit
		$query = "	SELECT `id`,`title`,`name`,`type` FROM `profile_custom_fields` 
					WHERE `department_id` = ".$db->qstr($PROFILE)."
					AND `active` = '1'";
		$fields = $db->GetAll($query);
		echo json_encode($fields);
} elseif($_SERVER['REQUEST_METHOD'] == "POST"){
	//POST requests update a list of fields for a profile data passed from QWeb or other CMS platforms for a given department
		$errors = array();
		$types = array('TEXTAREA','TEXTINPUT','CHECKBOX','RICHTEXT','TWITTER','LINK');
		if (isset($_POST["new"]) && is_array($_POST["new"])){
			foreach($_POST["new"] as $field){
				$field_data["department_id"] = $PROFILE;
				$field_data["organisation_id"] = $ORG_ID;
				$field_data["title"] = clean_input($field["title"],array("trim","notags"));
				$field_data["type"] = clean_input($field["type"],array("trim","notags"));
				$field_data["name"] = clean_input($field["name"],array("trim","notags"));
				$field_data["active"] = 1;		
				error_log('Inserting field');
				error_log(print_r($field_data,true));
				if(!$field_data['title'] || !$field_data['name'] || !in_array($field_data['type'], $types)){
					$errors[] = "Invalid data provided for ".($field_data["title"] ? $field_data["title"] : ($field_data['name'] ? $field_data['name'] : 'a field.'));
					continue;
				}

				$res = $db->AutoExecute("profile_custom_fields",$field_data,"INSERT");	
			}
		}
		
		if (isset($_POST["delete"]) && is_array($_POST["delete"])) {
			$remfields = array();
			foreach($_POST["delete"] as $field){
				$remfields[] = (int)$field;
			}
			$where = "`id` IN (".implode(",",$remfields).")";			
			$db->AutoExecute("profile_custom_fields",array('active'=>0),"UPDATE",$where);
		}

		if ($errors) {
			echo json_encode(array("error"=>true,"errors"=>$errors));
		} else {
			echo json_encode(array("success"=>true));
		}
}

exit;