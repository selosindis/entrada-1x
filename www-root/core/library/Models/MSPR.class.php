<?php

require_once("User.class.php");
require_mspr_models();

class MSPR implements ArrayAccess, AttentionRequirable {
	private $closed;
	private $generated;
	private $last_update;
	private $user_id;
	private $models = array ( // Title => Class
							"Internal Awards" => "InternalAwardReceipts",
							"External Awards" => "ExternalAwardReceipts",
							"Studentships" => "Studentships",
							"Clinical Performance Evaluation Comments" => "ClinicalPerformanceEvaluations",
							"Contributions to Medical School" => "Contributions",
							"Disciplinary Actions" => "DisciplinaryActions",
							"Leaves of Absence" => "LeavesOfAbsence",
							"Formal Remediation Received" => "FormalRemediations",
							"Student-Run Electives" => "StudentRunElectives",
							"Observerships" => "Observerships",
							"International Activities" => "InternationalActivities",
							"Critical Enquiry" => "CriticalEnquiry",
							"Community Health and Epidemiology" => "CommunityHealthAndEpidemiology",
							"Research" => "ResearchCitations",
							"Clerkship Core Completed" => "ClerkshipCoreCompleted",
							"Clerkship Core Pending" => "ClerkshipCorePending",
							"Clerkship Electives Completed" => "ClerkshipElectivesCompleted"
							);
	
	function __construct($user_id, $last_update, $closed = NULL, $generated = NULL) {
		$this->user_id = $user_id;
		$this->last_update = $last_update;
		$this->closed = $closed;
		$this->generated = $generated;
	}
	
	function getUser() {
		return User::get($this->user_id);
	}
	
	/**
	 * Returns true if the closed timestamp exceeds the 
	 */
	function isClosed() {
		return (!is_null($this->closed) && $this->closed < time());
	}
	
	/**
	 * Sets the scheduled closed timestamp
	 * @param $timestamp
	 */
	function close($timestamp) {
		
	}
	
	/**
	 * Clears the scheduled closed timestamp
	 */
	function open() {
		
	}
	
	function isGenerated() {
		return (!is_null($this->generated) && $this->generated < time());
	}
	
	function generate() {
		//TODO call document generation
	}
	
	/**
	 * Returns a timestamp of submission closure
	 */
	function getClosedTimestamp() {
		return $this->closed;
	}
	
	/**
	 * Returns a timestamp of the last mspr generation
	 */
	function getGeneratedTimestamp() {
		return $this->generated;
	}
	
	function getComponent($component) {
		if (array_key_exists($component, $this->models)) {
			$component_class = $this->models[$component];
			return call_user_func($component_class."::get", $this->getUser());
		}
	}
	
	function isAttentionRequired() {
		$user = $this->getUser();
		//get all student entered data;
		$att_reqs[] = CriticalEnquiry::get($user);
		$att_reqs[] = ExternalAwardReceipts::get($user);
		$att_reqs[] = Contributions::get($user);
		$att_reqs[] = CommunityHealthAndEpidemiology::get($user);
		$att_reqs[] = ResearchCitations::get($user);
		foreach ($att_reqs as $att_req) {
			if ($att_req && $att_req->isAttentionRequired()) return true;
		}
		return false;	
	}
	
	public function offsetSet($offset, $value) {
        //cannot set
    }
    public function offsetExists($key) {
        return array_key_exists($key, $this->models);
    }
    public function offsetUnset($offset) {
       //cannot unset
    }
    public function offsetGet($key) {
        return $this->getComponent($key);
    }
	
    /**
     * 
     * @param $user
     * @return MSPR
     */
    public static function get(User $user) {
    	global $db;
		$user_id = $user->getID();
		$query		= "SELECT * FROM `student_mspr` WHERE `user_id` = ".$db->qstr($user_id);
		$result = $db->getRow($query);
		if ($result) {
			$mspr =  new self($result['user_id'], $result['last_update'], $result['closed'], $result['generated']);
			return $mspr;
		}    	
    }
    
    public static function create(User $user, $closed_ts = NULL) {
    	global $db,$SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR;

		$user_id = $user->getID();
		$query = "insert into `student_mspr` (`user_id`, `closed`) value (".$db->qstr($user_id).", ".$db->qstr($closed).")";
		
		if(!$db->Execute($query)) {
			application_log("error", "Unable to update a student_mspr record. Database said: ".$db->ErrorMsg());
			return false;
		} else {
			return true;
		}
    } 
}