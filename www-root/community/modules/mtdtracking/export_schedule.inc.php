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
 * Allows students to delete an elective in the system if it has not yet been approved.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if (!defined("IN_MTDTRACKING")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: " . ENTRADA_URL);
	exit;
} else {
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core/includes",
    get_include_path(),
)));

require_once("functions.inc.php");

$usersId = $_SESSION["details"]["id"];
$PROCESSED["service_id"] = validate_integer_field($_GET["service_id"]);

ob_clear_open_buffers();
$query = "SELECT `mtd_facilities`.`facility_name`, `mtd_residents`.`first_name`,
				 `mtd_residents`.`last_name`, `mtd_schedule`.`start_date`, 
				 `mtd_schedule`.`end_date`, `mtd_residents`.`cpso_no`,
				 `mtd_residents`.`student_no`,`mtd_schools`.`school_code`,
				 `mtd_locale_duration`.`percent_time`, `mtd_moh_program_codes`.`program_code`,
				 `mtd_categories`.`category_code`
		  FROM  `mtd_schedule`, `mtd_facilities`, `mtd_residents`, `mtd_locale_duration`,
				`mtd_schools`, `mtd_moh_program_codes`, `mtd_categories`
	      WHERE `mtd_schedule`.`id` = `mtd_locale_duration`.`schedule_id`
		  AND `mtd_facilities`.`id` = `mtd_locale_duration`.`location_id`
		  AND `mtd_schedule`.`service_id` = '" . $PROCESSED["service_id"] . "'
		  AND `mtd_schedule`.`resident_id` = `mtd_residents`.`id`
		  AND `mtd_schools`.`id` = `mtd_residents`.`school_id`
		  AND `mtd_moh_program_codes`.`id` = `mtd_residents`.`program_id`
		  AND `mtd_categories`.`id` = `mtd_residents`.`category_id`
		  ORDER BY start_date DESC";

$results = $db->GetAll($query);

header("Expires: 0"); // set expiration time
// browser must download file from server instead of cache
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// force download dialog
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Type:  application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"mtd_schedule.csv\"");

echo "\"School_Code\",\"CPSO_No\",\"Student_No\",\"Program_Code\",\"First_Name\",\"Last_Name\",\"Category_Code\",\"Service_Code\",\"Location\",\"Start_Date\",\"End_Date\",\"Percent_Time\"\n";

if ($results) {
	//an array for holding each line of the result set before echoing it
	//to the buffer.
	$line = array();
	foreach ($results as $result) {

		$line["school_code"] = $result["school_code"];
		$line["cpso_no"] = $result["cpso_no"];
		$line["student_no"] = $result["student_no"];
		$line["program_code"] = $result["program_code"];
		$line["first_name"] = $result["first_name"];
		$line["last_name"] = $result["last_name"];
		$line["category_code"] = $result["category_code"];
		$line["service_code"] = $PROCESSED["service_id"];
		$line["facility_name"] = $result["facility_name"];
		$line["start_date"] = $result["start_date"];
		$line["end_date"] = $result["end_date"];
		$line["percent_time"] = $result["percent_time"];
		echo implode(",", $line) . "\n";
	}
} else {
	echo "No results";
}
exit();
}
?>