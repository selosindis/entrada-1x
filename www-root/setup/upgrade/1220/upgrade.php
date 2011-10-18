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
 * Serves as the main Entrada administrative request controller file.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");
require_once("dbconnection.inc.php");

$query = "SELECT * FROM `notices` WHERE `notice_id` NOT IN(SELECT `notice_id` FROM `notice_audience`)";
$orphan_notices = $db->GetAll($query);

if($orphan_notices){
	$audience["updated_by"] = 1;
	$audience["updated_date"] = time();
	foreach($orphan_notices as $notice){
		$target_info = explode(":",$notice["target"]);
		$type = $target_info[0];
		$value = isset($target_info[1])?$target_info[1]:0;
		switch($type){
			case "cohort":
				$audience["audience_type"] = "cohorts";
				break;
			case "all":
				$audience["audience_type"] = "all:users";
				break;
			default:
				$audience["audience_type"] = "all:".$type;
				break;
		}
		$audience["audience_value"] = $value;
		$audience["notice_id"] = $notice["notice_id"];
		print_r($audience);
	}
}