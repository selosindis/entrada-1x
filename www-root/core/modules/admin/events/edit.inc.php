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
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/growler/src/Growler.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
	if ($EVENT_ID) {
		$query = "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);
		if ($event_info) {
			if (!$ENTRADA_ACL->amIAllowed(new EventResource($event_info["event_id"], $event_info["course_id"], $ENTRADA_USER->getActiveOrganisation()), 'update')) {
				application_log("error", "A program coordinator attempted to edit an event [".$EVENT_ID."] that they were not the coordinator for.");
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "edit", "id" => $EVENT_ID)), "title" => "Editing Event");
				$ORGANISATION_ID = $event_info["organisation_id"];
				$PROCESSED["associated_faculty"] = array();
				$PROCESSED["event_audience_type"] = "course";
				$PROCESSED["associated_grad_year"] = "";
				$PROCESSED["associated_group_ids"] = array();
				$PROCESSED["associated_proxy_ids"] = array();
				$PROCESSED["event_types"] = array();

				echo "<div class=\"no-printing\">\n";
				echo "	<div style=\"float: right; margin-top: 8px\">\n";
				echo "		<a href=\"".ENTRADA_URL."/admin/events?".replace_query(array("section" => "content", "id" => $EVENT_ID))."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage event content\" title=\"Manage event content\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/events?".replace_query(array("section" => "content", "id" => $EVENT_ID, "step" => false))."\" style=\"font-size: 10px; margin-right: 8px\">Manage event content</a>\n";
				echo "	</div>\n";
				echo "</div>\n";

				echo "<h1>Editing Event</h1>\n";

				// Error Checking
				switch($STEP) {
					case 2 :
						
						$query		= "SELECT a.*, b.`eventtype_title` FROM `event_eventtypes` AS a LEFT JOIN `events_lu_eventtypes` AS b ON a.`eventtype_id` = b.`eventtype_id` WHERE a.`event_id` = ".$db->qstr($EVENT_ID)." ORDER BY `eeventtype_id` ASC";
						$results	= $db->GetAll($query);
						$initial_duration = 0;
						if ($results) {
							foreach ($results as $result) {
								$initial_duration += $result["duration"];
								//$event_eventtypes[] = array($result["eventtype_id"], $result["duration"], $event_eventtypes_list[$result["eventtype_id"]]);
								$old_event_eventtypes[] = $result;
							}
						}
						
						
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
						 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
						 * This is actually accomplished after the event is inserted below.
						 */
						if ((isset($_POST["associated_faculty"]))) {
							$associated_faculty = explode(',',$_POST["associated_faculty"]);
							foreach($associated_faculty as $contact_order => $proxy_id) {
								if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
									$PROCESSED["associated_faculty"][(int) $contact_order] = $proxy_id;
									$PROCESSED["contact_role"][(int)$contact_order] = $_POST["faculty_role"][(int) $contact_order];
									$PROCESSED["display_role"][$proxy_id] = $_POST["faculty_role"][(int) $contact_order];
								}
							}
						}

						/**
						 * Non-required field "associated_faculty" / Associated Faculty (array of proxy ids).
						 * This is actually accomplished after the event is inserted below.
						 */
						if (isset($_POST["event_audience_type"])) {
							$PROCESSED["event_audience_type"] = clean_input($_POST["event_audience_type"], array("page_url"));

							switch($PROCESSED["event_audience_type"]) {
								case "course" :
									/**
									 * Required field "course" / Course
									 * This data is inserted into the event_audience table as course.
									 */
								break;									
								case "grad_year" :
									/**
									 * Required field "associated_grad_year" / Graduating Year
									 * This data is inserted into the event_audience table as grad_year.
									 */
									if ((isset($_POST["associated_grad_year"])) && ($associated_grad_year = clean_input($_POST["associated_grad_year"], "alphanumeric"))) {
										$PROCESSED["associated_grad_year"] = $associated_grad_year;
									} else {
										$ERROR++;
										$ERRORSTR[] = "You have chosen <strong>Entire Class Event</strong> as an <strong>Event Audience</strong> type, but have not selected a graduating year.";
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
									if ((isset($_POST["associated_student"]))) {
										$associated_proxies = explode(',', $_POST["associated_student"]);
										if ((isset($associated_proxies)) && (is_array($associated_proxies)) && (count($associated_proxies))) {
											foreach($associated_proxies as $proxy_id) {
												if ($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
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
											if (!count($PROCESSED["associated_proxy_ids"])) {
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
									if ((isset($_POST["associated_organisation_id"])) && ($associated_organisation_id = clean_input($_POST["associated_organisation_id"], array("trim", "int")))) {
										if ($ENTRADA_ACL->amIAllowed('resourceorganisation'.$associated_organisation_id, 'create')) {
											$PROCESSED["associated_organisation_id"] = $associated_organisation_id;
										} else {
											$ERROR++;
											$ERRORSTR[] = "You do not have permission to add an event for this organisation, please select a different one.";
										}
									} else {
										$ERROR++;
										$ERRORSTR[] = "You have chosen <strong>Entire Organisation Event</strong> as an <strong>Event Audience</strong> type, but have not selected an organisation.";
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

						/**
						 * Required field "event_start" / Event Date & Time Start (validated through validate_calendars function).
						 */
						$start_date = validate_calendars("event", true, false);
						if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
							$PROCESSED["event_start"] = (int) $start_date["start"];
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
										//$query = "SELECT a.*, b.`eventtype_title` FROM `event_eventtypes` AS a LEFT JOIN `events_lu_eventtypes` AS b ON a.`eventtype_id` = b.`eventtype_id` WHERE a.`eventtype_id` = ".$db->qstr($eventtype_id)." ORDER BY `eeventtype_id` ASC";
										$result	= $db->GetRow($query);
										
										if ($result) {
											$PROCESSED["event_types"][] = array("eventtype_id"=>$eventtype_id,"duration"=> $duration, "eventtype_title"=>$result["eventtype_title"]);
											//$PROCESSED["event_types"][] = $result;									
										} else {
											$ERROR++;
											$ERRORSTR[] = "One of the <strong>event types</strong> you specified was invalid.";
										}
									} else {
										$ERROR++;
										$ERRORSTR[] = "One of the <strong>event types</strong> you specified is invalid.";
									}
								}
								
								
							$event_duration	= 0;
							$old_event_duration = 0;
							foreach($PROCESSED["event_types"] as $event_type) {
								$event_duration += $event_type["duration"];
							}
						
							foreach($old_event_eventtypes as $event_type) {
								$old_event_duration += $event_type["duration"];
							}
							
							if($old_event_duration != $event_duration) {
								$ERROR++;
								$ERRORSTR[] = "The modified <strong>Event Types</strong> duration specified is different than the exisitng one, please ensure the event's duration remains the same.";
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
										WHERE `course_id` = ".$db->qstr($course_id);
							$result	= $db->GetRow($query);
							if ($result) {
								if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $course_id, $ENTRADA_USER->getActiveOrganisation()), "create")) {
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
							$PROCESSED["updated_date"]	= time();
							$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

							$PROCESSED["event_finish"] = $PROCESSED["event_start"];
							$PROCESSED["event_duration"] = 0;
							foreach($PROCESSED["event_types"] as $event_type) {
								$PROCESSED["event_finish"] += $event_type["duration"]*60;
								$PROCESSED["event_duration"] += $event_type["duration"];
							}

							$PROCESSED["eventtype_id"] = $PROCESSED["event_types"][0]["eventtype_id"];

							if ($db->AutoExecute("events", $PROCESSED, "UPDATE", "`event_id` = ".$db->qstr($EVENT_ID))) {
								$query = "DELETE FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID);
								if ($db->Execute($query)) {
									$query = "DELETE FROM `event_eventtypes` WHERE `event_id` = ".$db->qstr($EVENT_ID);
									if ($db->Execute($query)) {
										foreach($PROCESSED["event_types"] as $event_type) {
											if (!$db->AutoExecute("event_eventtypes", array("event_id" => $EVENT_ID, "eventtype_id" => $event_type["eventtype_id"], "duration" => $event_type["duration"]), "INSERT")) {
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

									switch($PROCESSED["event_audience_type"]) {
										case "course" :
											/**
											 * If the audience for the event is meant to be grabbed via the course,
											 * add it to the event_audience table.
											 */
												if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "course", "audience_value" => $PROCESSED["course_id"], "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
													$ERROR++;
													$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Course Audience</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

													application_log("error", "Unable to insert a new event_audience record while adding a new event. Database said: ".$db->ErrorMsg());
												}
										break;
										case "grad_year" :
											/**
											 * If there are any graduating years associated with this event,
											 * add it to the event_audience table.
											 */
											if ($PROCESSED["associated_grad_year"]) {
												if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "grad_year", "audience_value" => $PROCESSED["associated_grad_year"], "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
													$ERROR++;
													$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Graduating Year</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

													application_log("error", "Unable to insert a new event_audience record while adding a new event. Database said: ".$db->ErrorMsg());
												}
											}
										break;
										case "proxy_id" :
											/**
											 * If there are proxy_ids associated with this event,
											 * add them to the event_audience table.
											 */
											if (count($PROCESSED["associated_proxy_ids"])) {
												foreach($PROCESSED["associated_proxy_ids"] as $proxy_id) {
													if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "proxy_id", "audience_value" => (int) $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
														$ERROR++;
														$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

														application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
													}
												}
											}
										break;
										case "organisation_id":
											if (isset($PROCESSED["associated_organisation_id"])) {
												if (!$db->AutoExecute("event_audience", array("event_id" => $EVENT_ID, "audience_type" => "organisation_id", "audience_value" => $PROCESSED["associated_organisation_id"], "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
													$ERROR++;
													$ERRORSTR[] = "There was an error while trying to attach the selected <strong>Proxy ID</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";
													application_log("error", "Unable to insert a new event_audience, proxy_id record while adding a new event. Database said: ".$db->ErrorMsg());
												}
											}
										break;
										default :
											application_log("error", "Unrecognized event_audience_type [".$_POST["event_audience_type"]."] encountered, no audience added for event_id [".$EVENT_ID."].");
										break;
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
											if (!$db->AutoExecute("event_contacts", array("event_id" => $EVENT_ID, "proxy_id" => $proxy_id,"contact_role"=>$PROCESSED["contact_role"][$contact_order], "contact_order" => (int) $contact_order, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
												$ERROR++;
												$ERRORSTR[] = "There was an error while trying to attach an <strong>Associated Faculty</strong> to this event.<br /><br />The system administrator was informed of this error; please try again later.";

												application_log("error", "Unable to insert a new event_contact record while adding a new event. Database said: ".$db->ErrorMsg());
											}
										}
									}
								}

								switch($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"]) {
									case "content" :
										$url	= ENTRADA_URL."/admin/events?section=content&id=".$EVENT_ID;
										$msg	= "You will now be redirected to the event content page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "new" :
										$url	= ENTRADA_URL."/admin/events?section=add";
										$msg	= "You will now be redirected to add a new event; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
									case "index" :
									default :
										$url	= ENTRADA_URL."/admin/events";
										$msg	= "You will now be redirected to the event index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
									break;
								}
								if(!$ERROR){
									$query = "	SELECT b.* FROM `community_courses` AS a 
												LEFT JOIN `community_pages` AS b 
												ON a.`community_id` = b.`community_id` 
												LEFT JOIN `community_page_options` AS c 
												ON b.`community_id` = c.`community_id` 
												WHERE c.`option_title` = 'show_history' 
												AND c.`option_value` = 1 
												AND b.`page_url` = 'course_calendar' 
												AND b.`page_active` = 1 
												AND a.`course_id` = ".$PROCESSED["course_id"];
									$result = $db->GetRow($query);
						
									if($result){
										$COMMUNITY_ID = $result["community_id"];
										$PAGE_ID = $result["cpage_id"];
										communities_log_history($COMMUNITY_ID, $PAGE_ID, $EVENT_ID, "community_history_edit_learning_event", 1);
									}
									
									
									$SUCCESS++;
									$SUCCESSSTR[] = "You have successfully edited <strong>".html_encode($PROCESSED["event_title"])."</strong> in the system.<br /><br />".$msg;
									$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

									application_log("success", "Event [".$EVENT_ID."] has been modified.");
								}
							} else {
								$ERROR++;
								$ERRORSTR[] = "There was a problem updating this event in the system. The system administrator was informed of this error; please try again later.";

								application_log("error", "There was an error updating event_id [".$EVENT_ID."]. Database said: ".$db->ErrorMsg());
							}
						}

						if ($ERROR) {
							$STEP = 1;
						}
					break;
					case 1 :
					default :
						$PROCESSED	= $event_info;
						/**
						 * Add existing event type segments to the processed array.
						 */
						$query = "	SELECT `types`.`eventtype_id`,`types`.`duration`,`lu_types`.`eventtype_title` FROM `event_eventtypes` AS `types` 
									LEFT JOIN `events_lu_eventtypes` AS `lu_types` 
									ON `lu_types`.`eventtype_id` = `types`.`eventtype_id` 
									LEFT JOIN `eventtype_organisation` AS `type_org` 
									ON `lu_types`.`eventtype_id` = `type_org`.`eventtype_id` 
									LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS `org` 
									ON `type_org`.`organisation_id` = `org`.`organisation_id` 
									WHERE `types`.`event_id` = ".$db->qstr($EVENT_ID)." 
									AND `type_org`.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
									ORDER BY `types`.`eventtype_id` ASC";
					
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $contact_order => $result) {
								$PROCESSED["event_types"][] = array("eventtype_id"=>$result["eventtype_id"], "duration"=>$result["duration"], "eventtype_title"=>$result["eventtype_title"]);
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
								$PROCESSED["display_role"][(int)$result["proxy_id"]] = $result["contact_role"];
							}
						}

						$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID);
						$results = $db->GetAll($query);
						if ($results) {
							/**
							 * Set the audience_type.
							 */
							$PROCESSED["event_audience_type"] = $results[0]["audience_type"];

							foreach($results as $result) {
								if ($result["audience_type"] == $PROCESSED["event_audience_type"]) {
									switch($result["audience_type"]) {
										case "course" :
											$PROCESSED["associated_course"] = (int)$result["audience_value"];
										break;										
										case "grad_year" :
											$PROCESSED["associated_grad_year"] = clean_input($result["audience_value"], "alphanumeric");
										break;
										case "group_id" :
											$PROCESSED["associated_group_ids"][] = (int) $result["audience_value"];
										break;
										case "proxy_id" :
											$PROCESSED["associated_proxy_ids"][] = (int) $result["audience_value"];
										break;
										case "organisation_id" :
											$PROCESSED["associated_organisation_id"] = (int) $result["audience_value"];
										break;
									}
								}
							}
						}
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
						$ONLOAD[] = "selectEventAudienceOption('".$PROCESSED["event_audience_type"]."')";
						
						$LASTUPDATED = $result["updated_date"];

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

						$query = "SELECT `organisation_id`, `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
						$organisation_results = $db->GetAll($query);
						if ($organisation_results) {
							$organisations = array();
							foreach ($organisation_results as $result) {
								if ($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'create')) {
									$organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
								}
							}
						}
						?>
						<form name="editEventForm" id="editEventForm" action="<?php echo ENTRADA_URL; ?>/admin/events?<?php echo replace_query(array("step" => 2)); ?>" method="post">
							<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Editing Event">
								<colgroup>
									<col style="width: 3%" />
									<col style="width: 20%" />
									<col style="width: 77%" />
								</colgroup>
								<tr>
									<td colspan="3"><h2>Event Details</h2></td>
								</tr>
								<tr>
									<td></td>
									<td><label for="event_title" class="form-required">Event Title</label></td>
									<td>
										<input type="text" id="event_title" name="event_title" value="<?php echo html_encode($PROCESSED["event_title"]); ?>" maxlength="255" style="width: 95%" />
										<input type="hidden" name="event_id" value ="<?php echo $EVENT_ID;?>"/>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<?php echo generate_calendars("event", "Event Date & Time", true, true, ((isset($PROCESSED["event_start"])) ? $PROCESSED["event_start"] : 0)); ?>
								<tr>
									<td></td>
									<td><label for="event_location" class="form-nrequired">Event Location</label></td>
									<td><input type="text" id="event_location" name="event_location" value="<?php echo $PROCESSED["event_location"]; ?>" maxlength="255" style="width: 203px" /></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td></td>
									<td style="vertical-align: top"><label for="eventtype_ids" class="form-required">Event Types</label></td>
									<td>
										<select id="eventtype_ids" name="eventtype_ids">
											<option id="-1"> -- Pick a type to add -- </option>
												<?php
												$query		= "	SELECT a.* FROM `events_lu_eventtypes` AS a 
																LEFT JOIN `eventtype_organisation` AS c 
																ON a.`eventtype_id` = c.`eventtype_id` 
																LEFT JOIN `".AUTH_DATABASE."`.`organisations` AS b
																ON b.`organisation_id` = c.`organisation_id` 
																WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
																AND a.`eventtype_active` = '1' 
																ORDER BY a.`eventtype_order`
													";
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
									<div id="duration_notice" class="content-small">Use the list above to select the different components of this event. When you select one, it will appear here and you can change the order and duration.</div>
									<?php
                                    echo "<ol id=\"duration_container\" class=\"sortableList\" style=\"display: none\">\n";
                                    if (is_array($PROCESSED["event_types"])) {
                                        
										foreach ($PROCESSED["event_types"] as $eventtype) {
                                            echo "<li id=\"type_".(int) $eventtype["eventtype_id"]."\" class=\"\">".html_encode($eventtype["eventtype_title"])."
                                                <a href=\"#\" onclick=\"$(this).up().remove(); cleanupList(); return false;\" class=\"remove\">
                                                    <img src=\"".ENTRADA_URL."/images/action-delete.gif\">
                                                </a>
                                                <span class=\"duration_segment_container\">
                                                    Duration: <input class=\"duration_segment\" name=\"duration_segment[]\" onchange=\"cleanupList();\" value=\"".$eventtype["duration"]."\"> minutes
                                                </span>
                                            </li>";
                                        }
                                    echo "</ol>";
									
									}
									?>
									<div id="total_duration" class="content-small">Total time: 0 minutes.</div>
									<input id="eventtype_duration_order" name="eventtype_duration_order" style="display: none;"/>									
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
														<li class="community" id="faculty_<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>" style="cursor: move;margin-bottom:10px;width:350px;"><?php echo $FACULTY_LIST[$faculty]["fullname"]; ?><select name ="faculty_role[]" style="float:right;margin-right:30px;margin-top:-5px;"><option value="teacher" <?php if($PROCESSED["display_role"][$faculty] == "teacher") echo "SELECTED";?>>Teacher</option><option value="tutor" <?php if($PROCESSED["display_role"][$faculty] == "tutor") echo "SELECTED";?>>Tutor</option><option value="ta" <?php if($PROCESSED["display_role"][$faculty] == "ta") echo "SELECTED";?>>Teacher's Assistant</option><option value="auditor" <?php if($PROCESSED["display_role"][$faculty] == "auditor") echo "SELECTED";?>>Auditor</option></select><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="faculty_list.removeItem('<?php echo $FACULTY_LIST[$faculty]["proxy_id"]; ?>');" class="list-cancel-image" /></li>
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
								<tr>
									<td colspan="3"><h2>Event Audience</h2></td>
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
									<td></td>
									<td><label for="course_id" class="form-required">Course</label></td>
									<td>
										<select id="course_id" name="course_id" onchange="generateEventAutocomplete()" style="width: 95%">
										<?php
										$query		= "	SELECT * FROM `courses`
														WHERE `organisation_id` = ".$db->qstr($ORGANISATION_ID)."
														AND (`course_active` = '1' OR `course_id` = ".$db->qstr($PROCESSED["course_id"]).")
														ORDER BY `course_name` ASC";
										$results	= $db->GetAll($query);
										if ($results) {
											foreach($results as $result) {
												if ($ENTRADA_ACL->amIAllowed(new EventResource(null, $result["course_id"], $ENTRADA_USER->getActiveOrganisation()), "create")) {
													echo "<option value=\"".(int) $result["course_id"]."\"".(($PROCESSED["course_id"] == $result["course_id"]) ? " selected=\"selected\"" : "").">".html_encode($result["course_name"])."</option>\n";
												}
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
									<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_course" value="course" onclick="selectEventAudienceOption('course')" style="vertical-align: middle"<?php echo (($PROCESSED["event_audience_type"] == "course") ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2" style="padding-bottom: 15px">
										<label for="event_audience_type_course" class="radio-group-title">Use Course Audience</label>
										<div class="content-small">This event is intended for people enrolled in the course.</div>
									</td>
								</tr>									
								<tr>
									<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_grad_year" value="grad_year" onclick="selectEventAudienceOption('grad_year')" style="vertical-align: middle"<?php echo (($PROCESSED["event_audience_type"] == "grad_year") ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2" style="padding-bottom: 15px">
										<label for="event_audience_type_grad_year" class="radio-group-title">Entire Class Event</label>
										<div class="content-small">This event is intended for an entire class.</div>
									</td>
								</tr>
								<tr class="event_audience grad_year_audience">
									<td></td>
									<td><label for="associated_grad_year" class="form-required">Graduating Year</label></td>
									<td>
										<select id="associated_grad_year" name="associated_grad_year" style="width: 203px">
										<?php
										if (isset($SYSTEM_GROUPS["student"]) && !empty($SYSTEM_GROUPS["student"])) {
											foreach ($SYSTEM_GROUPS["student"] as $class) {
												echo "<option value=\"".$class."\"".(($PROCESSED["associated_grad_year"] == $class) ? " selected=\"selected\"" : "").">Class of ".html_encode($class)."</option>\n";
											}
										}
										?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr >
									<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_proxy_id" value="proxy_id" onclick="selectEventAudienceOption('proxy_id')" style="vertical-align: middle"<?php echo (($PROCESSED["event_audience_type"] == "proxy_id") ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2" style="padding-bottom: 15px">
										<label for="event_audience_type_proxy_id" class="radio-group-title">Individual Student Event</label>
										<div class="content-small">This event is intended for a specific student or students.</div>
									</td>
								</tr>
								<tr class="event_audience proxy_id_audience">
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
											if (is_array($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
												foreach ($PROCESSED["associated_proxy_ids"] as $student) {
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
									<td colspan="3">&nbsp;</td>
								</tr>
								<?php if ($ENTRADA_ACL->amIAllowed(new EventResource(null, null, $ENTRADA_USER->getActiveOrganisation()),'create')) { ?>
								<tr>
									<td style="vertical-align: top"><input type="radio" name="event_audience_type" id="event_audience_type_organisation_id" value="organisation_id" onclick="selectEventAudienceOption('organisation_id')" style="vertical-align: middle"<?php echo (($PROCESSED["event_audience_type"] == "organisation_id") ? " checked=\"checked\"" : ""); ?> /></td>
									<td colspan="2" style="padding-bottom: 15px">
										<label for="event_audience_type_organisation_id" class="radio-group-title">Entire Organisation Event</label>
										<div class="content-small">This event is intended for every member of an organisation.</div>
									</td>
								</tr>
								<tr class="event_audience organisation_id_audience">
									<td></td>
									<td><label for="associated_organisation_id" class="form-required">Organisation</label></td>
									<td>
										<select id="associated_organisation_id" name="associated_organisation_id" style="width: 203px">
											<?php
											if (is_array($organisation_categories) && count($organisation_categories)) {
												foreach($organisation_categories as $organisation_id => $organisation_info) {
													//echo "<option value=\"".$organisation_id."\"".(($PROCESSED["associated_organisation_id"] == $year) ? " selected=\"selected\"" : "").">".$organisation_info['text']."</option>\n";
													echo "<option value=\"".$organisation_id."\"".(($ORGANISATION_ID == $organisation_id) ? " selected=\"selected\"" : "").">".$organisation_info['text']."</option>\n";
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
									<td colspan="3"><h2>Related Events</h2></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td colspan="2">
										<div id="related_events">
											<?php
												require_once("modules/admin/events/api-related-events.inc.php");
											?>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td colspan="3"><h2>Time Release Options</h2></td>
								</tr>
								<?php echo generate_calendars("viewable", "", true, false, ((isset($PROCESSED["release_date"])) ? $PROCESSED["release_date"] : 0), true, false, ((isset($PROCESSED["release_until"])) ? $PROCESSED["release_until"] : 0)); ?>
								<tr>
									<td colspan="3" style="padding-top: 25px">
										<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
											<tr>
												<td style="width: 25%; text-align: left">
													<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/events'" />
												</td>
												<td style="width: 75%; text-align: right; vertical-align: middle">
													<span class="content-small">After saving:</span>
													<select id="post_action" name="post_action">
														<option value="content"<?php echo (((!isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"])) || ($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "content")) ? " selected=\"selected\"" : ""); ?>>Add content to event</option>
														<option value="new"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "new") ? " selected=\"selected\"" : ""); ?>>Add another event</option>
														<option value="index"<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["post_action"] == "index") ? " selected=\"selected\"" : ""); ?>>Return to event list</option>
													</select>
													<input type="submit" class="button" value="Save" />
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</form>
						<script type="text/javascript">
							function selectEventAudienceOption(type) {
								$$('.event_audience').invoke('hide');
								$$('.'+type+'_audience').invoke('show');
								if(type!== 'proxy_id'){
									checkConflict();
								}
							}
				
							function removeRelatedEvent(event_id) {
								var updater = new Ajax.Updater('related_events', '<?php echo ENTRADA_URL."/admin/events?section=api-related-events";?>',{
									evalScripts: true,
									method:'post',
									parameters: {
										'ajax' : 1,
										'course_id' : $('course_id').value,
										'event_id' : '<?php echo $EVENT_ID; ?>',
										'remove_id' : event_id,
										'related_event_ids_clean' : $F('related_event_ids_clean')
									},
									onLoading: function (transport) {
										$('related_events_list').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
									},
									onComplete: function (transport) {
										generateEventAutocomplete();
									}
								});
							}
				
							function addRelatedEvent(event_id) {
								var updater = new Ajax.Updater('related_events', '<?php echo ENTRADA_URL."/admin/events?section=api-related-events";?>',{
									evalScripts: true,
									method:'post',
									parameters: {
										'ajax' : 1,
										'course_id' : $('course_id').value,
										'event_id' : '<?php echo $EVENT_ID; ?>',
										'add_id' : event_id,
										'related_event_ids_clean' : $F('related_event_ids_clean')
									},
									onLoading: function (transport) {
										$('related_events_list').innerHTML = '<br/><br/><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span>';
									},
									onComplete: function (transport) {
										generateEventAutocomplete();
									}
								});
							}
							
							var prevDate = $('event_start_date').value;
							var prevTime = $('event_start_display').innerHTML;
							var t=self.setInterval("checkDifference()",1500);
								
								
							Event.observe('associated_grad_year','change',checkConflict);
							Event.observe('associated_organisation_id','change',checkConflict);
							Event.observe('student_list','change',checkConflict)
							Event.observe('eventtype_ids','change',checkConflict)
							//Event.observe('event_start_date','keyup',checkConflict);
								
							
							function checkDifference(){
								if($('event_start_date').value !== prevDate){
									prevDate = $('event_start_date').value;
									checkConflict();
								}
								else if($('event_start_display').innerHTML !== prevTime){
									prevTime = $('event_start_display').innerHTML;
									checkConflict();						
								}
							}
							function checkConflict(){
								new Ajax.Request('<?php echo ENTRADA_URL;?>/api/learning-event-conflicts.api.php',
								{
									method:'post',
									parameters: $("editEventForm").serialize(true),
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
							var events_updater = null;
							function generateEventAutocomplete() {
								events_updater = new Ajax.Autocompleter('related_event_id', 'events_autocomplete', 
								'<?php echo ENTRADA_URL; ?>/api/events-by-id.api.php?parent_id='+$('parent_id').value+'&course_id='+$('course_id').options[$('course_id').selectedIndex].value, 
								{
									frequency: 0.2, 
									minChars: 1,
									afterUpdateElement: function (text, li) {
										addRelatedEvent(li.id);
									}
								});
							}
						</script>
						
						<br /><br />
						<?php
					break;
				}
			}
		} else {
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

			$ERROR++;
			$ERRORSTR[] = "In order to edit a event you must provide a valid event identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer when attempting to edit a event.");
		}
	} else {
		$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

		$ERROR++;
		$ERRORSTR[] = "In order to edit a event you must provide the events identifier.";

		echo display_error();

		application_log("notice", "Failed to provide event identifer when attempting to edit a event.");
	}
}

?>
