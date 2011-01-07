<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

class TaskVerifiers extends Collection {
	
	private $task_id;
	
	public static function get($task_id) {
		global $db;
		
		$query = "SELECT b.* from `task_verifiers` a left join `".AUTH_DATABASE."`.`user_data` b on a.`verifier_id`=b.`id` where a.`task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$verifiers = array();
		if ($results) {
			foreach ($results as $result) {
				$verifier = User::fromArray($result);
				if ($verifier) {
					$verifiers[] = $verifier;
				}
			}
		}
		return new self($verifiers, $task_id);
	}
	
	function __construct($verifiers, $task_id) {
		parent::__construct($verifiers);
		$this->task_id = $task_id;
	}
	
	
	public static function add($task_id, $verifiers) {
		global $db;
		$query = "insert ignore into `task_verifiers` (`task_id`,`verifier_id`) values ";
		$q_task_id = $db->qstr($task_id);
		if (!is_array($verifiers)) {
			$verifiers = array($verifiers);
		}
		$records = array();
		foreach($verifiers as $verifier) {
			if ($verifier instanceof User) {
				$verifier_id = $verifier->getID();
			} elseif (is_numeric($verifier)) {
				$verifier_id = (int) $verifier;
			} else {
				continue;
			}
			$records[] = "(".$q_task_id.",".$db->qstr($verifier_id). ")";
		}
		$query .= implode(",",$records);
		if(false === $db->Execute($query)) {
			add_error("Failed to add verifier to task. Database said: ".$db->ErrorMsg());
			application_log("error", "Unable to create task_verifiersy records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added verifier to task.");
		}
	} 
	
	public static function remove($task_id, $verifiers=null) {
		global $db;
		$q_task_id = $db->qstr($task_id);
		if (is_null($verifiers)) {
			$query = "delete from `task_verifiers` where `task_id`=?";
		} else {
			$query = "delete from `task_verifiers` where `task_id`=? and `verifier_id` IN (";
			if( !is_array($verifiers)) {
				$verifiers = array($verifiers);
			} 
			$records = array();
			foreach($verifiers as $verifier) {
				if ($verifier instanceof User) {
					$verifier_id = $verifier->getID();
				} elseif (is_int($verifier)) {
					$verifier_id = $verifier;
				} else {
					continue;
				}
				$records[] = $db->qstr($verifier_id);
			}
			$query .= implode(",",$records) . ")";
		}
		if(!$db->Execute($query, array($task_id))) {
			add_error("Failed to remove verifier from task");
			application_log("error", "Unable to remove from task_verifiers records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed verifiers from task.");
		}		
	}
} 
