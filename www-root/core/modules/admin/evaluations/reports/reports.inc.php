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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/", "title" => "Evaluation Reports");
	
	if(isset($_GET["evaluation"]))  {
		$EVALUATIONS[] =  trim($_GET["evaluation"]);
	} elseif((!isset($_POST["checked"])) || (!is_array($_POST["checked"])) || (!@count($_POST["checked"]))) {
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	} else {
		foreach($_POST["checked"] as $evaluation) {
			$evaluation = trim($evaluation);
			if($evaluation) {
				$EVALUATIONS[] = $evaluation;
			}
		}
		if(!@count($EVALUATIONS)) {
			$ERROR++;
			$ERRORSTR[] = "There were no valid evaluation identifiers provided to copy. Please ensure that you access this section through the event index.";
			echo display_error();
		}
	}

	foreach($EVALUATIONS as $evaluation){
		list($evaluation_id, $target_id, $object_id) = explode(':',$evaluation);

		$target = $db->GetOne("SELECT `target_shortname` FROM `evaluations_lu_targets` WHERE `target_id` = ".$db->qstr($target_id));

		switch($target) {
			case "course" :
				$query = "	SELECT e.*, f.`eform_id` form_id, f.`form_title`, f.`form_description`, c.`course_id`, c.`organisation_id`, c.`course_name`, c.`course_code` FROM `evaluations` e 
							LEFT JOIN `evaluation_forms` f ON e.`eform_id` = f.`eform_id`
							INNER JOIN `evaluation_targets` t ON e.`evaluation_id` = t.`evaluation_id`
							LEFT JOIN `courses` c ON t.`target_value` = c.`course_id`
							WHERE e.`evaluation_id` = ".$db->qstr($evaluation_id)."
							AND t.`target_value` = ".$db->qstr($object_id)."
							AND f.`form_active` = 1";
				$results	= $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
				/*echo "<br>$query<br>"; */print_r($results);
			break;
		
		}
	}
}
?>