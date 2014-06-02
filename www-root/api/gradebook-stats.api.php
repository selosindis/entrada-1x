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
 * Loads the stats for the selected module
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

ob_start("on_checkout");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
    echo "<div id=\"scripts-on-open\" style=\"display: none;\">\n";
    echo "alert('It appears as though your session has expired; you will now be taken back to the login page.');\n";
    echo "if(window.opener) {\n";
    echo "    window.opener.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
    echo "    top.window.close();\n";
    echo "} else {\n";
    echo "    window.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
    echo "}\n";
    echo "</div>\n";
    exit;
} else {

    $EVENT_ID            = 0;
    $EFILE_ID            = 0;

    if(isset($_GET["action"])) {
        $ACTION    = trim($_GET["action"]);
    }

    if((isset($_GET["step"])) && ((int) trim($_GET["step"]))) {
        $STEP = (int) trim($_GET["step"]);
    }

    if((isset($_GET["fid"])) && ((int) trim($_GET["fid"]))) {
        $EFILE_ID = (int) trim($_GET["fid"]);
    }

    if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
        $ASSESSMENT_ID    = (int) trim($_GET["id"]);
    }
    if((isset($_GET["group_id"])) && ((int) trim($_GET["group_id"]))) {
        $group_id = (int) trim($_GET["group_id"]);
    }

    $modal_onload = array();
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
                $modal_onload[]    = "closeWizard()";

                $ERROR++;
                $ERRORSTR[]    = "Your MEdTech account does not have the permissions required to use this feature of this module. If you believe you are receiving this message in error please contact the MEdTech Unit at 613-533-6000 x74918 and we can assist you.";

                echo display_error();

                application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to the file wizard.");
            } else {
                switch($ACTION) {
                    case "assessment" :
                        $action_field = "assessment_id";
                        $action = "view";
                        //selects the cohort of students for the assessment
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
                        //adds each memeber of the cohorts views to an array
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
                                $statsArray[] = array("lastname" => $student["lastname"], "firstname" => $student["firstname"], "views" => $statistics["views"], "firstviewed" => date("m-j-Y g:ia", $statistics["firstViewedTime"]), "lastviewed" => date("m-j-Y g:ia", $statistics["lastViewedTime"]));
                            } else {                                
                                $statsArray[] = array("lastname" => $student["lastname"], "firstname" => $student["firstname"], "views" => "0", "firstviewed" => "", "lastviewed" => "");
                            }
                        }
                        //initialy sorts by last name
                        usort($statsArray, "cmp_names_asc");
                        //loops through the array to generate a list
                        foreach ($statsArray as $student_detail) {
                            $statsHTML .=   "<li class='statsLI'><span class='sortStats sortStatsNameModel'>" . $student_detail["lastname"] . ", " . $student_detail["firstname"] . "</span><span class='sortStats sortStatsViewsModel'>" . $student_detail["views"] . "</span><span class='sortStats sortStatsDateModel'>" . $student_detail["firstviewed"] . "</span><span class='sortStats sortStatsDateModel'>" . $student_detail["lastviewed"] . "</span></li>";                                                                                            
                        }

                            $query    = "SELECT * FROM `assessments` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
                            $result    = $db->GetRow($query);
                            if($result) {
                                $PROCESSED["name"] = trim($result["name"]);
                                ?>
                                <script type="text/javascript">
                                    jQuery(document).ready(function(){
                                        jQuery(".sortStatsHeader").click(function() {
                                            var sortID = jQuery(this).attr("id");
                                            if(jQuery(this).hasClass("ASC")) {
                                                var sortOrder = "DESC";
                                            } else {
                                                var sortOrder = "ASC";
                                            }
                                            var ASSESSMENT_ID = "<?php echo $ASSESSMENT_ID?>";
                                            var action_field = "<?php echo $action_field?>";
                                            var action = "<?php echo $action?>";
                                            var group_id = "<?php echo $group_id?>";
                                            var dataString = 'sortOrder=' + sortOrder + '&sortID=' + sortID + '&ASSESSMENT_ID=' + ASSESSMENT_ID + '&action_field=' + action_field + '&action=' + action + '&group_id=' + group_id;
                                            var url = '<?php echo ENTRADA_URL . "/api/stats-gradebook-view.php";?>'
                                            jQuery.ajax({
                                                type: "POST",
                                                url: url,
                                                data: dataString,
                                                dataType: "json",
                                                success: function(data) {
                                                    jQuery("#userViews").html("<strong>" + data["userViews"] + "</strong>");
                                                    jQuery("#totalViews").html("<strong>" + data["totalViews"] + "</strong>");
                                                    jQuery("#statsHTML").html(data["statsHTML"]);
                                                    if(jQuery("#" + sortID).hasClass("ASC")) {
                                                        jQuery(".sortStatsHeader").removeClass("ASC").addClass("DESC");;
                                                    } else {
                                                        jQuery(".sortStatsHeader").removeClass("DESC").addClass("ASC");
                                                    }
                                                }
                                             });
                                        });
                                    });
                                </script>
                                <div class="modal-dialog" id="file-edit-wizard-<?php echo $ASSESSMENT_ID; ?>">
                                        <div id="wizard">
                                            <h2 title="Event Statistics Section">Assessment Statistics</h2>
                                            <h3><?php echo html_encode($PROCESSED["name"]); ?></h3>
                                            <div id="bodyStats">
                                                <ul class="statsUL">
                                                    <li class="statsLI"><span class="statsLISpan1">Number of users who viewed this assessment: </span><span id="userViews"><strong><?php echo $userViews?></strong></span></li>
                                                    <li class="statsLI"><span class="statsLISpan1">Total views of this assessment: </span><span id="totalViews"><strong><?php echo $totalViews?></strong></span></li>
                                                </ul>
                                                <ul class="statsUL">
                                                    <li class="statsLIHeader"><span class="sortStatsHeader ASC sortStatsNameModel" id="name">Name</span><span class="sortStatsHeader ASC sortStatsViewsModel" id="view">Views</span><span class="sortStatsHeader ASC sortStatsDateModel" id="dateFirst">First viewed on</span><span class="sortStatsHeader ASC sortStatsDateModel" id="dateLast">Last viewed on</span></li>
                                                    <div id="statsHTML"><?php echo $statsHTML ?></div>
                                                </ul>
                                                <p class="content-small">Click on title to change sort</p>
                                            </div>
                                            <div id="footer">
                                                <input type="button" class="btn" value="Close" onclick="closeWizard()" style="float: left; margin: 4px 0px 4px 10px" />
                                            </div>
                                        </div>
                                </div>
                                <?php
                            } else {
                                $ERROR++;
                                $ERRORSTR[] = "The provided assessment does not exist in the provided event.";

                                echo display_error();

                                application_log("error", "assessment statistics was accessed with a assessment id that was not found in the database.");
                            }
                        break;
                    default :

                }
                ?>
                <div id="scripts-on-open" style="display: none;">
                <?php
                    foreach ($modal_onload as $string) {
                        echo $string.";\n";
                    }
                ?>
                </div>
                <?php
            }
        } else {
            $ERROR++;
            $ERRORSTR[] = "The provided assessment identifier does not exist in this system.";

            echo display_error();

            application_log("error", "File wizard was accessed without a valid assessment id.");
        }
    } else {
        $ERROR++;
        $ERRORSTR[] = "You must provide an assessment identifier when using the file wizard.";

        echo display_error();

        application_log("error", "File wizard was accessed without any assessment id.");
    }
}