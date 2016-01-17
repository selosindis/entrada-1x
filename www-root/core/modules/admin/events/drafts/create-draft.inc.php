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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} else if ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} else if (!$ENTRADA_ACL->amIAllowed('event', 'create', false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => "", "title" => "Create Draft Schedule");

	echo "<h1>Create Draft Schedule</h1>";

	switch ($STEP) {
		case 2 :
            $PROCESSED = array(
                "status" => "open",
                "options" => array(),
                "created" => time()
            );

            $i = 0;
			if (isset($_POST["options"]) && is_array($_POST["options"]) && !empty($_POST["options"])) {
			    foreach ($_POST["options"] as $option => $value) {
				    $PROCESSED["options"][$i]["option"] = clean_input($option, "alpha");
				    $PROCESSED["options"][$i]["value"] = 1;

                    $PROCESSED["draft_option_".$option] = 1; // Used only to recheck checkboxes after a form error.

				    $i++;
				}
			}
			
			if (isset($_POST["draft_name"]) && !empty($_POST["draft_name"])) {
				$PROCESSED["name"] = clean_input($_POST["draft_name"], array("trim"));
			} else {
				add_error("The <strong>Draft Name</strong> is a required field.");
			}
			
			if (isset($_POST["draft_description"]) && !empty($_POST["draft_description"])) {
				$PROCESSED["description"] = clean_input($_POST["draft_description"], array("nohtml"));
			} else {
                $PROCESSED["description"] = "";
            }

			if (isset($_POST["course_ids"])) {
				foreach ($_POST["course_ids"] as $course_id) {
					$PROCESSED["course_ids"][] = (int) $course_id;
				}
			}

			/**
			 * Non-required field "draft_start_date" / Draft Start (validated through validate_calendars function).
			 * Non-required field "draft_finish_date" / Draft Finish (validated through validate_calendars function).
			 */
			$draft_date = Entrada_Utilities::validate_calendars("copy", true, true, false);
			if ((isset($draft_date["start"])) && ((int) $draft_date["start"])) {
				$PROCESSED["draft_start_date"] = (int) $draft_date["start"];
			} else {
				$PROCESSED["draft_start_date"] = 0;
			}

			if ((isset($draft_date["finish"])) && ((int) $draft_date["finish"])) {
				$PROCESSED["draft_finish_date"] = (int) $draft_date["finish"];
			} else {
				$PROCESSED["draft_finish_date"] = 0;
			}
			
			/**
			 * Required field "new_start" / Event Date & Time Start (validated through validate_calendars function).
			 */
			$start_date = Entrada_Utilities::validate_calendars("new", true, false, false);
			if ((isset($start_date["start"])) && ((int) $start_date["start"])) {
				$PROCESSED["new_start_day"] = (int) $start_date["start"];
			}
			
			if (has_error()) {
				$STEP = 1;
			} else {
                if ($db->AutoExecute("drafts", $PROCESSED, "INSERT") && $draft_id = $db->Insert_ID()) {
                    $creators = array(
                        "draft_id" => $draft_id,
                        "proxy_id" => $ENTRADA_USER->getActiveId()
                    );

                    if (!$db->AutoExecute("draft_creators", $creators, "INSERT")) {
                        application_log("error", "Error when creating draft [".$draft_id."]. Unable to insert to the draft_creators table. Database said: ".$db->ErrorMsg());
                    }

                    if ($PROCESSED["options"]) {
                        // This is just to be safe I am assuming.
                        $query = "DELETE FROM `draft_options` WHERE `draft_id` = ".$db->qstr($draft_id);
                        $db->Execute($query);

                        foreach ($PROCESSED["options"] as $option) {
                            $option["draft_id"] = $draft_id;
                            if (!$db->AutoExecute("draft_options", $option, "INSERT")) {
                                application_log("error", "Error when saving draft [".$draft_id."] options, DB said: ".$db->ErrorMsg());
                            }
                        }
                    }
				
                    if (isset($PROCESSED["course_ids"]) && $PROCESSED["course_ids"]) {
                        foreach ($PROCESSED["course_ids"] as $course_id) {
                            // Copy the Learning Events from this course into the drafts table.
                            $query = "	SELECT *
                                        FROM `events` AS a
                                        WHERE a.`course_id` = ".$db->qstr($course_id)."
                                        AND a.`event_start` >= ".$db->qstr($PROCESSED["draft_start_date"])."
                                        AND a.`event_finish` <= ".$db->qstr($PROCESSED["draft_finish_date"])."
                                        ORDER BY a.`event_start`";
                            $events = $db->GetAll($query);

                            $date_diff = (int) ($PROCESSED["new_start_day"] - $events[0]["event_start"]);

                            foreach ($events as $event) {
                                $event["draft_id"] = $draft_id;

                                // adds the offset time to the event year and week, preserves the day of the week
                                $event["event_start"] = strtotime((date("o", ($event["event_start"] + $date_diff)))."-W".date("W", ($event["event_start"] + $date_diff))."-".date("w", $event["event_start"])." ".date("H:i",$event["event_start"]));
                                $event["event_finish"] = strtotime((date("o", ($event["event_finish"] + $date_diff)))."-W".date("W", ($event["event_finish"] + $date_diff))."-".date("w", $event["event_finish"])." ".date("H:i",$event["event_finish"]));

                                if ($event["objectives_release_date"] != 0) {
                                    $event["objectives_release_date"] = strtotime((date("o", ($event["objectives_release_date"] + $date_diff)))."-W".date("W", ($event["objectives_release_date"] + $date_diff))."-".date("w", $event["objectives_release_date"])." ".date("H:i",$event["objectives_release_date"]));
                                } else {
                                    $event["objectives_release_date"] = 0;
                                }

                                if ($db->AutoExecute("draft_events", $event, "INSERT") && $devent_id = $db->Insert_ID()) {
                                    // Copy the audience for the event.
                                    $query = "	SELECT *
                                                FROM `event_audience`
                                                WHERE `event_id` = ".$db->qstr($event["event_id"]);
                                    $audiences = $db->GetAll($query);
                                    if ($audiences) {
                                        foreach ($audiences as $audience) {
                                            $audience["devent_id"] = $devent_id;
                                            if (!$db->AutoExecute("draft_audience", $audience, "INSERT")) {
                                                add_error("An error occurred when attempting to copy one of the Learning Events into the new draft. A system administrator has been notified of issue, we apologize for the inconvenience.");

                                                application_log("error", "An error occurred when inserting a draft event audience into a draft event schedule. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                    }

                                    // copy the contacts for the event
                                    $query = "	SELECT *
                                                FROM `event_contacts`
                                                WHERE `event_id` = ".$db->qstr($event["event_id"]);
                                    $contacts = $db->GetAll($query);
                                    if ($contacts) {
                                        foreach ($contacts as $contact) {
                                            $contact["devent_id"] = $devent_id;
                                            if (!$db->AutoExecute("draft_contacts", $contact, "INSERT")) {
                                                add_error("An error occurred when attempting to copy one of the Learning Events into the new draft. A system administrator has been notified of issue, we apologize for the inconvenience.");

                                                application_log("error", "An error occurred when inserting a draft event contact into a draft event schedule. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                    }

                                    // copy the eventtypes for the event
                                    $query = "	SELECT *
                                                FROM `event_eventtypes`
                                                WHERE `event_id` = ".$db->qstr($event["event_id"]);
                                    $eventtypes = $db->GetAll($query);
                                    if ($eventtypes) {
                                        foreach ($eventtypes as $eventtype) {
                                            $eventtype["devent_id"] = $devent_id;
                                            if (!$db->AutoExecute("draft_eventtypes", $eventtype, "INSERT")) {
                                                add_error("An error occurred when attempting to copy one of the Learning Events into the new draft. A system administrator has been notified of issue, we apologize for the inconvenience.");

                                                application_log("error", "An error occurred when inserting a draft eventtype into a draft event schedule. Database said: ".$db->ErrorMsg());
                                            }
                                        }
                                    }

                                } else {
                                    add_error("An error occurred when attempting to copy one of the Learning Events into the new draft. A system administrator has been notified of issue, we apologize for the inconvenience.");

                                    application_log("error", "An error occurred when inserting an event into a draft event schedule. DB said: ".$db->ErrorMsg());
                                }
                            }
                        }
                    }
                } else {
                    add_error("An error occurred when attempting to create your new draft. A system administrator has been notified of issue, we apologize for the inconvenience.");

                    application_log("error", "Error occurred when creating a new Learning Event draft. Database said: ".$db->ErrorMsg());
                }
                if (has_error()) {
                    $STEP = 1;
                } else {
                    add_success("You have successfully create a new draft, and you will be <strong>automatically</strong> redirected to it in 5 seconds. You can also <a href=\"".ENTRADA_URL."/admin/events/drafts?section=edit&amp;draft_id=".$draft_id."\">click here</a> to be redirected immediately.");
                    display_success();

                    $ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/events/drafts?section=edit&draft_id=".$draft_id."\\'', 5000)";
                }
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}
	switch ($STEP) {
		case 2 :
			if ($SUCCESS) {
				echo display_success();
			}
			if ($NOTICE) {
				echo display_notice();
			}
			if ($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			
			if (has_error()) {
				echo display_error();
			}

			if (has_notice()) {
				echo display_notice();
			}

			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_RELATIVE."/javascript/picklist.js\"></script>\n";
			$ONLOAD[] = "$('courses_list').style.display = 'none'";

			/**
			* Fetch all courses into an array that will be used.
			*/
			$query = "SELECT * FROM `courses` WHERE `organisation_id` = ".$ENTRADA_USER->getActiveOrganisation()." ORDER BY `course_code` ASC";
			$courses = $db->GetAll($query);
			if ($courses) {
				foreach ($courses as $course) {
					$course_list[$course["course_id"]] = array("code" => $course["course_code"], "name" => $course["course_name"]);
				}
			}
			?>
			<style type="text/css">
				.new_start_day .ui-datepicker-calendar tbody tr:hover td a {background: url("images/ui-bg_flat_55_fbec88_40x100.png") repeat-x scroll 50% 50% #FBEC88;border: 1px solid #FAD42E;color: #363636;}
			</style>
			<div class="no-printing">
				<form action="<?php echo ENTRADA_RELATIVE; ?>/admin/events/drafts?section=create-draft&step=2" method="post" onsubmit="selIt()" class="form-horizontal">

                    <h2 class="collapsable" title="Draft Information Section">Draft Information</h2>
                    <div id="draft-information-section">
                        <div class="control-group">
                            <label class="control-label form-required" for="draft_name">Draft Name</label>
                            <div class="controls">
                                <input type="text" id="draft_name" name="draft_name" value="<?php echo ((isset($PROCESSED["name"])) ? html_encode($PROCESSED["name"]) : ""); ?>"  maxlength="255" placeholder="Example: <?php echo date("Y"); ?> Draft Teaching Schedule" class="span10" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label form-nrequired" for="draft_description">Optional Description</label>
                            <div class="controls">
                                <textarea type="text" name="draft_description" id="draft_description" class="span10 expandable"><?php echo ((isset($PROCESSED["description"])) ? html_encode($PROCESSED["description"]) : ""); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <h2 class="collapsable<?php echo (!isset($PROCESSED["course_ids"]) ? " collapsed" : ""); ?>" title="Copy Events Section">Copy Forward Existing Learning Events</h2>
                    <div id="copy-events-section">
                        <p>Previous Learning Events can be copied into this new draft schedule by selecting courses from the list below and setting the date range. Learning Events found in the selected courses during the selected date range will be automatically copied into the new draft, starting on the week selected in the <strong>New Start Date</strong> field.</p>

                        <div class="control-group">
                            <label class="control-label form-nrequired">Copying Learning Resources</label>
                            <div class="controls">
                                <div class="alert alert-info">
                                    <strong>Did you know:</strong> When you copy learning events forward you can select what learning resources are copied along with each event?
                                </div>

                                <label class="checkbox">
                                    <input type="checkbox" name="options[files]"<?php echo (!isset($_POST) || !$_POST || isset($PROCESSED["draft_option_files"]) ? " checked=\"checked\"" : ""); ?> />
                                    Copy all <strong>attached files</strong>.
                                </label>

                                <label class="checkbox">
                                    <input type="checkbox" name="options[links]"<?php echo (!isset($_POST) || !$_POST || isset($PROCESSED["draft_option_links"]) ? " checked=\"checked\"" : ""); ?> />
                                    Copy all <strong>attached links</strong>.
                                </label>

                                <label class="checkbox">
                                    <input type="checkbox" name="options[objectives]"<?php echo (!isset($_POST) || !$_POST || isset($PROCESSED["draft_option_objectives"]) ? " checked=\"checked\"" : ""); ?> />
                                    Copy all <strong>attached learning objectives</strong>.
                                </label>

                                <label class="checkbox">
                                    <input type="checkbox" name="options[topics]"<?php echo (!isset($_POST) || !$_POST || isset($PROCESSED["draft_option_topics"]) ? " checked=\"checked\"" : ""); ?> />
                                    Copy all <strong>attached hot topics</strong>.
                                </label>

                                <label class="checkbox">
                                    <input type="checkbox" name="options[quizzes]"<?php echo (!isset($_POST) || !$_POST || isset($PROCESSED["draft_option_quizzes"]) ? " checked=\"checked\"" : ""); ?> />
                                    Copy all <strong>attached quizzes</strong>.
                                </label>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label form-nrequired">Courses Included</label>
                            <div class="controls">
                                <?php
                                echo "<select class=\"multi-picklist\" id=\"PickList\" name=\"course_ids[]\" multiple=\"multiple\" size=\"5\" style=\"width: 100%; margin-bottom: 5px\">\n";
                                        if ((is_array($PROCESSED["course_ids"])) && (count($PROCESSED["course_ids"]))) {
                                            foreach ($PROCESSED["course_ids"] as $course_id) {
                                                echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
                                            }
                                        }
                                echo "</select>\n";
                                echo "<div style=\"float: left; display: inline\">\n";
                                echo "	<input type=\"button\" id=\"courses_list_state_btn\" class=\"btn\" value=\"Show List\" onclick=\"toggle_list('courses_list')\" />\n";
                                echo "</div>\n";
                                echo "<div style=\"float: right; display: inline\">\n";
                                echo "	<input type=\"button\" id=\"courses_list_remove_btn\" class=\"btn btn-danger\" onclick=\"delIt()\" value=\"Remove\" />\n";
                                echo "	<input type=\"button\" id=\"courses_list_add_btn\" class=\"btn btn-primary\" onclick=\"addIt()\" style=\"display: none\" value=\"Add\" />\n";
                                echo "</div>\n";
                                echo "<div id=\"courses_list\" style=\"clear: both; padding-top: 3px; display: none\">\n";
                                echo "	<h2>Courses List</h2>\n";
                                echo "	<select class=\"multi-picklist\" id=\"SelectList\" name=\"other_courses_list\" multiple=\"multiple\" size=\"15\" style=\"width: 100%\">\n";
                                        if ((is_array($course_list)) && (count($course_list))) {
                                            foreach ($course_list as $course_id => $course) {
                                                if (!is_array($PROCESSED["course_ids"])) {
                                                    $PROCESSED["course_ids"] = array();
                                                }
                                                if (!in_array($course_id, $PROCESSED["course_ids"])) {
                                                    echo "<option value=\"".(int) $course_id."\">".html_encode($course_list[$course_id]["code"] . " - " . $course_list[$course_id]["name"])."</option>\n";
                                                }
                                            }
                                        }
                                echo "	</select>\n";
                                echo "</div>\n";
                                echo "<script type=\"text/javascript\">\n";
                                echo "	\$('PickList').observe('keypress', function(event) {\n";
                                echo "		if (event.keyCode == Event.KEY_DELETE) {\n";
                                echo "			delIt();\n";
                                echo "		}\n";
                                echo "	});\n";
                                echo "	\$('SelectList').observe('keypress', function(event) {\n";
                                echo "	    if (event.keyCode == Event.KEY_RETURN) {\n";
                                echo "			addIt();\n";
                                echo "		}\n";
                                echo "	});\n";
                                echo "</script>\n";
                                ?>
                            </div>
                        </div>

                        <?php echo Entrada_Utilities::generate_calendars("copy", "", true, true, ((isset($PROCESSED["draft_start_date"])) ? $PROCESSED["draft_start_date"] : strtotime("September 1st, ".(date("o") - 1))), true, true, ((isset($PROCESSED["draft_finish_date"])) ? $PROCESSED["draft_finish_date"] : time()), false); ?>

                        <?php echo Entrada_Utilities::generate_calendars("new", "New Start Date", true, true, ((isset($PROCESSED["new_start_day"])) ? $PROCESSED["new_start_day"] : ((isset($PROCESSED["draft_start_date"])) ? strtotime("+1 Year", $PROCESSED["draft_start_date"]) : strtotime("September 1st, ".(date("o"))))), false, false, 0, false, false, ""); ?>
                    </div>

                    <a href="<?php echo ENTRADA_RELATIVE; ?>/admin/events/drafts" class="btn">Cancel</a>
                    <input type="submit" class="btn btn-primary pull-right" value="Create Draft" />
				</form>
			</div>
		<?php
		break;
	}
}