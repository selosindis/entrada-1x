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

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/tasks?section=create", "title" => "Create Task");
	
	$ORGANISATION_ID = $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"];
	
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	
	
	//set defaults
	$PROCESSED = array();
	$PROCESSED["task_recipient_type"] = TASK_DEFAULT_RECIPIENT_TYPE;
	$PROCESSED["completion_comment_policy"] = TASK_DEFAULT_COMPLETE_COMMENT;
	$PROCESSED["rejection_comment_policy"] = TASK_DEFAULT_REJECT_COMMENT;
	$PROCESSED["faculty_selection_policy"] = TASK_DEFAULT_FACULTY_SELECTION;
	$PROCESSED["task_verification_notification"] = TASK_DEFAULT_VERIFICATION_NOTIFICATION;
	$PROCESSED["task_verification_type"] = TASK_DEFAULT_VERIFICATION_TYPE;
	
	
	switch($_POST['action']) {
		case 'Save':
			
			$PROCESSED = validate_task_details_inputs();

			process_task_common_errors($PROCESSED);
			
			//echo "<pre>".print_r($PROCESSED, true)."</pre>";
			
			//Error processing comeplete
			if (!has_error()) {
				//first create the task, then add the owners, and finally, if verification is required add those records.
	
				$org_id = ($PROCESSED["associated_organisation_id"] ? $PROCESSED["associated_organisation_id"] : $ORGANISATION_ID); //default to associated organisation as user may have access to multiple
				
				$task_values = array(
					"creator_id" => $PROXY_ID,
					"title" => $PROCESSED['title'],
					"deadline" => $PROCESSED['deadline'],
					"duration" => $PROCESSED['time_required'],
					"description" => $PROCESSED["description"],
					"release_start" => $PROCESSED["release_start"],
					"release_finish" => $PROCESSED["release_finish"],
					"organisation_id" => $org_id,
					"faculty_selection_policy" => $PROCESSED["faculty_selection_policy"],
					"completion_comment_policy" => $PROCESSED["completion_comment_policy"],
					"rejection_comment_policy" => $PROCESSED["rejection_comment_policy"],
					"verification_type" => $PROCESSED["task_verification_type"],
					"verification_notification_policy" => array_reduce($PROCESSED["task_verification_notification"], 'or_bin', 0)
				);
				
				$task_id = Task::create($task_values);
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
							foreach($PROCESSED["associated_individual"] as $proxy_id) {
								$recipients[] = array("type" => TASK_RECIPIENT_USER, "id" => $proxy_id);	
							}
							break;
						case TASK_RECIPIENT_CLASS:
							foreach($PROCESSED["associated_cohorts"] as $cohort) {
								$recipients[] = array("type" => TASK_RECIPIENT_CLASS, "id" => $cohort);	
							}
							break;
						case TASK_RECIPIENT_ORGANISATION:
							$recipients[] = array("type"=>TASK_RECIPIENT_ORGANISATION, "id" => $PROCESSED["associated_organisation_id"]);
							break;
					}
					TaskRecipients::add($task_id,$recipients);
					
					TaskAssociatedFaculty::add($task_id,$PROCESSED['associated_faculty']);
					
					if (TASK_VERIFICATION_OTHER == $PROCESSED["task_verification_type"]) {
						TaskVerifiers::add($task_id, $PROCESSED['associated_verifier']);
					}
				}
				
				if (!has_error()) {
					switch($_POST['post_action']) {
						case 'new':
							$url = ENTRADA_URL."/admin/tasks?section=create";
							$page_title="Create Task";
							break;
						case 'index':
						default:
							$page_title="Manage Tasks";
							$url = ENTRADA_URL."/admin/tasks";
					}
					header( "refresh:5;url=".$url );
								
					clear_success(); //clear sucess messages from models.
					add_success("<p>You have successfully created the <strong>".$PROCESSED['title']."</strong> task.</p><p>You will now be redirected to the <strong>".$page_title."</strong> page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\">click here</a> to continue.</p>");
					display_status_messages();
					break;
				}
			}
		
		default:
	
			load_rte(); //load the Rich Text Editor
			$ONLOAD[]	= "selectTaskRecipientsOption('".$PROCESSED["task_recipient_type"]."')";
			$ONLOAD[]	= "selectTaskVerificationOption('".$PROCESSED["task_verification_type"]."')";
			
	?>
	<h1><?php echo $translate->translate("task_heading_create"); ?></h1>
	
	<?php display_status_messages(); ?>
	
	<form id="new_task_form" action="<?php echo ENTRADA_URL; ?>/admin/tasks?section=create" method="post" class="form-horizontal">
	<div class="control-group">
		<label for="title" class="control-label form-required"><?php echo $translate->translate("task_field_title"); ?>:</label>
		<div class="controls">
			<input id="title" name="title" type="text" maxlength="255" value="<?php echo html_encode($PROCESSED["title"]); ?>"></input>	
		</div>
	</div>
	<div class="control-group">
	<table>
		<tr>
			<?php
					echo generate_calendar("deadline",$translate->translate("task_field_deadline"),false,$PROCESSED['deadline'],true,false,false,false,false);
				?>
		</tr>
	</table>
	</div>
	<div class="control-group">
		<label for="time_required" class="control-label form-nrequired"><?php echo $translate->translate("task_field_time_required"); ?>:</label>
		<div class="controls">
			<input id="time_required" name="time_required" type="text" maxlength="4096" value="<?php echo $PROCESSED['time_required']; ?>"></input>	<?php echo $translate->translate("task_misc_minutes"); ?>
		</div>
	</div>
	<div class="control-group">
		<label for="course_id" class="control-label form-nrequired"><?php echo $translate->translate("task_field_course"); ?>:</label>
		<div class="controls">
			<select id="course_id" name="course_id" style="width: 95%">
						<option value="0"><?php echo $translate->translate("task_option_course_none"); ?></option>
						<?php
						$query		= "	SELECT * FROM `courses` 
										WHERE `course_active` = '1'
										AND `organisation_id` = " . $ENTRADA_USER->getActiveOrganisation() . "
										ORDER BY `course_name` ASC";
						$results	= $db->GetAll($query);
						if($results) {
							foreach($results as $result) {
								if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, $result["course_id"], $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"]), "create")) {
									echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
								}
							}
						}
						?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label for="associated_faculty_ids" class="control-label form-nrequired"><?php echo $translate->translate("task_field_associated_faculty"); ?>:</label>
		<div class="controls">
			<input type="text" id="faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
							$ONLOAD[] = "window.faculty_list = new AutoCompleteList({ type: 'faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
						?>
						<div class="autocomplete" id="faculty_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_faculty" name="associated_faculty" />
						<input type="button" class="btn" id="add_associated_faculty" value="<?php echo $translate->translate("task_button_add"); ?>" style="vertical-align: middle" />
						<span class="content-small"><?php echo str_replace("%MY_FULLNAME%", html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]), $translate->translate("task_instructions_faculty_name")); ?></span>
						<ul id="faculty_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
								foreach ($PROCESSED["associated_faculty"] as $proxy_id) {
									if ($faculty = User::get($proxy_id)) {
										?>
										<li class="community" id="faculty_<?php echo $faculty->getID(); ?>" style="cursor: move;"><?php echo $faculty->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.faculty_list.removeItem('<?php echo $faculty->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="faculty_ref" name="faculty_ref" value="" />
						<input type="hidden" id="faculty_id" name="faculty_id" value="" />
		</div>
	</div> <!--/control-group-->
	<h2><?php echo $translate->translate("task_heading_description"); ?></h2>
	<div class="control-group">
			<textarea id="description" name="description" style="width: 100%; height: 100px;" cols="65" rows="20"><?php echo html_encode(trim(strip_selected_tags($PROCESSED['description'], array("font")))); ?></textarea>	
	</div>
	<h2><?php echo $translate->translate("task_heading_recipients"); ?></h2>
	
	<div class="control-group">
		<label class="radio-group-title" for="task_recipient_type_<?php echo TASK_RECIPIENT_CLASS; ?>">
			<input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_CLASS; ?>" value="<?php echo TASK_RECIPIENT_CLASS; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_CLASS; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_CLASS) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_recipients_class"); ?>
		</label>
		<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_class"); ?></div>
		<div class="controls">
			
		</div>
	</div>
	<div  class="task_recipient <?php echo TASK_RECIPIENT_CLASS; ?>_recipient control-group ">
		<label for="associated_cohorts" class="control-label form-required"><?php echo $translate->translate("task_field_cohort"); ?>:</label>
		<div class="controls">
			<select id="associated_cohorts" name="associated_cohorts">
						<?php
						$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
						if (isset($active_cohorts) && !empty($active_cohorts)) {
							foreach ($active_cohorts as $cohort) {
								echo "<option value=\"".$cohort["group_id"]."\"".(($PROCESSED["associated_cohorts"] == $cohort["group_id"]) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
							}
						}
						?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label for="task_recipient_type_<?php echo TASK_RECIPIENT_USER; ?>" class="radio-group-title">
			<input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_USER; ?>" value="<?php echo TASK_RECIPIENT_USER; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_USER; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_USER) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_recipients_students"); ?>
		</label>
		<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_students"); ?></div>
	</div>
	<div class="control-group task_recipient <?php echo TASK_RECIPIENT_USER; ?>_recipient"">
		<label for="associated_proxy_ids" class="control-label form-required"><?php echo $translate->translate("task_field_associated_students"); ?>:</label>
		<div class="controls">
			<input type="text" id="individual_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
						<?php
							$ONLOAD[] = "window.individual_list = new AutoCompleteList({ type: 'individual', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=student', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
						?>
						<div class="autocomplete" id="individual_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_individual" name="associated_individual" />
						<input type="button" class="btn" id="add_associated_individual" value="Add" style="vertical-align: middle" />
						<span class="content-small"><?php echo str_replace("%MY_FULLNAME%", html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]), $translate->translate("task_instructions_associated_students")); ?></span>
						<ul id="individual_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_individual"]) && count($PROCESSED["associated_individual"])) {
								foreach ($PROCESSED["associated_individual"] as $proxy_id) {
									if ($individual = User::get($proxy_id)) {
										?>
										<li class="community" id="individual_<?php echo $individual->getID(); ?>" style="cursor: move;"><?php echo $individual->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.individual_list.removeItem('<?php echo $individual->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="individual_ref" name="individual_ref" value="" />
						<input type="hidden" id="individual_id" name="individual_id" value="" />
		</div>
	</div> <!--/control-group-->
	<?php if (/*disabled*/false && $ENTRADA_ACL->amIAllowed(new TaskResource(null, null, $ORGANISATION_ID), 'create')) { ?>
	<div class="control-group">
		<label class="radio radio-group-title" for="task_recipient_type_<?php echo TASK_RECIPIENT_ORGANISATION; ?>">
			<input type="radio" name="task_recipient_type" id="task_recipient_type_<?php echo TASK_RECIPIENT_ORGANISATION; ?>" value="<?php echo TASK_RECIPIENT_ORGANISATION; ?>" onclick="selectTaskRecipientsOption('<?php echo TASK_RECIPIENT_ORGANISATION; ?>')" style="vertical-align: middle"<?php echo (($PROCESSED["task_recipient_type"] == TASK_RECIPIENT_ORGANISATION) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_recipients_organisation"); ?>
		</label>
		<div class="content-small"><?php echo $translate->translate("task_instructions_recipients_organisation"); ?></div>
	</div>
	<div class="control-group task_recipient <?php echo TASK_RECIPIENT_ORGANISATION; ?>_recipient">
		<label for="associated_organisation_id" class="control-group form-required"><?php echo $translate->translate("task_field_organisation"); ?></label>
		<div class="controls">
			<select id="associated_organisation_id" name="associated_organisation_id">
							<?php
							$organisations = Organisations::get();
							if ($organisations) {
								foreach($organisations as $organisation) {
									$organisation_id = $organisation->getID();
									if ($ENTRADA_ACL->amIAllowed(new TaskResource(null, null, $organisation_id), 'create')) { 
										$organisation_title = $organisation->getTitle();
										echo build_option($organisation_id, html_encode($organisation_title), ($PROCESSED["associated_organisation_id"] == $organisation_id) );
									}
								}
							}
							?>
			</select>
		</div>
	</div>
	<?php } ?>
	<h2 class="collapsed" title="<?php echo $translate->translate("task_heading_completion_options"); ?>"><?php echo $translate->translate("task_heading_completion_options"); ?></h2>
	<div id="<?php echo strtolower(str_replace(" ", "-",$translate->translate("task_heading_completion_options"))); ?>">
		<div class="control-group">
			<label class="control-label"><?php echo $translate->translate("task_field_faculty_selection"); ?>:</label>
			<div class="controls">
				<select id="faculty_selection_policy" name="faculty_selection_policy">
							<?php
							foreach (array(TASK_FACULTY_SELECTION_OFF, TASK_FACULTY_SELECTION_ALLOW, TASK_FACULTY_SELECTION_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_faculty_selection_".$policy), $PROCESSED["faculty_selection_policy"] == $policy);
							}
							?>
				</select>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label"><?php echo $translate->translate("task_field_completion_comments"); ?>:</label>
			<div class="controls">
				<select id="completion_comment_policy" name="completion_comment_policy">
							<?php
							foreach (array(TASK_COMMENT_NONE, TASK_COMMENT_ALLOW, TASK_COMMENT_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_complete_".$policy), $PROCESSED["completion_comment_policy"] == $policy);
							}
							?>
						</select>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label"><?php echo $translate->translate("task_field_rejection_comments"); ?>:</label>
			<div class="controls">
				<select id="rejection_comment_policy" name="rejection_comment_policy">
							<?php
							foreach (array(TASK_COMMENT_NONE, TASK_COMMENT_ALLOW, TASK_COMMENT_REQUIRE) as $policy) {
								echo build_option($policy, $translate->translate("task_option_complete_".$policy), $PROCESSED["rejection_comment_policy"] == $policy);
							}
							?>
						</select>
			</div>
		</div>
		</div>
		<h2 class="collapsed" title="<?php echo $translate->translate("task_heading_verification_options"); ?>"><?php echo $translate->translate("task_heading_verification_options"); ?></h2>
		<div id="<?php echo strtolower(str_replace(" ", "-",$translate->translate("task_heading_verification_options"))); ?>">
			<!-- 1 --->
			<div class="control-group">
				<label for="task_verification_type_<?php echo TASK_VERIFICATION_NONE; ?>" class="radio radio-group-title">
					<input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_NONE; ?>" value="<?php echo TASK_VERIFICATION_NONE; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_NONE; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_NONE) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_verification_none"); ?>
				</label>
				<div class="content-small"><?php echo $translate->translate("task_instructions_verification_none"); ?></div>
			</div>
			<div class="control-group <?php echo TASK_VERIFICATION_FACULTY; ?>_anchor">
				<label for="task_verification_type_<?php echo TASK_VERIFICATION_FACULTY; ?>" class="radio radio-group-title">
					<input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_FACULTY; ?>" value="<?php echo TASK_VERIFICATION_FACULTY; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_FACULTY; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_FACULTY) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_verification_faculty"); ?>
				</label>
				<div class="content-small"><?php echo $translate->translate("task_instructions_verification_faculty"); ?></div>
			</div>
			<div class="active_verification_options">
						<h3><?php echo $translate->translate("task_field_notification_types"); ?></h3>
						<table>
							<colgroup>
								<col width="3%"></col>
								<col width="25%"></col>
								<col width="72%"></col>
							</colgroup>
							<tbody>
								<tr>
									<td><input type="checkbox" name="task_verification_notification[]" value="<?php echo TASK_VERIFICATION_NOTIFICATION_EMAIL; ?>" <?php if ($PROCESSED['task_verification_notification'] && ($PROCESSED['task_verification_notification'] & TASK_VERIFICATION_NOTIFICATION_EMAIL) ) { echo "checked='checked'"; } ?> /></td>
									<td colspan="2"><?php echo $translate->translate("task_field_verification_notificaiton_email"); ?></td>
								</tr>
								<tr>
									<td><input type="checkbox" name="task_verification_notification[]" disabled="disabled" value="<?php echo TASK_VERIFICATION_NOTIFICATION_DASHBOARD; ?>"  <?php if ($PROCESSED['task_verification_notification'] && ($PROCESSED['task_verification_notification'] & TASK_VERIFICATION_NOTIFICATION_DASHBOARD) ) { echo "checked='checked'"; } ?> /></td>
									<td colspan="2"><?php echo $translate->translate("task_field_verification_notification_dashboard"); ?></td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>
							</tbody>
						</table>
				</div>
			
			<div class="control-group">
				<label for="task_verification_type_<?php echo TASK_VERIFICATION_OTHER; ?>" class="radio radio-group-title">
					<input type="radio" name="task_verification_type" id="task_verification_type_<?php echo TASK_VERIFICATION_OTHER; ?>" value="<?php echo TASK_VERIFICATION_OTHER; ?>" onclick="selectTaskVerificationOption('<?php echo TASK_VERIFICATION_OTHER; ?>', event)" style="vertical-align: middle"<?php echo (($PROCESSED["task_verification_type"] == TASK_VERIFICATION_OTHER) ? " checked=\"checked\"" : ""); ?> /> <?php echo $translate->translate("task_field_verification_other"); ?>
				</label>
				<div class="content-small"><?php echo $translate->translate("task_instructions_verification_other"); ?></div>
			</div>
			<div class="task_verification <?php echo TASK_VERIFICATION_OTHER; ?>_verification <?php echo TASK_VERIFICATION_OTHER; ?>_anchor control-group">
				<label for="verifier_proxy_ids" class="form-required"><?php echo $translate->translate("task_field_verification_other_names"); ?>:</label>
					<input type="text" id="verifier_name" name="fullname" size="30" autocomplete="off" /> 
				
				<?php
							$ONLOAD[] = "window.verifier_list = new AutoCompleteList({ type: 'verifier', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=facultyorstaff', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif', limit: 1})";
						?>
						<div class="autocomplete" id="verifier_name_auto_complete"></div><script type="text/javascript"></script>
						<input type="hidden" id="associated_verifier" name="associated_verifier" />
						<input type="button" class="btn" id="add_associated_verifier" value="<?php echo $translate->translate("task_button_add"); ?>" style="vertical-align: middle" />
						<ul id="verifier_list" class="menu" style="margin-top: 15px">
							<?php
							if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
								foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
									if ($verifier = User::get($proxy_id)) {
										?>
										<li class="community" id="verifier_<?php echo $verifier->getID(); ?>" style="cursor: move;"><?php echo $verifier->getFullname(); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="window.verifier_list.removeItem('<?php echo $verifier->getID(); ?>');"/></li>
										<?php
									}
								}
							}
							?>
						</ul>
						<input type="hidden" id="verifier_ref" name="verifier_ref" value="" />
						<input type="hidden" id="verifier_id" name="verifier_id" value="" />
			
		</div>
		
	</div> <!--/container-->
	<h2><?php echo $translate->translate("task_heading_time_release_options"); ?></h2>
	<table>
		<tr>
			<?php echo generate_calendars("release", "", true, true, ((isset($PROCESSED["release_start"])) ? $PROCESSED["release_start"] : time()), true, false, ((isset($PROCESSED["release_finish"])) ? $PROCESSED["release_finish"] : 0)); ?>
		</tr>
	</table>
	<div class="row-fluid" style="margin-top:20px;">
		<input type="button" class="btn" value="<? echo $translate->translate("task_button_cancel"); ?>" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/tasks'" />
		<div class="pull-right">
			<span class="content-small">After saving:</span>
			<select id="post_action" name="post_action">
				<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another task</option>
				<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to task list</option>
			</select>
			<input type="hidden" name="action" value="Save" />
			<input type="submit" class="btn btn-primary" value="<? echo $translate->translate("task_button_save"); ?>" />
		</div>
	</div>
	
	</form>
	<script type="text/javascript">
		function selectTaskRecipientsOption(type) {
			$$('.task_recipient').invoke('hide');
			$$('.'+type+'_recipient').invoke('show');
		}

		function selectTaskVerificationOption(type, event) {
			$$('.task_verification').invoke('hide');
			$$('.'+type+'_verification').invoke('show');
			switch(type) {
				case '<?php echo TASK_VERIFICATION_FACULTY; ?>':
				case '<?php echo TASK_VERIFICATION_OTHER; ?>':
					var target = $$('.'+type+'_anchor');
					if (target) target = target[0];
					else return;
					$$('.active_verification_options').each(function(element){
						target.insert({after:element});
					});
					$$('.active_verification_options').invoke('show');
					break;
				default:
					$$('.active_verification_options').invoke('hide');
			}
		}
	</script>
	<?php
	}
}
