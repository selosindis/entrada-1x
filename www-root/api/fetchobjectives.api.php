<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * Module:	Courses
 * Area:	Admin
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @version 0.8.3
 * @copyright Copyright 2009 Queen's University, MEdTech Unit
 *
 * $Id: add.inc.php 505 2009-07-09 19:15:57Z jellis $
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} else {
	
	/**
	 * Clears all open buffers so we can return a simple REST response.
	 */
	ob_clear_open_buffers();
	
	$id = (int)$_GET["objective_id"];

	$query = "	SELECT * FROM `global_lu_objectives` 
				WHERE `objective_parent` = ".$db->qstr($id)." 
				AND `objective_active` = '1' 
				ORDER BY `objective_order`";
	$objectives = $db->GetAll($query);
	if ($objectives) {
		$obj_array = array();
		foreach($objectives as $objective){
			$obj_array[] = array(	'objective_id'=>$objective["objective_id"],
									'objective_code'=>$objective["objective_code"],
									'objective_name'=>$objective["objective_name"],
									'objective_description'=>$objective["objective_description"]
								);
		}
		echo json_encode($obj_array);
	} else {
		echo json_encode(array('error'=>'No child objectives found for the selected objective.'));
	}
	
	exit;
}