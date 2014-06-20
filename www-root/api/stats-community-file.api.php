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
 * this file loads the views for the event sorted different way
 *
 * @author Organization: David Geffen School of Medicine at UCLA
 * @author Unit: Instructional Design and Technology Unit
 * @author Developer: Sam Payne <spayne@mednet.ucla.edu>
 * @copyright Copyright 2013 Regents of The University of California. All Rights Reserved.
 *
*/

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

    //$PROCESSED["proxy_id"] = $ENTRADA_USER->getID();

//    if ($_POST["sortID"] == "name") {
//        $sortOrder = "users.lastname";
//    }
//    if ($_POST["sortID"] == "date") {
//        $sortOrder = "lastViewedTime";
//    }
//    if ($_POST["sortID"] == "view") {
//        $sortOrder = "views";
//    }                          
//    if(isset($_POST["EFILE_ID"])) {
//        $EFILE_ID = $_POST["EFILE_ID"];
//    }
    if (isset($_POST["action"]) && $_POST["action"] != "") {
        $action = $_POST["action_field"];
    }
    
    if (isset($_POST["action_field"]) && $_POST["action_field"] != "") {
        $action_field = $_POST["action_field"];
    }
    
    if (isset($_POST["action_value"]) && $_POST["action_value"] != "") {
        $action_value = $_POST["action_value"];
    }
    
    if (isset($_POST["module"]) && $_POST["module"] != "") {
        $module = $_POST["module"];
    }
    
    $html = "";
    $file_views = Models_Statistic::getCommunityFileViews($module, $action_value);
    if ($file_views) {
        foreach ($file_views as $file_view) {
            $html .= "<tr>";
            $html .= "<td>" . $file_view["lastname"] . ", " . $file_view["firstname"] . "</td>";
            $html .= "<td class='centered'>" . $file_view["views"] . "</td>";
            $html .= "<td>" . date("Y-m-d H:i", $file_view["last_viewed_time"]) . "</td>";
        $html .= "</tr>";
        }
    } else {
        $html .= "<tr>";
            $html .= "<td colspan='3'>This file has not yet been viewed by users.</td>";
        $html .= "</tr>";
    }
     
    $record = array();
    $record["html"] = $html;
    header("Content-type: application/json");
    echo json_encode($record);
}
?>