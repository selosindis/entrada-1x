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
				$user =  User::fromArray($result);
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
	
	/**
	 * alias of getGradYear()
	 */
	public function getID() {
		return $this->getGradYear();
	}
}