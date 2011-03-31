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
 * Run this script to copy a community (specified by community id) to a new
 * community.
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

output_notice("This script is used to add the Medical Training Days application as a page in each Postgrad Community.");

$site_names = array("Aboriginal Health - Family Medicine",
"Anatomic Pathology",
"Anesthesia - Family Medicine",
"Anesthesiology",
"Cardiology",
"Care of the Elderly - Family Medicine",
"Public Health and Preventative Medicine",
"Critical Care Medicine",
"Developmental Disabilities - Family Medicine",
"Diagnostic Radiology",
"Emergency Medicine",
"Emergency Medicine - Family Medicine",
"Family Medicine",
"Gastroenterology",
"General Surgery",
"Hematology",
"Internal Medicine",
"Medical Oncology",
"Nephrology",
"Neurology",
"Obstetrics and Gynecology",
"Ophthalmology",
"Orthopedic Surgery",
"Palliative Care - Family Medicine",
"Palliative Care Medicine",
"Pediatrics",
"Physical Medicine and Rehabilitation",
"Psychiatry",
"Radiation Oncology",
"Respirology",
"Rheumatology",
"Rural Skills - Family Medicine",
"Surgical Foundations",
"Urology",
"Women's Health - Family Medicine",
"Accreditation Standards");

$site_name_prefix = "pgme";
$template = $community["community_template"];
$theme = $community["community_theme"];

output_notice("Step 2: The pages from the given Community ID are inserted as pages for the new Community Site.");

foreach ($site_names as $s_name) {

	//format the program name for use as an URL and community short name
	$s_name_url = clean_input($s_name, array("page_url", "lowercase"));
	$community_shortname = $site_name_prefix . "_" . $s_name_url;

	//Get the community id
	$query = "SELECT *
			  FROM communities
			  WHERE community_shortname = " . $db->qstr($community_shortname);
	$result = $db->GetRow($query);
	if (!$result) {
		output_error("Could not find the community: " . $community_shortname);
		exit();
	}

	//MTD Module = 8
	communities_module_activate($result["community_id"], 8);

	output_notice("\n\nThe MTD Page has been added to all Postgrad Communities.");

} //END OF MAIN SCRIPT
?>