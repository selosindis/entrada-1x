<?php

require_once("SupervisedProject.class.php");

class CriticalEnquiry extends SupervisedProject {
	/**
	 * 
	 * @param User $user
	 * @return CriticalEnquiry
	 */
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_critical_enquiries` WHERE `user_id` = ".$db->qstr($user_id);
		$result = $db->getRow($query);
		if ($result) {
			$rejected=($result['status'] == -1);
			$approved = (bool) $result['status'];
			$critical_enquiry =  new CriticalEnquiry($result['user_id'], $result['title'], $result['organization'], $result['location'], $result['supervisor'], $approved, $rejected);
			return $critical_enquiry;
		}
	} 

	/**
	 * Creates a new Critical Enquiry entry OR updates if one already exists. This will reset the approval.
	 * @param unknown_type $user
	 * @param unknown_type $title
	 * @param unknown_type $organization
	 * @param unknown_type $location
	 * @param unknown_type $supervisor
	 */
	public static function create($user_id, $title, $organization, $location, $supervisor) {
		
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "insert into `student_critical_enquiries` 
					(`user_id`, `title`, `organization`,`location`,`supervisor`, `status`)
					value 
					(".$db->qstr($user_id).", ".$db->qstr($title).", ".$db->qstr($organization).", ".$db->qstr($location).", ".$db->qstr($supervisor).", ".$db->qstr(0).")
					on duplicate key update 
					`title`=".$db->qstr($title).", `organization`=".$db->qstr($organization).", `location`=".$db->qstr($location).", `supervisor`=".$db->qstr($supervisor).", `status`=".$db->qstr(0);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Critical Enquiry entry.";
			application_log("error", "Unable to update a student_critical_enquiries record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully updated Critical Enquiry entry.";
		}
	}
	
	public function approve() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "update `student_critical_enquiries` set `status`=1 where `user_id`=".$db->qstr($this->getUserID());
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Critical Enquiry entry.";
			application_log("error", "Unable to update a student_critical_enquiries record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully updated Critical Enquiry entry.";
		}
	}
	
	public function unapprove() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "update `student_critical_enquiries` set `status`=0 where `user_id`=".$db->qstr($this->getUserID());
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Critical Enquiry entry.";
			application_log("error", "Unable to update a student_critical_enquiries record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully updated Critical Enquiry entry.";
		}
	}
	
	public function reject() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "update `student_critical_enquiries` set `status`=-1 where `user_id`=".$db->qstr($this->getUserID());
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Critical Enquiry entry.";
			application_log("error", "Unable to update a student_critical_enquiries record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully updated Critical Enquiry entry.";
		}
	}
	
}