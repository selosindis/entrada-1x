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
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskVerificationResource($TASK_ID, $RECIPIENT_ID, $PROXY_ID), "update")) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
	
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
		if ($RECIPIENT_ID && ($recipient = User::get($RECIPIENT_ID))) {
		
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verification", "title" => "Task Verification");
			$BREADCRUMB[] = array("url" => ENTRADA_URL."/tasks?section=verify&id=".$TASK_ID."&recipient=".$RECIPIENT_ID, "title" => "Verify Task Completion");
			
			$user = User::get($PROXY_ID);
		
			$course = $task->getCourse();
			$completion = TaskCompletion::get($TASK_ID, $RECIPIENT_ID);
			
			switch($_POST['action']) {
				case "Submit":
					if ($task->isVerificationRequired()) {
						if (isset($_POST['task_verify'])) {
							switch ($_POST['task_verify']) {
								case 1:
									$mode = "verify";
									$c_time = $completion->getCompletedDate();
									$completion->update($c_time,$user->getID(), time());
									task_verification_notification(	"confirm",
																	array(
																		"firstname" => $recipient->getFirstname(),
																		"lastname" => $recipient->getLastname(),
																		"email" => $recipient->getEmail()),
																	array(
																		"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
																		"from_firstname" => $user->getFirstname(),
																		"from_lastname" => $user->getLastname(),
																		"task_title" => $task->getTitle(),
																		"application_name" => APPLICATION_NAME . " Task System"
																		));
									break;
								case 0:
									$mode = "decline";
									if (isset($_POST['reason']) && ($reason = $_POST['reason'])) {
										$completion->update(null,null,null);
										task_verification_notification(	"denial",
																		array(
																			"firstname" => $recipient->getFirstname(),
																			"lastname" => $recipient->getLastname(),
																			"email" => $recipient->getEmail()),
																		array(
																			"to_fullname" => $recipient->getFirstname(). " " . $recipient->getLastname(),
																			"from_firstname" => $user->getFirstname(),
																			"from_lastname" => $user->getLastname(),
																			"task_title" => $task->getTitle(),
																			"application_name" => APPLICATION_NAME . " Task System",
																			"reason" => $reason
																			));
									} else {
										add_error("You must supply a reason for declining this verification request.");
									}
									break;
								default:
									add_error("Unknown verification type selected.");
							}
						}
					} else {
						add_error("This task does not require verification.");
					}
					
					if (!has_error()) {
						clear_success();
						$task_verifications = TaskCompletions::getByVerifier($user->getID(), array("where" => "`verified_date` IS NULL" ));
						$has_verification_requests = (count($task_verifications) > 0);
	
						if ($has_verification_requests) {
							$url = ENTRADA_URL."/tasks?section=verification";
							$page_title = "Task Verification";
						} else {
							$url = ENTRADA_URL."/tasks";
							$page_title = "Task List";
						}
						
						header( "refresh:5;url=".$url );
						switch($mode) {
							case "verify":
								add_success("<p>You have successfully <strong>verified</strong> completion of the <strong>".html_encode($task->getTitle())."</strong> task by <strong>". $recipient->getFirstname() . " " . $recipient->getLastname() ."</strong>.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
								break;
							case "decline":
								add_success("<p>You have successfully <strong>declined verification</strong> of completion of the <strong>".html_encode($task->getTitle())."</strong> task by <strong>". $recipient->getFirstname() . " " . $recipient->getLastname() ."</strong>.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
								break;
						}
						display_status_messages();
						break;
					}
					
				default:
					
					if (!($completion->isVerified())) {
					?>
			<h1>Task Verification Request</h1>
			
			<?php display_status_messages(); ?>
			
			<div><?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?> has asked you to verify that he or she completed the task, <a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $TASK_ID; ?>"><?php echo $task->getTitle(); ?></a>, on <?php echo date(DEFAULT_DATE_FORMAT,$completion->getCompletedDate()); ?>.</div>
			
			<form method="post"  id="task_verify_form">
				<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>"/>
				<input type="hidden" name="recipient_id" value="<?php echo $RECIPIENT_ID; ?>"/>
				<table>
					<colgroup>
						<col width="3%"></col>
						<col width="25%"></col>
						<col width="72%"></col>
					</colgroup>
					<tbody>
						<tr>
							<td>
								<input type="radio" id="task_verify_yes" name="task_verify" value="1" checked="checked" />
							</td>
							<td colspan="2">
								<label for="task_verify_yes" class="form-nrequired">Task Completed</label>
							</td>
						</tr>
						<tr>
							<td>
								<input type="radio" id="task_verify_no" name="task_verify" value="0" />
							</td>
							<td colspan="2">
								<label for="task_verify_no" class="form-nrequired">Task Not Completed</label>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3">
								<input type="submit" name="action" value="Submit" />
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
			
			<h2 title="Task Details" class="collapsed">Task Details: <?php echo html_encode($task->getTitle()); ?></h2>
				<div id="task-details">
				<table class="task_details">
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
							<td><a href="<?php echo ENTRADA_URL; ?>/courses?id=<?php echo $course_id; ?>"><?php echo $course_title; ?></a></td>
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
							<td>&nbsp;</td>
							<td>Description</td>
							<td><?php echo html_encode($task->getDescription()); ?></td>
						</tr>
				</table>
				</div>
				
				<form id="decline_verification_form" method="post">
					<input type="hidden" name="task_id" value="<?php echo $TASK_ID; ?>"/>
					<input type="hidden" name="recipient_id" value="<?php echo $RECIPIENT_ID; ?>"/>
					<input type="hidden" id="task_verify" name="task_verify" value="1" />
					<input type="hidden" id="task_verify_details" name="reason" value="" />
					<input type="hidden" name="action" value="Submit" />
				</form>
		
				<div id="reject-verify-box" class="modal-confirmation" style="height: 300px">
					<h1>Decline <strong>Verification</strong> Request</h1>
					<div class="display-notice">
						Please confirm that you <strong>do not</strong> wish to verify the completion of this task by <?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?>.
					</div>
					<p>
						<label for="reject-verify-details" class="form-required">Please provide an explanation for this decision:</label><br />
						<textarea id="reject-verify-details" name="reject_verify_details" style="width: 99%; height: 75px" cols="45" rows="5"></textarea>
					</p>
					<div class="footer">
						<button class="left" onclick="Control.Modal.close()">Close</button>
						<button class="right" id="reject-verify-confirm">Reject</button>
					</div>
				</div>
		
				<script type="text/javascript">
				
				document.observe('dom:loaded', function() {
					var verify_modal = new Control.Modal('reject-verify-box', {
						overlayOpacity:	0.75,
						closeOnClick:	'overlay',
						className:		'modal-confirmation',
						fade:			true,
						fadeDuration:	0.30
					});

					$('task_verify_form').observe('submit',function (e) {
						if ($('task_verify_no').checked) {
							Event.stop(e);
							verify_modal.open();
						}
					});
					
		
					Event.observe('reject-verify-confirm', 'click', function() {
						$('task_verify').setValue('0');
		
						if ($('reject-verify-details')) {
							$('task_verify_details').setValue($('reject-verify-details').getValue());
						}
						$('decline_verification_form').submit();
					});
				});
				</script>
					<?php
					} else {
					?>
				<h1>Task Verification</h1>
				
				<div>Completion of this task has already been verified for <?php echo $recipient->getFirstname() . " " . $recipient->getLastname(); ?>.</div>
						
					<?php
					}
			
			}
		} else {
			header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
			
			add_error("In order to verify a task you must provide a valid recipient identifier.");
	
			echo display_error();
	
			application_log("notice", "Failed to provide valid recipient identifer when attempting to verify a task.");
		}
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/".$MODULE );
		
		add_error("In order to view task details you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to view a task.");
	}
}
