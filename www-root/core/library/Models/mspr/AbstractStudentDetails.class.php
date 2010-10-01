<?php
abstract class AbstractStudentDetails {
	protected $id;
	protected $user;
	protected $details;
		
	public abstract function delete();
	
	public function getID() {
		return $this->id;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getDetails() {
		return $this->details;
	}
	
}