<?php

class InternationalActivity {
	private $id;
	private $student_id;
	private $title;
	private $site;
	private $start;
	private $end;
	private $location;
	
	function __construct($id, $student_id, $title, $site, $location, $start, $end) {
		$this->id = $id;
		$this->student_id = $student_id;
		$this->title = $title;
		$this->site = $site;
		$this->location = $location;
		$this->start = $start;
		$this->end = $end;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getStudentID() {
		return $this->student_id;	
	}

	public function getSite() {
		return $this->site;
	}
	
	public function getLocation () {
		return $this->location;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getDetails() {
		$elements = array();
		$elements[] = $this->title;
		$elements[] = $this->site . ", " . $this->location;
		$details = implode("\n", $elements);
		return $details;
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function getEnd() {
		return $this->end;
	}
	
	public function getStartDate() {
		return array(
			"d" => date("j", $this->start),
			"m" => date("n", $this->start),
			"y" => date("Y", $this->start)
		);
	}
	
	public function getEndDate() {
		return array(
			"d" => date("j", $this->end),
			"m" => date("n", $this->end),
			"y" => date("Y", $this->end)
		);
	}
	
	public function getPeriod() {
		return formatDateRange($this->getStartDate(), $this->getEndDate()); 
	}
	
	public static function get($id) {
		global $db;
		$query		= "SELECT * FROM `student_international_activities` WHERE `id` = ".$db->qstr($id);
		$result = $db->getRow($query);
		if ($result) {
			
			$obs =  new InternationalActivity($result['id'], $result['student_id'], $result['title'], $result['site'], $result['location'], $result['start'], $result['end']);
			return $obs;
		}
	} 

	public static function create($user, $title, $site, $location, $start, $end) {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$student_id = $user->getID();
		$query = "insert into `student_international_activities` (`student_id`, `title`,`site`,`location`,`start`, `end`) value (".$db->qstr($student_id).", ".$db->qstr($title).", ".$db->qstr($site).", ".$db->qstr($location).", ".$db->qstr($start).", ".$db->qstr($end).")";
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to create new International Activity.";
			application_log("error", "Unable to update a student_international_activity record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully added new International Activity.";
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	public function delete() {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		$query = "DELETE FROM `student_international_activities` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to remove International Activity from database.";
			application_log("error", "Unable to delete a student_international_activity record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully removed International Activity.";
		}		
	}
}