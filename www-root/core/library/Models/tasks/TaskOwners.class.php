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
		$query = "SELECT * from `task_owners` where `task_id`=".$db->qstr($task_id);
		
		$results = $db->getAll($query);
		$owners = array();
		if ($results) {
			foreach ($results as $result) {
				$otype = $result['owner_type'];
				$oid = $result['owner_id'];
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
	
	static function getCourse($task_id) {
		global $db;
		$query = "SELECT `owner_id` from `task_owners` where `task_id`=".$db->qstr($task_id) ." and `owner_type`=".$db->qstr(TASK_OWNER_COURSE);
		$result = $db->getOne($query);
		if ($result) {
			return Course::get($result);
		}
	}
	
	static public function add($task_id,$owners) {
		global $db;
		if (!is_array($owners)) { //single owner case?
			$owners = array($owners);
		}
		$query = "insert ignore into `task_owners` (`task_id`,`owner_id`, `owner_type` ) values ";
		$q_task_id = $db->qstr($task_id);
		$records = array();
		foreach($owners as $owner) {
			if ($owner instanceof User) {
				$owner_type = TASK_OWNER_USER;
				$owner_id = $owner->getID();
			} elseif ($owner instanceof Course) {
				$owner_type = TASK_OWNER_COURSE;
				$owner_id = $owner->getID();
			} elseif ($owner instanceof Organisation) {
				$owner_type = TASK_OWNER_EVENT;
				$owner_id = $owner->getID();
			} elseif (is_array($owner)) {
				//manually passing type and id
				$owner_type = $owner['type'];
				$owner_id = $owner['id'];
			} else {
				continue; //skip if invalid
			}
			
			$records[] = "(".$q_task_id.",".$db->qstr($owner_id)."," . $db->qstr($owner_type) . ")";
		}
		$query .= implode(",",$records);
		
		if(!$db->Execute($query)) {
			add_error("Failed to add owners to task");
			application_log("error", "Unable to create task_owners records. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added owners to task.");
		}
	}
}