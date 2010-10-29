<?php
require_once("Models/utility/Template.class.php");
require_once("Entrada/mspr/functions.inc.php");

class MSPRAdminController {
	
	private $_translator;
	
	private $_user;
	
	private $type;
	
	function __construct($translator, User $forUser) {
		$this->_translator = $translator;
		$this->_user = $forUser;
		$this->type="admin";
	}
	
	public function process() {
		$user = $this->_user;
		$translator = $this->_translator;
		$type = $this->type;

		static $valid = array(
								"studentships" => array("add", "remove"),
								"clineval" => array("add","remove", "edit"),
								"internal_awards" => array("add","remove"),
								"student_run_electives" => array("add","remove"),
								"observerships" => array("add","remove", "edit"),
								"int_acts" => array("add","remove"),
								"external_awards" => array("approve","unapprove","reject"),
								"contributions" => array("approve","unapprove","reject"),
								"critical_enquiry" => array("approve","unapprove","reject"),
								"community_health_and_epidemiology" => array("approve","unapprove","reject"),
								"research_citations" => array("approve","unapprove","reject")
								);
								
		$section =  clean_input((isset($_GET['mspr-section']) ? $_GET['mspr-section'] : ""), array("lower", "trim"));
		
		if ($section) {
			$entity_id = clean_input((isset($_POST['entity_id']) ? $_POST['entity_id'] : 0), array("int"));
			$action = clean_input((isset($_POST['action']) ? $_POST['action'] : ""), array("lower"));
			$comment = clean_input((isset($_POST['comment']) ? $_POST['comment'] : ""), array("notags"));
			$user_id = clean_input((isset($_POST['user_id']) ? $_POST['user_id'] : 0), array("int"));
			
			if (!$action) {
				add_error($translator->translate("mspr_no_action"));
			}
			if (!array_key_exists($section, $valid)) {
				add_error($translator->translate("mspr_invalid_section"));	
			} else {
				if (!in_array($action, $valid[$section])){
					add_error($translator->translate("mspr_invalid_action"));
				}
			}
			
			if (($action == "reject") && (MSPR_REJECTION_REASON_REQUIRED)) {
				if (!$comment) {
					add_error($translator->translate("mspr_no_reject_reason"));
				}
			}
			
			if (!has_error() && ($action != "add")) {
				if (!$entity_id) {
					add_error($translator->translate("mspr_no_entity"));
				} else {
					switch($section) {
						case 'studentships':
							$entity = Studentship::get($entity_id);
							break;
						case 'clineval':
							$entity = ClinicalPerformanceEvaluation::get($entity_id);
							break;
						case 'internal_awards':
							$entity = InternalAwardReceipt::get($entity_id);
							break;
						case 'external_awards':
							$entity = ExternalAwardReceipt::get($entity_id);
							break;
						case 'contributions':
							$entity = Contribution::get($entity_id);
							break;
						case 'student_run_electives':
							$entity = StudentRunElective::get($entity_id);
							break;
						case 'observerships':
							$entity = Observership::get($entity_id);
							break;
						case 'int_acts':
							$entity = InternationalActivity::get($entity_id);
							break;
						case 'critical_enquiry':
							$entity = CriticalEnquiry::get($entity_id);
							break;
						case 'community_health_and_epidemiology':
							$entity = CommunityHealthAndEpidemiology::get($entity_id);
							break;
						case 'research_citations':
							$entity = ResearchCitation::get($entity_id);
							break;
					}
					if (!$entity) {
						add_error($translator->translate("mspr_invalid_entity"));
					}
					
					if (!has_error()) {
						switch($action) {
							case "approve":
								$entity->approve();
								break;
							case "unapprove":
								$entity->unapprove();
								break;
							case "remove":
								$entity->delete();
								break;
							case "reject":
								if (MSPR_REJECTION_SEND_EMAIL) {
									$sub_info = get_submission_information($entity);
									$reason_type = ((!$comment) ?  "noreason" : "reason");
									$active_user = User::get($_SESSION["details"]["id"]);
									if ($active_user && $type) {
			
										submission_rejection_notification(	$reason_type,
																		array(
																			"firstname" => $user->getFirstname(),
																			"lastname" => $user->getLastname(),
																			"email" => $user->getEmail()),
																		array(
																			"to_fullname" => $user->getFirstname(). " " . $user->getLastname(),
																			"from_firstname" => $active_user->getFirstname(),
																			"from_lastname" => $active_user->getLastname(),
																			"reason" => clean_input($comment,array("notags","specialchars")),
																			"submission_details" => $sub_info,
																			"application_name" => APPLICATION_NAME . " MSPR System"
																			));
									} else {
										add_error($translator->translate("mspr_email_failed"));
									}
								}
								$entity->reject($comment);
								break;
							case "edit":
								switch($section) {
									case "clineval":
										$this->edit_clineval($entity);
										break;
									case "observerships":
										$this->edit_observership($entity);
										break;
								}
						}
					}
				}
			} elseif($action == "add") {
				if (!$user_id) {
					add_error($translator->translate("mspr_invalid_user_info"));
				}
				if (!has_error()) {
					switch($section) {
						case 'studentships':
							$this->add_studentship($user_id);
							break;
						case 'clineval':
							$this->add_clineval($user_id);
							break;
						case 'internal_awards':
							$this->add_internal_award_receipt($user_id);
							break;
						case 'student_run_electives':
							$this->add_student_run_elective($user_id);
							break;
						case 'observerships':
							$this->add_observership($user_id);
							break;
						case 'int_acts':
							$this->add_int_act($user_id);
							break;
					}
				}
			}
			
			switch($section) {
				case 'studentships':
					$studentships = Studentships::get($user);
					display_status_messages();
					echo display_studentships($studentships, $type);
				break;
				
				case 'clineval':
					$clinical_evaluation_comments = ClinicalPerformanceEvaluations::get($user);
					display_status_messages();
					echo display_clineval($clinical_evaluation_comments, $type);
				break;
				
				case 'internal_awards':
					$internal_awards = InternalAwardReceipts::get($user);
					display_status_messages();
					echo display_internal_awards($internal_awards, $type);
				break;
				
				case 'external_awards':
					$external_awards = ExternalAwardReceipts::get($user);
					display_status_messages();
					echo display_external_awards($external_awards, $type);
				break;
				
				case 'contributions':
					$contributions = Contributions::get($user);
					display_status_messages();
					echo display_contributions($contributions, $type);
				break;
				
				case 'student_run_electives':
					$student_run_electives = StudentRunElectives::get($user);
					display_status_messages();
					echo display_student_run_electives($student_run_electives, $type);
				break;
				
				case 'observerships':
					$observerships = Observerships::get($user);
					display_status_messages();
					echo display_observerships($observerships, $type);
				break;
				
				case 'int_acts':
					$int_acts = InternationalActivities::get($user);
					display_status_messages();
					echo display_international_activities($int_acts, $type);
				break;
				
				case 'critical_enquiry':
					$critical_enquiry = CriticalEnquiry::get($user);
					display_status_messages();
					echo display_critical_enquiry($critical_enquiry, $type);
				break;
	
				case 'community_health_and_epidemiology':
					$community_health_and_epidemiology = CommunityHealthAndEpidemiology::get($user);
					display_status_messages();
					echo display_community_health_and_epidemiology($community_health_and_epidemiology, $type);
				break;
	
				case 'research_citations':
					$research_citations = ResearchCitations::get($user);
					display_status_messages();
					echo display_research_citations($research_citations, $type);
				break;
			}
		}
	}
	
	private function add_observership($user_id) {
		$translator = $this->_translator;
		
		$title = clean_input((isset($_POST['title']) ? $_POST['title'] : "" ),array("notags"));
		$site = clean_input((isset($_POST['site']) ? $_POST['site'] : "" ),array("notags"));
		$location = clean_input((isset($_POST['location']) ? $_POST['location'] : "" ),array("notags"));
		$start = clean_input((isset($_POST['start']) ? $_POST['start'] : "" ),array("notags"));
		$end = clean_input((isset($_POST['end']) ? $_POST['end'] : "" ),array("notags"));
				
		$preceptor_proxy_id = clean_input((isset($_POST['preceptor_proxy_id']) ? $_POST['preceptor_proxy_id'] : "" ),array("int"));
		$preceptor_firstname = clean_input((isset($_POST['preceptor_firstname']) ? $_POST['preceptor_firstname'] : "" ),array("notags"));
		$preceptor_lastname = clean_input((isset($_POST['preceptor_lastname']) ? $_POST['preceptor_lastname'] : "" ),array("notags"));
		
		if (!checkDateFormat($start)) {
			add_error($translator->translate("mspr_observership_invalid_dates"));
		} else {
			$parts = date_parse($start);  
			$start_ts = mktime(0,0, 0, $parts['month'],$parts['day'], $parts['year']);
			if ($end && checkDateFormat($end)) {
				$parts = date_parse($end);  
				$end_ts = mktime(0,0, 0, $parts['month'],$parts['day'], $parts['year']);
			} else {
				$end_ts = null;
			}
		}
		
		if (!$preceptor_proxy_id) {
			$preceptor_proxy_id = null; 
		}
		
		if (!$preceptor_proxy_id && !($preceptor_firstname || $preceptor_lastname)) {
			add_error($translator->translate("mspr_observership_preceptor_required"));
		}
		
		if ($preceptor_proxy_id == -1) {
			//special case for "Various"
			$preceptor_proxy_id = 0; //not faculty 
			$preceptor_firstname = "Various";
			$preceptor_lastname = "";
		}
		
		if (!has_error()) {
			if ($user_id && $title && $site && $location && $start_ts) {
								
				Observership::create($user_id, $title, $site, $location, $preceptor_proxy_id, $preceptor_firstname, $preceptor_lastname, $start_ts, $end_ts);
			} else {
				add_error($translator->translate("mspr_insufficient_info"));
			}
		}
	}
	
	private function edit_observership($obs) {
		$translator = $this->_translator;
		
		$title = clean_input((isset($_POST['title']) ? $_POST['title'] : "" ),array("notags"));
		$site = clean_input((isset($_POST['site']) ? $_POST['site'] : "" ),array("notags"));
		$location = clean_input((isset($_POST['location']) ? $_POST['location'] : "" ),array("notags"));
		$start = clean_input((isset($_POST['start']) ? $_POST['start'] : "" ),array("notags"));
		$end = clean_input((isset($_POST['end']) ? $_POST['end'] : "" ),array("notags"));
				
		$preceptor_proxy_id = clean_input((isset($_POST['preceptor_proxy_id']) ? $_POST['preceptor_proxy_id'] : "" ),array("int"));
		$preceptor_firstname = clean_input((isset($_POST['preceptor_firstname']) ? $_POST['preceptor_firstname'] : "" ),array("notags"));
		$preceptor_lastname = clean_input((isset($_POST['preceptor_lastname']) ? $_POST['preceptor_lastname'] : "" ),array("notags"));
		
		if (!checkDateFormat($start)) {
			add_error($translator->translate("mspr_observership_invalid_dates"));
		} else {
			$parts = date_parse($start);  
			$start_ts = mktime(0,0, 0, $parts['month'],$parts['day'], $parts['year']);
			if ($end && checkDateFormat($end)) {
				$parts = date_parse($end);  
				$end_ts = mktime(0,0, 0, $parts['month'],$parts['day'], $parts['year']);
			} else {
				$end_ts = null;
			}
		}
		
		if (!$preceptor_proxy_id) {
			$preceptor_proxy_id = null; 
		}
		
		if (!$preceptor_proxy_id && !($preceptor_firstname || $preceptor_lastname)) {
			add_error($translator->translate("mspr_observership_preceptor_required"));
		}
		
		if ($preceptor_proxy_id === -1) {
			//special case for "Various"
			$preceptor_proxy_id = 0; //not faculty 
			$preceptor_firstname = "Various";
			$preceptor_lastname = "";
		}
		
		if (!has_error()) {
			if ($title && $site && $location && $start_ts) {
				$obs->update($title, $site, $location, $preceptor_proxy_id, $preceptor_firstname, $preceptor_lastname, $start_ts, $end_ts);				
			} else {
				add_error($translator->translate("mspr_insufficient_info"));
			}
		}
	}
	
	private function add_studentship($user_id) {
		$translator = $this->_translator;
		
		$title = clean_input((isset($_POST['title']) ? $_POST['title'] : "" ),array("notags"));
		$year = clean_input((isset($_POST['year']) ? $_POST['year'] : "" ), array("int"));
		if ($title && $year && $user_id) {
			Studentship::create($user_id,$title,$year);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
	private function add_clineval($user_id) {
		$translator = $this->_translator;
		
		$source = clean_input((isset($_POST['source']) ? $_POST['source'] : "" ),array("notags"));
		$comment = clean_input((isset($_POST['text']) ? $_POST['text'] : "" ),array("notags"));
		if ($source && $comment && $user_id) {
			ClinicalPerformanceEvaluation::create($user_id,$comment,$source);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
	private function edit_clineval($clineval) {
		$translator = $this->_translator;
		
		$source = clean_input((isset($_POST['source']) ? $_POST['source'] : "" ),array("notags"));
		$comment = clean_input((isset($_POST['text']) ? $_POST['text'] : "" ),array("notags"));
		if ($source && $comment && $clineval) {
			$clineval->update($comment, $source);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
	private function add_int_act($user_id) {
		$translator = $this->_translator;
		
		$title = clean_input((isset($_POST['title']) ? $_POST['title'] : "" ),array("notags"));
		$site = clean_input((isset($_POST['site']) ? $_POST['site'] : "" ),array("notags"));
		$location = clean_input((isset($_POST['location']) ? $_POST['location'] : "" ),array("notags"));
		$start = clean_input((isset($_POST['start']) ? $_POST['start'] : "" ),array("notags"));
		$end = clean_input((isset($_POST['end']) ? $_POST['end'] : "" ),array("notags"));
			
		if (!checkDateFormat($start)) {
			add_error($translator->translate("mspr_observership_invalid_dates"));
		} else {
			if (!$end || !checkDateFormat($end)) {
				$end = $start;
			}
		}
		
		if ($user_id && $title && $site && $location && $start) {
							
			InternationalActivity::create($user_id, $title, $site, $location, $start, $end);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
	private function add_student_run_elective($user_id) {
		$translator = $this->_translator;
		
		$group_name = clean_input((isset($_POST['group_name']) ? $_POST['group_name'] : "" ),array("notags"));
		$university = clean_input((isset($_POST['university']) ? $_POST['university'] : "" ),array("notags"));
		$location = clean_input((isset($_POST['location']) ? $_POST['location'] : "" ),array("notags"));
		$start_year = clean_input((isset($_POST['start_year']) ? $_POST['start_year'] : "" ),array("int"));
			
		if ($user_id && $group_name && $university && $location && $start_year) {
			$end_year = clean_input((isset($_POST['end_year']) ? $_POST['end_year'] : "" ),array("int"));
			$start_month = clean_input((isset($_POST['start_month']) ? $_POST['start_month'] : "" ),array("int"));
			$end_month = clean_input((isset($_POST['end_month']) ? $_POST['end_month'] : "" ),array("int"));
							
			StudentRunElective::create($user_id, $group_name, $university, $location, $start_month, $start_year, $end_month, $end_year);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
	private function add_internal_award_receipt($user_id) {
		$translator = $this->_translator;
		
		$award_id = clean_input((isset($_POST['title']) ? $_POST['title'] : 0), array("int"));
		if ($user_id && $award_id) {
			$year = clean_input((isset($_POST['year']) ? $_POST['year'] : "" ), array("int"));
			InternalAwardReceipt::create($award_id,$user_id,$year);
		} else {
			add_error($translator->translate("mspr_insufficient_info"));
		}
	}
	
}

/**
 * used for getting information about submissions in a simple text format (for email)
 * @param mixed $entity
 */
function get_submission_information($entity) {
	$class_name = get_class($entity);
	switch ($class_name) {
		case 'Contribution':
			$output = "Contribution to Medical School/Student Life\n\n";
			$output .= "Role: " . $entity->getRole() ."\nOrganisation/Event: ".$entity->getOrgEvent() . "\nPeriod: ".$entity->getPeriod()."\n";
			break;
		case 'ResearchCitation':
			$output = "Research\n\n";
			$output .= $entity->getText() ."\n";
			break;
		case 'CriticalEnquiry':
			$output = "Critical Enquiry\n\n";
			$output .= "Title: " . $entity->getTitle() ."\nOrganisation: ".$entity->getOrganization() . "\nLocation: ".$entity->getLocation()."\nSupervisor: " . $entity->getSupervisor() . "\n";
			break;
		case 'CommunityHealthAndEpidemiology':
			$output = "Community-Based Project\n\n";
			$output .= "Title: " . $entity->getTitle() ."\nOrganisation: ".$entity->getOrganization() . "\nLocation: ".$entity->getLocation()."\nSupervisor: " . $entity->getSupervisor() . "\n";
			break;
		case 'ExternalAward':
			$output = "External Award\n\n";
			$award = $entity->getAward();
			$output .= "Title: " . $award->getTitle() ."\nTerms: ".$award->getTerms() . "\nAwarding Body: ".$award->getAwardingBody()."\nYear Awarded: " . $entity->getAwardYear() . "\n";
			break;
		default:
			$output = "Unknown"; 
			
	}
	return $output;
}

function process_mspr_details($translator,$section) {
	$action = clean_input((isset($_POST['action']) ? $_POST['action'] : ""), array("lower"));
	if (!$action) {
		return;
	}
	switch($action) {
		case 'add':
			$user_id = clean_input((isset($_POST['user_id']) ? $_POST['user_id'] : 0), array("int"));
			$details = clean_input((isset($_POST['details']) ? $_POST['details'] : "" ), array("notags"));
			if (!$user_id) {
				add_error($translator->translate("mspr_invalid_user_info"));
			}
			if (!$details) {
				add_error($translator->translate("mspr_no_details"));
			}
			if (!has_error()){
				switch($section) {
					case 'leaves_of_absence':
						LeaveOfAbsence::create($user_id, $details);
						break;
					case 'disciplinary_actions':
						DisciplinaryAction::create($user_id,$details);
						break;
					case 'formal_remediation':
						FormalRemediation::create($user_id,$details);
						break;
				}
			}
			break;
		case 'remove':
			$entity_id = clean_input((isset($_POST['entity_id']) ? $_POST['entity_id'] : 0), array("int"));
			if (!$entity_id) {
				add_error($translator->translate("mspr_no_entity"));
			}
			if (!has_error()) {
				switch($section) {
					case 'leaves_of_absence':
						$entity = LeaveOfAbsence::get($entity_id);
						break;
					case 'disciplinary_actions':
						$entity = DisciplinaryAction::get($entity_id);
						break;
					case 'formal_remediation':
						$entity = FormalRemediation::get($entity_id);
						break;
				}
				if (!$entity) {
					add_error($translator->translate("mspr_invalid_entity"));
				}
				if (!has_error()) {
					$entity->delete();
				}
			}
			break;
	}
	
}

/**
 * Sends email based on the specified type using templates from TEMPLATE_ABSOLUTE/email directory
 * @param string $type One of "reason", "noreason"
 * @param array $to associative array consisting of firstname, lastname, and email
 * @param array $keywords Associative array of keywords mapped to the replacement contents
 */
function submission_rejection_notification($type, $to = array(), $keywords = array()) {
	global $AGENT_CONTACTS;
	if (!is_array($to) || !isset($to["email"]) || !valid_address($to["email"]) || !isset($to["firstname"]) || !isset($to["lastname"])) {
		application_log("error", "Attempting to send a task_verification_notification() however the recipient information was not complete.");
		
		return false;
	}
	
	if (!in_array($type, array("reason", "noreason"))) {
		application_log("error", "Encountered an unrecognized notification type [".$type."] when attempting to send a submission_rejection_notification().");

		return false;
	}
	
	
	$xml_file = TEMPLATE_ABSOLUTE."/email/mspr-rejection-".$type.".xml";
	
	try {
		require_once("Models/utility/Template.class.php");
		require_once("Models/utility/TemplateMailer.class.php");
		$template = new Template($xml_file);
		$mail = new TemplateMailer(new Zend_Mail());
		$mail->addHeader("X-Section", "MSPR Module", true);
		
		$from = array("email"=>$AGENT_CONTACTS["agent-notifications"]["email"], "firstname"=> "MSPR System","lastname"=>"");
		if ($mail->send($template,$to,$from,DEFAULT_LANGUAGE,$keywords)) {
			return true;
		} else {
			add_notice("We were unable to e-mail a task notification <strong>".$to["email"]."</strong>.<br /><br />A system administrator was notified of this issue, but you may wish to contact this individual manually and let them know their task verification status.");
			application_log("error", "Unable to send task verification notification to [".$to["email"]."] / type [".$type."]. Zend_Mail said: ".$mail->ErrorInfo);
		}
					
	} catch (Exception $e) {
		application_log("error", "Unable to load the XML file [".$xml_file."] or the XML file did not contain the language requested [".DEFAULT_LANGUAGE."], when attempting to send a regional education notification.");
	}

	return false;
}