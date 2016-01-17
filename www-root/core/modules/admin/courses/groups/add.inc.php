<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * This file is used to add groups.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2011 University of Calgary. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_GROUPS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed(new CourseResource($COURSE_ID, $ENTRADA_USER->getActiveOrganisation()), 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	ini_set('auto_detect_line_endings',true);

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	
	    $BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/course/groups?".replace_query(array("section" => "add")), "title" => "Add Course Group");

	$group_type = "individual";
	$group_populate = "group_number";
	$number_of_groups = "";
	$populate = 0;
	$GROUP_IDS = array();
	
	$course_details = $db->GetRow("SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
	courses_subnavigation($course_details,"groups");
	echo "<h2>Add Group</h2>\n";
	
	// Error Checking
	switch($STEP) {
		case 2 :
			/*
			 *  CSV file format "group_name, first_name, last_name, status, entrada_id"
			 */
			$PROCESSED["updated_date"]	= time();
			$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();
			
			/**
			 * Required field "prefix" / Group Name.
			 */
			if ((isset($_POST["prefix"])) && ($group_prefix = clean_input($_POST["prefix"], array("notags", "trim")))) {
				$PROCESSED["group_name"] = $group_prefix;
			} else {
				add_error("The <strong>Group Prefix</strong> field is required.");
			}

			/**
			 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
			 * This is actually accomplished after the event is inserted below.
			 */
			if (isset($_POST["group_type"])) {
				$group_type = clean_input($_POST["group_type"], array("page_url"));

				switch($group_type) {
					case "individual" :
						if (!((isset($_POST["group_number"])) && ($number_of_groups = clean_input($_POST["group_number"], array("trim", "int"))))) {
							add_error("A <strong>Number of Groups</strong> value is required.");
						}
					break;
					case "populated" :
						/**
						 * Audience type is a required field. There shouldn't be anyway to not have it, but it checks to be sure.
						 */
						if (!(isset($_POST["group_audience_type"]) && $audience_type = clean_input($_POST["group_audience_type"],array("trim")))) {
							add_error("You have requested the groups be preopulated but have not specified where they should be populated from.");
							$audience_type = false;
						} else {
							$PROCESSED["group_audience_type"] = $audience_type;
						}

						if ($audience_type == "course") {
							if (!isset($_POST["cperiod_id"]) || !$cperiod_id = clean_input($_POST["cperiod_id"], array("trim"))) {
								add_error("You selected all learners enrolled in " . $course_details["course_code"] . " to prepopulate the groups from but did not select an enrolment period.");
							}
						} else {
							/**
							 * If the audience is custom sets $groups variable to false, or an array of group ids. Used to fetch course members for each group further down.
							 */
							if (!isset($_POST["group_audience_cohorts"]) || !$cohorts = clean_input($_POST["group_audience_cohorts"], array("trim"))) {
								add_error("You selected a custom audience to prepopulate the groups from but did not select any groups.");
								$groups = false;
							} else {
								$split_cohorts = explode(",", $cohorts);
								if (!$split_cohorts || empty($split_cohorts)) {
									add_error("You selected a custom audience to prepopulate the groups from but did not select any groups.");
									$groups = false;
								} else {
									$groups = array();
									foreach ($split_cohorts as $cohort) {
										$groups[] = substr($cohort, 6);
										$PROCESSED["associated_cohort_ids"][] = substr($cohort, 6);
									}
								}

							}
						}

						if (!((isset($_POST["number"])) && ($number_of_groups = clean_input($_POST["number"], array("trim", "int"))))) {
							$number_of_groups = 0;
						}
						if (!((isset($_POST["size"])) && ($size_of_groups = clean_input($_POST["size"], array("trim", "int"))))) {
							$size_of_groups = 0;
						}

						if (isset($_POST["group_populate"])) {
							$group_populate = clean_input($_POST["group_populate"], array("page_url"));
							switch($group_populate) {
								case "group_number" :
									if (!$number_of_groups) {
										add_error("A value for <strong>Number of Groups</strong> is required.");
									}
								break;
								case "group_size" :
									if (!$size_of_groups) {
										add_error("A value for <strong>Group size</strong> is required.");
									}
								break;
								default:
									add_error("Unable to proceed because the <strong>Groups</strong> style is unrecognized.");
								break;
							}
						} else {
							add_error("Unable to proceed because the <strong>Groups</strong> style is unrecognized.");
						}
						$populate = 1;
					break;
					default :
						add_error("Unable to proceed because the <strong>Grouping</strong> type is unrecognized.");

						application_log("error", "Unrecognized group_type [".$_POST["group_type"]."] encountered.");
					break;
				}
			} else {
				add_error("Unable to proceed because the <strong>Grouping</strong> type is unrecognized.");

				application_log("error", "The group_type field has not been set.");
			}

			if (isset($_POST["post_action"])) {
				switch($_POST["post_action"]) {
					case "content" :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
					break;
					case "new" :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "new";
					break;
					case "index" :
					default :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "index";
					break;
				}
			} else {
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "content";
			}

			if (!$ERROR) {
				
				$query = "SELECT * FROM `courses` WHERE `course_id` = ".$db->qstr($COURSE_ID);
				if ($course = $db->GetRow($query)) {
					if ($course["permission"] == "closed") {
						$course_audience = true;
					} else {
						$course_audience = false;
					}
				}
				
				if (!$course_audience) {
					$query = "SELECT a.* FROM `course_audience` AS a 
								JOIN `curriculum_periods` AS b
								ON a.`cperiod_id` = b.`cperiod_id`
								WHERE a.`course_id` = ".$db->qstr($COURSE_ID)." 
								AND a.`audience_active` = 1
								AND b.`start_date` <= ".$db->qstr(time())."
								AND b.`finish_date` >= ".$db->qstr(time());
					$course_audience_record = $db->GetRow($query);
					if ($course_audience_record) {
						$course_audience = true;
					}
					
				}
				
				$PROCESSED["course_id"] = $COURSE_ID;
				$PROCESSED["active"] = 1;

				if ($number_of_groups == 1) {
					$result = $db->GetRow("SELECT `cgroup_id` FROM `course_groups` WHERE `group_name` = ".$db->qstr($PROCESSED["group_name"])." AND `course_id` = ".$db->qstr($COURSE_ID));
					if ($result) {
						add_error("The <strong>Group name</strong> already exists. The group was not created");
					} else {
						if (!$db->AutoExecute("course_groups", $PROCESSED, "INSERT")) {
							add_error("There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.");
							application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
						}
						$GROUP_IDS[] = $db->Insert_Id();
					}

				} else {
					$prefix = $PROCESSED["group_name"].' ';

					if ($group_populate == "group_size") {
						if ($audience_type == "course") {
							/**
							 * Sets the audience_members to the audience of the course at the time the groups are being made.
							 * Also sets $students to the number of results.
							 */
                            if (isset($course_details) && isset($cperiod_id)) {
                                $course = new Models_Course();
                                $course->fromArray($course_details);

                                if ($course) {
                                    $course_audience = $course->getMembers($cperiod_id);

                                    if ($course_audience) {
                                        $audience_members = array();

                                        foreach ($course_audience as $type => $audience_type_members) {
                                            if ($type == "groups") {
                                                foreach ($audience_type_members as $audience) {
                                                    foreach ($audience as $audience_member) {
                                                        $audience_members[] = array("id" => $audience_member->getProxyId());
                                                    }
                                                }
                                            } else {
                                                foreach ($audience_type_members as $audience_member) {
                                                    $audience_members[] = array("id" => $audience_member->getProxyId());
                                                }
                                            }
                                        }
                                    }
                                }
                            }
							$students = count($audience_members);
						} else {
							$query	= "	SELECT a.`id`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										JOIN `group_members` c
										ON a.`id` = c.`proxy_id`
										AND c.`group_id` IN(".implode(",",$groups).")
										AND c.`member_active` = '1'
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")					
										AND b.`account_active` = 'true'
										AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
										AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
										AND (c.`start_date` = '0' OR c.`start_date` <= ".$db->qstr(time()).")
										AND (c.`finish_date` = '0' OR c.`finish_date` > ".$db->qstr(time()).")											
										GROUP BY a.`id`
										ORDER BY a.`lastname` ASC, a.`firstname` ASC";
							$audience_members = $db->GetAll($query);
							$students = count($audience_members);
						}
						$number_of_groups = ceil($students / $size_of_groups) ;
					}
					$dfmt = "%0".strlen((string) $number_of_groups)."d";

					$result = false;
					for ($i = 1; $i <= $number_of_groups && !$result; $i++) {
                        $result = $db->GetRow("SELECT `cgroup_id` FROM `course_groups` WHERE `group_name` = ".$db->qstr($prefix.sprintf($dfmt,$i))." AND `course_id` = ".$db->qstr($COURSE_ID))?true:$result;    
					}
					if ($result) {
						add_error("A <strong>Group name</strong> already exists. The groups were not created");
					} else {
						for ($i = 1; $i <= $number_of_groups; $i++) {
							$PROCESSED["group_name"] = $prefix.sprintf($dfmt,$i);
							$PROCESSED["active"] = 1;
							if (!$db->AutoExecute("course_groups", $PROCESSED, "INSERT")) {
								add_error("There was an error while trying to add the <strong>Group</strong> ".$PROCESSED["group_name"].".<br /><br />The system administrator was informed of this error; please try again later.");
								application_log("error", "Unable to insert a new group ".$PROCESSED["group_name"].". Database said: ".$db->ErrorMsg());
								break;
							}
							$GROUP_IDS[] = $db->Insert_Id();
						}
					}
				}	
				if ($populate) {
					unset($PROCESSED["group_name"]);
					$PROCESSED["active"] = 1;
					if (!isset($audience_members) || !$audience_members) {
						/**
						 * if somehow the audience wasn't populated above, populate it here.
						 */
						if ($audience_type == "course") {
							if (isset($course_details) && isset($cperiod_id)) {
								$course = new Models_Course();
								$course->fromArray($course_details);

								if ($course) {
									$course_audience = $course->getMembers($cperiod_id);

									if ($course_audience) {
										$audience_members = array();

										foreach ($course_audience as $type => $audience_type_members) {
                                            if ($type == "groups") {
                                                foreach ($audience_type_members as $audience) {
                                                    foreach ($audience as $audience_member) {
                                                        $audience_members[] = array("id" => $audience_member->getProxyId());
                                                    }
                                                }
                                            } else {
                                                foreach ($audience_type_members as $audience_member) {
                                                    $audience_members[] = array("id" => $audience_member->getProxyId());
                                                }
                                            }
										}
									}
								}
							}
						} else {
							$query	= "	SELECT a.`id`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON a.`id` = b.`user_id`
										JOIN `group_members` c
										ON a.`id` = c.`proxy_id`
										AND c.`group_id` IN(".implode(",",$groups).")
										AND c.`member_active` = '1'
										WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
										AND b.`account_active` = 'true'
										AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
										AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
										AND (c.`start_date` = '0' OR c.`start_date` <= ".$db->qstr(time()).")
										AND (c.`finish_date` = '0' OR c.`finish_date` > ".$db->qstr(time()).")
										GROUP BY a.`id`
										ORDER BY a.`lastname` ASC, a.`firstname` ASC";
							$audience_members = $db->GetAll($query);
						}
					}

					$i = 0;
					if ($audience_members) {
						foreach ($audience_members as $result) {
							$PROCESSED["proxy_id"] =  $result["id"];

							$PROCESSED["cgroup_id"] =  $GROUP_IDS[$i];
							$i++;
							if (!$db->AutoExecute("course_group_audience", $PROCESSED, "INSERT")) {
								add_error("There was an error while trying to add an audience member to the database.<br /><br />The system administrator was informed of this error; please try again later.");
								application_log("error", "Unable to insert a new group member ".$PROCESSED["proxy_id"].". Database said: ".$db->ErrorMsg());
								break;
							}
							if ($i==$number_of_groups) {
								$i = 0;
							}
						}
					}
				}
				if (!$ERROR) {
					switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
						case "content" :
							$url	= ENTRADA_URL."/admin/courses/groups?section=edit&id=".$COURSE_ID."&ids=".implode(",", $GROUP_IDS);
							$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "new" :
							$url	= ENTRADA_URL."/admin/courses/groups?section=add&id=".$COURSE_ID;
							$msg	= "You will now be redirected to add more group(s); this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "index" :
						default :
							$url	= ENTRADA_URL."/admin/courses/groups?id=".$COURSE_ID;
							$msg	= "You will now be redirected to the group index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
					}
	
					add_success("You have successfully added <strong>".$number_of_groups." course groups</strong> to the system.<br /><br />".$msg);
					$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
					application_log("success", "New course groups added for course [".$COURSE_ID."] added to the system.");
				}
			} else {
				add_error("There was a problem inserting group into the system. The system administrator was informed of this error; please try again later.");

				application_log("error", "There was an error inserting a group. Database said: ".$db->ErrorMsg());
			}

			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
		break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$ONLOAD[] = "selectgroupOption('".$group_type."',0)";

			if ($ERROR) {
				echo display_error();
			}

			?>
			<form id="frmSubmit" class="form-horizontal" action="<?php echo ENTRADA_URL; ?>/admin/courses/groups?<?php echo replace_query(array("step" => 2)); ?>" method="post" id="addGroupForm">
				<h3>Group Details</h3>

                <div class="control-group">
                    <label class="control-label form-required" for="prefix">Group Name Prefix</label>

                    <div class="controls">
                        <input type="text" id="prefix" name="prefix" class="span8" value="<?php echo (isset($PROCESSED["group_name"]) && $PROCESSED["group_name"] ? html_encode($PROCESSED["group_name"]) : ""); ?>" maxlength="255"/>
                        <div class="content-small" style="margin-top: 10px">This will be used as the first portion of the default name for the groups created, with the second portion being which number the group is (eg. For the prefix "small group" the first 3 groups created would be named: small group 1, small group 2 and small group 3).</div>
                    </div>
                </div>

                <label class="control-label form-required">Group Type</label>

                <div class="control-group">
                    <div class="controls">
                        <label class="radio radio-group-title" for="group_type_individual">
                            <input type="radio" name="group_type" id="group_type_individual" value="individual" onclick="selectgroupOption('individual', 0)"<?php echo (($group_type == "individual") ? " checked=\"checked\"" : ""); ?> />
                            &nbsp;Empty Groups
                        </label>

                        <div class="content-small">These groups will be created without any members, then you may add whichever members you require on the next screen.</div>

                        <br />

                        <div class="control-group group_members individual_members">
                            <label class="control-label form-required" for="empty_group_number">Number of Groups</label>

                            <div class="controls">
                                <input type="text" id="empty_group_number" name="group_number" style="width: 50px;" value="<?php echo html_encode($number_of_groups); ?>" maxlength="10"/>
                            </div>
                        </div>

                        <label class="radio radio-group-title" for="group_type_populated">
                            <input type="radio" name="group_type" id="group_type_populated" value="populated" onclick="selectgroupOption('populated', 0)" <?php echo (($group_type == "populated") ? " checked=\"checked\"" : ""); ?> />
                            &nbsp;Prepopulated Groups
                        </label>

                        <div class="content-small">These groups will be prepopulated with a number of users based on either the number of groups, or the explicit maximum group size.</div>

                        <br />

                        <div class="group_members populated_members">
                            <?php
                            $PROCESSED["course_id"] = $COURSE_ID;
                            if (isset($PROCESSED["course_id"]) && $PROCESSED["course_id"]) {
                                require_once(ENTRADA_ABSOLUTE."/core/modules/admin/courses/groups/api-audience-options.inc.php");
                            }
                            ?>

                            <label class="control-label form-required">Populate based on</label>

                            <div class="control-group">
                                <div class="controls">
                                    <label class="radio pull-left space-right span5" for="group_populate_group_number">
                                        <input type="radio" name="group_populate" id="group_populate_group_number" value="group_number" onclick="toggleGroupTextbox()" <?php echo (!isset($group_populate) || ($group_populate == "group_number") ? " checked=\"checked\"" : ""); ?> />
                                        &nbsp;Number of Groups
                                    </label>

                                    <input type="text" id="group_number" class="pull-left" name="number" value="<?php echo html_encode($number_of_groups); ?>"  style="width: 50px<?php echo (!isset($group_populate) || ($group_populate == "group_number") ? "" : "; display: none;"); ?>" />
                                </div>
                            </div>

                            <div class="control-group">
                                <div class="controls">
                                    <label class="radio pull-left space-right span5" for="group_populate_group_size">
                                        <input type="radio" name="group_populate" id="group_populate_group_size" value="group_size" onclick="toggleGroupTextbox()" <?php echo (($group_populate == "group_size") ? " checked=\"checked\"" : ""); ?> />
                                        &nbsp;Group Size
                                    </label>

                                    <input type="text" id="group_size" class="pull-left" name="size" value="<?php echo html_encode($number_of_groups); ?>"  style="width: 50px<?php echo (($group_populate == "group_size") ? "" : "; display: none;"); ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="pull-left">
                        <input type="button" class="btn" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/courses/groups?id=<?php echo $COURSE_ID; ?>'" />
                    </div>

                    <div class="pull-right">
                        <span class="content-small">After saving:</span>

                        <select id="post_action" name="post_action">
                            <option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add members to group(s)</option>
                            <option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add additional group(s)</option>
                            <option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to group list</option>
                        </select>

                        <input type="submit" class="btn btn-primary" value="Proceed" />
                    </div>
                </div>
			</form>
			<script type="text/javascript">
				function selectgroupOption(type,files) {
					$$('.group_members').invoke('hide');
					$$('.'+type+'_members').invoke('show');
					if (files) {
						$$('.prefixR').invoke('hide');
						$('file').style.display = 'block';
						$('file').disabled = false;
						$('frmSubmit').enctype="multipart/form-data";
					} else {
						$$('.prefixR').invoke('show');
						$('frmSubmit').enctype="application/x-www-form-urlencoded";
						$('file').style.display = 'none';
						$('file').disabled = true;
					}
				}
				
				function toggleGroupTextbox () {
					if ($('group_populate_group_size').checked) {
						$('group_size').show();
                        $('group_size').focus();
						$('group_number').hide();
					} else {
						$('group_number').show();
                        $('group_number').focus();
                        $('group_size').hide();
                    }
				}
			</script>
			<script type="text/javascript">
			var multiselect = [];
			var audience_type;

			function showMultiSelect() {
				$$('select_multiple_container').invoke('hide');
				audience_type = $F('audience_type');
				course_id = <?php echo $COURSE_ID;?>;
				if (multiselect[audience_type]) {
					multiselect[audience_type].container.show();
				} else {
					if (audience_type) {
						new Ajax.Request('<?php echo ENTRADA_RELATIVE; ?>/admin/courses/groups?section=api-audience-selector', {
							evalScripts : true,
							parameters: { 
								'options_for' : audience_type, 
								'course_id' : course_id, 
								'group_audience_cohorts' : $('group_audience_cohorts').value, 
								'group_audience_course_groups' : $('group_audience_course_groups').value, 
								'group_audience_students' : $('group_audience_students').value
							},
							method: 'post',
							onLoading: function() {
								$('options_loading').show();
							},
							onSuccess: function(response) {
								if (response.responseText) {
									$('options_container').insert(response.responseText);
	
									if ($(audience_type + '_options')) {
	
										$(audience_type + '_options').addClassName('multiselect-processed');
	
										multiselect[audience_type] = new Control.SelectMultiple('group_audience_'+audience_type, audience_type + '_options', {
											checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
											nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
											filter: audience_type + '_select_filter',
											resize: audience_type + '_scroll',
											afterCheck: function(element) {
												var tr = $(element.parentNode.parentNode);
												tr.removeClassName('selected');
	
												if (element.checked) {
													tr.addClassName('selected');
	
													addAudience(element.id, audience_type);
												} else {
													removeAudience(element.id, audience_type);
												}
											}
										});
	
										if ($(audience_type + '_cancel')) {
											$(audience_type + '_cancel').observe('click', function(event) {
												this.container.hide();
	
												$('audience_type').options.selectedIndex = 0;
												$('audience_type').show();
	
												return false;
											}.bindAsEventListener(multiselect[audience_type]));
										}
	
										if ($(audience_type + '_close')) {
											$(audience_type + '_close').observe('click', function(event) {
												this.container.hide();
												
												$('audience_type').clear();
	
												return false;
											}.bindAsEventListener(multiselect[audience_type]));
										}
	
										multiselect[audience_type].container.show();
									}
								} else {
									new Effect.Highlight('audience_type', {startcolor: '#FFD9D0', restorecolor: 'true'});
									new Effect.Shake('audience_type');
								}
							},
							onError: function() {
								alert("There was an error retrieving the requested audience. Please try again.");
							},
							onComplete: function() {
								$('options_loading').hide();
							}
						});
					}
				}
				return false;
			}

			function addAudience(element, audience_id) {
				if (!$('audience_'+element)) {
					$('audience_list').innerHTML += '<li class="' + (audience_id == 'students' ? 'user' : 'group') + '" id="audience_'+element+'" style="cursor: move;">'+$($(element).value+'_label').innerHTML+'<img src="<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif" onclick="removeAudience(\''+element+'\', \''+audience_id+'\');" class="list-cancel-image" /></li>';
					$$('#audience_list div').each(function (e) { e.hide(); });

					Sortable.destroy('audience_list');
					Sortable.create('audience_list');
				}
			}

			function removeAudience(element, audience_id) {
				$('audience_'+element).remove();
				Sortable.destroy('audience_list');
				Sortable.create('audience_list');
				if ($(element)) {
					$(element).checked = false;
				}
				var audience = $('group_audience_'+audience_id).value.split(',');
				for (var i = 0; i < audience.length; i++) {
					if (audience[i] == element) {
						audience.splice(i, 1);
						break;
					}
				}
				$('group_audience_'+audience_id).value = audience.join(',');
			}

			function selectGroupAudienceOption(type) {
				switch (type) {
					case 'course' :
						if (!jQuery('#group_audience_type_course_options').is(":visible")) {
							jQuery('#group_audience_type_course_options').slideDown();
							jQuery('#group_audience_type_custom_options').slideUp();
						}
					break;
					case 'custom' :
						if (!jQuery('#group_audience_type_custom_options').is(":visible")) {
							jQuery('#group_audience_type_custom_options').slideDown();
							jQuery('#group_audience_type_course_options').slideUp();
						}
					break;
				}
			}
			
			function updateAudienceOptions() {
				if ($F('course_id') > 0)  {

					var selectedCourse = '';
					
					var currentLabel = $('course_id').options[$('course_id').selectedIndex].up().readAttribute('label');

					if (currentLabel != selectedCourse) {
						selectedCourse = currentLabel;

						$('audience-options').show();
						$('audience-options').update('<tr><td colspan="2">&nbsp;</td><td><div class="content-small" style="vertical-align: middle"><img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please Wait" title="" style="vertical-align: middle" /> Please wait while <strong>audience options</strong> are being loaded ... </div></td></tr>');

						new Ajax.Updater('audience-options', '<?php echo ENTRADA_RELATIVE; ?>/admin/courses/groups?section=api-audience-options', {
							evalScripts : true,
							parameters : {
								ajax : 1,
								course_id : $F('course_id'),
								group_audience_students: ($('group_audience_students') ? $('group_audience_students').getValue() : ''),
								group_audience_course_groups: ($('group_audience_course_groups') ? $('group_audience_course_groups').getValue() : ''),
								group_audience_cohort: ($('group_audience_cohort') ? $('group_audience_cohort').getValue() : '')
							},
							onSuccess : function (response) {
								if (response.responseText == "") {
									$('audience-options').update('');
									$('audience-options').hide();
								}
							},
							onFailure : function () {
								$('audience-options').update('');
								$('audience-options').hide();
							}
						});
					}
				} else {
					$('audience-options').update('');
					$('audience-options').hide();
				}
			}			
			</script>									
			<br /><br />
			<?php
		break;
	}
}
?>
