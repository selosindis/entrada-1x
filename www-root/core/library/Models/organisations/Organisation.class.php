<?php
require_once("Models/utility/SimpleCache.class.php");

class Organisation {
	private	$organisation_id,
			$organisation_title,
			$organisation_address1,
			$organisation_address2,
			$organisation_city,
			$organisation_province,
			$organisation_country,
			$organisation_postcode,
			$organisation_telephone,
			$organisation_fax,
			$organisation_email,
			$organisation_url,
			$organisation_desc;
	
	function __construct(	$organisation_id,
							$organisation_title,
							$organisation_address1,
							$organisation_address2,
							$organisation_city,
							$organisation_province,
							$organisation_country,
							$organisation_postcode,
							$organisation_telephone,
							$organisation_fax,
							$organisation_email,
							$organisation_url,
							$organisation_desc) {

							
		$this->organisation_id = $organisation_id;
		$this->organisation_title = $organisation_title;
		$this->organisation_address1 = $organisation_address1;
		$this->organisation_address2 = $organisation_address2;
		$this->organisation_city = $organisation_city;
		$this->organisation_province = $organisation_province;
		$this->organisation_country = $organisation_country;
		$this->organisation_postcode = $organisation_postcode;
		$this->organisation_telephone = $organisation_telephone;
		$this->organisation_fax = $organisation_fax;
		$this->organisation_email = $organisation_email;
		$this->organisation_url = $organisation_url;
		$this->organisation_desc = $organisation_desc;
		
		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"Organisation",$this->organisation_id);
		
	}
	
	function getID() {
		return $this->organisation_id;
	}
	
	function getTitle() {
		return $this->organisation_title;
	}
	
	//XXX should address info be formatted differently or remain atomic by address lines? 
	function getAddress1() {
		return $this->organisation_address1;
	}
	
	function getAddress2() {
		return $this->organisation_address2;
	}
	
	function getCity() {
		return $this->organisation_city;
	}
	
	function getProvince() {
		return $this->organisation_province;
	}
	
	function getCountry() {
		return $this->organisation_country;
	}
	
	function getPostCode() {
		return $this->organisation_postcode;
	}
	
	function getTelephone() {
		return $this->organisation_telephone;
	}
	
	function getFax() {
		return $this->organisation_fax;
	}
	
	function getEmail() {
		return $this->organisation_email;
	}
	
	function getURL() {
		return $this->organisation_url;
	}
	
	function getDescription() {
		return $this->organisation_desc;
	}
	
	static function get($organisation_id) {
		$cache = SimpleCache::getCache();
		$organisation = $cache->get("Organisation",$organisation_id);
		if (!$organisation) {
			global $db;
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($organisation_id);
			$result = $db->getRow($query);
			if ($result) {
				$organisation = new Organisation($result['organisation_id'],$result['organisation_title'],$result['organisation_address1'],$result['organisation_address2'],$result['organisation_city'],$result['organisation_province'],$result['organisation_country'],$result['organisation_postcode'],$result['organisation_telephone'],$result['organisation_fax'],$result['organisation_email'],$result['organisation_url'],$result['organisation_desc']);			
			}		
		} 
		return $organisation;
		
	}
}