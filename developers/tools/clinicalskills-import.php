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
 * Clinical Skills Import Events Utility
 *
 * This is a script that you can use to import Clinical Skills events into the system from an
 * ICS calendar file. I fully expect this file to be absolutely useless for anyone else at this point;
 * however, I am including it in case anyone else needs to import events from ICS files so you can see
 * how it is done. In other words, this will not work out of the box for anyone really, but should be
 * used as an example only.
 *
 * Instructions:
 * 0. Backup the databases *always* before importing.
 *
 * 1. Run "./clinicalskills-import.php -update" to download the remote calendar files into the data directory OR
 *    just load the ICS files into the data directory on your own if they are not web-accessible.
 *
 * 2. Run "./clinicalskills-import.php -import" to import the events into your Entrada events, event_audience
 *    and event_contacts table.
 *
 * Note about the ICS files:
 * Please note that ICS summary field should contain CSV data in the following order:
 *
 * unused, title_suffix, group_numbers, teacher_names, staff_numbers
 *
 * unused			The first column is not used by this script, but is useful for the maintainers reference.
 * title_suffix		If you would like the event to have a suffix, you can put it here and if the $ics_files["title_suffix"]
 *					key is true, then whatever you provide in this column will be displayed there.
 * group_numbers	This is a semi-colon delimited list of group numbers that will be used to determine the which
 *					proxy_ids (from the $student_groups array) are added to the event_audience table.
 * teacher_names	This field is not really used by this script, but is useful for the maintainers reference.
 * staff_numbers	This is a semi-colon delimited list of staff numbers that will be added as "Associated Faculty" to
 *					the event_contacts table when this event is imported.
 *
 * Some examples of valid iCalendar event summaries (titles):
 *	- Medicine A, , 1;5;9, James Blunt, 1253233
 *	- Surgery A, GX & FX, 2;3;8, James Blunt; Stew Robinson, 1253233; 8347362
 *	- , , 3;4;7, , 1253233; 8347362
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
 *
 */

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

require_once("classes/icsparser.class.php");
require_once("classes/adodb/adodb.inc.php");
require_once("config.inc.php");
require_once("dbconnection.inc.php");
require_once("functions.inc.php");

$ACTION = ((isset($_SERVER["argv"][1])) ? trim($_SERVER["argv"][1]) : "-usage");

/**
 * student_groups is an array of student numbers and their corresponding group number in
 * a format like: $student_groups[student number] = group number;
 *
 * @example	$student_groups[63718352] = 1;
 *			$student_groups[12523664] = 1;
 *			$student_groups[28162563] = 2;
 *			$student_groups[28162563] = 2;
 */
$student_groups = array();

/**
 * ics_files is an array of the ICS files that you wish to import into the system. The array key will be used in the
 * event_title in the format of "Clinical Skills: Surgery"
 *
 * url			This is the remote URL of the ICS file that will be downloaded to the data directory with -update.
 * filename		The filename of the ICS file when it is downloaded to or read from the data directory.
 * title_suffix	If this is set to true, the second column of the summary field will be used as a suffix
 *				in the title (i.e "Clinical Skills: Surgery Suffix Here")
 */
$ics_files = array();
$ics_files["Surgery"]					= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "surgery.ics");
$ics_files["Anesthesia"]				= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "anesthesia.ics");
$ics_files["Diabetes Education"]		= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "dec.ics");
$ics_files["Emergency Medicine"]		= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "emerg.ics");
$ics_files["Expanded Clinical Skills"]	= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "expanded_cs.ics", "title_suffix" => true);
$ics_files["Family Medicine"]			= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "family_medicine.ics");
$ics_files["Gynecological Teaching"]	= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "gta.ics");
$ics_files["Medicine"]					= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "medicine.ics");
$ics_files["Pediatrics"]				= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "peds.ics", "title_suffix" => true);
$ics_files["Routine Practices"]			= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "routine_practices.ics");
$ics_files["Technical Skills"]			= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "technical_skills.ics");
$ics_files["Urology"]					= array("url" => "http://www.google.com/calendar/ical/basic.ics", "filename" => "urology.ics");

$SUCCESS				= 0;
$NOTICE					= 0;
$ERROR					= 0;

$group_details = array();
foreach ($student_groups as $student_number => $student_group) {
	$group_details[$student_group][] = $student_number;
}

switch($ACTION) {
	case "-update" :
		foreach($ics_files as $ics_name => $ics_file) {
			$contents	= file_get_contents($ics_file["url"]);
			$contents	= str_ireplace(array(";TZID=America/Toronto", "J7\, HDH", "\,", "\;"), array("", "J7 HDH", ",", ";"), $contents);
			file_put_contents("./data/".$ics_file["filename"], $contents);
		}
	break;
	case "-validate" :
	break;
	case "-import" :
		if ((isset($ics_files)) && (is_array($ics_files))) {
			foreach($ics_files as $ics_name => $ics_file) {
				$ics	= new ICSReader("./data/".$ics_file["filename"]);
				$events = $ics->getEvents();

				if ((is_array($events)) && (count($events))) {
					foreach ($events as $key => $event) {
						$timestamp_start	= strtotime(trim($event["DTSTART"]));
						$timestamp_end		= strtotime(trim($event["DTEND"]));
						$event_duration		= (($timestamp_end - $timestamp_start) / 60);
						$event_string		= str_replace(array("\\", ", ", "; "), array("", ",", ";"), $event["SUMMARY"]);
						$col = explode(",", $event_string);

						$event_location		= ((isset($event["LOCATION"]) && ($event["LOCATION"] != "")) ? clean_input($event["LOCATION"]) : "TBA");
						$title_suffix		= ((isset($col[1]) && ($col[1] != "")) ? clean_input($col[1]) : "");
						$group_numbers		= ((isset($col[2]) && ($col[2] != "")) ? preg_replace("/[^0-9\;]+/i", "", $col[2]) : "");
						$staff_numbers		= ((isset($col[4]) && ($col[4] != "")) ? preg_replace("/[^0-9\;]+/i", "", $col[4]) : "");
						$proxy_ids			= array();

						if ((($timestamp_start == 0) || ($timestamp_start >= strtotime("September 1st, 2009")))) {
							$processed_event						= array();
							$processed_event["recurring_id"]		= 0;
							$processed_event["eventtype_id"]		= 8;
							$processed_event["region_id"]			= 0;
							$processed_event["course_id"]			= 67;
							$processed_event["event_phase"]			= "2E";
							$processed_event["event_title"]			= "Clinical Skills: ".$ics_name.(((bool) $ics_file["title_suffix"]) && ($ics_file["title_suffix"] != "") ? " ".$title_suffix : "");
							$processed_event["event_description"]	= "";
							$processed_event["event_goals"]			= "";
							$processed_event["event_objectives"]	= "";
							$processed_event["event_message"]		= "";
							$processed_event["event_location"]		= $event_location;
							$processed_event["event_start"]			= $timestamp_start;
							$processed_event["event_finish"]		= $timestamp_end;
							$processed_event["event_duration"]		= $event_duration;
							$processed_event["release_date"]		= 0;
							$processed_event["release_until"]		= 0;
							$processed_event["updated_date"]		= time();
							$processed_event["updated_by"]			= 1;

							if(($db->AutoExecute("events", $processed_event, "INSERT")) && ($new_event_id = $db->Insert_Id())) {
								output_success("[UID ".$event["UID"]."]\tImported Clinical Ckills event [".$new_event_id."] on ".date("r", $processed_event["event_start"]));

								if ($group_numbers) {
									$pieces = explode(";", $group_numbers);
									if((is_array($pieces)) && (count($pieces))) {
										foreach ($pieces as $group) {
											if (trim($group) != "") {
												if ((isset($group_details[$group])) && (count($group_details[$group]))) {
													foreach ($group_details[$group] as $number) {
														if($number = clean_input($number, array("nows", "int"))) {
															if($proxy_id = get_proxy_id($number)) {
																$processed_audience	= array();
																$processed_audience["event_id"]			= $new_event_id;
																$processed_audience["audience_type"]	= "proxy_id";
																$processed_audience["audience_value"]	= $proxy_id;
																$processed_audience["updated_date"]		= time();
																$processed_audience["updated_by"]		= 1;

																if($db->AutoExecute("event_audience", $processed_audience, "INSERT")) {
																	output_success("[UID ".$event["UID"]."]\tAttached event_id [".$new_event_id."] to student proxy_id ".$proxy_id);
																} else {
																	output_error("[UID ".$event["UID"]."]\tFailed to attached event_id [".$new_event_id."] to student proxy_id ".$proxy_id);
																}
															} else {
																output_error("[UID ".$event["UID"]."]\tFailed to locate valid student proxy_id [".$number."].");
															}
														}
													}
												} else {
													output_error("[UID ".$event["UID"]."]\tGroup [".$group."] does not exist in the group_details array.");
												}
											}
										}
									} else {
										output_notice("[UID ".$event["UID"]."]\tThere are no group numbers for this event, but the group_numbers variable [".$group_numbers."] is not empty.");
									}
								}

								if ($staff_numbers) {
									$pieces = explode(";", $staff_numbers);
									if((is_array($pieces)) && (count($pieces))) {
										foreach($pieces as $order => $number) {
											if($number = clean_input($number, array("nows", "int"))) {
												if($proxy_id = get_proxy_id($number)) {
													$processed_contact = array();
													$processed_contact["event_id"]		= $new_event_id;
													$processed_contact["proxy_id"]		= $proxy_id;
													$processed_contact["contact_order"]	= (int) $order;
													$processed_contact["updated_date"]	= time();
													$processed_contact["updated_by"]	= 1;

													if($db->AutoExecute("event_contacts", $processed_contact, "INSERT")) {
														output_success("[UID ".$event["UID"]."]\tAttached event_id [".$new_event_id."] to teacher proxy_id ".$number);
													} else {
														output_error("[UID ".$event["UID"]."]\tFailed to attached event_id [".$new_event_id."] to teacher proxy_id ".$number);
													}
												} else {
													output_error("[UID ".$event["UID"]."]\tFailed to locate valid teacher proxy_id [".$number."].");
												}
											}
										}
									} else {
										output_notice("[UID ".$event["UID"]."]\tThere are no staff numbers for this event, but the staff_numbers variable [".$staff_numbers."] is not empty.");
									}
								}
							} else {
								output_error("[UID ".$event["UID"]."]\tFailed to insert the event record. Database said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					output_notice("iCalendar filename [".$ics_name."] contains no valid events.");
				}
			}
		}
	break;
	case "-usage" :
	default :
		echo "\nUsage: clinicalskills-import.php [options]";
		echo "\n   -usage           Brings up this help screen.";
		echo "\n   -validate        Validates the import file.";
		echo "\n   -update			Updates the local data directory with updated files.";
		echo "\n   -import          Proceeds with live import of the clerkship events.";
	break;
}
echo "\n\n";
?>