<?php

require_once("Models/utility/Collection.class.php");
require_once("FormalRemediation.class.php");

class FormalRemediations extends Collection {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_formal_remediations` WHERE `user_id` = ".$db->qstr($user_id);
		$results = $db->getAll($query);
		if ($results) {
			$frs = array();
			foreach ($results as $result) {
				$fr =  new FormalRemediation($user, $result['id'], $result['remediation_details']);
				$frs[] = $fr;
			}
		}
		return new self ($frs);
	}
}