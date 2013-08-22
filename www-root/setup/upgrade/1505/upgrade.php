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
 * Entrada upgrade helper.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@qmed.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

require_once("config/config.inc.php");
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();
require_once("config/settings.inc.php");
require_once("Entrada/adodb/adodb.inc.php");
require_once("functions.inc.php");
require_once("dbconnection.inc.php");

global $db;

$query = "SELECT `notice_id`, `updated_by` FROM `notices`";
$results = $db->GetAll($query);
foreach ($results as $result) {
	$notice_array = array("created_by" => $result["updated_by"]);
	if ($db->AutoExecute("notices", $notice_array, "UPDATE", "notice_id = ".$db->qstr($result["notice_id"]))) {
		application_log("error", "There was an error updating the created_by field in the notices table. Database said: ".$db->ErrorMsg());
	}
}

$query = "SELECT `quiz_id`, `updated_by` FROM `quizzes`";
$results = $db->GetAll($query);
foreach ($results as $result) { 
	$quiz_array = array("created_by" => $result["updated_by"]);
	if ($db->AutoExecute("quizzes", $quiz_array, "UPDATE", "quiz_id = ".$db->qstr($result["quiz_id"]))) {
		application_log("error", "There was an error updating the created_by field in the quizzes table. Database said: ".$db->ErrorMsg());
	}
}
