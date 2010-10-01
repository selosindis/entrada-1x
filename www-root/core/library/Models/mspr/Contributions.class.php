<?php

require_once("Contribution.class.php");
require_once("Models/utility/Collection.class.php");

class Contributions extends Collection implements AttentionRequirable {
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_contributions` WHERE `user_id` = ".$db->qstr($user_id)." ORDER BY `id` ASC";
		$results = $db->getAll($query);
		$contributions = array();
		if ($results) {
			foreach ($results as $result) {
				$rejected=($result['status'] == -1);
				$approved = ($result['status'] == 1);
			
				$contribution =  new Contribution($result['id'], $result['user_id'], $result['role'], $result['org_event'], $result['start_month'], $result['start_year'], $result['end_month'], $result['end_year'], $approved, $rejected);
				$contributions[] = $contribution;
			}
		}
		return new self($contributions);
	}
	
	public function isAttentionRequired() {
		$att_req = false;
		foreach ($this as $element) {
			$att_req = $element->isAttentionRequired();
			if ($att_req) break;
		}
		return $att_req;
	}
}