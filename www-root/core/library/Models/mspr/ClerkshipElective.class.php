<?php

require_once("ClerkshipRotation.class.php");

class ClerkshipElective extends ClerkshipRotation {
	private $location;
	private $supervisor;
	
	function __construct($title, $location, $supervisor, $event_start, $event_finish) {
		parent::__construct($title, $event_start, $event_finish, $completed);
		$this->location = $location;
		$this->supervisor = $supervisor;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function getSupervisor() {
		return $this->supervisor;
	}
	
	public function getDetails() {
		$elements = array();
		$elements[] = parent::getDetails();
		$elements[] = $this->location;
		$elements[] = $this->supervisor;
		$details = implode("\n", $elements);
		return $details;
	}
}