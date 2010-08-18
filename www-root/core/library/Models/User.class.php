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
*/
require_once("SimpleCache.class.php");

/**
 * Simple User class with basic information
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class User {
	private $id;
	private $username;
	private $firstname;
	private $lastname;
	private $number;
	private $grad_year;
	private $entry_year;
	
	function __construct($id,$username, $lastname, $firstname, $number = 0, $grad_year = null, $entry_year = null) {
		$this->id = $id;
		$this->username = $username;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->number = $number;
		$this->grad_year = $grad_year;
		$this->entry_year = $entry_year;
		
		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"User",$this->$id);
	}
	
	/**
	 * Returns the id of the user
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
		
	/**
	 * Returns the username of the user
	 * @return string
	 */
	function getUsername() {
		return $this->username;
	}
	
	/**
	 * Returns the graduating year of the user, if available
	 * @return int
	 */
	function getGradYear() {
		return $this->grad_year;
	}
	
	/**
	 * Returns the year a student enetered med school, if available
	 * @return int
	 */
	function getEntryYear() {
		return $this->entry_year;
	}
	
	/**
	 * Returns the first name of the user
	 * @return string
	 */
	function getFirstname(){
		return $this->firstname;
	} 
	
	/**
	 * Returns the last name of the user
	 * @return string
	 */
	function getLastname() {
		return $this->lastname;
	}
	
	/**
	 * Returns the Last and First names formatted as "lastname, firstname"
	 * @return string
	 */
	function getFullname() {
		return $this->lastname . ", " . $this->firstname;
	}
	
	/**
	 * Returns the real world student number/employee number
	 * @return int
	 */
	function getNumber() {
		return $this->number;
	}
	
	public static function get($user_id) {
		$cache = SimpleCache::getCache();
		$user = $cache->get("User",$user_id);
		if (!$user) {
			global $db;
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($user_id);
			$result = $db->getRow($query);
			if ($result) {
				$user =  new User($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['number'],$result['grad_year'],$result['entry_year']);			
			}		
		} 
		return $user;
	}
}