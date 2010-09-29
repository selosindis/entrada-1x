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
} elseif (!$ENTRADA_ACL->amIAllowed("task", "create", false)) {
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
	require_once("Models/users/User.class.php");
	require_once("Models/users/GraduatingClass.class.php");
	
	if ($TASK_ID && ($task = Task::get($TASK_ID))) {
		
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=edit&id=".$TASK_ID, "title" => "Edit Task");
		
		//set defaults
		$PROCESSED = array();
		$PROCESSED["task_recipient_type"] = TASK_DEFAULT_RECIPIENT_TYPE;
		$PROCESSED['require_verification'] = TASK_DEFAULT_REQUIRE_VERIFICATION;
		
		
		if (isset($_POST['action']) && ($_POST['action'] === 'Save')) {
			if ($_POST['title'] && ($task_title = clean_input($_POST['title'], array("notags","trim")))) {
				$PROCESSED['title'] = $task_title;
			} else {
				add_error("The <strong>Task Title</strong> field is required.");
			}
			
			
			
			/**
			 * Required field "course_id" / Course
			 */
			if((isset($_POST["course_id"])) &&($course_id = clean_input($_POST["course_id"], array("int"))) ) {
				$course = Course::get($course_id);
				if ($course) {
					if ($ENTRADA_ACL->amIAllowed(new TaskResource(null,$course_id,$ORGANISATION_ID), "create")) {
						$PROCESSED['course_id'] = $course_id;
					} else {
						add_error("You do not have permission to add a task for the course you selected. <br />Please re-select the course you would like to associate with this task.");
						application_log("error", "A program coordinator attempted to add a task to a course [".$course_id."] they were not the coordinator of.");
					}
				} else {
					add_error("The <strong>Course</strong> you selected does not exist.");
				}
			}
			
			$deadline = validate_calendar("Deadline","task_deadline");
			if((isset($deadline)) && ((int) $deadline)) {
				$PROCESSED["deadline"] = (int) $deadline;
			}
			
			
			if (isset($_POST["time_required"]) && ($time_required = clean_input($_POST['time_required']))) {
				$PROCESSED['time_required'] = $time_required;
			}
			
			if (isset($_POST['description']) && (clean_input($_POST["description"], array("notags", "nows")))) {
				$PROCESSED['description'] = clean_input($_POST["description"], array("allowedtags"));
			}
					
			
			if ($_POST['require_verification']) {
				$PROCESSED['require_verification'] = TASK_VERIFICATION_REQUIRED;
			} else {
				$PROCESSED['require_verification'] = TASK_VERIFICATION_NOT_REQUIRED;
			}
			
			if(isset($_POST["task_recipient_type"])) {
				$PROCESSED["task_recipient_type"] = clean_input($_POST["task_recipient_type"], array("page_url"));
	
				switch($PROCESSED["task_recipient_type"]) {
					case "grad_year" :
					/**
					 * Required field "associated_grad_years" / Graduating Year
					 * This data is inserted into the task_recipient table as grad_year.
					 */
						if((isset($_POST["associated_grad_years"]))) {
							$associated_grad_years = explode(',', $_POST["associated_grad_years"]);
							if((isset($associated_grad_years)) && (is_array($associated_grad_years)) && (count($associated_grad_years))) {
								foreach($associated_grad_years as $year) {
									if($year = clean_input($year, array("trim", "int"))) {
										$PROCESSED["associated_grad_years"][] = $year;
									}
								}
								if(!count($PROCESSED["associated_grad_years"])) {
									add_error("You have chosen <strong>Entire Class Task</strong> as <strong>Task Recipients</strong>, but have not selected any graduating years.");
								}
							} else {
								add_error("You have chosen <strong>Entire Class Task</strong> as an <strong>Task Recipients</strong> type, but have not selected any graduating years.");
							}
						}
	
						break;
					case "group_id" :
						add_error("The <strong>Group Task</strong> as <strong>Task Recipients</strong> type, has not yet been implemented.");
						break;
					case "proxy_id" :
					/**
					 * Required field "associated_proxy_ids" / Associated Students
					 * This data is inserted into the task_recipients table as proxy_id.
					 */
						if((isset($_POST["associated_individual"]))) {
							$associated_proxies = explode(',', $_POST["associated_individual"]);
							if((isset($associated_proxies)) && (is_array($associated_proxies)) && (count($associated_proxies))) {
								foreach($associated_proxies as $proxy_id) {
									if($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
										$query = "	SELECT a.*
													FROM `".AUTH_DATABASE."`.`user_data` AS a
													LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
													ON a.`id` = b.`user_id`
													WHERE a.`id` = ".$db->qstr($proxy_id)."
													AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
													AND b.`account_active` = 'true'
													AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
													AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
										$result	= $db->GetRow($query);
										if($result) {
											$PROCESSED["associated_proxy_ids"][] = $proxy_id;
										}
									}
								}
								if(!count($PROCESSED["associated_proxy_ids"])) {
									add_error("You have chosen <strong>Individual Task</strong> as <strong>Task Recipients</strong> type, but have not selected any individuals.");
								}
							} else {
								add_error("You have chosen <strong>Individual Task</strong> as a <strong>Task Recipients</strong> type, but have not selected any individuals.");
							}
						}
						break;
					case "organisation_id":
						if ($PROCESSED['require_verification'] == TASK_VERIFICATION_REQUIRED) {
							add_error("Task completion verification is not available for an Organisation-wide recipient list.");
						} elseif((isset($_POST["associated_organisation_id"])) && ($associated_organisation_id = clean_input($_POST["associated_organisation_id"], array("trim", "int")))) {
							if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$associated_organisation_id, 'create')) {
								$PROCESSED["associated_organisation_id"] = $associated_organisation_id;
							} else {
								add_error("You do not have permission to add a task for this organisation, please select a different one.");
							}
						} else {
							add_error("You have chosen <strong>Entire Organisation Task</strong> as a <strong>Task Recipients</strong> type, but have not selected an organisation.");
						}
						break;
					default :
						add_error("Unable to proceed because the <strong>Task Recipients</strong> type is unrecognized.");
						application_log("error", "Unrecognized task_recipient_type [".$_POST["task_recipient_type"]."] encountered.");
						break;
				}
			} else {
				add_error("Unable to proceed because the <strong>Task Recipients</strong> type is unrecognized.");
				application_log("error", "The task_recipient_type field has not been set.");
			}		
			
			//Error processing comeplete
			if (!has_error()) {
				//first create the task, then add the owners, and finally, if verification is required add those records.
	
				$org_id = ($PROCESSED["associated_organisation_id"] ? $PROCESSED["associated_organisation_id"] : $ORGANISATION_ID); //default to associated organisation as user may have access to multiple
				$task_id = Task::create($PROXY_ID,$PROCESSED['title'], $PROCESSED['deadline'], $PROCESSED['duration'], $PROCESSED['description'], $PROCESSED['release_start'], $PROCESSED['release_finish'], $org_id);
				if ($task_id) {
					
					$owners = array();
					
					//stub, owners for now limited to a single course and the creator
					$owners[] = User::get($PROXY_ID);
					if ($PROCESSED["course_id"]) {
						$owners[] = array("type" => TASK_OWNER_COURSE, "id" => $PROCESSED["course_id"]);
					}
					
					TaskOwners::add($task_id, $owners);
					
					$recipients = array();
					switch($PROCESSED['task_recipient_type']) {
						case TASK_RECIPIENT_USER:
							foreach($PROCESSED["associated_proxy_ids"] as $proxy_id) {
								$recipients[] = array("type" => TASK_RECIPIENT_USER, "id" => $proxy_id);	
							}
							break;
						case TASK_RECIPIENT_CLASS:
							foreach($PROCESSED["associated_grad_years"] as $grad_year) {
								$recipients[] = array("type" => TASK_RECIPIENT_CLASS, "id" => $grad_year);	
							}
							break;
						case TASK_RECIPIENT_ORGANISATION:
							$recipients[] = array("type"=>TASK_RECIPIENT_ORGANISATION, "id" => $PROCESSED["associated_organisation_id"]);
							break;
					}
					
					TaskRecipients::add($task_id,$recipients);
					
					if ($PROCESSED['require_verification'] == TASK_VERIFICATION_REQUIRED) {
						$verification_ids = $PROCESSED["associated_proxy_ids"];
						if ($PROCESSED["associated_grad_years"]) {
							foreach ($PROCESSED["associated_grad_years"] as $grad_year) {
								$students = GraduatingClass::get($grad_year);
								foreach ($students as $student) {
									$verification_ids[] = $student->getID();	
								}
							}
						}
						$verification_ids = array_unique($verification_ids);
						TaskVerification::add($task_id, $verification_ids);
					}
				}
				
				if (!has_error()) {
					switch($_POST['post_action']) {
						case 'new':
							header( "refresh:5;url=".ENTRADA_URL."/admin/tasks?section=create" );
							break;
						case 'index':
						default:
							header( "refresh:5;url=".ENTRADA_URL."/admin/tasks" );
					
					}
					display_status_messages();
					exit;
				}
			}
			
		} else {
			$PROCESSED['title'] = $task->getTitle();
			$course = $task->getCourse();
			$PROCESSED['course_id'] = ($course) ? $course->getID() : null;
			$PROCESSED['task_deadline'] = $task->getDeadline();
			$PROCESSED['time_required'] = $task->getDuration();
			$PROCESSED['desciption'] = $task->getDescription();
			$PROCESSED['require_verification'] = $task->isVerificationRequired();
			$recipients = TaskRecipients::get($task->getID());
			$recipient = $recipients->current();
			if ($recipient instanceof User) {
				$recipient_type = TASK_RECIPIENT_USER;
			} elseif ($recipient instanceof GraduatingClass ) {
				$recipient_type = TASK_RECIPIENT_CLASS;
			} elseif ($recipient instanceof Organisation) {
				$recipient_type = TASK_RECIPIENT_ORGANISATION;
			}
			$PROCESSED['task_recipient_type'] = $recipient_type;
			
			foreach ($recipients as $recipient ){
				if ($recipient instanceof User) {
					$PROCESSED["associated_proxy_ids"][] = $recipient->getID();
				} elseif($recipient instanceof GraduatingClass) {
					$PROCESSED["associated_grad_years"][] = $recipient->getGradYear();
					$PROCESSED["associated_grad_year"] = $recipient->getGradYear(); //XXX for compaibility with single select style (before new multi-select widget) 
				} elseif($recipient instanceof Organisation) {
					$PROCESSED["associated_organisation_id"] = $recipient->getID();
				}
				
			}
		}
		
		
		
		
		
		load_rte(); //load the Rich Text Editor
		
		$ONLOAD[]	= "selectTaskRecipientsOption('".$PROCESSED["task_recipient_type"]."')";
		
		display_status_messages();
		?>
		
		<h1>Edit Task</h1>
		
		<form id="new_task_form" action="<?php echo ENTRADA_URL; ?>/admin/tasks?section=create" method="post">
			<table class="task_details">
				<colgroup>
					<col width="3%"></col>
					<col width="25%"></col>
					<col width="72%"></col>
				</colgroup>
				<tfoot>
					<tr>
						<td colspan="3" style="padding-top: 25px">
							<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td style="width: 25%; text-align: left">
										<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/tasks'" />
									</td>
									<td style="width: 75%; text-align: right; vertical-align: middle">
										<span class="content-small">After saving:</span>
										<select id="post_action" name="post_action">
											<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another task</option>
											<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to task list</option>
										</select>
										<input type="submit" class="button" name="action" value="Save" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td>&nbsp;</td>
						<td >
							<label for="title" class="form-required">Task Title</label>
						</td>
						<td >
							<input id="title" name="title" type="text" maxlength="4096" style="width: 250px; vertical-align: middle;" value="<?php echo html_encode($PROCESSED["title"]); ?>"></input>	
						</td>
					</tr>
					<tr>
							<td></td>
							<td><label for="course_id" class="form-nrequired">Course</label></td>
							<td>
								<select id="course_id" name="course_id" style="width: 95%">
								<option value="0">None</option>
								<?php
								$query		= "	SELECT * FROM `courses` 
												WHERE `course_active` = '1'
												ORDER BY `course_name` ASC";
								$results	= $db->GetAll($query);
								if($results) {
									foreach($results as $result) {
										if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), "create")) {
											echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
										}
									}
								}
								?>
								</select>
							</td>
						</tr>
					<?php
						echo generate_calendar("task_deadline","Deadline",false,$PROCESSED['task_deadline'],true,false,false,false,false);
					?>
					<tr>
						<td>&nbsp;</td>
						<td >
							<label for="time_required" class="form-nrequired">Estimated Time Required</label>
						</td>
						<td >
							<input id="time_required" name="time_required" type="text" maxlength="4096" style="width: 5em; vertical-align: middle;" value="<?php echo $PROCESSED['time_required']; ?>"></input>	minutes
						</td>
					</tr>
					
					<tr>
						<td colspan="3">
							<h2>Task Description</h2>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="2">
							<textarea id="description" name="description" style="width: 100%; height: 100px;" cols="65" rows="20"><?php echo html_encode(trim(strip_selected_tags($PROCESSED['description'], array("font")))); ?></textarea>	
						</td>
					</tr>
					<tr>
							<td colspan="3"><h2>Task Recipients</h2></td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="require_verification" id="require_verification" <?php echo ($PROCESSED['require_verification'] == TASK_VERIFICATION_REQUIRED)?"checked=\"checked\"":"" ?>></input>
						</td>
						<td colspan="2">
							<label for="require_verification" class="form_nrequired">Completion of this task requires verification</label>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_grad_year" value="<?php echo TASK_RECIPIENT_CLASS; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_CLASS; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_CLASS) ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="task_recipient_type_grad_year" class="radio-group-title">Entire Class task</label>
							<div class="content-small">This task is intended for an entire class.</div>
						</td>
					</tr>
					<tr class="task_recipient <?php echo TASK_RECIPIENT_CLASS; ?>_recipient">
						<td></td>
						<td><label for="associated_grad_years" class="form-required">Graduating Year</label></td>
						<td>
							<select id="associated_grad_years" name="associated_grad_years" style="width: 203px">
							<?php
							for($year = (date("Y", time()) + 4); $year >= (date("Y", time()) - 1); $year--) {
								echo "<option value=\"".(int) $year."\"".(($PROCESSED["associated_grad_year"] == $year) ? " selected=\"selected\"" : "").">Class of ".html_encode($year)."</option>\n";
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_proxy_id" value="<?php echo TASK_RECIPIENT_USER; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_USER; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_USER) ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="task_recipient_type_proxy_id" class="radio-group-title">Individual task</label>
							<div class="content-small">This task is intended for a specific individual or individuals.</div>
						</td>
					</tr>
					<tr class="task_recipient <?php echo TASK_RECIPIENT_USER; ?>_recipient">
						<td></td>
						<td style="vertical-align: top"><label for="associated_proxy_ids" class="form-required">Associated Individuals</label></td>
						<td>
							<input type="text" id="individual_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
							<?php
								$ONLOAD[] = "var individual_list = new AutoCompleteList({ type: 'individual', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
							?>
							<div class="autocomplete" id="individual_name_auto_complete"></div><script type="text/javascript"></script>
							<input type="hidden" id="associated_individual" name="associated_individual" />
							<input type="button" class="button-sm" id="add_associated_individual" value="Add" style="vertical-align: middle" />
							<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="individual_list" class="menu" style="margin-top: 15px">
								<?php
								if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
									foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
										if ($individual = User::get($proxy_id)) {
											?>
											<li class="community" id="individual_<?php echo $individual->getID(); ?>" style="cursor: move;"><?php echo $individual->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $individual->getID(); ?>', 'individual');"/></li>
											<?php
										}
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
					<?php if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, null, $ORGANISATION_ID), 'create')) { ?>
					<tr>
						<td style="vertical-align: top"><input type="radio" name="task_recipient_type" id="task_recipient_type_organisation_id" value="<?php echo TASK_RECIPIENT_ORGANISATION; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_ORGANISATION; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_ORGANISATION) ? " checked=\"checked\"" : ""); ?> /></td>
						<td colspan="2" style="padding-bottom: 15px">
							<label for="task_recipient_type_organisation_id" class="radio-group-title">Entire Organisation Task</label>
							<div class="content-small">This task is intended for every member of an organisation.</div>
						</td>
					</tr>
					<tr class="task_recipient <?php echo TASK_RECIPIENT_ORGANISATION; ?>_recipient">
						<td></td>
						<td><label for="associated_organisation_id" class="form-required">Organisation</label></td>
						<td>
							<select id="associated_organisation_id" name="associated_organisation_id" style="width: 203px">
								<?php
								$organisations = Organisations::get();
								if ($organisations) {
									foreach($organisations as $organisation) {
										$organisation_id = $organisation->getID();
										$organisation_title = $organisation->getTitle();
										echo "<option value=\"".$organisation_id."\"".(($PROCESSED["associated_organisation_id"] == $year) ? " selected=\"selected\"" : "").">".$organisation_title."</option>\n";
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3"><h2>Time Release Options</h2></td>
					</tr>
					<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_start"])) ? $PROCESSED["release_start"] : 0), true, false, ((isset($PROCESSED["release_finish"])) ? $PROCESSED["release_finish"] : 0)); ?>
				</tbody>
			</table>
		</form>
		<script type="text/javascript">
			function selectTaskRecipientsOption(type) {
				$$('.task_recipient').invoke('hide');
				$$('.'+type+'_recipient').invoke('show');
			}
		</script>
		<?php
	
	} else {
		header( "refresh:15;url=".ENTRADA_URL."/admin/".$MODULE );
		
		add_error("In order to edit a task you must provide a valid task identifier.");

		echo display_error();

		application_log("notice", "Failed to provide valid task identifer when attempting to edit a task.");
	}
}
