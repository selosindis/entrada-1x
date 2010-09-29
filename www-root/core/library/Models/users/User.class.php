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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
require_once("Models/utility/SimpleCache.class.php");
require_once("Models/organisations/Organisation.class.php");
require_once("Models/users/GraduatingClass.class.php");

/**
 * Simple User class with basic information
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class User {
	private $id,
			$username,
			$firstname,
			$lastname,
			$number,
			$grad_year,
			$entry_year,
			$password,
			$organisation_id,
			$department,
			$prefix,
			$email,
			$email_alt,
			$google_id,
			$telephone,
			$fax,
			$address,
			$city,
			$province,
			$postcode,
			$country,
			$country_id,
			$province_id,
			$notes,
			$privacy_level,
			$notifications,
			$office_hours,
			$clinical;
	
	
	function __construct(	$id,
							$username,
							$firstname,
							$lastname,
							$number,
							$grad_year,
							$entry_year,
							$password,
							$organisation_id,
							$department,
							$prefix,
							$email,
							$email_alt,
							$google_id,
							$telephone,
							$fax,
							$address,
							$city,
							$province,
							$postcode,
							$country,
							$country_id,
							$province_id,
							$notes,
							$privacy_level,
							$notifications,
							$office_hours,
							$clinical) {
		$this->id = $id;
		$this->username = $username;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->number = $number;
		$this->grad_year = $grad_year;
		$this->entry_year = $entry_year;
		$this->password = $password;
		$this->organisation_id = $organisation_id;
		$this->department = $department;
		$this->prefix = $prefix;
		$this->email = $email;
		$this->email_alt = $email_alt;
		$this->google_id = $google_id;
		$this->telephone = $telephone;
		$this->fax = $fax;
		$this->address = $address;
		$this->city = $city;
		$this->province = $province;
		$this->postcode = $postcode;
		$this->country = $country;
		$this->country_id = $country_id;
		$this->province_id = $province_id;
		$this->notes = $notes;
		$this->privacy_level = $privacy_level;
		$this->notifications = $notifications;
		$this->office_hours = $office_hours;
		$this->clinical = $clinical;
		
		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"User",$this->id);
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
	
	function getGraduatingClass() {
		if ($this->grad_year) {
			return GraduatingClass::get($this->grad_year);
		}
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
	
	function getOrganisation() {
		return Organisation::get($this->organisation_id);
	}
	
	public static function get($user_id) {
		$cache = SimpleCache::getCache();
		$user = $cache->get("User",$user_id);
		if (!$user) {
			global $db;
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($user_id);
			$result = $db->getRow($query);
			if ($result) {
				$user = self::fromArray($result);  			
			}		
		} 
		return $user;
	}
	
	public static function fromArray($arr) {
		return new User($arr['id'],$arr['username'],$arr['firstname'],$arr['lastname'],$arr['number'],$arr['grad_year'],$arr['entry_year'],$arr['password'],$arr['organisation_id'],$arr['department'],$arr['prefix'],$arr['email'],$arr['email_alt'],$arr['google_id'],$arr['telephone'],$arr['fax'],$arr['address'],$arr['city'],$arr['province'],$arr['postcode'],$arr['country'],$arr['country_id'],$arr['province_id'],$arr['notes'],$arr['privacy_level'],$arr['notifications'],$arr['office_hours'],$arr['clinical']);	
	}
}