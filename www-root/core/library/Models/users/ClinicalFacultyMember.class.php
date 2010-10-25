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
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version 
 */

/**
 * Simple User class with basic information
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */

require_once("User.class.php");

class ClinicalFacultyMember extends User{
	
	function __construct($id,$username, $firstname, $lastname) {
		parent::__construct($id,$username,$firstname,$lastname);
	}
	
	/**
	 * Returns the id of the department the user is in
	 * @return int
	 */
	public function getDepartmentID() {
		return $this->department_id;
	}
		
	/**
	 * Returns the username of the user
	 * @return string
	 */
	function getDepartmentName() {
		return $this->department_name;
	}
		
	public static function get($user_id) {
		global $db;
		
		$query = "SELECT `user_data`.`id`, username, firstname, lastname, dep_id, department_title
				FROM user_departments
				LEFT JOIN user_data ON user_departments.user_id = user_data.id
				LEFT JOIN user_access ON `user_access`.`user_id` = `user_data`.`id`
				LEFT JOIN departments ON `user_departments`.`dep_id` = `departments`.`department_id`
				where user_access.group='faculty' and clinical='1' and ".$db->qstr($user_id)." group by department_title,lastname,firstname 
				order by department_title,lastname,firstname";
		$result = $db->getRow($query);
		if ($result) {
			$user =  new ClinicaFaculty($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['dep_id'], $result['department_title'] );
			return $user;
		}		
	
	}
}