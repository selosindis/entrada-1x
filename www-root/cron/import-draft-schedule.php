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
$query = "	SELECT *
			FROM `drafts`
			WHERE `status` = 'approved'";
$drafts = $db->GetAll($query);
if ($drafts) {
	foreach ($drafts as $draft) {
		$msg[$draft["draft_id"]][] = "Imported draft: \"".$draft["draf_title"]."\" on ".date("Y-m-d H:i", time());
		$notification_events = "";
		// fetch the draft events
		
		$query = "	SELECT a.`proxy_id`, CONCAT(b.`firstname`, ' ', b.`lastname`) AS `name`, b.`email`
					FROM `draft_creators` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
		$draft_creators = $db->GetAll($query);
		
		$query = "	SELECT *
					FROM `draft_events`
					WHERE `draft_id` = ".$db->qstr($draft["draft_id"]);
		$events = $db->GetAll($query);
		if ($events) {
			foreach ($events as $event) {

				if ($event["event_id"]) {
					$old_event_id = $event["event_id"];
					unset($event["event_id"]);
				}
				
				$event["updated_date"]	= time();
				$event["updated_by"]	= $draft_creators[0]["proxy_id"];
				
				if ($db->AutoExecute("`events`", $event, 'INSERT')) {
					$event_id = $db->Insert_ID();
					
					// delete event from draft_events
					$query = "DELETE FROM `draft_events` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
					if (!$db->Execute($query)) { 
						$error++;
						application_log("error", "Error deleting draft event [devent_id - ".$event["devent_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
					}
				} else {
					$error++;
					application_log("error", "Error inserting event [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
				}
					
				$notification_events .= $event["event_title"]." at ".date("Y-m-d H:i", $event["event_start"])."\n";
				
				// add the eventtypes associated with the draft event
				$query = "	SELECT *
							FROM `draft_eventtypes`
							WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
				$eventtypes = $db->GetAll($query);
				if ($eventtypes) {
					foreach ($eventtypes as $eventtype) {
						$eventtype["event_id"]		= $event_id;
						unset($eventtype["deventtype_id"]);
						unset($eventtype["eeventtype_id"]);
						unset($eventtype["devent_id"]);
						if ($db->AutoExecute("`event_eventtypes`", $eventtype, "INSERT")) {
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


				// add the event contacts associated with the draft event
				$query = "	SELECT *
							FROM `draft_contacts`
							WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
				$eventcontacts = $db->GetAll($query);

				if ($eventcontacts) {
					foreach ($eventcontacts as $contact) {
						$contact["event_id"] = $event_id;
						$contact["updated_date"] = time();
						$contact["updated_by"] =  $draft_creators[0]["proxy_id"];
						unset($contact["dcontact_id"]);
						unset($contact["econtact_id"]);
						unset($contact["devent_id"]);
						if ($db->AutoExecute("`event_contacts`", $contact, "INSERT")) {
							// delete contents of draft_contacts
							$query = "DELETE FROM `draft_contacts` WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
							if (!$db->Execute($query)) { 
								$error++;
								application_log("error", "Error deleting draft event contact [dcontact_id - ".$contact["dcontact_id"]."] on draft schedule import. DB said: ".$db->ErrorMsg());
							} else {
								$msg[$draft["draft_id"]]["contacts"][$contact["proxy_id"]][] = $contact["email"];
								$msg[$draft["draft_id"]]["contacts"][$contact["proxy_id"]][] = $contact["fullname"];
							}
						} else {
							$error++;
							application_log("error", "Error inserting event_contact [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}

				// add the event audience associated with the draft event
				$query = "	SELECT *
							FROM `draft_audience`
							WHERE `devent_id` = ".$db->qstr($event["devent_id"]);
				$eventaudience = $db->GetAll($query);
				if ($eventaudience) {
					foreach ($eventaudience as $audience) {
						
						$audience["event_id"] = $event_id;
						$audience["updated_date"] = time();
						$audience["updated_by"] =  $draft_creators[0]["proxy_id"];
						unset($audience["daudience_id"]);
						unset($audience["eaudience_id"]);
						unset($audience["devent_id"]);
						
						if ($db->AutoExecute("`event_audience`", $audience, "INSERT")) {
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
				
				// add the event files associated with the event
				$query = "	SELECT *
							FROM `event_files`
							WHERE `event_id` = ".$db->qstr($old_event_id);
				$event_files = $db->GetAll($query);
				if ($event_files) {
					foreach ($event_files as $file) {
						unset($file["efile_id"]);
						$file["event_id"]		= $event_id;
						$file["updated_date"]	= time();
						$file["updated_by"]		= $draft_creators[0]["proxy_id"];
						if (!$db->AutoExecute("`event_files`", $file, "INSERT")) {
							$error++;
							application_log("error", "Error inserting event_files [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}


				// add the event links associated with the event
				$query = "	SELECT *
							FROM `event_links`
							WHERE `event_id` = ".$db->qstr($old_event_id);
				$event_links = $db->GetAll($query);
				if ($event_links) {
					foreach ($event_links as $link) {
						unset($link["elink_id"]);
						$link["event_id"]		= $event_id;
						$link["updated_date"]	= time();
						$file["updated_by"]		= $draft_creators[0]["proxy_id"];
						if (!$db->AutoExecute("`event_links`", $link, "INSERT")) {
							$error++;
							application_log("error", "Error inserting event_links [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}

				// add the event objectives associated with the draft event
				$query = "	SELECT *
							FROM `event_objectives`
							WHERE `event_id` = ".$db->qstr($old_event_id);
				$event_objectives = $db->GetAll($query);
				if ($event_objectives) {
					foreach ($event_objectives as $objective) {
						unset($objective["eobjective_id"]);
						$objective["event_id"]		= $event_id;
						$objective["updated_date"]	= time();
						$objective["updated_by"]	= $draft_creators[0]["proxy_id"];
						if (!$db->AutoExecute("`event_objectives`", $objective, "INSERT")) {
							$error++;
							application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}

				// add the event objectives associated with the draft event
				$query = "	SELECT *
							FROM `event_topics`
							WHERE `event_id` = ".$db->qstr($old_event_id);
				$event_topics = $db->GetAll($query);
				if ($event_topics) {
					foreach ($event_topics as $topic) {
						unset($topic["eobjective_id"]);
						$topic["event_id"]		= $event_id;
						$topic["updated_date"]	= time();
						$topic["updated_by"]	= $draft_creators[0]["proxy_id"];
						if (!$db->AutoExecute("`event_objectives`", $topic, "INSERT")) {
							$error++;
							application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}
				
				// add the event objectives associated with the draft event
				$query = "	SELECT *
							FROM `attached_quizzes`
							WHERE `content_type` = 'event'
							AND `content_id` = ".$db->qstr($old_event_id);
				$event_quizzes = $db->GetAll($query);
				if ($event_quizzes) {
					foreach ($event_quizzes as $quiz) {
						unset($quiz["aquiz_id"]);
						$quiz["content_id"]		= $event_id;
						$quiz["updated_date"]	= time();
						$quiz["updated_by"]	= $draft_creators[0]["proxy_id"];
						if (!$db->AutoExecute("`attached_quizzes`", $quiz, "INSERT")) {
							$error++;
							application_log("error", "Error inserting event_objectives [".$event_id."] on draft schedule import. DB said: ".$db->ErrorMsg());
						}
					}
				}
				
				if (!$error) {
					$count++;
					$msg[$draft["draft_id"]][] = $event["event_title"]." - ".date("Y-m-d H:i",$event["event_start"]);
				}
			}
		}
		
		if (!$error) {
			// notify the draft creators that their draft has been imported
			$message = "This email is to notify you that the draft learning event schedule \"".$draft["name"]."\" was successfully imported on ".date("Y-m-d H:i", time()).".\n\n";
			$message .= "The following learning events were imported into the system:\n";
			$message .= "------------------------------------------------------------\n\n";
			$message .= $notification_events;

			if ($draft_creators) {
				$mail = new Zend_Mail();
				$mail->addHeader("X-Section", "Learning Events Notification System", true);
				$mail->setFrom($AGENT_CONTACTS["administrator"]["email"], $AGENT_CONTACTS["administrator"]["name"]);
				$mail->clearSubject();
				$mail->setSubject("Draft Learning Event Schedule Imported");
				$mail->setBodyText($message);
				$mail->clearRecipients();
				
				foreach ($draft_creators as $result) {
					$mail->addTo($result["email"], $result["name"]);
				}
				
				$mail->send();
			} else {
				$errors[] = "<pre>no draft creators...</pre>";
			}
			
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