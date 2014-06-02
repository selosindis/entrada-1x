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
 * Loads the Learning Event file wizard when a teacher / director wants to add /
 * edit a file on the Manage Events > Content page.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
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

    $EVENT_ID            = 0;
    $EFILE_ID            = 0;

    if(isset($_GET["action"])) {
        $ACTION    = trim($_GET["action"]);
    }

    if((isset($_GET["id"])) && ((int) trim($_GET["id"]))) {
        $EVENT_ID    = (int) trim($_GET["id"]);
    }

    if((isset($_GET["fid"])) && ((int) trim($_GET["fid"]))) {
        $EFILE_ID = (int) trim($_GET["fid"]);
    }
    
    if(isset($_GET["module"])) {
        $MODULE = trim($_GET["module"]);
    }
    

    switch($ACTION) {
        case "file" :

            $action_field = "csfile_id";
            $action = "file_download";
            $viewsSQL = "   SELECT DISTINCT (stats.proxy_id), COUNT(*) AS views, users.firstname, users.lastname, MAX(stats.timestamp) as lastViewedTime
                          FROM " . DATABASE_NAME . ".statistics AS stats, " . AUTH_DATABASE . ".user_data AS users
                          WHERE stats.module = '" . $MODULE . "'
                          AND stats.action = '" . $action . "'
                          AND stats.action_field = '" . $action_field . "'
                          AND stats.action_value = " . $EFILE_ID . " 
                          AND stats.proxy_id = users.id
                          GROUP BY stats.proxy_id
                          ORDER BY users.lastname ASC";

            $statistics = $db->GetAll($viewsSQL);
            $totalViews = 0;
            $userViews = 0;
            $statsHTML = "";
            foreach ($statistics as $stats) {
              $statsHTML .=   "<li class='statsLI'><span class='sortStats sortStatsNameModel'>" . $stats["lastname"] . ", " . $stats["firstname"] . "</span><span class='sortStats sortStatsViewsModel'>" . $stats["views"] . "</span><span class='sortStats sortStatsDateModel'>" . date("m-j-Y g:ia", $stats["lastViewedTime"]) . "</span></li>";
              $userViews++;
              $totalViews = $totalViews + $stats["views"];
            }


            if($EFILE_ID) {           
                $query    = "SELECT * FROM `community_share_files` WHERE `csfile_id` = '" . $EFILE_ID . "'";
                $result    = $db->GetRow($query);
                if($result) {                
                    $PROCESSED["file_title"] = trim($result["file_title"]);
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
                                var eventID = "<?php echo $EVENT_ID?>";
                                var EFILE_ID = "<?php echo $EFILE_ID?>";
                                var action_field = "<?php echo $action_field?>";
                                var action = "<?php echo $action?>";
                                var module = "<?php echo $MODULE?>";
                                var dataString = 'sortOrder=' + sortOrder + '&sortID=' + sortID + '&EFILE_ID=' + EFILE_ID + '&action_field=' + action_field + '&action=' + action + '&module=' + module;
                                var url = '<?php echo ENTRADA_URL . "/api/stats-community-file.api.php";?>'
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
                    <div class="modal-dialog" id="file-edit-wizard-<?php echo $EFILE_ID; ?>">
                        <div id="wizard">
                            <h2 title="Event Statistics Section">File Statistics</h2>
                            <h3><?php echo html_encode($PROCESSED["file_title"]); ?></h3>
                            <div id="bodyStats">
                                <ul class="statsUL">
                                    <li class="statsLI"><span class="statsLISpan1">Number of users who downloaded this file: </span><span id="userViews"><strong><?php echo $userViews?></strong></span></li>
                                    <li class="statsLI"><span class="statsLISpan1">Total downloaded of this file: </span><span id="totalViews"><strong><?php echo $totalViews?></strong></span></li>
                                </ul>
                                <ul class="statsUL">
                                    <li class="statsLIHeader"><span class="sortStatsHeader ASC sortStatsNameModel" id="name">Name</span><span class="sortStatsHeader ASC sortStatsViewsModel" id="view">Saves</span><span class="sortStatsHeader ASC sortStatsDateModel" id="date">Last saved on</span></li>
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
                    $ERRORSTR[] = "The provided file identifier does not exist in the provided event.";

                    echo display_error();

                    application_log("error", "file/link event statistics was accessed with a file id that was not found in the database.");
                }
            } else {
                $ERROR++;
                $ERRORSTR[] = "You must provide a file identifier when using the file wizard.";

                echo display_error();

                application_log("error", "File wizard was accessed without any file id.");
            }
        break;
        case "link" :

            break;
        default :

    }
}
                ?>