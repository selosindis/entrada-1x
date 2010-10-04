<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Allows administrators to edit users from the entrada_auth.user_data table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_TASKS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskResource($TASK_ID, null, $ORGANISATION_ID), "read")) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	require_once("Models/courses/Courses.class.php");
	require_once("Models/organisations/Organisations.class.php");
	require_once("Models/tasks/Tasks.class.php");
	require_once("Models/tasks/TaskOwners.class.php");
	require_once("Models/tasks/TaskRecipients.class.php");
	require_once("Models/tasks/TaskCompletion.class.php");
	require_once("Models/users/User.class.php");
	require_once("Models/users/GraduatingClass.class.php");
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=details&id=".$TASK_ID, "title" => "Task Details");
		
		$user = User::get($PROXY_ID);
	
		$course = $task->getCourse();
		$completion = TaskCompletion::get($task->getID(), $user->getID());
		
		switch($_POST['action']) {
			case "Submit":
				if (!isset($_POST['task_completed']) || $_POST['task_completed'] != "completed") {
					add_error("You must check the box \"I have completed this task\" when indicating the task is completed.");
				} else {
					$PROCESSED['task_completed'] = "completed";
				}
				if ($task->isVerificationRequired()) {
					if (isset($_POST['associated_individual']) && $_POST['associated_individual']) {
						$verifier = User::get($_POST['associated_individual']);
						if ($verifier) {
							$PROCESSED['associated_individual'] = $_POST['associated_individual'];
						} else {
							add_error("Supplied verifier not found or not permitted to verify completion of this task.");
						}
					} else {
						add_error("This task requires verification of completion. Please enter and select your verifier below.");
					}
				} else {
					$verifier = $user; //self-verification
					$verification_date = time();
				}
				if (!has_error()) {
					$completion->update(time(),$verifier->getID(), $verification_date);
					if ($task->isVerificationRequired()) { //don't email if verification isn't required
						task_verification_notification(	"request",
														array(
															"firstname" => $verifier->getFirstname(),
															"lastname" => $verifier->getLastname(),
															"email" => $verifier->getEmail()),
														array(
															"to_fullname" => $verifier->getFirstname(). " " . $verifier->getLastname(),
															"from_firstname" => $user->getFirstname(),
															"from_lastname" => $user->getLastname(),
															"task_title" => $task->getTitle(),
															"application_name" => APPLICATION_NAME . " Task System",
															"task_verification_url" => ENTRADA_URL."/tasks?section=verify&id=".$task->getID()."&recipient=".$user->getID()
															));
					}
				}
				
				if (!has_error()) {
					clear_success();
					$page_title = "Task Details";
					$url = ENTRADA_URL."/tasks?section=details&id=".$TASK_ID;
					if (!$task->isVerificationRequired()) {
						add_success("<p>You have successfully submitted <strong>completion</strong> of the <strong>".html_encode($task->getTitle())."</strong> task.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
					} else {
						add_success("<p>You have successfully <strong>requested verification</strong> from <strong>".$verifier->getFirstname() . " " . $verifier->getLastname()."</strong> for completion the <strong>".html_encode($task->getTitle())."</strong> task.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
					}
						header( "refresh:5;url=".$url );
						display_status_messages();
					break;
				}
				
			default:
		?>
		
		<h1>Task Details: <?php echo html_encode($task->getTitle()); ?></h1>
		<?php display_status_messages(); ?>
		
		<form method="post">
			<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>" />
			<table id="task_details">
				<colgroup>
					<col width="3%"></col>
					<col width="25%"></col>
					<col width="72%"></col>
				</colgroup>
				<tbody>
					<?php if ($course = $task->getCourse()) {
						$course_title = $course->getTitle();
						$course_id = $course->getID();
						?>
					<tr>
						<td>&nbsp;</td>
						<td>Course</td>
						<td><a href="<?php echo ENTRADA_URL; ?>/courses?id=<?php echo $course_id; ?>"><?php echo html_encode($course_title); ?></a></td>
					</tr>
					<?php 
						}
						if ($time_required = $task->getDuration()) {
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Estimated Time Required</td>
						<td><?php echo html_encode($time_required); ?> minutes</td>
					</tr>
					<?php	
						}
						if ($deadline = $task->getDeadline()) {	
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Deadline</td>
						<td><?php echo date(DEFAULT_DATE_FORMAT,$task->getDeadline()); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php
						if ($description = $task->getDescription()) {
					?>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">
							<h2>Description</h2>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2"><?php echo clean_input($description,array("allowedtags")); ?></td>
					</tr>
					<?php 
						} 
						if ($task->isRecipient($user)) { //might be allowed to view, but not actually a recipient. e.g. admin. 
					?>
					<tr>
						<td colspan="3"><h2>Task Completion</h2></td>
					</tr>
					<?php if ($completion->isCompleted()) { ?>
					<tr>
						<td>&nbsp;</td>
						<td>Task Completed</td>
						<td><?php echo date(DEFAULT_DATE_FORMAT,$completion->getCompletedDate()); ?></td>
					</tr>
					<?php 
						if ($task->isVerificationRequired()) {
							$verifier = $completion->getVerifier();
							if ($completion->isVerified()) {
								$v_date = $completion->getVerifiedDate();
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Verified By</td>
						<td><?php echo $verifier->getFullname(); ?></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>Verified On</td>
						<td><?php echo date(DEFAULT_DATE_FORMAT,$v_date); ?></td>
					</tr>
					<?php 
							} else {
					?>
					<tr>
						<td>&nbsp;</td>
						<td>Verification Request</td>
						<td><?php echo $verifier->getFullname(); ?></td>
					</tr>
					<?php
							}
						}
					} else {
					?> 
					<tr>
						<td>
							<input type="checkbox" name="task_completed" id="task_completed" value="completed" <?php echo ($PROCESSED['task_completed'] == 'completed') ? "checked=\"checked\"" : "" ; ?> />
						</td>
						<td colspan="2"><label for="task_completed">I have completed this task</label></td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php if ($task->isVerificationRequired()) { ?>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">This task requires verification of completion. In the space provided below, please enter the name of the individual that can confirm completion of this task.</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td  style="vertical-align: top"><label class="form-required" for="fullname">Verifier Name</label></td>
						<td>
						
							<input type="text" id="individual_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle:margin-bottom:15px;" />
							<?php
								$ONLOAD[] = "individual_list = new AutoCompleteList({ type: 'individual', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=facultyorstaff', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif', limit: 1})";
							?>
							<div class="autocomplete" id="individual_name_auto_complete"></div><script type="text/javascript"></script>
							<input type="hidden" id="associated_individual" name="associated_individual" />
							<input type="button" class="button-sm" id="add_associated_individual" value="Add" style="vertical-align: middle" />
							<span id="individual_example" class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="individual_list" class="menu">
								<?php
								if ($proxy_id = $PROCESSED['associated_individual']) {
									if ($individual = User::get($proxy_id)) {
										?>
										<li class="community" id="individual_<?php echo $individual->getID(); ?>" style="cursor: move;"><?php echo $individual->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="individual_list.removeItem('<?php echo $individual->getID(); ?>');" class="list-cancel-image"/></li>
										<?php
									}
								}
								?>
							</ul>
							<input type="hidden" id="individual_ref" name="individual_ref" value="" />
							<input type="hidden" id="individual_id" name="individual_id" value="" />
						
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
					<td colspan="3">
					<input type="Submit" value="Submit" name="action"></input>
					</td> 
					</tr>
				</tfoot>
					<?php } 
					}
					?>
			</table>
		</form>
		<?php
		}
		
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
		
		add_error("In order to view task details you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to view a task.");
	}
}
