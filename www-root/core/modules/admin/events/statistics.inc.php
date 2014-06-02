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
 * This file is used to view the statistics (i.e. views
 * etc.) within a learning event from the entrada.statistics table.
 *
 * @author Organization: David Geffen School of Medicine at UCLA
 * @author Unit: Instructional Design and Technology Unit
 * @author Developer: Sam Payne <spayne@mednet.ucla.edu>
 * @copyright Copyright 2013 Regents of The University of California. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	if ($EVENT_ID) {
		$query		= "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);

		if ($event_info) {
			if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")) {
				application_log("error", "Someone attempted to view statistics for an event [".$EVENT_ID."] that they were not the coordinator for.");

				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "Statistics", "id" => $EVENT_ID)), "title" => "Event Statistics");
                                $PROCESSED["proxy_id"] = $ENTRADA_USER->getID();
                                //This will create a record set that has the proxyid, firstname, lastname, last timestamp, view per user.
                                $viewsSQL = "   SELECT DISTINCT (stats.proxy_id), COUNT(*) AS views, users.firstname, users.lastname, MAX(stats.timestamp) as lastViewedTime
                                                FROM `".DATABASE_NAME."`.statistics AS stats, `".AUTH_DATABASE."`.user_data AS users
                                                WHERE stats.module = 'events'
                                                AND stats.action = 'view'
                                                AND stats.action_field = 'event_id' 
                                                AND stats.action_value = " . $EVENT_ID . " 
                                                AND stats.proxy_id = users.id
                                                GROUP BY stats.proxy_id
                                                ORDER BY users.lastname ASC";
                                $statistics = $db->GetAll($viewsSQL);


                                $totalViews = 0;
                                $userViews = 0;
                                $statsHTML = "";
                                foreach ($statistics as $stats) {
                                    $statsHTML .=   "<li class='statsLI'><span class='sortStats sortStatsName'>" . $stats["lastname"] . ", " . $stats["firstname"] . "</span><span class='sortStats sortStatsViews'>" . $stats["views"] . "</span><span class='sortStats sortStatsDate'>" . date("m-j-Y g:ia", $stats["lastViewedTime"]) . "</span></li>";
                                    //$statsHTML .= " <tr><td>" . $stats["lastname"] . ", " . $stats["firstname"] . "</td>
                                                   // <td>" . $stats["views"] . "</td>
                                                   // <td>" . date("m-j-Y g:ia", $stats["lastViewedTime"]) . "</td></tr>";
                                    $userViews++;
                                    $totalViews = $totalViews + $stats["views"];
                                }


                                events_subnavigation($event_info,'statistics');
                                echo "<div class=\"content-small\">".fetch_course_path($event_info["course_id"])."</div>\n";
				echo "<h1 id=\"page-top\" class=\"event-title\">".html_encode($event_info["event_title"])."</h1>\n";
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
                                                    var dataString = 'sortOrder=' + sortOrder + '&sortID=' + sortID + '&eventID=' + eventID;
                                                    var url = '<?php echo ENTRADA_URL . "/api/stats-event-view.php";?>'
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
					<h2 title="Event Statistics Section">Event Statistics</h2>
                                        <ul class="statsUL">
                                            <li class="statsLI"><span class="statsLISpan1">Number of users who viewed this event: </span><span id="userViews"><strong><?php echo $userViews?></strong></span></li>
                                            <li class="statsLI"><span class="statsLISpan1">Total views of this event: </span><span id="totalViews"><strong><?php echo $totalViews?></strong></span></li>
                                        </ul>
                                        <ul class="statsUL">
                                            <li class="statsLIHeader"><span class="sortStatsHeader ASC sortStatsName" id="name">Name</span><span class="sortStatsHeader ASC sortStatsViews" id="view">Views</span><span class="sortStatsHeader ASC sortStatsDate" id="date">Last viewed on</span></li>
                                            <div id="statsHTML"><?php echo $statsHTML ?></div>
                                        </ul>
                                        <p class="content-small">Click on title to change sort</p>
                                <?Php
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to view event update history you must provide a valid event identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer when attempting to view event updates.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to view event update history you must provide the events identifier.";

		echo display_error();

		application_log("notice", "Failed to provide event identifer when attempting to view history of an event.");
	}
}