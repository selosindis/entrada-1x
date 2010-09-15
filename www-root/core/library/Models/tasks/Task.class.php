<?php


require_once("User.class.php");
require_once("Course.class.php");
require_once("Event.class.php");
require_once("SimpleCache.class.php");

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
	 * 
	 */
	function getLasUpdateUser() {
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
	
	function getOrganisationID() {
		return $this->organisation_id;
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
	 * 
	 * @return int
	 */
	static function create($creator_id, $title, $owner_type, $owner_id, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null) {
		
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
	function update($updater_id, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null) {
		
	}
	
	/**
	 * Deletes the task record AND associated audience, owners, and verification
	 */
	function delete() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "DELETE `tasks`, `task_owner`, `task_audience`, `task_verified`
					FROM `tasks` a left join `task_owner` b left join `task_audience` c left join `task_verified` d
					where a.`task_id` = b.`task_id` and 
					a.`task_id` = c.`task_id` and
					a.`task_id` = d.`task_id` and
					a.`task_id`=".$db->qstr($this->task_id);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to remove task from database.";
			application_log("error", "Unable to delete a tasks record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully removed task.";
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
			$query = "SELECT * FROM `tasks` WHERE `id` = ".$db->qstr($task_id);
			$result = $db->getRow($query);
			if ($result) {
				$task = new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['deadline'], $result['duration'], $result['description'],$result['release_start'], $result['release_finish'], $result['organisation_id']);			
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
	 */
	function __construct($task_id, $last_updated_date, $last_updated_by, $title, $deadline, $duration = 0, $description = "", $release_start = null, $release_finish = null, $organisation_id=null) {
		$this->$task_id = $task_id;
		$this->last_updated_date = $last_updated_date;
		$this->last_updated_by = $last_updated_by;
		$this->title = $title;
		$this->deadline = $deadline;
		$this->duration = $duration;
		$this->description = $description;
		$this->release_start = $release_start;
		$this->release_finish = $release_finish;
		$this->organisation_id = $organisation_id;
		
		$cache = SimpleCache::getCache();
		$cache->set($this,"Task", $task_id);
	}
}