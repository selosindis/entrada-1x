<?php


require_once("LeaveOfAbsence.class.php");

class LeavesOfAbsence {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_leaves_of_absence` WHERE `user_id` = ".$db->qstr($user_id);
		$results = $db->getAll($query);
		if ($results) {
			$frs = array();
			foreach ($results as $result) {
				$fr =  new LeaveOfAbsence($user, $result['id'], $result['absence_details']);
				$frs[] = $fr;
			}
			return $frs;
		}
	}
}