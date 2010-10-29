<?php

require_once("Entrada/mspr/functions.inc.php");

/**
 * Processes the various sections of the MSPR module. Retrieves section specifiction from $_GET. 
 */
function process_mspr_profile($user) {
	if (isset($_GET['mspr-section']) && $section = $_GET['mspr-section']) {
	
		switch($section) {
			
			case 'external_awards':
				process_external_awards_profile($user);
				display_status_messages();
				$external_awards = ExternalAwardReceipts::get($user);
				echo display_external_awards_profile($external_awards);
			break;
			
			case 'contributions':
				process_contributions_profile($user);
				display_status_messages();
				$contributions = Contributions::get($user);
				echo display_contributions_profile($contributions);
			break;
			
			case 'critical_enquiry':
				process_critical_enquiry_profile($user);
				display_status_messages();
				$critical_enquiry = CriticalEnquiry::get($user);
				echo display_supervised_project_profile($critical_enquiry);
			break;
			
			case 'community_health_and_epidemiology':
				process_community_health_and_epidemiology_profile($user);
				display_status_messages();
				$community_health_and_epidemiology = CommunityHealthAndEpidemiology::get($user);
				echo display_supervised_project_profile($community_health_and_epidemiology);
			break;
			
			case 'research_citations':
				process_research_citations_profile($user);
				display_status_messages();
				$research_citations = ResearchCitations::get($user);
				echo display_research_citations_profile($research_citations);
			break;
		}
	}
}

/**
 * Processes the external awards commands from $_POST for $user. NOTE: Does NOT do auth check. Do so prior to caling this.
 * @param User $user
 */
function process_external_awards_profile(User $user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST['title']) ? $_POST['title'] : '');
			$body = (isset($_POST['body']) ? $_POST['body'] : '');
			$terms = (isset($_POST['terms']) ? $_POST['terms'] : '');
			$year = (isset($_POST['year']) ? $_POST['year'] : '');
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			if ($user_id && $title && $terms && $year && $body && ($user->getID() == $user_id)) {
				ExternalAwardReceipt::create($user_id,$title, $terms, $body, $year);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$recipient = ExternalAwardReceipt::get($id);
				if ($recipient) {
					$recipient->delete();
				}
			}
		}
	}
}

/**
 * Processes the contributions to medical school commands from $_POST for $user. NOTE: Does NOT do auth check. Do so prior to caling this.
 * @param User $user
 */
function process_contributions_profile(User $user) {
	$action = $_POST['action'];
	if (isset($_POST['action'])) {
		if ($action == "Add") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$role = (isset($_POST['role']) ? $_POST['role'] : '');
			$org_event = (isset($_POST['org_event']) ? $_POST['org_event'] : '');
			$date = (isset($_POST['date']) ? $_POST['date'] : '');
			$start_year = $_POST['start_year'];
				
			if (($user_id == $user->getID()) && $role && $org_event && $start_year) {
				$end_year = $_POST['end_year'];
				$start_month = $_POST['start_month'];
				$end_month = $_POST['end_month'];

				Contribution::create($user,$role, $org_event, $start_month, $start_year, $end_month, $end_year);
			}
		
		} elseif ($action == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$contribution = Contribution::get($id);
				if ($contribution) {
					$contribution->delete();
				}
			}
		} 
	}
}

/**
 * Processes the critical enquiry commands from $_POST for $user. NOTE: Does NOT do auth check. Do so prior to caling this.
 * @param User $user
 */
function process_critical_enquiry_profile(User $user) {
	$section = 'critical_enquiry';
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Update") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST['title']) ? $_POST['title'] : '');
			$organization = (isset($_POST['organization']) ? $_POST['organization'] : '');
			$location = (isset($_POST['location']) ? $_POST['location'] : '');
			$supervisor = (isset($_POST['supervisor']) ? $_POST['supervisor'] : '');
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			if ($user_id && $title && $organization && $supervisor && $location) {
				CriticalEnquiry::create($user_id,$title, $organization, $location, $supervisor);
			}
		
		}
	}
}

/** 
 * Processes the critical enquiry commands from $_POST for $user. NOTE: Does NOT do auth check. Do so prior to caling this.
 * @param User $user
 */
function process_community_health_and_epidemiology_profile(User $user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Update") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST['title']) ? $_POST['title'] : '');
			$organization = (isset($_POST['organization']) ? $_POST['organization'] : '');
			$location = (isset($_POST['location']) ? $_POST['location'] : '');
			$supervisor = (isset($_POST['supervisor']) ? $_POST['supervisor'] : '');
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			if ($user_id && $title && $organization && $supervisor && $location) {
				CommunityHealthAndEpidemiology::create($user_id,$title, $organization, $location, $supervisor);
			}
		
		}
	}
}

function process_research_citations_profile(User $user) {
	$action = $_POST['action'];
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$citation = clean_input((isset($_POST['details']) ? $_POST['details'] : ''), array("notags", "specialchars"));
				
			if (($user_id == $user->getID()) && $citation ) {
				ResearchCitation::create($user_id,$citation);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$citation = ResearchCitation::get($id);
				if ($citation) {
					$citation->delete();
				}
			}
		} elseif ($_POST['action'] == "Edit") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			$text = clean_input((isset($_POST['details']) ? $_POST['details'] : ''), array("notags", "specialchars"));
			
			if ($id) {
				$citation = ResearchCitation::get($id);
				if ($citation) {
					$citation->update($text);
				}
			}
		} elseif ($action == "resequence") {
			$ids = (isset($_POST['research_citations']) ? $_POST['research_citations'] : null);
			if ($ids) {
				ResearchCitations::setSequence($user,$ids);
			}
		}
	}
}

/**
 * Returns a table containing received internal academic awards
 * @param InternalAwardReceipts $received_awards
 * @return string 
 */
function display_internal_awards_profile(InternalAwardReceipts $receipts = null) {
	return display_internal_awards($receipts,"public");
}

/**
 * Returns a table containing received studentships and details
 * @param Studentships $studentships
 * @return string
 */
function display_studentships_profile(Studentships $studentships = null) {
	return display_studentships($studentships,"public");
}

/**
 * Outputs a table with awards for a given student. Includes profile functions
 * @param ExternalAwardReceipts $receipts
 * @return string
 */
function display_external_awards_profile(ExternalAwardReceipts $receipts, $view_mode = false) {
	return display_external_awards($receipts, "public", $view_mode);
}

/**
 * Returns a table containing submitted contributions with approval status indicated.
 * @param Contributions $contributions
 * @return string
 */
function display_contributions_profile(Contributions $contributions,$view_mode=false) {
	return display_contributions($contributions,"public",$view_mode);
}

/**
 * Returns a table containing clinical performance evaluation comments.
 * @param ClinicalPerformanceEvaluations $clinevals
 * @return string
 */
function display_clineval_profile(ClinicalPerformanceEvaluations $clinevals = null) {
	return display_clineval($clinevals,"public");
}

/**
 * Returns a table containing student-run-electives.
 * @param StudentRunElectives $sres
 * @return string
 */
function display_student_run_electives_profile(StudentRunElectives $sres=null) {
	return display_student_run_electives($sres,"public");
}

/**
 * Returns an HTML table containing Observerships.
 * @param Observerships $obss
 * @return string
 */
function display_observerships_profile(Observerships $obss=null) {
	return display_observerships($obss,"public");
}

/**
 * Returns an HTML table containing Observerships.
 * @param Observerships $obss
 * @return string
 */
function display_international_activities_profile(InternationalActivities $int_acts) {
	return display_international_activities($int_acts,"public");
}

/**
 * Returns a single-row-table (for consistency of formatting and markup) containing the critical entry project details.
 * @param CriticalEnquiry $critical_enquiry
 * @return string
 */
function display_supervised_project_profile(SupervisedProject $project = null) {
	return display_supervised_project($project,"public",true);
}

function display_research_citations_profile(ResearchCitations $research_citations, $view_mode=false) {
	return display_research_citations($research_citations,"public",$view_mode);
}