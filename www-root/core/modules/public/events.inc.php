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
} elseif (!$ENTRADA_ACL->amIAllowed("event", "read", false)) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
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
	if ((isset($_GET["rid"])) && ($tmp_input = clean_input($_GET["rid"], array("nows", "int")))) {
		$EVENT_ID = $tmp_input;
		$transverse = true;
		if (isset($_GET["community"]) && ((int)$_GET["community"])) {
			$community_id = ((int)$_GET["community"]);
		}
	} elseif ((isset($_GET["drid"])) && ($tmp_input = clean_input($_GET["drid"], array("nows", "int")))) {
		$EVENT_ID = $tmp_input;
		$transverse = true;
	} elseif ((isset($_GET["id"])) && ($tmp_input = clean_input($_GET["id"], array("nows", "int")))) {
		$EVENT_ID = $tmp_input;
		$transverse = false;
	}

    $event = Models_Event::get($EVENT_ID);

	/**
	 * Check for groups which have access to the administrative side of this module
	 * and add the appropriate toggle sidebar item.
	 */
	if ($ENTRADA_ACL->amIAllowed("eventcontent", "update", false)) {
		switch ($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]) {
			case "admin" :
				$admin_wording = "Administrator View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "edit", "id" => $EVENT_ID)) : "");
			break;
			case "pcoordinator" :
				$admin_wording = "Coordinator View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "edit", "id" => $EVENT_ID)) : "");
			break;
			case "director" :
				$admin_wording = "Director View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "content", "id" => $EVENT_ID)) : "");
			break;
			case "teacher" :
			case "faculty" :
			case "lecturer" :
				$admin_wording = "Teacher View";
				$admin_url = ENTRADA_RELATIVE."/admin/events".(($EVENT_ID) ? "?".replace_query(array("section" => "content", "id" => $EVENT_ID)) : "");
			break;
			default :
				$admin_wording = "";
				$admin_url = "";
			break;
		}

		$sidebar_html  = "<ul class=\"menu none\">\n";
		$sidebar_html .= "	<li><a href=\"".ENTRADA_RELATIVE."/events".(($EVENT_ID) ? "?".replace_query(array("id" => $EVENT_ID, "action" => false)) : "")."\"><img src=\"".ENTRADA_RELATIVE."/images/checkbox-on.gif\" alt=\"\" /> <span>Learner View</span></a></li>\n";
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
			$ORGANISATION_ID = $_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["organisation_id"];
			$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["events"]["organisation_id"] = $ORGANISATION_ID;
		}
	}

	$sidebar_html  = "<div style=\"text-align: center\">\n";
	$sidebar_html .= "	<a href=\"".ENTRADA_RELATIVE."/podcasts\"><img src=\"".ENTRADA_RELATIVE."/images/podcast-dashboard-image.jpg\" width=\"149\" height=\"99\" alt=\"MEdTech Podcasts\" title=\"Subscribe to our Podcast feed.\" border=\"0\"></a><br />\n";
	$sidebar_html .= "	<a href=\"".ENTRADA_RELATIVE."/podcasts\" style=\"color: #557CA3; font-size: 14px\">Podcasts Available</a>";
	$sidebar_html .= "</div>\n";
	new_sidebar_item("Podcasts in iTunes", $sidebar_html, "podcast-bar", "open", "2.1");

	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/events", "title" => "Learning Events");

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
						buttonClass:	'btn',
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
			add_error("The requested learning event does not exist in the system.");

			echo display_error();
		} else {
			$LASTUPDATED = $event_info["updated_date"];

			if (($event_info["release_date"]) && ($event_info["release_date"] > time())) {
				add_error("The event you are trying to view is not yet available. Please try again after ".date("r", $event_info["release_date"]));

				echo display_error();
			} elseif (($event_info["release_until"]) && ($event_info["release_until"] < time())) {
				add_error("The event you are trying to view is no longer available; it expired ".date("r", $event_info["release_until"]));

				echo display_error($errorstr);
			} else {
				if ($ENTRADA_ACL->amIAllowed(new EventResource($EVENT_ID, $event_info['course_id'], $event_info['organisation_id']), 'read')) {
					add_statistic($MODULE, "view", "event_id", $EVENT_ID);

					$event_contacts = events_fetch_event_contacts($EVENT_ID);
                    
                    $event_audience = $event->getEventAudience();

					$associated_cohorts = array("all");
					$associated_cohorts_string = "";
					$query = "SELECT * FROM `event_audience` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `audience_type` = 'cohort'";
					$cohorts = $db->GetAll($query);
					if ($cohorts) {
						foreach ($cohorts as $cohort) {
							$associated_cohorts[] = $cohort["audience_value"];
							$associated_cohorts_string .= ($associated_cohorts_string ? ", ".$db->qstr($cohort["audience_value"]) : $db->qstr($cohort["audience_value"]) );
						}
						$event_audience_type = "cohort";
					}

					$event_resources = events_fetch_event_resources($EVENT_ID, "all");
					$event_discussions = $event_resources["discussions"];
					$event_types = $event_resources["types"];

					// Meta information for this page.
					$PAGE_META["title"]			= $event_info["event_title"]." - ".APPLICATION_NAME;
					$PAGE_META["description"]	= trim(str_replace(array("\t", "\n", "\r"), " ", html_encode(strip_tags($event_info["event_goals"]))));
					$PAGE_META["keywords"]		= "";

					$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/events?".replace_query(array("id" => $event_info["event_id"])), "title" => $event_info["event_title"]);

					$include_details			= true;
					$include_audience			= true;
					$include_objectives			= false;
					$include_resources			= true;
					$include_comments			= true;

					$icon_discussion			= (((is_array($event_discussions)) && (count($event_discussions))) ? true : false);
					$icon_course_website		= true;

					$discussion_title			= (($icon_discussion) ? "Read the posted discussion comments." : "Start up a conversion, leave your comment!");
					$syllabus_title				= "Visit Course Website";

// @todo simpson This needs to be fixed.
					if (($_SESSION["details"]["allow_podcasting"]) && ($event_audience_type == "cohort") && (in_array($_SESSION["details"]["allow_podcasting"], $associated_cohorts))) {
						$sidebar_html = "To upload a podcast: <a href=\"#\" onclick=\"openDialog('".ENTRADA_URL."/api/file-wizard-podcast.api.php?id=".$EVENT_ID."')\">click here</a>";
						new_sidebar_item("Upload A Podcast", $sidebar_html, "podcast_uploading", "open", "2.0");

						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/elementresizer.js\"></script>";
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/wizard.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
						$HEAD[] = "<link href=\"".ENTRADA_URL."/css/wizard.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
						$HEAD[] = "<link href=\"".ENTRADA_RELATIVE."/javascript/calendar/css/xc2_default.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/config/xc2_default.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
						$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/calendar/script/xc2_inpage.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
						?>

						<iframe id="upload-frame" name="upload-frame" onload="frameLoad()" style="display: none;"></iframe>
						<a id="false-link" href="#placeholder"></a>
						<div id="placeholder" style="display: none"></div>
						<script type="text/javascript">
						var ajax_url = '';
						var modalDialog;
						document.observe('dom:loaded', function() {
							modalDialog = new Control.Modal($('false-link'), {
								position:		'center',
								overlayOpacity:	0.75,
								closeOnClick:	'overlay',
								className:		'modal',
								fade:			true,
								fadeDuration:	0.30,
								beforeOpen: function(request) {
									eval($('scripts-on-open').innerHTML);
								}
							});
						});

						function openDialog (url) {
							if (url && url != ajax_url) {
								ajax_url = url;
								new Ajax.Request(ajax_url, {
									method: 'get',
									onComplete: function(transport) {
										modalDialog.container.update(transport.responseText);
										modalDialog.open();
									}
								});
							} else {
								$('scripts-on-open').update();
								modalDialog.open();
							}
						}
						</script>
						<?php
					}

					if ($transverse) {
						$transversal_ids = events_fetch_transversal_ids($EVENT_ID, (isset($community_id) && $community_id ? $community_id : false));
					}
                    ?>
					<div class="no-printing">
                        <?php
                        if ($transverse && is_array($transversal_ids) && !empty($transversal_ids)) {
                            $back_click = "";
                            $next_click = "";
                            ?>
                            <div class="btn-toolbar clearfix">
                                <div class="btn-group span10">
                                    <?php
                                    if (isset($transversal_ids["prev"])) {
                                        $back_click = ENTRADA_RELATIVE . "/events?" . replace_query(array((isset($_GET["drid"]) ? "drid" : "rid") => $transversal_ids["prev"]));
    
                                        echo "<a class=\"btn\" id=\"back_event\" href=\"".$back_click."\" title=\"Previous Event\"><i class=\"icon-chevron-left\"></i></a>";
                                    } else {
                                        echo "<a class=\"btn disabled\" id=\"back_event\" href=\"#\" title=\"Previous Event\"><i class=\"icon-chevron-left\"></i></a>";
                                    }
                                    ?>
                                    <div id="swipe-location" class="event-navbar-middle"><?php echo html_encode($event_info["event_title"]); ?></div>
                                    <?php
                                    if (isset($transversal_ids["next"])) {
                                        $next_click = ENTRADA_RELATIVE . "/events?" . replace_query(array((isset($_GET["drid"]) ? "drid" : "rid") => $transversal_ids["next"]));
    
                                        echo "<a class=\"btn\" id=\"next_event\" href=\"".$next_click."\" title=\"Next Event\"><i class=\"icon-chevron-right\"></i></a>";
                                    } else {
                                        echo "<a class=\"btn disabled\" id=\"next_event\" href=\"#\" title=\"Next Event\"><i class=\"icon-chevron-right\"></i></a>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="pull-right">
                            <i class="fa fa-link"></i> <a href="<?php echo ENTRADA_RELATIVE; ?>/events?id=<?php echo $event_info["event_id"]; ?>" class="space-right"><small>Link to this page</small></a>
                            <i class="fa fa-print"></i> <a href="javascript: window.print()"><small>Print this page</small></a>
                        </div>
					</div>

                    <div class="clearfix"></div>
					<div class="content-small"><?php echo fetch_course_path($event_info["course_id"]); ?></div>
					<h1 id="page-top" class="event-title"><?php echo html_encode($event_info["event_title"]); ?></h1>

                    <script type="text/javascript">
                        var ajax_url = '';
                        var modalDialog;

                        function submitLTIForm() {
                            jQuery('#ltiSubmitForm').submit();
                        }

                        function openLTIDialog(url) {
                            var width  = jQuery(window).width() * 0.9,
                                height = jQuery(window).height() * 0.9;

                            if(width < 400) { width = 400; }
                            if(height < 400) { height = 400; }

                            modalDialog = new Control.Modal($('#false-link'), {
                                position:		'center',
                                overlayOpacity:	0.75,
                                closeOnClick:	'overlay',
                                className:		'modal',
                                fade:			true,
                                fadeDuration:	0.30,
                                width: width,
                                height: height,
                                afterOpen: function(request) {
                                    eval($('scripts-on-open').innerHTML);
                                },
                                beforeClose: function(request) {
                                    jQuery('#ltiContainer').remove();
                                }
                            });

                            new Ajax.Request(url, {
                                method: 'get',
                                parameters: 'width=' + width + '&height=' + height,
                                onComplete: function(transport) {
                                    modalDialog.container.update(transport.responseText);
                                    modalDialog.open();
                                }
                            });
                        }

                        function closeLTIDialog() {
                            modalDialog.close();
                        }
                    </script>

                    <div class="row-fluid">
                        <div class="span8">
                            <?php
                            if (clean_input($event_info["event_description"], array("allowedtags", "nows")) != "") {
                                echo "<div class=\"event-description\">";
                                echo trim(strip_selected_tags($event_info["event_description"], array("font")));
                                echo "</div>";
                            }

                            if (clean_input($event_info["event_message"], array("allowedtags", "nows")) != "") {
                                echo "<div class=\"event-message\">\n";
                                echo "	<h3>Required Preparation</h3>\n";
                                echo	trim(strip_selected_tags($event_info["event_message"], array("font")));
                                echo "</div>\n";
                            }
                            ?>
                        </div>

                        <div class="span4">
                            <table class="event-details">
                                <tbody>
                                    <tr>
                                        <th>Date &amp; Time</th>
                                        <td><?php echo date(DEFAULT_DATE_FORMAT, $event_info["event_start"]); ?></td>
                                    </tr>
                                    <tr class="spacer">
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <tr>
                                        <th>Location</th>
                                        <td><?php echo (($event_info["event_location"]) ? $event_info["event_location"] : "To Be Announced"); ?></td>
                                    </tr>
                                    <tr class="spacer">
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td>
                                            <?php
                                            echo (((int) $event_info["event_duration"]) ? $event_info["event_duration"]." Minutes" : "To Be Announced");

                                            if ($event_types) {
                                                echo "<br /><br />";
                                                echo "<div class=\"content-small\">\n";
                                                echo "<strong>Breakdown</strong><br />";
                                                foreach($event_types as $type) {
                                                    echo "".$type["duration"]." minutes of ".strtolower($type["eventtype_title"])."<br />";
                                                }
                                                echo "</div>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($event_contacts) {
                                        if (isset($event_contacts["teacher"]) && ($count = count($event_contacts["teacher"]))) {
                                            ?>
                                            <tr class="spacer">
                                                <td colspan="2"><hr></td>
                                            </tr>
                                            <tr>
                                                <th>Teacher<?php echo (($count != 1) ? "s" : ""); ?></th>
                                                <td>
                                                    <?php
                                                    foreach ($event_contacts["teacher"] as $contact) {
                                                        echo "<a href=\"".ENTRADA_RELATIVE."/people?id=".$contact["proxy_id"]."\">".html_encode($contact["fullname"])."</a><br />\n";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (isset($event_contacts["tutor"]) && ($count = count($event_contacts["tutor"]))) {
                                            ?>
                                            <tr class="spacer">
                                                <td colspan="2"><hr></td>
                                            </tr>
                                            <tr>
                                                <th>Tutor<?php echo (($count != 1) ? "s" : ""); ?></th>
                                                <td>
                                                    <?php
                                                    foreach ($event_contacts["tutor"] as $contact) {
                                                        echo "<a href=\"".ENTRADA_RELATIVE."/people?id=".$contact["proxy_id"]."\">".html_encode($contact["fullname"])."</a>\n";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (isset($event_contacts["ta"]) && ($count = count($event_contacts["ta"]))) {
                                            ?>
                                            <tr class="spacer">
                                                <td colspan="2"><hr></td>
                                            </tr>
                                            <tr>
                                                <th>TA<?php echo (($count != 1) ? "s" : ""); ?></th>
                                                <td>
                                                    <?php
                                                    foreach ($event_contacts["ta"] as $contact) {
                                                        echo "<a href=\"".ENTRADA_RELATIVE."/people?id=".$contact["proxy_id"]."\">".html_encode($contact["fullname"])."</a><br />\n";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (isset($event_contacts["auditor"]) && ($count = count($event_contacts["auditor"]))) {
                                            ?>
                                            <tr class="spacer">
                                                <td colspan="2"><hr></td>
                                            </tr>
                                            <tr>
                                                <th>Auditor<?php echo (($count != 1) ? "s" : ""); ?></th>
                                                <td>
                                                    <?php
                                                    foreach ($event_contacts["auditor"] as $contact) {
                                                        echo "<a href=\"".ENTRADA_RELATIVE."/people?id=".$contact["proxy_id"]."\">".html_encode($contact["fullname"])."</a><br />\n";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <tr class="spacer">
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <?php if (($ENTRADA_USER->getActiveGroup() == "student" && $event->getAudienceVisible()) || $ENTRADA_USER->getActiveGroup() != "student") { ?>
                                    <tr>
                                        <th>Audience</th>
                                        <td>
                                            <?php
                                            if ($event_audience) {
                                                foreach ($event_audience as $audience) {
                                                    $a = $audience->getAudience();

                                                    if ($a && method_exists($a, "getAudienceMembers") && is_array($a->getAudienceMembers())) {
                                                        $link = false;
                                                        switch ($audience->getAudienceType()) {
                                                            case "proxy_id" :
                                                                $css_class = "fa fa-user";
                                                            break;
                                                            case "course_id" :
                                                            case "group_id" :
                                                            case "cohort" :
                                                                if ($ENTRADA_USER->getActiveGroup() == "student") {
                                                                    if (in_array($ENTRADA_USER->getActiveID(), array_keys($a->getAudienceMembers()))) {
                                                                        $link = true;
                                                                    }
                                                                } else {
                                                                    if (count($a->getAudienceMembers()) > 0) {
                                                                        $link = true;
                                                                    }
                                                                }
                                                                $css_class = "fa fa-users";
                                                            break;
                                                            default:
                                                                $css_class = "fa fa-users";
                                                            break;
                                                        }

                                                        if ($a) {
                                                            if ($link) {
                                                                ?>
                                                                <a href="#audience-<?php echo $audience->getEventAudienceID(); ?>" data-toggle="modal"><?php echo $a->getAudienceName(); ?></a>
                                                                <?php
                                                            } else {
                                                                echo $a->getAudienceName();
                                                            }

                                                            if ($a && $link && count($a->getAudienceMembers() > 0)) {
                                                                ?>
                                                                <div id="audience-<?php echo $audience->getEventAudienceID(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                        <h3 id="myModalLabel"><?php echo $a->getAudienceName(); ?> Group Members</h3>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row-fluid">
                                                                        <?php
                                                                            $count = round(count($a->getAudienceMembers()) / 3);
                                                                            $i = 0;

                                                                            echo "<div class=\"span4\">\n";
                                                                            foreach ($a->getAudienceMembers() as $member) {
                                                                                if (($i == $count || $i == $count * 2) && $count != 0) {
                                                                                    echo "</div><div class=\"span4\">\n";
                                                                                }
                                                                                echo $member["firstname"] . " " . $member["lastname"]."<br />\n";
                                                                                $i++;
                                                                            }
                                                                            echo "</div>\n"
                                                                        ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                    } elseif (method_exists($a, "getAudienceName")) {
                                                        echo $a->getAudienceName();
                                                    } else {
                                                        echo "Not Available";
                                                    }

                                                    echo "<br />";
                                                }
                                            } else {
                                                echo "Not Available";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class="spacer">
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <?php } ?>
                                    <?php
                                    /**
                                     * @todo simpson This needs to be fixed as $event_audience_type is no longer for grad_year.
                                     */
                                    if (isset($event_audience_type) && $event_audience_type == "cohort") {
                                        $query = "	SELECT a.`event_id`, a.`event_title`, b.`audience_value` AS `event_cohort`
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
                                                    AND b.`audience_value` IN (".$associated_cohorts_string.")
                                                    ORDER BY `event_title` ASC";
                                        $results = $db->GetAll($query);
                                        if ($results) {
                                            echo "	<tr>\n";
                                            echo "		<td colspan=\"2\">&nbsp;</td>\n";
                                            echo "	</tr>\n";
                                            echo "	<tr>\n";
                                            echo "		<th>Overlapping Event".((count($results) != 1) ? "s" : "")."</th>\n";
                                            echo "		<td>\n";
                                            echo "          <ul class=\"menu\">\n";
                                            foreach ($results as $result) {
                                                echo "          <li class=\"link\"><a href=\"".ENTRADA_RELATIVE."/events?id=".$result["event_id"]."\">".html_encode($result["event_title"])."</a></li>\n";
                                            }
                                            echo "          </ul>\n";
                                            echo "		</td>\n";
                                            echo "	</tr>\n";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <?php
                        $query = "  SELECT ek.`keyword_id`, d.`descriptor_name` 
                                    FROM `event_keywords` AS ek
                                    JOIN `mesh_descriptors` AS d 
                                    ON ek.`keyword_id` = d.`descriptor_ui`
                                    AND ek.`event_id` = " . $db->qstr($EVENT_ID) . "
                                    ORDER BY `descriptor_name`";

                        $results = $db->GetAll($query);
                        if ($results && (!$event_info['keywords_hidden'] || $ENTRADA_USER->getActiveGroup() != "student") && isset($event_info['keywords_release_date']) && time() >= $event_info['keywords_release_date']) {
                            $include_keywords = true;
                            ?>
                            <a name="event-keywords-section"></a>
                            <h2 title="Event Keywords Section">Event Keywords</h2>
                            <div id="event-keywords-section">
                                <ul>
                                    <?php
                                    foreach($results as $result) {
                                        echo "<li data-dui=\"" . $result['keyword_id'] . "\" data-dname=\"" . $result['descriptor_name'] . "\" id=\"tagged_keyword\">" . $result['descriptor_name'] . "</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div>
                        <?php
                        $query = "SELECT b.`objective_id`, b.`objective_name`
                                    FROM `event_objectives` AS a
                                    LEFT JOIN `global_lu_objectives` AS b
                                    ON b.`objective_id` = a.`objective_id`
                                    JOIN `objective_organisation` AS c
                                    ON b.`objective_id` = c.`objective_id`
                                    AND c.`organisation_id` = ".$db->qstr($event->getOrganisationID())."
                                    WHERE a.`objective_type` = 'event'
                                    AND b.`objective_active` = '1'
                                    AND a.`event_id` = ".$db->qstr($EVENT_ID)."
                                    ORDER BY b.`objective_name` ASC;";
                        $clinical_presentations	= $db->GetAll($query);
                        $show_event_objectives	= ((clean_input($event_info["event_objectives"], array("notags", "nows")) != "") ? true : false);
                        $show_clinical_presentations = (($clinical_presentations) ? true : false);

                        $show_curriculum_objectives = false;
                        list($curriculum_objectives,$top_level_id) = courses_fetch_objectives($event->getOrganisationID(),array($event_info["course_id"]),-1, 1, false, false, $EVENT_ID, true);

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
						if (isset($event_info["objectives_release_date"]) && time() >= $event_info["objectives_release_date"]) {
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

								if ($show_clinical_presentations) { ?>
									<div class="section-holder mapped">
                                        <h2 title="Clinical Presentation List" class="list-heading">Clinical Presentations</h2>
                                        <ul class="objective-list mapped-list">
                                        <?php
                                        foreach ($clinical_presentations as $key => $result) {
                                            $set = Models_Objective::fetchObjectiveSet($result["objective_id"]);
                                            ?>
                                            <li>
                                                <strong><?php echo $result["objective_name"]; ?></strong>
                                                <div class="objective-description">From the Objective Set: <strong><?php echo $set->getName(); ?></strong></div>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                        </ul>
									</div>
                                    <?php
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
									echo	course_objectives_in_list($curriculum_objectives, $top_level_id,$top_level_id, false, false, 1, true)."\n";
									echo "</div>\n";
								}
							}

							$COURSE_ID = $event_info["course_id"];
                            $query = "	SELECT a.*, COALESCE(b.`objective_details`,a.`objective_description`) AS `objective_description`, COALESCE(b.`objective_type`,c.`objective_type`) AS `objective_type`,
                                    b.`importance`,c.`objective_details`, COALESCE(c.`eobjective_id`,0) AS `mapped`,
                                    COALESCE(b.`cobjective_id`,0) AS `mapped_to_course`
                                    FROM `global_lu_objectives` a
                                    LEFT JOIN `course_objectives` b
                                    ON a.`objective_id` = b.`objective_id`
                                    AND b.`course_id` = ".$db->qstr($COURSE_ID)."
                                    AND b.`active` = '1'
                                    LEFT JOIN `event_objectives` c
                                    ON c.`objective_id` = a.`objective_id`
                                    AND c.`event_id` = ".$db->qstr($EVENT_ID)."
                                    WHERE a.`objective_active` = '1'
                                    AND (c.`event_id` = ".$db->qstr($EVENT_ID)." OR b.`course_id` = ".$db->qstr($COURSE_ID).")
                                    GROUP BY a.`objective_id`
                                    ORDER BY a.`objective_id` ASC";
                            $mapped_objectives = $db->GetAll($query);

                            $explicit_event_objectives = false;
                            if ($mapped_objectives) {
                                foreach ($mapped_objectives as $objective) {
                                    //if its mapped to the event, but not the course, then it belongs in the event objective list
                                    if ($objective["mapped"] && !$objective["mapped_to_course"]) {
                                        $objective_name = $translate->_("events_filter_controls");
                                        $clinical_presentations_name = $objective_name["cp"]["global_lu_objectives_name"];
                                        
                                        $objective_set = Models_Objective::fetchObjectiveSet($objective["objective_id"]);
                                        
                                        if ($objective_set->getName() != $clinical_presentations_name) {
                                            if (!event_objective_parent_mapped_course($objective["objective_id"], $EVENT_ID, true)) {
                                                $explicit_event_objectives[] = $objective;
                                            }
                                        } else {
                                            if (Models_Objective::fetchExplicitEventObjective($objective["objective_id"], $EVENT_ID)) {
                                                $explicit_event_objectives[] = $objective;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="section-holder">
                                <div id="mapped_objectives" class="mapped">
                                    <div id="event-list-wrapper" <?php echo ($explicit_event_objectives)?'':' style="display:none;"';?>>
                                        <a name="event-objective-list"></a>
                                        <h2 id="event-toggle"  title="Event Objective List" class="list-heading">Event Specific Objectives</h2>
                                        <div id="event-objective-list">
                                            <ul class="objective-list mapped-list" id="mapped_event_objectives" data-importance="event">
                                                <?php
                                                if ($explicit_event_objectives) {
                                                    foreach ($explicit_event_objectives as $objective) {
                                                        $title = ($objective["objective_code"] ? $objective["objective_code"] . ': ' . $objective["objective_name"] : $objective["objective_name"]);
                                                        ?>
                                                        <li class = "mapped-objective"
                                                            id = "mapped_objective_<?php echo $objective["objective_id"]; ?>"
                                                            data-id = "<?php echo $objective["objective_id"]; ?>"
                                                            data-title="<?php echo $title;?>"
                                                            data-description="<?php echo htmlentities($objective["objective_description"]);?>"
                                                            data-mapped="<?php echo $objective["mapped_to_course"]?1:0;?>">
                                                            <strong><?php echo $title; ?></strong>
                                                            <div class="objective-description">
                                                                <?php
                                                                $set = fetch_objective_set_for_objective_id($objective["objective_id"]);
                                                                if ($set) {
                                                                    echo "From the Objective Set: <strong>".$set["objective_name"]."</strong><br/>";
                                                                }

                                                                echo $objective["objective_description"];
                                                                ?>
                                                            </div>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $query = "SELECT a.`topic_id`,a.`topic_name`, e.`topic_coverage`, e.`topic_time`
                                    FROM `events_lu_topics` AS a
                                    LEFT JOIN `topic_organisation` AS b
                                    ON a.`topic_id` = b.`topic_id`
                                    LEFT JOIN `courses` AS c
                                    ON b.`organisation_id` = c.`organisation_id`
                                    LEFT JOIN `events` AS d
                                    ON c.`course_id` = d.`course_id`
                                    JOIN `event_topics` AS e
                                    ON d.`event_id` = e.`event_id`
                                    AND a.`topic_id` = e.`topic_id`
                                    WHERE d.`event_id` = ".$db->qstr($EVENT_ID);
                            $topic_results = $db->GetAll($query);
                            if ($topic_results) {
                                ?>
                                <table style="width: 100%" cellspacing="0">
                                    <colgroup>
                                        <col style="width: 80%" />
                                        <col style="width: 10%" />
                                        <col style="width: 10%" />
                                    </colgroup>
                                    <tr>
                                        <td colspan="3">
                                            <h2>Event Topics</h2>
                                            <div class="content-small" style="padding-bottom: 10px">These topics will be covered in this learning event.</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span style="font-weight: bold; color: #003366;">Hot Topic</span></td>
                                        <td><span style="font-weight: bold; color: #003366;">Major</span></td>
                                        <td><span style="font-weight: bold; color: #003366;">Minor</span></td>
                                    </tr>
                                    <?php
                                    foreach ($topic_results as $topic_result) {
                                        echo "<tr>\n";
                                        echo "	<td>".html_encode($topic_result["topic_name"])."</td>\n";
                                        echo "	<td>".(($topic_result["topic_coverage"] == "major") ? "<img src=\"".ENTRADA_URL."/images/question-correct.gif"."\" />" : "" )."</td>\n";
                                        echo "	<td>".(($topic_result["topic_coverage"] == "minor") ? "<img src=\"".ENTRADA_URL."/images/question-correct.gif"."\" />": "" )."</td>\n";
                                        echo "</tr>\n";
                                    }
                                    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>";
                                    ?>
                                </table>
                                <?php
                            }
                        }
                        ?>
					</div>
                    <?php 
                    $event_resource_entites = Models_Event_Resource_Entity::fetchAllByEventID($EVENT_ID);
                    if ($event_resource_entites) {
                        echo "<a name=\"event-resources-section\"></a>";
                        echo "<h2 title=\"Event Resources Section\">Event Resources</h2>";

                        echo "<div id=\"event-resources-section\">";

                            $resource = false;
                            $entity_timeframe_pre = array();
                            $entity_timeframe_during = array();
                            $entity_timeframe_post = array();
                            $entity_timeframe_none = array();
                            foreach ($event_resource_entites as $entity) {
                                $resource = $entity->getResource()->toArray();
                                if ($resource) {
                                    switch ($entity->getEntityType()) {
                                        case 1 :
                                        case 5 :
                                        case 6 :
                                        case 11 :

                                            if ($entity->getEntityType() == 1) {
                                                $resource["type"] = "Audio / Video";
                                            } else if ($entity->getEntityType() == 5) {
                                                $resource["type"] = "Lecture Notes";
                                            } else if ($entity->getEntityType() == 6) {
                                                $resource["type"] = "Lecture Slides";
                                            } else if ($entity->getEntityType() == 11) {
                                                $resource["type"] = "Other";
                                            }

                                            $resource_statistic = $entity->getResource()->getViewed();
                                            $resource["title"] = "";
                                            $resource["description"] = "";

                                            $title = ($resource["file_title"] != "" ? $resource["file_title"] : $resource["file_name"]);
                                            if ((((!(int) $resource["release_date"]) || ($resource["release_date"] <= time())) && ((!(int) $resource["release_until"]) || ($resource["release_until"] >= time())))) {
                                                $resource["title"] = "<a class=\"resource-link\" href=\"".ENTRADA_RELATIVE."/file-event.php?id=".html_encode($resource["efile_id"])."\" title=\"Click to download ".html_encode($title)."\"".(((int) $resource["access_method"]) ? " target=\"_blank\"" : "").">".html_encode($title)."</a>";
                                            } else {
                                                $resource["title"] = "<p class=\"resource-title\">". html_encode($title) ."</p>";
                                            }

                                            if ($resource_statistic) {
                                                $resource["title"] .= (((int) $resource_statistic->getTimestamp()) ? (((int) $resource_statistic->getTimestamp() >= (int) $resource["updated_date"]) ? "<span class=\"resource-viewed pull-right\" title=\"You have already downloaded the latest version of this file.\"><i class=\"icon-ok icon-white\"></i></span>" : "<span class=\"resource-updated pull-right\" title=\"This file has been updated since you have last downloaded it.\"><i class=\"icon-exclamation-sign icon-white\"></i></span>") : "");
                                            }

                                            if (((int) $resource["release_date"]) && ($resource["release_date"] > time())) {
                                                $resource["description"] .=  "<p class=\"muted resource-description\">This file will be available for downloading <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_date"])."</strong>.</p>";
                                            } elseif (((int) $resource["release_until"]) && ($resource["release_until"] < time())) {
                                                $resource["description"] .= "<p class=\"muted resource-description\">This file was only available for download until <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_until"])."</strong>. Please contact the primary teacher for assistance if required.</p>";
                                            }

                                            $resource["description"] .= "<p class=\"muted resource-description\">" . html_encode($resource["file_notes"]) . "</p>";
                                            $resource["type"] =  html_encode($resource["type"] . " " . readable_size($resource["file_size"]));
                                            $resource["resource_id"] = $entity->getEntityValue();
                                            $resource["type_id"] = $entity->getEntityType();
                                            $resource["viewed"] = $entity->getResource()->getViewed();
                                        break;
                                        case 2 :
                                            $resource["title"] = "<p class=\"resource-title\">Class Work</p>";
                                            $resource["description"] = "<p class=\"muted resource-description\">" . html_encode($resource["resource_class_work"]) . "</p>";
                                            $resource["type"] = "Class Work";
                                            $resource["type_id"] = $entity->getEntityType();
                                        break;
                                        case 3 :
                                            $resource["title"] = "";
                                            $resource["description"] = "";
                                            $resource_statistic = $entity->getResource()->getViewed();

                                            if ((((!(int) $resource["release_date"]) || ($resource["release_date"] <= time())) && ((!(int) $resource["release_until"]) || ($resource["release_until"] >= time())))) {
                                                $resource["title"] = "<a class=\"resource-link\" href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($resource["elink_id"])."\" title=\"Click to visit ".html_encode($resource["link"])."\"  target=\"_blank\">".(($resource["link_title"] != "") ? html_encode($resource["link_title"]) : html_encode($resource["link"]))."</a>\n";
                                            } else {
                                                $resource["title"] = "<p class=\"resource-title\">" . ($resource["link_title"] != "" ? html_encode($resource["link_title"]) : html_encode($resource["link"])) . "</p>";
                                            }

                                            if ($resource_statistic) {
                                                $resource["title"] .= (((int) $resource_statistic->getTimestamp()) ? (((int) $resource_statistic->getTimestamp() >= (int) $resource["updated_date"]) ? "<span class=\"resource-viewed pull-right\" title=\"You have previously visited this link..\"><i class=\"icon-ok icon-white\"></i></span>" : "<span class=\"resource-updated pull-right\" title=\"An update to this link has been made, please re-visit it.\"><i class=\"icon-exclamation-sign icon-white\"></i></span>") : "");
                                            }

                                            if (((int) $resource["release_date"]) && ($resource["release_date"] > time())) {
                                                $resource["description"] .=  "<p class=\"muted resource-description\">This link will become accessible <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_date"])."</strong>.</p>";
                                            } elseif (((int) $resource["release_until"]) && ($resource["release_until"] < time())) {
                                                $resource["description"] .= "<p class=\"muted resource-description\">This link was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_until"])."</strong>. Please contact the primary teacher for assistance if required.</p>";
                                            }

                                            $resource["description"] .= "<p class=\"muted resource-description\">" . html_encode($resource["link_notes"]) . "</p>";
                                            $resource["type"] = "Link";
                                            $resource["type_id"] = $entity->getEntityType();
                                            $resource["viewed"] = $entity->getResource()->getViewed();
                                        break;
                                        case 4 :
                                            $resource["title"] = "<p class=\"resource-title\">Homework</p>";
                                            $resource["description"] = "<p class=\"muted resource-description\">" . html_encode($resource["resource_homework"]) . "</p>";
                                            $resource["type"] = "Homework";
                                            $resource["type_id"] = $entity->getEntityType();
                                        break;
                                        case 7 :
                                            $resource["description"] = "";
                                            $resource_statistic = $entity->getResource()->getViewed();

                                            if ((((!(int) $resource["release_date"]) || ($resource["release_date"] <= time())) && ((!(int) $resource["release_until"]) || ($resource["release_until"] >= time())))) {
                                                $resource["title"] = "<a class=\"resource-link\" href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($resource["elink_id"])."\" title=\"Click to visit ".html_encode($resource["link"])."\"  target=\"_blank\">".(($resource["link_title"] != "") ? html_encode($resource["link_title"]) : html_encode($resource["link"]))."</a>\n";
                                            } else {
                                                $resource["title"] = "<p class=\"resource-title\">". (($resource["link_title"] != "") ? html_encode($resource["link_title"]) : html_encode($resource["link"])) ."</p>";
                                            }

                                            if ($resource_statistic) {
                                                $resource["title"] .= (((int) $resource_statistic->getTimestamp()) ? (((int) $resource_statistic->getTimestamp() >= (int) $resource["updated_date"]) ? "<span class=\"resource-viewed pull-right\" title=\"You have previously visited this learning module\"><i class=\"icon-ok icon-white\"></i></span>" : "<span class=\"resource-updated pull-right\" title=\"An update to this learning module has been made, please re-visit it.\"><i class=\"icon-exclamation-sign icon-white\"></i></span>") : "");
                                            }

                                            if (((int) $resource["release_date"]) && ($resource["release_date"] > time())) {
                                                $resource["description"] .=  "<p class=\"muted resource-description\">This learning module will become accessible <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_date"])."</strong>.</p>";
                                            } elseif (((int) $resource["release_until"]) && ($resource["release_until"] < time())) {
                                                $resource["description"] .= "<p class=\"muted resource-description\">This learning module was only accessible until <strong>".date(DEFAULT_DATE_FORMAT, $resource["release_until"])."</strong>. Please contact the primary teacher for assistance if required.</p>";
                                            }

                                            $resource["description"] .= "<p class=\"muted resource-description\">" . html_encode($resource["link_notes"]) . "</p>";
                                            $resource["type"] = "Online Learning Module";
                                            $resource["type_id"] = $entity->getEntityType();
                                        break;
                                        case 8 :
                                            $quiz = $entity->getResource();
                                            $total_questions = quiz_count_questions($quiz->getQuizID());
                                            $resource_statistic = $entity->getResource()->getViewed();
                                            $resource["title"] = "";
                                            $resource["description"] = "";
                                            $resource["attempts_history"] = "";

                                            $attempts = 0;
                                            $quiz_progress_records = Models_Quiz_Progress::fetchAllByAquizIDProxyID($entity->getEntityValue(), $ENTRADA_USER->getActiveID());

                                            if ($quiz_progress_records) {
                                                $attempts = count($quiz_progress_records);
                                            }

                                            $exceeded_attempts    = ((((int) $quiz->getQuizAttempts() === 0) || ($attempts < $quiz->getQuizAttempts())) ? false : true);

                                            if (isset($quiz) && $quiz->getRequireAttendance() && !events_fetch_event_attendance_for_user($EVENT_ID,$ENTRADA_USER->getID())) {
                                                $allow_attempt = false;
                                            } elseif (((!(int) $quiz->getReleaseDate()) || ($quiz->getReleaseDate() <= time())) && ((!(int) $quiz->getReleaseUntil()) || ($quiz->getReleaseUntil() >= time())) && (!$exceeded_attempts)) {
                                                $allow_attempt = true;
                                            } else {
                                                $allow_attempt = false;
                                            }

                                            if ($allow_attempt) {
                                                $resource["title"] = "<a class=\"resource-link\" href=\"javascript: beginQuiz(".html_encode($resource["aquiz_id"]).")\" title=\"Take ".html_encode($resource["quiz_title"])."\">".html_encode($resource["quiz_title"])."</a>";
                                            } else {
                                                $resource["title"] = "<p class=\"resource-title\">". html_encode($resource["quiz_title"]) ."</p>";
                                            }

                                            if ($resource_statistic) {
                                                $resource["title"] .= (((int) $resource_statistic->getTimestamp()) ? (((int) $resource_statistic->getTimestamp() >= (int) $resource["updated_date"]) ? "<span class=\"resource-viewed pull-right\" title=\"You have previously completed this quiz.\"><i class=\"icon-ok icon-white\"></i></span>" : "<span class=\"resource-updated pull-right\" title=\"This attached quiz has been updated since you last completed it.\"><i class=\"icon-exclamation-sign icon-white\"></i></span>") : "");
                                            }

                                            if ((int) $quiz->getReleaseDate() && (int) $quiz->getReleaseUntil()) {
                                                $resource["description"] .= "<p class=\"muted resource-description\">This quiz " . ($quiz->getReleaseUntil() > time() ? "is" : "was only") .  " available from <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseDate()))."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseUntil()))."</strong>.</p>";
                                            } elseif ((int) $quiz->getReleaseDate()) {
                                                if ($quiz->getReleaseDate() > time()) {
                                                    $resource["description"] .= "<p class=\"muted resource-description\">You will be able to attempt this quiz starting <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseDate()))."</strong>.</p>";
                                                } else {
                                                    $resource["description"] .= "<p class=\"muted resource-description\">This quiz has been available since <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseDate()))."</strong>.</p>";
                                                }
                                            } elseif ((int) $quiz->getReleaseUntil()) {
                                                if ($quiz->getReleaseUntil() > time()) {
                                                    $resource["description"] .= "<p class=\"muted resource-description\">You will be able to attempt this quiz until <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseUntil()))."</strong>.</p>";
                                                } else {
                                                    $resource["description"] .= "<p class=\"muted resource-description\">This quiz was only available until <strong>".date(DEFAULT_DATE_FORMAT, html_encode($quiz->getReleaseUntil()))."</strong>. Please contact a teacher for assistance if required.</p>";
                                                }
                                            } else {
                                                $resource["description"] .= "<p class=\"muted resource-description\">This quiz is available indefinitely.</p>";
                                            }

                                            switch ($quiz->getQuiztypeID()) {
                                                case "1" :
                                                    $quiz_type_code = "delayed";
                                                break;
                                                case "2" :
                                                    $quiz_type_code = "immediate";
                                                break;
                                                case "3" :
                                                    $quiz_type_code = "hide";
                                                break;
                                            }

                                            $resource["description"] .= "<p class=\"muted resource-description\">" . quiz_generate_description($quiz->getRequired(), $quiz_type_code, $quiz->getQuizTimeout(), $total_questions, $quiz->getQuizAttempts(), $quiz->getTimeframe(), $quiz->getRequireAttendance(), $event_info["course_id"]) . "</p>";
                                            $resource["description"] .= "<p class=\"muted resource-description\">" . html_encode($resource["quiz_notes"]) . "</p>";

                                            if ($quiz_progress_records) {
                                                $resource["description"] .= "<br><p class=\"muted resource-description\"><strong>Your Attempts</strong></p>";
                                                $resource["description"] .= "<ul class=\"menu\">";
                                                foreach ($quiz_progress_records as $entry) {
                                                    $quiz_start_time	= $entry->getUpdatedDate();
                                                    $quiz_end_time		= (((int) $quiz->getQuizTimeout()) ? ($quiz_start_time + ($quiz->getQuizTimeout() * 60)) : 0);

                                                    /**
                                                     * Checking for quizzes that are expired, but still in progress.
                                                     */
                                                    if (($entry->getProgressValue() == "inprogress") && ((((int) $quiz->getReleaseUntil()) && ($quiz->getReleaseUntil() < time())) || (($quiz_end_time) && (time() > ($quiz_end_time + 30))))) {
                                                        $quiz_progress_array	= array (
                                                            "qprogress_id" => $entry->getQprogressID(),
                                                            "aquiz_id" => $entry->getAquizID(),
                                                            "content_type" => $entry->getContentType(),
                                                            "content_id" => $entry->getContentID(),
                                                            "quiz_id" => $entry->getQuizID(),
                                                            "proxy_id" => $entry->getProxyID(),
                                                            "progress_value" => "expired",
                                                            "quiz_score" => "0",
                                                            "quiz_value" => "0",
                                                            "updated_date" => time(),
                                                            "updated_by" => $ENTRADA_USER->getID()
                                                        );

                                                        $entry = new Models_Quiz_Progress($quiz_progress_array);

                                                        if ($entry) {
                                                            if (!$entry->update()) {
                                                                application_log("error", "Unable to update the qprogress_id [".$entry->getQprogressID()."] to expired. Database said: ".$db->ErrorMsg());
                                                            }
                                                        }

                                                        $entry->setProgressValue("expired");
                                                    }

                                                    switch ($entry->getProgressValue()) {
                                                        case "complete" :
                                                            if (($quiz_type_code == "delayed" && $quiz->getReleaseUntil() <= time()) || ($quiz_type_code == "immediate")) {
                                                                $percentage = ((round(($entry->getQuizScore() / $entry->getQuizValue()), 2)) * 100);
                                                                $resource["description"] .= "<li class=\"".(($percentage >= 60) ? "correct" : "incorrect")."\">";
                                                                $resource["description"] .=	date(DEFAULT_DATE_FORMAT, $entry->getUpdatedDate())." <strong>Score:</strong> ".$entry->getQuizScore()."/".$entry->getQuizValue()." (".$percentage."%)";
                                                                $resource["description"] .= "	( <a href=\"".ENTRADA_RELATIVE."/quizzes?section=results&amp;id=".html_encode($entry->getQprogressID())."\">review quiz</a> )";
                                                                $resource["description"] .= "</li>";
                                                            } elseif ($quiz_type_code == "hide") {
                                                                $resource["description"] .= "<li>".date(DEFAULT_DATE_FORMAT, $entry->getUpdatedDate())." - <strong>Completed</strong></li>";
                                                            } else {
                                                                $resource["description"] .= "<li>".date(DEFAULT_DATE_FORMAT, $entry->getUpdatedDate())." <strong>Score:</strong> To Be Released ".date(DEFAULT_DATE_FORMAT, $quiz->getReleaseUntil())."</li>";
                                                            }
                                                        break;
                                                        case "expired" :
                                                            $resource["description"] .= "<li class=\"incorrect\">".date(DEFAULT_DATE_FORMAT, $entry->getUpdatedDate())." <strong>Expired Attempt</strong>: not completed.</li>";
                                                        break;
                                                        case "inprogress" :
                                                            $resource["description"] .= "<li>".date(DEFAULT_DATE_FORMAT, $entry->getUpdatedDate())." <strong>Attempt In Progress</strong> ( <a href=\"".ENTRADA_RELATIVE."/quizzes?section=attempt&amp;id=".html_encode($quiz->getAquizID())."\">continue quiz</a> )</li>";
                                                        break;
                                                        default :
                                                            continue;
                                                        break;
                                                    }
                                                }
                                                $resource["description"] .= "</ul>";
                                            }

                                            $resource["type"] = "Quiz";
                                            $resource["type_id"] = $entity->getEntityType();
                                            $resource["viewed"] = $quiz->getViewed();
                                        break;
                                        case 9 :
                                            $resource["title"] = "<p class=\"resource-title\">Textbook Reading</p>";
                                            $resource["description"] = "<p class=\"muted resource-description\">" . html_encode($resource["resource_textbook_reading"]) . "</p>";
                                            $resource["type"] = "Textbook Reading";
                                            $resource["type_id"] = $entity->getEntityType();
                                        break;
                                        case 10 :
                                            $resource["title"] = "<p class=\"resource-title\">" . html_encode($resource["lti_title"]) . "</p>";
                                            $resource["description"] = html_encode($resource["lti_notes"]);
                                            $resource["type"] = "LTI Provider";
                                            $resource["type_id"] = $entity->getEntityType();
                                        break;
                                    }

                                    if ($resource) {
                                        switch ($resource["timeframe"]) {
                                            case "pre" :
                                                $entity_timeframe_pre[] = $resource;
                                            break;
                                            case "during" :
                                                $entity_timeframe_during[] = $resource;
                                            break;
                                            case "post" :
                                                $entity_timeframe_post[] = $resource;
                                            break;
                                            case "none" :
                                                $entity_timeframe_none[] = $resource;
                                            break;
                                        }
                                    }
                                }
                            }
                            ?>
                            <div id="event-resources-container">
                            <?php
                            if ($entity_timeframe_pre) { ?>
                                <div id="event-resource-timeframe-pre-container" class="resource-list">
                                    <div class="resource-container-pre">
                                        <p class="timeframe-heading">Before Class</p>
                                        <ul class="timeframe-pre timeframe">
                                            <?php
                                            foreach ($entity_timeframe_pre as $entity) {
                                            ?>
                                            <li>
                                                <div>
                                                    <?php echo $entity["title"]; ?>
                                                    <?php echo $entity["description"]; ?>
                                                </div>
                                                <div>
                                                    <?php
                                                    if ($entity["required"] ==  "1") { ?>
                                                        <span class="label label-important event-resource-stat-label">Required</span>
                                                    <?php
                                                    } else { ?>
                                                        <span class="label label-default event-resource-stat-label">Optional</span>
                                                    <?php
                                                    }
                                                    ?>
                                                    <span class="label label-info event-resource-stat-label"><?php echo html_encode($entity["type"]); ?></span>
                                                    <?php
                                                    switch ($entity["type_id"]) {
                                                        case 1 :
                                                        case 5 :
                                                        case 6 :
                                                        case 11 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/file-event.php?id=".html_encode($entity["efile_id"])."\" title=\"Click to download ".html_encode($title)."\"".(((int) $entity["access_method"]) ? " target=\"_blank\"" : "")."><span class=\"icon-download-alt\"></span></a>";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 3 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 7 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                    }
                                                    ?>
                                                </div>
                                            </li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                            }

                            if ($entity_timeframe_during) { ?>
                                <div id="event-resource-timeframe-during-container" class="resource-list">
                                    <div class="resource-container-during">
                                        <p class="timeframe-heading">During Class</p>
                                        <ul class="timeframe-during timeframe">
                                            <?php
                                            foreach ($entity_timeframe_during as $entity) {
                                            ?>
                                            <li>
                                                <div>
                                                    <?php echo $entity["title"]; ?>
                                                    <?php echo  $entity["description"]; ?>
                                                </div>
                                                <div>
                                                    <?php
                                                    if ($entity["required"] ==  "1") { ?>
                                                        <span class="label label-important event-resource-stat-label">Required</span>
                                                    <?php
                                                    } else { ?>
                                                        <span class="label label-default event-resource-stat-label">Optional</span>
                                                    <?php
                                                    }
                                                    ?>
                                                    <span class="label label-info event-resource-stat-label"><?php echo html_encode($entity["type"]); ?></span>
                                                    <?php
                                                    switch ($entity["type_id"]) {
                                                        case 1 :
                                                        case 5 :
                                                        case 6 :
                                                        case 11 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/file-event.php?id=".html_encode($entity["efile_id"])."\" title=\"Click to download ".html_encode($title)."\"".(((int) $entity["access_method"]) ? " target=\"_blank\"" : "")."><span class=\"icon-download-alt\"></span></a>";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 3 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 7 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                    }
                                                    ?>
                                                </div>
                                            </li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                            }

                            if ($entity_timeframe_post) { ?>
                                <div id="event-resource-timeframe-post-container" class="resource-list">
                                    <div class="resource-container-post">
                                        <p class="timeframe-heading">After Class</p>
                                        <ul class="timeframe-post timeframe">
                                            <?php
                                            foreach ($entity_timeframe_post as $entity) {
                                            ?>
                                            <li>
                                                <div>
                                                    <?php echo $entity["title"]; ?>
                                                    <?php echo $entity["description"]; ?>
                                                </div>
                                                <div>
                                                    <?php
                                                    if ($entity["required"] ==  "1") { ?>
                                                        <span class="label label-important event-resource-stat-label">Required</span>
                                                    <?php
                                                    } else { ?>
                                                        <span class="label label-default event-resource-stat-label">Optional</span>
                                                    <?php
                                                    }
                                                    ?>
                                                    <span class="label label-info event-resource-stat-label"><?php echo html_encode($entity["type"]); ?></span>
                                                    <?php
                                                    switch ($entity["type_id"]) {
                                                        case 1 :
                                                        case 5 :
                                                        case 6 :
                                                        case 11 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/file-event.php?id=".html_encode($entity["efile_id"])."\" title=\"Click to download ".html_encode($title)."\"".(((int) $entity["access_method"]) ? " target=\"_blank\"" : "")."><span class=\"icon-download-alt\"></span></a>";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 3 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 7 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                    }
                                                    ?>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                            }

                            if ($entity_timeframe_none) { ?>
                                <div id="event-resource-timeframe-none-container" class="resource-list">
                                    <div class="resource-container-none">
                                        <p class="timeframe-heading">No Timeframe</p>
                                        <ul class="timeframe-none timeframe">
                                            <?php
                                            foreach ($entity_timeframe_none as $entity) {
                                            ?>
                                            <li>
                                                <div>
                                                    <?php echo $entity["title"]; ?>
                                                    <?php echo $entity["description"]; ?>
                                                </div>
                                                <div>
                                                    <?php
                                                    if ($entity["required"] ==  "1") { ?>
                                                        <span class="label label-important event-resource-stat-label">Required</span>
                                                    <?php
                                                    } else { ?>
                                                        <span class="label label-default event-resource-stat-label">Optional</span>
                                                    <?php
                                                    }
                                                    ?>
                                                    <span class="label label-info event-resource-stat-label"><?php echo html_encode($entity["type"]); ?></span>
                                                    <?php
                                                    switch ($entity["type_id"]) {
                                                        case 1 :
                                                        case 5 :
                                                        case 6 :
                                                        case 11 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/file-event.php?id=".html_encode($entity["efile_id"])."\" title=\"Click to download ".html_encode($title)."\"".(((int) $entity["access_method"]) ? " target=\"_blank\"" : "")."><span class=\"icon-download-alt\"></span></a>";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 3 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                        case 7 :
                                                            if ((((!(int) $entity["release_date"]) || ($entity["release_date"] <= time())) && ((!(int) $entity["release_until"]) || ($entity["release_until"] >= time())))) {
                                                                echo "<span>";
                                                                echo "<a href=\"".ENTRADA_RELATIVE."/link-event.php?id=".html_encode($entity["elink_id"])."\" title=\"Click to visit ".html_encode($entity["link"])."\"  target=\"_blank\"><span class=\"icon-globe\"></span></a>\n";
                                                                echo "</span>";
                                                            }
                                                        break;
                                                    }
                                                    ?>
                                                </div>
                                            </li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                    
                    <div>
                        <?php
                        echo "<a name=\"event-comments-section\"></a>\n";
                        echo "<h2 title=\"Event Comments Section\">Discussions &amp; Comments</h2>\n";
                        echo "<div id=\"event-comments-section\" class=\"section-holder\">\n";
                        if (defined("NOTIFICATIONS_ACTIVE") && NOTIFICATIONS_ACTIVE) {
                            ?>
                            <div id="notifications-toggle" style="display: inline; padding-top: 4px; width: 100%; text-align: right;"></div>
                            <br /><br />
                            <script type="text/javascript">
                            function promptNotifications(enabled) {
                                Dialog.confirm('Do you really wish to '+ (enabled == 1 ? "stop" : "begin") +' receiving notifications when new comments are made on this event?',
                                    {
                                        id:				'requestDialog',
                                        width:			350,
                                        height:			75,
                                        title:			'Notification Confirmation',
                                        className:		'medtech',
                                        okLabel:		'Yes',
                                        cancelLabel:	'No',
                                        closable:		'true',
                                        buttonClass:	'btn',
                                        destroyOnClose:	true,
                                        ok:				function(win) {
                                                            new Window(	{
                                                                            id:				'resultDialog',
                                                                            width:			350,
                                                                            height:			75,
                                                                            title:			'Notification Result',
                                                                            className:		'medtech',
                                                                            okLabel:		'close',
                                                                            buttonClass:	'btn',
                                                                            resizable:		false,
                                                                            draggable:		false,
                                                                            minimizable:	false,
                                                                            maximizable:	false,
                                                                            recenterAuto:	true,
                                                                            destroyOnClose:	true,
                                                                            url:			'<?php echo ENTRADA_URL."/api/notifications.api.php?record_id=".$EVENT_ID; ?>&content_type=event_discussion&action=edit&active='+(enabled == 1 ? '0' : '1'),
                                                                            onClose:			function () {
                                                                                                new Ajax.Updater('notifications-toggle', '<?php echo ENTRADA_URL."/api/notifications.api.php?record_id=".$EVENT_ID; ?>&content_type=event_discussion&action=view');
                                                                                            }
                                                                        }
                                                            ).showCenter();
                                                            return true;
                                                        }
                                    }
                                );
                            }
                            </script>
                            <?php
                            $ONLOAD[] = "new Ajax.Updater('notifications-toggle', '".ENTRADA_URL."/api/notifications.api.php?record_id=".$EVENT_ID."&content_type=event_discussion&action=view')";
                        }

                        $editable	= false;
                        $edit_ajax	= array();
                        if ($event_discussions) {
                            $i = 0;
                            foreach ($event_discussions as $result) {
                                if ($result["proxy_id"] == $ENTRADA_USER->getID()) {
                                    $editable		= true;
                                    $edit_ajax[]	= $result["ediscussion_id"];
                                } else {
                                    $editable		= false;
                                }

                                $poster_name = get_account_data("firstlast", $result["proxy_id"]);

                                echo "<blockquote id=\"event_comment_" . (int) $result["ediscussion_id"]."\">\n";
                                echo " " . html_encode($result["discussion_title"]) . "<br />";
                                echo "	<div class=\"discussion-comment\" id=\"discussion_comment_".$result["ediscussion_id"]."\">".nl2br(html_encode($result["discussion_comment"]))."</div>\n";
                                echo "	<small><strong>".get_account_data("firstlast", $result["proxy_id"])."</strong>, ".date(DEFAULT_DATE_FORMAT, $result["updated_date"])." ".($editable ? " ( <span id=\"edit_mode_" . (int) $result["ediscussion_id"] . "\">edit</span> )" : "") . "</small>\n";
                                echo "</blockquote>\n";

                                $i++;
                            }

                            if ((@is_array($edit_ajax)) && (@count($edit_ajax))) {
                                echo "<script type=\"text/javascript\">\n";
                                foreach ($edit_ajax as $discussion_id) {
                                    echo "var editor_".$discussion_id." = new Ajax.InPlaceEditor('discussion_comment_".$discussion_id."', '".ENTRADA_RELATIVE."/api/discussions.api.php', { rows: 7, cols: 150, okText: 'Save Changes', cancelText: 'Cancel Changes', externalControl: 'edit_mode_".$discussion_id."', callback: function(form, value) { return 'action=edit&sid=".session_id()."&id=".$discussion_id."&discussion_comment='+escape(value) } });\n";
                                }
                                echo "</script>\n";
                            }
                        } else {
                            echo "<div class=\"content-small\">There are no comments or discussions on this event. <strong>Start a conversation</strong>, leave your comment below.</div>\n";
                        }
                        echo "	<br /><br />";
                        echo "	<form action=\"".ENTRADA_RELATIVE."/discussions?action=add".(($USE_QUERY) ? "&amp;".((isset($_GET["drid"])) ? "drid" : "rid")."=".$EVENT_ID : "")."\" method=\"post\">\n";
                        echo "		<input type=\"hidden\" name=\"event_id\" value=\"".$EVENT_ID."\" />\n";
                        echo "		<label for=\"discussion_comment\" class=\"content-subheading\">Leave a Comment</label>\n";
                        echo "		<div class=\"content-small\">Posting comment as <strong>".$_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"]."</strong></div>\n";
                        echo "		<textarea id=\"discussion_comment\" name=\"discussion_comment\" class=\"expandable span12\"></textarea>\n";
                        echo "		<div style=\"text-align: right; padding-top: 8px\"><input type=\"submit\" class=\"btn btn-primary\" value=\"Post Comment\" /></div>\n";
                        echo "	</form>\n";
                        echo "</div>\n";
                        ?>
                    </div>
                    <?php
					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					if ($include_details) {
						$sidebar_html .= "	<li class=\"link\"><a href=\"#event-details-section\" onclick=\"$('event-details-section').scrollTo(); return false;\" title=\"Event Details\">Event Details</a></li>\n";
					}
                    
                    if (isset($include_keywords) && $include_keywords) {
                        $sidebar_html .= "  <li class=\"link\"><a href=\"#event-keywords-section\" onclick=\"$('event-keywords-section').scrollTo(); return false;\" title=\"Event Keywords\">Event Keywords</a></li>\n";
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
					add_error("You are not permitted to access this event. This error has been logged.");

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
				$ENTRADA_USER->getActiveId(),
				$ENTRADA_USER->getActiveGroup(),
				$ENTRADA_USER->getActiveRole(),
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
						echo "Found ".count($learning_events["result_ids_map"])." event".((count($learning_events["result_ids_map"]) != 1) ? "s" : "")." that take place on <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					case "month" :
						echo "Found ".count($learning_events["result_ids_map"])." event".((count($learning_events["result_ids_map"]) != 1) ? "s" : "")." that take place during <strong>".date("F", $learning_events["duration_start"])."</strong> of <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					case "year" :
						echo "Found ".count($learning_events["result_ids_map"])." event".((count($learning_events["result_ids_map"]) != 1) ? "s" : "")." that take place during <strong>".date("Y", $learning_events["duration_start"])."</strong>.\n";
					break;
					default :
					case "week" :
						echo "Found ".count($learning_events["result_ids_map"])." event".((count($learning_events["result_ids_map"]) != 1) ? "s" : "")." from <strong>".date("D, M jS, Y", $learning_events["duration_start"])."</strong> to <strong>".date("D, M jS, Y", $learning_events["duration_end"])."</strong>.\n";
					break;
				}
				?>
			</div>
			<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Learning Events">
				<thead>
					<tr>
						<td class="date<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "date") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("date", "Date &amp; Time"); ?></td>
						<td class="course-code<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "course") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("course", "Course"); ?></td>
						<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["events"]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["events"]["so"]) : ""); ?>"><?php echo public_order_link("title", "Event Title"); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$count_modified = 0;

					foreach ($learning_events["events"] as $result) {
                        $attachments = attachment_check($result["event_id"]);
                        $url = ENTRADA_RELATIVE."/events?rid=".$result["event_id"];
                        $is_modified = false;

                        /**
                         * Determine if this event has been modified since their last visit.
                         */
                        if (isset($result["last_visited"]) && ((int) $result["last_visited"]) && ((int) $result["last_visited"] < (int) $result["updated_date"])) {
                            $is_modified = true;
                            $count_modified++;
                        }

                        if ($is_modified) {
                        }

                        echo "<tr id=\"event-".$result["event_id"]."\" class=\"event".(($is_modified) ? " modified" : "")."\">\n";
                        echo "	<td><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</a></td>\n";
                        echo "	<td><a href=\"".$url."\">".html_encode($result["course_code"])."</a></td>\n";
                        echo "	<td><a href=\"".$url."\">".html_encode($result["event_title"])."</a></td>\n";
                        echo "</tr>\n";
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