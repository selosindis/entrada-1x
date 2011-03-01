#!/usr/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * Export Events Utility
 * 
 * This is a script that you can use to export events from the entrada database
 * to a CSV file.
 * 
 * Instructions:
 * 1. Simply run "./schedule-export.php" to export all events to a CSV file.
 *    
 *    The CSV file will be named schedule-export-YYYY-MM-DD.csv and will be
 *    saved to the tools/data directory.
 * 
 * 2. If you would like to export certain dates, you can do this as well by
 *    providing the start date as the first attribute and the end date as the
 *    second attribute.
 *    
 *    Example: "./schedule-export.php 2008-09-01 2009-05-31"
 *   
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

echo "\n\n";
echo "@todo\n\n";
echo "Before you run this to you have to change event_duration\n\n";
echo "and eventtype_title to a semi-colon delimited list.\n\n";
exit;

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only.";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

require_once("classes/adodb/adodb.inc.php");
require_once("config.inc.php");
require_once("dbconnection.inc.php");
require_once("functions.inc.php");


$START_DATE		= ((isset($_SERVER["argv"][1]) && (trim($_SERVER["argv"][1]))) ? strtotime(trim($_SERVER["argv"][1])." 00:00:00") : mktime(0, 0, 0, 9, 1, date("Y")));
$END_DATE		= ((isset($_SERVER["argv"][2]) && (trim($_SERVER["argv"][2]))) ? strtotime(trim($_SERVER["argv"][2])." 23:59:59") : strtotime("+1 year", ($START_DATE - 1)));

$CSV_HEADINGS	= array("Event ID", "Phase", "Grad Class", "Course Num", "Course / Unit Name", "Date", "Start Time", "Event Duration", "Event Type", "Event Title", "Event Location", "Teacher Staff Number(s)", "Teacher Name(s)");

$OUTPUT_FILE	= dirname(__FILE__)."/data/schedule-export_".date("Y-m-d").".csv"; 

$total_events	= 0;
$total_errors	= 0;

$query		= "
			SELECT a.*, b.`audience_value` AS `event_grad_year`, c.`course_name`, d.`eventtype_title`
			FROM `events` AS a
			LEFT JOIN `event_audience` AS b
			ON b.`event_id` = a.`event_id`
			LEFT JOIN `courses` AS c
			ON c.`course_id` = a.`course_id`
			LEFT JOIN `events_lu_eventtypes` AS d
			ON d.`eventtype_id` = a.`eventtype_id`
			WHERE a.`event_start` BETWEEN ".$db->qstr($START_DATE)." AND ".$db->qstr($END_DATE)."
			AND b.`audience_type` = 'grad_year'
			ORDER BY a.`event_phase` ASC, a.`event_start` ASC";
$results	= $db->GetAll($query);
if($results) {
	$handle = fopen($OUTPUT_FILE, "w+");
	if($handle) {
		fputcsv($handle, $CSV_HEADINGS);
			
		foreach($results as $result) {
			$staff_number	= array();
			$staff_names	= array();
			
			$query		= "SELECT * FROM `event_contacts` WHERE `event_id` = ".$db->qstr($result["event_id"])." ORDER BY `contact_order` ASC";
			$sresults	= $db->GetAll($query);
			if($sresults) {
				foreach($sresults as $sresult) {
					if($info = get_user_info($sresult["proxy_id"])) {
						$staff_number[$sresult["proxy_id"]]	= $info["number"];
						$staff_names[$sresult["proxy_id"]]	= $info["firstname"]." ".$info["lastname"];
					}
				}
			}
			
			$row	= array();
			$row[]	= $result["event_id"];							// Event ID
			$row[]	= stripslashes($result["event_phase"]);			// Phase
			$row[]	= stripslashes($result["event_grad_year"]);		// Grad Class
			$row[]	= stripslashes($result["course_num"]);			// Course Num
			$row[]	= stripslashes($result["course_name"]);			// Course / Unit Name
			$row[]	= date("Y-m-d", $result["event_start"]);		// Date
			$row[]	= date("H:i", $result["event_start"]);			// Start Time
			$row[]	= stripslashes($result["event_duration"]);		// Duration
			$row[]	= stripslashes($result["eventtype_title"]);		// Event Type Title
			$row[]	= stripslashes($result["event_title"]);			// Event Title
			$row[]	= stripslashes($result["event_location"]);		// Location
			$row[]	= stripslashes(implode("; ", $staff_number));	// Teacher Staff Number(s)
			$row[]	= stripslashes(implode("; ", $staff_names));	// Teacher Name(s)
	
			if(fputcsv($handle, $row)) {
				$total_events++;
			} else {
				$total_errors++;	
			}
		}
		
		fclose($handle);
	} else {
		echo "\n\nWARNING: Unable to write data to your output file, please ensure this directory is writable by PHP.";
		echo "\n".$OUTPUT_FILE;
	}
}

if($total_events) {
	if($total_errors) {
		echo "\n\nWARNING: Unable to save ".$total_errors." row".(($total_errors != 1) ? "s" : "")." to the export file.\n";	
	}
	
	echo "\n".$total_events." Learning Event".(($total_events != 1) ? "s" : "")." from ".date("Y-m-d", $START_DATE)." until ".date("Y-m-d", $END_DATE)." have been saved to:";	
	echo "\n".$OUTPUT_FILE;
} else {
	echo "\n\nWARNING: There were no Learning Events in the system from ".date("Y-m-d", $START_DATE)." until ".date("Y-m-d", $END_DATE);
}
echo "\n\n================================================================\n";
?>