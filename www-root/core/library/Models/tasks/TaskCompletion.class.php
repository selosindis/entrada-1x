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
	
	private $faculty_id;
	
	private $completion_comment;
	
	private $rejection_comment;
	
	private $rejection_date;
	
	public function getFaculty() {
		if ($this->faculty_id) {
			return User::get($this->faculty_id);
		}
	}
	
	public function getCompletionComment() {
		return $this->completion_comment;
	}
	
	public function getRejectionComment() {
		return $this->rejection_comment;
	}
	
	public function getRejectionDate() {
		return $this->rejection_date;
	}
	
	public function isRejected() {
		return $this->rejection_date > 0;
	}
	
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
	
	public function update($update_data) {
		extract($update_data);
		global $db;
		
		//Insert/update to account for potentially missing entries
		$query = "	INSERT into `task_completion` (`task_id`, `recipient_id`, `verifier_id`, `verified_date`, `completed_date`, `faculty_id`, `completion_comment`, `rejection_comment`, `rejection_date`) value (?,?,?,?,?,?,?,?,?) 
					on duplicate key update `verifier_id`=?, `verified_date`=?, `completed_date`=?, `faculty_id`=?, `completion_comment`=?, `rejection_comment`=?, `rejection_date`=?";
		
		if(!$db->Execute($query,array($this->task_id, $this->recipient_id, 
				$verifier_id, $verified_date, $completed_date, $faculty_id, $completion_comment, $rejection_comment, $rejection_date, 
				$verifier_id, $verified_date, $completed_date, $faculty_id, $completion_comment, $rejection_comment, $rejection_date
				))) {
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
			
			$query = "	SELECT a.*, b.`task_id`, c.`verifier_id`, c.`verified_date`, a.`id` as `recipient_id`, c.`completed_date`, c.`faculty_id`, c.`completion_comment`, c.`rejection_comment`, c.`rejection_date`
						from `".AUTH_DATABASE."`.`user_data` a
						inner join `task_recipients` b on 
						(b.`recipient_type`='grad_year' and a.`grad_year`=b.`recipient_id`) 
						or (b.`recipient_type`='user' and a.`id` = b.`recipient_id`) 
						or (b.`recipient_type`='organisation' and b.`recipient_id`=a.`organisation_id`) 
						left join `task_completion` c on c.`task_id`=b.`task_id` and c.`recipient_id` = a.`id`
						where b.`task_id`=? and a.`id`=?";
			$result = $db->GetRow($query, array($task_id,$recipient_id));
			
			if ($result) {
				$task_v = self::fromArray($result);
			} 
		}
		return $task_v;
	}
	
	function __construct($task_id, $recipient_id, $verifier_id=null, $verified_date=null, $completed_date=null, $faculty_id=null, $completion_comment=null, $rejection_comment=null, $rejection_date=null) {
		$this->task_id = $task_id;
		$this->recipient_id = $recipient_id;
		$this->verifier_id = $verifier_id;
		$this->verified_date = $verified_date;
		$this->completed_date = $completed_date;
		$this->faculty_id = $faculty_id;
		$this->completion_comment = $completion_comment;
		$this->rejection_comment = $rejection_comment;
		$this->rejection_date = $rejection_date;

		$cache = SimpleCache::getCache();
		$cache->set($this,"TaskCompletion",$task_id);
	}
	
	public static function fromArray($arr) {
		return new self($arr['task_id'],$arr['recipient_id'],$arr['verifier_id'],$arr['verified_date'], $arr['completed_date'], $arr['faculty_id'], $arr['completion_comment'], $arr['rejection_comment'], $arr['rejection_date']);  
	}
}