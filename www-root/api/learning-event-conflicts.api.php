<?php

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

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	
	if (!isset($_POST)) {
		echo "No date selected. Please select a date for the event";
		die();
	}
	
	
	$date = validate_calendars("event", true,false);
	
	$length = 0;
	foreach($_POST["duration_segment"] as $segment){
		$cln_seg = clean_input($segment, array("trim", "int"));
		$length += $cln_seg;
	}
	
	$start_time = $date["start"];
	$finish_time = $start_time + ($length * 60);
	
	$audience_type = clean_input($_POST["event_audience_type"], array("notags","trim"));
	$event_id = (int)clean_input($_POST["event_id"],array("trim","int"));
	
	$query = "	SELECT * FROM `events`  AS a JOIN `event_audience` AS b ON a.`event_id` = b.`event_id`
			WHERE (" . $start_time . " BETWEEN `event_start` AND `event_finish` 
			OR " . $finish_time . " BETWEEN `event_start` AND `event_finish`)";
	
	switch ($audience_type) {
		case "grad_year":
			$grad_year = isset($_POST["associated_grad_year"])?clean_input($_POST["associated_grad_year"],array("notags","trim")):0;
			$query .= "AND b.audience_type = 'grad_year' AND b.`audience_value` = ".$db->qstr($grad_year);
			break;
		case "proxy_id":
			$proxy_ids = isset($_POST["associated_student"])?clean_input($_POST["associated_student"],array("notags","trim")):0;
			$query .= "AND b.audience_type = 'proxy_id' AND b.`audience_value` IN(".$proxy_ids.")";
			break;
		case "organisation_id":
			$org_id = isset($_POST["associated_organisation_id"])?clean_input($_POST["associated_organisation_id"],array("notags","trim")):0;
			$query .= "AND b.audience_type = 'organisation_id' AND b.`audience_value` IN(".$org_id.")";
			break;
		default:
			$query .= "AND 1=2";
			break;
	}
	
	$query .= " AND a.`event_id` != ".$event_id;
	
	$results = $db->GetAll($query);

	if ($results) {
		echo "This date is in conflict with existing events being attended by your selected audience. Please ensure you still want to select this timeframe.<br/>";
		foreach ($results as $result) {
			echo "<a href=\"".ENTRADA_RELATIVE."/events?id=" . $result["event_id"] . "\">" . $result["event_title"] . "</a><br/>";
		}
	}

}
?>
