<?php

require_once("AbstractStudentDetails.class.php");

class LeaveOfAbsence extends AbstractStudentDetails {
	
	function __construct($user, $id, $details) {
		$this->user = $user;
		$this->id = $id;
		$this->details = $details;
	}
	
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_leaves_of_absence` where `id`=".$db->qstr($id);
		$result	= $db->GetRow($query);
		if ($result) {
			$user = User::get($result['user_id']);
			if ($user) {
				$fr = new LeaveOfAbsence($user, $result['id'], $result['absence_details']);
				return $fr;
			}
		}
	}
	
	public static function create($user_id, $details) {
		global $db;

		$query = "insert into `student_leaves_of_absence` (`user_id`, `absence_details`) value (".$db->qstr($user_id).", ".$db->qstr($details).")";
		
		if(!$db->Execute($query)) {
			add_error("Failed to create new Leave of Absence.");
			application_log("error", "Unable to update a student_leaves_of_absence record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new Leave of Absence.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public function delete() {
		global $db;
		
		$query = "DELETE FROM `student_leaves_of_absence` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove leave of absence from database.");
			application_log("error", "Unable to delete a student_leaves_of_absence record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed Leave of Absence.");
		}	
	}

}