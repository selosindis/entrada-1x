<?php

require_once("Models/tasks/Task.class.php");

class TaskCompletion {
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
	 * timestamp the verifier verified completion
	 * @var int
	 */
	private $verified_date;
	
	/**
	 * timestamp the recipient stated completion
	 * @var int
	 */
	private $completed_date;
	
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
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	public function isVerifier(User $user) {
		return $user === $this->getVerifier();
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
	 * @return bool
	 */
	public function isCompleted() {
		return $this->completed_date > 0;
	}
	
	/**
	 * @return int
	 */
	public function getCompletedDate() {
		return $this->completed_date;
	}
	
	/**
	 * @return Task
	 */
	public function getTask() {
		return Task::get($this->task_id);
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
	
	public function update($completed_date, $verifier_id=null, $verified_date = null) {
		global $db;
		
		//Insert/update to account for potentially missing entries
		$query = "	INSERT into `task_completion` (`task_id`, `recipient_id`, `verifier_id`, `verified_date`, `completed_date`) value (?,?,?,?,?) 
					on duplicate key update `verifier_id`=?, `verified_date`=?, `completed_date`=?";
		
		if(!$db->Execute($query,array($this->task_id, $this->recipient_id, $verifier_id, $verified_date, $completed_date, $verifier_id, $verified_date, $completed_date))) {
			add_error("Failed to update task completion information");
			application_log("error", "Unable to update a task_completion record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated task completion record");
		}
	}
	
	/**
	 * 
	 * @param int $task_id
	 * @param int $recipient_id
	 * 
	 * @return TaskCompletion 
	 */
	public static function get($task_id, $recipient_id) {
		$cache = SimpleCache::getCache();
		$task_v = $cache->get("TaskCompletion",$task_id,$recipient_id);
		
		if (!$task_v) {
			global $db;
			
			$query = "	SELECT DISTINCT * from (
						SELECT a.*, b.`task_id`, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date` 
						from `".AUTH_DATABASE."`.`user_data` a 
						inner join `task_recipients` b on a.`grad_year`=b.`recipient_id` 
						left join `task_completion` c on c.`task_id`=b.`task_id` and c.`recipient_id` = a.`id`
						where b.`recipient_type`='grad_year' and b.`task_id`=? and a.`id`=?
						UNION
						SELECT w.*, z.`task_id`, y.`verifier_id`, y.`verified_date`, z.`recipient_id`, y.`completed_date` 
						from `task_recipients` z 
						left join `task_completion` y on z.`recipient_id`=y.`recipient_id` and y.`task_id`=z.`task_id`  
						inner join `".AUTH_DATABASE."`.`user_data` w on z.`recipient_id`=w.`id`
						where z.`recipient_type`='user' and z.`task_id`=? and w.`id`=?
						UNION
						SELECT i.*, j.`task_id`, k.`verifier_id`, k.`verified_date`, i.`id` as `recipient_id`, k.`completed_date` 
						from `task_recipients` j
						inner join `".AUTH_DATABASE."`.`user_data` i on j.`recipient_id`=i.`organisation_id` 
						left join `task_completion` k on i.`id`=k.`recipient_id` and j.`task_id`=k.`task_id`
						where j.`recipient_type` = 'organisation' and j.`task_id`=? and i.`id`=?) m";			
			
			$result = $db->GetRow($query, array($task_id,$recipient_id, $task_id,$recipient_id, $task_id,$recipient_id));
			if ($result) {
				$task_v = self::fromArray($result);
			} 
		}
		return $task_v;
	}
	
	function __construct($task_id, $recipient_id, $verifier_id=null, $verified_date=null, $completed_date=null) {
		$this->task_id = $task_id;
		$this->recipient_id = $recipient_id;
		$this->verifier_id = $verifier_id;
		$this->verified_date = $verified_date;
		$this->completed_date = $completed_date;

		$cache = SimpleCache::getCache();
		$cache->set($this,"TaskCompletion",$task_id);
	}
	
	public static function fromArray($arr) {
		return new self($arr['task_id'],$arr['recipient_id'],$arr['verifier_id'],$arr['verified_date'], $arr['completed_date']);  
	}
}