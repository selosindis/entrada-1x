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
	
	$tasks = Tasks::getByRecipient($user,array('order_by' => $sort_by, 'dir' => $sort_order/*, 'limit' => 25, 'offset'=>0*/ )); //no limit for now. TODO work on pagination later.
	
	?>
	
	<h1>My Tasks</h1>
	
	<!--  Include something similar to learning event calendar/range select here -->
	<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Events">
		<colgroup>
			<col class="modified" />
			<col class="deadline" />
			<col class="course" />
			<col class="title" />
			<col class="attachment" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="deadline<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "deadline") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("deadline", "Deadline"); ?></td>
				<td class="course">Course</td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", " Task Title"); ?></td>
				<td class="attachment">&nbsp;</td>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($tasks as $task) { ?>
			<tr>
				<td>&nbsp;</td>
				<td><?php echo ($task->getDeadline()) ? date(DEFAULT_DATE_FORMAT,$task->getDeadline()) : ""; ?></td>
				<td><?php 
					$course = $task->getCourse();
					if ($course) {
						?><a href="<?php echo ENTRADA_URL; ?>/courses?id=<?php echo $course->getID(); ?>">
						<?php echo $course->getTitle(); ?></a>
					<?php
					}
				?></td>
				<td><a href="<?php echo ENTRADA_URL; ?>/tasks?section=details&id=<?php echo $task->getID(); ?>"><?php echo $task->getTitle(); ?></a></td>
				<td>&nbsp;</td>
			</tr>
		<?php } ?>
		</tbody>	
	</table>
	<?php

}