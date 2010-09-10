<?php

require_once("Observership.class.php");
require_once("Models/utility/Collection.class.php");

class Observerships extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT *, UNIX_TIMESTAMP(`end`) as `end`, UNIX_TIMESTAMP(`start`) as `start`  FROM `student_observerships` WHERE `student_id` = ".$db->qstr($user_id)." ORDER BY `start` ASC";
		$results = $db->getAll($query);
		$obss = array();
		if ($results) {
			foreach ($results as $result) {
				$obs =  new Observership($result['id'], $result['student_id'], $result['title'], $result['site'], $result['location'], $result['start'], $result['end']);
				$obss[] = $obs;
			}
		} 
		return new self($obss);
	}
}