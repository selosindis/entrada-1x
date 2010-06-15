<?php

require_once("Contribution.class.php");
require_once("Collection.class.php");

class Contributions extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_contributions` WHERE `user_id` = ".$db->qstr($user_id)." ORDER BY `id` ASC";
		$results = $db->getAll($query);
		$contributions = array();
		if ($results) {
			foreach ($results as $result) {
				$contribution =  new Contribution($result['id'], $result['user_id'], $result['role'], $result['org_event'], $result['start_month'], $result['start_year'], $result['end_month'], $result['end_year'], $result['approved']);
				$contributions[] = $contribution;
			}
		}
		return new self($contributions);
	}
}