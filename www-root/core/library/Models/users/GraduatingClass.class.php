<?php

require_once("Models/utility/Collection.class.php");
require_once("User.class.php");

class GraduatingClass extends Collection {
	private $grad_year;
	public static function get($grad_year) {
		global $db;
		$query = "SELECT * from `".AUTH_DATABASE."`.`user_data` where `grad_year`=".$db->qstr($grad_year);
		
		$results = $db->getAll($query);
		$users = array();
		if ($results) {
			foreach ($results as $result) {
				$user =  new User($result['id'],$result['username'],$result['firstname'],$result['lastname'],$result['number'],$result['grad_year'],$result['entry_year'],$result['password'],$result['organization_id'],$result['department'],$result['prefix'],$result['email'],$result['email_alt'],$result['google_id'],$result['telephone'],$result['fax'],$result['address'],$result['city'],$result['province'],$result['postcode'],$result['country'],$result['country_id'],$result['province_id'],$result['notes'],$result['privacy_level'],$result['notifications'],$result['office_hours'],$result['clinical']);
				$users[] = $user;
			}
		}
		
		return new self($users,$grad_year);
	}
	
	function __construct($users, $grad_year) {
		parent::__construct($users);
		$this->grad_year = $grad_year;
	}
	
	public function getGradYear() {
		return $this->grad_year;
	}
}