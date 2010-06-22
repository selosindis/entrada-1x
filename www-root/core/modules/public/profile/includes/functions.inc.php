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
		if ($_POST['action'] == "add_external_award") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST['external_award_title']) ? $_POST['external_award_title'] : '');
			$body = (isset($_POST['external_award_body']) ? $_POST['external_award_body'] : '');
			$terms = (isset($_POST['external_award_terms']) ? $_POST['external_award_terms'] : '');
			$year = (isset($_POST['external_award_year']) ? $_POST['external_award_year'] : '');
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			if ($user_id && $title && $terms && $year && $body && ($user->getID() == $user_id)) {
				ExternalAwardReceipt::create($user_id,$title, $terms, $body, $year);
			}
		
		} elseif ($_POST['action'] == "remove_external_award") {
			$id = (isset($_POST['external_award_id']) ? $_POST['external_award_id'] : 0);
			
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
		if ($action == "add_contribution") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$role = (isset($_POST['contribution_role']) ? $_POST['contribution_role'] : '');
			$org_event = (isset($_POST['contribution_org_event']) ? $_POST['contribution_org_event'] : '');
			$date = (isset($_POST['contribution_date']) ? $_POST['contribution_date'] : '');
			$start_year = $_POST['contribution_start_year'];
				
			if (($user_id == $user->getID()) && $role && $org_event && $start_year) {
				$end_year = $_POST['contribution_end_year'];
				$start_month = $_POST['contribution_start_month'];
				$end_month = $_POST['contribution_end_month'];

				Contribution::create($user,$role, $org_event, $start_month, $start_year, $end_month, $end_year);
			}
		
		} elseif ($action == "remove_contribution") {
			$id = (isset($_POST['contribution_id']) ? $_POST['contribution_id'] : 0);
			
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
		if ($_POST['action'] == "edit_".$section) {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST[$section.'_title']) ? $_POST[$section.'_title'] : '');
			$organization = (isset($_POST[$section.'_organization']) ? $_POST[$section.'_organization'] : '');
			$location = (isset($_POST[$section.'_location']) ? $_POST[$section.'_location'] : '');
			$supervisor = (isset($_POST[$section.'_supervisor']) ? $_POST[$section.'_supervisor'] : '');
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
	$section = 'community_health_and_epidemiology';
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "edit_".$section) {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$title = (isset($_POST[$section.'_title']) ? $_POST[$section.'_title'] : '');
			$organization = (isset($_POST[$section.'_organization']) ? $_POST[$section.'_organization'] : '');
			$location = (isset($_POST[$section.'_location']) ? $_POST[$section.'_location'] : '');
			$supervisor = (isset($_POST[$section.'_supervisor']) ? $_POST[$section.'_supervisor'] : '');
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
		if ($_POST['action'] == "add_research_citation") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$citation = (isset($_POST['research_citation_details']) ? $_POST['research_citation_details'] : '');
				
			if (($user_id == $user->getID()) && $citation ) {
				ResearchCitation::create($user,$citation);
			}
		
		} elseif ($_POST['action'] == "remove_research_citation") {
			$id = (isset($_POST['research_citation_id']) ? $_POST['research_citation_id'] : 0);
			
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
function display_internal_awards(InternalAwardReceipts $received_awards = null) {
	ob_start();
	?>

	<table class="tableList mspr_table" cellspacing="0">
		<colgroup>
			<col width="85%"></col>
			<col width="15%"></col>
		</colgroup>
			<thead>
				<tr>
					<td>Award Title</td>
					<td>Year Awarded</td>
				</tr>
			</thead>
			<tbody id="internal_awards_body">
			
	<?php 
	if ($received_awards) {
		foreach($received_awards as $received_award) {
			$award = $received_award->getAward();
			?>
			<tr id="internal-award-receipt_<?php echo $received_award->getID()?>">
				<td>
					<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?>	
				</td>
				<td>
					<?php echo clean_input($received_award->getAwardYear(), array("notags", "specialchars")) ?>	
				</td>
				
			</tr>
			<?php 
			
		}
	} else {
		?>
			<tr>
				<td colspan="2">
					None	
				</td>
			</tr>
		<?php
	}
	?>
	</table>
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

	<table class="tableList mspr_table" cellspacing="0">
		<colgroup>
			<col width="85%"></col>
			<col width="15%"></col>
		</colgroup>
			<thead>
				<tr>
					<td>Title</td>
					<td>Year Awarded</td>
				</tr>
			</thead>
			<tbody>
			
	<?php 
	if ($studentships) {
		foreach($studentships as $studentship) {
			?>
			<tr>
				<td>
					<?php echo clean_input($studentship->getTitle(), array("notags", "specialchars")) ?>	
				</td>
				<td>
					<?php echo clean_input($studentship->getYear(), array("notags", "specialchars")) ?>	
				</td>
				
			</tr>
			<?php 
			
		}
	} else {
		?>
			<tr>
				<td colspan="2">
					None	
				</td>
			</tr>
		<?php
	}
	?>
	</table>
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
	<table class="mspr_table awards tableList"  cellspacing="0">
		<colgroup>
			<col width="30%"></col>
			<col width="37%"></col>
			<col width="17%"></col>
			<col width="13%"></col>
			<col width="3%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Award Title
				</td>
				<td class="general">
					Award Terms
				</td>
				<td class="general">
					Awarding Body
				</td>
				<td class="sortedDESC">
					Year Awarded
				</td>
				<td class="general">&nbsp;</td>
			</tr>
			</thead>
			<tbody>
	<?php 
	if ($receipts) {
		
		foreach($receipts as $receipt) {
			$award = $receipt->getAward();
			$user = $receipt->getUser();
			?>
			<tr<?php echo (!$receipt->isApproved())? " class=\"unapproved\"" : ""; ?>>
				<td class="award">
					<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?>	
				</td>
				<td class="award_terms">
					<?php echo clean_input($award->getTerms(), array("notags", "specialchars")) ?>	
				
				</td>
				<td>
					<?php echo clean_input($award->getAwardingBody(), array("notags", "specialchars")) ?>	
				</td>
				<td class="award_year">
					<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>	
				
				</td>
				<td class="controls">
					<form class="remove_external_award_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
						<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
						<input type="hidden" name="action" value="remove_external_award"></input>
						<input type="hidden" name="external_award_id" value="<?php echo $receipt->getID(); ?>"></input>
						
						<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
					</form>
					
				</td>
			</tr>
			<?php 
			
		}
	} else {
	?>
		<tr>
			<td colspan="5">
				None	
			</td>
		</tr>
	<?php
}
	?>
	</tbody></table>
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
	<table class="mspr_table awards tableList"  cellspacing="0">
		<colgroup>
			<col width="35%"></col>
			<col width="42%"></col>
			<col width="20%"></col>
			<col width="3%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Role
				</td>
				<td class="general">
					Organization/Event
				</td>
				<td class="general">
					Date
				</td>
				<td class="general">&nbsp;</td>
			</tr>
			</thead>
			<tbody>
	<?php 
	if ($contributions) {
		
		foreach($contributions as $contribution) {
			?>
			<tr<?php echo (!$contribution->isApproved())? " class=\"unapproved\"" : ""; ?>>
				<td class="award">
					<?php echo clean_input($contribution->getRole(), array("notags", "specialchars")) ?>	
				</td>
				<td class="award_terms">
					<?php echo clean_input($contribution->getOrgEvent(), array("notags", "specialchars")) ?>	
				
				</td>
				<td>
					<?php echo clean_input($contribution->getPeriod(), array("notags", "specialchars")) ?>	
				</td>
				<td class="controls">
					<form class="remove_contribution_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $contribution->getUserID(); ?>" method="post" >
						<input type="hidden" name="user_id" value="<?php echo $contribution->getUserID(); ?>"></input>
						<input type="hidden" name="action" value="remove_contribution"></input>
						<input type="hidden" name="contribution_id" value="<?php echo $contribution->getID(); ?>"></input>
						
						<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
					</form>
					
				</td>
			</tr>
			<?php 
			
		}
	} else {
		?>
			<tr>
				<td colspan="4">
					None	
				</td>
			</tr>
		<?php
	}

	?>
	</tbody></table>
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
	<table class="mspr_table clineval tableList" cellspacing="0">
		<colgroup>
			<col width="65%"></col>
			<col width="35%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Details
				</td>
				<td class="sortedDESC">
					Dates
				</td>
			</tr>
			</thead>
			<tbody>
			
	<?php 
	if ($rotations && ($rotations->count() > 0)) {
		foreach($rotations as $rotation) {
			?>
			<tr>
				<td class="clerkship_details">
					<?php echo clean_input($rotation->getDetails(), array("notags", "specialchars", "nl2br")) ?>	
				</td>
				<td class="clerkship_dates">
					<?php echo clean_input($rotation->getDates(), array("notags", "specialchars")) ?>	
				
				</td>
			</tr>
			<?php 
		}
	} else {
		?>
			<tr>
				<td colspan="2">
					None	
				</td>
			</tr>
		<?php
	}
	?>
	</table>
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
	<table class="mspr_table clineval tableList" cellspacing="0">
		<colgroup>
			<col width="65%"></col>
			<col width="35%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Comment
				</td>
				<td class="sortedDESC">
					Source
				</td>
			</tr>
			</thead>
			<tbody>
			
	<?php 
	if ($clinevals) {
		foreach($clinevals as $clineval) {
			?>
			<tr>
				<td class="clineval_comment">
					<?php echo clean_input($clineval->getComment(), array("notags", "specialchars")) ?>	
				</td>
				<td class="clineval_source">
					<?php echo clean_input($clineval->getSource(), array("notags", "specialchars")) ?>	
				
				</td>
			</tr>
			<?php 
			
		}
	} else {
		?>
			<tr>
				<td colspan="2">
					None	
				</td>
			</tr>
		<?php
	}
	?>
	</table>
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
		<table class="mspr_table student-run-electives tableList" cellspacing="0">
		<colgroup>
			<col width="60%"></col>
			<col width="40%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Details
				</td>
				<td class="sortedDESC">
					Period
				</td>
			</tr>
		</thead>
		<tbody>
	<?php 
	if ($sres && ($sres->count() > 0)) {
		
		foreach($sres as $sre) {
			?>
			<tr>
				<td class="award">
					<?php echo clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")) ?>	
				</td>
				<td class="award_terms">
					<?php echo clean_input($sre->getPeriod(), array("notags", "specialchars")) ?>	
				
				</td>
			</tr>
			<?php 
			
		}
	} else {
	?>
		<tr>
			<td colspan="2">
				None	
			</td>
		</tr>
	<?php
	}
	?>
	</tbody></table>
	
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
	<table class="mspr_table student-run-electives tableList" cellspacing="0">
		<colgroup>
			<col width="100%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general">
					Project Details
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="details">
					<?php 
					if ($project) {
						echo clean_input($project->getDetails(), array("notags", "specialchars", "nl2br"));
					} else {
						echo "Not yet entered.";
					}
					?>	
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php
	return ob_get_clean();
}

function display_research_citations_profile(ResearchCitations $research_citations) {
	ob_start();
	?>
	<table class="mspr_table tableList"  cellspacing="0">
		<colgroup>
			<col width="3%"></col>
			<col width="94%"></col>
			<col width="3%"></col>
		</colgroup>
		<thead>
			<tr>
				<td class="general"></td>
				<td class="general">
					Details
				</td>
				<td class="general">&nbsp;</td>
			</tr>
			</thead>
			<tbody id="research_citations_body">
	<?php 
	if ($research_citations) {
		
		foreach($research_citations as $research_citation) {
			?>
			<tr<?php echo (!$research_citation->isApproved())? " class=\"unapproved\"" : ""; ?> id="research_citation_<?php echo $research_citation->getID(); ?>">
			
				<td class="handle"><img src="<?php echo ENTRADA_URL; ?>/images/arrow_up_down.png" /></td>
				<td>
					<?php echo clean_input($research_citation->getText(), array("notags", "specialchars")) ?>	
				</td>
				<td class="controls">
					<form class="remove_research_citation_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $research_citation->getUserID(); ?>" method="post" >
						<input type="hidden" name="user_id" value="<?php echo $research_citation->getUserID(); ?>"></input>
						<input type="hidden" name="action" value="remove_research_citation"></input>
						<input type="hidden" name="research_citation_id" value="<?php echo $research_citation->getID(); ?>"></input>
						
						<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
					</form>
				</td>
			</tr>
			<?php 
			
		}
	} else {
		?>
			<tr>
				<td colspan="3">
					None	
				</td>
			</tr>
		<?php
	}

	?>
	</tbody></table>
	<?php
	return ob_get_clean();
}