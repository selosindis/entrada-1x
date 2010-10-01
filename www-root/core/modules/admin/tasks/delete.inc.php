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
} elseif (!$ENTRADA_ACL->amIAllowed("task", "delete", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=delete", "title" => "Delete Tasks");
	
	$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
		
	require_once("Models/tasks/Tasks.class.php");
	require_once("Models/users/User.class.php");
	
	$user = User::get($PROXY_ID);
	
	if (isset($_POST['tasks']) && is_array($_POST['tasks'])) {
		$task_ids = $_POST['tasks'];
		$tasks = new Tasks();
		foreach ($task_ids as $task_id) {
			$task = Task::get($task_id);
			if ($ENTRADA_ACL->amIAllowed(New TaskResource($task_id,null,$ORGANISATION_ID), "delete")) {
				$tasks->push($task);
			} else {
				add_notice("You do not have permission to delete the task ".html_encode($task->getTitle())." so it was omitted from this list.");
			}
		}
		if (isset($_POST['delete']) && ($_POST['delete'] == 'Confirm Delete')) {
			foreach ($tasks as $task) {
				$task->delete();
				if (has_success()) {
					clear_success();
					$delete_successes[] = html_encode($task->getTitle());
				}
			}
			clear_success();
			
			if ($delete_successes) {
				$success_listing = "<ul>";
				foreach ($delete_successes as $delete_success) {
					$success_listing .= "<li><strong>".$delete_success."</strong></li>";
				}
				$success_listing .= "</ul>";
				$url = ENTRADA_URL."/admin/tasks";
				$page_title = "Manage Tasks";
				add_success("<p>You have successfully <strong>deleted</strong> the following tasks:</p>".$success_listing."<p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
				header( "refresh:5;url=".$url );
			}
			display_status_messages();
			$done = true;
		}
	}
	if (!$done) {
		?>
		
		<h1>Delete Tasks</h1>
		
		<?php display_status_messages(); ?>
		
		<?php if (count($tasks) > 0) { ?>
			
		<form method="post">
		<p>Are you sure you want to delete the following tasks?</p>
		<ul>
			<?php
			foreach ($tasks as $task) {
			?>
			<li>
				<?php echo html_encode($task->getTitle()); ?>
				<input type="hidden" name="tasks[]" value="<?php echo $task->getID(); ?>" />
			</li>
			<?php
			}
			?>
		</ul>
		<input type="submit" name="delete" value="Confirm Delete" />
		</form>
		<?php
		} else {
		?>
		<p>Nothing to do.</p>
		<?php
		}
	}
}
