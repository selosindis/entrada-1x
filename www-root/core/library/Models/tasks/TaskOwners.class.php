<?php
require_once("Collection.class.php");
require_once("Task.class.php");

class TaskOwners extends Collection {
	
	
	static function get($task_id) {
		$query = "SELECT * from `task_owner`";
		
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
				}
				$task =  new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['entry_year']);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
	}
}