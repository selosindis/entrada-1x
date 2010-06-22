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
			$critical_enquiry =  new CommunityHealthAndEpidemiology($result['user_id'], $result['title'], $result['organization'], $result['location'], $result['supervisor'], $result['approved']);
			return $critical_enquiry;
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
		
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "insert into `student_community_health_and_epidemiology` 
					(`user_id`, `title`, `organization`,`location`,`supervisor`, `approved`)
					value 
					(".$db->qstr($user_id).", ".$db->qstr($title).", ".$db->qstr($organization).", ".$db->qstr($location).", ".$db->qstr($supervisor).", ".$db->qstr(false).")
					on duplicate key update 
					`title`=".$db->qstr($title).", `organization`=".$db->qstr($organization).", `location`=".$db->qstr($location).", `supervisor`=".$db->qstr($supervisor).", `approved`=".$db->qstr(false);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to update Community Health and Epidemiology entry.".$db->ErrorMsg();
			application_log("error", "Unable to update a student_community_health_and_epidemiology record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully updated Community Health and Epidemiology entry.";
		}
	}
}