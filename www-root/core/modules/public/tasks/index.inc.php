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
} elseif (!$ENTRADA_ACL->amIAllowed("task", "read", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	require_once("Models/tasks/Tasks.class.php");
	require_once("Models/tasks/TaskCompletions.class.php");
	$user = User::get($PROXY_ID);
	
	$sort_by = 'deadline';
	$sort_order = 'asc';
	
	if (isset($_GET['sb'])) {
		$sort_by = $_GET['sb'];
	}
	if (isset($_GET['so'])) {
		$sort_order = $_GET['so'];
	} 
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = $sort_by;
	$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = $sort_order;
	
	//$tasks = Tasks::getByRecipient($user,array('order_by' => $sort_by, 'dir' => $sort_order/*, 'limit' => 25, 'offset'=>0*/ )); //no limit for now. TODO work on pagination later.
	$task_verifications = TaskCompletions::getByVerifier($user->getID(), array("where" => "`verified_date` IS NULL" ));
	$has_verification_requests = (count($task_verifications) > 0);
	$task_completions = TaskCompletions::getByRecipient($user, array('order_by'=>array(array($sort_by, $sort_order))));
	$has_completions = (count($task_completions) > 0);
	?>
	
	<h1>My Tasks</h1>
	
	<?php 
	if ($has_verification_requests) {
	?>
	<div class="display-notice"><h3>Outstanding Verifications</h3>You have outstanding task verification requests. Please go to the <a href="<?php echo ENTRADA_URL;?>/tasks?section=verification">Task Verification</a> page to manage them.</div>
	<?php 
	} 
	if ($has_completions) {
	?>
	<!--  Include something similar to learning event calendar/range select here -->
	<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
		<colgroup>
			<col class="status" width="3%" />
			<col class="deadline" />
			<col class="course" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td>&nbsp;</td>
				<td class="deadline<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "deadline") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("deadline", "Deadline"); ?></td>
				<td class="course">Course</td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo public_order_link("title", " Task Title"); ?></td>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach ($task_completions as $task_completion) { 
			$task = $task_completion->getTask();
			?>
		
			<tr>
				<td>
					<?php
					if ($task_completion->isCompleted()) { 
						if ($task->isVerificationRequired()) {
							if ($task_completion->isVerified()) {
								?>
								<img src="<?php echo ENTRADA_URL?>/images/task_completed.png" alt="Task Completed" title="Task Completed" />
								<?php
							} else {
								?>
								<img src="<?php echo ENTRADA_URL?>/images/task_pending.png" alt="Pending Verification" title="Pending Verification" />
								<?php
							}
						} else {
							?>
							<img src="<?php echo ENTRADA_URL?>/images/task_completed.png" alt="Task Completed" title="Task Completed" />
							<?php
						}
					} else { 
						echo "&nbsp;"; 
					}
					?> 
				</td>
				<td><a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $task->getID(); ?>"><?php echo ($task->getDeadline()) ? date(DEFAULT_DATE_FORMAT,$task->getDeadline()) : ""; ?></a></td>
				<td><?php 
					$course = $task->getCourse();
					if ($course) {
						?><a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $task->getID(); ?>">
						<?php echo $course->getTitle(); ?></a>
					<?php
					}
				?></td>
				<td><a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $task->getID(); ?>"><?php echo $task->getTitle(); ?></a></td>
			</tr>
		<?php } ?>
		</tbody>	
	</table>
	<?php
	} else {
		?>
		<div class="display-notice"><h3>No Matching Tasks</h3>
			<?php
			$message = "There are no tasks scheduled"; 
		
			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
				case "day" :
					$message .= " that take place on <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong>";
				break;
				case "month" :
					$message .= " that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>";
				break;
				case "year" :
					$message .= " that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>";
				break;
				default :
				case "week" :
					$message .= " from <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_end"])."</strong>";
				break;
				default :
					continue;
				break;
			}
			$message .= (($filters_applied) ? " that also match the supplied &quot;Show Only&quot; restrictions." : ".");
			echo $message; 
			?>
			<br /><br />
			If this is unexpected there are a few things that you can check:
			<ol>
				<li style="padding: 3px">Make sure that you are browsing the intended time period. For example, if you trying to browse <?php echo date("F", time()); ?> of <?php echo date("Y", time()); ?>, make sure that the results bar above says &quot;... takes place in <strong><?php echo date("F", time()); ?></strong> of <strong><?php echo date("Y", time()); ?></strong>&quot;.</li>
				<?php
				if ($filters_applied) {
					echo "<li style=\"padding: 3px\">You also have ".$filters_total." filter".(($filters_total != 1) ? "s" : "")." applied to the event list. you may wish to remove ".(($filters_total != 1) ? "one or more of these" : "it")." by clicking the link in the &quot;Showing Events That Include&quot; box above.</li>";
				}
				?>
			</ol>
		</div>
		<?php
	}
}