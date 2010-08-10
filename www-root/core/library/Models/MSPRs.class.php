<?php

require_once("MSPR.class.php");

class MSPRs extends Collection {
	
	/**
	 * @return MSPRs
	 */
	static public function getAll() {
		global $db;
		$query		= "select * from `student_mspr` a 
						left join `".AUTH_DATABASE."`.`user_data` b 
						on a.user_id = b.id
						order by lastname, firstname";
		
		$results	= $db->GetAll($query);
		$msprs = array();
		if ($results) {
			foreach ($results as $result) {
				
				$user = new User($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['number'], $result['grad_year']);
				
				$mspr = new MSPR( $result['id'], $result['last_update'], $result['closed'], $result['generated']);
				$msprs[] = $mspr;
			}
		}
		return new self($msprs);
	}
	
	/**
	 * 
	 * @param int $year
	 * @return MSPRs
	 */
	static public function getYear($year) {
		global $db;
		$query		= "select * from `student_mspr` a 
						left join `".AUTH_DATABASE."`.`user_data` b 
						on a.user_id = b.id 
						where `grad_year`=".$db->qstr($year)."  
						order by lastname, firstname";
		
		$results	= $db->GetAll($query);
		$msprs = array();
		if ($results) {
			foreach ($results as $result) {
				
				$user = new User($result['id'], $result['username'], $result['lastname'], $result['firstname'], $result['number'], $result['grad_year']);
				
				$mspr = new MSPR( $result['id'], $result['last_update'], $result['closed'], $result['generated']);
				$msprs[] = $mspr;
			}
		}
		return new self($msprs);
	}
}

class MSPRClassData {
	private $year;
	private $closed;
	
	public function __construct($year, $closed) {
		$this->year = $year;
		$this->closed = $closed;
	} 
	
	public function getClosedTimestamp() {
		return $this->closed;
	}
	
	public function getClassYear() {
		return $this->year;
	}
	

	/**
	 * Returns the meta-data for this class. At this point just the close date/time.
	 * @return MSPRClassData
	 */
	static public function get($year) {
		global $db;
		$query		= "select * from `student_mspr_class` 
						where `year`=".$db->qstr($year);
		
		$result = $db->getRow($query);
		if ($result) {
			return new self($result['year'],$result['closed']);
		}
	}
	
	public static function create($year, $closed = null) {
		global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;
		
		$query = "insert into `student_mspr_class` (`year`, `closed`) value (".$db->qstr($year).", ".$db->qstr($closed).")";
		if(!$db->Execute($query)) {
			$ERROR++;
			$ERRORSTR[] = "Failed to create new MSPR Class.";
			application_log("error", "Unable to update a student_mspr_class record. Database said: ".$db->ErrorMsg());
		} else {
			$SUCCESS++;
			$SUCCESSSTR[] = "Successfully created new MSPR Class.";
		}
	}
}