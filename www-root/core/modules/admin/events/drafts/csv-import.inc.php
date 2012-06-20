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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else { 

	require_once(ENTRADA_ABSOLUTE."/core/library/Models/events/drafts/CsvImporter.class.php");
	
	$draft_id = (int) $_GET["draft_id"];
	
	$query = "SELECT * FROM `drafts` WHERE `draft_id` = ".$db->qstr($draft_id);
	$result = $db->GetRow($query);
	
	echo "<h1>Import Draft Learning Events from CSV</h1>";
	
	if ($result["status"] == "open") {
		$csv_importer = new CsvImporter($draft_id, $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
		$csv_importer->importCsv($_FILES["csv_file"]);

		if ($errors = $csv_importer->getErrors()) {
			$err_msg = "The following errors occured while importing <strong>".$_FILES["csv_file"]["name"]."</strong>. Please correct the errors and import them from a new CSV file.<br /><br />";
			foreach ($errors as $rowid => $error) {
				$err_msg .= "Row <strong>".$rowid."</strong> contained the following errors and was not imported:</strong><br />";
				foreach ($error as $msg) {
					$err_msg .= html_encode($msg)."<br />";
				}
			}
			$err_msg .= "<br />Please <a href=\"".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> to return to the edit draft page.";
			add_error($err_msg);
			echo display_error();
		}
		if ($success = $csv_importer->getSuccess()) {
			add_success("Successfully imported <strong>".count($success)."</strong> events from <strong>".$_FILES["csv_file"]["name"]."</strong>".
						(!isset($errors) ? "<br /><br />You will now be redirected to the edit draft page; this will happen automatically in 5 seconds or <a href=\"".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\">click here</a> to continue." : "" ));
			echo display_success();
			if (!isset($errors)) {
				$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\\'', 5000)";
			}
		}
	} else {
		add_error("The specified draft is not available.");
		echo display_error();
		$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts\'', 5000)";
	}
}