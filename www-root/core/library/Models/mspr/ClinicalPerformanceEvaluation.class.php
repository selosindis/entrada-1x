<?php

/**
 * TODO This class should be expanded to include more than just the comments
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class ClinicalPerformanceEvaluation {
	private $comment;
	private $id;
	private $source;
	private $user;
	
	public function getUser() {
		return $this->user;
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	public function getSource() {
		return $this->source;
	}
	
	public function getID() {
		return $this->id;
	}
	
	function __construct($user, $id, $comment,$source) {
		$this->user = $user;
		$this->id = $id;
		$this->comment = $comment;
		$this->source = $source;
	}
	
	public static function create($user, $comment,$source) {
		global $db;
		$user_id = $user->getID();
	
		$query = "insert into `student_clineval_comments` (`user_id`, `comment`,`source`) value (".$db->qstr($user_id).", ".$db->qstr($comment).", ".$db->qstr($source).")";
		if(!$db->Execute($query)) {
			add_error("Failed to create new clinical performance evaluation.");
			application_log("error", "Unable to update a student_clineval_comment record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new clinical performance evaluation.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_clineval_comments` WHERE `id` = ".$db->qstr($id);
		$result = $db->getRow($query);
		if ($result) {
			$user = User::get($result['user_id']);
			$clineval =  new ClinicalPerformanceEvaluation($user, $result['id'], $result['comment'], $result['source']);
			return $clineval;
		}
	}  
	
	public function delete() {
		global $db;
		$query = "DELETE FROM `student_clineval_comments` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove clinical performance evaluation from database.");
			application_log("error", "Unable to delete a student_clineval_comment record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed clinical performance evaluation.");
		}		
	}
}