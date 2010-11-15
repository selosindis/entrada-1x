<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("Models/users/User.class.php");
require_once("ExternalAward.class.php");

/**
 * 
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class ExternalAwardReceipt implements Approvable,AttentionRequirable {
	private $award_receipt_id;
	private $award;
	private $user_id;
	private $year;
	private $approved;
	private $rejected;
	private $comment;
	
	function __construct($user_id, $award, $award_receipt_id, $year, $comment, $approved = false, $rejected = false){
		$this->user_id = $user_id;
		$this->award = $award;
		$this->award_receipt_id = $award_receipt_id;
		$this->year = $year;
		$this->comment = $comment;
		$this->approved = (bool)$approved;
		$this->rejected = (bool)$rejected;
	}
	
	public static function fromArray(array $arr) {
		$rejected=($arr['status'] == -1);
		$approved = ($arr['status'] == 1);
		return new self($arr['user_id'], $arr['award'], $arr['award_receipt_id'], $arr['year'], $arr['comment'], $approved, $rejected);
	}
	
	/**
	 * Requires attention if not approved, unless rejected
	 * @see www-root/core/library/Models/AttentionRequirable#isAttentionRequired()
	 */
	public function isAttentionRequired() {
		return !$this->isApproved() && !$this->isRejected();
	}
	
	public function isApproved() {
		return (bool)($this->approved);	
	}
	
	public function getID() {
		return $this->award_receipt_id;
	}
	
	public function getAwardYear() {
		return $this->year;
	}
	
	public function getUser() {
		return User::get($this->user_id);
	}
	
	public function getAward() {
		return $this->award;
	}
	
	public function isRejected() {
		return (bool)($this->rejected);
	}
	
	public function getComment() {
		return $this->comment;
	}
		
	static public function create($user_id, $title, $terms, $awarding_body,$year, $approved = false) {
		global $db;
		$approved = (int) $approved;
		$query = "INSERT INTO `student_awards_external` (`user_id`,`title`, `award_terms`, `awarding_body`, `year`, `status`) VALUES (".$db->qstr($user_id).", ".$db->qstr($title).", ".$db->qstr($terms).", ".$db->qstr($awarding_body).", ".$db->qstr($year).", ".$db->qstr($approved ? 1 : 0).")";
		if(!$db->Execute($query)) {
			add_error("Failed to add award recipient to database. Please check your values and try again.");
			application_log("error", "Unable to insert a student_awards_external record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added Award Recipient.");
		}
	}
	
	/**
	 * 
	 * @param int $award_receipt_id
	 * @return ExternalAwardRecipient
	 */
	static public function get($award_receipt_id) {
		global $db;
		$query		= "SELECT a.id as `award_receipt_id`, user_id, a.title, a.award_terms, a.awarding_body, a.status, a.year 
				FROM `". DATABASE_NAME ."`.`student_awards_external` a 
				WHERE a.id = ".$db->qstr($award_receipt_id);
		
		$result	= $db->GetRow($query);
			
		if ($result) {
				
			$award = ExternalAward::fromArray($result);
			$result['award'] = $award;
			return ExternalAwardReceipt::fromArray($result);
		} else {
			add_error("Failed to retreive award receipt from database.");
			application_log("error", "Unable to retrieve a student_awards_external record. Database said: ".$db->ErrorMsg());
		}
			 
	} 
	
	public function delete() {
		global $db;
	
		$query = "DELETE FROM `student_awards_external` where `id`=".$db->qstr($this->award_receipt_id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove award receipt from database.");
			application_log("error", "Unable to delete a student_awards_external record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed award receipt.");
		}
	}
	
	private function setStatus($status_code, $comment=null) {
		global $db;
		$query = "update `student_awards_external` set
				 `status`=?, `comment`=?
				 where `id`=?";
		
		if(!$db->Execute($query, array($status_code, $comment, $this->award_receipt_id))) {
			add_error("Failed to update award.");
			application_log("error", "Unable to update a student_awards_external record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated award.");
			$this->approved = true;
		}
	}
	
	public function approve() {
		$this->setStatus(1);
	}
	
	public function unapprove() {
		$this->setStatus(0);
	}
	
	public function reject($comment) {
		$this->setStatus(-1,$comment);
	}
	
	public function compare($ar, $compare_by="year") {
		switch($compare_by) {
			case 'year':
				return $this->year == $ar->year ? 0 : ( $this->year > $ar->year ? 1 : -1 );
				break;
			case 'title':
				$award = $this->getAward();
				$other_award = $ar->getAward();
				return $award->compare($other_award);
				break;
		}
	}
}