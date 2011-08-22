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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('course', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($COURSE_ID) {
		$HEAD[] = "<script type=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
		$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
		$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/groups_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";	
		$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/growler/src/Growler.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";		
		
		$query			= "	SELECT * FROM `courses` 
							WHERE `course_id` = ".$db->qstr($COURSE_ID)."
							AND `course_active` = '1'";
		$course_details	= $db->GetRow($query);
		if ($course_details && $ENTRADA_ACL->amIAllowed(new CourseResource($course_details['course_id'], $course_details['organisation_id']), 'update')) {
			$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "edit", "id" => $COURSE_ID, "step" => false)), "title" => "Editing Course");


			/** 
			* Fetch the Clinical Presentation details.
			*/
			$clinical_presentations_list	= array();
			$clinical_presentations			= array();

			$results = fetch_clinical_presentations();
			if ($results) {
				foreach ($results as $result) {
					$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
				}
			}
			else {
				$NOTICE++;
				$NOTICESTR[] = "No Mandated Objectives found for this organisation.";
				$clinical_presentations_list = false;
			}

			if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
			if ((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"]))) {
				foreach ($_POST["clinical_presentations"] as $objective_id) {
					if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
							$query	= "	SELECT `objective_id`
										FROM `global_lu_objectives` 
									WHERE `objective_id` = ".$db->qstr($objective_id)."
										AND `objective_active` = '1'
										ORDER BY `objective_order` ASC";
						$result	= $db->GetRow($query);
						if ($result) {
							$clinical_presentations[$objective_id] = $clinical_presentations_list[$objective_id];
						}
					}
				}
			} else {
					$clinical_presentations = array();
				}
			} else {
				$query		= "SELECT `objective_id` FROM `course_objectives` WHERE `objective_type` = 'event' AND `course_id` = ".$db->qstr($COURSE_ID);
				$results	= $db->GetAll($query);
				if ($results) {
					foreach ($results as $result) {
						if (!empty($clinical_presentations_list[$result["objective_id"]])) {
						$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
						}
					}
				}
			}			
			
			
			
			courses_subnavigation($course_details);

			echo "<h1>Editing Course</h1>\n";

			$PROCESSED["permission"] = $course_details["permission"];
			
			// Error Checking
			switch($STEP) {
				case 2 :
					/**
					 * Non-required field "curriculum_type_id" / Curriculum Category
					 */
					if ((isset($_POST["curriculum_type_id"])) && ($curriculum_type_id = clean_input($_POST["curriculum_type_id"], array("int")))) {
						$PROCESSED["curriculum_type_id"] = $curriculum_type_id;
					} else {
						$PROCESSED["curriculum_type_id"] = 0;
					}

					/**
					 * Required field "course_name" / Course Name.
					 */
					if ((isset($_POST["course_name"])) && ($course_name = clean_input($_POST["course_name"], array("notags", "trim")))) {
						$PROCESSED["course_name"] = $course_name;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Course Name</strong> field is required.";
					}

					/**
					 * Required field "organisation_id" / Organisation Name.
					 */
						if ($ENTRADA_ACL->amIAllowed(new CourseResource(null, $ENTRADA_USER->getActiveOrganisation()), 'create')) {
							$PROCESSED["organisation_id"] = $ENTRADA_USER->getActiveOrganisation();
						} else {
							$ERROR++;
							$ERRORSTR[] = "You do not have permission to add a course for this organisation. This error has been logged and will be investigated.";
							application_log("Proxy id [".$_SESSION['details']['proxy_id']."] tried to eicreate a course within an organisation [".$ENTRADA_USER->getActiveOrganisation()."] they didn't have permissions on. ");
						}


					/**
					 * Non-required field "course_code" / Course Code.
					 */
					if ((isset($_POST["course_code"])) && ($course_code = clean_input($_POST["course_code"], array("notags", "trim")))) {
						$PROCESSED["course_code"] = $course_code;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The <strong>Course Code</strong> field is required and must be provided.";
					}

					/**
					 * Non-required field "pcoord_id" .
					 */
					if ((isset($_POST["pcoord_id"])) && ($pcoord_id = clean_input($_POST["pcoord_id"], "int"))) {
						$PROCESSED["pcoord_id"] = $pcoord_id;
					} else {
						$PROCESSED["pcoord_id"] = 0;
					}
					
					/**
					 * Non-required field "evalrep_id".
					 */
					if ((isset($_POST["evalrep_id"])) && ($evalrep_id = clean_input($_POST["evalrep_id"], "int"))) {
						$PROCESSED["evalrep_id"] = $evalrep_id;
					} else {
						$PROCESSED["evalrep_id"] = 0;
					}
					
					/**
					 * Non-required field "studrep_id" .
					 */
					if ((isset($_POST["studrep_id"])) && ($studrep_id = clean_input($_POST["studrep_id"], "int"))) {
						$PROCESSED["studrep_id"] = $studrep_id ;
					} else {
						$PROCESSED["studrep_id"] = 0;
					}

					/**
					 * Check to see if notifications are enabled or not for events in this course.
					 */
					if ((isset($_POST["notifications"])) && (!clean_input($_POST["notifications"], "int"))) {
						$PROCESSED["notifications"] = 0;
					} else {
						$PROCESSED["notifications"] = 1;
					}

					/**
					 * Non-required field "course_directors" / Course Directors (array of proxy ids).
					 * This is actually accomplished after the course is modified below.
					 */

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
					$posted_objectives = array();
					if ((isset($_POST["primary_objectives"])) && ($objectives = $_POST["primary_objectives"]) && (count($objectives))) {
						$PRIMARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$PRIMARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["primary"][] = clean_input($objective, "int");
						}
					}

					if ((isset($_POST["secondary_objectives"])) && ($objectives = $_POST["secondary_objectives"]) && (count($objectives))) {
						$SECONDARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$SECONDARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["secondary"][] = clean_input($objective, "int");
						}
					}

					if ((isset($_POST["tertiary_objectives"])) && ($objectives = $_POST["tertiary_objectives"]) && (count($objectives))) {
						$TERTIARY_OBJECTIVES = array();
						foreach ($objectives as $objective_key => $objective) {
							$TERTIARY_OBJECTIVES[] = clean_input($objective, "int");
							$posted_objectives["tertiary"][] = clean_input($objective, "int");
						}
					}
					
					/**
					 * Check to see if the course is open or private.
					 */
					if ((isset($_POST["course_permission"])) && ($perm = clean_input($_POST["course_permission"], array("trim","notags")))) {
						$PROCESSED["permission"] = $perm;
					} else {
						$PROCESSED["permission"] = "closed";
					}					

					if ((isset($_POST["sync_ldap"])) && $sync = clean_input($_POST["sync_ldap"],array("int","notags"))) {
						$PROCESSED["sync_ldap"] = 1;
					} else {
						$PROCESSED["sync_ldap"] = 0;
					}					
					
					
					if (!$ERROR) {
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

						if ($db->AutoExecute("courses", $PROCESSED, "UPDATE", "`course_id`=".$db->qstr($COURSE_ID))) {
							$community_id = $db->GetOne("SELECT `community_id` FROM `community_courses` WHERE `course_id` = ".$db->qstr($COURSE_ID));
							if ($community_id) {
								$query = "SELECT `proxy_id` FROM `course_contacts` WHERE `course_id` = ".$db->qstr($COURSE_ID);
								$results = $db->GetAll($query);
								if ($results) {
									$delete_ids_string = false;
									foreach ($results as $result) {
										if ($delete_ids_string) {
											$delete_ids_string .= ", ".$db->qstr($result["proxy_id"]);
										} else {
											$delete_ids_string = $result["proxy_id"];
										}
									}
									$db->Execute("UPDATE `community_members` SET `member_acl` = '0' WHERE `proxy_id` IN (".$delete_ids_string.") AND `community_id` = ".$db->qstr($community_id));
								}
							}
							/**
							 * Update Clinical Presentations.
							 */
							$query = "DELETE FROM `course_objectives` WHERE `objective_type` = 'event' AND `course_id` = ".$db->qstr($COURSE_ID);
							if ($db->Execute($query)) {
								if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
									foreach ($clinical_presentations as $objective_id => $presentation_name) {
										if (!$db->AutoExecute("course_objectives", array("course_id" => $COURSE_ID, "objective_id" => $objective_id, "objective_type" => "event", "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error when trying to insert a &quot;clinical presentation&quot; into the system. System administrators have been informed of this error; please try again later.";
											application_log("error", "Unable to insert a new clinical presentation to the database when adding a new event. Database said: ".$db->ErrorMsg());
											echo $db->ErrorMsg();
										}
									}
								}
							}
							$query = "DELETE FROM `course_contacts` WHERE `course_id`=".$db->qstr($COURSE_ID);
							if ($db->Execute($query)) {
								if ((isset($_POST["associated_director"])) && ($associated_directors = explode(",", $_POST["associated_director"])) && (@is_array($associated_directors)) && (@count($associated_directors))) {
									$community_member = false;
									$order = 0;
									foreach($associated_directors as $proxy_id) {
										if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
											if (!$db->AutoExecute("course_contacts", array("course_id" => $COURSE_ID, "proxy_id" => $proxy_id, "contact_type" => "director", "contact_order" => $order), "INSERT")) {
												$ERROR++;
												$ERRORSTR[] = "There was an error when trying to insert a &quot;Course Director&quot; into the system. The system administrator was informed of this error; please try again later.";

												application_log("error", "Unable to insert a new course_contact to the database when updating an event. Database said: ".$db->ErrorMsg());
											} else {
												$order++;
												$community_member = $db->GetOne("SELECT `proxy_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `community_id` = ".$db->qstr($community_id));
												if (!$community_member) {
													if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $proxy_id, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";
		
														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												} else {
													if ($community_id && !$db->AutoExecute("community_members", array("member_active" => 1, "member_acl" => 1), "UPDATE", "`community_id` = ".$db->qstr($community_id)." AND `proxy_id` = ".$db->qstr($proxy_id))) {
														$ERROR++;
														$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";
		
														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
								}
								if ((isset($_POST["associated_coordinator"])) && ($associated_coordinators = explode(",", $_POST["associated_coordinator"])) && (@is_array($associated_coordinators)) && (@count($associated_coordinators))) {
									$community_member = false;
									foreach($associated_coordinators as $proxy_id) {
										if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
											if (!$db->AutoExecute("course_contacts", array("course_id" => $COURSE_ID, "proxy_id" => $proxy_id, "contact_type" => "ccoordinator"), "INSERT")) {
												$ERROR++;
												$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";

												application_log("error", "Unable to insert a new course_contact to the database when updating an event. Database said: ".$db->ErrorMsg());
											} else {
												$community_member = $db->GetOne("SELECT `proxy_id` FROM `community_members` WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `community_id` = ".$db->qstr($community_id));
												if (!$community_member) {
													if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $proxy_id, "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";
		
														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												} else {
													if ($community_id && !$db->AutoExecute("community_members", array("member_active" => 1, "member_acl" => 1), "UPDATE", "`community_id` = ".$db->qstr($community_id)." AND `proxy_id` = ".$db->qstr($proxy_id))) {
														$ERROR++;
														$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";
		
														application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
								}
								if (isset($PROCESSED["pcoord_id"]) && $PROCESSED["pcoord_id"]) {
									if ($community_id && !$db->AutoExecute("community_members", array("community_id" => $community_id, "proxy_id" => $PROCESSED["pcoord_id"], "member_active" => 1, "member_joined" => time(), "member_acl" => 1), "INSERT")) {
										$ERROR++;
										$ERRORSTR[] = "There was an error when trying to insert a &quot;Curriculum Coordinator&quot; into the system. The system administrator was informed of this error; please try again later.";

										application_log("error", "Unable to insert a new community_member to the database when updating an event. Database said: ".$db->ErrorMsg());
									}
								}
							}

							switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
								case "content" :
									$url = ENTRADA_URL."/admin/".$MODULE."?section=content&id=".$COURSE_ID;
									$msg = "You will now be redirected to the course content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "new" :
									$url = ENTRADA_URL."/admin/".$MODULE."?section=add";
									$msg = "You will now be redirected to add a new course; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
								case "index" :
								default :
									$url = ENTRADA_URL."/admin/".$MODULE."";
									$msg = "You will now be redirected to the course index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
								break;
							}

							$query = "SELECT * FROM `course_objectives` WHERE `objective_type` = 'course' AND `course_id` = ".$db->qstr($COURSE_ID);
							$course_objectives = $db->GetAll($query);
							$objective_details = array();
							if ($course_objectives && count($course_objectives)) {
								foreach ($course_objectives as $course_objective) {
									if ($course_objective["objective_details"]) {
										$objective_details[$course_objective["objective_id"]]["details"] = $course_objective["objective_details"];
										$objective_details[$course_objective["objective_id"]]["found"] = false;
									}
								}
							}
							$db->Execute("DELETE FROM `course_objectives` WHERE `objective_type` = 'course' AND `course_id` = ".$db->qstr($COURSE_ID));
							if (is_array($PRIMARY_OBJECTIVES) && count($PRIMARY_OBJECTIVES)) {
								foreach($PRIMARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($_SESSION["details"]["id"]).", `importance` = '1', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}
							if (is_array($SECONDARY_OBJECTIVES) && count($SECONDARY_OBJECTIVES)) {
								foreach($SECONDARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($_SESSION["details"]["id"]).", `importance` = '2', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}
							if (is_array($TERTIARY_OBJECTIVES) && count($TERTIARY_OBJECTIVES)) {
								foreach($TERTIARY_OBJECTIVES as $objective_id) {
									$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($_SESSION["details"]["id"]).", `importance` = '3', `objective_details` = ".(isset($objective_details[$objective_id]) && $objective_details[$objective_id] ? $db->qstr($objective_details[$objective_id]["details"]) : "NULL"));
									if (isset($objective_details[$objective_id]) && $objective_details[$objective_id]) {
										$objective_details[$objective_id]["found"] = true;
									}
								}
							}
							if (is_array($objective_details) && count($objective_details)) {
								foreach($objective_details as $objective_id => $objective) {
									if (!$objective["found"]) {
										$db->Execute("INSERT INTO `course_objectives` SET `course_id` = ".$db->qstr($COURSE_ID).", `objective_id` = ".$db->qstr($objective_id).", `updated_date` = ".$db->qstr(time()).", `updated_by` = ".$db->qstr($_SESSION["details"]["id"]).", `importance` = '1', `objective_details` = ".$db->qstr($objective["details"]));
									}
								}
							}

							if (isset($_POST["group_order"]) && strlen($_POST["group_order"])) {
								$groups = explode(",", clean_input($_POST["group_order"],array("notags","trim")));
								if ($_POST["period"] && $cperiod_id = clean_input($_POST["period"], array("trim", "int"))) {		
									if ((is_array($groups)) && (count($groups))) {
										
										$new_groups = array();
										$updated_groups = array();
										$removed_groups = array();
										$group_ids_string = "";
										
										foreach ($groups as $group_id) {
											$group_ids_string .= ($group_ids_string ? ", ".$db->qstr($group_id) : $db->qstr($group_id));
											$query = "SELECT * FROM `course_audience` WHERE `audience_type` = 'group_id' AND `audience_value` = ".$db->qstr($group_id)." AND `course_id` = ".$db->qstr($COURSE_ID);
											if ($group_record = $db->GetRow($query) && $group_record["cperiod_id"] != $cperiod_id) {
												$updated_groups[] = $group_id;
											} else {
												$new_groups[] = $group_id;
											}
										}
										
										$query = "SELECT * FROM `course_audience` WHERE `audience_type` = 'group_id' AND `audience_value` NOT IN (".$group_ids_string.") AND `course_id` = ".$db->qstr($COURSE_ID)." AND `audience_active` = 1 AND (`enroll_finish` > ".$db->qstr(time())." OR `enroll_finish` = 0)";
										if ($group_records = $db->GetAll($query)) {
											foreach ($group_records as $group_record) {
												$removed_groups = $group_record["audience_value"];
											}
										}
										
										foreach($new_groups as $order => $group_id) {
											if ($group_id = clean_input($group_id, array("trim", "int"))) {
												$query = "SELECT `group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($group_id);
												$result	= $db->GetRow($query);
												if ($result) {
													$PROCESSED["groups"][] = array("id" => $group_id, "title" => $result["group_name"]);
													
													if(!$db->AutoExecute("course_audience", array("audience_type" => "group_id", "audience_value" => $group_id, "course_id" => $COURSE_ID, "cperiod_id" => $cperiod_id), "INSERT")){
														add_error("Unable to insert the group [".$group_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
													}

												} else {
													$ERROR++;
													$ERRORSTR[] = "One of the <strong>groups</strong> you specified was invalid.";
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "One of the <strong>groups</strong> you specified is invalid.";
											}
										}
										
										foreach($updated_groups as $order => $group_id) {
											if ($group_id = clean_input($group_id, array("trim", "int"))) {
												$query = "SELECT `group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($group_id);
												$result	= $db->GetRow($query);
												if ($result) {
													$PROCESSED["groups"][] = array("id" => $group_id, "title" => $result["group_name"]);
													
													if(!$db->AutoExecute("course_audience", array("audience_type" => "group_id", "audience_value" => $group_id, "course_id" => $COURSE_ID, "cperiod_id" => $cperiod_id, "enroll_finish" => 0, "audience_active" => 1), "UPDATE", "`audience_value` = ".$db->qstr($group_id)." AND `audience_type` = 'group_id' AND `course_id` = ".$db->qstr($COURSE_ID))){
														add_error("Unable to update the group [".$group_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
													}

												} else {
													$ERROR++;
													$ERRORSTR[] = "One of the <strong>groups</strong> you specified was invalid.";
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "One of the <strong>groups</strong> you specified is invalid.";
											}
										}
										
										foreach($removed_groups as $order => $group_id) {
											if ($group_id = clean_input($group_id, array("trim", "int"))) {
												$query = "SELECT `group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($group_id);
												$result	= $db->GetRow($query);
												if ($result) {
													$PROCESSED["groups"][] = array("id" => $group_id, "title" => $result["group_name"]);
													
													if(!$db->AutoExecute("course_audience", array("audience_type" => "group_id", "audience_value" => $group_id, "course_id" => $COURSE_ID, "enroll_finish" => time(), "audience_active" => 0), "UPDATE", "`audience_value` = ".$db->qstr($group_id)." AND `audience_type` = 'group_id' AND `course_id` = ".$db->qstr($COURSE_ID))){
														add_error("Unable to deactivate the group [".$group_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
													}

												} else {
													$ERROR++;
													$ERRORSTR[] = "One of the <strong>groups</strong> you specified was invalid.";
												}
											} else {
												$ERROR++;
												$ERRORSTR[] = "One of the <strong>groups</strong> you specified is invalid.";
											}
										}
									}		
								}
							}
							if ($_POST["period"] && $cperiod_id = clean_input($_POST["period"], array("trim", "int"))) {	
								if (isset($_POST["associated_student"]) && strlen($_POST["associated_student"])) {
									$PROCESSED["associated_students"] = explode(",",clean_input($_POST["associated_student"], array("notags", "trim")));
									if (isset($PROCESSED["associated_students"]) && is_array($PROCESSED["associated_students"])) {
										
										$new_students = array();
										$updated_students = array();
										$removed_students = array();
										$student_ids_string = "";
										
										foreach ($PROCESSED["associated_students"] as $student_id) {
											$student_ids_string .= ($student_ids_string ? ", ".$db->qstr($student_id) : $db->qstr($student_id));
											$query = "SELECT * FROM `course_audience` WHERE `audience_type` = 'proxy_id' AND `audience_value` = ".$db->qstr($student_id)." AND `course_id` = ".$db->qstr($COURSE_ID);
											if ($student_record = $db->GetRow($query) && $student_record["cperiod_id"] != $cperiod_id) {
												$updated_students[] = $student_id;
											} else {
												$new_students[] = $student_id;
											}
										}
										
										$query = "SELECT * FROM `course_audience` WHERE `audience_type` = 'proxy_id' AND `audience_value` NOT IN (".$student_ids_string.") AND `course_id` = ".$db->qstr($COURSE_ID)." AND `audience_active` = 1 AND (`enroll_finish` > ".$db->qstr(time())." OR `enroll_finish` = 0)";
										if ($student_records = $db->GetAll($query)) {
											foreach ($student_records as $student_record) {
												$removed_students[] = $student_record["audience_value"];
											}
										}
																				
										foreach($new_students as $student_id){
											if(!$db->AutoExecute("course_audience", array("audience_type" => "proxy_id", "audience_value" => $student_id, "course_id" => $COURSE_ID, "cperiod_id" => $cperiod_id), "INSERT")){
												add_error("Unable to insert the student [".$student_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
											}
										}
										
										foreach($updated_students as $student_id){
											if(!$db->AutoExecute("course_audience", array("audience_type" => "proxy_id", "audience_value" => $student_id, "course_id" => $COURSE_ID, "enroll_finish" => 0, "audience_active" => 1, "cperiod_id" => $cperiod_id), "UPDATE", "`audience_value` = ".$db->qstr($student_id)." AND `audience_type` = 'proxy_id' AND `course_id` = ".$db->qstr($COURSE_ID))){
												add_error("Unable to update the student [".$student_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
											}
										}
										
										foreach($removed_students as $student_id){
											if(!$db->AutoExecute("course_audience", array("audience_type" => "proxy_id", "audience_value" => $student_id, "course_id" => $COURSE_ID, "enroll_finish" => time(), "audience_active" => 0, "cperiod_id" => $cperiod_id), "UPDATE", "`audience_value` = ".$db->qstr($student_id)." AND `audience_type` = 'proxy_id' AND `course_id` = ".$db->qstr($COURSE_ID))){
												add_error("Unable to deactivate the student [".$student_id."] as an audience member for course [".$COURSE_ID."]. Please try again later.");				
											}
										}
									}
								}
							} elseif ((isset($_POST["associated_student"]) && strlen($_POST["associated_student"])) || isset($_POST["group_order"]) && strlen($_POST["group_order"])) {
								$ERROR++;
								$ERRORSTR[] = "Please ensure you select an <strong>Enrollment period</strong> for the selected course audience.";
							}
							
							if (!$ERROR) {
								$NOTICE = 0;
								$url = ENTRADA_URL."/admin/courses";
								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully edited <strong>".html_encode($PROCESSED["course_name"])."</strong> in the system.<br /><br />".$msg;
								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";
							}

							application_log("success", "Course [".$COURSE_ID."] has been modified.");
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was a problem updating this course in the system. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error updating a course. Database said: ".$db->ErrorMsg());
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				case 1 :
				default :
					$PROCESSED = $course_details;
				break;
			}

			// Display Content
			switch($STEP) {
				case 2 :
					if ($SUCCESS) {
						echo display_success();
					}
					if ($NOTICE) {
						echo display_notice();
					}
					if ($ERROR) {
						echo display_error();
					}
				break;
				case 1 :
				default :

					
					$LASTUPDATED	= $course_details["updated_date"];

					$course_directors	= array();
					$curriculum_coordinators = array();
					$chosen_course_directors	= array();

					
					$query = "SELECT a.* FROM `course_audience` AS a
								JOIN `curriculum_periods` AS b
								ON a.`cperiod_id` = b.`cperiod_id`
								WHERE a.`course_id` = ".$db->qstr($COURSE_ID)." 
								AND a.`audience_active` = 1 
								AND (a.`enroll_finish` = 0 OR a.`enroll_finish` > ".$db->qstr(time()).")
								AND b.`finish_date` >= ".$db->qstr(time());
					$course_audience = $db->GetAll($query);
					if ($course_audience) {
						$PROCESSED["cperiod_id"] = $course_audience[0]["cperiod_id"];
						foreach($course_audience as $audience_member){
							if ($audience_member["audience_type"] == "group_id") {
								$query = "SELECT `group_id`,`group_name` FROM `groups` WHERE `group_id` = ".$db->qstr($audience_member["audience_value"]);
								$result = $db->GetRow($query);
								if($result){
									$PROCESSED["groups"][] = array("id"=>$result["group_id"],"title"=>$result["group_name"]);
								}
							} else {
								$PROCESSED["associated_students"][] = $audience_member["audience_value"];
							}
						}
					}
					
					
					
					$query	= "
							SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`organisations`.`organisation_id`
							FROM `".AUTH_DATABASE."`.`user_data`
							LEFT JOIN `".AUTH_DATABASE."`.`user_access`
							ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
							LEFT JOIN `".AUTH_DATABASE."`.`organisations`
							ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
							WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'faculty'
							AND (`".AUTH_DATABASE."`.`user_access`.`role` = 'director' OR `".AUTH_DATABASE."`.`user_access`.`role` = 'admin')
							AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
							AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
							ORDER BY `fullname` ASC";
					$results	= ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach($results as $result) {
							$course_directors[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
						$DIRECTOR_LIST = $course_directors;
					}

					$query	= "
							SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`, `".AUTH_DATABASE."`.`organisations`.`organisation_id`
							FROM `".AUTH_DATABASE."`.`user_data`
							LEFT JOIN `".AUTH_DATABASE."`.`user_access`
							ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
							LEFT JOIN `".AUTH_DATABASE."`.`organisations`
							ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
							WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'staff'
							AND `".AUTH_DATABASE."`.`user_access`.`role` = 'admin'
							AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
							AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
							ORDER BY `fullname` ASC";
					$results	= ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach($results as $result) {
							$curriculum_coordinators[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
						$COORDINATOR_LIST = $curriculum_coordinators;
					}

					
					/**
					 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
					 * This is actually accomplished after the event is inserted below.
					 */
					if ((isset($_POST["associated_director"]))) {
						$associated_director = explode(',',$_POST["associated_director"]);
						foreach($associated_director as $contact_order => $proxy_id) {
							if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
								$chosen_course_directors[(int) $contact_order] = $proxy_id;
							}
						}
					} else {
						$query		= "SELECT * FROM `course_contacts` WHERE `course_id` = ".$db->qstr($COURSE_ID)." AND `contact_type` = 'director' ORDER BY `contact_order` ASC";
						$results	= $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$chosen_course_directors[$result["contact_order"]] = $course_directors[$result["proxy_id"]]["proxy_id"];
							}
						}
					}
					
					if ((isset($_POST["associated_coordinator"]))) {
						$associated_coordinator = explode(',',$_POST["associated_coordinator"]);
						foreach($associated_coordinator as $contact_order => $proxy_id) {
							if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
								$chosen_ccoordinators[] = $proxy_id;
							}
						}
					} else {
						$query		= "SELECT * FROM `course_contacts` WHERE `course_id`=".$db->qstr($COURSE_ID)." AND `contact_type` = 'ccoordinator'";
						$results	= $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$chosen_ccoordinators[] = $result["proxy_id"];
							}
						}
					}

					// Compiles Program Coordinator list
					$programcoodinators = array();
		
					$query		= "
								SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`role` = 'pcoordinator'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results	= ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$programcoodinators[$result["proxy_id"]] = $result["fullname"]. ' (' . $result['organisation_title'].')';
						}
					}
		
					// Compiles Evaluation Representative (evalrep_id)  list
					$evaluationreps = array();
		
					$query		= "
								SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'faculty'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results	= ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$evaluationreps[$result["proxy_id"]] = $result["fullname"] . ' (' . $result['organisation_title'].')';
						}
					}
		
					// Compiles Student Representative (evalrep_id)  list
					$studentreps = array();
		
					$query		= "
								SELECT `".AUTH_DATABASE."`.`user_data`.`id` AS `proxy_id`, CONCAT_WS(', ', `".AUTH_DATABASE."`.`user_data`.`lastname`, `".AUTH_DATABASE."`.`user_data`.`firstname`) AS `fullname`,`".AUTH_DATABASE."`.`user_data`.`id`, `".AUTH_DATABASE."`.`organisations`.`organisation_title`
								FROM `".AUTH_DATABASE."`.`user_data`
								LEFT JOIN `".AUTH_DATABASE."`.`user_access`
								ON `".AUTH_DATABASE."`.`user_access`.`user_id` = `".AUTH_DATABASE."`.`user_data`.`id`
								LEFT JOIN `".AUTH_DATABASE."`.`organisations`
								ON `".AUTH_DATABASE."`.`user_data`.`organisation_id` = `".AUTH_DATABASE."`.`organisations`.`organisation_id`
								WHERE `".AUTH_DATABASE."`.`user_access`.`group` = 'student'
								AND `".AUTH_DATABASE."`.`user_access`.`app_id` = '".AUTH_APP_ID."'
								AND `".AUTH_DATABASE."`.`user_access`.`account_active` = 'true'
								ORDER BY `fullname` ASC";
					$results	= ((USE_CACHE) ? $db->CacheGetAll(AUTH_CACHE_TIMEOUT, $query) : $db->GetAll($query));
					if ($results) {
						foreach ($results as $result) {
							$studentreps[$result["proxy_id"]] = $result["fullname"] . ' (' . $result['organisation_title'].')';
						}
					}
					
					
					/**
					 * Compiles the list of students.
					 */
					$STUDENT_LIST = array();
					$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND b.`group` = 'student'
								AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
								ORDER BY b.`role` ASC, a.`lastname` ASC, a.`firstname` ASC";
					$results = $db->GetAll($query);
					if ($results) {
						foreach($results as $result) {
							$STUDENT_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
						}
					}
					
					
					
					
					if ($ERROR) {
						echo display_error();
					}
					?>
					<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?<?php echo replace_query(array("step" => 2)); ?>" method="post" id="editCourseForm" onsubmit="selIt()">
					<h2 title="Course Details Section">Course Details</h2>
					<div id="course-details-section">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Course Details">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 22%" />
							<col style="width: 75%" />
						</colgroup>
						<tbody>
							<tr>
								<td></td>
								<td style="vertical-align: top"><label for="curriculum_type_id" class="form-nrequired">Curriculum Category</label></td>
								<td>
									<select id="curriculum_type_id" name="curriculum_type_id" style="width: 250px" onchange="loadCurriculumPeriods(this.options[this.selectedIndex].value)">
										<option value="0"<?php echo (((!isset($PROCESSED["curriculum_type_id"])) || (!(int) $PROCESSED["curriculum_type_id"])) ? " selected=\"selected\"" : ""); ?>>- Select Curriculum Category -</option>
									<?php
									$query = "	SELECT a.* FROM `curriculum_lu_types` AS a 
										JOIN `curriculum_type_organisation` AS b 
										ON a.`curriculum_type_id` = b.`curriculum_type_id` 
										WHERE a.`curriculum_type_active` = 1 
										AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
										ORDER BY `curriculum_type_order` ASC";
									
									$results	= $db->GetAll($query);
									if ($results) {
										foreach($results as $result) {
											echo "<option value=\"".(int) $result["curriculum_type_id"]."\"".(((isset($PROCESSED["curriculum_type_id"])) && ($PROCESSED["curriculum_type_id"] == $result["curriculum_type_id"])) ? " selected=\"selected\"" : "").">".html_encode($result["curriculum_type_name"])."</option>\n";
										}
									}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td><label for="course_name" class="form-required">Course Name</label></td>
								<td><input type="text" id="course_name" name="course_name" value="<?php echo html_encode($PROCESSED["course_name"]); ?>" maxlength="85" style="width: 243px" /></td>
							</tr>
							<tr>
								<td></td>
								<td><label for="course_code" class="form-required">Course Code</label></td>
								<td><input type="text" id="course_code" name="course_code" value="<?php echo html_encode($PROCESSED["course_code"]); ?>" maxlength="16" style="width: 243px" /></td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td style="vertical-align: top"><span class="form-nrequired">Reminder Notifications</span></td>
								<td style="vertical-align: top">
									<input type="radio" name="notifications" id="notification_on" value="1"<?php echo (((!isset($PROCESSED["notifications"])) || ((isset($PROCESSED["notifications"])) && ($PROCESSED["notifications"]))) ? " checked=\"checked\"" : ""); ?> /> <label for="notification_on">Send e-mail notifications to faculty for events under this course.</label><br />
									<input type="radio" name="notifications" id="notification_off" value="0"<?php echo (((isset($PROCESSED["notifications"])) && (!(int) $PROCESSED["notifications"])) ? " checked=\"checked\"" : ""); ?> /> <label for="notification_off"><strong>Do not</strong> send e-mail notifications to faculty for events under this course.</label>
								</td>
							</tr>
						</tbody>
						</table>
					</div>

					<?php 
					
						list($course_objectives,$top_level_id) = courses_fetch_objectives_for_org($ENTRADA_USER->getActiveOrganisation(), array($COURSE_ID), -1, 0, false, $posted_objectives);
						require_once(ENTRADA_ABSOLUTE."/javascript/courses.js.php");
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
					?>
					<a name="course-objectives-section"></a>
					<h2 title="Course Objectives Section">Course Objectives</h2>
					<div id="course-objectives-section">
						<input type="hidden" id="objectives_head" name="course_objectives" value="" />
						<?php
						if($course_objectives){
						if (is_array($course_objectives["primary_ids"])) {
							foreach ($course_objectives["primary_ids"] as $objective_id) {
								echo "<input type=\"hidden\" class=\"primary_objectives\" id=\"primary_objective_".$objective_id."\" name=\"primary_objectives[]\" value=\"".$objective_id."\" />\n";
							}
						}
						if (is_array($course_objectives["secondary_ids"])) {
							foreach ($course_objectives["secondary_ids"] as $objective_id) {
								echo "<input type=\"hidden\" class=\"secondary_objectives\" id=\"secondary_objective_".$objective_id."\" name=\"secondary_objectives[]\" value=\"".$objective_id."\" />\n";
							}
						}
						if (is_array($course_objectives["tertiary_ids"])) {
							foreach ($course_objectives["tertiary_ids"] as $objective_id) {
								echo "<input type=\"hidden\" class=\"tertiary_objectives\" id=\"tertiary_objective_".$objective_id."\" name=\"tertiary_objectives[]\" value=\"".$objective_id."\" />\n";
							}
						}
						}
						?>
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0">
						<colgroup>
							<col width="3%" />
							<col width="22%" />
							<col width="75%" />
						</colgroup>
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td style="vertical-align: top">
									Clinical Presentations
									<div class="content-small" style="margin-top: 5px">
										<strong>Note:</strong> For more detailed information please refer to the <a href="http://www.mcc.ca/Objectives_online/objectives.pl?lang=english&loc=contents" target="_blank" style="font-size: 11px">MCC Objectives for the Qualifying Examination</a>.
									</div>
								</td>
								<td>
									<?php
									if(!$clinical_presentations_list){
										echo display_notice();
									}
									else{   
									?>
									<select class="multi-picklist" id="PickList" name="clinical_presentations[]" multiple="multiple" size="5" style="width: 100%; margin-bottom: 5px">
									<?php
									if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
										$ONLOAD[] = "$('clinical_presentations_list').style.display = 'none'";
										foreach ($clinical_presentations as $objective_id => $presentation_name) {
											echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
										}
									}
									?>
									</select>
									
									<div style="float: left; display: inline">
										<input type="button" id="clinical_presentations_list_state_btn" class="button" value="Show List" onclick="toggle_list('clinical_presentations_list')" />
									</div>
									<div style="float: right; display: inline">
										<input type="button" id="clinical_presentations_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
										<input type="button" id="clinical_presentations_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
									</div>
									<div id="clinical_presentations_list" style="clear: both; padding-top: 3px; display: none">
										<h2>Clinical Presentations List</h2>
										<select class="multi-picklist" id="SelectList" name="other_event_objectives_list" multiple="multiple" size="15" style="width: 100%">
										<?php
										if ((is_array($clinical_presentations_list)) && (count($clinical_presentations_list))) {
											foreach ($clinical_presentations_list as $objective_id => $presentation_name) {
												if (!array_key_exists($objective_id, $clinical_presentations)) {
													echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
												}
											}
										}
										?>
										</select>
									</div>
									<?php
									}
									?>
									<input type="hidden" value="1" name="clinical_presentations_submit" />
									<script type="text/javascript">
									if($('PickList')){
										$('PickList').observe('keypress', function(event) {
											if (event.keyCode == Event.KEY_DELETE) {
												delIt();
											}
										});
									}
									if($('SelectList')){
										$('SelectList').observe('keypress', function(event) {
											if (event.keyCode == Event.KEY_RETURN) {
												addIt();
											}
										});
									}
									</script>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<label for="objective_select" class="form-nrequired">Curriculum Objectives</label>
								</td>
								<td>
									<?php
									if(!count($course_objectives["objectives"])){
										$NOTICE = 1;
										$NOTICESTR = null;
										$NOTICESTR[] = "No Curriculum Objectives were found for this organisation.";
										echo display_notice();
										
									}
									else{
									?>
									<select id="objective_select" onchange="showMultiSelect()">
									<option value="">- Select Competency -</option>
									<?php
									$objective_select = "";
									foreach ($course_objectives["objectives"] as $parent_id => $parent) {
										if ($parent["parent"] == $top_level_id) {
											echo "<optgroup label=\"".$parent["name"]."\">";
											foreach($course_objectives["objectives"] as $objective_id => $objective) {
												if ($objective["parent"] == $parent_id) {
													echo "<option value=\"id_".$objective_id."\">".$objective["name"]."</option>";
													foreach ($course_objectives["objectives"] as $child_id => $child) {
														if ($child["parent"] == $objective_id) {
															if (array_search($child_id, $course_objectives["used_ids"]) !== false) {
																$checked = "checked=\"checked\"";
															} else {
																$checked = "";
															}
															$selectable_objectives[$child_id] = array("text" => $child["name"], "value" => $child_id, "checked" => $checked, "category" => true);
															foreach($course_objectives["objectives"] as $grandkid_id => $grandkid) {
																if ($grandkid["parent"] == $child_id) {
																	if (array_search($grandkid_id, $course_objectives["used_ids"]) !== false) {
																		$checked = "checked=\"checked\"";
																	} else {
																		$checked = "";
																	}
																	if ($grandkid["parent"] == $child_id) {
																		$selectable_objectives[$grandkid_id] = array("text" => "<strong>".$grandkid["name"]."</strong><br />".$grandkid["description"], "value" => $grandkid_id, "checked" => $checked);
																	}
																}
															}
														}
													}
													$objective_select .= course_objectives_multiple_select_options_checked("id_".$objective_id, $selectable_objectives, array("title" => "Please select program or curricular objectives", "cancel" => true, "cancel_text" => "Close", "submit" => false, "width" => "550px"));
												}
												unset($selectable_objectives);
											}
											echo "\n</optgroup>";
										}
									}
									?>
									</select>
									<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>
									<span class="content-small"><strong>Helpful Tip:</strong> Select a competency from the select box above, and a list of course and curricular objectives will then be displayed. Once you have selected an objective it will be placed in the list below and you may leave it as primary or change the importance to secondary or tertiary.</span>
									<?php echo $objective_select; ?>
									<script type="text/javascript">
										var multiselect = [];
										var id;
										function showMultiSelect() {
											$$('select_multiple_container').invoke('hide');
											id = $F('objective_select');
											if (multiselect[id]) {
												$('objective_select').hide();
												multiselect[id].container.show();
												multiselect[id].container.down("input").activate();
											} else {
												if ($(id+'_options')) {
													$(id+'_options').addClassName('multiselect-processed');
													multiselect[id] = new Control.SelectMultiple('objectives_head',id+'_options',{
														checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
														nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
														resize: id+'_scroll',
														afterCheck: function(element) {
															if (element.checked) {
																addObjective(element);
															} else {
																removeObjective(element);
															}
														}
													});
	
													$(id+'_cancel').observe('click',function(event){
														this.container.hide();
														$('objective_select').show();
														$('objective_select').options.selectedIndex = 0;
														return false;
													}.bindAsEventListener(multiselect[id]));;
	
													$('objective_select').hide();
													multiselect[id].container.show();
													multiselect[id].container.down("input").activate();
												}
											}
											return false;
										}
									</script>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
								<td style="padding-top: 5px;">
									<div id="objectives_list">
									<?php echo course_objectives_in_list($course_objectives,$top_level_id, $top_level_id, true, false, 1, false, true, "primary", true); ?>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
						</tbody>
						</table>
					</div>
					
					<h2 title="Course Contacts Section">Course Contacts</h2>
					<div id="course-contacts-section">
						<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Course Contacts">
						<colgroup>
							<col style="width: 3%" />
							<col style="width: 22%" />
							<col style="width: 75%" />
						</colgroup>
						<tbody>
							<tr>
								<td></td>
								<td style="vertical-align: top"><label for="director_name" class="form-nrequired">Course Directors</label></td>
								<td>
									<div style="position: relative;">
										<script type="text/javascript">
										var sortables = new Array();
										function updateOrder(type) {
											$('associated_'+type).value = Sortable.sequence(type+'_list');
										}
										function addItem(type) {
											if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
												var li = new Element('li', {'class':'community', 'id':type+'_'+$(type+'_id').value, 'style':'cursor: move;'}).update($(type+'_name').value);
												$(type+'_name').value = '';
												li.insert({bottom: '<img src=\"<?php echo ENTRADA_URL; ?>/images/action-delete.gif\" class=\"list-cancel-image\" onclick=\"removeItem(\''+$(type+'_id').value+'\', \''+type+'\')\" />'});
												$(type+'_id').value	= '';
												$(type+'_list').appendChild(li);
												sortables[type] = Sortable.destroy($(type+'_list'));
												Sortable.create(type+'_list', {onUpdate : function(){updateOrder(type);}});
												updateOrder(type);
											} else if ($(type+'_'+$(type+'_id').value) != null) {
												alert('Important: Each user may only be added once.');
												$(type+'_id').value = '';
												$(type+'_name').value = '';
												return false;
											} else if ($(type+'_name').value != '' && $(type+'_name').value != null) {
												alert('Important: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');
												return false;
											} else {
												return false;
											}
										}
										
										function addItemNoError(type) {
											if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
												addItem(type);
											}
										}
						
										function copyItem(type) {
											if (($(type+'_name') != null) && ($(type+'_ref') != null)) {
												$(type+'_ref').value = $(type+'_name').value;
											}
						
											return true;
										}
						
										function checkItem(type) {
											if (($(type+'_name') != null) && ($(type+'_ref') != null) && ($(type+'_id') != null)) {
												if ($(type+'_name').value != $(type+'_ref').value) {
													$(type+'_id').value = '';
												}
											}
						
											return true;
										}
						
										function removeItem(id, type) {
											if ($(type+'_'+id)) {
												$(type+'_'+id).remove();
												Sortable.destroy($(type+'_list'));
												Sortable.create(type+'_list', {onUpdate : function (type) {updateOrder(type)}});
												updateOrder(type);
											}
										}
						
										function selectItem(id, type) {
											if ((id != null) && ($(type+'_id') != null)) {
												$(type+'_id').value = id;
											}
										}
										
										function loadCurriculumPeriods(cperiod_id) {
											var updater = new Ajax.Updater('curriculum_type_periods', '<?php echo ENTRADA_URL."/api/curriculum_type_periods.api.php"; ?>',{
												method:'post',
												parameters: {
													'cperiod_id': cperiod_id
												},
												onFailure: function(transport){
													$('curriculum_type_periods').update(new Element('div', {'class':'display-error'}).update('No Periods were found for this Curriculum Category.'));
												}
											});
										}
										</script>
										<input type="text" id="director_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkItem('director')" onblur="addItemNoError('director')" />
										<script>
											$('director_name').observe('keypress', function(event){
											    if (event.keyCode == Event.KEY_RETURN) {
											        addItem('director');
											        Event.stop(event);
											    }
											});
										</script>
										<?php
										$ONLOAD[] = "Sortable.create('director_list', {onUpdate : function() {updateOrder('director')}})";
										$ONLOAD[] = "$('associated_director').value = Sortable.sequence('director_list')";
										?>
										<div class="autocomplete" id="director_name_auto_complete"></div><script type="text/javascript">new Ajax.Autocompleter('director_name', 'director_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=director', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectItem(li.id, 'director'); copyItem('director');}});</script>
										<input type="hidden" id="associated_director" name="associated_director" />
										<input type="button" class="button-sm" onclick="addItem('director');" value="Add" style="vertical-align: middle" />
										<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
										<ul id="director_list" class="menu" style="margin-top: 15px">
											<?php
											if (is_array($chosen_course_directors) && count($chosen_course_directors)) {
												foreach ($chosen_course_directors as $director) {
													if ((array_key_exists($director, $DIRECTOR_LIST)) && is_array($DIRECTOR_LIST[$director])) {
														?>
															<li class="community" id="director_<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>" style="cursor: move;"><?php echo $DIRECTOR_LIST[$director]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $DIRECTOR_LIST[$director]["proxy_id"]; ?>', 'director');"/></li>								
														<?php
													}
												}
											}
											?>
										</ul>
										<input type="hidden" id="director_ref" name="director_ref" value="" />
										<input type="hidden" id="director_id" name="director_id" value="" />
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td></td>
								<td style="vertical-align: top"><label for="coordinator_name" class="form-nrequired">Curriculum Coordinators</label></td>
								<td>
									<div style="position: relative;">
										<input type="text" id="coordinator_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" onkeyup="checkItem('coordinator')" onblur="addItemNoError('coordinator')" />
										<script>
											$('coordinator_name').observe('keypress', function(event){
											    if (event.keyCode == Event.KEY_RETURN) {
											        addItem('coordinator');
											        Event.stop(event);
											    }
											});
										</script>
										<?php
										$ONLOAD[] = "Sortable.create('coordinator_list', {onUpdate : function() {updateOrder('coordinator')}})";
										$ONLOAD[] = "$('associated_coordinator').value = Sortable.sequence('coordinator_list')";
										?>
										<div class="autocomplete" id="coordinator_name_auto_complete"></div><script type="text/javascript">new Ajax.Autocompleter('coordinator_name', 'coordinator_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=coordinator', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {selectItem(li.id, 'coordinator'); copyItem('coordinator');}});</script>
										<input type="hidden" id="associated_coordinator" name="associated_coordinator" />
										<input type="button" class="button-sm" onclick="addItem('coordinator');" value="Add" style="vertical-align: middle" />
										<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
										<ul id="coordinator_list" class="menu" style="margin-top: 15px">
											<?php
											if (is_array($chosen_ccoordinators) && count($chosen_ccoordinators)) {
												foreach ($chosen_ccoordinators as $coordinator) {
													if ((array_key_exists($coordinator, $COORDINATOR_LIST)) && is_array($COORDINATOR_LIST[$coordinator])) {
														?>
															<li class="community" id="coordinator_<?php echo $COORDINATOR_LIST[$coordinator]["proxy_id"]; ?>" style="cursor: move;"><?php echo $COORDINATOR_LIST[$coordinator]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" class="list-cancel-image" onclick="removeItem('<?php echo $COORDINATOR_LIST[$coordinator]["proxy_id"]; ?>', 'coordinator');"/></li>								
														<?php
													}
												}
											}
											?>
										</ul>
										<input type="hidden" id="coordinator_ref" name="coordinator_ref" value="" />
										<input type="hidden" id="coordinator_id" name="coordinator_id" value="" />
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>

							<!-- Listing the Program Coordinator for the selected course -->
							<tr>
								<td></td>
								<td><label for="programcoodinator_id" class="form-nrequired">Program Coordinator</label></td>
								<td>
								<?php
									if ((is_array($programcoodinators)) && (count($programcoodinators))) {
										echo "<select id=\"pcoord_id\" name=\"pcoord_id\" style=\"width: 95%\">\n";
										echo "<option value=\"\"".((!isset($PROCESSED["pcoord_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
										foreach($programcoodinators as $proxy_id => $fullname) {
											echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["pcoord_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
										}
										echo "</select>\n";
									} else {
										echo "<input type=\"hidden\" id=\"pcoord_id\" name=\"pcoord_id\" value=\"0\" />\n";
										echo "Program Coordinator Information Not Available\n";
									}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
								<td>
									<span class="content-small"><strong>Important:</strong> Program Coordinators will be able to add, edit or remove learning events in this course.</span>
								</td>
							</tr>

							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>


							<!-- Listing the Evaluation Rep for the selected course -->
							<tr>
								<td></td>
								<td><label for="evaluationrep_id" class="form-nrequired">Evaluation Rep.</label></td>
								<td>
									<?php
									if ((is_array($evaluationreps)) && (count($evaluationreps))) {
										echo "<select id=\"evalrep_id\" name=\"evalrep_id\" style=\"width: 95%\">\n";
										echo "<option value=\"\"".((!isset($PROCESSED["evalrep_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
										foreach($evaluationreps as $proxy_id => $fullname) {
											echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["evalrep_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
										}
										echo "</select>\n";
									} else {
										echo "<input type=\"hidden\" id=\"evalrep_id\" name=\"evalrep_id\" value=\"0\" />\n";
										echo "Evaluation Representative Information Not Available\n";
									}
									?>
								</td>
							</tr>

							<!-- Listing the Student Rep for the selected course -->
							<tr>
								<td></td>
								<td><label for="studentrep_id" class="form-nrequired">Student Rep.</label></td>
								<td>
									<?php
									if ((is_array($studentreps)) && (count($studentreps))) {
										echo "<select id=\"studrep_id\" name=\"studrep_id\" style=\"width: 95%\">\n";
										echo "<option value=\"\"".((!isset($PROCESSED["studrep_id"])) ? " selected=\"selected\"" : "").">-- To Be Announced --</option>\n";
										foreach($studentreps as $proxy_id => $fullname) {
											echo "<option value=\"".(int) $proxy_id."\"".(($PROCESSED["studrep_id"] == $proxy_id) ? " selected=\"selected\"" : "").">".html_encode($fullname)."</option>\n";
										}
										echo "</select>\n";
									} else {
										echo "<input type=\"hidden\" id=\"studrep_id\" name=\"studrep_id\" value=\"0\" />\n";
										echo "Student Representative Information Not Available\n";
									}
								?>
								</td>
							</tr>
						</tbody>
						</table>
					</div>
					
					<!-- Course Audience-->	
					
					<h2>Course Audience</h2>
					<div>
						<table>
						<tr>
							<td style="vertical-align: top"><input type="radio" name="course_permission" id="course_permission_closed" value="closed"  style="vertical-align: middle" checked="checked" /></td>
							<td colspan="2" style="padding-bottom: 15px">
								<label for="event_audience_type_course" class="radio-group-title">This course is private.</label>
								<div class="content-small">This course is only viewable by its members.</div>
							</td>
						</tr>
						<tr>
							<td style="vertical-align: top"><input type="radio" name="course_permission" id="course_permission_open" value="open"  style="vertical-align: middle"<?php echo (($PROCESSED["permission"] == "open") ? " checked=\"checked\"" : ""); ?> /></td>
							<td colspan="2" style="padding-bottom: 15px">
								<label for="event_audience_type_course" class="radio-group-title">This course is open.</label>
								<div class="content-small">This course is viewable by everyone.</div>
							</td>
						</tr>
						<tr>
							<td><input type="checkbox" id="ldap_sync" name="sync_ldap" value ="1" <?php echo ((isset($PROCESSED["sync_ldap"]) && ($PROCESSED["sync_ldap"] == 1))?" checked=\"checked\"":"");?>/></td>
							<td colspan="2">
								<label for="sync_ldap" class="radio-group-title">Sync course with enrollment records.</label>
								<div class="content-small">Checking this box will sync this course list with the LDAP server twice a day.</div>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr class="course_audience group_audience">
							<td></td>
							<td><label for="group_ids" class="form-required">Associated Groups</label></td>
							<td>
								<select id="group_ids" name="group_ids" style="width: 203px">
									<option id="-1">-- Select a Group --</option>
								<?php

								$query = "	SELECT `group_id`,`group_name` FROM `groups`";
								$groups = $db->GetAll($query);							
								if (isset($groups)) {
									foreach ($groups as $group) {
										echo "<option value=\"".$group["group_id"]."\">".html_encode($group["group_name"])."</option>";
									}
								}
								?>
								</select>
								<div id="group_notice" class="content-small" >Use the list above to select the different groups to enroll in this course. When you select one, it will appear here.</div>
								<ol id="group_container" class="sortableList" style="display: none;">
									<?php
									foreach($PROCESSED["groups"] as $group) {
										echo "<li id=\"type_".$group["id"]."\" class=\"\">".$group["title"]."
											<a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\">
												<img src=\"".ENTRADA_URL."/images/action-delete.gif\">
											</a>
										</li>";
									}
									?>
								</ol>
								<input id="group_order" name="group_order" style="display: none;">
							</td>
						</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>

							<tr class="course_audience proxy_id_audience">
								<td></td>
								<td style="vertical-align: top"><label for="associated_proxy_ids" class="form-required">Associated Students</label></td>
								<td>
									<input type="text" id="student_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
									<?php
									$ONLOAD[] = "student_list = new AutoCompleteList({ type: 'student', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=student', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
									?>
									<div class="autocomplete" id="student_name_auto_complete"></div>

									<input type="hidden" id="associated_student" name="associated_student" />
									<input type="button" class="button-sm" id="add_associated_student" value="Add" style="vertical-align: middle" />
									<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
									<ul id="student_list" class="menu" style="margin-top: 15px">
										<?php
										if (isset($PROCESSED["associated_students"]) && is_array($PROCESSED["associated_students"])) {
											foreach ($PROCESSED["associated_students"] as $student) {
												if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
													?>
													<li class="community" id="student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="student_list.removeItem('<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
													<?php
													}
											}
										}
										?>
									</ul>
									<input type="hidden" id="student_ref" name="student_ref" value="" />
									<input type="hidden" id="student_id" name="student_id" value="" />
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><label for="period" class="form-required">Enrollment period</label></td>
								<td>
									<div id="curriculum_type_periods">
										<?php 
										if ($PROCESSED["curriculum_type_id"]) {
												$query = "SELECT * FROM `curriculum_periods` WHERE `curriculum_type_id` = ".$db->qstr($PROCESSED["curriculum_type_id"]." AND `active` = 1 AND `finish_date` >= ".$db->qstr(time()));
												if ($periods = $db->GetAll($query)) {
													?>
													<select name="period" id="period">
														<option value="0">-- Select a Period --</option>
														<?php
														foreach ($periods as $period) {
															echo "<option value=\"".$period["cperiod_id"]."\"".($PROCESSED["cperiod_id"] == $period["cperiod_id"] ? " selected=\"selected\"" : "").">".date("F jS, Y" ,$period["start_date"])." to ".date("F jS, Y" ,$period["finish_date"])."</option>";
														}
														?>
													</select>
													<?php
												} else {
													echo "<div class=\"display-notice\"><ul><li>No periods have been found for the selected <strong>Curriculum Category</strong>.</li></ul></div>";
												}
										} else {
											echo "<div class=\"display-notice\"><ul><li>No <strong>Curriculum Category</strong> has been selected.</li></ul></div>";
										}
										?>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<script type="text/javascript">
					$('student_list').observe('change', checkConditions);
					$('group_order').observe('change', checkConditions);

					function checkConditions(){
							if($F('associated_student')){
								var students = $F('associated_student').split(',');
								if(students.length>0);{
									if($F('group_order').length >0){
										checkConflict();
									}
								}
							}

						}

						function checkConflict(){
							new Ajax.Request('<?php echo ENTRADA_URL;?>/api/course-audience-conflicts.api.php',
							{
								method:'post',
								parameters: $("editCourseForm").serialize(true),
								onSuccess: function(transport){
								var response = transport.responseText || null;
								if(response !==null){
									var g = new k.Growler();
									g.smoke(response,{life:7});
								}
								},
								onFailure: function(){ alert('Unable to check if a conflict exists.') }
							});
						}

					</script>
			
			
			
					<!--/Course Audience-->					
					
					
					
					<div style="padding-top: 25px">
						<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td style="width: 25%; text-align: left">
								<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/events'" />
							</td>
							<td style="width: 75%; text-align: right; vertical-align: middle">
								<span class="content-small">After saving:</span>
								<select id="post_action" name="post_action">
								<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to course</option>
								<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another course</option>
								<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to course list</option>
								</select>
								<input type="submit" class="button" value="Save" />
							</td>
						</tr>
						</table>
					</div>
					</form>
					<?php
				break;
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a course you must provide a valid course identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid course identifer when attempting to edit a course.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a course you must provide the courses identifier.";

		echo display_error();

		application_log("notice", "Failed to provide course identifer when attempting to edit a course.");
	}
}
?>
