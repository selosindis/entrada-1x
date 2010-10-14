<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Functions for tasks module that need to be accessible by both admin and public sections of the module
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
*/

 
/**
 * Sends email based on the specified type using templates from TEMPLATE_ABSOLUTE/email directory
 * @param string $type One of "confirm", "request", "denial"
 * @param array $to associative array consisting of firstname, lastname, and email
 * @param array $keywords Associative array of keywords mapped to the replacement contents
 */
function task_verification_notification($type="",$to = array(), $keywords = array()) {
	global $AGENT_CONTACTS;
	if (!is_array($to) || !isset($to["email"]) || !valid_address($to["email"]) || !isset($to["firstname"]) || !isset($to["lastname"])) {
		application_log("error", "Attempting to send a task_verification_notification() however the recipient information was not complete.");
		
		return false;
	}
	
	if (!in_array($type, array("confirm", "request", "denial"))) {
		application_log("error", "Encountered an unrecognized notification type [".$type."] when attempting to send a task_verification_notification().");

		return false;
	}
	
	
	$xml_file = TEMPLATE_ABSOLUTE."/email/task-verification-".$type.".xml";
	
	try {
		require_once("Models/utility/Template.class.php");
		require_once("Models/utility/TemplateMailer.class.php");
		$template = new Template($xml_file);
		$mail = new TemplateMailer(new Zend_Mail());
		$mail->addHeader("X-Section", "Tasks Module", true);
		
		$from = array("email"=>$AGENT_CONTACTS["agent-notifications"]["email"], "firstname"=> "Task System","lastname"=>"");
		if ($mail->send($template,$to,$from,DEFAULT_LANGUAGE,$keywords)) {
			return true;
		} else {
			add_notice("We were unable to e-mail a task notification <strong>".$to["email"]."</strong>.<br /><br />A system administrator was notified of this issue, but you may wish to contact this individual manually and let them know their task verification status.");
			application_log("error", "Unable to send task verification notification to [".$to["email"]."] / type [".$type."]. Zend_Mail said: ".$mail->ErrorInfo);
		}
					
	} catch (Exception $e) {
		application_log("error", "Unable to load the XML file [".$xml_file."] or the XML file did not contain the language requested [".DEFAULT_LANGUAGE."], when attempting to send a regional education notification.");
	}

	return false;
}

/**
 * Generates the list of successful verifications when verifying task completion in bulk. 
 * @param array $task_successes 2-dimensional array consisting of "task name" => array_of_recipients pairs
 */
function generate_bulk_task_verify_success_list($task_successes) {
	$success_listing = "";
	foreach($task_successes as $task_title=>$recipients) {
		$success_listing .= "<div class='success_task'><span class='task_title'>".html_encode($task_title)."</span><ul>";
		foreach ($recipients as $recipient) {
			$success_listing .= "<li>".$recipient."</li>";
		}
		$success_listing .= "</ul></div>";
	}
	return $success_listing;
}