<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

class Departments extends collection {
	
	public static function getByUser($user_id) {
		global $db;
 		$query			= "SELECT * FROM FROM `".AUTH_DATABASE."`.`user_departments` join `".AUTH_DATABASE."`.`departments` on `dep_id` = `department_id WHERE `user_id` = ?";
		$results	= $db->GetAll($query, array($user_id));
		$depts = array();
		if ($results) { 
			foreach ($results as $result) {
				$depts[] = Department::fromArray($result);
			}
		}
		return new self($depts);
	} 
}