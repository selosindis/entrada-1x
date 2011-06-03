#!/usr/local/zend/bin/php
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
 * Run this script to create the MTD Tracking page on each of the specified
 * Communities in the site_names array.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/includes");

@ini_set("auto_detect_line_endings", 1);
@ini_set("display_errors", 1);
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
require_once("auth_dbconnection.inc.php");
require_once("functions.inc.php");

$ERROR = false;

output_notice("This script is used to add a module (and it's page) to a Community.");

$site_names = array("Aboriginal Health - Family Medicine",
"Accreditation Standards",
"Anatomic Pathology",
"Anesthesia - Family Medicine",
"Anesthesiology",
"Cardiac Surgery",
"Care of the Elderly - Family Medicine",
"Critical Care Medicine",
"Developmental Disabilities - Family Medicine",
"Diagnostic Radiology",
"Emergency Medicine",
"Emergency Medicine - Family Medicine",
"Endocrinology",
"Family Medicine",
"Gastroenterology",
"General Surgery",
"Generic Program",
"Geriatrics",
"Hematology",
"Internal Medicine",
"Medical Oncology",
"Nephrology",
"Neurology",
"Neurosurgery",
"Obstetrics and Gynecology",
"Ophthalmology",
"Orthopedic Surgery",
"Otolaryngology",
"Palliative Care - Family Medicine",
"Palliative Care Medicine",
"Pediatrics",
"Physical Medicine and Rehabilitation",
"Plastic Surgery",
"Psychiatry",
"Public Health and Preventative Medicine",
"Radiation Oncology",
"Respirology",
"Rheumatology",
"Rural Skills - Family Medicine",
"Surgical Foundations",
"Thoracic Surgery",
"Urology",
"Vascular Surgery",
"Women's Health - Family Medicine");

$site_name_prefix = "pgme";

print "\nStep 1 - Please enter the module number (from the communities_module table) of the module to create: ";
fscanf(STDIN, "%d\n", $module_number); // reads number from STDIN

foreach ($site_names as $s_name) {

	$s_name_arr = explode("-", $s_name);
	$s_name = trim($s_name_arr[0]);

	//format the program name for use as an URL and community short name
	$s_name_url = clean_input($s_name, array("page_url", "lowercase"));
	$community_url = "/" . $site_name_prefix . "_" . $s_name_url;
	$community_shortname = $site_name_prefix . "_" . $s_name_url;

	//Get the community id
	$query = "SELECT *
			  FROM " . DATABASE_NAME . ".`communities`
			  WHERE community_url = " . $db->qstr($community_url);
	$result = $db->GetRow($query);
	if (!$result) {
		output_error("Could not find the community URL: " . $community_url . " " . $query);
	} else {
		output_notice("Creating page for community id: " . $result["community_id"]);

		communities_module_activate_and_page_create($result["community_id"], $module_number);

		set_module_page_permissions($db, $result["community_id"], $module_number, 0, 0, 0);

		output_notice("The MTD Page has been added to " . $s_name . ".");
	}
}

function set_module_page_permissions($db, $community_id, $module_id, $allow_member_view, $allow_public_view, $allow_troll_view) {
	$query = "SELECT * FROM " . DATABASE_NAME . ".`communities_modules` WHERE `module_id` = " . $db->qstr($module_id) . " AND `module_active` = '1'";
	$module_info = $db->GetRow($query);
	$module_shortname = "";

	if ($module_info) {
		$module_shortname = $module_info["module_shortname"];

		if ($db->AutoExecute("" . DATABASE_NAME . ".`community_pages`",
						array("allow_member_view" => 0, "allow_public_view" => 0, "allow_troll_view" => 0,
							"updated_date" => time(), "updated_by" => 5440), "UPDATE",
						"`community_id` = " . $db->qstr($community_id) . " AND page_type = " . $db->qstr($module_shortname))) {

			output_success("Permission set to allow Admin access only.");
		} else {
			output_error("Failed to create the module page.");
		}
	} else {
		output_error("Module does not exist.");
	}
}
?>