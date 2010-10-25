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
 * Import Events Utility
 * 
 * This is a script that you can use to import learning events into the
 * database. You can either import a standard CSV file (example provided in
 * import_events_example.csv) or you can import an updated version of a file
 * that was exported using the provided schedule-export.php tool.
 * 
 * Instructions:
 * 0. Backup the file system and databases *always* before importing.
 * 
 * 1. Run "./schedule-import.php -validate /path/to/file.csv" to validate all of
 *    the data in the rows of your CSV file.
 *
 * 2. Run "./schedule-import.php -testimport /path/to/file.csv " to run a data
 *    entry test. WARNING! This will make changes to your database and should be
 *    used prior to importing to production to ensure there are no SQL errors.
 *    This will not actually copy the files from the file system.
 *    
 * 3. After you have run -validate, and -testimport you can run
 *    "./schedule-import.php -import /path/to/file.csv", which will actually
 *    create all of the new entries in the entrada.events table as well as
 *    copy any of the files from copied learning events.
 *    
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("magic_quotes_runtime", 0);
set_time_limit(0);

if ((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
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

$ENABLE_LOGGING			= true;		// true | false to enable | disable logging.

$COPY_EVENT_ED10		= false;	// Copy ED10 data from old events.
$COPY_EVENT_ED11		= false;	// Copy ED11 data from old events.
$COPY_EVENT_FILES		= true;		// Copy event files from old events.
$COPY_EVENT_LINKS		= true;		// Copy event links from old events.
$COPY_EVENT_OBJECTIVES	= true;		// Copy event objectives from old events.

$ACTION					= ((isset($_SERVER["argv"][1])) ? trim($_SERVER["argv"][1]) : "-usage");
$CSV_FILE				= (((isset($_SERVER["argv"][2])) && (trim($_SERVER["argv"][2]) != "")) ? trim($_SERVER["argv"][2]) : false);

$SKIP_FILE_COPY			= false;	// Do not change this, it is set with the -testimport option.
$LOG_FILENAME			= dirname(__FILE__)."/data/".basename(__FILE__)."_".date("Y-m-d")."_log.txt";

$SUCCESS				= 0;
$NOTICE					= 0;
$ERROR					= 0;

switch ($ACTION) {
	case "-testimport" :
		$SKIP_FILE_COPY = true;

	case "-import" :
		$handle = fopen($CSV_FILE, "r");
		if ($handle) {
			$row_count	= 0;
			
			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;
				
				/**
				 * We do not want the first row to be imported because it should
				 * be the CSV heading titles.
				 */
				if ($row_count > 1) {
					$event_ids			= array();
					$proxy_ids			= array();
		
					$event_id			= ((isset($row[0])) ? clean_input($row[0]) : "");
					$event_phase		= ((isset($row[1])) ? clean_input($row[1]) : "");
					$event_grad_year	= ((isset($row[2])) ? clean_input($row[2]) : "");
					$course_num			= ((isset($row[3])) ? clean_input($row[3]) : "");
					$course_name		= ((isset($row[4])) ? clean_input($row[4]) : "");
					$event_date			= ((isset($row[5])) ? clean_input($row[5]) : "");
					$event_start_time	= ((isset($row[6])) ? clean_input($row[6]) : "");
					$event_duration		= ((isset($row[7])) ? clean_input($row[7]) : 0);
					$event_type			= ((isset($row[8])) ? clean_input($row[8]) : "");
					$event_title		= ((isset($row[9])) ? clean_input($row[9]) : "");
					$event_location		= ((isset($row[10]) && ($row[10] != "")) ? clean_input($row[10]) : "TBA");
					$staff_numbers		= ((isset($row[11])) ? clean_input($row[11]) : "");
					
					/**
					 * Validation for provided previous lecture ids.
					 */
					if ($event_id) {
						$pieces = explode(";", $event_id);
						if ((is_array($pieces)) && (count($pieces))) {
							foreach ($pieces as $tmp_event_id) {
								if (($tmp_event_id = clean_input($tmp_event_id, array("nows", "int"))) && (validate_event_id($tmp_event_id))) {
									$event_ids[] = $tmp_event_id;
								}
							}
						}
					}

					if (trim($event_type) == "") {
						$event_type = "Other";
					}

					if (trim($course_name) == "") {
						$course_name = "General Events";
					}

					/**
					 * Validation for provided staff ids.
					 */
					if ($staff_numbers) {
						$pieces = explode(";", $staff_numbers);
						if ((is_array($pieces)) && (count($pieces))) {
							foreach ($pieces as $tmp_staff_number) {
								if ($tmp_staff_number = clean_input($tmp_staff_number, array("nows", "int"))) {
									if ($proxy_id = get_proxy_id($tmp_staff_number)) {
										$proxy_ids[] = $proxy_id;
									}
								}
							}
						}
					}
	
					$historical								= get_event_data($event_ids);
	
					$processed_event						= array();
					$processed_event["recurring_id"]		= 0;
					$processed_event["eventtype_id"]		= get_eventtype_id($event_type);
					$processed_event["region_id"]			= ((isset($historical["region_id"])) ? $historical["region_id"] : 0);
					$processed_event["course_id"]			= get_course_id($course_name);
					$processed_event["course_num"]			= $course_num;
					$processed_event["event_phase"]			= $event_phase;
					$processed_event["event_title"]			= $event_title;
					$processed_event["event_description"]	= ((isset($historical["event_description"])) ? $historical["event_description"] : "");
					$processed_event["event_goals"]			= ((isset($historical["event_goals"])) ? $historical["event_goals"] : "");
					$processed_event["event_objectives"]	= ((isset($historical["event_objectives"])) ? $historical["event_objectives"] : "");
					$processed_event["event_message"]		= ((isset($historical["event_message"])) ? $historical["event_message"] : "");
					$processed_event["event_location"]		= $event_location;
					$processed_event["event_start"]			= strtotime($event_date." ".$event_start_time);
					$processed_event["event_finish"]		= ($processed_event["event_start"] + ($event_duration * 60));
					$processed_event["event_duration"]		= $event_duration;
					$processed_event["release_date"]		= ((isset($historical["release_date"])) ? (int) $historical["release_date"] : 0);
					$processed_event["release_until"]		= ((isset($historical["release_until"])) ? (int) $historical["release_until"] : 0);
					$processed_event["updated_date"]		= time();
					$processed_event["updated_by"]			= 1;
		
					if (($db->AutoExecute("events", $processed_event, "INSERT")) && ($new_event_id = $db->Insert_Id())) {
						output_success("[Row ".$row_count."]\tImported learning event for phase ".$event_phase." [".$new_event_id."] on ".date("r", $processed_event["event_start"]));
							
						/**
						 * Attach graduating class information to new event.
						 */
						$processed_audience						= array();
						$processed_audience["event_id"]			= $new_event_id;
						$processed_audience["audience_type"]	= "grad_year";
						$processed_audience["audience_value"]	= $event_grad_year;
						$processed_audience["updated_date"]		= time();
						$processed_audience["updated_by"]		= 1;
						
						if ($db->AutoExecute("event_audience", $processed_audience, "INSERT")) {
							output_success("[Row ".$row_count."]\tAttached event_id [".$new_event_id."] to class of ".$event_grad_year);

							/**
							 * Attach teachers to new event.
							 */
							if (count($proxy_ids)) {
								foreach ($proxy_ids as $key => $proxy_id) {
									if ($db->AutoExecute("event_contacts", array("event_id" => $new_event_id, "proxy_id" => $proxy_id, "contact_order" => $key, "updated_date" => time(), "updated_by" => 1), "INSERT")) {
										output_success("[Row ".$row_count."]\tAttached proxy_id [".$proxy_id."] to event_id [".$new_event_id."] as teacher #".$key.".");
									}
								}
							}

							if (count($event_ids)) {
								foreach ($event_ids as $old_event_id) {
									$historical	= get_event_data($old_event_id);

									/**
									 * Attach any ED10 data from the previous event to this new event.
									 */
									if ($COPY_EVENT_ED10) {
										$query		= "SELECT * FROM `event_ed10` WHERE `event_id` = ".$db->qstr($old_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_ed10	= array();
											
											foreach ($results as $result) {
												if (!in_array($result["ed10_id"], $copied_ed10)) {
													$processed_ed10					= array();
													$processed_ed10["event_id"]		= $new_event_id;
													$processed_ed10["ed10_id"]		= $result["ed10_id"];
													$processed_ed10["major_topic"]	= $result["major_topic"];
													$processed_ed10["minor_topic"]	= $result["minor_topic"];
													$processed_ed10["minor_desc"]	= $result["minor_desc"];
													$processed_ed10["updated_date"]	= $result["updated_date"];
													$processed_ed10["updated_by"]	= $result["updated_by"];
	
													if (($db->AutoExecute("event_ed10", $processed_ed10, "INSERT")) && ($new_eed10_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied ed10_id [".$processed_ed10["ed10_id"]."] to new event_id [".$new_event_id."].");
														
														$copied_ed10[] = $processed_ed10["ed10_id"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy ed10_id [".$processed_ed10["ed10_id"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
									
									/**
									 * Attach any ED11 data from the previous event to this new event.
									 */
									if ($COPY_EVENT_ED11) {
										$query		= "SELECT * FROM `event_ed11` WHERE `event_id` = ".$db->qstr($old_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_ed11	= array();
											
											foreach ($results as $result) {
												if (!in_array($result["ed11_id"], $copied_ed11)) {
													$processed_ed11					= array();
													$processed_ed11["event_id"]		= $new_event_id;
													$processed_ed11["ed11_id"]		= $result["ed11_id"];
													$processed_ed11["major_topic"]	= $result["major_topic"];
													$processed_ed11["minor_topic"]	= $result["minor_topic"];
													$processed_ed11["minor_desc"]	= $result["minor_desc"];
													$processed_ed11["updated_date"]	= $result["updated_date"];
													$processed_ed11["updated_by"]	= $result["updated_by"];
	
													if (($db->AutoExecute("event_ed11", $processed_ed11, "INSERT")) && ($new_eed11_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied ed11_id [".$processed_ed11["ed11_id"]."] to new event_id [".$new_event_id."].");
														
														$copied_ed11[] = $processed_ed11["ed11_id"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy ed11_id [".$processed_ed11["ed11_id"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
									
									/**
									 * Attach any files from the previous event to this new event.
									 */
									if ($COPY_EVENT_FILES) {
										$query		= "SELECT * FROM `event_files` WHERE `event_id` = ".$db->qstr($old_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_files	= array();
											
											foreach ($results as $result) {
												if ((!in_array($result["file_name"], $copied_files)) && ($result["file_category"] != "podcast")) {
													$old_event_file = FILE_STORAGE_PATH."/".$result["efile_id"];
	
													if ((@file_exists($old_event_file)) || ((bool) $SKIP_FILE_COPY)) {
														$processed_file						= array();
														$processed_file["event_id"]			= $new_event_id;
														$processed_file["required"]			= $result["required"];
														$processed_file["timeframe"]		= $result["timeframe"];
														$processed_file["file_category"]	= $result["file_category"];
														$processed_file["file_type"]		= $result["file_type"];
														$processed_file["file_size"]		= $result["file_size"];
														$processed_file["file_name"]		= $result["file_name"];
														$processed_file["file_title"]		= $result["file_title"];
														$processed_file["file_notes"]		= $result["file_notes"];
														$processed_file["access_method"]	= $result["access_method"];
														$processed_file["accesses"]			= 0;
														$processed_file["release_date"]		= ((((int) $result["release_date"]) && (isset($historical["event_start"]))) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_date"]) : 0);
														$processed_file["release_until"]	= ((((int) $result["release_until"]) && (isset($historical["event_start"]))) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_until"]) : 0);
														$processed_file["updated_date"]		= $result["updated_date"];
														$processed_file["updated_by"]		= $result["updated_by"];
	
														if (($db->AutoExecute("event_files", $processed_file, "INSERT")) && ($new_file_id = $db->Insert_Id())) {
															output_success("[Row ".$row_count."]\tCopied file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in database.");
														
															if (!(bool) $SKIP_FILE_COPY) {
																if (copy($old_event_file, FILE_STORAGE_PATH."/".$new_file_id)) {
																	output_success("[Row ".$row_count."]\tCopied file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in filesystem.");
	
																	$copied_files[] = $processed_file["file_name"];
																} else {
																	output_error("[Row ".$row_count."]\tUnable to copy file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in filesystem.");
																}
															} else {
																output_notice("[Row ".$row_count."]\tSkipped copying file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in filesystem.");
	
																$copied_files[] = $processed_file["file_name"];
															}
														} else {
															output_error("[Row ".$row_count."]\tUnable to copy file [".$processed_file["file_name"]."] to new event_id [".$new_event_id."] in database. Database said: ".$db->ErrorMsg());
														}
													} else {
														output_error("[Row ".$row_count."]\tThe old event file [".$old_event_file."] does not exist in the filesystem, so it cannot be copied.");
													}
												}
											}
										}
									}
	
									/**
									 * Attach any links from the previous event to this new event.
									 */
									if ($COPY_EVENT_LINKS) {
										$query		= "SELECT * FROM `event_links` WHERE `event_id` = ".$db->qstr($old_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_links	= array();
											
											foreach ($results as $result) {
												if (!in_array($result["link"], $copied_links)) {
													$processed_link						= array();
													$processed_link["event_id"]			= $new_event_id;
													$processed_link["required"]			= $result["required"];
													$processed_link["timeframe"]		= $result["timeframe"];
													$processed_link["proxify"]			= $result["proxify"];
													$processed_link["link"]				= $result["link"];
													$processed_link["link_title"]		= $result["link_title"];
													$processed_link["link_notes"]		= $result["link_notes"];
													$processed_link["accesses"]			= 0;
													$processed_link["release_date"]		= (((int) $result["release_date"]) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_date"]) : 0);
													$processed_link["release_until"]	= (((int) $result["release_until"]) ? offset_validity($historical["event_start"], $processed_event["event_start"], $result["release_until"]) : 0);
													$processed_link["updated_date"]		= $result["updated_date"];
													$processed_link["updated_by"]		= $result["updated_by"];
	
													if (($db->AutoExecute("event_links", $processed_link, "INSERT")) && ($new_link_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied link [".$processed_link["link_title"]."] to new event_id [".$new_event_id."].");
														
														$copied_links[] = $processed_link["link"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy link [".$processed_link["link_title"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
									
									/**
									 * Attach any objectives from the previous event to this new event.
									 */
									if ($COPY_EVENT_OBJECTIVES) {
										$query		= "SELECT * FROM `event_objectives` WHERE `event_id` = ".$db->qstr($old_event_id);
										$results	= $db->GetAll($query);
										if ($results) {
											$copied_objectives	= array();
											
											foreach ($results as $result) {
												if (!in_array($result["objective_id"], $copied_objectives)) {
													$processed_objective = array();
													$processed_objective["event_id"] = $new_event_id;
													$processed_objective["objective_id"] = $result["objective_id"];
													$processed_objective["updated_date"] = $result["updated_date"];
													$processed_objective["updated_by"] = $result["updated_by"];
													$processed_objective["objective_type"] = $result["objective_type"];
													$processed_objective["objective_details"] = $result["objective_details"];
	
													if (($db->AutoExecute("event_objectives", $processed_objective, "INSERT")) && ($new_eobjective_id = $db->Insert_Id())) {
														output_success("[Row ".$row_count."]\tCopied objective_id [".$processed_objective["objective_id"]."] to new event_id [".$new_event_id."].");
														
														$copied_objectives[] = $processed_objective["objective_id"];
													} else {
														output_error("[Row ".$row_count."]\tUnable to copy objective_id [".$processed_objective["objective_id"]."] to new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}
										}
									}
									
									/**
									 * Add a line to the related linking to previous copied lecture.
									 */
									$processed_related						= array();
									$processed_related["event_id"]			= $new_event_id;
									$processed_related["related_type"]		= "event_id";
									$processed_related["related_value"]		= $old_event_id;
									$processed_related["updated_date"]		= time();
									$processed_related["updated_by"]		= 1;
		
									if ($db->AutoExecute("event_related", $processed_related, "INSERT")) {
										output_success("[Row ".$row_count."]\tAdded relationship between old event_id [".$old_event_id."] and new event_id [".$new_event_id."].");
									} else {
										output_error("[Row ".$row_count."]\tUnable to add relationship between old event_id [".$old_event_id."] and new event_id [".$new_event_id."]. Database said: ".$db->ErrorMsg());
									}
								}
							}
						} else {
							output_error("[Row ".$row_count."]\tUnable to attached event_id [".$new_event_id."] to class of ".$event_grad_year.". Database said: ".$db->ErrorMsg());
						}
					} else {
						output_error("[Row ".$row_count."]\tUnable to import learning event for phase [".$event_phase."] on ".date("r", $processed_event["event_start"]).". Database said: ".$db->ErrorMsg());
					}
				}
			}
			fclose($handle);
		} else {
			output_error("Unable to open the provided CSV file [".$CSV_FILE."].");
		}
	break;
	case "-validate" :
		$handle = fopen($CSV_FILE, "r");
		if ($handle) {
			$row_count	= 0;
			
			while (($row = fgetcsv($handle)) !== false) {
				$row_count++;
				
				/**
				 * We do not want the first row to be imported because it should
				 * be the CSV heading titles.
				 */
				if ($row_count > 1) {
					$event_ids			= array();
					$proxy_ids			= array();
		
					$event_id			= ((isset($row[0])) ? clean_input($row[0]) : "");
					$event_phase		= ((isset($row[1])) ? clean_input($row[1]) : "");
					$event_grad_year	= ((isset($row[2])) ? clean_input($row[2]) : "");
					$course_num			= ((isset($row[3])) ? clean_input($row[3]) : "");
					$course_name		= ((isset($row[4])) ? clean_input($row[4]) : "");
					$event_date			= ((isset($row[5])) ? clean_input($row[5]) : "");
					$event_start_time	= ((isset($row[6])) ? clean_input($row[6]) : "");
					$event_duration		= ((isset($row[7])) ? clean_input($row[7]) : 0);
					$event_type			= ((isset($row[8])) ? clean_input($row[8]) : "");
					$event_title		= ((isset($row[9])) ? clean_input($row[9]) : "");
					$event_location		= ((isset($row[10]) && ($row[10] != "")) ? clean_input($row[10]) : "TBA");
					$staff_numbers		= ((isset($row[11])) ? clean_input($row[11]) : "");

					if ($event_id) {
						$pieces = explode(";", $event_id);
						if ((is_array($pieces)) && (count($pieces))) {
							foreach ($pieces as $tmp_event_id) {
								if ($tmp_event_id = clean_input($tmp_event_id, array("nows", "int"))) {
									if (!validate_event_id($tmp_event_id)) {
										output_notice("[Row ".$row_count."]\tAn old event_id [".$tmp_event_id."] does not exist in the database.");
									}
								}
							}
						}
					}

					if (trim($event_type) == "") {
						$event_type = "Other";
					}

					if (trim($course_name) == "") {
						$course_name = "General Events";
					}

					if ($event_phase == "") {
						output_notice("[Row ".$row_count."]\tThe event phase [".$event_phase."] appears to be missing.");
					}

					if (!(int) $event_grad_year) {
						output_notice("[Row ".$row_count."]\tThe event grad year [".$event_grad_year."] appears to be missing or invalid.");
					}

					if (!get_eventtype_id($event_type)) {
						output_notice("[Row ".$row_count."]\tUnable to locate an event type id match for event type [".$event_type."].");
					}

					if (!get_course_id($course_name)) {
						output_notice("[Row ".$row_count."]\tUnable to locate a course_id match for course name [".$course_name."].");
					}

					if (!$event_start = strtotime($event_date." ".$event_start_time)) {
						output_notice("[Row ".$row_count."]\tUnable to parse the UNIX timestamp for [".$event_date." ".$event_start_time."].");
					}
					
					if (!(int) $event_duration) {
						output_notice("[Row ".$row_count."]\tThe event duration [".$event_duration."] appears to be missing or invalid.");
					}
					
					if ($event_title == "") {
						output_notice("[Row ".$row_count."]\tThe event title [".$event_title."] appears to be missing.");
					}
					
					if ($staff_numbers) {
						$pieces = explode(";", $staff_numbers);
						if ((is_array($pieces)) && (count($pieces))) {
							foreach ($pieces as $tmp_staff_number) {
								if ($tmp_staff_number = clean_input($tmp_staff_number, array("nows", "int"))) {
									if (!get_proxy_id($tmp_staff_number)) {
										output_notice("[Row ".$row_count."]\tUnable to locate a proxy_id for staff number [".$tmp_staff_number."].");
									}
								}
							}
						}
					}
				}
			}
			fclose($handle);
		} else {
			output_error("Unable to open the provided CSV file [".$CSV_FILE."].");
		}
		
		if ((!$SUCCESS) && (!$NOTICE) && (!$ERROR)) {
			echo "\nThis CSV file has been parsed, but there does not not appear to be any errors or notices.";
		}
	break;
	case "-usage" :
	default :
		echo "\nUsage: schedule-import.php [options] /path/to/import-file.csv";
		echo "\n   -usage               Brings up this help screen.";
		echo "\n   -validate            Validates the import file.";
		echo "\n   -testimport			Proceeds with live import, but does not copy any files.";
		echo "\n   -import              Proceeds with live import.";
	break;
}
echo "\n\n";
?>