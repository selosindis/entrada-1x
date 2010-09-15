<?php
require_once("Models/utility/Collection.class.php");
require_once("Task.class.php");
require_once("Models/users/User.class.php");
require_once("Models/courses/Course.class.php");
require_once("Models/events/Event.class.php");

class TaskOwners extends Collection {
	
	/**
	 * @return TaskOwners
	 */
	static function get($task_id) {
		global $db;
		$query = "SELECT * from `task_owner` where `task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$owners = array();
		if ($results) {
			foreach ($results as $result) {
				$otype = $result['owner_type'];
				$oid = $this->owner_id;
				switch($otype) {
					case TASK_OWNER_USER:
						$owner = User::get($oid);
						break;
					case TASK_OWNER_COURSE:
						$owner = Course::get($oid);
						break;
					case TASK_OWNER_EVENT:
						$owner = Event::get($oid);
						break;
					default:
						$owner = null; // not a valid owner type. ensures we don't use the same owner as the last run through the loop.
				}
				if ($owner) {
					$owners[] = $owner;
				}
			}
		}
		return new self($owners);
	}
}