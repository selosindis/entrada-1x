<?php

require_once("Models/utility/Collection.class.php");
require_once("User.class.php");

/**
 * Provides a Collection wrapper to User objects. Methods provide means of getting all users belonging to a supplied grad year
 * @author Jonathan Fingland
 *
 */
class GraduatingClass extends Collection {
	/**
	 * Graduating year. 
	 * @var int
	 */
	private $grad_year;
	
	/**
	 * Returns a collection of User objects belonging to the provided grad_year
	 * <code>
	 * $class = GraduatingClass::get(2014);
	 * foreach ($class as $student) { ... }
	 * </code>
	 * 
	 * @param int $grad_year
	 * @return GraduatingClass
	 */
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
	
	/**
	 * returns the grad_year for this collection
	 * @return int
	 */
	public function getGradYear() {
		return $this->grad_year;
	}
	
	/**
	 * alias of getGradYear()
	 * @see GraduatingClass::getGradYear()
	 * @return int
	 */
	public function getID() {
		return $this->getGradYear();
	}
}