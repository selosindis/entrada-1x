<?php

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
			
			$citation = (isset($_POST['details']) ? $_POST['details'] : '');
				
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
		} elseif ($action == "resequence") {
			$ids = (isset($_POST['research_citations']) ? $_POST['research_citations'] : null);
			if ($ids) {
				ResearchCitations::Resequence($user,$ids);
			}
		}
	}
}

/**
 * Returns a table containing received internal academic awards
 * @param InternalAwardReceipts $received_awards
 * @return string 
 */
function display_internal_awards(InternalAwardReceipts $receipts = null) {
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
				<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?>
			</span>
			<span class="label">
				Year Awarded: 
			</span>
			<span class="data">
				<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>
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

/**
 * Returns a table containing received studentships and details
 * @param Studentships $studentships
 * @return string
 */
function display_studentships(Studentships $studentships = null) {
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
 * @param ExternalAwardReceipts $receipts
 * @return string
 */
function display_external_awards_profile(ExternalAwardReceipts $receipts) {
	ob_start();
	?>
		<ul class="mspr-list">
	<?php 
	if ($receipts && $receipts->count() > 0) {
		
		foreach($receipts as $receipt) {
			
			$award = $receipt->getAward();
			$mode =  ($receipt->isApproved())? "Unapprove" : "Approve";
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
				<form class="remove_external_award_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
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
 * Returns a table containing submitted contributions with approval status indicated.
 * @param Contributions $contributions
 * @return string
 */
function display_contributions_profile(Contributions $contributions) {
	ob_start();
	?>
	
	<ul class="mspr-list">
	<?php 
	if ($contributions) {
		foreach($contributions as $contribution) {
			$mode =  ($contribution->isApproved())? "Unapprove" : "Approve";
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
				<form action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $contribution->getUserID(); ?>" method="post" >
					<input type="hidden" name="user_id" value="<?php echo $contribution->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $contribution->getID(); ?>"></input>
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
 * Returns a table containing clerkship details. Note that this can be used for any of the clerkship types
 * @param ClerkShipRotations $rotations
 * @return string
 */
function display_clerkship_details(ClerkShipRotations $rotations = null) {
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

/**
 * Returns a table containing clinical performance evaluation comments.
 * @param ClinicalPerformanceEvaluations $clinevals
 * @return string
 */
function display_clineval_profile(ClinicalPerformanceEvaluations $clinevals = null) {
	ob_start();	
	?>
	<ul class="mspr-list">
	<?php 
	if ($clinevals && $clinevals->count() > 0) {
		foreach($clinevals as $clineval) {
		?>
		<li class="entry">
			<span class="label">
				Comment: 
			</span>
			<p class="data">
				<?php echo clean_input($clineval->getComment(), array("notags", "specialchars", "nl2br")) ?>
			</p>
			<span class="label">
				Source: 
			</span>
			<span class="data">
				<?php echo clean_input($clineval->getSource(), array("notags", "specialchars")) ?>
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

/**
 * Returns a table containing student-run-electives.
 * @param StudentRunElectives $sres
 * @return string
 */
function display_student_run_electives_public(StudentRunElectives $sres=null) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($sres && ($sres->count() > 0)) {
		foreach($sres as $sre) {
		?>
		<li class="entry">
			<span class="label">
				Details: 
			</span>
			<p class="data">
				<?php echo clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")); ?>
			</p>
			<span class="label">
				Period: 
			</span>
			<span class="data">
				<?php echo clean_input($sre->getPeriod(), array("notags", "specialchars")); ?>
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

/**
 * Returns an HTML table containing Observerships.
 * @param Observerships $obss
 * @return string
 */
function display_observerships_public(Observerships $obss=null) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($obss && ($obss->count() > 0)) {
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
 * Returns an HTML table containing Observerships.
 * @param Observerships $obss
 * @return string
 */
function display_international_activities(InternationalActivities $int_acts) {
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
function display_supervised_project_profile(SupervisedProject $project = null) {
	ob_start();
	?>
	<ul class="mspr-list">
	<?php 
	if ($project) {
	?>
		<li class="entry">
			<span class="label">
				Title: 
			</span>
			<span class="heading">
				<?php echo clean_input($project->getTitle(), array("notags", "specialchars")); ?>
			</span>
			<span class="label">
				Organization:
			</span>
			<span class="data">
				<?php echo clean_input($project->getOrganization(), array("notags", "specialchars")); ?>
			</span>
			<span class="label">
				Location:
			</span>
			<span class="data">
				<?php echo clean_input($project->getLocation(), array("notags", "specialchars")); ?>
			</span>
			<span class="label">
				Supervisor:
			</span>
			<span class="data">
				<?php echo clean_input($project->getSupervisor(), array("notags", "specialchars")); ?>
			</span>
		</li>
		<?php 
	} else {
	?>
		<li>Not yet entered</li>
	<?php
	}
	?>
	</ul>
	<?php
	return ob_get_clean();
}

function display_research_citations_profile(ResearchCitations $research_citations) {
	ob_start();
	?>
	
	<ul class="mspr-list priority-list" id="citations_list">
	<?php 
	if ($research_citations && $research_citations->count() > 0) {
		foreach($research_citations as $research_citation) {
			$class = ($research_citation->isRejected() ? "rejected" : ($research_citation->isApproved()? "approved" : "unapproved"));
		?>
		<li class="entry <?php echo $class; ?>" id="research_citation_<?php echo $research_citation->getID(); ?>">
			<span class="handle"><img src="<?php echo ENTRADA_URL; ?>/images/arrow_up_down.png" /></span> 
			<span class="label">
				Citation: 
			</span>
			<p class="data">
				<?php echo clean_input($research_citation->getText(), array("notags", "specialchars", "nl2br")) ?>
			</p>
			<div class="controls">
				<form action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $research_citation->getUserID(); ?>" method="post" >
					<input type="hidden" name="user_id" value="<?php echo $research_citation->getUserID(); ?>"></input>
					<input type="hidden" name="entity_id" value="<?php echo $research_citation->getID(); ?>"></input>
					
					<input type="submit" name="action" value="Remove" ></input> 
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