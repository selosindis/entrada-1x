<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.finglan@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_TASKS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new TaskResource($TASK_ID), "update")) {
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
	require_once("Models/tasks/TaskCompletions.class.php");
	require_once("Models/users/User.class.php");
	require_once("Models/users/GraduatingClass.class.php");
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=completion&id=".$TASK_ID, "title" => "Task Completion Information");
		
		$task_completions = TaskCompletions::getByTask($TASK_ID, array("order_by" => array(array("lastname","asc"), array("firstname", "asc") ) ));
		
		$BULK_COMPLETE = "Verify Complete";
		
		switch ($_POST['action']) {
			case $BULK_COMPLETE:
				$recipients_to_complete = $_POST['complete_verify'];
				if (!$recipients_to_complete || !is_array($recipients_to_complete)){
					add_error("No recipients were selected for task completion");
				} else {
					//NOTE: This makes no distunction between verification required vs. not required. This is to ensure there is a record of who "completed" the task.
					foreach ($recipients_to_complete as $recipient_id) {
						$completion = TaskCompletion::get($TASK_ID,$recipient_id);
						$completion->update(time(),$PROXY_ID,time());
					}
					if (!has_error()) {
						clear_success();
						add_success("Successfully updated task completion information for ".html_encode($task->getTitle()));
						$url = ENTRADA_URL."/admin/tasks?section=completion&id=".$TASK_ID;
						$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
						display_status_messages();
						break;
					}
				}
			default: 
			?>	
			<h1><?php echo html_encode($task->getTitle());?>: Completion Information</h1>
			<?php display_status_messages();
			if ($task->isVerificationRequired()) {
			?>
			
			<form id="completion_list" method="post">
				<input type="hidden" name="task_id" value="<?php echo $task->getID(); ?>" />
				<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
					<colgroup>
						<col class="general" width="3%" />
						<col class="general" width="25%" />
						<col class="general" width="23%" />
						<col class="general" width="25%" />
						<col class="general" width="24%" />
						
					</colgroup>
					<tfoot>
						<tr>
						<td colspan="5">
						<input type="submit" value="<?php echo $BULK_COMPLETE; ?>" name="action" />
						</td>
						</tr>
					</tfoot>
					<thead>
						<tr>
							<td><input type="checkbox" id="check_all" /></td>
							<td>Recipient</td>
							<td>Task Completion</td>
							<td>Verifier</td>
							<td>Verification Date</td>
						</tr>
					</thead>
					<tbody>
						<?php 
							foreach ($task_completions as $task_completion) {
							$recipient = $task_completion->getRecipient();
							$verifier = $task_completion->getVerifier();			 
						?>
						<tr>
							<td>
								<?php if ($verifier) { ?>
								<img src="<?php echo ENTRADA_URL?>/images/accept.png" />
								<?php } else { ?> 
								<input type="checkbox" name="complete_verify[]" value="<?php echo $recipient->getID(); ?>" />
								<?php } ?>
							</td>
							<td>
								<a href="<?php echo ENTRADA_URL; ?>/admin/users/manage?id=<?php echo $recipient->getID(); ?>"><?php echo $recipient->getFullname(); ?></a> 
							</td>
							<td>
								<?php echo ($task_completion->isCompleted())? date(DEFAULT_DATE_FORMAT, $task_completion->getCompletedDate()) : "&nbsp;"?>
							</td>
							<td>
								<?php if ($verifier) { ?>
								<a href="<?php echo ENTRADA_URL; ?>/admin/users/manage?id=<?php echo $recipient->getID(); ?>"><?php echo $verifier->getFullname(); ?></a>
								<?php } else { ?> 
								&nbsp;
								<?php } ?>
							</td>
							<td>
								<?php echo ($task_completion->isVerified())? date(DEFAULT_DATE_FORMAT, $task_completion->getVerifiedDate()) : "&nbsp;"?>
							</td>
						</tr>
						<?php 
							} 
						?>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
			function checkAll(event) {
				var state = Event.findElement(event).checked;
				//var state = $$("#mspr-class-list thead input[type=checkbox]").pluck("checked").any();
				$$("#completion_list tbody input[type=checkbox]").reject(isDisabled).each(function (el) { el.checked=state; });
			}
	
			function areAllChecked() {
				return $$("#completion_list tbody input[type=checkbox]").reject(isDisabled).pluck("checked").all();
			}
	
			function isDisabled(el) {
				return el.disabled;
			}
	
			function setCheckAll() {
				var state = areAllChecked();
				$$("#completion_list thead input[type=checkbox]").each(function (el) { el.checked=state; });
			}
	
			document.observe("dom:loaded",function() { 
					$$("#completion_list tbody input[type=checkbox]").invoke("observe","click",setCheckAll);
					$$("#completion_list thead input[type=checkbox]").invoke("observe","click",checkAll);
				});
			</script>
			<?
			} else {
			?>
			<form id="completion_list" method="post">
				<input type="hidden" name="task_id" value="<?php echo $task->getID(); ?>" />
				<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
					<colgroup>
						<col class="general" width="3%" />
						<col class="general" width="48%" />
						<col class="general" width="49%" />
						
					</colgroup>
					<tfoot>
						<tr>
						<td colspan="3">
						<input type="submit" value="<?php echo $BULK_COMPLETE; ?>" name="action" />
						</td>
						</tr>
					</tfoot>
					<thead>
						<tr>
							<td><input type="checkbox" id="check_all" /></td>
							<td>Recipient</td>
							<td>Task Completion</td>
						</tr>
					</thead>
					<tbody>
						<?php 
							foreach ($task_completions as $task_completion) {
							$recipient = $task_completion->getRecipient();
						?>
						<tr>
							<td>
								<?php if ($task_completion->isCompleted()) { ?>
								<img src="<?php echo ENTRADA_URL?>/images/accept.png" />
								<?php } else { ?> 
								<input type="checkbox" name="complete_verify[]" value="<?php echo $recipient->getID(); ?>" />
								<?php } ?>
							</td>
							<td>
								<a href="<?php echo ENTRADA_URL; ?>/admin/users/manage?id=<?php echo $recipient->getID(); ?>"><?php echo $recipient->getFullname(); ?></a> 
							</td>
							<td>
								<?php echo ($task_completion->isCompleted())? date(DEFAULT_DATE_FORMAT, $task_completion->getCompletedDate()) : "&nbsp;"?>
							</td>
						</tr>
						<?php 
							} 
						?>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
			function checkAll(event) {
				var state = Event.findElement(event).checked;
				//var state = $$("#mspr-class-list thead input[type=checkbox]").pluck("checked").any();
				$$("#completion_list tbody input[type=checkbox]").reject(isDisabled).each(function (el) { el.checked=state; });
			}
	
			function areAllChecked() {
				return $$("#completion_list tbody input[type=checkbox]").reject(isDisabled).pluck("checked").all();
			}
	
			function isDisabled(el) {
				return el.disabled;
			}
	
			function setCheckAll() {
				var state = areAllChecked();
				$$("#completion_list thead input[type=checkbox]").each(function (el) { el.checked=state; });
			}
	
			document.observe("dom:loaded",function() { 
					$$("#completion_list tbody input[type=checkbox]").invoke("observe","click",setCheckAll);
					$$("#completion_list thead input[type=checkbox]").invoke("observe","click",checkAll);
				});
			</script>
			<?php
			}
		}
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/admin/".$MODULE );
		
		add_error("In order to edit a task you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to edit a task.");
	}
}
		