<?php

/**
 * Class for MSPR listing of Research projects in citation form. Citations are supposed to adhere to MLA guidelines however they are not enforced in this class
 * Priority property allows students to set their preference for appearance in the MSPR. At this time, a maximum of 6 Research citations will be included in the 
 * MSPR, AND since this is student input we need to get staff approval for inclusion, there is potential for students to end up with a sub-optimal listing if we 
 * had a strict limit of 6 citations and some of them were not approved.       
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class ResearchCitation {
	private $id;
	private $user_id;
	private $citation;
	private $priority;
	private $approved;
	
	function __construct($id, $user_id, $citation, $priority, $approved = false) {
		$this->id = $id;
		$this->user_id = $user_id;
		$this->citation = $citation;
		$this->priority = $priority;
		$this->approved = (bool) $approved;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getUserID() {
		return $this->user_id;	
	}
	
	/**
	 * Returns the text of the citation
	 * @return string
	 */
	public function getText() {
		return $this->citation;
	}
	
	/**
	 * Returns the priority of the citation 
	 */
	public function getPriority() {
		return $this->priority;
	}
	
	public function isApproved() {
		return (bool)($this->approved);
	}
	
		
	/**
	 * Returns a single ResearchCitation if found
	 * @param int $id
	 * @return ResearchCitation
	 */
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_research` WHERE `id` = ".$db->qstr($id);
		$result = $db->getRow($query);
		if ($result) {
			
			$citation =  new self($result['id'], $result['user_id'], $result['citation'], $result['priority'], $result['approved']);
			return $citation;
		}
	} 
	
	/**
	 * Returns the next priority number. 0 if there are no eistent entires for this user, and max+1 otherwise.
	 * @param $user_id
	 */
	private static function getNewPriority($user_id) {
		$query = "select MAX(`priority`) + 1 as hp from student_research where user_id=".$db->qstr($user_id)." group by `user_id`";
		$result = $db->getRow($query);
		if (!$result) {
			$priority = 0;
		} else {
			$priority = $result['hp'];
		}
		return $priority;
	}
	
	/**
	 * Adds a new citation and sets the priority at the end of the list.  
	 * @param $user_id
	 * @param $citation
	 * @param $approved
	 */
	public static function create($user_id, $citation, $approved = false) {
		
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$approved = (int) $approved;
		$priority = self::getNewPriority($user_id);
		$query = "insert into `student_research` (`user_id`, `citation`, `priority`, `approved`) value (".$db->qstr($user_id).", ".$db->qstr($citation).", ".$db->qstr($priority).", ". $db->qstr($approved).")";
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to create new Research Citation.";
			application_log("error", "Unable to create a student_research record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully added new Research Citation.";
		}
	}
	
	/**
	 * Deletes the citation from the DB and resequences the following priorities
	 */
	public function delete() {
		
		$cur_priority = $this->priority;
		$user_id = $this->user_id;
		
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "DELETE FROM `student_research` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to remove Research Citation from database.";
			application_log("error", "Unable to delete a student_research record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully removed Research Citation.";
		}
		$query = "UPDATE `student_research` set `priority`=`priority`-1 where `priority` > ".$db->qstr($cur_priority)." and `user_id`=".$db->qstr($user_id);
		$db->Execute($query);
				
	}
	
	public function approve() {
		if (!$this->isApproved()) {
			global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
			$query = "update `student_research` set
					 `approved`=1 
					 where `id`=".$db->qstr($this->id);
			
			if(!$db->Execute($query)) {
				$ERROR++;
				$ERRORSTR[] = "Failed to approved Research Citation.".$db->ErrorMsg();
				application_log("error", "Unable to update a student_research record. Database said: ".$db->ErrorMsg());
			} else {
				$SUCCESS++;
				$SUCCESSSTR[] = "Successfully approved Research Citation.";
				$this->approved = true;
			}
		}
	}
	
	public function unapprove() {
		if ($this->isApproved()) {
			global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
			$query = "update `student_research` set
					 `approved`=0 
					 where `id`=".$db->qstr($this->id);
			
			if(!$db->Execute($query)) {
				$ERROR++;
				$ERRORSTR[] = "Failed to unapproved Research Citation.";
				application_log("error", "Unable to update a student_research record. Database said: ".$db->ErrorMsg());
			} else {
				$SUCCESS++;
				$SUCCESSSTR[] = "Successfully unapproved Research Citation.";
				$this->approved = false;
			}
		}
	}
	
	public function setPriority($priority) {
		$query = "update `student_research` set
				 `approved`=0 
				 where `id`=".$db->qstr($this->id);
		
		if($db->Execute($query)) {
			$this->priority = $priority;
		}
	}
}
