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
 * This file is used to display events from the entrada.events table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_RELATIVE);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('event', 'read', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_RELATIVE."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	
	$USE_QUERY = false;
	$EVENT_ID = 0;
	$RESULT_ID = 0;
	$RESULT_TOTAL_ROWS = 0;
	$PREFERENCES = preferences_load($MODULE);

	/**
	 * Process any sorting or pagination requests.
	 */
	events_process_sorting();

	/**
	 * Check to see if they are trying to view an event using an event_id.
	 */
	if ((isset($_GET["id"])) && ($tmp_input = clean_input($_GET["id"], array("nows", "int")))) {
		$EVENT_ID = $tmp_input;
	}

	/**
	 * Check to see if they are tring to view an event using a result set id (either from the
	 * dashboard (drid) or from the events page (rid).
	 */
	if (isset($_GET["drid"])) {
		$RESULT_ID = (int) trim($_GET["drid"]);

		if (($RESULT_ID) || ($RESULT_ID === 0)) {
			if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dashboard"]["previous_query"]["query"])) {
				$query = sprintf($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dashboard"]["previous_query"]["query"], "`events`.`event_start` ASC", $RESULT_ID, 1);
				$result	= ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query) : $db->GetRow($query));
				if ($result) {
					$USE_QUERY = true;

					$EVENT_ID = (int) $result["event_id"];
					$RESULT_TOTAL_ROWS = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dashboard"]["previous_query"]["total_rows"];

					$_SERVER["QUERY_STRING"] = replace_query(array("rid" => false));
				}
			}

			if (!$USE_QUERY) {
				$_SERVER["QUERY_STRING"] = replace_query(array("drid" => false));
			}
		}
	} elseif (isset($_GET["rid"])) {
		$RESULT_ID = (int) trim($_GET["rid"]);

		if ($RESULT_ID || ($RESULT_ID === 0)) {
			$sort_by = events_fetch_sorting_query($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"], $_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]);
			
			if (isset($_GET["community"]) && $community_id = ((int)$_GET["community"])) {
				if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["community_page"][$community_id]["previous_query"]["query"])) {
					$query	= sprintf($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["community_page"][$community_id]["previous_query"]["query"], $sort_by, $RESULT_ID, 1);

					$result	= ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query) : $db->GetRow($query));
					if ($result) {
						$USE_QUERY = true;
	
						$EVENT_ID = (int) $result["event_id"];
						$RESULT_TOTAL_ROWS = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["community_page"][$community_id]["previous_query"]["total_rows"];
	
						$_SERVER["QUERY_STRING"] = replace_query(array("drid" => false));
					}
				}
			} else {
				if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["previous_query"]["query"])) {
					$query = sprintf($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["previous_query"]["query"], $sort_by, $RESULT_ID, 1);
					$result	= ((USE_CACHE) ? $db->CacheGetRow(CACHE_TIMEOUT, $query) : $db->GetRow($query));
					if ($result) {
						$USE_QUERY = true;

						$EVENT_ID = (int) $result["event_id"];
						$RESULT_TOTAL_ROWS = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["previous_query"]["total_rows"];

						$_SERVER["QUERY_STRING"] = replace_query(array("drid" => false));
					}
				}
			}

			if (!$USE_QUERY) {
				$_SERVER["QUERY_STRING"] = replace_query(array("rid" => false));
			}
		}
	}


	/**
	 * Check for groups which have access to the administrative side of this module
	 * and add the appropriate toggle sidebar item.
	 */
	if ($ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
		switch ($_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]) {
			case "admin" :
				$admin_wording = "Administrator View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "manage", "id" => $EVENT_ID)) : "");
			break;
			case "pcoordinator" :
				$admin_wording = "Coordinator View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "manage", "id" => $EVENT_ID)) : "");
			break;
			case "director" :
				$admin_wording = "Director View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "manage", "id" => $EVENT_ID)) : "");
			break;
			case "teacher" :
			case "faculty" :
			case "lecturer" :
				$admin_wording = "Teacher View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "manage", "id" => $EVENT_ID)) : "");
			break;
			default :
				$admin_wording = "";
				$admin_url = "";
			break;
		}

		$sidebar_html  = "<ul class=\"menu none\">\n";
		$sidebar_html .= "	<li><a href=\"".ENTRADA_RELATIVE."/events".(($EVENT_ID) ? "?".replace_query(array("id" => $EVENT_ID, "action" => false)) : "")."\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-on.gif\" alt=\"\" /> <span>Student View</span></a></li>\n";
		if (($admin_wording) && ($admin_url)) {
			$sidebar_html .= "<li><a href=\"".$admin_url."\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-off.gif\" alt=\"\" /> <span>".html_encode($admin_wording)."</span></a></li>\n";
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Display Style", $sidebar_html, "display-style", "open");
	}
	

	if (isset($_GET["organisation_id"]) && ($organisation = ((int) $_GET["organisation_id"]))) {
		$ORGANISATION_ID = $organisation;
		$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"] = $ORGANISATION_ID;
	} else {
		if (isset($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"]) && $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"]) {
			$ORGANISATION_ID = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"];
		} else {
			$ORGANISATION_ID = $_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["organisation_id"];
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"] = $ORGANISATION_ID;
		}
	}

	$sidebar_html  = "<div style=\"text-align: center\">\n";
	$sidebar_html .= "	<a href=\"".ENTRADA_RELATIVE."/podcasts\"><img src=\"".ENTRADA_RELATIVE."/images/podcast-dashboard-image.jpg\" width=\"149\" height=\"99\" alt=\"MEdTech Podcasts\" title=\"Subscribe to our Podcast feed.\" border=\"0\"></a><br />\n";
	$sidebar_html .= "	<a href=\"".ENTRADA_RELATIVE."/podcasts\" style=\"color: #557CA3; font-size: 14px\">Podcasts Available</a>";
	$sidebar_html .= "</div>\n";
	new_sidebar_item("Podcasts in iTunes", $sidebar_html, "podcast-bar", "open", "2.1");

	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/events", "title" => "View Events");

	/**
	 * If we were going into the $EVENT_ID
	 */
	if ($EVENT_ID) {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/windows/window.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
		$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/css/windows/default.css\" rel=\"stylesheet\" type=\"text/css\" />";
		$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/css/windows/medtech.css\" rel=\"stylesheet\" type=\"text/css\" />";
		?>
		<script type="text/javascript">
			function beginQuiz(id) {
				Dialog.confirm('Do you really wish to begin your attempt of this quiz? The timer will begin immediately if this quiz has a time-limit, and you will only have until that timer expires to answer the questions before the quiz is closed to you.',
					{
						id:				'requestDialog',
						width:			350,
						height:			125,
						title:			'Quiz Start Confirmation',
						className:		'medtech',
						okLabel:		'Yes',
						cancelLabel:	'No',
						closable:		'true',
						buttonClass:	'button small',
						ok:				function(win) {
											window.location = '<?php echo ENTRADA_RELATIVE; ?>/quizzes?section=attempt&id='+id;
											return true;
										}
					}
				);
			}
		</script>
		<?php
		$query = "	SELECT a.*, b.`organisation_id`
					FROM `events` AS a
					LEFT JOIN `courses` AS b
					ON b.`course_id` = a.`course_id`
					WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);
		if (!$event_info) {
			$ERROR++;
			$ERRORSTR[] = "The requested learning event does not exist in the system.";

			echo display_error();
		} else {
			$LASTUPDATED = $event_info["updated_date"];

			if (($event_info["release_date"]) && ($event_info["release_date"] > time())) {
				$ERROR++;
				$ERRORSTR[] = "The event you are trying to view is not yet available. Please try again after ".date("r", $event_info["release_date"]);

				echo display_error();
			} elseif (($event_info["release_until"]) && ($event_info["release_until"] < time())) {
				$ERROR++;
				$ERRORSTR[] = "The event you are trying to view is no longer available; it expired ".date("r", $event_info["release_until"]);

				echo display_error($errorstr);
			} else {
				if ($ENTRADA_ACL->amIAllowed(new EventResource($EVENT_ID, $event_info['course_id'], $event_info['organisation_id']), 'read')) {
					add_statistic($MODULE, "view", "event_id", $EVENT_ID);
					if ($event_info["parent_id"]) {
						$event_resources = fetch_event_resources($event_info["parent_id"], array("files", "links", "quizzes"));
						$event_files = $event_resources["files"];
						$event_links = $event_resources["links"];
						$event_quizzes = $event_resources["quizzes"];
						$event_resources = fetch_event_resources($EVENT_ID, array("discussions", "types"));
						$event_discussions = $event_resources["discussions"];
						$event_types = $event_resources["types"];
						$query = "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($event_info["parent_id"]);
						$parent_info = $db->GetRow($query);
						$event_info["event_title"] = $parent_info["event_title"]." - ".$event_info["event_title"];
						$event_info["event_objectives"] = $parent_info["event_objectives"];
						$event_info["release_date"] = $parent_info["release_date"];
						$event_info["release_until"] = $parent_info["release_until"];
						$event_info["event_phase"] = $parent_info["event_phase"];
						$event_info["course_id"] = $parent_info["course_id"];
						if ($event_info["include_parent_description"] && $parent_info["event_description"]) {
							$event_info["event_description"] = $parent_info["event_description"].(isset($event_info["event_description"]) && $event_info["event_description"] ? "\n\n<br/><br/>".$event_info["event_description"] : "");
						}
						if ($event_info["include_parent_message"] && $parent_info["event_message"]) {
							$event_info["event_message"] = $parent_info["event_message"].(isset($event_info["event_message"]) && $event_info["event_message"] ? "\n\n<br/><br/>".$event_info["event_message"] : "");
						}
					} else {
						$event_resources = fetch_event_resources($EVENT_ID, "all");
						$event_files = $event_resources["files"];
						$event_links = $event_resources["links"];
						$event_quizzes = $event_resources["quizzes"];
						$event_discussions = $event_resources["discussions"];
						$event_types = $event_resources["types"];
					}
					
					/**
					 * Gather the learners associated with this event from the event_audience table.
					 */
					$associated_learners = array();;
					$associated_cohort_ids = array();;
					$associated_group_ids = array();;

					$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID);
					$results = $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$associated_learners[$result["audience_type"]] = $result["audience_value"];
							$event_audience_type = $result["audience_type"];
							if ($result["audience_type"] == "cohort") {
								$associated_cohort_ids = $result["audience_value"];
							} elseif ($result["audience_type"] == "group_id") {
								$associated_group_ids = $result["audience_value"];
							}
						}
					}

					// Meta information for this page.
					$PAGE_META["title"]			= $event_info["event_title"]." - ".APPLICATION_NAME;
					$PAGE_META["description"]	= trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($event_info["event_goals"]))));
					$PAGE_META["keywords"]		= "";

					$BREADCRUMB[]				= array("url" => ENTRADA_RELATIVE."/events?".replace_query(array("id" => $event_info["event_id"])), "title" => $event_info["event_title"]);

					$include_details			= true;
					$include_audience			= true;
					$include_objectives			= false;
					$include_resources			= true;
					$include_comments			= true;

					$icon_resources				= ((((is_array($event_files)) && (count($event_files))) || ((is_array($event_links)) && (count($event_links))) || ((is_array($event_quizzes)) && (count($event_quizzes)))) ? true : false);
					$icon_discussion			= (((is_array($event_discussions)) && (count($event_discussions))) ? true : false);
					$icon_course_website		= true;

					$resources_title			= (($icon_resources) ? "Download or view the attached event resources." : "There are no event resources currently attached.");
					$discussion_title			= (($icon_discussion) ? "Read the posted discussion comments." : "Start up a conversion, leave your comment!");
					$syllabus_title				= "Visit Course Website";

					if (($_SESSION["details"]["allow_podcasting"]) && ($event_audience_type == "cohort") && (in_array($_SESSION["details"]["allow_podcasting"], array($associated_cohort, "all")))) {
						$sidebar_html = "To upload a podcast: <a href=\"javascript:openPodcastWizard('".$EVENT_ID."')\">click here</a>";
						new_sidebar_item("Upload A Podcast", $sidebar_html, "podcast_uploading", "open", "2.0");
						?>
						<script type="text/javascript">
							function openPodcastWizard(id) {
								if (!id) {
									return;
								} else {
									var windowW = 485;
									var windowH = 585;

									var windowX = (screen.width / 2) - (windowW / 2);
									var windowY = (screen.height / 2) - (windowH / 2);

									fileWizard = window.open('<?php echo ENTRADA_RELATIVE ?>/file-wizard-podcast.php?id=' + id, 'podcastWizard', 'width='+windowW+', height='+windowH+', scrollbars=no, resizable=yes');
									fileWizard.blur();
									window.focus();

									fileWizard.resizeTo(windowW, windowH);
									fileWizard.moveTo(windowX, windowY);

									fileWizard.focus();
								}
							}
						</script>
						<?php
					}

					echo "<div class=\"no-printing\">\n";
					if (($USE_QUERY) && ($RESULT_TOTAL_ROWS > 1)) {
						echo "<table style=\"width: 100%; height: 23px\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
						echo "<tr>\n";
						echo "	<td style=\"width: 22px; height: 23px\">";
						if ($RESULT_ID > 0) {
							echo "<a href=\"".ENTRADA_RELATIVE."/events?".replace_query(array(((isset($_GET["drid"])) ? "drid" : "rid") => ($RESULT_ID - 1)))."\" title=\"Previous Event\"><img src=\"".ENTRADA_RELATIVE."/images/cal-back.gif\" border=\"0\" width=\"22\" height=\"23\" alt=\"Previous Event\" title=\"Previous Event\" /></a>";
						} else {
							echo "<img src=\"".ENTRADA_RELATIVE."/images/cal-back-off.gif\" border=\"0\" width=\"22\" height=\"23\" alt=\"\" title=\"\" />";
						}
						echo "	</td>\n";
						echo "	<td style=\"width: 100%; height: 23px; background: url('".ENTRADA_RELATIVE."/images/cal-table-bg.gif') #FFFFFF repeat-x; text-align: center; font-size: 10px; color: #666666; white-space: nowrap; overflow: hidden\">".html_encode($event_info["event_title"])."</td>\n";
						echo "	<td style=\"width: 22px; height: 23px\">";
						if ($RESULT_ID < ($RESULT_TOTAL_ROWS - 1)) {
							echo "<a href=\"".ENTRADA_RELATIVE."/events?".replace_query(array(((isset($_GET["drid"])) ? "drid" : "rid") => ($RESULT_ID + 1)))."\" title=\"Next Event\"><img src=\"".ENTRADA_RELATIVE."/images/cal-next.gif\" border=\"0\" width=\"22\" height=\"23\" alt=\"Next Event\" title=\"Next Event\" /></a>";
						} else {
							echo "<img src=\"".ENTRADA_RELATIVE."/images/cal-next-off.gif\" width=\"22\" height=\"23\" alt=\"\" title=\"\" />";
						}
						echo "	</td>\n";
						echo "</tr>\n";
						echo "</table>\n";
					}

					echo "	<div style=\"text-align: right; margin-top: 8px\">\n";
					echo "		<a href=\"".ENTRADA_RELATIVE."/events?id=".$event_info["event_id"]."\"><img src=\"".ENTRADA_RELATIVE."/images/page-link.gif\" width=\"16\" height=\"16\" alt=\"Link to this page\" title=\"Link to this page\" border=\"0\" style=\"margin-right: 3px; vertical-align: middle\" /></a> <a href=\"".ENTRADA_RELATIVE."/events?id=".$event_info["event_id"]."\" style=\"font-size: 10px; margin-right: 8px\">Link to this page</a>\n";
					echo "		<a href=\"javascript:window.print()\"><img src=\"".ENTRADA_RELATIVE."/images/page-print.gif\" width=\"16\" height=\"16\" alt=\"Print this page\" title=\"Print this page\" border=\"0\" style=\"margin-right: 3px; vertical-align: middle\" /></a> <a href=\"javascript: window.print()\" style=\"font-size: 10px; margin-right: 8px\">Print this page</a>\n";
					echo "	</div>\n";

					echo "</div>\n";

					echo "<div class=\"content-small\">";
					if ($event_info["course_id"]) {
						$curriculum_path = curriculum_hierarchy($event_info["course_id"]);

						if ((is_array($curriculum_path)) && (count($curriculum_path))) {
							echo implode(" &gt; ", $curriculum_path);
						}
					} else {
						echo "No Associated Course";
					}
					echo "</div>\n";
					echo "<h1 class=\"event-title\">".html_encode($event_info["event_title"])."</h1>\n";
					
					echo "<div style=\"float: right; width: 215px;\">\n";
//					echo "   <ul class=\"access-legend\">\n";
//					echo "     <li><span style=\"font-weight: bold\">Quick Access Legend</span>\n";
//					echo "         <ul>\n";
//					echo "           <li><a href=\"#event-resources-section\" onclick=\"$('event-resources-section').scrollTo(); return false;\" title=\"".$resources_title."\"><img src=\"".ENTRADA_RELATIVE."/images/icon-resources-".(($icon_resources) ? "on" : "off").".gif\" width=\"36\" height=\"36\" alt=\"".$resources_title."\" title=\"".$resources_title."\" border=\"0\" style=\"vertical-align: middle; margin-right: 5px\" />Event Resources</a></li>\n";
//					echo "           <li><a href=\"#event-comments-section\" onclick=\"$('event-comments-section').scrollTo(); return false;\" title=\"".$discussion_title."\"><img src=\"".ENTRADA_RELATIVE."/images/icon-discussion-".(($icon_discussion) ? "on" : "off").".gif\" width=\"36\" height=\"36\" alt=\"".$discussion_title."\" title=\"".$discussion_title."\" border=\"0\" style=\"vertical-align: middle; margin-right: 5px\" />Event Discussions</a></li>\n";
//					echo "           <li><a href=\"\"><img src=\"".ENTRADA_RELATIVE."/images/icon-syllabus-on.gif\" width=\"36\" height=\"36\" alt=\"".$syllabus_title."\" title=\"".$syllabus_title."\" border=\"0\" style=\"vertical-align: middle; margin-right: 5px\" />".$syllabus_title."</a></li>\n";
//					echo "         </ul>\n";
//					echo "     </li>\n";
//					echo "   </ul>\n";
					
require_once("Models/utility/Collection.class.php");
require_once("Models/utility/SimpleCache.class.php");

require_once("Models/users/User.class.php");
require_once("Models/users/Users.class.php");
require_once("Models/users/Assistant.class.php");
require_once("Models/users/UserPhoto.class.php");
require_once("Models/users/UserPhotos.class.php");
require_once("Models/users/Cohort.class.php");
require_once("Models/organisations/Organisation.class.php");
require_once("Models/organisations/Organisations.class.php");
require_once("Models/users/Department.class.php");
require_once("Models/users/Departments.class.php");
require_once("Models/events/EventContacts.class.php");
require_once("Models/events/Event.class.php");
require_once("Models/events/Events.class.php");
require_once("Models/courses/Course.class.php");
require_once("Models/courses/Courses.class.php");

require_once("Models/regions/Region.class.php");
require_once("Models/regions/Country.class.php");

require_once("Models/tasks/Tasks.class.php");
require_once("Models/tasks/Task.class.php");
require_once("Models/tasks/TaskOwners.class.php");
require_once("Models/tasks/TaskRecipients.class.php");
require_once("Models/tasks/TaskCompletions.class.php");
require_once("Models/tasks/TaskAssociatedFaculty.class.php");
require_once("Models/tasks/TaskVerifiers.class.php");

					$query		= "	SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`, a.`contact_order`
									FROM `event_contacts` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
									ON b.`id` = a.`proxy_id`
									WHERE a.`event_id` = ".$db->qstr($event_info["event_id"])."
									AND b.`id` IS NOT NULL
									
									UNION
									
									SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`, a.`contact_order`
									FROM `event_contacts` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
									ON b.`id` = a.`proxy_id`
									WHERE a.`event_id` = ".$db->qstr($event_info["parent_id"])."
									AND b.`id` IS NOT NULL
									GROUP BY a.`proxy_id`
									ORDER BY `contact_order` ASC";
					$faculty = $db->GetAll($query);
					if ($faculty) {
						echo "<ul class=\"access-legend\">\n";
						echo "	<li><span style=\"font-weight: bold\">Faculty Members</span>\n";
						echo "		<ul>\n";
						foreach ($faculty as $result) {
							
							$photos = UserPhotos::get($result["proxy_id"]);
							
//							$photo = new UserPhoto(UserPhoto::fromArray($official));

//							$feat_str = $official_photo->getFilename();
							Zend_Debug::dump($photos);
							
							echo "		<li><a href=\"mailto:".html_encode($result["email"])."\">".html_encode($result["fullname"])."</a></li>\n";
						}
						echo "		</ul>\n";
						echo "	</li>\n";
						echo "</ul>\n";
					}					
					echo "</div>\n";

					echo "<div style=\"float: left; width: 520px\">\n";
					
					if (clean_input($event_info["event_description"], array("notags", "nows")) != "") {
						echo "<div style=\"font-size: 14px; line-height: 150%\">";
						echo trim(strip_selected_tags($event_info["event_description"], array("font")));
						echo "</div>";
					}
					
					echo "	<a name=\"event-details-section\"></a>\n";
					echo "	<h2 title=\"Event Details Section\">Event Details</h2>\n";
					echo "	<div id=\"event-details-section\" class=\"section-holder\">\n";
					echo "		<table style=\"width: 100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" summary=\"Detailed Event Information\">\n";
					echo "		<colgroup>\n";
					echo "			<col style=\"width: 25%\" />\n";
					echo "			<col style=\"width: 75%\" />\n";
					echo "		</colgroup>\n";
					echo "		<tbody>\n";
					echo "			<tr>\n";
					echo "				<td>Date &amp; Time</td>\n";
					echo "				<td>".date(DEFAULT_DATE_FORMAT, $event_info["event_start"])."</td>\n";
					echo "			</tr>\n";
					echo "			<tr>\n";
					echo "				<td>Location</td>\n";
					echo "				<td>".(($event_info["event_location"]) ? $event_info["event_location"] : "To Be Announced")."</td>\n";
					echo "			</tr>\n";
					
					/**
					 * @todo This needs to be fixed as $event_audience_type is no longer for cohort.
					 */
					if ($event_audience_type == "cohort") {
						$cohort = groups_get_cohort($_SESSION["details"]["id"]);
						$query		= "	SELECT a.`event_id`, a.`event_title`, b.`audience_value` AS `event_cohort`
										FROM `events` AS a
										LEFT JOIN `event_audience` AS b
										ON b.`event_id` = a.`event_id`
										LEFT JOIN `courses` AS c
										ON a.`course_id` = c.`course_id`
										AND c.`organisation_id` = ".$db->qstr($event_info["organisation_id"])."
										WHERE (a.`event_start` BETWEEN ".$db->qstr($event_info["event_start"])." AND ".$db->qstr(($event_info["event_finish"] - 1)).")
										AND c.`course_active` = '1'
										AND a.`event_id` <> ".$db->qstr($event_info["event_id"])."
										AND b.`audience_type` = 'cohort'
										AND b.`audience_value` = ".$db->qstr((int) $cohort["group_id"])."
										ORDER BY `event_title` ASC";
						$results	= $db->GetAll($query);
						if ($results) {
							echo "	<tr>\n";
							echo "		<td colspan=\"2\">&nbsp;</td>\n";
							echo "	</tr>\n";
							echo "	<tr>\n";
							echo "		<td style=\"vertical-align: top\">Overlapping Event".((count($results) != 1) ? "s" : "")."</td>\n";
							echo "		<td>\n";
							foreach ($results as $result) {
								echo "		<a href=\"".ENTRADA_RELATIVE."/events?id=".$result["event_id"]."\">".html_encode($result["event_title"])."</a><br />\n";
							}
							echo "		</td>\n";
							echo "	</tr>\n";
						}
					}
					
					if ($event_types) {
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		<td style=\"vertical-align: top\">Event Type".(count($event_types) > 1 ? "s" : "")."</td>\n";
						echo "		<td>";
						foreach($event_types as $type) {
							echo html_encode($type["eventtype_title"]) . " <span class=\"content-small\">".$type["duration"]." minutes</span><br />";
						}
						echo "		";
						echo "		</td>\n";
						echo "	</tr>\n";
					}

					if ((!$event_types) || (count($event_types) > 1)) {
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		<td>Event Duration</td>\n";
						echo "		<td>".(((int) $event_info["event_duration"]) ? $event_info["event_duration"]." minutes" : "To Be Announced")."</td>\n";
						echo "	</tr>\n";
					}					

					echo "			<tr>\n";
					echo "				<td colspan=\"2\">&nbsp;</td>\n";
					echo "			</tr>\n";

					if (clean_input($event_info["event_message"], array("notags", "nows")) != "") {
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">&nbsp;</td>\n";
						echo "	</tr>\n";
						echo "	<tr>\n";
						echo "		<td colspan=\"2\">\n";
						echo "			<h3>Teacher's Message</h3>\n";
						echo			trim(strip_selected_tags($event_info["event_message"], array("font")));
						echo "		</td>\n";
						echo "	</tr>\n";
					}
					
					echo "			<tr>\n";
					echo "				<td colspan=\"2\">&nbsp;</td>\n";
					echo "			</tr>\n";

					echo "	<tr>\n";
					echo "		<td style=\"vertical-align: top\">Learners</td>\n";
					echo "		<td style=\"vertical-align: top\">\n";
					echo "			<ul class=\"general-list\">";
					
					switch ($event_audience_type) {
						case "course_id" :
							echo "<li>Course ID: ".$group_id."</li>\n";
						break;
						case "group_id" :
							echo "	<tr>\n";
							echo "		<td style=\"vertical-align: top\"></td>\n";
							echo "		<td style=\"vertical-align: top\">\n";
							echo "			<ul class=\"general-list\">";
							foreach ($associated_group_ids as $group_id) {
								echo "			<li>".html_encode(groups_get_name($group_id))."</li>\n";
							}
							echo "			</ul>\n";
							echo "		</td>\n";
							echo "	</tr>\n";
						break;
						case "cohort" :
							echo "	<tr>\n";
							echo "		<td style=\"vertical-align: top\"></td>\n";
							echo "		<td style=\"vertical-align: top\">\n";
							echo "			<ul class=\"general-list\">";
							foreach ($associated_cohort_ids as $group_id) {
								echo "			<li>".html_encode(groups_get_name($group_id))."</li>\n";
							}
							echo "			</ul>\n";
							echo "		</td>\n";
							echo "	</tr>\n";
						break;
						case "proxy_id" :
							echo "	<tr>\n";
							echo "		<td style=\"vertical-align: top\">Learner".((count($associated_proxy_ids) != 1) ? "s" : "")."</td>\n";
							echo "		<td style=\"vertical-align: top\">\n";
							echo "			<ul class=\"general-list\">";
							foreach ($associated_proxy_ids as $proxy_id) {
								echo "			<li><a href=\"".ENTRADA_RELATIVE."/people?profile=".get_account_data("username", $proxy_id)."\" target=\"_blank\">".get_account_data("firstlast", $proxy_id)."</a></li>\n";
							}
							echo "			</ul>\n";
							echo "		</td>\n";
							echo "	</tr>\n";
						break;
					}					
					echo "			</ul>\n";
					echo "		</td>\n";
					echo "	</tr>\n";
					echo "		</tbody>\n";
					echo "		</table>\n";
					echo "	</div>\n";

					$query					= "	SELECT b.`objective_id`, b.`objective_name`
												FROM `event_objectives` AS a
												LEFT JOIN `global_lu_objectives` AS b
												ON b.`objective_id` = a.`objective_id`
												WHERE a.`objective_type` = 'event'
												AND b.`objective_active` = '1'
												AND a.`event_id` = ".$db->qstr($EVENT_ID)."
												ORDER BY b.`objective_name` ASC;";
					$clinical_presentations	= $db->GetAll($query);

					$show_event_objectives	= ((clean_input($event_info["event_objectives"], array("notags", "nows")) != "") ? true : false);
					$show_clinical_presentations = (($clinical_presentations) ? true : false);

					$show_curriculum_objectives = false;
					$curriculum_objectives = courses_fetch_objectives(array($event_info["course_id"]), 1, false, false, $EVENT_ID, true);
					
					$temp_objectives = $curriculum_objectives["objectives"];
					foreach ($temp_objectives as $objective_id => $objective) {
						unset($curriculum_objectives["used_ids"][$objective_id]);
						$curriculum_objectives["objectives"][$objective_id]["objective_primary_children"] = 0;
						$curriculum_objectives["objectives"][$objective_id]["objective_secondary_children"] = 0;
						$curriculum_objectives["objectives"][$objective_id]["objective_tertiary_children"] = 0;
					}
					foreach ($curriculum_objectives["objectives"] as $objective_id => $objective) {
						if (isset($objective["event_objective"]) && $objective["event_objective"]) {
							foreach ($objective["parent_ids"] as $parent_id) {
								if ($objective["primary"] || $objective["secondary"] || $objective["tertiary"] || $curriculum_objectives["objectives"][$parent_id]["primary"] || $curriculum_objectives["objectives"][$parent_id]["secondary"] || $curriculum_objectives["objectives"][$parent_id]["tertiary"]) {
									$curriculum_objectives["objectives"][$parent_id]["objective_".($objective["primary"] || ($curriculum_objectives["objectives"][$parent_id]["primary"] && !$objective["secondary"] && !$objective["tertiary"]) ? "primary" : ($objective["secondary"] || ($curriculum_objectives["objectives"][$parent_id]["secondary"] && !$objective["primary"] && !$objective["tertiary"]) ? "secondary" : "tertiary"))."_children"]++;
								if ($curriculum_objectives["objectives"][$parent_id]["primary"]) {
									$curriculum_objectives["objectives"][$objective_id]["primary"] = true;
								} elseif ($curriculum_objectives["objectives"][$parent_id]["secondary"]) {
									$curriculum_objectives["objectives"][$objective_id]["secondary"] = true;
								} elseif ($curriculum_objectives["objectives"][$parent_id]["tertiary"]) {
									$curriculum_objectives["objectives"][$objective_id]["tertiary"] = true;
								} 
							}
							}
							$show_curriculum_objectives = true;
						}
					}
					foreach ($temp_objectives as $objective_id => $objective) {
						if (!isset($objective["event_objective"]) || !$objective["event_objective"]) {
							if (isset($objective["primary"]) && $objective["primary"] && !$curriculum_objectives["objectives"][$objective_id]["objective_primary_children"]) {
								$curriculum_objectives["objectives"][$objective_id]["primary"] = false;
							} elseif (isset($objective["secondary"]) && $objective["secondary"] && !$curriculum_objectives["objectives"][$objective_id]["objective_secondary_children"]) {
								$curriculum_objectives["objectives"][$objective_id]["secondary"] = false;
							} elseif (isset($objective["tertiary"]) && $objective["tertiary"] && !$curriculum_objectives["objectives"][$objective_id]["objective_tertiary_children"]) {
								$curriculum_objectives["objectives"][$objective_id]["tertiary"] = false;
							}
						}
					}
					if ($show_event_objectives || $show_clinical_presentations || $show_curriculum_objectives) {
						$include_objectives = true;

						echo "<a name=\"event-objectives-section\"></a>\n";
						echo "<h2 title=\"Event Objectives Section\">Event Objectives</h2>\n";
						echo "<div id=\"event-objectives-section\">\n";

						if ($show_event_objectives) {
							echo "	<div class=\"section-holder\">\n";
							echo "		<h3>Free-Text Objectives</h3>\n";
							echo		trim(strip_selected_tags($event_info["event_objectives"], array("font")));
							echo "	</div>\n";
						}

						if ($show_clinical_presentations) {
							echo "	<div class=\"section-holder\">\n";
							echo "		<h3>Clinical Presentations</h3>\n";
							foreach ($clinical_presentations as $key => $result) {
								echo (($key) ? ", " : "").$result["objective_name"];
							}
							echo "	</div>\n";
						}
						
						if ($show_curriculum_objectives) {
							?>
							<script type="text/javascript">
							function renewList (hierarchy) {
								if (hierarchy != null && hierarchy) {
									hierarchy = 1;
								} else {
									hierarchy = 0;
								}
								new Ajax.Updater('objectives_list', '<?php echo ENTRADA_RELATIVE; ?>/api/objectives.api.php', 
									{
										method:	'post',
										parameters: 'course_ids=<?php echo $event_info["course_id"] ?>&hierarchy='+hierarchy+'&event_id=<?php echo $EVENT_ID; ?>'
							    	}
					    		);
							}
				    		</script>
							<?php
							echo "<div class=\"section-holder\">\n";
							echo "	<h3>Curriculum Objectives</h3>\n";
							echo "	<strong>The learner will be able to:</strong>";
							echo	course_objectives_in_list($curriculum_objectives, 1, false, false, 1, true)."\n";
							echo "</div>\n";
						}
						echo "</div>\n";
					}

					echo "</div>\n";

					echo "<div style=\"clear: both\" />\n";

					echo "<a name=\"event-resources-section\"></a>";
					echo "<h2 title=\"Event Resources Section\">Event Resources</h2>\n";
					echo "<div id=\"event-resources-section\">\n";
					echo "	<div class=\"section-holder\">\n";
					echo "		<h3>Attached Files</h3><a name=\"event-resources-files\"></a>\n";
					echo "		<table class=\"tableList\" cellspacing=\"0\" summary=\"List of File Attachments\">\n";
					echo "		<colgroup>\n";
					echo "			<col class=\"modified\" />\n";
					echo "			<col class=\"file-category\" />\n";
					echo "			<col class=\"title\" />\n";
					echo "			<col class=\"date\" />\n";
					echo "		</colgroup>\n";
					echo "		<thead>\n";
					echo "			<tr>\n";
					echo "				<td class=\"modified\">&nbsp;</td>\n";
					echo "				<td class=\"file-category sortedASC\"><div class=\"noLink\">File Category</div></td>\n";
					echo "				<td class=\"title\"><div class=\"noLink\">File Title</div></td>\n";
					echo "				<td class=\"date\">Last Updated</td>\n";
					echo "			</tr>\n";
					echo "		</thead>\n";
					echo "		<tbody>\n";

					if ($event_files) {
						foreach ($event_files as $result) {
							$filename	= $result["file_name"];
							$parts		= pathinfo($filename);
							$ext		= $parts["extension"];

							echo "	<tr id=\"file-".$result["efile_id"]."\">\n";
							echo "		<td class=\"modified\" style=\"vertical-align: top\">".(((int) $result["last_visited"]) ? (((int) $result["last_visited"] >= (int) $result["updated_date"]) ? "<img src=\"".ENTRADA_RELATIVE."/images/accept.png\" width=\"16\" height=\"16\" alt=\"You have already downloaded the latest version.\" title=\"You have already downloaded the latest version.\" />" : "<img src=\"".ENTRADA_RELATIVE."/images/exclamation.png\" width=\"16\" height=\"16\" alt=\"This file has been updated since you have last downloaded it.\" title=\"This file has been updated since you have last downloaded it.\" />") : "")."</td>\n";
							echo "		<td class=\"file-category\" style=\"vertical-align: top\">".((isset($RESOURCE_CATEGORIES["event"][$result["file_category"]])) ? html_encode($RESOURCE_CATEGORIES["event"][$result["file_category"]]) : "Unknown Category")."</td>\n";
							echo "		<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";
							echo "			<img src=\"".ENTRADA_RELATIVE."/serve-icon.php?ext=".$ext."\" width=\"16\" height=\"16\" alt=\"".strtoupper($ext)." Document\" title=\"".strtoupper($ext)." Document\" style=\"vertical-align: middle\" />\n";
							if (((!(int) $result["release_date"]) || ($result["release_date"] <= time())) && ((!(int) $result["release_until"]) || ($result["release_until"] >= time()))) {
								echo "		<a href=\"".ENTRADA_RELATIVE."/file-event.php?id=".$result["efile_id"]."\" title=\"Click to download ".html_encode($result["file_title"])."\" style=\"font-weight: bold\"".(((int) $result["access_method"]) ? " target=\"_blank\"" : "").">".html_encode($result["file_title"])."</a>";
							} else {
								echo "		<span style=\"color: #666666; font-weight: bold\">".html_encode($result["file_title"])."</span>";
							}
							echo "			<span class=\"content-small\">(".readable_size($result["file_size"]).")</span>";
							echo "			<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
							if (((int) $result["release_date"]) && ($result["release_date"] > time())) {
								echo "		This file will be available for downloading <strong>".date(DEFAULT_DATE_FORMAT, $result["release_date"])."</strong>.<br /><br />";
							} elseif (((int) $result["release_until"]) && ($result["release_until"] < time())) {
								echo "		This file was only available for download until <strong>".date(DEFAULT_DATE_FORMAT, $result["release_until"])."</strong>. Please contact the primary teacher for assistance if required.<br /><br />";
							}
							if (clean_input($result["file_notes"], array("notags", "nows")) != "") {
								echo "		".trim(strip_selected_tags($result["file_notes"], array("font")))."\n";
							}
							echo "			</div>\n";
							echo "		</td>\n";
							echo "		<td class=\"date\" style=\"vertical-align: top\">".(((int) $result["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $result["updated_date"]) : "Unknown")."</td>\n";
							echo "	</tr>\n";
						}
					} else {
						echo "		<tr>\n";
						echo "			<td colspan=\"4\">\n";
						echo "				<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no file downloads added to this event.</div>\n";
						echo "			</td>\n";
						echo "		</tr>\n";
					}
					echo "		</tbody>\n";
					echo "		</table>\n";
					echo "	</div>\n";

					echo "	<div class=\"section-holder\">\n";
					echo "		<h3>Attached Links</h3><a name=\"event-resources-links\"></a>\n";
					echo "		<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Linked Resources\">\n";
					echo "		<colgroup>\n";
					echo "			<col class=\"modified\" />\n";
					echo "			<col class=\"title\" />\n";
					echo "			<col class=\"date\" />\n";
					echo "		</colgroup>\n";
					echo "		<thead>\n";
					echo "			<tr>\n";
					echo "				<td class=\"modified\">&nbsp;</td>\n";
					echo "				<td class=\"title sortedASC\"><div class=\"noLink\">Linked Resource</div></td>\n";
					echo "				<td class=\"date\">Last Updated</td>\n";
					echo "			</tr>\n";
					echo "		</thead>\n";
					echo "		<tbody>\n";
					if ($event_links) {
						foreach ($event_links as $result) {
							echo "	<tr>\n";
							echo "		<td class=\"modified\" style=\"vertical-align: top\">".(((int) $result["last_visited"]) ? (((int) $result["last_visited"] >= (int) $result["updated_date"]) ? "<img src=\"".ENTRADA_RELATIVE."/images/accept.png\" width=\"16\" height=\"16\" alt=\"You have previously visited this link.\" title=\"You have previously visited this link.\" />" : "<img src=\"".ENTRADA_RELATIVE."/images/exclamation.png\" width=\"16\" height=\"16\" alt=\"An update to this link has been made, please re-visit it.\" title=\"An update to this link has been made, please re-visit it.\" />") : "")."</td>\n";
							echo "		<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";
							if (((!(int) $result["release_date"]) || ($result["release_date"] <= time())) && ((!(int) $result["release_until"]) || ($result["release_until"] >= time()))) {
								echo "		<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".$result["elink_id"]."\" title=\"Click to visit ".$result["link"]."\" style=\"font-weight:  bold\" target=\"_blank\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : $result["link"])."</a>\n";
							} else {
								echo "		<span style=\"color: #666666; font-weight: bold\">".(($result["link_title"] != "") ? html_encode($result["link_title"]) : "Untitled Link")."</span>";
							}
							echo "			<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
							if (((int) $result["release_date"]) && ($result["release_date"] > time())) {
								echo "		This link will become accessible <strong>".date(DEFAULT_DATE_FORMAT, $result["release_date"])."</strong>.<br /><br />";
							} elseif (((int) $result["release_until"]) && ($result["release_until"] < time())) {
								echo "		This link was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $result["release_until"])."</strong>. Please contact the primary teacher for assistance if required.<br /><br />";
							}
							if (clean_input($result["link_notes"], array("notags", "nows")) != "") {
								echo "		".trim(strip_selected_tags($result["link_notes"], array("font")))."\n";
							}
							echo "			</div>\n";
							echo "		</td>\n";
							echo "		<td class=\"date\" style=\"vertical-align: top\">".(((int) $result["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $result["updated_date"]) : "Unknown")."</td>\n";
							echo "	</tr>\n";
						}
					} else {
						echo "		<tr>\n";
						echo "			<td colspan=\"3\">\n";
						echo "				<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There have been no linked resources added to this event.</div>\n";
						echo "			</td>\n";
						echo "		</tr>\n";
					}
					echo "		</tbody>\n";
					echo "		</table>\n";
					echo "	</div>\n";

					echo "	<div class=\"section-holder\">\n";
					echo "		<h3>Attached Quizzes</h3><a name=\"event-resources-quizzes\"></a>\n";
					echo "		<table class=\"tableList\" cellspacing=\"0\" summary=\"List of Attached Quizzes\">\n";
					echo "		<colgroup>\n";
					echo "			<col class=\"modified\" />\n";
					echo "			<col class=\"title\" />\n";
					echo "			<col class=\"date\" />\n";
					echo "		</colgroup>\n";
					echo "		<thead>\n";
					echo "			<tr>\n";
					echo "				<td class=\"modified\">&nbsp;</td>\n";
					echo "				<td class=\"title sortedASC\"><div class=\"noLink\">Quiz Title</div></td>\n";
					echo "				<td class=\"date\">Quiz Expires</td>\n";
					echo "			</tr>\n";
					echo "		</thead>\n";
					echo "		<tbody>\n";

					if ($event_quizzes) {
						foreach ($event_quizzes as $quiz_record) {
							$quiz_attempts		= 0;
							$total_questions	= quiz_count_questions($quiz_record["quiz_id"]);

							$query				= "	SELECT *
											FROM `quiz_progress`
											WHERE `aquiz_id` = ".$db->qstr($quiz_record["aquiz_id"])."
											AND `proxy_id` = ".$db->qstr($_SESSION["details"]["id"]);
							$progress_record	= $db->GetAll($query);
							if ($progress_record) {
								$quiz_attempts = count($progress_record);
							}

							$exceeded_attempts	= ((((int) $quiz_record["quiz_attempts"] === 0) || ($quiz_attempts < $quiz_record["quiz_attempts"])) ? false : true);

							if (((!(int) $quiz_record["release_date"]) || ($quiz_record["release_date"] <= time())) && ((!(int) $quiz_record["release_until"]) || ($quiz_record["release_until"] >= time())) && (!$exceeded_attempts)) {
								$allow_attempt = true;
							} else {
								$allow_attempt = false;
							}

							echo "	<tr id=\"quiz-".$quiz_record["aquiz_id"]."\">\n";
							echo "		<td class=\"modified\" style=\"vertical-align: top\">".(((int) $quiz_record["last_visited"]) ? (((int) $quiz_record["last_visited"] >= (int) $quiz_record["updated_date"]) ? "<img src=\"".ENTRADA_RELATIVE."/images/checkmark.gif\" width=\"20\" height=\"20\" alt=\"You have previously completed this quiz.\" title=\"You have previously completed this quiz.\" style=\"vertical-align: middle\" />" : "<img src=\"".ENTRADA_RELATIVE."/images/exclamation.gif\" width=\"20\" height=\"20\" alt=\"This attached quiz has been updated since you last completed it.\" title=\"This attached quiz has been updated since you last completed it.\" style=\"vertical-align: middle\" />") : "")."</td>\n";
							echo "		<td class=\"title\" style=\"vertical-align: top; white-space: normal; overflow: visible\">\n";
							if ($allow_attempt) {
								echo "		<a href=\"javascript: beginQuiz(".$quiz_record["aquiz_id"].")\" title=\"Take ".html_encode($quiz_record["quiz_title"])."\" style=\"font-weight: bold\">".html_encode($quiz_record["quiz_title"])."</a>";
							} else {
								echo "		<span style=\"color: #666666; font-weight: bold\">".html_encode($quiz_record["quiz_title"])."</span>";
							}

							echo "			<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">\n";
							if (((int) $quiz_record["release_date"]) && ($quiz_record["release_date"] > time())) {
								echo "You will be able to attempt this quiz starting <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_date"])."</strong>.<br /><br />";
							} elseif (((int) $quiz_record["release_until"]) && ($quiz_record["release_until"] < time())) {
								echo "This quiz was only available until <strong>".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</strong>. Please contact a teacher for assistance if required.<br /><br />";
							}

							echo quiz_generate_description($quiz_record["required"], $quiz_record["quiztype_code"], $quiz_record["quiz_timeout"], $total_questions, $quiz_record["quiz_attempts"], $quiz_record["timeframe"]);
							echo "			</div>\n";

							if ($progress_record) {
								echo "<strong>Your Attempts</strong>";
								echo "<ul class=\"menu\">";
								foreach ($progress_record as $entry) {
									$quiz_start_time	= $entry["updated_date"];
									$quiz_end_time		= (((int) $quiz_record["quiz_timeout"]) ? ($quiz_start_time + ($quiz_record["quiz_timeout"] * 60)) : 0);

									/**
									 * Checking for quizzes that are expired, but still in progress.
									 */
									if (($entry["progress_value"] == "inprogress") && ((((int) $quiz_record["release_until"]) && ($quiz_record["release_until"] < time())) || (($quiz_end_time) && (time() > ($quiz_end_time + 30))))) {
										$quiz_progress_array	= array (
											"progress_value" => "expired",
											"quiz_score" => "0",
											"quiz_value" => "0",
											"updated_date" => time(),
											"updated_by" => $_SESSION["details"]["id"]
										);
										if (!$db->AutoExecute("quiz_progress", $quiz_progress_array, "UPDATE", "qprogress_id = ".$db->qstr($entry["qprogress_id"]))) {
											application_log("error", "Unable to update the qprogress_id [".$qprogress_id."] to expired. Database said: ".$db->ErrorMsg());
										}
										$entry["progress_value"] = "expired";
									}

									switch ($entry["progress_value"]) {
										case "complete" :
											if (($quiz_record["quiztype_code"] != "delayed") || ($quiz_record["release_until"] <= time())) {
												$percentage = ((round(($entry["quiz_score"] / $entry["quiz_value"]), 2)) * 100);
												echo "<li class=\"".(($percentage >= 60) ? "correct" : "incorrect")."\">";
												echo	date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Score:</strong> ".$entry["quiz_score"]."/".$entry["quiz_value"]." (".$percentage."%)";
												echo "	( <a href=\"".ENTRADA_RELATIVE."/quizzes?section=results&amp;id=".$entry["qprogress_id"]."\">review quiz</a> )";
												echo "</li>";
											} else {
												echo "<li>".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Score:</strong> To Be Released ".date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"])."</li>";
											}
										break;
										case "expired" :
											echo "<li class=\"incorrect\">".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Expired Attempt</strong>: not completed.</li>";
										break;
										case "inprogress" :
											echo "<li>".date(DEFAULT_DATE_FORMAT, $entry["updated_date"])." <strong>Attempt In Progress</strong> ( <a href=\"".ENTRADA_RELATIVE."/quizzes?section=attempt&amp;id=".$quiz_record["aquiz_id"]."\">continue quiz</a> )</li>";
										break;
										default :
											continue;
										break;
									}
								}
								echo "</ul>";
							}

							echo "		</td>\n";
							echo "		<td class=\"date\" style=\"vertical-align: top\">".(((int) $quiz_record["release_until"]) ? date(DEFAULT_DATE_FORMAT, $quiz_record["release_until"]) : "No Expiration")."</td>\n";
							echo "	</tr>\n";
						}
					} else {
						echo "		<tr>\n";
						echo "			<td colspan=\"3\">\n";
						echo "				<div class=\"content-small\" style=\"margin-top: 3px; margin-bottom: 5px\">There are no online quizzes currently attached to this learning event.</div>\n";
						echo "			</td>\n";
						echo "		</tr>\n";
					}
					echo "		</tbody>\n";
					echo "		</table>\n";
					echo "	</div>\n";
					echo "</div>\n";

					echo "<a name=\"event-comments-section\"></a>\n";
					echo "<h2 title=\"Event Comments Section\">Discussions &amp; Comments</h2>\n";
					echo "<div id=\"event-comments-section\" class=\"section-holder\">\n";

					$editable	= false;
					$edit_ajax	= array();
					if ($event_discussions) {
						$i = 0;
						foreach ($event_discussions as $result) {
							if ($result["proxy_id"] == $_SESSION["details"]["id"]) {
								$editable		= true;
								$edit_ajax[]	= $result["ediscussion_id"];
							} else {
								$editable		= false;
							}

							$poster_name = get_account_data("firstlast", $result["proxy_id"]);

							echo "<div id=\"event_comment_".$result["ediscussion_id"]."\" class=\"discussion\"".(($i % 2) ? " style=\"background-color: #F3F3F3\"" : "").">\n";
							echo "	<span class=\"discussion-title\">".html_encode($result["discussion_title"])."</span>".(($editable) ? " ( <span id=\"edit_mode_".$result["ediscussion_id"]."\" style=\"cursor: pointer\">edit</span> )" : "")."<br />\n";
							echo "	<div class=\"content-small\"><strong>".get_account_data("firstlast", $result["proxy_id"])."</strong>, ".date(DEFAULT_DATE_FORMAT, $result["updated_date"])."</div>\n";
							echo "	<div class=\"discussion-comment\" id=\"discussion_comment_".$result["ediscussion_id"]."\">".nl2br(html_encode($result["discussion_comment"]))."</div>\n";
							echo "</div>\n";

							$i++;
						}

						if ((@is_array($edit_ajax)) && (@count($edit_ajax))) {
							echo "<script type=\"text/javascript\">\n";
							foreach ($edit_ajax as $discussion_id) {
								echo "var editor_".$discussion_id." = new Ajax.InPlaceEditor('discussion_comment_".$discussion_id."', '".ENTRADA_RELATIVE."/api/discussions.api.php', { rows: 7, cols: 75, okText: \"Save Changes\", cancelText: \"Cancel Changes\", externalControl: \"edit_mode_".$discussion_id."\", callback: function(form, value) { return 'action=edit&sid=".session_id()."&id=".$discussion_id."&discussion_comment='+escape(value) } });\n";
							}
							echo "</script>\n";
						}
					} else {
						echo "<div class=\"content-small\">There are no comments or discussions on this event. <strong>Start a conversation</strong>, leave your comment below.</div>\n";
					}
					echo "	<br /><br />";
					echo "	<form action=\"".ENTRADA_RELATIVE."/discussions?action=add".(($USE_QUERY) ? "&amp;".((isset($_GET["drid"])) ? "drid" : "rid")."=".$RESULT_ID : "")."\" method=\"post\">\n";
					echo "		<input type=\"hidden\" name=\"event_id\" value=\"".$EVENT_ID."\" />\n";
					echo "		<label for=\"discussion_comment\" class=\"content-subheading\">Leave a Comment</label>\n";
					echo "		<div class=\"content-small\">Posting comment as <strong>".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]."</strong></div>\n";
					echo "		<textarea id=\"discussion_comment\" name=\"discussion_comment\" cols=\"85\" rows=\"10\" style=\"width: 100%; height: 135px\"></textarea>\n";
					echo "		<div style=\"text-align: right; padding-top: 8px\"><input type=\"submit\" class=\"button\" value=\"Submit\" /></div>\n";
					echo "	</form>\n";
					echo "</div>\n";

					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					if ($include_details) {
						$sidebar_html .= "	<li class=\"link\"><a href=\"#event-details-section\" onclick=\"$('event-details-section').scrollTo(); return false;\" title=\"Event Details\">Event Details</a></li>\n";
					}

					if ($include_objectives) {
						$sidebar_html .= "	<li class=\"link\"><a href=\"#event-objectives-section\" onclick=\"$('event-objectives-section').scrollTo(); return false;\" title=\"Event Objectives\">Event Objectives</a></li>\n";
					}

					if ($include_resources) {
						$sidebar_html .= "	<li class=\"link\"><a href=\"#event-resources-section\" onclick=\"$('event-resources-section').scrollTo(); return false;\" title=\"Event Resources\">Event Resources</a></li>\n";
					}

					if ($include_comments) {
						$sidebar_html .= "	<li class=\"link\"><a href=\"#event-comments-section\" onclick=\"$('event-comments-section').scrollTo(); return false;\" title=\"Event Discussions &amp; Comments\">Event Comments</a></li>\n";
					}
					$sidebar_html .= "</ul>\n";

					new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open", "1.9");

				} else {
					$ERROR++;
					$ERRORSTR[] = "You are not permitted to access this event. This error has been logged.";

					echo display_error($errorstr);
					application_log("error", "User [".$_SESSION['details']['username']."] tried to access the event [".$EVENT_ID."] and was denied access.");
				}
			}
		}
	} else {
		$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/javascript/calendar/css/xc2_default.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/config/xc2_default.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_inpage.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_timestamp.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/elementresizer.js\"></script>\n";

		/**
		 * Process any filter requests.
		 */
		events_process_filters($ACTION);

		/**
		 * Check if preferences need to be updated.
		 */
		preferences_update($MODULE, $PREFERENCES);

		/**
		 * Fetch all of the events that apply to the current filter set.
		 */
		$learning_events = events_fetch_filtered_events(
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"],
				$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"],
				$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"],
				$ENTRADA_USER->getActiveOrganisation(),
				$_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"],
				$_SESSION[APPLICATION_IDENTIFIER]["events"]["so"],
				$_SESSION[APPLICATION_IDENTIFIER]["events"]["dtype"],
				$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"],
				0,
				$_SESSION[APPLICATION_IDENTIFIER]["events"]["filters"],
				true,
				(isset($_GET["pv"]) ? (int) trim($_GET["pv"]) : 1),
				$_SESSION[APPLICATION_IDENTIFIER]["events"]["pp"]);

		echo "<h1>View Events</h1>";

		/**
		 * Output the filter HTML.
		 */
		events_output_filter_controls();
		
		/**
		 * Output the calendar controls and pagination.
		 */
		events_output_calendar_controls();

		if (!empty($learning_events["events"])) {
			?>
			<div class="tableListTop">
				<img src="<?php echo ENTRADA_RELATIVE; ?>/images/lecture-info.gif" width="15" height="15" alt="" title="" style="vertical-align: middle" />
				<?php
				switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
					case "day" :
						echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place on <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					case "month" :
						echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					case "year" :
						echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					default :
					case "week" :
						echo "Found ".$learning_events["total_rows"]." event".(($learning_events["total_rows"] != 1) ? "s" : "")." from <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong> to <strong>".date("D, M jS, Y", $learning_events["duration_end"])."</strong>.\n";
					break;
				}
				?>
			</div>
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Learning Events">
				<colgroup>
					<col class="modified" />
					<col class="date" />
					<col class="course-code" />
					<col class="title" />
					<col class="attachment" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("date", "Date &amp; Time"); ?></td>
						<td class="course-code<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "course") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("course", "Course"); ?></td>
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("title", "Event Title"); ?></td>
						<td class="attachment">&nbsp;</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$rid = $learning_events["rid"];
					$count_modified = 0;
					$count_cohort = 0;
					$count_group = 0;
					$count_individual = 0;

					foreach ($learning_events["events"] as $result) {
						if (((!$result["release_date"]) || ($result["release_date"] <= time())) && ((!$result["release_until"]) || ($result["release_until"] >= time()))) {
							$attachments = attachment_check($result["event_id"]);
							$url = ENTRADA_RELATIVE."/events?rid=".$rid;
							$is_modified = false;

							/**
							 * Determine if this event has been modified since their last visit.
							 */
							if (isset($result["last_visited"]) && ((int) $result["last_visited"]) && ((int) $result["last_visited"] < (int) $result["updated_date"])) {
								$is_modified = true;
								$count_modified++;
							}

							/**
							 * Increment the appropriate audience_type counter.
							 */
							switch ($result["audience_type"]) {
								case "cohort" :
									$count_cohort++;
								break;
								case "group_id" :
									$count_group++;
								break;
								case "proxy_id" :
									$count_individual++;
								break;
								default :
									continue;
								break;
							}

							echo "<tr id=\"event-".$result["event_id"]."\" class=\"event".(($is_modified) ? " modified" : (($result["audience_type"] == "proxy_id") ? " individual" : ""))."\">\n";
							echo "	<td class=\"modified\">";
							if ($is_modified) {
								echo "<img src=\"".ENTRADA_RELATIVE."/images/event-modified.gif\" width=\"16\" height=\"16\" alt=\"This event has been modified since your last visit on ".date(DEFAULT_DATE_FORMAT, $result["last_visited"]).".\" title=\"This event has been modified since your last visit on ".date(DEFAULT_DATE_FORMAT, $result["last_visited"]).".\" style=\"vertical-align: middle\" />";
							} elseif ($result["audience_type"] == "proxy_id") {
								echo "<img src=\"".ENTRADA_RELATIVE."/images/event-individual.gif\" width=\"16\" height=\"16\" alt=\"Individual Event\" title=\"Individual Event\" style=\"vertical-align: middle\" />";
							} else {
								echo "<img src=\"".ENTRADA_RELATIVE."/images/pixel.gif\" width=\"16\" height=\"16\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
							}
							echo "	</td>\n";
							echo "	<td class=\"date\"><a href=\"".$url."\" title=\"Event Date\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</a></td>\n";
							echo "	<td class=\"course-code\"><a href=\"".$url."\" title=\"Course: ".html_encode($result["course_name"])."\">".html_encode($result["course_code"])."</a></td>\n";
							echo "	<td class=\"title\"><a href=\"".$url."\" title=\"Event Title: ".html_encode($result["event_title"])."\">".html_encode($result["event_title"])."</a></td>\n";
							echo "	<td class=\"attachment\">".(($attachments) ? "<img src=\"".ENTRADA_RELATIVE."/images/attachment.gif\" width=\"16\" height=\"16\" alt=\"Contains ".$attachments." attachment".(($attachments != 1) ? "s" : "")."\" title=\"Contains ".$attachments." attachment".(($attachments != 1) ? "s" : "")."\" />" : "<img src=\"".ENTRADA_RELATIVE."/images/pixel.gif\" width=\"16\" height=\"16\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />")."</td>\n";
							echo "</tr>\n";
						}

						$rid++;
					}
					?>
				</tbody>
			</table>
			<?php
			if ($count_modified) {
				if ($count_modified != 1) {
					$sidebar_html = "There are ".$count_modified." teaching events on this page which were updated since you last looked at them.";
				} else {
					$sidebar_html = "There is ".$count_modified." teaching event on this page which has been updated since you last looked at it.";
				}
				$sidebar_html .= " Eg. <img src=\"".ENTRADA_RELATIVE."/images/highlighted-example.gif\" width=\"67\" height=\"14\" alt=\"Updated events are denoted like.\" title=\"Updated events are denoted like.\" style=\"vertical-align: middle\" />";

				new_sidebar_item("Recently Modified", $sidebar_html, "modified-event", "open");
			}
		} else {
			$filters_applied = (((isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"])) && ($filters_total = @count($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"]))) ? true : false);
			?>
			<div class="display-notice">
				<h3>No Matching Events</h3>
				There are no learning events scheduled
				<?php
				switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
					case "day" :
						echo "that take place on <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong>";
					break;
					case "month" :
						echo "that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>";
					break;
					case "year" :
						echo "that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>";
					break;
					default :
					case "week" :
						echo "from <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_start"])."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, $learning_events["duration_end"])."</strong>";
					break;
				}
				echo (($filters_applied) ? " that also match the supplied &quot;Show Only&quot; restrictions" : "") ?>.
				<br /><br />
				If this is unexpected there are a few things that you can check:
				<ol>
					<li style="padding: 3px">Make sure that you are browsing the intended time period. For example, if you trying to browse <?php echo date("F", time()); ?> of <?php echo date("Y", time()); ?>, make sure that the results bar above says &quot;... takes place in <strong><?php echo date("F", time()); ?></strong> of <strong><?php echo date("Y", time()); ?></strong>&quot;.</li>
					<?php
					if ($filters_applied) {
						echo "<li style=\"padding: 3px\">You also have ".$filters_total." filter".(($filters_total != 1) ? "s" : "")." applied to the event list. you may wish to remove ".(($filters_total != 1) ? "one or more of these" : "it")." by clicking the link in the &quot;Showing Events That Include&quot; box above.</li>";
					}
					?>
				</ol>
			</div>
			<?php
		}

		echo "<form action=\"\" method=\"get\">\n";
		echo "	<input type=\"hidden\" id=\"dstamp\" name=\"dstamp\" value=\"".html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])."\" />\n";
		echo "</form>\n";

		/**
		 * Output the sidebar for sorting and legend.
		 */
		events_output_sidebar();

		$ONLOAD[] = "initList()";
	}
}