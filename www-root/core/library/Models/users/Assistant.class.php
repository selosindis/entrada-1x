<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

class Assistant extends User {
	private $permission_id;
	private $assigned_to;
	private $valid_from;
	private $valid_until;
	
	private function addAssistantFields(array $arr) { 
		$this->permission_id = $arr['permission_id'];
		$this->assigned_to = $arr['assigned_to'];
		$this->valid_from = $arr['valid_from'];
		$this->valid_to = $arr['valid_to'];
	}

	public static function fromArray(array $arr) {
		$asst = new self(); 
		$asst = parent::fromArray($arr, $asst);
		$asst->addAssistantFields($arr);
		return $asst;
	}
}
