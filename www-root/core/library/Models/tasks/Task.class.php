<?php


require_once("Models/users/User.class.php");
require_once("Models/courses/Course.class.php");
require_once("Models/events/Event.class.php");
require_once("Models/organisations/Organisation.class.php");
require_once("Models/utility/SimpleCache.class.php");
require_once("Models/tasks/TaskOwners.class.php");
require_once("Models/tasks/TaskRecipients.class.php");
require_once("Models/tasks/TaskCompletions.class.php");
require_once("TaskOwners.class.php");

class Task {
	/**
	 * @var int
	 */
	private $task_id;
		
	/**
	 * Task Title. limited to 255 characters
	 * @var Title
	 */
	private $title;
	
	/**
	 * Description of Task. Limited to 65535 characters
	 * @var unknown_type
	 */
	private $description;
	
	/**
	 * timestamp for when the task should appear to the audience. does not affect visibility for owner
	 * @var int
	 */
	private $release_start;
	
	/**
	 * timestamp for when the task should no longer appear to the audience. does not affect visibility for owner. Should never be earlier than the deadline
	 * @var int
	 */
	private $release_finish;
	
	/**
	 * timestamp for indicating when audience members *should* complete the task by. deadline does not impose constraints in the system; this is intended to be a real world deadline.
	 * @var deadline
	 */
	private $deadline;
	
	/**
	 * Suggested task length in minutes. e.g. 60 minutes. 
	 * @var int
	 */
	private $duration;
	
	/**
	 * timestamp indicating when the task was last updated
	 * @var int
	 */
	private $last_updated;
	
	/**
	 * proxy_id of the user that last updated this task.
	 * @var int
	 */
	private $last_updated_by;
	
	
	/**
	 * id of organisation to which this task belongs.
	 * @var int
	 */
	private $organisation_id;
	
	/**
	 * Boolean status of verfification requirement. True if required.
	 * @var bool
	 */
	private $require_verification;
	
	/**
	 * Returns a User, Course, or Event depending on the owner type
	 * @return mixed
	 */
	function getOwners() {
		return TaskOwners::get($this->task_id);
	}
	
	/**
	 * Returns the unique id of this task
	 * @return int
	 */
	function getID(){
		return $this->task_id;
	}
	
	/**
	 * Returns the title of the task.
	 * @return string
	 */
	function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the description of the task.
	 * @return string
	 */
	function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns the Deadline set for this task as a timestamp.
	 * @return int
	 */
	function getDeadline() {
		return $this->deadline;
	}
	
	/**
	 * Returns the expected duration required for task completion in minutes
	 * @return int
	 */
	function getDuration() {
		return $this->duration;
	}
	
	/**
	 * Returns a timestamp of the time this information was last updated
	 * @return int
	 */
	function getLastUpdate() {
		return $this->last_updated;
	}
	
	/**
	 * Returns the User that last updated this information
	 * @return User
	 */
	function getLastUpdateUser() {
		return User::get($this->last_updated_by);
	}
	
	/**
	 * Returns the timestamp for when this Task should be made available to the audience. 0 or null if not set.
	 * @return int 
	 */
	function getReleaseStart() {
		return $this->release_start;
	}
	
	/**
	 * Returns the timestamp for when this Task should no longer be available to the audience. 0 or null if not set.
	 * @return int 
	 */
	function getReleaseFinish() {
		return $this->release_finish;
	}
	
	/**
	 * @return Organisation
	 */
	function getOrganisation() {
		return Organisation::get($this->organisation_id);
	}
	
	/**
	 * @return Course
	 */
	function getCourse() {
		return TaskOwners::getCourse($this->task_id);
	} 
	
	/**
	 * 
	 * @param $obj
	 * 
	 * @return bool
	 */
	function addOwner($obj) {
		TaskOwners::add($this,$obj);
	}
	
	/**
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	function isOwner(User $user) {
		$task_owners = $this->getOwners();
		if ($task_owners->count() == 0) {
			//no owners? orphan?
			application_log("error", "A task was found to have no owners associated with it. Task ID: ".$task_id);
			return false;
		}
		
		foreach($task_owners as $task_owner) {
			if (($task_owner instanceof User) && ($task_owner === $user)  ) {
				return true;
			} else if(($task_owner instanceof Course) && ($task_owner->isOwner($user))) {
				return true;
			} else if(($task_owner instanceof Event) && ($task_owner->isOwner($user))) {
				return true;
			}
		}
	}
	
	/**
	 * 
	 * @param User $user
	 * 
	 * @return bool
	 */
	function isRecipient(User $user) {
		$task_recipients = TaskRecipients::get($this->task_id);
		
		if ($task_recipients->count() == 0) {
			application_log("error", "A task was found to have no recipients associated with it. Task ID: ".$task_id);
			return false;
		}
		foreach($task_recipients as $task_recipient) {
			if (($task_recipient instanceof User) && ($task_recipient === $user)  ) {
				return true;
			} else if(($task_recipient instanceof GraduatingClass) && ($user->getGraduatingClass() == $task_recipient)) {
				return true;
			} else if(($task_recipient instanceof Organisation) && ($task_recipient == $user->getOrganisation())) {
				return true;
			}
		}
	}
	
	/**
	 * Returns true if the supplied user is a verifier for any of the recipients
	 * @param $user
	 * 
	 * @return bool
	 */
	function isVerifier(User $user) {
		return TaskCompletions::isVerifier($user->getID(), $this->task_id);
	}
	
	/**
	 * @return bool
	 */
	function isVerificationRequired() {
		return $this->require_verification;
	}
		
	/**
	 * Allows sorting by 'deadline', 'course', and 'title'
	 * @param Task $task
	 * @param string $sort_by
	 */
	function compare(Task $task, $compare_by='title') {
		switch($compare_by) {
			case 'deadline':
				return $this->deadline == $task->deadline ? 0 : ( $this->deadline > $task->deadline ? 1 : -1 );
				break;
			case 'title':
				return strcasecmp($this->title, $task->title);
				break;
			case 'course':
				$thiscourse = $this->getCourse();
				$thatcourse = $task->getCourse();
				$thistitle = ($thiscourse) ? $thiscourse->getTitle() : "";
				$thattitle = ($thatcourse) ? $thatcourse->getTitle() : "";
				return strcasecmp($thiscourse->getTitle(),$thatcourse->getTitle());
				break;
		}
	} 
	
	/**
	 * Creates a new task. Returns new task_id
	 * 
	 * @param int $creator_id
	 * @param string $title
	 * @param int $deadline
	 * @param int $duration
	 * @param string $description
	 * @param int $release_start
	 * @param int $release_finish
	 * @param int $organisation_id
	 * @param bool $require_verification
	 * 
	 * @return int
	 */
	static function create($creator_id, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null, $require_verification=0) {
		global $db;
		$query = "insert into `tasks` (`updated_by`, `updated_date`, `title`, `deadline`,`duration`,`description`, `release_start`, `release_finish`, `organisation_id`, `require_verification`) 
				value (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		if(!$db->Execute($query, array($creator_id, time(), $title, $deadline, $duration, $description, $release_start, $release_finish, $organisation_id, $require_verification))) {
			add_error("Failed to create Task".$db->ErrorMsg());
			application_log("error", "Unable to update a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully created task.");
			return ($db->Insert_ID('tasks','task_id'));
		}
	} 
	
	/**
	 * 
	 * @param int $updater_id
	 * @param string $title
	 * @param int $deadline
	 * @param int $duration
	 * @param string $description
	 * @param int $release_start
	 * @param int $release_finish
	 */
	function update($updater_id, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null, $require_verification=0) {
		global $db;
		$query = "UPDATE `tasks` set `updated_by`=?, `updated_date`=?, `title`=?, `deadline`=?, `duration`=?, `description`=?, `release_start`=?, `release_finish`=?, `organisation_id`=?, `require_verification`=? where `task_id`=?";
		
		if(!$db->Execute($query, array($updater_id, time(), $title, $deadline, $duration, $description, $release_start, $release_finish, $organisation_id, $require_verification, $this->task_id))) {
			add_error("Failed to update Task");
			application_log("error", "Unable to update a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated task.");
		}
	}
	
	/**
	 * Deletes the task record AND associated audience, owners, and verification
	 */
	function delete() {
		global $db;
		$query = "	DELETE a,b,c,d
					FROM `tasks` a 
					left join `task_owners` b on a.`task_id`=b.`task_id` 
					left join `task_completion` c on a.`task_id`=c.`task_id`
					left join `task_recipients` d on a.`task_id`=d.`task_id`
					where a.`task_id` = ?";
		if(!$db->Execute($query, array($this->task_id))) {
			add_error("Failed to remove task from database.");
			application_log("error", "Unable to delete a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed task.");
		}		
	}
	
	/**
	 * 
	 * @param int $task_id
	 * @return Task
	 */
	static function get($task_id) {
		$cache = SimpleCache::getCache();
		$task = $cache->get("Task",$task_id);
		if (!$task) {
			global $db;
			$query = "SELECT * FROM `tasks` WHERE `task_id` = ".$db->qstr($task_id);
			$result = $db->getRow($query);
			if ($result) {
				$task = self::fromArray($result);		
			}		
		} 
		return $task;
	}
	
	/**
	 * 
	 * @param int $task_id
	 * @param int $last_updated_date
	 * @param int $last_updated_by
	 * @param string $title
	 * @param int $deadline
	 * @param int $duration
	 * @param string $description
	 * @param int $release_start
	 * @param int $release_finish
	 * @param int $organisation_id
	 * @param bool $require_verification
	 */
	function __construct($task_id, $last_updated_date, $last_updated_by, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null, $require_verification=0) {
		$this->task_id = $task_id;
		$this->last_updated_date = $last_updated_date;
		$this->last_updated_by = $last_updated_by;
		$this->title = $title;
		$this->deadline = $deadline;
		$this->duration = $duration;
		$this->description = $description;
		$this->release_start = $release_start;
		$this->release_finish = $release_finish;
		$this->organisation_id = $organisation_id;
		$this->require_verification = (bool) $require_verification;
		
		$cache = SimpleCache::getCache();
		$cache->set($this,"Task", $task_id);
	}
	
	static public function fromArray($arr) {
		return new Task($arr['task_id'], $arr['last_updated_date'], $arr['last_updated_by'], $arr['title'], $arr['deadline'], $arr['duration'], $arr['description'],$arr['release_start'], $arr['release_finish'], $arr['organisation_id'], $arr['require_verification']);
	}
}