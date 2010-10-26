<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

 
require_once("Models/utility/Template.class.php");


function item_wrap_content($type, $status, $user_id, $entity_id, $content, $hide_controls = false, $comment="", $id_string="") {
	
	//static and test used to minimize re-loading of the same template
	$status_file = TEMPLATE_ABSOLUTE."/modules/".$type."/mspr/item_status".($hide_controls?"_no_controls":"").".xml";
	$status_template = new Template($status_file);
	$status_bind = array (
				"content"	=> $content,
				"user_id"	=> $user_id,
				"entity_id"	=> $entity_id,
				"reason"	=> clean_input($comment,array("notags","specialchars","nl2br")),
				"id"		=> ($id_string ? "id='".$id_string."'" : ""),
				"form_url"	=> ENTRADA_URL . "/admin/users/manage/students?section=mspr&id=" . $user_id
	);
	
			
			
	return $status_template->getResult(DEFAULT_LANGUAGE, $status_bind, array("status"=>$status));	
}

function list_wrap_content($content, $class="", $id="") {
	$list_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/mspr_list.xml";
	$list_template = new Template($list_file);
	
	$list_bind = array (
				"class" => $class,
				"id" => ($id? "id='".$id."'":""),
				"content"	=> $content
	);
			
	return $list_template->getResult(DEFAULT_LANGUAGE, $list_bind);	
}

function getStatus($entity) {
	if ($entity instanceof Approvable) {
		$status=($entity->isRejected() ? ($entity->getComment()?"rejected_reason":"rejected") : ($entity->isApproved()? "approved" : "unapproved"));
	} else {
		$status="default";
	}
	return $status;
}

function display_studentships(Studentships $studentships, $type, $hide_controls = false) {
	
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/studentship.xml";
	$content_template =  new Template($content_file);
	
	if ($studentships && count($studentships) > 0) {
		foreach($studentships as $studentship) {
			$status = getStatus($studentship);
			
			$content_bind = array (
				"title"	=> clean_input($studentship->getTitle(), array("notags", "specialchars")),
				"year"	=> clean_input($studentship->getYear(), array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			$contents .= item_wrap_content($type,$status, $studentship->getUserID(), $studentship->getID(), $content, $hide_controls);
		}		
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_clineval(ClinicalPerformanceEvaluations $clinevals,$type, $hide_controls = false) {
	
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/clinical_performance_evaluation_comment.xml";
	$content_template =  new Template($content_file);
	
	if ($clinevals && count($clinevals) > 0) {
		foreach($clinevals as $clineval) {
			$user = $clineval->getUser();
			
			$status = getStatus($clineval);
			
			$content_bind = array (
				"comment"	=> clean_input($clineval->getComment(), array("notags", "specialchars", "nl2br")),
				"source"	=> clean_input($clineval->getSource(), array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			$contents .= item_wrap_content($type,$status, $user->getID(), $clineval->getID(), $content, $hide_controls);
		}		
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}


function display_internal_awards(InternalAwardReceipts $receipts,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/internal_award.xml";
	$content_template =  new Template($content_file);
	
	if ($receipts && count($receipts) > 0) {
		foreach($receipts as $receipt) {
			$status = getStatus($receipt);
			$award = $receipt->getAward();
			$user = $receipt->getUser();
			
			$content_bind = array (
				"title"	=> clean_input($award->getTitle(), array("notags", "specialchars")),
				"year"	=> clean_input($receipt->getAwardYear(), array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			$contents .= item_wrap_content($type,$status, $user->getID(), $receipt->getID(), $content, $hide_controls);
		}		
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_external_awards(ExternalAwardReceipts $receipts,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/external_award.xml";
	$content_template =  new Template($content_file);
	
	if ($receipts && count($receipts) > 0) {
		foreach($receipts as $receipt) {
			$status = getStatus($receipt);
			$award = $receipt->getAward();
			$user = $receipt->getUser();
			
			$content_bind = array (
				"title"	=> clean_input($award->getTitle(), array("notags", "specialchars")),
				"terms"	=> clean_input($award->getTerms(), array("notags", "specialchars")),
				"body"	=> clean_input($award->getAwardingBody(), array("notags", "specialchars")),
				"year"	=> clean_input($receipt->getAwardYear(), array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			$contents .= item_wrap_content($type,$status, $user->getID(), $receipt->getID(), $content, $hide_controls, $receipt->getComment());
		}		
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_contributions(Contributions $contributions,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/contribution.xml";
	$content_template =  new Template($content_file);
	
	$contents="";
	
	if ($contributions && count($contributions) > 0) {
		foreach($contributions as $contribution) {
			
			$status = getStatus($contribution);
			
			$content_bind = array (
				"role"		=> clean_input($contribution->getRole(), array("notags", "specialchars")),
				"org_event"	=> clean_input($contribution->getOrgEvent(), array("notags", "specialchars")),
				"period"	=> clean_input($contribution->getPeriod() , array("notags", "specialchars"))
			);
				
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			
			$contents .= item_wrap_content($type,$status, $contribution->getUserID(), $contribution->getID(), $content, $hide_controls, $contribution->getComment());		
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_clerkship_details(ClerkshipRotations $rotations) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/clerkship_details.xml";
	$content_template = new Template($content_file);
	
	$contents = "";
	
	if ($rotations && count($rotations) > 0) {
		foreach($rotations as $rotation) {
			
			$content_bind = array (
				"details" => clean_input($rotation->getDetails(), array("notags", "specialchars", "nl2br")),
				"period" 	=> clean_input($rotation->getPeriod() , array("notags", "specialchars"))
			);
			
			$contents .= $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);			
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_clerkship_elective_details(ClerkshipElectivesCompleted $rotations) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/clerkship_elective.xml";
	$content_template = new Template($content_file);
	
	$contents = "";
	
	if ($rotations && count($rotations) > 0) {
		foreach($rotations as $rotation) {
			
			$content_bind = array (
				"details" => clean_input($rotation->getTitle(), array("notags", "specialchars", "nl2br")),
				"period" 	=> clean_input($rotation->getPeriod() , array("notags", "specialchars")),
				"location"	=> clean_input($rotation->getLocation() , array("notags", "specialchars")),
				"supervisor" => clean_input($rotation->getSupervisor() , array("notags", "specialchars"))
			);
			
			$contents .= $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);			
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_clerkship_core_completed(ClerkshipRotations $rotations) {
	return display_clerkship_details($rotations);
}

function display_clerkship_core_pending(ClerkshipRotations $rotations) {
	return display_clerkship_details($rotations);
}

function display_clerkship_elective_completed(ClerkshipElectivesCompleted $rotations) {
	return display_clerkship_elective_details($rotations);
}

function display_student_run_electives(StudentRunElectives $sres,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/student_run_elective.xml";
	$content_template = new Template($content_file);
	
	$contents="";
	
	if ($sres && count($sres) > 0) {
		foreach($sres as $sre) {
			$status = getStatus($sre);
			
			$content_bind = array (
				"details" 	=> clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")),
				"period" 	=> clean_input($sre->getPeriod() , array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			
			$contents .= item_wrap_content($type,$status, $sre->getUserID(), $sre->getID(), $content, $hide_controls);		
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_supervised_project(SupervisedProject $project = null, $type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/supervised_project.xml";
	$content_template =  new Template($content_file);
	
	if ($project) {
		$status = getStatus($project);
		
		$content_bind = array (
			"title"			=> clean_input($project->getTitle(), array("notags", "specialchars")),
			"organisation"	=> clean_input($project->getOrganization(), array("notags", "specialchars")),
			"location" 		=> clean_input($project->getLocation(), array("notags", "specialchars")),
			"supervisor"	=> clean_input($project->getSupervisor(), array("notags", "specialchars"))
		);
		
		$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);

		$contents = item_wrap_content($type, $status, $project->getUserID(), $project->getUserID(), $content, $hide_controls, $project->getComment());
	} else {
		$contents = "<li>Not yet entered.</li>";	
	}
	
	return list_wrap_content($contents);
}

function display_critical_enquiry(CriticalEnquiry $critical_enquiry = null, $type, $hide_controls = false) {
	return display_supervised_project($critical_enquiry, $type, $hide_controls);
}

function display_community_health_and_epidemiology(CommunityHealthAndEpidemiology $community_health_and_epidemiology = null, $type, $hide_controls = false) {
	return display_supervised_project($community_health_and_epidemiology, $type, $hide_controls);	
}

function display_research_citations(ResearchCitations $research_citations, $type, $hide_controls = false) {
	if ($hide_controls){
		$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/research_citation.xml";
	} else {
		$content_file = TEMPLATE_ABSOLUTE."/modules/".$type."/mspr/research_citation.xml";
		if ($type=="public") {
			$class="priority-list";
		}
	}
	$content_template =  new Template($content_file);
	
	$contents = "";
	
	if ($research_citations && $research_citations->count() > 0) {
		foreach($research_citations as $research_citation) {
			$status = getStatus($research_citation);
			
			$content_bind = array (
				"image" => ENTRADA_URL. "/images/arrow_up_down.png",
				"details" => clean_input($research_citation->getText(), array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			$id_string = "research_citation_".$research_citation->getID();
			$contents .= item_wrap_content($type,$status, $research_citation->getUserID(), $research_citation->getID(), $content, $hide_controls, $research_citation->getComment(), $id_string);		
		}
	} else {
		$contents = "<li>None</li>";
	}
	$id = "citations_list";
	return list_wrap_content($contents, $class, $id);
}


function display_period_details(Collection $collection, $type, $template_name, $hide_controls = false) {

}

function display_observerships(Observerships $observerships,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/observership.xml";
	$content_template =  new Template($content_file);
	
	$contents = "";
	
	if ($collection && $collection->count() > 0) {
		foreach($collection as $entity) {
			$status = getStatus($entity);
			
			$preceptor = trim($entity->getPreceptorFirstname() . " " . $entity->getPreceptorLastname());
			if (preg_match("/\b[Dd][Rr]\./", $preceptor) == 0) {
				$preceptor = "Dr. ".$preceptor;
			}
				
			$content_bind = array (
				"title" 	=> clean_input($entity->getTitle(), array("notags", "specialchars")),
				"site" 	=> clean_input($entity->getSite(), array("notags", "specialchars")),
				"location" 	=> clean_input($entity->getLocation(), array("notags", "specialchars")),
				"preceptor" 	=> clean_input($preceptor, array("notags", "specialchars")),
				"period" 	=> clean_input($entity->getPeriod() , array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			
			$contents .= item_wrap_content($type,$status, $entity->getStudentID(), $entity->getID(), $content, $hide_controls);		 
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}

function display_international_activities(InternationalActivities $int_acts,$type, $hide_controls = false) {
	$content_file = TEMPLATE_ABSOLUTE."/modules/common/mspr/international_activity.xml";
	$content_template =  new Template($content_file);
	
	$contents = "";
	if ($int_acts && $int_acts->count() > 0) {
		foreach($int_acts as $entity) {
			$status = getStatus($entity);
			
			$content_bind = array (
				"details" 	=> clean_input($entity->getDetails(), array("notags", "specialchars", "nl2br")),
				"period" 	=> clean_input($entity->getPeriod() , array("notags", "specialchars"))
			);
			
			$content = $content_template->getResult(DEFAULT_LANGUAGE, $content_bind);
			
			$contents .= item_wrap_content($type,$status, $entity->getStudentID(), $entity->getID(), $content, $hide_controls);		 
		}
	} else {
		$contents = "<li>None</li>";
	}
	
	return list_wrap_content($contents);
}