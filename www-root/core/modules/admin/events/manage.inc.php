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
 * This file is used to edit existing events in the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	/**
	 * Load the rich text editor.
	 */
	load_rte();

	echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";
	if ($EVENT_ID) {
		$query = "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($EVENT_ID)."
					AND b.`course_active` = '1'";
		$event_info	= $db->GetRow($query);
		if ($event_info["parent_id"]) {
			$selected_session_id = $EVENT_ID;
			$EVENT_ID = $event_info["parent_id"];
			
			$query = "	SELECT a.*, b.`organisation_id`
						FROM `events` AS a
						LEFT JOIN `courses` AS b
						ON b.`course_id` = a.`course_id`
						WHERE a.`event_id` = ".$db->qstr($EVENT_ID)."
						AND b.`course_active` = '1'";
			$event_info	= $db->GetRow($query);
		}
	} else {
		$event_info = false; 
		if ($db->AutoExecute("events", array("event_title" => "Placeholder Event", "release_start" => 1, "release_finish" => 2), "INSERT")) {
			$EVENT_ID = $db->Insert_Id();
			$HEAD[] = "<script type=\"text/javascript\"> 
						Event.observe(window, 'unload', 
							function() {
								var params = 'event_id=".(int) $EVENT_ID."'; 
								var ajax = new Ajax.Request( '".ENTRADA_URL."/api/remove-placeholder-event.api.php', { 
									method: 'get', 
									parameters: params, 
									asynchronous: false
								});
							}, 
							false
						);
						</script>";
		}
	}
	
	/**
	 * Fetch the Clinical Presentation details.
	 */
	$clinical_presentations_list = array();
	$clinical_presentations = array();

	$results = fetch_mcc_objectives(0, array(), $event_info["course_id"]);
	$course_id = $event_info["course_id"];
	if ($results) {
		foreach ($results as $result) {
			$clinical_presentations_list[$result["objective_id"]] = $result["objective_name"];
		}
	}

	/**
	 * Fetch the Curriculum Objective details.
	 */
	$curriculum_objectives_list = courses_fetch_objectives(array($event_info["course_id"]), 1, false, false, $EVENT_ID, true);
	$curriculum_objectives = array();

	$query = "SELECT `objective_id` FROM `event_objectives` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `objective_type` = 'course'";
	$results = $db->GetAll($query);
	if ($results) {
		foreach ($results as $result) {
			$curriculum_objectives_list["objectives"][$result["objective_id"]]["event_objective"] = true;
		}
	}

	/**
	 * Fetch the event type information.
	 */
	$event_eventtypes_list	= array();
	$event_eventtypes		= array();

	$query		= "SELECT * FROM `events_lu_eventtypes` WHERE `eventtype_active` = '1' ORDER BY `eventtype_order` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		foreach ($results as $result) {
			$event_eventtypes_list[$result["eventtype_id"]] = $result["eventtype_title"];
		}
	}

	$query		= "SELECT * FROM `event_eventtypes` WHERE `event_id` = ".$db->qstr($EVENT_ID)." ORDER BY `eeventtype_id` ASC";
	$results	= $db->GetAll($query);
	$initial_duration = 0;
	if ($results) {
		foreach ($results as $result) {
			$initial_duration += $result["duration"];
			$event_eventtypes[] = array($result["eventtype_id"], $result["duration"], $event_eventtypes_list[$result["eventtype_id"]]);
		}
	}
	?>
    <script type="text/javascript" charset="utf-8">
            var EVENT_LIST_STATIC_TOTAL_DURATION = true;
    </script>
    <?php
		
	$query 	= "SELECT * FROM `events`
			WHERE `parent_id` = ".$db->qstr($EVENT_ID)."
			ORDER BY `event_start` ASC";
	if ($event_sessions = $db->GetAll($query)) {
		$event_info["sessions"] = $event_sessions;
	}
	
	// Error Checking
	switch($STEP) {
		case 2 :
			if (isset($_POST["clinical_presentations_submit"]) && $_POST["clinical_presentations_submit"]) {
				if (((isset($_POST["clinical_presentations"])) && (is_array($_POST["clinical_presentations"])) && (count($_POST["clinical_presentations"])))) {
					foreach ($_POST["clinical_presentations"] as $objective_id) {
						if ($objective_id = clean_input($objective_id, array("trim", "int"))) {
							$query	= "	SELECT a.`objective_id`
										FROM `global_lu_objectives` AS a
										JOIN `course_objectives` AS b
										ON b.`course_id` = ".$event_info["course_id"]."
										AND a.`objective_id` = b.`objective_id`
										WHERE a.`objective_id` = ".$db->qstr($objective_id)."
										AND b.`objective_type` = 'event'
										AND a.`objective_active` = '1'";
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
				$query = "	SELECT a.`objective_id`
							FROM `event_objectives` AS a
							JOIN `course_objectives` AS b
							ON b.`course_id` = ".$event_info["course_id"]."
							AND a.`objective_id` = b.`objective_id`
							WHERE a.`objective_type` = 'event'
							AND b.`objective_type` = 'event'
							AND a.`event_id` = ".$db->qstr($EVENT_ID);
				$results = $db->GetAll($query);
				if ($results) {
					foreach ($results as $result) {
						$clinical_presentations[$result["objective_id"]] = $clinical_presentations_list[$result["objective_id"]];
					}
				}
			}
			if (isset($_POST["checked_objectives"]) && ($checked_objectives = $_POST["checked_objectives"]) && (is_array($checked_objectives))) {
				foreach ($checked_objectives as $objective_id => $status) {
					if ($objective_id = (int) $objective_id) {
						if (isset($_POST["objective_text"][$objective_id]) && ($tmp_input = clean_input($_POST["objective_text"][$objective_id], array("notags")))) {
							$objective_text = $tmp_input;
						} else {
							$objective_text = false;
						}
		
						$curriculum_objectives[$objective_id] = $objective_text;
					}
				}
			}


			if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {	
				/**
				 * Required field "event_title" / Event Title.
				 */
				if ((isset($_POST["event_title"])) && ($event_title = clean_input($_POST["event_title"], array("notags", "trim")))) {
					$PROCESSED["event_title"] = $event_title;
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Title</strong> field is required.";
				}
				/**
				 * Required fields "eventtype_id" / Event Type
				 */
				if (isset($_POST["eventtype_duration_order"])) {
					$event_types = explode(",", trim($_POST["eventtype_duration_order"]));
					$eventtype_durations = $_POST["duration_segment"];
	
					if ((is_array($event_types)) && (count($event_types))) {
						foreach ($event_types as $order => $eventtype_id) {
							if (($eventtype_id = clean_input($eventtype_id, array("trim", "int"))) && ($duration = clean_input($eventtype_durations[$order], array("trim", "int")))) {
								if (!($duration > 0)) {
									$ERROR++;
									$ERRORSTR[] = "Event type <strong>durations</strong> may not be 0 or negative.";
								}
	
								$query	= "SELECT `eventtype_title` FROM `events_lu_eventtypes` WHERE `eventtype_id` = ".$db->qstr($eventtype_id);
								$result	= $db->GetRow($query);
								if ($result) {
									$PROCESSED["event_types"][] = array($eventtype_id, $duration, $result["eventtype_title"]);
								} else {
									$ERROR++;
									$ERRORSTR[] = "One of the <strong>event types</strong> you specified was invalid.";
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "One of the <strong>event types</strong> you specified is invalid.";
							}
						}
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "The <strong>Event Types</strong> field is required.";
				}
	
				/**
				 * Non-required field "event_location" / Event Location
				 */
				if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
					$PROCESSED["event_location"] = $event_location;
				} else {
					$PROCESSED["event_location"] = "";
				}
	
				/**
				 * Required field "course_id" / Course
				 */
				if ((isset($_POST["course_id"])) && ($course_id = clean_input($_POST["course_id"], array("int")))) {
					$query	= "	SELECT * FROM `courses` 
								WHERE `course_id` = ".$db->qstr($course_id)."
								AND `course_active` = '1'";
					$result	= $db->GetRow($query);
					if ($result) {
						if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $course_id, $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), "create")) {
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
	
				/**
				 * Non-required field "event_phase" / Phase
				 */
				if ((isset($_POST["event_phase"])) && ($event_phase = clean_input($_POST["event_phase"], array("notags", "trim")))) {
					$PROCESSED["event_phase"] = $event_phase;
				} else {
					$PROCESSED["event_phase"] = "";
				}
	
				/**
				 * Non-required field "release_date" / Viewable Start (validated through validate_calendars function).
				 * Non-required field "release_until" / Viewable Finish (validated through validate_calendars function).
				 */
				$viewable_date = validate_calendars("viewable", false, false);
				if ((isset($viewable_date["start"])) && ((int) $viewable_date["start"])) {
					$PROCESSED["release_date"] = (int) $viewable_date["start"];
				} else {
					$PROCESSED["release_date"] = 0;
				}
				if ((isset($viewable_date["finish"])) && ((int) $viewable_date["finish"])) {
					$PROCESSED["release_until"] = (int) $viewable_date["finish"];
				} else {
					$PROCESSED["release_until"] = 0;
				}
			}

			if (isset($_POST["post_action"])) {
				switch($_POST["post_action"]) {
					case "manage" :
						$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] = "manage";
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
			if ($ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {	
				/**
				 * Event Description
				 */
				if ((isset($_POST["event_description"])) && (clean_input($_POST["event_description"], array("notags", "nows")))) {
					$PROCESSED["event_description"] = clean_input($_POST["event_description"], array("allowedtags"));
				} else {
					$PROCESSED["event_description"] = "";
				}
	
				/**
				 * Teacher's Message
				 */
				if ((isset($_POST["event_message"])) && (clean_input($_POST["event_message"], array("notags", "nows")))) {
					$PROCESSED["event_message"] = clean_input($_POST["event_message"], array("allowedtags"));
				} else {
					$PROCESSED["event_message"] = "";
				}
			}

			/**
			 * Free-Text Objectives
			 */
			if ((isset($_POST["event_objectives"])) && (clean_input($_POST["event_objectives"], array("notags", "nows")))) {
				$PROCESSED["event_objectives"] = clean_input($_POST["event_objectives"], array("allowedtags"));
			} else {
				$PROCESSED["event_objectives"] = "";
			}
			if ((!isset($event_info["sessions"]) || !count($event_info["sessions"])) && $ENTRADA_ACL->amIAllowed('event', 'create', false)) {
				/**
				 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
				 */
				$start_date = validate_calendars("event", true, false);
				if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
					$PROCESSED["event_start"] = (int) $start_date["start"];
				}
				
				if ((isset($_POST["current_session"])) && (((int) $_POST["current_session"]) || ((int) $_POST["current_session"]) === 0)) {
					$session_id = ((int) $_POST["current_session"]);
				}
				
				if ((isset($_POST["students_session_audience"]))) {
					$associated_audience = explode(',', $_POST["students_session_audience"]);
					if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
						foreach($associated_audience as $audience_id) {
							if (strpos($audience_id, "student") !== false) {
								if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
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
									if ($result) {
										$PROCESSED["associated_proxy_ids"][] = $proxy_id;
									}
								}
							}
						}
					}
				}
			
				if ((isset($_POST["groups_session_audience"]))) {
					$associated_audience = explode(',', $_POST["groups_session_audience"]);
					if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
						foreach($associated_audience as $audience_id) {
							if (strpos($audience_id, "group") !== false) {
								if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
									$query = "	SELECT *
												FROM `groups`
												WHERE `group_id` = ".$db->qstr($group_id)."
												AND `group_active` = 1";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_group_ids"][] = $group_id;
									}
								}
							}
						}
					}
				}
				
				if ((isset($_POST["course_session_audience"])) && $_POST["course_session_audience"]) {
					if ($course_id || ($course_id = $PROCESSED["course_id"])) {
						$query = "SELECT *
									FROM `courses`
									WHERE `course_id` = ".$db->qstr($course_id)."
									AND `course_active` = 1";
						$result	= $db->GetRow($query);
						if ($result) {
							$PROCESSED["associated_course_ids"][] = $course_id;
						}
					}
				}
			}
			
			if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {	
				/**
				 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
				 * This is actually accomplished after the event is inserted below.
				 */
				if ((isset($_POST["associated_faculty"]))) {
					$associated_faculty = explode(',',$_POST["associated_faculty"]);
					foreach($associated_faculty as $contact_order => $proxy_id) {
						if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
							$PROCESSED["associated_faculty"][(int) $contact_order] = $proxy_id;
						}
					}
				}
				
				/**
				 * Required field "course_id" / Course
				 */
				if ((isset($_POST["event_children"])) && ($event_children = clean_input($_POST["event_children"], array("int")))) {
					$PROCESSED["event_children"] = $event_children;
				} else {
					$PROCESSED["event_children"] = 0;
				}
			}
			
			if (!$ERROR) {
				if (isset($event_info["sessions"]) && count($event_info["sessions"])) {
					$PROCESSED["session"] = array();
					
					if ((isset($_POST["current_session"])) && (((int) $_POST["current_session"]) || ((int) $_POST["current_session"]) === 0)) {
						$session_id = ((int) $_POST["current_session"]);
						$PROCESSED["session"]["event_id"] = $session_id;
					}
					
					if ($ENTRADA_ACL->amIAllowed(new EventResource($session_id, $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
						/**
						 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
						 */
						$start_date = validate_calendars("event", true, false);
						if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
							$PROCESSED["session"]["event_start"] = (int) $start_date["start"];
						}
					
						/**
						 * Non-required field "event_location" / Event Location
						 */
						if ((isset($_POST["event_location"])) && ($event_location = clean_input($_POST["event_location"], array("notags", "trim")))) {
							$PROCESSED["session"]["event_location"] = $event_location;
						} else {
							$PROCESSED["session"]["event_location"] = "";
						}
						
						/**
						 * Required field "event_title" / Event Title.
						 */
						if ((isset($_POST["session_title"])) && ($event_title = clean_input($_POST["session_title"], array("notags", "trim")))) {
							$PROCESSED["session"]["event_title"] = $event_title;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The <strong>Session Title</strong> is required.";
						}
						
						
						if ((isset($_POST["students_session_audience"]))) {
							$associated_audience = explode(',', $_POST["students_session_audience"]);
							if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
								foreach($associated_audience as $audience_id) {
									if (strpos($audience_id, "student") !== false) {
										if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
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
											if ($result) {
												$PROCESSED["session"]["associated_proxy_ids"][] = $proxy_id;
											}
										}
									}
								}
							}
						}
						
						if ((isset($_POST["groups_session_audience"]))) {
							$associated_audience = explode(',', $_POST["groups_session_audience"]);
							if ((isset($associated_audience)) && (is_array($associated_audience)) && (count($associated_audience))) {
								foreach($associated_audience as $audience_id) {
									if (strpos($audience_id, "group") !== false) {
										if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $audience_id), array("trim", "int"))) {
											$query = "	SELECT *
														FROM `groups`
														WHERE `group_id` = ".$db->qstr($group_id)."
														AND `group_active` = 1";
											$result	= $db->GetRow($query);
											if ($result) {
												$PROCESSED["session"]["associated_group_ids"][] = $group_id;
											}
										}
									}
								}
							}
						}
						
						if ((isset($_POST["course_session_audience"])) && $_POST["course_session_audience"]) {
							if ($course_id || ($course_id = $PROCESSED["course_id"])) {
								$query = "SELECT *
											FROM `courses`
											WHERE `course_id` = ".$db->qstr($course_id)."
											AND `course_active` = 1";
								$result	= $db->GetRow($query);
								if ($result) {
									$PROCESSED["session"]["associated_course_ids"][] = $course_id;
								}
							}
						}
						
						/**
						 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
						 * This is actually accomplished after the event is inserted below.
						 */
						if ((isset($_POST["associated_session_faculty"]))) {
							$associated_session_faculty = explode(',',$_POST["associated_session_faculty"]);
							foreach($associated_session_faculty as $contact_order => $proxy_id) {
								if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
									$PROCESSED["session"]["associated_session_faculty"][(int) $contact_order] = $proxy_id;
								}
							}
						}
						
						$PROCESSED["session"]["parent_id"] = $EVENT_ID;
						$PROCESSED["session"]["course_id"] = $PROCESSED["course_id"];
						$PROCESSED["session"]["event_phase"] = $PROCESSED["event_phase"];
						$PROCESSED["session"]["event_finish"] = $PROCESSED["session"]["event_start"];
						$PROCESSED["session"]["event_duration"] = 0;
						foreach($PROCESSED["event_types"] as $event_type) {
							$PROCESSED["session"]["event_finish"] += $event_type[1]*60;
							$PROCESSED["session"]["event_duration"] += $event_type[1];
						}
					}
					

					/**
					 * Event Description
					 */
					if ((isset($_POST["session_description"])) && (clean_input($_POST["session_description"], array("notags", "nows")))) {
						$PROCESSED["session"]["event_description"] = clean_input($_POST["session_description"], array("allowedtags"));
					}
					
					/**
					 * Include Parent Event's Description
					 */
					if ((isset($_POST["include_parent_description"])) && (clean_input($_POST["include_parent_description"], array("int"))) == 1) {
						$PROCESSED["session"]["include_parent_description"] = 1;
					} else {
						$PROCESSED["session"]["include_parent_description"] = 0;
					}
					/**
					 * Teacher's Message
					 */
					if ((isset($_POST["session_message"])) && (clean_input($_POST["session_message"], array("notags", "nows")))) {
						$PROCESSED["session"]["event_message"] = clean_input($_POST["session_message"], array("allowedtags"));
					}
					
					/**
					 * Include Parent Event's Teacher's Message
					 */
					if ((isset($_POST["include_parent_message"])) && (clean_input($_POST["include_parent_message"], array("int"))) == 1) {
						$PROCESSED["session"]["include_parent_message"] = 1;
					} else {
						$PROCESSED["session"]["include_parent_message"] = 0;
					}
						
					$PROCESSED["session"]["updated_date"] = time();
					$PROCESSED["session"]["updated_by"] = $_SESSION["details"]["id"];
					
					$event_info["sessions"][] = $PROCESSED["session"];
					
					if (!$ERROR) {
						$session_updated = false;
						if (isset($PROCESSED["session"]["event_id"]) && $PROCESSED["session"]["event_id"]) {
							if ($db->AutoExecute("events", $PROCESSED["session"], "UPDATE", "`event_id` = ".$db->qstr($PROCESSED["session"]["event_id"]))) {
								$session_updated = true;
							} else {
								$session_updated = false;
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this session in the system. The system administrator was informed of this error; please try again later.";
			
								application_log("error", "There was an error updating event_id [".$PROCESSED["session"]["event_id"]."]. Database said: ".$db->ErrorMsg());
							}
						} else {
							$PROCESSED["session"]["event_id"] = 0;
							if ($db->AutoExecute("events", $PROCESSED["session"], "INSERT")) {
								$PROCESSED["session"]["event_id"] =  $db->Insert_Id();
								$session_updated = true;
							} else {
								$session_updated = false;
								$ERROR++;
								$ERRORSTR[] = "There was a problem creating this session in the system. The system administrator was informed of this error; please try again later.";
			
								application_log("error", "There was an error inserting a new event. Database said: ".$db->ErrorMsg());
							}
						}
						if ($session_updated == true && $ENTRADA_ACL->amIAllowed(new EventResource($session_id, $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
							$query = "DELETE FROM `event_audience` WHERE `event_id` = ".$db->qstr($PROCESSED["session"]["event_id"]);
							if ($db->Execute($query)) {
								$query = "DELETE FROM `event_eventtypes` WHERE `event_id` = ".$db->qstr($PROCESSED["session"]["event_id"]);
								if ($db->Execute($query)) {
									foreach($PROCESSED["event_types"] as $event_type) {
										if (!$db->AutoExecute("event_eventtypes", array("event_id" => $PROCESSED["session"]["event_id"], "eventtype_id" => $event_type[0], "duration" => $event_type[1]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to save the selected <strong>Event Type</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
											application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								} else {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to update the selected <strong>Event Types</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
									application_log("error", "Unable to delete any eventtype records while editing an event. Database said: ".$db->ErrorMsg());
								}
		
								if (count($PROCESSED["session"]["associated_course_ids"])) {
									if ($PROCESSED["session"]["associated_course_ids"]) {
										if (!$db->AutoExecute("event_audience", array("event_id" => $PROCESSED["session"]["event_id"], "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Course List</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
											application_log("error", "Unable to insert a new event_audience, course_id record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}
								if (count($PROCESSED["session"]["associated_group_ids"])) {
									foreach($PROCESSED["session"]["associated_group_ids"] as $group_id) {
										if (!$db->AutoExecute("event_audience", array("event_id" => $PROCESSED["session"]["event_id"], "audience_type" => "group_id", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Group</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
											application_log("error", "Unable to insert a new event_audience, group_id record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}
								if (count($PROCESSED["session"]["associated_proxy_ids"])) {
									foreach($PROCESSED["session"]["associated_proxy_ids"] as $proxy_id) {
										if (!$db->AutoExecute("event_audience", array("event_id" => $PROCESSED["session"]["event_id"], "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
		
											application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}
								if (!count($PROCESSED["session"]["associated_proxy_ids"]) && !count($PROCESSED["session"]["associated_group_ids"])) {
									application_log("error", "No audience added for event_id [".$PROCESSED["session"]["event_id"]."].");
								}
							} else {
								application_log("error", "Unable to delete audience details from event_audience table during an edit. Database said: ".$db->ErrorMsg());
							}
		
							/**
							 * If there are faculty associated with this event, add them
							 * to the event_contacts table.
							 */
							$query = "DELETE FROM `event_contacts` WHERE `event_id` = ".$db->qstr($PROCESSED["session"]["event_id"]);
							if ($db->Execute($query)) {
								if ((is_array($PROCESSED["session"]["associated_session_faculty"])) && (count($PROCESSED["session"]["associated_session_faculty"]))) {
									foreach($PROCESSED["session"]["associated_session_faculty"] as $contact_order => $proxy_id) {
										if (!$db->AutoExecute("event_contacts", array("event_id" => $PROCESSED["session"]["event_id"], "proxy_id" => $proxy_id, "contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
											$ERROR++;
											$ERRORSTR[] = "There was an error while trying to attach an <strong>Associated Faculty</strong> to this session.<br /><br />The system administrator was informed of this error; please try again later.";
		
											application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
										}
									}
								}
							}
		
							$SUCCESS++;
							
							if (!$ERROR) {
								$session_title = $db->GetOne("SELECT CONCAT(a.`event_title`, ' - ', b.`event_title`) FROM `events` AS a JOIN `events` AS b ON a.`event_id` = b.`parent_id` WHERE b.`event_id` = ".$db->qstr($session_id));
								$SUCCESSSTR[] = "You have successfully edited <strong>".html_encode($session_title)."</strong> in the system.<br /><br />".$msg;
								$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
							}
		
							application_log("success", "Event session [".$PROCESSED["session"]["event_id"]."] has been modified.");
						}
					}
				} else {
					$PROCESSED["event_finish"] = $PROCESSED["event_start"];
					$PROCESSED["event_duration"] = 0;
					foreach($PROCESSED["event_types"] as $event_type) {
						$PROCESSED["event_finish"] += $event_type[1]*60;
						$PROCESSED["event_duration"] += $event_type[1];
					}
				}
				$PROCESSED["updated_date"] = time();
				$PROCESSED["updated_by"] = $_SESSION["details"]["id"];

				$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0][0];
				if ($db->AutoExecute("events", $PROCESSED, "UPDATE", "`event_id` = ".$db->qstr($EVENT_ID))) {
					if (isset($event_info["course_id"]) && $PROCESSED["course_id"] != $event_info["course_id"]) {
						$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr($EVENT_ID);
						if ($child_events = $db->GetAll($query)) {
							foreach ($child_events as $child_event) {
								$db->AutoExecute("event_audience", array("audience_value" => $PROCESSED["course_id"]), "UPDATE", "`event_id` = ".$db->qstr($child_event["event_id"])." AND `audience_type` = 'course_id'");
							}
						}
					}
					$query = "DELETE FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID);
					if ($db->Execute($query)) {
						$query = "DELETE FROM `event_eventtypes` WHERE `event_id` = ".$db->qstr($EVENT_ID);
						if ($db->Execute($query)) {
							foreach($PROCESSED["event_types"] as $event_type) {
								if (!$db->AutoExecute("event_eventtypes", array("event_id" => $EVENT_ID, "eventtype_id" => $event_type[0], "duration" => $event_type[1]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to save the selected <strong>Event Type</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new event_eventtype record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "There was an error while trying to update the selected <strong>Event Types</strong> for this event.<br /><br />The system administrator was informed of this error; please try again later.";

							application_log("error", "Unable to delete any eventtype records while editing an event. Database said: ".$db->ErrorMsg());
						}

						if (count($PROCESSED["associated_course_ids"])) {
							if($PROCESSED["associated_course_ids"]) {
								if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "course_id", "audience_value" => (int) $course_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Course List</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new event_audience, group_id record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						}
						if (count($PROCESSED["associated_group_ids"])) {
							foreach($PROCESSED["associated_group_ids"] as $group_id) {
								if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "group_id", "audience_value" => (int) $group_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Group</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new event_audience, group_id record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						}
						if (count($PROCESSED["associated_proxy_ids"])) {
							foreach($PROCESSED["associated_proxy_ids"] as $proxy_id) {
								if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						}
						if (!count($PROCESSED["associated_proxy_ids"]) && !count($PROCESSED["associated_group_ids"]) && !count($PROCESSED["session"]["associated_proxy_ids"]) && !count($PROCESSED["session"]["associated_group_ids"])) {
							application_log("error", "No audience added for event_id [".$EVENT_ID."].");
						}
					} else {
						application_log("error", "Unable to delete audience details from event_audience table during an edit. Database said: ".$db->ErrorMsg());
					}

					/**
					 * If there are faculty associated with this event, add them
					 * to the event_contacts table.
					 */
					$query = "DELETE FROM `event_contacts` WHERE `event_id` = ".$db->qstr($EVENT_ID);
					if ($db->Execute($query)) {
						if ((is_array($PROCESSED["associated_faculty"])) && (count($PROCESSED["associated_faculty"]))) {
							foreach($PROCESSED["associated_faculty"] as $contact_order => $proxy_id) {
								if (!$db->AutoExecute("event_contacts", array("event_id" => $EVENT_ID, "proxy_id" => $proxy_id, "contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach an <strong>Associated Faculty</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
								}
							}
						}
					}

					switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
						case "manage" :
							$url	= ENTRADA_URL."/admin/events?section=manage&id=".$EVENT_ID;
							$msg	= "You will now be redirected to the event management page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "new" :
							$url	= ENTRADA_URL."/admin/events?section=manage";
							$msg	= "You will now be redirected to add a new event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
						case "index" :
						default :
							$url	= ENTRADA_URL."/admin/events";
							$msg	= "You will now be redirected to the event index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
						break;
					}

					$SUCCESS++;
					
					if (!$ERROR) {
						$SUCCESSSTR[] = "You have successfully edited <strong>".html_encode($event_info["event_title"])."</strong> in the system.<br /><br />".$msg;
						$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
					} else {
						$STEP = 1;
					}

					application_log("success", "Event [".$EVENT_ID."] has been modified.");
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem updating this event in the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error updating event_id [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
				}
			} else {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			$PROCESSED	= $event_info;
		break;
	}

	// Display Content
	switch($STEP) {
		case 2 :
			display_status_messages();
		break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
			}
			$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
			$HEAD[]		= "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
			$HEAD[]		= "<link href=\"".ENTRADA_URL."/css/tree.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
			$HEAD[]		= "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
			$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
			$HEAD[]		= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/scriptaculous/tree.js\"></script>\n";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			/**
			 * Compiles the full list of faculty members.
			 */
			$FACULTY_LIST = array();
			$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`
						FROM `".AUTH_DATABASE."`.`user_data` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
						ON b.`user_id` = a.`id`
						WHERE b.`app_id` = '".AUTH_APP_ID."'
						AND (b.`group` = 'faculty' OR (b.`group` = 'resident' AND b.`role` = 'lecturer'))
						ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $result) {
					$FACULTY_LIST[$result["proxy_id"]] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"], 'organisation_id'=>$result['organisation_id']);
				}
			}
			/**
			 * Compiles the list of students.
			 */
			$STUDENT_LIST = array();
			$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`organisation_id`
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
			/**
			 * Compiles the list of groups.
			 */
			$GROUP_LIST = array();
			$query = "	SELECT *
						FROM `groups`
						WHERE `group_active` = '1'
						ORDER BY `group_name`";
			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $result) {
					$GROUP_LIST[$result["group_id"]] = $result;
				}
			}
			/**
			 * Add existing event type segments to the processed array.
			 */
			$query = "	SELECT *
						FROM `event_eventtypes` AS `types`
						LEFT JOIN `events_lu_eventtypes` AS `lu_types`
						ON `lu_types`.`eventtype_id` = `types`.`eventtype_id`
						WHERE `event_id` = ".$db->qstr($EVENT_ID)."
						ORDER BY `types`.`eeventtype_id` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach ($results as $contact_order => $result) {
					$PROCESSED["event_types"][] = array($result["eventtype_id"], $result["duration"], $result["eventtype_title"]);
				}
			}

			/**
			 * Add any existing associated faculty from the event_contacts table
			 * into the $PROCESSED["associated_faculty"] array.
			 */
			$query = "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($EVENT_ID)." ORDER BY `contact_order` ASC";
			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $contact_order => $result) {
					$PROCESSED["associated_faculty"][(int) $contact_order] = $result["proxy_id"];
				}
			}
			
			$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr((isset($event_info["sessions"]) && $event_info["sessions"] ? (isset($selected_session_id) && $selected_session_id ? $selected_session_id : $event_info["sessions"][0]["event_id"]) : $EVENT_ID));
			$results = $db->GetAll($query);
			if ($results) {
				/**
				 * Set the audience_type.
				 */
				$PROCESSED["event_audience_type"] = $results[0]["audience_type"];

				foreach($results as $result) {
					if ($result["audience_type"] == $PROCESSED["event_audience_type"]) {
						switch($result["audience_type"]) {
							case "course_id" :
								$PROCESSED["associated_course_ids"][] = (int) $result["audience_value"];
							break;
							case "group_id" :
								$PROCESSED["associated_group_ids"][] = (int) $result["audience_value"];
							break;
							case "proxy_id" :
								$PROCESSED["associated_proxy_ids"][] = (int) $result["audience_value"];
							break;
							case "grad_year" :
								$query = "SELECT `group_id` FROM `groups` WHERE `group_name` = 'School of Medicine: Class of ".((int)$result["audience_value"])."'";
								$group_id = $db->GetOne($query);
								if ($group_id) {
									$PROCESSED["associated_group_ids"][] = (int) $group_id;
								}
							break;
						}
					}
				}
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/events?section=manage&amp;step=2<?php echo (isset($EVENT_ID) && $EVENT_ID ? "&amp;id=".$EVENT_ID : ""); ?>" method="post" id="addEventForm">
				<input type="hidden" value="<?php echo count($PROCESSED["sessions"]); ?>" name="sessions_count" id="sessions_count" />
				<input type="hidden" value="<?php echo $EVENT_ID; ?>" name="event_id" id="event_id" />
				<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Event">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 20%" />
						<col style="width: 77%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3" style="text-align: right; padding-top: 5px">
								<span class="content-small">After saving:</span>
								<select id="post_action" name="post_action">
									<option value="manage"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "manage")) ? " selected=\"selected\"" : ""); ?>>Return to the event</option>
									<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another event</option>
									<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to event index</option>
								</select>
								
								<input type="submit" value="Save" />
							</td>
						</tr>
					</tfoot>
					<?php
					if (!$ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
					?>
						<tr>
							<td colspan="3"><h1 class="event-title"><?php echo html_encode($event_info["event_title"]); ?></h1></td>
						</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="3"><h2>Parent Event Details</h2></td>
					</tr>
					<?php
					if ($ERROR || $NOTICE || $SUCCESS) {
						?>
						<tr>
							<td>&nbsp;</td>
							<td colspan="2">
							<?php
							if ($SUCCESS) {
								fade_element("out", "display-success-box");
								echo display_success();
							}
			
							if ($NOTICE) {
								echo display_notice();
							}
			
							if ($ERROR) {
								echo display_error();
							}
							?>
							</td>
						</tr>
						<?php
					}
					if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
					?>
					<tr>
						<td></td>
						<td><label for="event_title" class="form-required">Event Title</label></td>
						<td><input type="text" id="event_title" name="event_title" value="<?php echo html_encode($PROCESSED["event_title"]); ?>" maxlength="255" style="width: 95%" /></td>
					</tr>
					<tr>
						<td></td>
						<td><label for="course_id" class="form-required">Course</label></td>
						<td>
							<select id="course_id" name="course_id" style="width: 95%" onchange="updateCourse(this.selectedIndex)">
							<?php
							$query = "	SELECT * FROM `courses`
										WHERE `organisation_id` = ".$db->qstr($user->getActiveOrganisation())."
										AND `course_active` = '1'
										ORDER BY `course_name` ASC";

							$results = $db->GetAll($query);
							if ($results) {
								foreach($results as $result) {
									if ($event_info["course_id"] == $result["course_id"]) {
										$course_id = $event_info["course_id"];
										$course_name = $result["course_name"];
									}
									if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $result["course_id"], $user->getActiveOrganisation()), "create")) {
										echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
									}
								}
							}
							?>
							</select>
							<input type="hidden" id="event_course_id" value="<?php echo $course_id; ?>" />
							<input type="hidden" id="event_course_name" value="<?php echo $course_name; ?>" />
						</td>
					</tr>
					<?php
					} else {
						?>
						<tr>
							<td>&nbsp;</td>
							<td>Event Duration</td>
							<td><?php echo (($event_info["event_duration"]) ? $event_info["event_duration"]." minutes" : "To Be Announced"); ?></td>
						</tr>
						<?php
						if ($event_audience_type == "grad_year") {
							$query		= "	SELECT a.`event_id`, a.`event_title`, b.`audience_value` AS `event_grad_year`
											FROM `events` AS a
											LEFT JOIN `event_audience` AS b
											ON b.`event_id` = a.`event_id`
											JOIN `courses` AS c
											ON a.`course_id` = c.`course_id`
											AND c.`organisation_id` = ".$db->qstr($event_info["organisation_id"])."
											WHERE (a.`event_start` BETWEEN ".$db->qstr($event_info["event_start"])." AND ".$db->qstr(($event_info["event_finish"] - 1)).")
											AND a.`event_id` <> ".$db->qstr($event_info["event_id"])."
											AND b.`audience_type` = 'grad_year'
											AND b.`audience_value` = ".$db->qstr((int) $associated_grad_year)."
											ORDER BY a.`event_title` ASC";
							$results	= $db->GetAll($query);
							if ($results) {
								echo "<tr>\n";
								echo "	<td colspan=\"3\">&nbsp;</td>\n";
								echo "</tr>\n";
								echo "<tr>\n";
								echo "	<td>&nbsp;</td>\n";
								echo "	<td style=\"vertical-align: top\">Overlapping Event".((count($results) != 1) ? "s" : "")."</td>\n";
								echo "	<td>\n";
								foreach ($results as $result) {
									echo "	<a href=\"".ENTRADA_URL."/admin/events?id=".$result["event_id"]."&section=content\">".html_encode($result["event_title"])."</a><br />\n";
								}
								echo "	</td>\n";
								echo "</tr>\n";
							}
						}
						?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top">Associated Faculty</td>
							<td>
								<?php
								$query		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, a.`contact_role`, b.`email`
												FROM `event_contacts` AS a
												LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
												ON b.`id` = a.`proxy_id`
												WHERE a.`event_id` = ".$db->qstr($event_info["event_id"])."
												AND b.`id` IS NOT NULL
												ORDER BY a.`contact_order` ASC";
								$results	= $db->GetAll($query);
								if ($results) {
									foreach ($results as $key => $result) {
										echo "<a href=\"mailto:".html_encode($result["email"])."\">".html_encode($result["fullname"])."</a> - ".(($result["contact_role"] == "ta")?"Teacher's Assistant":html_encode(ucwords($result["contact_role"])))."<br />\n";
									}
								} else {
									echo "To Be Announced";
								}
								?>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<?php
						if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")) {
							echo "	<tr>\n";
							echo "	<td>&nbsp;</td>\n";
							echo "		<td>Phase / Term</td>\n";
							echo "		<td>".strtoupper($event_info["event_phase"])."</td>\n";
							echo "	</tr>\n";
							echo "	<tr>\n";
							echo "	<td>&nbsp;</td>\n";
							echo "		<td>Course</td>\n";
							echo "		<td>".(($event_info["course_id"]) ? "<a href=\"".ENTRADA_URL."/courses?id=".$event_info["course_id"]."\">".course_name($event_info["course_id"], true, true)."</a>" : "Not Yet Filed")."</td>\n";
							echo "	</tr>\n";
							if (clean_input($event_info["event_description"], array("notags", "nows")) != "") {
								echo "	<tr>\n";
								echo "		<td colspan=\"3\">&nbsp;</td>\n";
								echo "	</tr>\n";
								echo "	<tr>\n";
								echo "		<td>&nbsp;</td>\n";
								echo "		<td colspan=\"2\">\n";
								echo "			<h3>Event Description</h3>\n";
								echo			trim(strip_selected_tags($event_info["event_description"], array("font")));
								echo "		</td>\n";
								echo "	</tr>\n";
							}
		
							if (clean_input($event_info["event_message"], array("notags", "nows")) != "") {
								echo "	<tr>\n";
								echo "		<td colspan=\"3\">&nbsp;</td>\n";
								echo "	</tr>\n";
								echo "	<tr>\n";
								echo "		<td>&nbsp;</td>\n";
								echo "		<td colspan=\"2\">\n";
								echo "			<h3>Teacher's Message</h3>\n";
								echo			trim(strip_selected_tags($event_info["event_message"], array("font")));
								echo "		</td>\n";
								echo "	</tr>\n";
							}
						}
					}
					if ($ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")) {
					?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">Event Description</td>
						<td>
							<textarea id="event_description" name="event_description" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_description"], array("font")))); ?></textarea>
						</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							Teacher's Message
							<div class="content-small" style="margin-top: 10px">
								<strong>Note:</strong> You can use this to provide your learners with instructions or information they need for this class.
							</div>
						</td>
						<td>
							<textarea id="event_message" name="event_message" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_message"], array("font")))); ?></textarea>
						</td>
					</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php
					if ($ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
					?>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Event Types</label></td>
						<td>
							<select id="eventtype_ids" name="eventtype_ids">
								<option id="-1"> -- Pick a type to add -- </option>
								<?php
								$query		= "SELECT * FROM `events_lu_eventtypes` WHERE `eventtype_active` = '1' ORDER BY `eventtype_order` ASC";
								$results	= $db->GetAll($query);
								if ($results) {
									$event_types = array();
									foreach($results as $result) {
										$title = html_encode($result["eventtype_title"]);
										echo "<option value=\"".$result["eventtype_id"]."\">".$title."</option>";
									}
								}
								?>
							</select>
							<div id="duration_notice" class="content-small" >Use the list above to select the different components of this event. When you select one, it will appear here and you can change the order and duration.</div>
							<ol id="duration_container" class="sortableList" style="display: none;">
								<?php
								foreach($PROCESSED["event_types"] as $eventtype) {
									echo "<li id=\"type_".$eventtype[0]."\" class=\"\">".$eventtype[2]."
										<a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\">
											<img src=\"".ENTRADA_URL."/images/action-delete.gif\">
										</a>
										<span class=\"duration_segment_container\">
											Duration: <input class=\"duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".$eventtype[1]."\"> minutes
										</span>
									</li>";
								}
								?>
							</ol>
							<div id="total_duration" class="content-small">Total time: 0 minutes.</div>
							<input id="eventtype_duration_order" name="eventtype_duration_order" style="display: none;">
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td><label for="event_phase" class="form-nrequired">Term / Phase</label></td>
						<td>
							<select id="event_phase" name="event_phase" style="width: 203px">
								<option value="1"<?php echo (($PROCESSED["event_phase"] == "1") ? " selected=\"selected\"" : "") ?>>Term 1</option>
								<option value="2"<?php echo (($PROCESSED["event_phase"] == "2") ? " selected=\"selected\"" : "") ?>>Term 2</option>
								<option value="T3"<?php echo (($PROCESSED["event_phase"] == "T3") ? " selected=\"selected\"" : "") ?>>Term 3</option>
								<option value="T4"<?php echo (($PROCESSED["event_phase"] == "T4") ? " selected=\"selected\"" : "") ?>>Term 4</option>
								<option value="2A"<?php echo (($PROCESSED["event_phase"] == "2A") ? " selected=\"selected\"" : "") ?>>Phase 2A</option>
								<option value="2B"<?php echo (($PROCESSED["event_phase"] == "2B") ? " selected=\"selected\"" : "") ?>>Phase 2B</option>
								<option value="2C"<?php echo (($PROCESSED["event_phase"] == "2C") ? " selected=\"selected\"" : "") ?>>Phase 2C</option>
								<option value="2E"<?php echo (($PROCESSED["event_phase"] == "2E") ? " selected=\"selected\"" : "") ?>>Phase 2E</option>
								<option value="3"<?php echo (($PROCESSED["event_phase"] == "3") ? " selected=\"selected\"" : "") ?>>Phase 3</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Associated Faculty</label></td>
						<td>
							<input type="text" id="faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
							<?php
							$ONLOAD[] = "faculty_list = new AutoCompleteList({ type: 'faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
							?>
							<div class="autocomplete" id="faculty_name_auto_complete"></div>
							<input type="hidden" id="associated_faculty" name="associated_faculty" />
							<input type="button" class="button-sm" id="add_associated_faculty" value="Add" style="vertical-align: middle" />
							<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="faculty_list" class="menu" style="margin-top: 15px">
								<?php
								if (is_array($PROCESSED["associated_faculty"]) && count($PROCESSED["associated_faculty"])) {
									foreach ($PROCESSED["associated_faculty"] as $faculty) {
										if ((array_key_exists($faculty, $FACULTY_LIST)) && is_array($FACULTY_LIST[$faculty])) {
											?>
											<li class="community" id="faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="faculty_list.removeItem('<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
											<?php
										}
									}
								}
								?>
							</ul>
							<input type="hidden" id="faculty_ref" name="faculty_ref" value="" />
							<input type="hidden" id="faculty_id" name="faculty_id" value="" />
						</td>
					</tr>
					<?php
					}
					?>
					<tr>
						<td colspan="3">
							<h2>Sessions</h2>
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;
						</td>
						<td colspan="2">
							<div id="session-notices"></div>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<?php
							$count = 0;
							$page_count = 1;
							foreach ($event_info["sessions"] as $key => $session) {
								$count++;
								if ($count > 15) {
									$count = 1;
									$page_count++;
								}
								if (!isset($selected_session_id) && $key == 0 || $session["event_id"] == $selected_session_id) {
									$chosen_page = $page_count;
								}
							}
							?>
							<span class="content-small">Page <span id="current-page-text"><?php echo $chosen_page; ?></span> of <div id="max-page-text" style="display: inline;"><?php echo $page_count; ?></div></span>
							<div style="width: 100%;">
								<div class="session-pane-left">
									<div style="clear: both"></div>
									<div id="session-lists">
										<div class="session-list" id="page-1" style="width: 100%;<?php echo ($chosen_page == 1 || !isset($event_info["sessions"]) || !count($event_info["sessions"]) ? "" : " display: none;"); ?>">
										<?php 
										$count = 0;
										$page_count = 1;
										if (isset($event_info["sessions"]) && count($event_info["sessions"])) {
											if ($session_id === 0) {
												$selected_session_id = 0;
											}
											?>
											<input type="hidden" id="session-count" name="event_children" value="<?php echo (int)count($event_info["sessions"]); ?>" />		
											<?php
											$selected = false;
											$selected_id = 0;
											foreach ($event_info["sessions"] as $key => $result) {
												if ((!isset($selected_session_id) && !$selected && ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update') || $ENTRADA_ACL->amIAllowed(new EventContentResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update"))) || $result["event_id"] == $selected_session_id) {
													$selected = true;
													$selected_id = $result["event_id"];
													echo "<input type=\"hidden\" value=\"".$result["event_id"]."\" id=\"current-session\"  name=\"current_session\" />";
												}
												$count++;
												if ($count > 15) {
													$count = 1;
													$page_count++;
													echo "</div>\n";
													echo "<div class=\"session-list\"".($page_count != $chosen_page ? " style=\"display: none;\"" : "")." id=\"page-".$page_count."\">\n";
												}
												if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update') || $ENTRADA_ACL->amIAllowed(new EventContentResource($result["event_id"], $result["course_id"], $result["organisation_id"]), "update")) {
													?>
													<div id="session-line-<?php echo $result["event_id"]; ?>" class="event-session enabled<?php echo $selected_id == $result["event_id"] ? " selected" : ""; ?>">
														<div id="session-<?php echo $result["event_id"]; ?>" onclick="loadSession(<?php echo $result["event_id"]; ?>)" class="session-entry">
															<?php
															echo limit_chars($result["event_title"], 21);
															?>
														</div>
														<input id="session-name-<?php echo $result["event_id"]; ?>" value="<?php echo $result["event_title"]; ?>" onchange="saveSessionName()" type="text" style="width: 95%; background-color: #EEEEEE; display: none;" />
													</div>
													<?php
												} else {?>
													<div id="session-line-<?php echo $result["event_id"]; ?>" class="event-session disabled">
														<div id="session-<?php echo $result["event_id"]; ?>" class="session-entry">
															<?php
															echo limit_chars($result["event_title"], 21);
															?>
														</div>
													</div>
													<?php
												}
											}
											?>
											<?php
										} else {
											?>
											<input type="hidden" id="current-page" name="current_pages" value="1" />		
											<input type="hidden" value="0" id="current-session" name="current_session" />
											<input type="hidden" id="session-count" name="event_children" value="1" />
											<div id="session-line-0" class="event-session enabled selected">
												<div id="session-0" onclick="loadSession(0)" class="session-entry">
													Session 1
												</div>
												<input id="session-name-0" value="Session 1" onchange="saveSessionName()" type="text" style="width: 95%; background-color: #EEEEEE; display: none;" />
											</div>
											<?php
										}
										?>
										</div>
									</div>
									<?php 
									if (count($event_info["sessions"]) > 15) {
										echo "<div id=\"pagination-buttons\">\n";
										echo "<div class=\"large-button\" onclick=\"firstPage()\">&lt;&lt;</div>";
										echo "<div class=\"small-button\" onclick=\"prevPage()\">&lt;</div>";
										echo "<div class=\"small-button\" onclick=\"nextPage()\">&gt;</div>";
										echo "<div style=\"text-align: right;\" class=\"large-button\" onclick=\"lastPage()\">&gt;&gt;</div>";
										echo "</div>";
									}
									?>
									<div style="height: 15px; border-top: 1px solid #CCCCCC; position: absolute; bottom: 20px; width: 100%;">
										<div class="session-button<?php echo ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'create') ? "\" onclick=\"addSession()\" title=\"Add a new session" : " disabled\" title=\"[Disabled] Add a new session"); ?>">
											+
										</div>
										<div class="session-button remove-button<?php echo ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'delete') ? "\" onclick=\"removeSession()\" title=\"Remove the selected session" : " disabled\" title=\"[Disabled] Remove the selected session"); ?>">
											-
										</div>
										<div class="session-button edit-button<?php echo ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update') ? "\" onclick=\"renameSession()\" title=\"Rename the selected session" : " disabled\" title=\"[Disabled] Rename the selected session"); ?>">
											<img src="<?php echo ENTRADA_RELATIVE; ?>/images/action-edit.gif" />
										</div>
									</div>
								</div>
								<div class="session-pane-right" id="session">
									<div style="position: relative;">
										<?php
								        $query 	= "	SELECT * FROM `groups`
								        		WHERE `group_active` = 1
								        		ORDER BY `group_name`";
								        $group_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
								        if ($group_results) {
								            foreach ($group_results as $r) {
												$checked = (isset($PROCESSED["associated_group_ids"]) && count($PROCESSED["associated_group_ids"]) && array_search($r["group_id"], $PROCESSED["associated_group_ids"]) !== false ? "checked=\"checked\"" : "");
								                $groups[$r["group_id"]] = array('text' => $r['group_name'], 'value' => 'group_'.$r['group_id'], 'checked' => $checked);
								            }
								            echo lp_multiple_select_popup('groups', $groups, array('title'=>'Select Groups:', 'cancel_text'=>'Close', 'cancel'=>true, 'class'=>'audience_dialog'));
								        }
										
								        $query 	= "	SELECT CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`id` AS `proxy_id`, b.`role` FROM `".AUTH_DATABASE."`.`user_data` AS a
								        		JOIN `".AUTH_DATABASE."`.`user_access` AS b
								        		ON a.`id` = b.`user_id`
								        		AND b.`app_id` IN (".AUTH_APP_IDS_STRING.")
								        		WHERE b.`group` = 'student'
												AND b.`role` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
								        		AND b.`account_active` = 'true'
								        		AND (b.`access_starts` <= ".$db->qstr(time())." OR b.`access_starts` = 0)
								        		AND (b.`access_expires` >= ".$db->qstr(time())." OR b.`access_expires` = 0)";
								        $student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
								        if ($student_results) {
								            foreach ($student_results as $r) {
							                    $checked = (isset($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"]) && array_search($r["proxy_id"], $PROCESSED["associated_proxy_ids"]) !== false ? "checked=\"checked\"" : "");
								
								                $students[$r["role"]]['options'][] = array('text' => $r['fullname'], 'value' => 'student_'.$r['proxy_id'], 'checked' => $checked);
								            }
								            echo lp_multiple_select_popup('students', $students, array('title'=>'Select Students:', 'cancel_text'=>'Close', 'cancel'=>true, 'class'=>'audience_dialog'));
								        }
										?>
									</div>
									<?php
									if (isset($event_info["sessions"]) && count($event_info["sessions"])) {
										if ($session_id === 0) {
											$selected_session_id = 0;
										}
										foreach ($event_info["sessions"] as $key => $result) {
											if ((isset($selected_session_id) && $result["event_id"] == $selected_session_id) || (!isset($selected_session_id) && (($session_id !== 0 && $key == 0) || ($session_id === 0 && !isset($result["event_id"]))))) {
												if ($selected_session_id !== 0) {
													/**
													 * Add any existing associated faculty from the event_contacts table
													 * into the $PROCESSED["associated_faculty"] array.
													 */
													$result["associated_session_faculty"] = array();
													$query = "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($result["event_id"])." ORDER BY `contact_order` ASC";
													$faculty_results = $db->GetAll($query);
													if ($faculty_results) {$result["associated_session_faculty"] = array();
														foreach($faculty_results as $contact_order => $faculty) {
															$result["associated_session_faculty"][(int) $contact_order] = $faculty["proxy_id"];
														}
													}
												}
												?>
												<input type="hidden" value="<?php echo $result["event_title"]; ?>" id="session_title" name="session_title" />
												<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Session Information">
													<colgroup>
														<col style="width: 5%" />
														<col style="width: 20%" />
														<col style="width: 75%" />
													</colgroup>
													<?php
													if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
														echo generate_calendar("event_start", "Date and Time", true, $result["event_start"]);
													?>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td></td>
														<td><label for="event_location" class="form-nrequired">Session Location</label></td>
														<td><input type="text" id="event_location" name="event_location" value="<?php echo $result["event_location"]; ?>" maxlength="255" style="width: 203px" /></td>
													</tr>
													<?php
													} else {
													?>
													<tr>
														<td colspan="3"><h3>Session Details</h3></td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td>&nbsp;</td>
														<td>Event Date &amp; Time</td>
														<td><?php echo date(DEFAULT_DATE_FORMAT, $result["event_start"]); ?></td>
													</tr>
													<tr>
														<td>&nbsp;</td>
														<td>Event Location</td>
														<td><?php echo (($result["event_location"]) ? $result["event_location"] : "To Be Announced"); ?></td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td>&nbsp;</td>
														<td style="vertical-align: top">Associated Faculty</td>
														<td>
															<?php
															$query		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, a.`contact_role`, b.`email`
																			FROM `event_contacts` AS a
																			LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
																			ON b.`id` = a.`proxy_id`
																			WHERE a.`event_id` = ".$db->qstr($result["event_id"])."
																			AND b.`id` IS NOT NULL
																			ORDER BY a.`contact_order` ASC";
															$results	= $db->GetAll($query);
															if ($results) {
																foreach ($results as $key => $fresult) {
																	echo "<a href=\"mailto:".html_encode($fresult["email"])."\">".html_encode($fresult["fullname"])."</a> - ".(($fresult["contact_role"] == "ta")?"Teacher's Assistant":html_encode(ucwords($fresult["contact_role"])))."<br />\n";
																}
															} else {
																echo "To Be Announced";
															}
															?>
														</td>
													</tr>
													<?php
													}
													?>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td></td>
														<td style="vertical-align: top">Session Description</td>
														<td>
															<textarea id="session_description" name="session_description" style="width: 90%; height: 80px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($result["event_description"], array("font")))); ?></textarea>
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td><input type="checkbox" value="1" id="include_parent_description" name="include_parent_description"<?php echo (!isset($result["include_parent_description"]) || $result["include_parent_description"] ? " checked=\"checked\"" : "" ); ?> /></td>
														<td colspan="2">
															<label for="include_parent_description" class="form-nrequired">Include <strong>Event Description</strong> from parent event</label>
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td></td>
														<td style="vertical-align: top">
															Teacher's Message
															<div class="content-small" style="margin-top: 10px">
																<strong>Note:</strong> You can use this to provide your learners with instructions or information they need for this class.
															</div>
														</td>
														<td>
															<textarea id="session_message" name="session_message" style="width: 90%; height: 80px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($result["event_message"], array("font")))); ?></textarea>
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td><input type="checkbox" value="1" id="include_parent_message" name="include_parent_message"<?php echo (!isset($result["include_parent_message"]) || $result["include_parent_message"] ? " checked=\"checked\"" : "" ); ?> /></td>
														<td colspan="2">
															<label for="include_parent_message" class="form-nrequired">Include <strong>Teacher's Message</strong> from parent event</label>
														</td>
													</tr>
													<?php
													if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
													?>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td></td>
														<td style="vertical-align: top"><label for="session_faculty_name" class="form-nrequired">Associated Faculty</label></td>
														<td>
															<input type="text" id="session_faculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
															<div class="autocomplete" id="session_faculty_name_auto_complete"></div>
															<input type="hidden" id="associated_session_faculty" name="associated_session_faculty" />
															<input type="button" class="button-sm" id="add_associated_session_faculty" value="Add" style="vertical-align: middle" />
															<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
															<ul id="session_faculty_list" class="menu" style="margin-top: 15px">
																<?php
																$ONLOAD[] = "session_faculty_list = new AutoCompleteList({ type: 'session_faculty', url: '". ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE ."/images/action-delete.gif'})";
																if (is_array($result["associated_session_faculty"]) && count($result["associated_session_faculty"])) {
																	foreach ($result["associated_session_faculty"] as $faculty) {
																		if ((array_key_exists($faculty, $FACULTY_LIST)) && is_array($FACULTY_LIST[$faculty])) {
																			?>
																			<li class="community" id="session_faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="session_faculty_list.removeItem('<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
																			<?php
																		}
																	}
																}
																?>
															</ul>
															<input type="hidden" id="session_faculty_ref" name="session_faculty_ref" value="" />
															<input type="hidden" id="session_faculty_id" name="session_faculty_id" value="" />
														</td>
													</tr>
													<tr>
														<td colspan="3">&nbsp;</td>
													</tr>
													<tr>
														<td></td>
														<td><label for="audience_type" class="form-nrequired">Audience Type</label></td>
														<td>
															<?php
															$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($result["event_id"]);
															$audience_results = $db->GetAll($query);
															if ($audience_results) {
																/**
																 * Set the audience_type.
																 */
																$result["event_audience_type"] = $audience_results[0]["audience_type"];
																
																foreach($audience_results as $audience) {
																	switch($audience["audience_type"]) {
																		case "course_id" :
																			$result["associated_course_ids"][] = (int) $audience["audience_value"];
																		break;
																		case "group_id" :
																			$result["associated_group_ids"][] = (int) $audience["audience_value"];
																		break;
																		case "proxy_id" :
																			$result["associated_proxy_ids"][] = (int) $audience["audience_value"];
																		break;
																		case "grad_year" :
																			$query = "SELECT `group_id` FROM `groups` WHERE `group_name` = 'School of Medicine: Class of ".((int)$result["audience_value"])."'";
																			$group_id = $db->GetOne($query);
																			if ($group_id) {
																				$result["associated_group_ids"][] = (int) $group_id;
																			}
																		break;
																	}
																}
															}
															$group_ids_string = "";
															$student_ids_string = "";
															if (isset($PROCESSED["associated_course_ids"]) && $PROCESSED["associated_course_ids"]) {
																$course_audience_included = true;
															} else {
																$course_audience_included = false;
															}
															foreach ($PROCESSED["associated_group_ids"] as $group_id) {
																if ($group_ids_string) {
																	$group_ids_string .= ",group_".$group_id;
																} else {
																	$group_ids_string = "group_".$group_id; 
																}
															}
															foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
																if ($student_ids_string) {
																	$student_ids_string .= ",student_".$proxy_id;
																} else {
																	$student_ids_string = "student_".$proxy_id; 
																}
															}
															?>
															<input type="hidden" id="groups_audience_head" name="groups_session_audience" value="<?php echo $group_ids_string; ?>" />
															<input type="hidden" id="students_audience_head" name="students_session_audience" value="<?php echo $student_ids_string; ?>" />
															<input type="hidden" id="course_audience_head" name="course_session_audience" value="<?php echo $course_audience_included ? "1" : "0"; ?>" />
															<script type="text/javascript">
																var multiselect = [];
																var audience_id = 0;
																function showMultiSelect() {
																	$$('select_multiple_container').invoke('hide');
																	audience_id = $F('audience_select');
																	if (audience_id != 'course') {
																		$$('select_multiple_container').invoke('hide');
																		if (multiselect[audience_id]) {
																			$('audience_select').hide();
																			multiselect[audience_id].container.show();
																			multiselect[audience_id].container.down("input").activate();
																		} else {
																			if ($(audience_id+'_options')) {
																				$(audience_id+'_options').addClassName('multiselect-processed');
																				multiselect[audience_id] = new Control.SelectMultiple(audience_id+'_audience_head',audience_id+'_options',{
																					checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
																					nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
																					resize: audience_id+'_scroll',
																					afterCheck: function(element) {
																						if (element.checked) {
																							addAudience(element.id, audience_id);
																						} else {
																							removeAudience(element.id, audience_id);
																						}
																					}
																				});
								
																				$(audience_id+'_cancel').observe('click',function(event){
																					this.container.hide();
																					$('audience_select').show();
																					$('audience_select').options.selectedIndex = 0;
																					return false;
																				}.bindAsEventListener(multiselect[audience_id]));
								
																				$('audience_select').hide();
																				multiselect[audience_id].container.show();
																				multiselect[audience_id].container.down("input").activate();
																			}
																		}
																	} else if ($('audience_course') ==  null) {
																		if (!$('audience_course') || !$('audience_course').value) {
																			$('audience_list').innerHTML += '<li class="community" id="audience_course" style="cursor: move;">'+$('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" /></li>';
																			$$('#audience_list div').each(function (e) { e.hide(); });
																			Sortable.destroy('audience_list');
																			Sortable.create('audience_list');
																			$('audience_select').options.selectedIndex = 0;
																			$('course_audience_head').value = 1;
																		}
																	}
																	return false;
																}
															</script>
															<select id="audience_select" onchange="showMultiSelect()">
																<option value="">- Select Audience Type -</option>
																<option value="groups">Groups</option>
																<option value="students">Individual Students</option>
																<option value="course">Course List</option>
															</select>
														</td>
													</tr>
													<tr>
														<td colspan="2">
															&nbsp;
														</td>
														<td>
															<div style="position: relative; width: 60%">
																<ul class="menu" id="audience_list">
																	<?php
																	$ONLOAD[] = "Sortable.create('audience_list')";
																	if (is_array($result["associated_proxy_ids"]) && count($result["associated_proxy_ids"])) {
																		foreach ($result["associated_proxy_ids"] as $student) {
																			if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
																				?>
																				<li class="community" id="audience_student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>', 'students');" class="list-cancel-image" /></li>
																				<?php
																			}
																		}
																	}
																	if (is_array($result["associated_course_ids"]) && count($result["associated_course_ids"])) {
																		?>
																		<li class="community" id="audience_course" style="cursor: move;"><?php echo $course_name; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('course', 'course');" class="list-cancel-image" /></li>
																		<?php
																	}
																	if (is_array($result["associated_group_ids"]) && count($result["associated_group_ids"])) {
																		foreach ($result["associated_group_ids"] as $group) {
																			if ((array_key_exists($group, $GROUP_LIST)) && is_array($GROUP_LIST[$group])) {
																				?>
																				<li class="community" id="audience_group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>" style="cursor: move;"><?php echo $GROUP_LIST[$group]["group_name"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>', 'groups');" class="list-cancel-image" /></li>
																				<?php
																			}
																		}
																	}
																	
																	if (!(is_array($result["associated_proxy_ids"]) && count($result["associated_proxy_ids"])) && !(is_array($result["associated_group_ids"]) && count($result["associated_group_ids"])) && !(is_array($result["associated_course_ids"]) && count($result["associated_course_ids"]))) {
																		$NOTICE++;
																		$NOTICESTR[] = "No audience has been selected for this event.";
																		echo display_notice();
																	}
																	?>
																</ul>
															</div>
														</td>
													</tr>
													<?php
													}
													?>
												</table>
												<?php
											}
										}
									} else {
										?>
										<input type="hidden" value="Session 1" id="session_title" name="session_title" />
										<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Session Information">
											<colgroup>
												<col style="width: 5%" />
												<col style="width: 20%" />
												<col style="width: 75%" />
											</colgroup>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<tr>
												<td colspan="3">
													<div class="display-generic">This is currently the only session associated with this event. To create multiple distinct sessions, select the "+" button at the bottom-left of this pane. Once another session has been added, additional options will be available to be set at the session level, such as individual Session Descriptions, Teacher's Messages, and Associated Faculty lists.</div>
												</td>
											</tr>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<?php
											echo generate_calendar("event_start", "Date and Time", true, ((isset($PROCESSED["event_start"])) ? $PROCESSED["event_start"] : 0));
											?>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<tr>
												<td></td>
												<td><label for="event_location" class="form-nrequired">Session Location</label></td>
												<td><input type="text" id="event_location" name="event_location" value="<?php echo $PROCESSED["event_location"]; ?>" maxlength="255" style="width: 203px" /></td>
											</tr>
											<tr>
												<td colspan="3">&nbsp;</td>
											</tr>
											<tr>
												<td></td>
												<td><label for="audience_type" class="form-nrequired">Audience Type</label></td>
												<td>
												<?php
													$group_ids_string = "";
													$student_ids_string = "";
													if (isset($PROCESSED["associated_course_ids"]) && $PROCESSED["associated_course_ids"]) {
														$course_audience_included = true;
													} else {
														$course_audience_included = false;
													}
													foreach ($PROCESSED["associated_group_ids"] as $group_id) {
														if ($group_ids_string) {
															$group_ids_string .= ",group_".$group_id;
														} else {
															$group_ids_string = "group_".$group_id; 
														}
													}
													foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
														if ($student_ids_string) {
															$student_ids_string .= ",student_".$proxy_id;
														} else {
															$student_ids_string = "student_".$proxy_id; 
														}
													}
													?>
													<input type="hidden" id="groups_audience_head" name="groups_session_audience" value="<?php echo $group_ids_string; ?>" />
													<input type="hidden" id="students_audience_head" name="students_session_audience" value="<?php echo $student_ids_string; ?>" />
													<input type="hidden" id="course_audience_head" name="course_session_audience" value="<?php echo $course_audience_included ? "1" : "0"; ?>" />
													<select id="audience_select" onchange="showMultiSelect()">
														<option value="">- Select Audience Type -</option>
														<option value="groups">Groups</option>
														<option value="students">Individual Students</option>
														<option value="course">Course List</option>
													</select>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													&nbsp;
												</td>
												<td>
													<br/>
													<div style="position: relative; width: 60%">
														<ul class="menu" id="audience_list">
															<?php
															$ONLOAD[] = "Sortable.create('audience_list')";
															if (is_array($PROCESSED["associated_course_ids"]) && count($PROCESSED["associated_course_ids"])) {
																?>
																<li class="community" id="audience_course" style="cursor: move;"><?php echo $course_name; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('course', 'course');" class="list-cancel-image" /></li>
																<?php
															}
															if (is_array($PROCESSED["associated_group_ids"]) && count($PROCESSED["associated_group_ids"])) {
																foreach ($PROCESSED["associated_group_ids"] as $group) {
																	if ((array_key_exists($group, $GROUP_LIST)) && is_array($GROUP_LIST[$group])) {
																		?>
																		<li class="community" id="audience_group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>" style="cursor: move;"><?php echo $GROUP_LIST[$group]["group_name"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('group_<?php echo $GROUP_LIST[$group]["group_id"]; ?>', 'groups');" class="list-cancel-image" /></li>
																		<?php
																	}
																}
															}
															if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
																foreach ($PROCESSED["associated_proxy_ids"] as $student) {
																	if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
																		?>
																		<li class="community" id="audience_proxy_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience('student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>', 'students');" class="list-cancel-image" /></li>
																		<?php
																	}
																}
															}
															
															if (!(is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) && !(is_array($PROCESSED["associated_group_ids"]) && count($PROCESSED["associated_group_ids"])) && !(is_array($PROCESSED["associated_course_ids"]) && count($PROCESSED["associated_course_ids"]))) {
																$NOTICE++;
																$NOTICESTR[] = "No audience has been selected for this event.";
																echo display_notice();
															}
															?>
														</ul>
													</div>
												</td>
											</tr>
										</table>
										<?php
									}
									?>
									<script type="text/javascript">
										var multiselect = [];
										var audience_id = 0;
										function showMultiSelect() {
											audience_id = $F('audience_select');
											if (audience_id != 'course') {
												$$('select_multiple_container').invoke('hide');
												if (multiselect[audience_id]) {
													$('audience_select').hide();
													multiselect[audience_id].container.show();
													multiselect[audience_id].container.down("input").activate();
												} else {
													if ($(audience_id+'_options')) {
														$(audience_id+'_options').addClassName('multiselect-processed');
														multiselect[audience_id] = new Control.SelectMultiple(audience_id+'_audience_head',audience_id+'_options',{
															checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
															nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
															resize: audience_id+'_scroll',
															afterCheck: function(element) {
																if (element.checked) {
																	addAudience(element.id, audience_id);
																} else {
																	removeAudience(element.id, audience_id);
																}
															}
														});
		
														$(audience_id+'_cancel').observe('click',function(event){
															this.container.hide();
															$('audience_select').show();
															$('audience_select').options.selectedIndex = 0;
															return false;
														}.bindAsEventListener(multiselect[audience_id]));
		
														$('audience_select').hide();
														multiselect[audience_id].container.show();
														multiselect[audience_id].container.down("input").activate();
													}
												}
											} else if (!$('audience_course')) {
												if (!$('audience_course') || !$('audience_course').value) {
													$('audience_list').innerHTML += '<li class="community" id="audience_course" style="cursor: move;">'+$('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" /></li>';
													$$('#audience_list div').each(function (e) { e.hide(); });
													Sortable.destroy('audience_list');
													Sortable.create('audience_list');
													$('audience_select').options.selectedIndex = 0;
													$('course_audience_head').value = 1;
												}
											}
											return false;
										}
										
										function updateCourse(selectedIndex) {
											$('event_course_id').value = $('course_id').options[selectedIndex].value;
											$('event_course_name').value = $('course_id').options[selectedIndex].innerHTML;
											if ($('audience_course').innerHTML != $('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" />') {
												$('audience_course').innerHTML = $('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" />';
											}
										}
										
										function addAudience(element, audience_id) {
											if (!$('audience_'+element)) {
												$('audience_list').innerHTML += '<li class="community" id="audience_'+element+'" style="cursor: move;">'+$($(element).value+'_label').innerHTML+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\''+element+'\', \''+audience_id+'\');" class="list-cancel-image" /></li>';
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
											} else {
												$('course_audience_head').value = 0;
											}
											if ($(audience_id+'_audience_head').value == "") {
												$$('#audience_list div').each(function (e) { e.show(); });
											}
										}
										
										function addSession() {
											var session_id = $('current-session').value;
											new Ajax.Updater('session-notices' ,'<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
													{
														method: 'post',
														parameters: {
															'step': 2,
															'hide_controls': true,
															'event_start': 1,
															'parent_id' : <?php echo (int)$EVENT_ID; ?>,
															'event_id' : $('current-session').value,
															'event_title': $('session_title').value,
															'event_start_date': $('event_start_date').value,
															'event_start_hour': $('event_start_hour').value,
															'event_start_min': $('event_start_min').value,
															'event_location': $('event_location').value,
															'session_message': $('session_message').value,
															'session_description': $('session_description').value,
															'include_parent_message': $('include_parent_message').checked,
															'include_parent_description': $('include_parent_description').checked,
															'associated_session_faculty': ($('associated_session_faculty') ? $('associated_session_faculty').value : $('associated_faculty').value),
															'groups_session_audience': $('groups_audience_head').value,
															'students_session_audience': $('students_audience_head').value,
															'course_session_audience': $('course_audience_head').value,
															'new': 1
														},
														onComplete: function () {
															if ($('success').value == 1) {
																disableRTE();
																new Ajax.Updater({ success: 'session' }, '<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
																	{
																		method: 'post',
																		evalScripts: 'true',
																		parameters: {
																			'new': 1,
																			'event_start': 1,
																			'parent_id' : <?php echo (int)$EVENT_ID; ?>,
																			'event_start_date': $('event_start_date').value,
																			'event_start_hour': $('event_start_hour').value,
																			'event_start_min': $('event_start_min').value
																		},
																		onComplete: function () {
																			if ($('current-session').value == 0 && $('updated_session_id').value) {
																				$('current-session').value = $('updated_session_id').value;
																				$('session-line-0').id = 'session-line-'+$('updated_session_id').value;
																				$('session-line-'+$('updated_session_id').value).innerHTML = '<div id="session-'+ $('updated_session_id').value +'" onclick="loadSession('+ $('updated_session_id').value +')" class="session-entry">'+ $('session-0').innerHTML +'</div>';
																				$('updated_session_id').remove();
																			}
																			var session_id = $('current-session').value;
																			if (session_id == 0) {
																				$('session-line-'+session_id).removeClassName('selected');
																			} else {
																				$('current-session').value = 0;
																				$('session-line-'+session_id).removeClassName('selected');
																			}
																			var session_count = parseInt($('session-count').value);
																			session_count++;
																			$('session-count').value = session_count;
																			if (session_count % 15 == 1) {
																				document.getElementById('max-page-text').innerHTML = parseInt($('max-page-text').innerHTML) + 1;
																				document.getElementById('session-lists').innerHTML += '<div class="session-list" id="page-'+$('max-page-text').innerHTML+'" style="width: 100%; display: none;"></div>';
																			}
																			var page_count = document.getElementById('max-page-text').innerHTML;
																			document.getElementById('page-'+page_count).innerHTML += '<div id="session-line-0" class="event-session selected"><div id="session-0" onclick="loadSession(0)" class="session-entry"> Session '+ session_count +' </div></div>';
																			lastPage();
																			enableRTE();
																			session_faculty_list = new AutoCompleteList({ type: 'session_faculty', url: '<?php echo ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE; ?>/images/action-delete.gif'});
																		},
																		onCreate: function () {
																			$('session').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
																		}
																	}
																);
															}
														}
													}
												);
										}
										
										function disableRTE () {
											if(tinyMCE.getInstanceById('session_description')) {
												tinyMCE.execCommand('mceRemoveControl', false, 'session_description');
											}
											if(tinyMCE.getInstanceById('session_message')) {
												tinyMCE.execCommand('mceRemoveControl', false, 'session_message');
											}
										}
										
										function enableRTE () {
											if(!tinyMCE.getInstanceById('session_description')) {
												tinyMCE.execCommand('mceAddControl', false, 'session_description');
											}
											
											if(!tinyMCE.getInstanceById('session_message')) {
												tinyMCE.execCommand('mceAddControl', false, 'session_message');
											}
										}
										
										function renameSession() {
											var session_id = $('current-session').value;
											$('session-' + session_id).hide();
											$('session-name-' + session_id).show();
											$('session-name-' + session_id).focus();
											if ($('session-name-' + session_id)) {
												$('session-name-' + session_id).observe('keypress', function(event) {
													if (event.keyCode == Event.KEY_RETURN) {
														saveSessionName();
														Event.stop(event);
													}
												});
											}
										}
										
										function saveSessionName() {
											var session_id = $('current-session').value;
											if ($('session-name-' + session_id).visible()) {
												var newname = $('session-name-' + session_id).value;
												$('session-name-' + session_id).hide();
												$('session-' + session_id).show();
											}
											$("session-"+session_id).innerHTML = newname;
											$('session_title').value = newname;
										}
										
										function loadSession(session_id) {
											if ($('current-session').value != session_id && $('event_start_date')) {
												new Ajax.Updater('session-notices' ,'<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
													{
														method: 'post',
														parameters: {
															'step': 2,
															'hide_controls': true,
															'event_start': 1,
															'parent_id' : <?php echo (int)$EVENT_ID; ?>,
															'event_id' : $('current-session').value,
															'event_title': $('session_title').value,
															'event_start_date': $('event_start_date').value,
															'event_start_hour': $('event_start_hour').value,
															'event_start_min': $('event_start_min').value,
															'event_location': $('event_location').value,
															'session_message': $('session_message').value,
															'session_description': $('session_description').value,
															'include_parent_message': $('include_parent_message').checked,
															'include_parent_description': $('include_parent_description').checked,
															'associated_session_faculty': ($('associated_session_faculty') ? $('associated_session_faculty').value : $('associated_faculty').value),
															'groups_session_audience': $('groups_audience_head').value,
															'students_session_audience': $('students_audience_head').value,
															'course_session_audience': $('course_audience_head').value,
															'new': 1
														},
														onComplete: function () {
															if ($('current-session').value == 0 && $('updated_session_id').value) {
																$('session-line-0').id = 'session-line-'+$('updated_session_id').value;
																$('session-line-'+$('updated_session_id').value).innerHTML = '<div id="session-'+ $('updated_session_id').value +'" onclick="loadSession('+ $('updated_session_id').value +')" class="session-entry">'+ $('session-0').innerHTML +'</div>';
															}
															if ($('success').value == 1) {
																$('session-line-'+$('current-session').value).removeClassName('selected');
																$('current-session').value = session_id;
																$('session-line-'+$('current-session').value).addClassName('selected');
																disableRTE();
																new Ajax.Updater({ success: 'session' }, '<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
																	{
																		method: 'post',
																		evalScripts: 'true',
																		parameters: {
																			'event_id' : session_id
																		},
																		onComplete: function () {
																			session_faculty_list = new AutoCompleteList({ type: 'session_faculty', url: '<?php echo ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE; ?>/images/action-delete.gif'});
																			enableRTE();
																			if ($('audience_course') && $('audience_course').innerHTML != $('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" />') {
																				$('audience_course').innerHTML = $('event_course_name').value+'<img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeAudience(\'course\', \'course\');" class="list-cancel-image" />';
																			}
																		},
																		onCreate: function () {
																			$('session').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
																		}
																	}
																);
															}
														}
													}
												);
											} else if ($('current-session').value != session_id) {
												new Ajax.Updater('session-notices' ,'<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
													{
														method: 'post',
														parameters: {
															'step': 2,
															'hide_controls': true,
															'parent_id' : <?php echo (int)$EVENT_ID; ?>,
															'event_id' : $('current-session').value,
															'session_message': $('session_message').value,
															'session_description': $('session_description').value,
															'include_parent_message': $('include_parent_message').checked,
															'include_parent_description': $('include_parent_description').checked,
															'new': 1
														},
														onComplete: function () {
															if ($('current-session').value == 0 && $('updated_session_id').value) {
																$('session-line-0').id = 'session-line-'+$('updated_session_id').value;
																$('session-line-'+$('updated_session_id').value).innerHTML = '<div id="session-'+ $('updated_session_id').value +'" onclick="loadSession('+ $('updated_session_id').value +')" class="session-entry">'+ $('session-0').innerHTML +'</div>';
															}
															if ($('success').value == 1) {
																$('session-line-'+$('current-session').value).removeClassName('selected');
																$('current-session').value = session_id;
																$('session-line-'+$('current-session').value).addClassName('selected');
																new Ajax.Updater({ success: 'session' }, '<?php echo ENTRADA_URL; ?>/api/view-sessions.api.php', 
																	{
																		method: 'post',
																		evalScripts: 'true',
																		parameters: {
																			'event_id' : session_id
																		},
																		onComplete: function () {
																			session_faculty_list = new AutoCompleteList({ type: 'session_faculty', url: '<?php echo ENTRADA_RELATIVE ."/api/personnel.api.php?type=faculty', remove_image: '". ENTRADA_RELATIVE; ?>/images/action-delete.gif'});
																		},
																		onCreate: function () {
																			$('session').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
																		}
																	}
																);
															}
														}
													}
												);
											}
										}
										
										function removeSession() {
											var session_id = $('current-session').value;
											
											if ($('session-count').value > 1) {
												ask_user = confirm("Press OK to confirm that you would like to delete the selected session from this event, otherwise press Cancel.");
												if (ask_user == true) {
													if (session_id) {
														new Ajax.Updater({success: 'session-notices'}, '<?php echo ENTRADA_URL; ?>/api/remove-session.api.php', 
															{
																method: 'post',
																parameters: {
																	'event_id': session_id
																},
																onComplete: function() {
																	$('session-count').value = parseInt($('session-count').value) - 1;
																	if ($('session-line-'+session_id).previous(".event-session")) {
																		$('current-session').value = $('session-line-'+session_id).previous(".event-session").id.replace(/[^0-9]/g, '');
																	} else if ($('session-line-'+session_id).next(".event-session")) {
																		$('current-session').value = $('session-line-'+session_id).next(".event-session").id.replace(/[^0-9]/g, '');
																	} else if ($$('#page-'+(parseInt($('current-page-text').innerHTML) - 1)+' div:last-child').first()) {
																		$('current-session').value = $$('#page-'+(parseInt($('current-page-text').innerHTML) - 1)+' div:last-child').first().id.replace(/[^0-9]/g, '');
																		$('current-page-text').innerHTML = parseInt($('current-page-text').innerHTML) - 1;
																	}
																	if (parseInt($('session-count').value) % 15 == 0) {
																		$('max-page-text').innerHTML = parseInt($('max-page-text').innerHTML) - 1;
																		$('current-page-text').innerHTML = $('max-page-text').innerHTML;
																	}
																	new Ajax.Updater({success: 'session-lists'}, '<?php echo ENTRADA_URL; ?>/api/view-session-lists.api.php', 
																		{
																			method: 'post',
																			parameters: {
																				'event_id': '<?php echo $EVENT_ID; ?>',
																				'session_id': $('current-session').value
																			}
																		}
																	);
																}
															}
														);
													} else {
														$('session-line-'+session_id).remove();
														$('session-count').value = parseInt($('session-count').value) - 1;
														if (parseInt($('session-count').value) % 15 == 0) {
															$('max-page-text').innerHTML = parseInt($('max-page-text').innerHTML) - 1;
														}
														if ($('session-line-'+session_id).previous(".event-session")) {
															$('current-session').value = $('session-line-'+session_id).previous(".event-session").id.replace(/[^0-9]/g, '');
														} else if ($('session-line-'+session_id).next(".event-session")) {
															$('current-session').value = $('session-line-'+session_id).next(".event-session").id.replace(/[^0-9]/g, '');
														} else if ($$('#session-list-'+(parseInt($('current-page-text').innerHTML) - 1)+' div:last-child').first) {
															$('current-session').value = $$('#session-list-'+(parseInt($('current-page-text').innerHTML) - 1)+' div:last-child').first.id.replace(/[^0-9]/g, '');
														}
														new Ajax.Updater({success: 'session-lists'}, '<?php echo ENTRADA_URL; ?>/api/view-session-lists.api.php', 
															{
																method: 'post',
																parameters: {
																	'event_id': '<?php echo $EVENT_ID; ?>',
																	'session_id': $('current-session').value
																}
															}
														);
													}
												} else {
													return false;
												}
											} else {
												ask_user = confirm("Press OK to confirm that you would like to delete this event, otherwise press Cancel.");
												if (ask_user == true) {
													window.location = '<?php echo ENTRADA_URL; ?>/admin/events?section=delete&id='+session_id;
												} else {
													return false;
												}
											}
												
										}
										
										function firstPage() {
											var current_page = parseInt($('current-page-text').innerHTML);
											var first_page = 1;
											if ($('page-'+first_page)) {
												$('page-'+current_page).hide();
												if (!$('page-'+current_page).visible()) {
													$('page-'+first_page).show();
													$('current-page-text').innerHTML = first_page;
												}
											}
										}
										
										function prevPage() {
											var current_page = parseInt($('current-page-text').innerHTML);
											var prev_page = current_page - 1;
											if ($('page-'+prev_page)) {
												$('page-'+current_page).hide();
												if (!$('page-'+current_page).visible()) {
													$('page-'+prev_page).show();
													$('current-page-text').innerHTML = prev_page;
												}
											}
										}
										
										function nextPage() {
											var current_page = parseInt($('current-page-text').innerHTML);
											var next_page = current_page + 1;
											if ($('page-'+next_page)) {
												$('page-'+current_page).hide();
												if (!$('page-'+current_page).visible()) {
													$('page-'+next_page).show();
													$('current-page-text').innerHTML = next_page;
												}
											}
										}
										
										function lastPage() {
											var current_page = parseInt($('current-page-text').innerHTML);
											var max_page = parseInt($('max-page-text').innerHTML);
											if ($('page-'+max_page)) {
												$('page-'+current_page).hide();
												if (!$('page-'+current_page).visible()) {
													$('page-'+max_page).show();
													$('current-page-text').innerHTML = max_page;
												}
											}
										}
										
										function openFileWizard(eid, fid, action) {
											if (!action) {
												action = 'add';
											}
						
											if (!eid) {
												return;
											} else {
												var windowW = 485;
												var windowH = 585;
						
												var windowX = (screen.width / 2) - (windowW / 2);
												var windowY = (screen.height / 2) - (windowH / 2);
						
												fileWizard = window.open('<?php echo ENTRADA_URL; ?>/file-wizard-event.php?action=' + action + '&id=' + eid + ((fid) ? '&fid=' + fid : ''), 'fileWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
												fileWizard.blur();
												window.focus();
						
												fileWizard.resizeTo(windowW, windowH);
												fileWizard.moveTo(windowX, windowY);
						
												fileWizard.focus();
											}
										}
						
										function openLinkWizard(eid, lid, action) {
											if (!action) {
												action = 'add';
											}
						
											if (!eid) {
												return;
											} else {
												var windowW = 485;
												var windowH = 585;
						
												var windowX = (screen.width / 2) - (windowW / 2);
												var windowY = (screen.height / 2) - (windowH / 2);
						
												linkWizard = window.open('<?php echo ENTRADA_URL; ?>/link-wizard-event.php?action=' + action + '&id=' + eid + ((lid) ? '&lid=' + lid : ''), 'linkWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
												linkWizard.blur();
												window.focus();
						
												linkWizard.resizeTo(windowW, windowH);
												linkWizard.moveTo(windowX, windowY);
						
												linkWizard.focus();
											}
										}
						
										function openQuizWizard(eid, qid, action) {
											if (!action) {
												action = 'add';
											}
						
											if (!eid) {
												return;
											} else {
												var windowW = 485;
												var windowH = 585;
						
												var windowX = (screen.width / 2) - (windowW / 2);
												var windowY = (screen.height / 2) - (windowH / 2);
						
												quizWizard = window.open('<?php echo ENTRADA_URL; ?>/quiz-wizard.php?type=event&action=' + action + '&id=' + eid + ((qid) ? '&qid=' + qid : ''), 'quizWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
												quizWizard.blur();
												window.focus();
						
												quizWizard.resizeTo(windowW, windowH);
												quizWizard.moveTo(windowX, windowY);
						
												quizWizard.focus();
											}
										}
						
										function confirmFileDelete() {
											ask_user = confirm("Press OK to confirm that you would like to delete the selected file or files from this event, otherwise press Cancel.");
						
											if (ask_user == true) {
												$('file-listing').submit();
											} else {
												return false;
											}
										}
						
										function confirmLinkDelete() {
											ask_user = confirm("Press OK to confirm that you would like to delete the selected link or links from this event, otherwise press Cancel.");
						
											if (ask_user == true) {
												$('link-listing').submit();
											} else {
												return false;
											}
										}
						
										function confirmQuizDelete() {
											ask_user = confirm("Press OK to confirm that you would like to detach the selected quiz or quizzes from this event, otherwise press Cancel.");
						
											if (ask_user == true) {
												$('quiz-listing').submit();
											} else {
												return false;
											}
										}
						
										function updateEdChecks(obj) {
											var element = obj.id;
						
											if (obj.value == 'minor') {
												var select_box = element + '_desc';
						
												if ($(element).checked == true) {
													$(select_box).disabled = false;
													$(select_box).fade({ duration: 0.3, to: 1.0 });
						
													$(element.replace(/minor/i, 'major')).checked = false;
												} else {
													$(select_box).value = '0';
													$(select_box).disabled = true;
													$(select_box).fade({ duration: 0.3, to: 0.25 });
												}
											} else {
												var select_box = element.replace(/major/i, 'minor_desc');
						
												$(select_box).value = '0';
												$(select_box).disabled = true;
												$(select_box).fade({ duration: 0.3, to: 0.25 });
						
												$(element.replace(/major/i, 'minor')).checked = false;
											}
										}
										var text = new Array();
						
										function objectiveClick(element, objective_id, default_text) {
											if (element.checked) {
												var textarea = document.createElement('textarea');
												textarea.name = 'objective_text['+objective_id+']';
												textarea.id = 'objective_text_'+objective_id;
												if (text[objective_id] != null) {
													textarea.innerHTML = text[objective_id];
												} else {
													textarea.innerHTML = default_text;
												}
												textarea.className = "expandable objective";
												$('objective_'+objective_id+"_append").insert({after: textarea});
												setTimeout('new ExpandableTextarea($("objective_text_'+objective_id+'"));', 100);
											} else {
												if ($('objective_text_'+objective_id)) {
													text[objective_id] = $('objective_text_'+objective_id).value;
													$('objective_text_'+objective_id).remove();
												}
											}
										}
									</script>
								</div>
							</div>
							<div style="clear: both"></div>
						</td>
					</tr>
					<?php
					if ($ENTRADA_ACL->amIAllowed(new EventResource($result["event_id"], $result["course_id"], $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"]), 'update')) {
						?>
						<tr>
							<td colspan="3"><h2>Time Release Options</h2></td>
						</tr>
						<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0));
					}
					if (isset($event_info) && $event_info) {
						?>
						<tr>
							<td colspan="3">
								<a name="event-objectives-section"></a>
								<h2 title="Event Objectives Section">Event Objectives</h2>
								<div id="event-objectives-section">
									<table style="width: 100%" cellspacing="0" cellpadding="0" border="0" summary="Event Objectives Information">
										<colgroup>
											<col style="width: 20%" />
											<col style="width: 80%" />
										</colgroup>
										<tbody>
											<tr>
												<td style="vertical-align: top"><label for="event_objectives" class="form-nrequired">Free-Text Objectives</label></td>
												<td>
													<textarea id="event_objectives" name="event_objectives" style="width: 100%; height: 150px" cols="70" rows="10"><?php echo html_encode(trim(strip_selected_tags($event_info["event_objectives"], array("font")))); ?></textarea>
												</td>
											</tr>
											<tr>
												<td colspan="2">&nbsp;</td>
											</tr>
											<?php
											if ((is_array($clinical_presentations_list)) && (!empty($clinical_presentations_list))) {
												?>
												<tr>
													<td style="vertical-align: top">
														Clinical Presentations
														<div class="content-small" style="margin-top: 5px">
															<strong>Note:</strong> For more detailed information please refer to the <a href="http://www.mcc.ca/Objectives_online/objectives.pl?lang=english&loc=contents" target="_blank" style="font-size: 11px">MCC Objectives for the Qualifying Examination</a>.
														</div>
													</td>
													<td>
														<select class="multi-picklist" id="PickList" name="clinical_presentations[]" multiple="multiple" size="5" style="width: 100%; margin-bottom: 5px">
														<?php
														if ((is_array($clinical_presentations)) && (count($clinical_presentations))) {
															foreach ($clinical_presentations as $objective_id => $presentation_name) {
																echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
															}
														}
														?>
														</select>
														<input type="hidden" value="1" name="clinical_presentations_submit" />
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
															foreach ($clinical_presentations_list as $objective_id => $presentation_name) {
																if (!array_key_exists($objective_id, $clinical_presentations)) {
																	echo "<option value=\"".(int) $objective_id."\">".html_encode($presentation_name)."</option>\n";
																}
															}
															?>
															</select>
														</div>
														<script type="text/javascript">
														if ($('PickList')) {
															$('PickList').observe('keypress', function(event) {
																if (event.keyCode == Event.KEY_DELETE) {
																	delIt();
																}
															});
														}
	
														if ($('SelectList')) {
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
													<td colspan="2">&nbsp;</td>
												</tr>
												<?php
											}
	
											if ((is_array($curriculum_objectives_list["used_ids"])) && (count($curriculum_objectives_list["used_ids"]))) {
												echo "<tr>\n";
												echo "	<td style=\"vertical-align: top;\">\n";
												echo "		<span class=\"form-nrequired\">Curriculum Objectives</span>\n";
												echo "		<div class=\"content-small\" style=\"margin-top: 5px\">\n";
												echo "			<strong>Note:</strong> Please check any curriculum objectives that are covered during this learning event.\n";
												echo "		</div>\n";
												echo "	</td>\n";
												echo "	<td style=\"vertical-align: top\">\n";
												echo "		<div id=\"course-objectives-section\">\n";
												echo "			<strong>The learner will be able to:</strong>\n";
												echo			event_objectives_in_list($curriculum_objectives_list, 1, true, false, 1, false);
												echo "		</div>\n";
												echo "	</td>\n";
												echo "</tr>\n";
	
												new_sidebar_item("Objective Importance", $sidebar_html, "objective-legend", "open");
											}
											?>
										</tbody>
									</table>
								</div>
							</td>
						</tr>						<tr>
							<td colspan="3">
								<a name="event-resources-section"></a>
								<h2 title="Event Resources Section">Event Resources</h2>
								<div id="event-resources-section">
									<div style="margin-bottom: 15px">
										<div style="float: left; margin-bottom: 5px">
											<h3>Attached Files</h3>
										</div>
										<div style="float: right; margin-bottom: 5px">
											<ul class="page-action">
												<li><a href="javascript: openFileWizard('<?php echo $EVENT_ID; ?>', 0, 'add')">Add A File</a></li>
											</ul>
										</div>
										<div class="clear"></div>
										<?php
										$query		= "	SELECT *
														FROM `event_files`
														WHERE `event_id` = ".$db->qstr($EVENT_ID)."
														ORDER BY `file_category` ASC, `file_title` ASC";
										$results	= $db->GetAll($query);
										echo "<form id=\"file-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
										echo "<input type=\"hidden\" name=\"type\" value=\"files\" />\n";
										echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Files\">\n";
										echo "<colgroup>\n";
										echo "	<col class=\"modified\" style=\"width: 50px\" />\n";
										echo "	<col class=\"file-category\" />\n";
										echo "	<col class=\"title\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"accesses\" />\n";
										echo "</colgroup>\n";
										echo "<thead>\n";
										echo "	<tr>\n";
										echo "		<td class=\"modified\">&nbsp;</td>\n";
										echo "		<td class=\"file-category sortedASC\"><div class=\"noLink\">Category</div></td>\n";
										echo "		<td class=\"title\">File Title</td>\n";
										echo "		<td class=\"date-small\">Accessible Start</td>\n";
										echo "		<td class=\"date-small\">Accessible Finish</td>\n";
										echo "		<td class=\"accesses\">Saves</td>\n";
										echo "	</tr>\n";
										echo "</thead>\n";
										echo "<tfoot>\n";
										echo "	<tr>\n";
										echo "		<td>&nbsp;</td>\n";
										echo "		<td colspan=\"5\" style=\"padding-top: 10px\">\n";
										echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmFileDelete()\" />" : "&nbsp;");
										echo "		</td>\n";
										echo "	</tr>\n";
										echo "</tfoot>\n";
										echo "<tbody>\n";
										if ($results) {
											foreach ($results as $result) {
												$filename	= $result["file_name"];
												$parts		= pathinfo($filename);
												$ext		= $parts["extension"];
	
												echo "<tr id=\"file-".$result["efile_id"]."\">\n";
												echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
												echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["efile_id"]."\" style=\"vertical-align: middle\" />\n";
												echo "		<a href=\"".ENTRADA_URL."/file-event.php?id=".$result["efile_id"]."\" target=\"_blank\"><img src=\"".ENTRADA_URL."/images/btn_save.gif\" width=\"16\" height=\"16\" alt=\"Download ".html_encode($result["file_name"])." to your computer.\" title=\"Download ".html_encode($result["file_name"])." to your computer.\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
												echo "	</td>\n";
												echo "	<td class=\"file-category\">".((isset($RESOURCE_CATEGORIES["event"][$result["file_category"]])) ? html_encode($RESOURCE_CATEGORIES["event"][$result["file_category"]]) : "Unknown Category")."</td>\n";
												echo "	<td class=\"title\">\n";
												echo "		<img src=\"".ENTRADA_URL."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle\" />";
												echo "		<a href=\"javascript: openFileWizard('".$EVENT_ID."', '".$result["efile_id"]."', 'edit')\" title=\"Click to edit ".html_encode($result["file_title"])."\" style=\"font-weight: bold\">".html_encode($result["file_title"])."</a>";
												echo "	</td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
												echo "</tr>\n";
											}
										} else {
											echo "<tr>\n";
											echo "	<td colspan=\"6\">\n";
											echo "		<div class=\"display-generic\">\n";
											echo "			There have been no files added to this event. To <strong>add a new file</strong>, simply click the Add File button.\n";
											echo "		</div>\n";
											echo "	</td>\n";
											echo "</tr>\n";
										}
										echo "</tbody>\n";
										echo "</table>\n";
										echo "</form>\n";
										?>
									</div>
	
									<div style="margin-bottom: 15px">
										<div style="float: left; margin-bottom: 5px">
											<h3>Attached Links</h3>
										</div>
										<div style="float: right; margin-bottom: 5px">
											<ul class="page-action">
												<li><a href="javascript: openLinkWizard('<?php echo $EVENT_ID; ?>', 0, 'add')">Add A Link</a></li>
											</ul>
										</div>
										<div class="clear"></div>
										<?php
										$query		= "	SELECT *
														FROM `event_links`
														WHERE `event_id`=".$db->qstr($EVENT_ID)."
														ORDER BY `link_title` ASC";
										$results	= $db->GetAll($query);
										echo "<form id=\"link-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
										echo "<input type=\"hidden\" name=\"type\" value=\"links\" />\n";
										echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Links\">\n";
										echo "<colgroup>\n";
										echo "	<col class=\"modified\" style=\"width: 50px\" />\n";
										echo "	<col class=\"title\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"accesses\" />\n";
										echo "</colgroup>\n";
										echo "<thead>\n";
										echo "	<tr>\n";
										echo "		<td class=\"modified\">&nbsp;</td>\n";
										echo "		<td class=\"title sortedASC\"><div class=\"noLink\">Linked Resources</div></td>\n";
										echo "		<td class=\"date-small\">Accessible Start</td>\n";
										echo "		<td class=\"date-small\">Accessible Finish</td>\n";
										echo "		<td class=\"accesses\">Hits</td>\n";
										echo "	</tr>\n";
										echo "</thead>\n";
										echo "<tfoot>\n";
										echo "	<tr>\n";
										echo "		<td>&nbsp;</td>\n";
										echo "		<td colspan=\"4\" style=\"padding-top: 10px\">\n";
										echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Delete Selected\" onclick=\"confirmLinkDelete()\" />" : "&nbsp;");
										echo "		</td>\n";
										echo "	</tr>\n";
										echo "</tfoot>\n";
										echo "<tbody>\n";
										if ($results) {
											foreach ($results as $result) {
												echo "<tr>\n";
												echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
												echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["elink_id"]."\" style=\"vertical-align: middle\" />\n";
												echo "		<a href=\"".ENTRADA_URL."/link-event.php?id=".$result["elink_id"]."\" target=\"_blank\"><img src=\"".ENTRADA_URL."/images/url-visit.gif\" width=\"16\" height=\"16\" alt=\"Visit ".html_encode($result["link"])."\" title=\"Visit ".html_encode($result["link"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
												echo "	</td>\n";
												echo "	<td class=\"title\" style=\"white-space: normal; overflow: visible\">\n";
												echo "		<a href=\"javascript: openLinkWizard('".$EVENT_ID."', '".$result["elink_id"]."', 'edit')\" title=\"Click to edit ".html_encode($result["link"])."\" style=\"font-weight: bold\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : $result["link"])."</a>\n";
												echo "	</td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
												echo "</tr>\n";
											}
										} else {
											echo "<tr>\n";
											echo "	<td colspan=\"5\">\n";
											echo "		<div class=\"display-generic\">\n";
											echo "			There have been no links added to this event. To <strong>add a new link</strong>, simply click the Add Link button.\n";
											echo "		</div>\n";
											echo "	</td>\n";
											echo "</tr>\n";
										}
										echo "</tbody>\n";
										echo "</table>\n";
										echo "</form>\n";
										?>
									</div>
	
									<div style="margin-bottom: 15px">
										<div style="float: left; margin-bottom: 5px">
											<h3>Attached Quizzes</h3>
										</div>
										<div style="float: right; margin-bottom: 5px">
											<ul class="page-action">
												<li><a href="<?php echo ENTRADA_URL; ?>/admin/quizzes?section=add">Create New Quiz</a></li>
												<li><a href="javascript: openQuizWizard('<?php echo $EVENT_ID; ?>', 0, 'add')">Attach Existing Quiz</a></li>
											</ul>
										</div>
										<div class="clear"></div>
										<?php
										$query		= "	SELECT a.*, b.`quiztype_title`
														FROM `attached_quizzes` AS a
														LEFT JOIN `quizzes_lu_quiztypes` AS b
														ON b.`quiztype_id` = a.`quiztype_id`
														WHERE a.`content_type` = 'event' 
														AND a.`content_id` = ".$db->qstr($EVENT_ID)."
														ORDER BY b.`quiztype_title` ASC, a.`quiz_title` ASC";
										$results	= $db->GetAll($query);
										echo "<form id=\"quiz-listing\" action=\"".ENTRADA_URL."/admin/events?".replace_query()."\" method=\"post\">\n";
										echo "<input type=\"hidden\" name=\"type\" value=\"quizzes\" />\n";
										echo "<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Quizzes\">\n";
										echo "<colgroup>\n";
										echo "	<col class=\"modified\" style=\"width: 50px\"  />\n";
										echo "	<col class=\"file-category\" />\n";
										echo "	<col class=\"title\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"date\" />\n";
										echo "	<col class=\"accesses\" />\n";
										echo "</colgroup>\n";
										echo "<thead>\n";
										echo "	<tr>\n";
										echo "		<td class=\"modified\">&nbsp;</td>\n";
										echo "		<td class=\"file-category sortedASC\"><div class=\"noLink\">Category</div></td>\n";
										echo "		<td class=\"title\">Quiz Title</td>\n";
										echo "		<td class=\"date-small\">Accessible Start</td>\n";
										echo "		<td class=\"date-small\">Accessible Finish</td>\n";
										echo "		<td class=\"accesses\">Done</td>\n";
										echo "	</tr>\n";
										echo "</thead>\n";
										echo "<tfoot>\n";
										echo "	<tr>\n";
										echo "		<td>&nbsp;</td>\n";
										echo "		<td colspan=\"5\" style=\"padding-top: 10px\">\n";
										echo "			".(($results) ? "<input type=\"button\" class=\"button\" value=\"Detach Selected\" onclick=\"confirmQuizDelete()\" />" : "&nbsp;");
										echo "		</td>\n";
										echo "	</tr>\n";
										echo "</tfoot>\n";
										echo "<tbody>\n";
										if ($results) {
											foreach ($results as $result) {
												echo "<tr>\n";
												echo "	<td class=\"modified\" style=\"width: 50px; white-space: nowrap\">\n";
												echo "		<input type=\"checkbox\" name=\"delete[]\" value=\"".$result["aquiz_id"]."\" style=\"vertical-align: middle\" />\n";
												if ($result["accesses"] > 0) {
													echo "	<a href=\"".ENTRADA_URL."/admin/quizzes?section=results&amp;id=".$result["aquiz_id"]."\"><img src=\"".ENTRADA_URL."/images/view-stats.gif\" width=\"16\" height=\"16\" alt=\"View results of ".html_encode($result["quiz_title"])."\" title=\"View results of ".html_encode($result["quiz_title"])."\" style=\"vertical-align: middle\" border=\"0\" /></a>\n";
												} else {
													echo "	<img src=\"".ENTRADA_URL."/images/view-stats-disabled.gif\" width=\"16\" height=\"16\" alt=\"No completed quizzes at this time.\" title=\"No completed quizzes at this time.\" style=\"vertical-align: middle\" border=\"0\" />\n";
												}
												echo "	</td>\n";
												echo "	<td class=\"file-category\">".html_encode($result["quiztype_title"])."</td>\n";
												echo "	<td class=\"title\" style=\"white-space: normal; overflow: visible\">\n";
												echo "		<a href=\"javascript: openQuizWizard('".$EVENT_ID."', '".$result["aquiz_id"]."', 'edit')\" title=\"Click to edit ".html_encode($result["quiz_title"])."\" style=\"font-weight: bold\">".html_encode($result["quiz_title"])."</a>\n";
												echo "	</td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_date"]) ? date(DEFAULT_DATE_FORMAT, $result["release_date"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"date-small\"><span class=\"content-date\">".(((int) $result["release_until"]) ? date(DEFAULT_DATE_FORMAT, $result["release_until"]) : "No Restrictions")."</span></td>\n";
												echo "	<td class=\"accesses\" style=\"text-align: center\">".$result["accesses"]."</td>\n";
												echo "</tr>\n";
											}
										} else {
											echo "<tr>\n";
											echo "	<td colspan=\"6\">\n";
											echo "		<div class=\"display-generic\" style=\"white-space: normal\">\n";
											echo "			There have been no quizzes attached to this event. To <strong>create a new quiz</strong> click the <a href=\"".ENTRADA_URL."/admin/quizzes\" style=\"font-weight: bold\">Manage Quizzes</a> tab, and then to attach the quiz to this event click the <strong>Attach Quiz</strong> button below.\n";
											echo "		</div>\n";
											echo "	</td>\n";
											echo "</tr>\n";
										}
										echo "</tbody>\n";
										echo "</table>\n";
										echo "</form>\n";
										?>
									</div>
								</div>
	
								<script type="text/javascript">
								$$('select.ed_select_off').each(function(el) {
									$(el).disabled = true;
									$(el).fade({ duration: 0.3, to: 0.25 });
								});
								</script>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				
			</form>
			<?php
		break;
	}
}
?>
