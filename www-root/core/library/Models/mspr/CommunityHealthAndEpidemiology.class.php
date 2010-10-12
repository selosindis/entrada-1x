<?php

require_once("SupervisedProject.class.php");

class CommunityHealthAndEpidemiology extends SupervisedProject {
	/**
	 * 
	 * @param User $user
	 * @return CommunityHealthAndEpidemiology
	 */
	public static function get(User $user) {
		global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_community_health_and_epidemiology` WHERE `user_id` = ".$db->qstr($user_id);
		$result = $db->getRow($query);
		if ($result) {
			$rejected=($result['status'] == -1);
			$approved = ($result['status'] == 1);
			
			$comm_health =  new CommunityHealthAndEpidemiology($result['user_id'], $result['title'], $result['organization'], $result['location'], $result['supervisor'], $approved, $rejected);
			return $comm_health;
		} 
	} 

	/**
	 * Creates a new Community Health and Epidemiology entry OR updates if one already exists. This will reset the approval.
	 * @param unknown_type $user
	 * @param unknown_type $title
	 * @param unknown_type $organization
	 * @param unknown_type $location
	 * @param unknown_type $supervisor
	 */
	public static function create($user_id, $title, $organization, $location, $supervisor) {
		
		global $db;
		$query = "insert into `student_community_health_and_epidemiology` 
					(`user_id`, `title`, `organization`,`location`,`supervisor`, `status`)
					value 
					(".$db->qstr($user_id).", ".$db->qstr($title).", ".$db->qstr($organization).", ".$db->qstr($location).", ".$db->qstr($supervisor).", ".$db->qstr(0).")
					on duplicate key update 
					`title`=".$db->qstr($title).", `organization`=".$db->qstr($organization).", `location`=".$db->qstr($location).", `supervisor`=".$db->qstr($supervisor).", `status`=".$db->qstr(0);
		if(!$db->Execute($query)) {
			add_error("Failed to update Community Health and Epidemiology entry.");
			application_log("error", "Unable to update a student_community_health_and_epidemiology record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated Community Health and Epidemiology entry.");
		}
	}
	
	private function setStatus($status_code) {
		global $db;
		$query = "update `student_community_health_and_epidemiology` set `status`=".$db->qstr($status_code)." where `user_id`=".$db->qstr($this->getUserID());
		if(!$db->Execute($query)) {
			add_error("Failed to update Critical Enquiry entry.");
			application_log("error", "Unable to update a student_community_health_and_epidemiology record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated Community Health and Epidemiology entry.");
		}
	}
	
	public function approve() {
		$this->setStatus(1);
	}
	
	public function unapprove() {
		$this->setStatus(0);
	}
	public function reject() {
		$this->setStatus(-1);
	}
}