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
} elseif (!$ENTRADA_ACL->amIAllowed("tasks", "create", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=create", "title" => "Create Task");
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	$PROCESSED = array();
	$PROCESSED["task_audience_type"] = "proxy_id";
	
	
	if (isset($_POST['action']) && ($_POST['action'] === 'Save')) {
		if ($_POST['title'] && ($task_title = clean_input($_POST['title'], array("notags","trim")))) {
			$PROCESSED['title'] = $task_title;
		} else {
			add_error("The <strong>Task Title</strong> field is required.");
		}
		
		
		
		/**
		 * Required field "course_id" / Course
		 */
		if((isset($_POST["course_id"])) && ($course_id = clean_input($_POST["course_id"], array("int")))) {
			$course = Course::get($course_id);
			if ($course) {
				$ENTRADA_ACL->amIAllowed(new TaskResource(), "create");
			}
			
			$query	= "	SELECT * FROM `courses` 
						WHERE `course_id` = ".$db->qstr($course_id)."
						AND `course_active` = '1'";
			$result	= $db->GetRow($query);
			if ($result) {
				if($ENTRADA_ACL->amIAllowed(new EventResource(null, $course_id, $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), "create")) {
					$PROCESSED["course_id"] = $course_id;
				} else {
					$ERROR++;
					$ERRORSTR[] = "You do not have permission to add an event for the course you selected. <br /><br />Please re-select the course you would like to place this event into.";
					application_log("error", "A program coordinator attempted to add an event to a course [".$course_id."] they were not the coordinator of.");
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Course</strong> you selected does not exist.";
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "The <strong>Course</strong> field is a required field.";
		}
		
		
		/////////////
		
				if(isset($_POST["event_audience_type"])) {
				$PROCESSED["event_audience_type"] = clean_input($_POST["event_audience_type"], array("page_url"));

				switch($PROCESSED["event_audience_type"]) {
					case "grad_year" :
					/**
					 * Required field "associated_grad_years" / Graduating Year
					 * This data is inserted into the event_audience table as grad_year.
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
									$ERROR++;
									$ERRORSTR[] = "You have chosen <strong>Entire Class Event</strong> as an <strong>Event Audience</strong> type, but have not selected any graduating years.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "You have chosen <strong>Entire Class Event</strong> as an <strong>Event Audience</strong> type, but have not selected any graduating years.";
							}
						}

						break;
					case "group_id" :
						$ERROR++;
						$ERRORSTR[] = "The <strong>Group Event</strong> as an <strong>Event Audience</strong> type, has not yet been implemented.";
						break;
					case "proxy_id" :
					/**
					 * Required field "associated_proxy_ids" / Associated Students
					 * This data is inserted into the event_audience table as proxy_id.
					 */
						if((isset($_POST["associated_student"]))) {
							$associated_proxies = explode(',', $_POST["associated_student"]);
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
									$ERROR++;
									$ERRORSTR[] = "You have chosen <strong>Individual Student Event</strong> as an <strong>Event Audience</strong> type, but have not selected any individuals.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "You have chosen <strong>Individual Student Event</strong> as an <strong>Event Audience</strong> type, but have not selected any individuals.";
							}
						}
						break;
					case "organisation_id":
						if((isset($_POST["associated_organisation_id"])) && ($associated_organisation_id = clean_input($_POST["associated_organisation_id"], array("trim", "int")))) {
							if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$associated_organisation_id, 'create')) {
								$PROCESSED["associated_organisation_id"] = $associated_organisation_id;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You do not have permission to add an event for this organisation, please select a different one.";
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "You have chosen <strong>Entire Class Event</strong> as an <strong>Event Audience</strong> type, but have not selected a graduating year.";
						}
						break;
					default :
						$ERROR++;
						$ERRORSTR[] = "Unable to proceed because the <strong>Event Audience</strong> type is unrecognized.";

						application_log("error", "Unrecognized event_audience_type [".$_POST["event_audience_type"]."] encountered.");
						break;
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "Unable to proceed because the <strong>Event Audience</strong> type is unrecognized.";

				application_log("error", "The event_audience_type field has not been set.");
			}
		
		
	}
	
	
	
	
	
	load_rte(); //load the Rich Text Editor
	
	$ONLOAD[]	= "selectTaskAudienceOption('".$PROCESSED["task_audience_type"]."')";
	
	display_status_messages();
	?>
	
	<h1>Create Task</h1>
	
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
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td >
						<label for="title" class="form-required">Task Title</label>
					</td>
					<td >
						<input id="title" name="title" type="text" maxlength="4096" style="width: 250px; vertical-align: middle;" ></input>	
					</td>
				</tr>
				<tr>
						<td></td>
						<td><label for="course_id" class="form-nrequired">Course</label></td>
						<td>
							<select id="course_id" name="course_id" style="width: 95%">
							<option value="none">None</option>
							<?php
							$query		= "	SELECT * FROM `courses` 
											WHERE `course_active` = '1'
											ORDER BY `course_name` ASC";
							$results	= $db->GetAll($query);
							if($results) {
								foreach($results as $result) {
									if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), "create")) {
										echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
									}
								}
							}
							?>
							</select>
						</td>
					</tr>
				<?php
					echo generate_calendar("task_deadline","Deadline",true,$PROCESSED['deadline'],true,false,false,false,false);
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
						<textarea id="description" name="description" style="width: 100%; height: 100px;" cols="65" rows="20"></textarea>	
					</td>
				</tr>
				<tr>
						<td colspan="3"><h2>Task Audience</h2></td>
					</tr>
				<tr>
					<td>
						<input type="checkbox" name="verification_required" id="verification_required"></input>
					</td>
					<td colspan="2">
						<label for="verification_required" class="form_nrequired">Verification Required</label>
					</td>
				</tr>
				<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_audience_type" id="task_audience_type_grad_year" value="grad_year" onclick="selectTaskAudienceOption('grad_year')" style="vertical-align: middle"<?php echo (($PROCESSED["task_audience_type"] == "grad_year") ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_audience_type_grad_year" class="radio-group-title">Entire Class task</label>
						<div class="content-small">This task is intended for an entire class.</div>
					</td>
				</tr>
				<tr class="task_audience grad_year_audience">
					<td></td>
					<td><label for="associated_grad_year" class="form-required">Graduating Year</label></td>
					<td>
						<select id="associated_grad_year" name="associated_grad_year" style="width: 203px">
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
					<td style="vertical-align: top"><input type="radio" name="task_audience_type" id="task_audience_type_proxy_id" value="proxy_id" onclick="selectTaskAudienceOption('proxy_id')" style="vertical-align: middle"<?php echo (($PROCESSED["task_audience_type"] == "proxy_id") ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_audience_type_proxy_id" class="radio-group-title">Individual task</label>
						<div class="content-small">This task is intended for a specific individual or individuals.</div>
					</td>
				</tr>
				<tr class="task_audience proxy_id_audience">
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
								foreach ($PROCESSED["associated_proxy_ids"] as $individual) {
									if ((array_key_exists($individual, $STUDENT_LIST)) && is_array($STUDENT_LIST[$individual])) {
										?>
										<li class="community" id="individual_<?php echo $STUDENT_LIST[$individual]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$individual]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $STUDENT_LIST[$individual]["proxy_id"]; ?>', 'individual');"/></li>
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
				<?php if ($ENTRADA_ACL->amIAllowed(new EventResource(null, null, $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'create')) { ?>
				<tr>
					<td style="vertical-align: top"><input type="radio" name="task_audience_type" id="event_audience_type_organisation_id" value="organisation_id" onclick="selectTaskAudienceOption('organisation_id')" style="vertical-align: middle"<?php echo (($PROCESSED["task_audience_type"] == "organisation_id") ? " checked=\"checked\"" : ""); ?> /></td>
					<td colspan="2" style="padding-bottom: 15px">
						<label for="task_audience_type_organisation_id" class="radio-group-title">Entire Organisation Task</label>
						<div class="content-small">This task is intended for every member of an organisation.</div>
					</td>
				</tr>
				<tr class="task_audience organisation_id_audience">
					<td></td>
					<td><label for="associated_organisation_id" class="form-required">Organisation</label></td>
					<td>
						<select id="associated_organisation_id" name="associated_organisation_id" style="width: 203px">
							<?php
							if (is_array($organisation_categories) && count($organisation_categories)) {
								foreach($organisation_categories as $organisation_id => $organisation_info) {
									echo "<option value=\"".$organisation_id."\"".(($PROCESSED["associated_organisation_id"] == $year) ? " selected=\"selected\"" : "").">".$organisation_info['text']."</option>\n";
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
				<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
			</tbody>
		</table>
	</form>
	<script type="text/javascript">
		function selectTaskAudienceOption(type) {
			$$('.task_audience').invoke('hide');
			$$('.'+type+'_audience').invoke('show');
		}
	</script>
	<?php

}