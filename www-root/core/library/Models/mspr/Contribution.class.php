<?php

require_once("Models/utility/Approvable.interface.php");
require_once("Models/utility/AttentionRequirable.interface.php");

class Contribution implements Approvable, AttentionRequirable {
	private $id;
	private $user_id;
	private $role;
	private $start_month;
	private $end_month;
	private $start_year;
	private $end_year;
	private $org_event;
	private $approved;
	private $rejected;
	
	function __construct($id, $user_id, $role, $org_event, $start_month, $start_year, $end_month, $end_year, $approved = false, $rejected = false) {
		$this->id = $id;
		$this->user_id = $user_id;
		$this->role = $role;
		$this->org_event = $org_event;
		$this->approved = (bool) $approved;
		$this->rejected = (bool)$rejected;
		
		$this->start_month = $start_month;
		$this->start_year = $start_year;
		$this->end_month = $end_month;
		$this->end_year = $end_year;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getUserID() {
		return $this->user_id;	
	}
	
	public function getRole(){
		return $this->role;
	}
	
	public function getOrgEvent() {
		return $this->org_event;
	}
	
	public function getStartMonth(){
		return $this->start_month;
	}
	
	public function getStartYear(){
		return $this->start_year;
	}
	
	public function getEndMonth(){
		return $this->end_month;
	}
	
	public function getEndYear(){
		return $this->end_year;
	}
	
	public function getStartDate() {
		return array(
			"m" => $this->start_month,
			"y" => $this->start_year
		);
	}
	
	public function getEndDate() {
		return array(
			"m" => $this->end_month,
			"y" => $this->end_year
		);
	}
	
	public function getPeriod() {
		return formatDateRange($this->getStartDate(), $this->getEndDate()); 
	}
	
	public function isApproved() {
		return (bool)($this->approved);
	}
	
	/**
	 * Requires attention if not approved, unless rejected
	 * @see www-root/core/library/Models/AttentionRequirable#isAttentionRequired()
	 */
	public function isAttentionRequired() {
		return !$this->isApproved() && !$this->isRejected();
	}
	
	public function isRejected() {
		return (bool)($this->rejected);
	}
		
	/**
	 * 
	 * @param int $id
	 * @return Contribution
	 */
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_contributions` WHERE `id` = ".$db->qstr($id);
		$result = $db->getRow($query);
		if ($result) {
			$rejected=($result['status'] == -1);
			$approved = ($result['status'] == 1);
			
			$contribution =  new Contribution($result['id'], $result['user_id'], $result['role'], $result['org_event'], $result['start_month'], $result['start_year'], $result['end_month'], $result['end_year'], $approved, $rejected);
			return $contribution;
		}
	} 
	
	public static function create($user, $role, $org_event, $start_month, $start_year, $end_month, $end_year, $approved = false) {
		global $db;
		$user_id = $user->getID();
		$approved = (int) $approved;
		$query = "insert into `student_contributions` (`user_id`, `role`,`org_event`,`start_month`, `start_year`, `end_month`,`end_year`, `status`) value (".$db->qstr($user_id).", ".$db->qstr($role).", ".$db->qstr($org_event).", ".$db->qstr($start_month).", ".$db->qstr($start_year).", ".$db->qstr($end_month).", ".$db->qstr($end_year).", ". $db->qstr($approved ? 1 : 0).")";
		if(!$db->Execute($query)) {
			add_error("Failed to create new contribution.");
			application_log("error", "Unable to update a student_contributions record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new contribution.");
		}
	}
	
	public function delete() {
		global $db;
		$query = "DELETE FROM `student_contributions` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove contribution from database.");
			application_log("error", "Unable to delete a student_contributions record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed contribution.");
		}		
	}
	
	private function setStatus($status_code) {
			global $db;
			$query = "update `student_contributions` set
					 `status`=".$db->qstr($status_code)." 
					 where `id`=".$db->qstr($this->id);
			
			if(!$db->Execute($query)) {
				add_error("Failed to update contribution.");
				application_log("error", "Unable to update a student_contributions record. Database said: ".$db->ErrorMsg());
			} else {
				add_success("Successfully updated contribution.");
				$this->approved = true;
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