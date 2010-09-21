<?php

class TaskVerification {
	/**
	 * ID of the task we're working with
	 * @var int
	 */
	private $task_id;
	
	/**
	 * Proxy ID of the user receiving the task
	 * @var int
	 */
	private $recipient_id;
	
	/**
	 * Proxy ID of the user verifying completion
	 * @var int
	 */
	private $verifier_id;
	
	/**
	 * Date the verifier verified completion
	 * @var int
	 */
	private $verified_date;
	
	/**
	 * @return User
	 */
	public function getRecipient() {
		return User::get($this->recipient_id);
	}
	
	/**
	 * @return User
	 */
	public function getVerifier() {
		return User::get($this->verifier_id);
	}
	
	/**
	 * @return bool
	 */
	public function isVerified() {
		return $this->verified_date > 0;
	}
	
	/**
	 * @return int
	 */
	public function getVerifiedDate() {
		return $this->verified_date;
	}
	
	/**
	 * Takes a task_id and an array of recipient ids. Note, this can also be safely used to update the list of recipients without deleting or filtering the list 
	 * @param unknown_type $task_id
	 */
	public static function add($task_id,$recipients) {
		global $db;
		if (!is_array($recipients)) { //single recipient case?
			$recipients = array($recipients);
		}
		$query = "insert ignore into `task_verification` (`task_id`,`recipient_id`) values ";
		$q_task_id = $db->qstr($task_id);
		$records = array();
		foreach($recipients as $recipient_id) {
			$records[] = "(".$q_task_id.",".$db->qstr($recipient_id).")";
		}
		$query .= implode(",",$records);
		if(!$db->Execute($query)) {
			add_error("Failed to add task verification records");
			application_log("error", "Unable to add task_verification records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added task verification records.");
		}
	}
	
	public function update($verifier_id, $verified_date = null) {
		global $db;
		
		$query = "update `task_verification` set `verifier_id`=".$db->qstr($verifier_id).", `verified_date`=".$db->qstr($verified_date)." where `recipient_id`=".$db->qstr($this->recipient_id)." and `task_id`=".$db->qstr($this->task_id);
		if(!$db->Execute($query)) {
			add_error("Failed to update task verification information");
			application_log("error", "Unable to update a task_verification record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated task verification record");
		}
	}
}