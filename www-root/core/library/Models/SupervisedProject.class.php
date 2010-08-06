<?php

abstract class SupervisedProject implements Approvable,AttentionRequirable {
	private $user_id;
	private $location;
	private $organization;
	private $title;
	private $supervisor;
	private $approved;
	private $rejected;
	
	function __construct($user_id, $title, $organization, $location, $supervisor, $approved = false, $rejected = false) {
		$this->user_id = $user_id;
		$this->location = $location;
		$this->title = $title;
		$this->organization = $organization;
		$this->supervisor = $supervisor;
		$this->approved  = (bool)$approved;
		$this->rejected = (bool)$rejected;
	}
	
	public function getID() {
		return $this->user_id;
	}
	
	public function getUserID() {
		return $this->user_id;	
	}

	public function getLocation () {
		return $this->location;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getSupervisor() {
		return $this->supervisor;
	}
	
	public function getOrganization() {
		return $this->organization;
	}
	
	public function getDetails() {
		$elements = array();
		$elements[] = '"'.$this->title.'"';
		$elements[] = $this->organization;
		$elements[] = $this->location;
		$elements[] = 'Supervisor: '.$this->supervisor;
		
		$details = implode("\n", $elements);
		return $details;
	}
	
	public function isApproved() {
		return (bool)($this->approved);
	}	
	
	/**
	 * Requires attention if not approved, unless rejected
	 * @see www-root/core/library/Models/AttentionRequirable#isAttentionRequired()
	 */
	public function isAttentionRequired() {
		return !$this->isApproved() && !$this->isRejected();
	}
	
	public function isRejected() {
		return (bool)($this->rejected);
	}
	
}