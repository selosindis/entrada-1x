<?php


/**
 * Processes the various sections of the MSPR module
 */
function process_mspr_admin($user) {
	if (isset($_GET['mspr-section']) && $section = $_GET['mspr-section']) {
		//$user_id = $user->getID();
	
		switch($section) {
			case 'studentships':
				process_studentship_actions($user);
				display_status_messages();
				echo display_studentships_admin($user);
			break;
			
			case 'clineval':
				process_clineval_actions($user);
				display_status_messages();
				echo display_clineval_admin($user);
			break;
			
			case 'internal_awards':
				process_internal_awards_actions($user);
				display_status_messages();
				echo display_internal_awards_admin($user);
			break;
			
			case 'external_awards':
				process_external_awards_actions($user);
				display_status_messages();
				echo display_external_awards_admin($user);
			break;
			
			case 'contributions':
				process_contributions_actions($user);
				display_status_messages();
				echo display_contributions_admin($user);
			break;
			
			case 'student_run_electives':
				process_student_run_electives_actions($user);
				display_status_messages();
				echo display_student_run_electives_admin($user);
			break;
			
		}
	}
}

/**
 * Routine to process actions available for studentships: add and delete 
 */
function process_studentship_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
//TODO add error processing (e.g. invalid year, etc) 	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_studentship" && $user) {
				$title = $_POST['studentship_title'];
				$year = $_POST['studentship_year'];
				Studentship::create($user,$title,$year);
				//add_studentship($user_id, $title, $year);
			
		} elseif ($_POST['action'] == "remove_studentship") {
			$studentship_id = (isset($_POST['studentship_id']) ? $_POST['studentship_id'] : 0);
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
 * Routine to output the table of studentships for a given student. Includes admin actions 
 * @param $user_id
 */
function display_studentships_admin($user) {
	global $db;
	if ($user) {
		$studentships = Studentships::get($user);
		?>
			<table class="mspr_table awards tableList" cellspacing="0">
			<colgroup>
				<col width="75%"></col>
				<col width="15%"></col>
				<col width="10%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general">
						Title
					</td>
					<td class="sortedDESC">
						Year Awarded
					</td>
					<td class="general">&nbsp;</td>
				</tr>
				</thead>
				<tbody>
		<?php 
		if ($studentships) {
			
			foreach($studentships as $studentship) {
				?>
				<tr>
					<td class="award">
						<?php echo clean_input($studentship->getTitle(), array("notags", "specialchars")) ?>	
					</td>
					<td class="award_terms">
						<?php echo clean_input($studentship->getYear(), array("notags", "specialchars")) ?>	
					
					</td>
					<td class="controls">
						<form class="remove_studentship_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="remove_studentship"></input>
							<input type="hidden" name="studentship_id" value="<?php echo $studentship->getID(); ?>"></input>
							
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
	}
}

/**
 * Outputs a table with Clinical Permance Evaluation comments for a given student. Includes admin functions
 * @param $user_id
 */
function display_clineval_admin($user) {
	global $db;
	if ($user) {
		
		$clinevals = ClinicalPerformanceEvaluations::get($user);
		?>
		<table class="mspr_table clineval tableList" cellspacing="0">
			<colgroup>
				<col width="65%"></col>
				<col width="25%"></col>
				<col width="10%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general">
						Comment
					</td>
					<td class="sortedDESC">
						Source
					</td>
					<td class="general">&nbsp;</td>
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
					<td class="controls">
						<form class="remove_clineval_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="remove_clineval_comment"></input>
							<input type="hidden" name="comment_id" value="<?php echo $clineval->getID(); ?>"></input>
							
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
		</table>
		<?php
	}
}

/**
 * Routine to process the various actions available on Clinical Performance Evaluation Comments
 */
function process_clineval_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_clineval_comment") {
			
			if ($user) {
				$source = $_POST['clineval_comment_source'];
				$comment = $_POST['clineval_comment_text'];
				ClinicalPerformanceEvaluation::create($user,$comment,$source);
				//add_clineval_comment($user, $comment, $source);
			}
		
		} elseif ($_POST['action'] == "remove_clineval_comment") {
			$comment_id = (isset($_POST['comment_id']) ? $_POST['comment_id'] : 0);
			if ($comment_id) {
				$clineval = ClinicalPerformanceEvaluation::get($comment_id);
				
				if($clineval) {
					$clineval->delete();
				}
				//remove_clineval_comment($comment_id);
			}
		}
	}
}

function process_internal_awards_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_internal_award") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$award_id = (isset($_POST['internal_award_title']) ? $_POST['internal_award_title'] : 0);
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			if ($user_id && $award_id) {
				$year = $_POST['internal_award_year'];
				InternalAwardReceipt::create($award_id,$user_id,$year);
			}
		
		} elseif ($_POST['action'] == "remove_internal_award") {
			$id = (isset($_POST['students_award_id']) ? $_POST['students_award_id'] : 0);
			
			if ($id) {
				$recipient = InternalAwardReceipt::get($id);
				if ($recipient) {
					$recipient->delete();
				}
			}
		}
	}
}

/**
 * Outputs a table with awards for a given student. Includes admin functions
 * @param $user_id
 */
function display_internal_awards_admin($user) {
	global $db;
	if ($user) {
		$receipts = InternalAwardReceipts::get($user);
		
		?>
		<table class="mspr_table awards tableList"  cellspacing="0">
			<colgroup>
				<col width="78%"></col>
				<col width="15%"></col>
				<col width="3%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general">
						Award Title
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
				?>
				<tr>
					<td class="award">
						<a href="<?php echo ENTRADA_URL; ?>/admin/awards?section=award_details&id=<?php echo $award->getID(); ?>">
						<?php echo clean_input($award->getTitle(), array("notags", "specialchars")) ?></a>	
					</td>
					<td class="award_year">
						<?php echo clean_input($receipt->getAwardYear(), array("notags", "specialchars")) ?>	
					
					</td>
					<td class="controls">
						<form class="remove_internal_award_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="remove_internal_award"></input>
							<input type="hidden" name="students_award_id" value="<?php echo $receipt->getID(); ?>"></input>
							
							<input type="image" src="<?php echo ENTRADA_URL ?>/images/action-delete.gif"></input> 
						</form>
						
					</td>
				</tr>
				<?php 
				
			}
		}
		?>
		</tbody></table>
		<?php
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
		if ($_POST['action'] == "add_formal_remediation" && $user) {
				$details = $_POST['action_details'];
				FormalRemediation::create($user,$details);
			
		} elseif ($_POST['action'] == "remove_formal_remediation") {
			$formal_remediation_id = (isset($_POST['formal_remediation_id']) ? $_POST['formal_remediation_id'] : 0);
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
		if ($_POST['action'] == "add_leave_of_absence" && $user) {
				$details = $_POST['action_details'];
				LeaveOfAbsence::create($user,$details);
			
		} elseif ($_POST['action'] == "remove_leave_of_absence") {
			$absence_id = (isset($_POST['absence_id']) ? $_POST['absence_id'] : 0);
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
		if ($_POST['action'] == "approve_external_award") {
			$award_id = (isset($_POST['external_award_id']) ? $_POST['external_award_id'] : 0);
			if ($award_id) {
				$award = ExternalAwardReceipt::get($award_id);
				if ($award) {
					$award->approve();
				}
			}
		
		} elseif ($_POST['action'] == "unapprove_external_award") {
			$award_id = (isset($_POST['external_award_id']) ? $_POST['external_award_id'] : 0);
			if ($award_id) {
				$award = ExternalAwardReceipt::get($award_id);
				if ($award) {
					$award->unapprove();
				}
			}
		
		}
	}
}

/**
 * Outputs a table with awards for a given student. Includes profile functions
 * @param $user_id
 */
function display_external_awards_admin($user) {
	global $db;
	if ($user) {
		$receipts = ExternalAwardReceipts::get($user);
		
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
				$mode =  ($receipt->isApproved())? "unapprove" : "approve";
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
						<form class="<?php echo $mode; ?>_external_award_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="<?php echo $mode; ?>_external_award"></input>
							<input type="hidden" name="external_award_id" value="<?php echo $receipt->getID(); ?>"></input>
							
							<input type="image" src="<?php echo ENTRADA_URL ?>/images/btn-<?php echo $mode; ?>.gif"></input> 
						</form>
						
					</td>
				</tr>
				<?php 
				
			}
		}
		?>
		</tbody></table>
		<?php
	}
}

function display_contributions_admin($user) {
	global $db;
	if ($user) {
		$contributions = Contributions::get($user);
		
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
						Period
					</td>
					<td class="general">&nbsp;</td>
				</tr>
				</thead>
				<tbody>
		<?php 
		if ($contributions) {
			
			foreach($contributions as $contribution) {
				$mode =  ($contribution->isApproved())? "unapprove" : "approve";
				?>
				<tr<?php echo (!$contribution->isApproved())? " class=\"unapproved\"" : ""; ?>>
					<td class="award">
						<?php echo clean_input($contribution->getRole(), array("notags", "specialchars")) ?>	
					</td>
					<td class="award_terms">
						<?php echo clean_input($contribution->getOrgEvent(), array("notags", "specialchars")) ?>	
					
					</td>
					<td>
						<?php echo clean_input($contribution->getPeriod() , array("notags", "specialchars")) ?>	
					</td>
					<td class="controls">
						<form class="<?php echo $mode; ?>_contribution_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="<?php echo $mode; ?>_contribution"></input>
							<input type="hidden" name="contribution_id" value="<?php echo $contribution->getID(); ?>"></input>
							
							<input type="image" src="<?php echo ENTRADA_URL ?>/images/btn-<?php echo $mode; ?>.gif"></input> 
						</form>						
					</td>
				</tr>
				<?php 
				
			}
		}
		?>
		</tbody></table>
		<?php
	}
}

function process_contributions_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "approve_contribution") {
			$contribution_id = (isset($_POST['contribution_id']) ? $_POST['contribution_id'] : 0);
			if ($contribution_id) {
				$contribution = Contribution::get($contribution_id);
				if ($contribution) {
					$contribution->approve();
				}
			}
		
		} elseif ($_POST['action'] == "unapprove_contribution") {
			$contribution_id = (isset($_POST['contribution_id']) ? $_POST['contribution_id'] : 0);
			if ($contribution_id) {
				$contribution = Contribution::get($contribution_id);
				if ($contribution) {
					$contribution->unapprove();
				}
			}
		
		}
	}
}


function display_clerkship_details($rotations) {
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
						Period
					</td>
				</tr>
				</thead>
				<tbody>
				
		<?php 
		if ($rotations) {
			foreach($rotations as $rotation) {
				?>
				<tr>
					<td class="clerkship_details">
						<?php echo clean_input($rotation->getDetails(), array("notags", "specialchars", "nl2br")) ?>	
					</td>
					<td class="clerkship_period">
						<?php echo clean_input($rotation->getPeriod(), array("notags", "specialchars")) ?>	
					
					</td>
				</tr>
				<?php 
			}
		}
		?>
		</table>
		<?php	
}

function display_clerkship_core_completed($user) {
	global $db;
	if ($user) {
		
		$rotations = ClerkshipRotations::getCoreCompleted($user);
		return display_clerkship_details($rotations);
	}
}

function display_clerkship_core_pending($user) {
	global $db;
	if ($user) {
		
		$rotations = ClerkshipRotations::getCorePending($user);
		return display_clerkship_details($rotations);
	}
}

function display_clerkship_elective_completed($user) {
	global $db;
	if ($user) {
		
		$rotations = ClerkshipRotations::getElectiveCompleted($user);
		return display_clerkship_details($rotations);
	}
}

function display_student_run_electives_admin($user) {
	global $db;
	if ($user) {
		$sres = StudentRunElectives::get($user);
		?>
			<table class="mspr_table student-run-electives tableList" cellspacing="0">
			<colgroup>
				<col width="55%"></col>
				<col width="42%"></col>
				<col width="3%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general">
						Details
					</td>
					<td class="sortedDESC">
						Period
					</td>
					<td class="general">&nbsp;</td>
				</tr>
				</thead>
				<tbody>
		<?php 
		if ($sres) {
			
			foreach($sres as $sre) {
				?>
				<tr>
					<td class="award">
						<?php echo clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")) ?>	
					</td>
					<td class="award_terms">
						<?php echo clean_input($sre->getPeriod(), array("notags", "specialchars")) ?>	
					
					</td>
					<td class="controls">
						<form class="remove_student_run_elective_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="remove_student_run_elective"></input>
							<input type="hidden" name="sre_id" value="<?php echo $sre->getID(); ?>"></input>
							
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
	}
}

function process_student_run_electives_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_student_run_elective") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$group_name = $_POST['student_run_elective_group_name'];
			$university = $_POST['student_run_elective_university'];
			$location = $_POST['student_run_elective_location'];
			$start_year = $_POST['student_run_elective_start_year'];
				
			if ($user_id && $group_name && $university && $location && $start_year) {
				$end_year = $_POST['student_run_elective_end_year'];
				$start_month = $_POST['student_run_elective_start_month'];
				$end_month = $_POST['student_run_elective_end_month'];
								
				StudentRunElective::create($user, $group_name, $university, $location, $start_month, $start_year, $end_month, $end_year);
			}
		
		} elseif ($_POST['action'] == "remove_student_run_elective") {
			$id = (isset($_POST['sre_id']) ? $_POST['sre_id'] : 0);
			
			if ($id) {
				$sre = StudentRunElective::get($id);
				if ($sre) {
					$sre->delete();
				}
			}
		}
	}
}

function display_critical_enquiry_admin($user) {
	global $db;
	if ($user) {
		$sres = StudentRunElectives::get($user);
		?>
			<table class="mspr_table critical-enquiry tableList" cellspacing="0">
			<colgroup>
				<col width="55%"></col>
				<col width="42%"></col>
				<col width="3%"></col>
			</colgroup>
			<thead>
				<tr>
					<td class="general">
						Details
					</td>
				</tr>
				</thead>
				<tbody>
		<?php 
		if ($sres) {
			
			foreach($sres as $sre) {
				?>
				<tr>
					<td class="award">
						<?php echo clean_input($sre->getDetails(), array("notags", "specialchars", "nl2br")) ?>	
					</td>
					<td class="award_terms">
						<?php echo clean_input($sre->getPeriod(), array("notags", "specialchars")) ?>	
					
					</td>
					<td class="controls">
						<form class="remove_student_run_elective_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $user->getID(); ?>" method="post" >
							<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
							<input type="hidden" name="action" value="remove_student_run_elective"></input>
							<input type="hidden" name="sre_id" value="<?php echo $sre->getID(); ?>"></input>
							
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
	}
}

function process_student_run_electives_actions($user) {
	global $SUCCESS,$SUCCESSSTR,$ERROR,$ERRORSTR,$NOTICE,$NOTICESTR;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == "add_student_run_elective") {
			$user_id = (isset($_POST['user_id']) ? $_POST['user_id'] : 0);
			
			$group_name = $_POST['student_run_elective_group_name'];
			$university = $_POST['student_run_elective_university'];
			$location = $_POST['student_run_elective_location'];
			$start_year = $_POST['student_run_elective_start_year'];
				
			if ($user_id && $group_name && $university && $location && $start_year) {
				$end_year = $_POST['student_run_elective_end_year'];
				$start_month = $_POST['student_run_elective_start_month'];
				$end_month = $_POST['student_run_elective_end_month'];
								
				StudentRunElective::create($user, $group_name, $university, $location, $start_month, $start_year, $end_month, $end_year);
			}
		
		} elseif ($_POST['action'] == "remove_student_run_elective") {
			$id = (isset($_POST['sre_id']) ? $_POST['sre_id'] : 0);
			
			if ($id) {
				$sre = StudentRunElective::get($id);
				if ($sre) {
					$sre->delete();
				}
			}
		}
	}
}