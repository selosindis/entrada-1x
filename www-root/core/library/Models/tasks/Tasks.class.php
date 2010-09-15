<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("Task.class.php");
require_once("Models/utility/Collection.class.php");

/**
 * Utility Class for getting a list of Courses
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class Tasks extends Collection {
	
	/**
	 * Returns a Collection of Task objects
	 * @param array
	 * @return Tasks
	 */
	static private function getAll( ) {
		global $db;
		$ORGANISATION_ID	= $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
		
		$query = "SELECT * from `tasks` where `organization_id`=".$db->qstr($ORGANISATION_ID);
		
		$results = $db->getAll($query);
		$tasks = array();
		if ($results) {
			foreach ($results as $result) {
				$task = new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['deadline'], $result['duration'], $result['description'],$result['release_start'], $result['release_finish'], $result['organisation_id']);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
	}
	
	/**
	 * Returns a Collection of Tasks 
	 * @param int $award_id
	 * @return Tasks
	 */
	static public function get($obj = null) {
		if ($obj instanceof User) {
			$tasks = self::getByOwner($obj);
		} elseif ($obj instanceof Course) {
			$tasks = self::getByCourse($obj);
		} elseif ($obj instanceof Event) {
			$tasks = self::getByEvent($obj);
		} else {
			$tasks = self::getAll();
		}
		return $receipts;
	}
	
	static private function getByOwner(User $user) {
		return self::getByOwnerType(TASK_OWNER_USER,$user->getID());
	}
	
	static private function getByCourse(Course $course) {
		return self::getByOwnerType(TASK_OWNER_COURSE, $course->getID());
	}
	
	static private function getByEvent(Event $event) {
		return self::getByOwnerType(TASK_OWNER_EVENT,$event->getID());
	}
	
	static private function getByOwnerType($owner_type, $owner_id) {
		global $db;
		$query = "SELECT * from `tasks` a left join `task_owners` b on a.`task_id`=b.`task_id` where b.`owner_type`=".$db->qstr($owner_type)." AND b.`owner_id`=".$db->qstr($owner_id);
		$results = $db->getAll($query);
		$tasks = array();
		if ($results) {
			foreach ($results as $result) {
				$task = new Task($result['task_id'], $result['last_updated_date'], $result['last_updated_by'], $result['title'], $result['deadline'], $result['duration'], $result['description'],$result['release_start'], $result['release_finish'], $result['organisation_id']);
				$tasks[] = $task;
			}
		}
		return new self($tasks);
		
	}

}