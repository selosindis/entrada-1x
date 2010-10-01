<?php

require_once("Studentship.class.php");
require_once("Models/utility/Collection.class.php");

class Studentships extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_studentships` WHERE `user_id` = ".$db->qstr($user_id)." ORDER BY `year` ASC";
		$results = $db->getAll($query);
		if ($results) {
			$studentships = array();
			foreach ($results as $result) {
				$studentship =  new Studentship($result['id'], $result['user_id'], $result['title'], $result['year']);
				$studentships[] = $studentship;
			}
		}
		return new self($studentships);
	}
}