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

/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

class Department {
	private  $department_id,
			 $organisation_id,
			 $entity_id,
			 $parent_id,
			 $department_title,
			 $department_address1,
			 $department_address2,
			 $department_city,
			 $department_province,
			 $department_country,
			 $department_postcode,
			 $department_telephone,
			 $department_fax,
			 $department_email,
			 $department_url,
			 $department_desc,
			 $department_active,
			 $entity_title;
	
	/**
	 * @param $department_id
	 * @param $organisation_id
	 * @param $entity_id
	 * @param $parent_id
	 * @param $department_title
	 * @param $department_address1
	 * @param $department_address2
	 * @param $department_city
	 * @param $department_province
	 * @param $department_country
	 * @param $department_postcode
	 * @param $department_telephone
	 * @param $department_fax
	 * @param $department_email
	 * @param $department_url
	 * @param $department_desc
	 * @param $department_active
	 */
	function __construct($department_id,$organisation_id, $entity_id, $parent_id, $department_title, $department_address1, $department_address2, $department_city, $department_province, $department_country, $department_postcode, $department_telephone, $department_fax, $department_email, $department_url, $department_desc, $department_active, $entity_title) {
		 $this->department_id = $department_id;
		 $this->organisation_id = $organisation_id;
		 $this->entity_id = $entity_id;
		 $this->parent_id = $parent_id;
		 $this->department_title = $department_title;
		 $this->department_address1 = $department_address1;
		 $this->department_address2 = $department_address2;
		 $this->department_city = $department_city;
		 $this->department_province = $department_province;
		 $this->department_country = $department_country;
		 $this->department_postcode = $department_postcode;
		 $this->department_telephone = $department_telephone;
		 $this->department_fax = $department_fax;
		 $this->department_email = $department_email;
		 $this->department_url = $department_url;
		 $this->department_desc = $department_desc;
		 $this->department_active = $department_active;
		 $this->entity_title = $entity_title;
	 }

	/**
	 * @param array $arr
	 * @return Department
	 */
	public static function fromArray(array $arr) {
		return new self($arr["department_id"], $arr["organisation_id"], $arr["entity_id"], $arr["parent_id"], $arr["department_title"], $arr["department_address1"], $arr["department_address2"], $arr["department_city"], $arr["department_province"], $arr["department_country"], $arr["department_postcode"], $arr["department_telephone"], $arr["department_fax"], $arr["department_email"], $arr["department_url"], $arr["department_desc"], $arr["department_active"], $arr["entity_title"]);
	}

	/**
	 * @param $department_id
	 * @return Department
	 */
	public static function get($department_id) {
		global $db;
 		$query			= "SELECT * FROM `".AUTH_DATABASE."`.`departments` a left join `".AUTH_DATABASE."`.`entity_type` b on a.`entity_id` = b.`entity_id` WHERE `department_id` = ?";
		$result	= $db->GetRow($query, array($department_id));
		if ($result) { 
			return self::fromArray($result);
		}
	}
	
	public function getID() {
		return $this->department_id;
	}
	
	public function getTitle() {
		return $this->department_title;
	}
	
	public function isActive() {
		return !!$this->deaprtment_active;
	}	
	
	public function getAddress1() {
		return $this->department_address1;
	}

	public function getAddress2() {
		return $this->department_address2;
	}
	
	public function getCity(){
		return $this->department_city();
	}
	
	public function getProvince() {
		return $this->department_province;
	}
	
	public function getCountry() {
		return $this->department_country;
	}
	
	public function getPostalCode() {
		return $this->department_postcode;
	}
	
	public function getAddress() {
		$address_parts = array (
			$this->getAddress1(), 
			$this->getAddress2(),
			$this->getCity() . ", " . $this->getProvince(),
			$this->getPostalCode(),
			$this->getCountry()
		);
		return trim(implode("\n",$address_parts));
	}

	public function getTelephone() {
		return $this->department_telephone;
	}
	
	public function getFax() {
		return $this->department_fax;	
	}
	
	public function getEmail() {
		return $this->department_email;
	}
	
	public function getDescription() {
		return $this->department_desc;
	}
	
	public function getEntityID() {
		return $this->entity_id;
	}
	
	
	public function getOrganisation() {
		return Organisation::get($this->organisation_id);
	}
	
	public function getParent() {
		if ($this->parent_id) {
			return self::get($this->parent_id);
		}
	}
}
