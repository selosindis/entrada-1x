<?php

require_once("AbstractStudentDetails.class.php");

class FormalRemediation extends AbstractStudentDetails {
	
	function __construct($user, $id, $details) {
		$this->user = $user;
		$this->id = $id;
		$this->details = $details;
	}
	
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_formal_remediations` where `id`=".$db->qstr($id);
		$result	= $db->GetRow($query);
		if ($result) {
			$user = User::get($result['user_id']);
			if ($user) {
				$fr = new FormalRemediation($user, $result['id'], $result['remediation_details']);
				return $fr;
			}
		}
	}
	
	public static function create($user, $details) {
		global $db;

		$user_id = $user->getID();
		$query = "insert into `student_formal_remediations` (`user_id`, `remediation_details`) value (".$db->qstr($user_id).", ".$db->qstr($details).")";
		
		if(!$db->Execute($query)) {
			add_error("Failed to create new Formal Remediation.");
			application_log("error", "Unable to update a student_formal_remediations record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new Formal Remediation.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `student_formal_remediations` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove formal remediation from database.");
			application_log("error", "Unable to delete a student_formal_remediations record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed Formal Remediation.");
		}	
	}

}