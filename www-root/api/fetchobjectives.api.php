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
	$course_id = (int)(isset($_GET["course_id"])?$_GET["course_id"]:false);
	$event_id = (int)(isset($_GET["event_id"])?$_GET["event_id"]:false);
	$select = "a.*";

	if ($course_id){
		$select .=", COALESCE(b.`cobjective_id`,0) AS `mapped`";
	}elseif ($event_id) {
		$select .=", COALESCE(b.`eobjective_id`,0) AS `mapped`";
	}

	$qu_arr = array("SELECT ".$select." FROM `global_lu_objectives` a");
	
	if ($course_id){
		$qu_arr[1] = "LEFT JOIN `course_objectives` b
					ON a.`objective_id` = b.`objective_id`";
		$qu_arr[3] = "AND b.`course_id` = ".$db->qstr($course_id);					
	} elseif ($event_id) {
		$qu_arr[1] = "LEFT JOIN `event_objectives` b
					ON a.`objective_id` = b.`objective_id`";
		$qu_arr[3] = "AND b.`event_id` = ".$db->qstr($event_id);									
	}	
	
	$qu_arr[2] = "WHERE a.`objective_parent` = ".$db->qstr($id)." 
				AND a.`objective_active` = '1'";
	$qu_arr[4] = "ORDER BY a.`objective_order`";
	$query = implode(" ",$qu_arr);
	error_log($query);
	$objectives = $db->GetAll($query);
	if ($objectives) {
		$obj_array = array();
		foreach($objectives as $objective){
			$fields = array(	'objective_id'=>$objective["objective_id"],
									'objective_code'=>$objective["objective_code"],
									'objective_name'=>$objective["objective_name"],
									'objective_description'=>$objective["objective_description"]
								);
			if ($course_id || $event_id){
				$fields["mapped"] = $objective["mapped"];
				if ($course_id) {
					$fields["has_child"] = course_objective_has_child_mapped($objective["objective_id"],$course_id);
				}
				if ($event_id) {
					$fields["has_child"] = event_objective_has_child_mapped($objective["objective_id"],$event_id);
				}				
			}			
			$obj_array[] = $fields;
		}
		echo json_encode($obj_array);
	} else {
		echo json_encode(array('error'=>'No child objectives found for the selected objective.'));
	}
	
	exit;
}