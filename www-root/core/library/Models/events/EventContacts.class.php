<?php
require_once("Models/utility/Collection.class.php");
require_once("Models/users/User.class.php");

class EventContacts extends Collection {
	
	/**
	 * @return EventContacts
	 */
	static function get($event_id) {
		global $db;
		$query = "SELECT * from `event_contacts` where `event_id`=".$db->qstr($event_id);
		
		$results = $db->getAll($query);
		$contacts = array();
		if ($results) {
			foreach ($results as $result) {
				$contact = User::get($result['proxy_id']);
				
				if ($contact) {
					$contacts[] = $contact;
				}
			}
		}
		return new self($contacts);
	}
}