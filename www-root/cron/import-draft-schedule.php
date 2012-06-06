<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for importing draft schedule information into system
 * 
 * Setup to run the end of each week in CRON.
 *
 * @author Organisation: Queen's University
 * @author Unit: Faculty of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's Univerity. All Rights Reserved.
 *
 */
@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
	dirname(__FILE__) . "/../core",
	dirname(__FILE__) . "/../core/includes",
	dirname(__FILE__) . "/../core/library",
	get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

// Fetch approved drafts
$query = "	SELECT `draft_id`
			FROM `drafts`
			WHERE `status` = 'approved'";
$drafts = $db->GetAll($query);
if ($drafts) {
	foreach ($drafts as $draft) {
		// fetch the draft events
		$query = "	SELECT *
					FROM `draft_events`
					WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
		$events = $db->GetAll($query);
		if ($events) {
			foreach ($events as $event) {
				if (empty($event["event_id"])) {
					// if the event has been added to the draft it will have no event_id, so it will need to be inserted.
					if ($db->AutoExecute("`events`", $event, 'INSERT')) {
						// delete event from draft_events
						$query = "DELETE FROM `draft_events` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
						if (!$db->Execute($query)) { 
							$error++;
							application_log("error", "Error deleting draft event [devent_id - ".$event["devent_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
						$event_id = $db->Insert_ID();
					} else {
						$error++;
						application_log("error", "Error inserting event [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
					}
				} else {
					// if the event has been edited in the draft it will have an event_id, so it will need to be updated.
					if ($db->AutoExecute("`events`", $event, 'UPDATE', "`event_id` = ".$db->qstr($event["event_id"]))) {
						// delete event from draft_events
						$query = "DELETE FROM `draft_events` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
						if (!$db->Execute($query)) { 
							$error++;
							application_log("error", "Error deleting draft event [devent_id - ".$event["devent_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
						$event_id = $event["event_id"];
					} else {
						$error++;
						application_log("error", "Error updating event [".$event["event_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
					}
				}
				
				// remove the eventtypes associated with the event
				$query = "	DELETE FROM `event_eventtypes`
							WHERE `event_id` = ".$db->qstr($event["event_id"]);
				if ($db->Execute($query)) {
					// add the eventtypes associated with the draft event
					$query = "	SELECT *
								FROM `draft_eventtypes`
								WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
					$eventtypes = $db->GetAll($query);
					if ($eventtypes) {
						foreach ($eventtypes as $eventtype) {
							if ($db->AutoExecute("`event_eventtypes`", array("event_id" => $event_id, "eventtype_id" => $eventtype["eventtype_id"], "duration" => $eventtype["duration"]), "INSERT")) {
								// delete contents of draft_eventtypes
								$query = "DELETE FROM `draft_eventtypes` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if (!$db->Execute($query)) { 
									$error++;
									application_log("error", "Error deleting draft eventtype [deventtype_id - ".$eventtype["deventtype_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
								}
							} else {
								$error++;
								application_log("error", "Error inserting event_eventtype [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					$error++;
					application_log("error", "Error deleting eventtypes for [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
				}
				
				// remove the event_contacts associated with the event
				$query = "	DELETE FROM `event_contacts`
							WHERE `event_id` = ".$db->qstr($event["event_id"]);
				if ($db->Execute($query)) {
					// add the eventtypes associated with the draft event
					$query = "	SELECT *
								FROM `draft_contacts`
								WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
					$eventcontacts = $db->GetAll($query);
					if ($eventcontacts) {
						foreach ($eventcontacts as $contact) {
							if ($db->AutoExecute("`event_contacts`", array("event_id" => $event_id, "proxy_id" => $contact["proxy_id"], "contact_role" => $contact["contact_role"], "contact_order" => $contact["contact_order"], "updated_date" => time(), "updated_by" => $contact["updated_by"]), "INSERT")) {
								// delete contents of draft_contacts
								$query = "DELETE FROM `draft_contacts` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if (!$db->Execute($query)) { 
									$error++;
									application_log("error", "Error deleting draft event contact [dcontact_id - ".$contact["dcontact_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
								}
							} else {
								$error++;
								application_log("error", "Error inserting event_contact [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					$error++;
					application_log("error", "Error deleting event_contacts for [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
				}
				
				// remove the event_contacts associated with the event
				$query = "	DELETE FROM `event_audience`
							WHERE `event_id` = ".$db->qstr($event["event_id"]);
				if ($db->Execute($query)) {
					// add the eventtypes associated with the draft event
					$query = "	SELECT *
								FROM `draft_audience`
								WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
					$eventaudience = $db->GetAll($query);
					if ($eventaudience) {
						foreach ($eventaudience as $audience) {
							if ($db->AutoExecute("`event_audience`", array("event_id" => $event_id, "audience_type" => $audience["audience_type"], "audience_value" => $audience["audience_value"], "updated_date" => time(), "updated_by" => $audience["updated_by"]), "INSERT")) {
								// delete contents of draft_audience
								$query = "DELETE FROM `draft_audience` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
								if (!$db->Execute($query)) { 
									$error++;
									application_log("error", "Error deleting draft event audience [daudience_id - ".$audience["dcontact_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
								}
							} else {
								$error++;
								application_log("error", "Error inserting event_audience [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					$error++;
					application_log("error", "Error deleting event audience for [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
				}
				
				if (!$error) {
					$count++;
				}
			}
		}
		
		if (!$error) {
			// delete the draft creators
			$query = "DELETE FROM `draft_creators` WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
			if ($db->Execute($query)) {
				$query = "DELETE FROM `drafts` WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
				if ($db->Execute($query)) {
					// draft successfully imported and draft deleted from drafts tables
					application_log("success", "Successfully imported draft [draft_id-".$draft["draft_id"]."]. ".$count." records imported.");
				} else {
					// something went wrong
					application_log("error", "Failed to import draft [draft_id-".$draft["draft_id"]."], DB said: ".$db->ErrorMsg());
				}
			} else { 
				// failed to delete draft_creators entries
				application_log("error", "Failed to import draft [draft_id-".$draft["draft_id"]."], DB said: ".$db->ErrorMsg());
			}
		}
	}
}