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
 * this file loads the views for the gradebook sorted different way
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

if(isset($_POST["ASSESSMENT_ID"])) {
    $ASSESSMENT_ID = $_POST["ASSESSMENT_ID"];
}

if($ASSESSMENT_ID) {
            $query    = "    SELECT a.*
                        FROM `assessments` AS a
                        WHERE a.`assessment_id` = ".$db->qstr($ASSESSMENT_ID);
            $result    = $db->GetRow($query);


        if($result) {
            $access_allowed = false;
            if (!$ENTRADA_ACL->amIAllowed(new GradebookResource($ASSESSMENT_ID, $result["course_id"], $result["organisation_id"]), "update")) {

            } else {
                $access_allowed = true;
            }

        if (!$access_allowed) {
            $modal_onload[]= "closeWizard()";

            $ERROR++;
            $ERRORSTR[]= $query."Your account does not have the permissions required to use this feature of this module. If you believe you are receiving this message in error please contact us for assistance.";

            echo display_error();

            application_log("error", "Someone attempted to view statistics for an event [".$EVENT_ID."] that they were not the coordinator for.");
        } else {

            $PROCESSED["proxy_id"] = $ENTRADA_USER->getID();

            if ($_POST["sortID"] == "name") {
                $sortField = "cmp_names";
            }
            if ($_POST["sortID"] == "dateFirst") {
                $sortField = "cmp_first_view";
            }
            if ($_POST["sortID"] == "dateLast") {
                $sortField = "cmp_last_view";
            }
            if ($_POST["sortID"] == "view") {
                $sortField = "cmp_views";
            }
            
            if($_POST["sortOrder"] == "ASC") {
                $sortOrder = "_ASC";
            }
            
            if($_POST["sortOrder"] == "DESC") {
                $sortOrder = "_DESC";
            }            
            
            if($_POST["action"] == "view") {
                $action = "view";
            }

            if($_POST["action_field"] == "assessment_id") {
                $action_field = "assessment_id";
            }
            
            if(isset($_POST["group_id"])) {
                $group_id = clean_input($_POST["group_id"], int);
            }
            
                        $cohortSQL = "  SELECT b.`id` AS `proxy_id`, b.`lastname`, b.`firstname`
                                        FROM `".AUTH_DATABASE."`.`user_data` AS b
                                        JOIN `".AUTH_DATABASE."`.`user_access` AS c
                                        ON c.`user_id` = b.`id`
                                        AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
                                        AND c.`account_active` = 'true'
                                        AND (c.`access_starts` = '0' OR c.`access_starts`<=".$db->qstr(time()).")
                                        AND (c.`access_expires` = '0' OR c.`access_expires`>=".$db->qstr(time()).")
                                        JOIN `" . DATABASE_NAME . "`.`group_members` AS d
                                        ON b.`id` = d.`proxy_id`
                                        WHERE c.`group` = 'student'
                                        AND d.`group_id` = ".$db->qstr($group_id)."
                                        AND d.`member_active` = '1'
                                        ORDER BY b.`lastname` ASC, b.`firstname` ASC";
                        $classList = $db->GetAll($cohortSQL);

                        $totalViews = 0;
                        $userViews = 0;
                        $statsHTML = "";
                        foreach($classList as $student) {
                            $viewsSQL = "   SELECT DISTINCT (stats.proxy_id), COUNT(*) AS views, users.firstname, users.lastname, MIN(stats.timestamp) as firstViewedTime, MAX(stats.timestamp) as lastViewedTime
                                            FROM " . DATABASE_NAME . ".statistics AS stats, " . AUTH_DATABASE . ".user_data AS users
                                            WHERE stats.module = 'gradebook'
                                            AND stats.action = '" . $action . "'
                                            AND stats.action_field = '" . $action_field . "'
                                            AND stats.action_value = '" . $ASSESSMENT_ID . "'  
                                            AND stats.proxy_id = users.id 
                                            AND stats.proxy_id = '" . $student["proxy_id"] . "'
                                            GROUP BY stats.proxy_id
                                            ORDER BY users.lastname ASC";                       

                            $statistics = $db->GetRow($viewsSQL);
                            if ($statistics) {
                                $userViews++;
                                $totalViews = $totalViews + $statistics["views"];                                
                                $statsArray[] = array("lastname" => $student["lastname"], "firstname" => $student["firstname"], "views" => $statistics["views"], "firstviewed" => $statistics["firstViewedTime"], "lastviewed" => $statistics["lastViewedTime"]);
                            } else {                                
                                $statsArray[] = array("lastname" => $student["lastname"], "firstname" => $student["firstname"], "views" => "0", "firstviewed" => "", "lastviewed" => "");
                            }  
                        }
                        usort($statsArray, $sortField . $sortOrder);
                        foreach ($statsArray as $student_detail) {
                            $statsHTML .=   "<li class='statsLI'><span class='sortStats sortStatsNameModel'>" . $student_detail["lastname"] . ", " . $student_detail["firstname"] . "</span><span class='sortStats sortStatsViewsModel'>" . $student_detail["views"] . "</span><span class='sortStats sortStatsDateModel'>" . unixStringtoDate($student_detail["firstviewed"]) . "</span><span class='sortStats sortStatsDateModel'>" . unixStringtoDate($student_detail["lastviewed"]) . "</span></li>";
                        }            
            $record = array();
            $record["userViews"] = $userViews;
            $record["totalViews"] = $totalViews;
            $record["statsHTML"] = $statsHTML;
            $record["statsArray"] = $statsArray;
            $record["classlist"] = $classList;
        header("Content-type: application/json");
        echo json_encode($record);
        }
    }
}
?>