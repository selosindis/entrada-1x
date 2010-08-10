<?php
/**
 * Processes the various sections of the MSPR module
 */
function process_mspr_admin($user) {
	if (isset($_GET['mspr-section']) && $section = $_GET['mspr-section']) {
		
		switch($section) {
			case 'studentships':
				process_studentship_actions($user);
				$studentships = Studentships::get($user);
				display_status_messages();
				echo display_studentships_admin($studentships);
			break;
			
			case 'clineval':
				process_clineval_actions($user);
				$clinical_evaluation_comments = ClinicalPerformanceEvaluations::get($user);
				display_status_messages();
				echo display_clineval_admin($clinical_evaluation_comments);
			break;
			
			case 'internal_awards':
				process_internal_awards_actions($user);
				$internal_awards = InternalAwardReceipts::get($user);
				display_status_messages();
				echo display_internal_awards_admin($internal_awards);
			break;
			
			case 'external_awards':
				process_external_awards_actions($user);
				$external_awards = ExternalAwardReceipts::get($user);
				display_status_messages();
				echo display_external_awards_admin($external_awards);
			break;
			
			case 'contributions':
				process_contributions_actions($user);
				$contributions = Contributions::get($user);
				display_status_messages();
				echo display_contributions_admin($contributions);
			break;
			
			case 'student_run_electives':
				process_student_run_electives_actions($user);
				$student_run_electives = StudentRunElectives::get($user);
				display_status_messages();
				echo display_student_run_electives_admin($student_run_electives);
			break;
			
			case 'observerships':
				process_observerships_actions($user);
				$observerships = Observerships::get($user);
				display_status_messages();
				echo display_observerships_admin($observerships);
			break;
			
			case 'int_acts':
				process_international_activities_actions($user);
				$int_acts = InternationalActivities::get($user);
				display_status_messages();
				echo display_international_activities_admin($int_acts);
			break;
			
			case 'critical_enquiry':
				process_critical_enquiry_actions($user);
				$critical_enquiry = CriticalEnquiry::get($user);
				display_status_messages();
				echo display_critical_enquiry_admin($critical_enquiry);
			break;

			case 'community_health_and_epidemiology':
				process_community_health_and_epidemiology_actions($user);
				$community_health_and_epidemiology = CommunityHealthAndEpidemiology::get($user);
				display_status_messages();
				echo display_community_health_and_epidemiology_admin($community_health_and_epidemiology);
			break;

			case 'research_citations':
				process_research_citations_actions($user);
				$research_citations = ResearchCitations::get($user);
				display_status_messages();
				echo display_research_citations_admin($research_citations);
			break;
		}
	}
}

function process_research_citations_actions(User $user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Approve") {
			$research_citation_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($research_citation_id) {
				$research_citation = ResearchCitation::get($research_citation_id);
				if ($research_citation) {
					$research_citation->approve();
				}
			}
		
		} elseif ($_POST['action'] == "Unapprove") {
			$research_citation_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($research_citation_id) {
				$research_citation = ResearchCitation::get($research_citation_id);
				if ($research_citation) {
					$research_citation->unapprove();
				}
			}
		
		} elseif ($_POST['action'] == "Reject") {
			$research_citation_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($research_citation_id) {
				$research_citation = ResearchCitation::get($research_citation_id);
				if ($research_citation) {
					$research_citation->reject();
				}
			}
		
		}
	}
}

function process_critical_enquiry_actions(User $user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Approve") {
			$project = CriticalEnquiry::get($user);
			if ($project) {
				$project->approve();
			}
		} elseif ($_POST['action'] == "Unapprove") {
			$project = CriticalEnquiry::get($user);
			if ($project) {
				$project->unapprove();
			}
		}  elseif ($_POST['action'] == "Reject") {
			$project = CriticalEnquiry::get($user);
			if ($project) {
				$project->reject();
			}
		}
	}
}

function process_community_health_and_epidemiology_actions(User $user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Approve") {
			$project = CommunityHealthAndEpidemiology::get($user);
			if ($project) {
				$project->approve();
			}
		} elseif ($_POST['action'] == "Unapprove") {
			$project = CommunityHealthAndEpidemiology::get($user);
			if ($project) {
				$project->unapprove();
			}
		} elseif ($_POST['action'] == "Reject") {
			$project = CommunityHealthAndEpidemiology::get($user);
			if ($project) {
				$project->reject();
			}
		}
	}
}

function process_contributions_actions(User $user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Approve") {
			$contribution_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($contribution_id) {
				$contribution = Contribution::get($contribution_id);
				if ($contribution) {
					$contribution->approve();
				}
			}
		
		} elseif ($_POST['action'] == "Unapprove") {
			$contribution_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($contribution_id) {
				$contribution = Contribution::get($contribution_id);
				if ($contribution) {
					$contribution->unapprove();
				}
			}
		} elseif ($_POST['action'] == "Reject") {
			$contribution_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($contribution_id) {
				$contribution = Contribution::get($contribution_id);
				if ($contribution) {
					$contribution->reject();
				}
			}
		
		}
	}
}

function process_student_run_electives_actions(User $user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$group_name = $_POST['group_name'];
			$university = $_POST['university'];
			$location = $_POST['location'];
			$start_year = $_POST['start_year'];
				
			if ($user_id && $group_name && $university && $location && $start_year) {
				$end_year = $_POST['end_year'];
				$start_month = $_POST['start_month'];
				$end_month = $_POST['end_month'];
								
				StudentRunElective::create($user, $group_name, $university, $location, $start_month, $start_year, $end_month, $end_year);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$sre = StudentRunElective::get($id);
				if ($sre) {
					$sre->delete();
				}
			}
		}
	}
}

function process_observerships_actions(User $user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['student_id']) ? $_POST['student_id'] : 0);
			
			$title = $_POST['title'];
			$site = $_POST['site'];
			$location = $_POST['location'];
			$start = $_POST['start'];
				
			if ($user_id && $title && $site && $location && $start) {
				$end = $_POST['end'];
								
				Observership::create($user, $title, $site, $location, $start, $end);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$obs = Observership::get($id);
				if ($obs) {
					$obs->delete();
				}
			}
		}
	}
}

function process_international_activities_actions(User $user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['student_id']) ? $_POST['student_id'] : 0);
			
			$title = $_POST['title'];
			$site = $_POST['site'];
			$location = $_POST['location'];
			$start = $_POST['start'];
				
			if ($user_id && $title && $site && $location && $start) {
				$end = $_POST['end'];
								
				InternationalActivity::create($user, $title, $site, $location, $start, $end);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			
			if ($id) {
				$int_act = InternationalActivity::get($id);
				if ($int_act) {
					$int_act->delete();
				}
			}
		}
	}
}
/**
 * Routine to process actions available for studentships: add and delete 
 */
function process_studentship_actions(User $user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
//TODO add error processing (e.g. invalid year, etc) 	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add" && $user) {
				$title = $_POST['title'];
				$year = $_POST['year'];
				Studentship::create($user,$title,$year);
		} elseif ($_POST['action'] == "Remove") {
			$studentship_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($studentship_id) {
				$studentship = Studentship::get($studentship_id);
				if ($studentship) {
					$studentship->delete();
				}
			}
		}
	}
}

/**
 * Routine to process the various actions available on Clinical Performance Evaluation Comments
 */
function process_clineval_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			if ($user) {
				$source = $_POST['source'];
				$comment = $_POST['text'];
				ClinicalPerformanceEvaluation::create($user,$comment,$source);
				//add_clineval_comment($user, $comment, $source);
			}
		
		} elseif ($_POST['action'] == "Remove") {
			$comment_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($comment_id) {
				$clineval = ClinicalPerformanceEvaluation::get($comment_id);
				
				if($clineval) {
					$clineval->delete();
				}
			}
		}
	}
}

function process_internal_awards_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			$award_id = (isset($_POST['title']) ? $_POST['title'] : 0);
			if ($user_id && $award_id) {
				$year = $_POST['year'];
				InternalAwardReceipt::create($award_id,$user_id,$year);
			}
		} elseif ($_POST['action'] == "Remove") {
			$id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($id) {
				$recipient = InternalAwardReceipt::get($id);
				if ($recipient) {
					$recipient->delete();
				}
			}
		}
	}
}



function process_disciplinary_actions($user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_disciplinary_action" && $user) {
			$details = $_POST['action_details'];
			DisciplinaryAction::create($user,$details);
		} elseif ($_POST['action'] == "remove_disciplinary_action") {
			$action_id = (isset($_POST['action_id']) ? $_POST['action_id'] : 0);
			if ($action_id) {
				$action = DisciplinaryAction::get($action_id);
				if ($action) {
					$action->delete();
				}
			}
		}
	}	
}

function process_formal_remediations($user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add" && $user) {
				$details = $_POST['action_details'];
				FormalRemediation::create($user,$details);
			
		} elseif ($_POST['action'] == "Remove") {
			$formal_remediation_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($formal_remediation_id) {
				$formal_remediation = FormalRemediation::get($formal_remediation_id);
				if ($formal_remediation) {
					$formal_remediation->delete();
				}
			}
		}
	}	
}


function process_leaves_of_absence($user) {
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Add" && $user) {
				$details = $_POST['action_details'];
				LeaveOfAbsence::create($user,$details);
			
		} elseif ($_POST['action'] == "Remove") {
			$absence_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($absence_id) {
				$absence = LeaveOfAbsence::get($absence_id);
				if ($absence) {
					$absence->delete();
				}
			}
		}
	}	
}

function process_external_awards_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "Approve") {
			$award_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($award_id) {
				$award = ExternalAwardReceipt::get($award_id);
				if ($award) {
					$award->approve();
				}
			}
		
		} elseif ($_POST['action'] == "Unapprove") {
			$award_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($award_id) {
				$award = ExternalAwardReceipt::get($award_id);
				if ($award) {
					$award->unapprove();
				}
			}
		
		} elseif ($_POST['action'] == "Reject") {
			$award_id = (isset($_POST['entity_id']) ? $_POST['entity_id'] : 0);
			if ($award_id) {
				$award = ExternalAwardReceipt::get($award_id);
				if ($award) {
					$award->reject();
				}
			}
		
		}
	}
}

/**
 * Routine to output the table of studentships for a given student. Includes admin actions 
 * @param $user_id
 */
function display_studentships_admin(Studentships $studentships) {
	ob_start();	
	?>
	<ul class="mspr-list">
	<?php 
	if ($studentships && $studentships->count() > 0) {
		foreach($studentships as $studentship) {
		?>
		<li class="entry">
			<span class="label">
				Title:
			</span>
			<span class="heading">
				<?php echo clean_input($studentship->getTitle(), array("notags", "specialchars")) ?>
			</span>
			<span class="label">
				Year Awarded:
			</span>
			<span class="data">
				<?php echo clean_input($studentship->getYear(), array("notags", "specialchars")) ?>
			</span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $studentship->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $studentship->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $studentship->getID(); ?>"></input>
					<input type="submit" name="action" value="Remove"></input> 
				</form>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	
	<?php
	return ob_get_clean();
}

/**
 * Outputs a table with Clinical Permance Evaluation comments for a given student. Includes admin functions
 * @param $user_id
 */
function display_clineval_admin(ClinicalPerformanceEvaluations $clinevals) {
	ob_start();	
	?>
	<ul class="mspr-list">
	<?php 
	if ($clinevals && $clinevals->count() > 0) {
		foreach($clinevals as $clineval) {
			$user = $clineval->getUser();
			?>
		<li class="entry">
			<span class="label">
				Comment: 
			</span>
			<p class="data">
				<?php echo clean_input($clineval->getComment(), array("notags", "specialchars","nl2br")); ?>
			</p>
			<span class="label">
				Source: 
			</span>
			<span class="data">
				<?php echo clean_input($clineval->getSource(), array("notags", "specialchars")) ?>
			</span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $clineval->getID(); ?>"></input>
					<input type="submit" name="action" value="Remove"></input> 
				</form>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

/**
 * Outputs a table with awards for a given student. Includes admin functions
 * @param $user_id
 */
function display_internal_awards_admin(InternalAwardReceipts $receipts) {
	ob_start();	
	?>
	<ul class="mspr-list">
	<?php 
	if ($receipts && $receipts->count() > 0) {
		foreach($receipts as $receipt) {
			$award = $receipt->getAward();
			$user = $receipt->getUser();
			?>
		<li class="entry">
			<span class="label">
				Award Title: 
			</span>
			<span class="heading">
				<a href="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award->getID(); ?>">
				<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?></a>
			</span>
			<span class="label">
				Year Awarded: 
			</span>
			<span class="data">
				<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>
			</span>
			<div class="controls">
				<form class="remove_internal_award_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $receipt->getID(); ?>"></input>
					<input type="submit" name="action" value="Remove"></input>
				</form>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

/**
 * Outputs a table with awards for a given student. Includes profile functions
 * @param $user_id
 */
function display_external_awards_admin(ExternalAwardReceipts $receipts) {
	ob_start();		
	?>
	<ul class="mspr-list">
	<?php 
	if ($receipts && $receipts->count() > 0) {
		
		foreach($receipts as $receipt) {
			
			$award = $receipt->getAward();
			$mode =  (!$receipt->isApproved() || $receipt->isRejected())? "Approve" : "Unapprove";
			$user = $receipt->getUser();
			$class = ($receipt->isRejected() ? "rejected" : ($receipt->isApproved()? "approved" : "unapproved"));
		?>
		<li class="entry <?php echo $class; ?>">
			<span class="label">
			Title: 
			</span>
			<span class="heading">
			<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?>
			</span>
			<span class="label">
				Terms:
			</span>
			<p class="data">
				<?php echo clean_input($award->getTerms(), array("notags", "specialchars")) ?>
			</p>
			<span class="label">
				Awarding Body:
			</span>
			<span class="data">
				<?php echo clean_input($award->getAwardingBody(), array("notags", "specialchars")) ?>	
			</span>
			<span class="label">
				Year Awarded:	
			</span>
			<span class="data">
				<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>	
			</span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $receipt->getID(); ?>"></input>
					<input type="submit" name="action" value="<?php echo $mode; ?>"></input>
				</form>
				<?php if (!$receipt->isApproved() && !$receipt->isRejected()) { ?>
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $receipt->getID(); ?>"></input>
					<input type="submit" name="action" value="Reject"></input>
				</form>
				<?php } ?>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

function display_contributions_admin(Contributions $contributions) {
	ob_start();		
	?>
	<ul class="mspr-list">
	<?php 
	if ($contributions && $contributions->count() > 0) {
		foreach($contributions as $contribution) {
			$mode =  (!$contribution->isApproved() || $contribution->isRejected())? "Approve" : "Unapprove";
			$class = ($contribution->isRejected() ? "rejected" : ($contribution->isApproved()? "approved" : "unapproved"));
			?>
			<li class="entry <?php echo $class; ?>">

			<span class="label">
				Role:
			</span>
			<span class="heading">
				<?php echo clean_input($contribution->getRole(), array("notags", "specialchars")) ?>
			</span>
			<span class="label">
				Organization/Event:
			</span>
			<span class="data">
				<?php echo clean_input($contribution->getOrgEvent(), array("notags", "specialchars")) ?>
			</span>
			<span class="label">
				Period:
			</span>
			<span class="data">
				<?php echo clean_input($contribution->getPeriod() , array("notags", "specialchars")) ?>
			</span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $contribution->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $contribution->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $contribution->getID(); ?>"></input>
					<input type="submit" name="action" value="<?php echo $mode; ?>"></input>
				</form> 
				<?php if (!$contribution->isApproved() && !$contribution->isRejected()) { ?>
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $contribution->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $contribution->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $contribution->getID(); ?>"></input>
					<input type="submit" name="action" value="Reject"></input>
				</form>
				<?php } ?>
			</div>			
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

function display_clerkship_details(ClerkshipRotations $rotations) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($rotations && $rotations->count() > 0) {
		foreach($rotations as $rotation) {
			?>
			<li class="entry">
				<span class="label">
					Details: 
				</span>
				<p class="data">
					<?php echo clean_input($rotation->getDetails(), array("notags", "specialchars", "nl2br")) ?>
				</p>
				<span class="label">
					Period: 
				</span>
				<span class="data">
					<?php echo clean_input($rotation->getPeriod(), array("notags", "specialchars")) ?>
				</span>
			</li>
			<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php	
	return ob_get_clean();
}

function display_clerkship_core_completed(ClerkshipRotations $rotations) {
	return display_clerkship_details($rotations);
}

function display_clerkship_core_pending(ClerkshipRotations $rotations) {
	return display_clerkship_details($rotations);
}

function display_clerkship_elective_completed(ClerkshipRotations $rotations) {
	return display_clerkship_details($rotations);
}

function display_student_run_electives_admin(StudentRunElectives $sres) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($sres && $sres->count() > 0) {
		foreach($sres as $sre) {
		?>
		<li class="entry">
			<span class="label">
				Details: 
			</span>
			<p class="data">
				<?php echo clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")) ?>
			</p>
			<span class="label">
				Period: 
			</span>
			<span class="data">
				<?php echo clean_input($sre->getPeriod(), array("notags", "specialchars")) ?>
			</span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $sre->getUserID(); ?>" method="post" >
					<input type="hidden" name="user_id" value="<?php echo $sre->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $sre->getID(); ?>"></input>
					<input type="submit" name="action" value="Remove"></input> 
				</form>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

/**
 * Returns a single-row-table (for consistency of formatting and markup) containing the critical entry project details.
 * @param CriticalEnquiry $critical_enquiry
 * @return string
 */
function display_supervised_project_admin($section, SupervisedProject $project = null) {
	ob_start();
	?>
	<ul class="mspr-list">
		<?php 
		if ($project) {
			$mode =  (!$project->isApproved() || $project->isRejected())? "Approve" : "Unapprove";
			$class = ($project->isRejected() ? "rejected" : ($project->isApproved()? "approved" : "unapproved"));
		?>
		<li class="entry <?php echo $class; ?>">
		
			<span class="label">Title: </span>
			<span class="heading"><?php echo clean_input($project->getTitle(),array("notags", "specialchars", "nl2br")); ?></span>
			<span class="label">Organization: </span>
			<span class="data"><?php echo clean_input($project->getOrganization(),array("notags", "specialchars", "nl2br")); ?></span>
			<span class="label">Location: </span>
			<span class="data"><?php echo clean_input($project->getLocation(),array("notags", "specialchars", "nl2br")); ?></span>
			<span class="label">Supervisor: </span>
			<span class="data"><?php echo clean_input($project->getSupervisor(),array("notags", "specialchars", "nl2br")); ?></span>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $project->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $project->getUserID(); ?>"></input>
					<input type="submit" name="action" value="<?php echo $mode; ?>"></input>
				</form> 
				<?php if (!$project->isApproved() && !$project->isRejected()) { ?>
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $project->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $project->getUserID(); ?>"></input>
					<input type="submit" name="action" value="Reject"></input>
				</form>
				<?php } ?>
			</div>
		</li>	
			
		<?php		
		} else {
		?>
		<li>Not yet entered.</li>	
		<?php 
		}
		?>
		
	</ul>
	
	<?php
	return ob_get_clean();
}

function display_critical_enquiry_admin(CriticalEnquiry $critical_enquiry = null) {
	return display_supervised_project_admin("critical_enquiry", $critical_enquiry);
}

function display_community_health_and_epidemiology_admin(CommunityHealthAndEpidemiology $community_health_and_epidemiology = null) {
	return display_supervised_project_admin("community_health_and_epidemiology", $community_health_and_epidemiology);	
}

function display_research_citations_admin(ResearchCitations $research_citations) {
	ob_start();
	?>
	
	<ul class="mspr-list">
	<?php 
	if ($research_citations && $research_citations->count() > 0) {
		foreach($research_citations as $research_citation) {
			$mode =  (!$research_citation->isApproved() || $research_citation->isRejected())? "Approve" : "Unapprove";
			$class = ($research_citation->isRejected() ? "rejected" : ($research_citation->isApproved()? "approved" : "unapproved"));
		?>
		<li class="entry <?php echo $class; ?>">
			<span class="label">
				Details: 
			</span>
			<p class="data">
				<?php echo clean_input($research_citation->getText(), array("notags", "specialchars")) ?>
			</p>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $research_citation->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $research_citation->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $research_citation->getID(); ?>"></input>
					<input type="submit" name="action" value="<?php echo $mode; ?>"></input>
				</form>
				<?php if (!$research_citation->isApproved() && !$research_citation->isRejected()) { ?>
				<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $research_citation->getUserID(); ?>" method="post">
					<input type="hidden" name="user_id" value="<?php echo $research_citation->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $research_citation->getID(); ?>"></input>
					<input type="submit" name="action" value="Reject"></input>
				</form>
				<?php } ?>
			</div>
		</li>
		<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

function display_observerships_admin(Observerships $obss) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($obss && $obss->count() > 0) {
		foreach($obss as $obs) {
			?>
			<li class="entry">
				<span class="label">
					Details: 
				</span>
				<p class="data">
					<?php echo clean_input($obs->getDetails(), array("notags", "specialchars", "nl2br")) ?>
				</p>
				<span class="label">
					Period: 
				</span>
				<span class="data">
					<?php echo clean_input($obs->getPeriod(), array("notags", "specialchars")) ?>
				</span>
				<div class="controls">
					<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $obs->getStudentID(); ?>" method="post" >
						<input type="hidden" name="student_id" value="<?php echo $obs->getStudentID(); ?>"></input>
						<input type="hidden" name="entity_id" value="<?php echo $obs->getID(); ?>"></input>
						<input type="submit" name="action" value="Remove"></input> 
					</form>
				</div>
			</li>
			<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

function display_international_activities_admin(InternationalActivities $int_acts) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($int_acts && $int_acts->count() > 0) {
		foreach($int_acts as $int_act) {
			?>
			<li class="entry">
				<span class="label">
					Details: 
				</span>
				<p class="data">
					<?php echo clean_input($int_act->getDetails(), array("notags", "specialchars", "nl2br")) ?>
				</p>
				<span class="label">
					Period: 
				</span>
				<span class="data">
					<?php echo clean_input($int_act->getPeriod(), array("notags", "specialchars")) ?>
				</span>
				<div class="controls">
					<form action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $int_act->getStudentID(); ?>" method="post" >
						<input type="hidden" name="student_id" value="<?php echo $int_act->getStudentID(); ?>"></input>
						<input type="hidden" name="entity_id" value="<?php echo $int_act->getID(); ?>"></input>
						<input type="submit" name="action" value="Remove"></input> 
					</form>
				</div>
			</li>
			<?php 
		}
	} else {
	?>
		<li>None</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}