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
 * This API file returns a selectbox containing all the events under the 
 * given course id, discluding those which are listed below as having 
 * their parent_id set to the current event.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */

if (!defined("IN_EVENTS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("event", "update", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."].");
} else {
	if (isset($_POST["ajax"]) && ($_POST["ajax"] == 1)) {
		$use_ajax = true;
	} else {
		$use_ajax = false;
	}

	if ($use_ajax) {
		/**
		 * Clears all open buffers so we can return a plain response for the Javascript.
		 */
		ob_clear_open_buffers();

		$PROCESSED = array();
		$PROCESSED["course_id"] = 0;

		if (isset($_POST["event_id"]) && ($tmp_input = clean_input($_POST["event_id"], "int"))) {
			$PROCESSED["event_id"] = $tmp_input;
		}
		if (isset($_POST["course_id"]) && ($tmp_input = clean_input($_POST["course_id"], "int"))) {
			$PROCESSED["course_id"] = $tmp_input;
		}
		if (isset($_POST["add_id"]) && ($tmp_input = $_POST["add_id"])) {
			$PROCESSED["add_id"] = $tmp_input;
		}
		if (isset($_POST["remove_id"]) && ($tmp_input = $_POST["remove_id"])) {
			$PROCESSED["remove_id"] = $tmp_input;
		}
		if (isset($_POST["related_event_ids_clean"]) && ($tmp_input = explode(",", $_POST["related_event_ids_clean"])) && is_array($tmp_input)) {
			$PROCESSED["related_event_ids"] = $tmp_input;
			if ($PROCESSED["add_id"]) {
				$PROCESSED["related_event_ids"][] = $PROCESSED["add_id"];
			}
			if ($PROCESSED["remove_id"] && array_search($PROCESSED["remove_id"], $PROCESSED["related_event_ids"]) !== false) {
				unset($PROCESSED["related_event_ids"][array_search($PROCESSED["remove_id"], $PROCESSED["related_event_ids"])]);
			}
		}
	}
	
	if (isset($PROCESSED["course_id"]) && $PROCESSED["course_id"]) {
		if (isset($PROCESSED["event_id"]) && $PROCESSED["event_id"]) {
			$query = "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($PROCESSED["event_id"]);
			if ($event = $db->GetRow($query)) {
				if ($event["parent_id"]) {
					$keyword = "sibling";
					$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr($event["parent_id"]);
				} else {
					$keyword = "child";
					$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr($PROCESSED["event_id"]);
				}
				$related_event_ids = "";
				$related_event_ids_clean = "";
				$related_events = array();
				if (isset($PROCESSED["related_event_ids"]) && is_array($PROCESSED["related_event_ids"])) {
					foreach ($PROCESSED["related_event_ids"] as $event_id) {
						$query = "SELECT * FROM `events` WHERE `event_id` = ".$db->qstr($event_id)." AND `course_id` = ".$db->qstr($PROCESSED["course_id"]);
						if ($temp_event = $db->GetRow($query)) {
							$related_events[] = $temp_event;
						}
					}
				} else {
					$related_events = $db->GetAll($query);
				}
				if ($related_events) {
					foreach ($related_events as $related_event) {
						$related_event_ids .= ($related_event_ids ? ", ".$db->qstr($related_event["event_id"]) : $db->qstr($related_event["event_id"]));
						$related_event_ids_clean .= ($related_event_ids_clean ? ", ".$related_event["event_id"] : $related_event["event_id"]);
					}
				}
				
				$query = "SELECT * FROM `events` WHERE `course_id` = ".$db->qstr($PROCESSED["course_id"]).($related_event_ids ? " AND `event_id` NOT IN (".$related_event_ids.")" : "");
				if ($course_events = $db->GetAll($query)) {
					?>
					<div style="width: 100%;">
						<input id="related_event_ids_clean" name="related_event_ids_clean" type="hidden" value="<?php echo $related_event_ids_clean; ?>">
						<?php
						foreach ($related_events as $related_event) {
							?>
							<input id="related_event_ids" name="related_event_ids[]" type="hidden" value="<?php echo $related_event["event_id"]; ?>">
							<?php	
						}
						?>
						<div style="width: 21%; position: relative; float: left;">
							<label for="related_events_select" class="form-nrequired"><?php echo ucfirst($keyword); ?> Events</label>
						</div>
						<div style="width: 72%; float: left;">
							<input type="text" name="related_event_id" id="related_event_id" />
							<input class="button-sm" type="button" value="Add" onclick="addRelatedEvent($('related_event_id').value)" />
							<script type="text/javascript">
								$('related_event_id').observe('keypress', function(event){
									if(event.keyCode == Event.KEY_RETURN) {
										Event.stop(event);
										addRelatedEvent($('related_event_id').value);
									}
								});
							</script>
						</div>
						<div style="clear: both; padding-top: 5px;" class="content-small">
							Please select an <strong>Event ID</strong> to be added as a <?php echo $keyword; ?> event.
						</div>
						<div style="width: 21%; position: relative; float: left;">
							&nbsp;
						</div>
						<div style="width: 72%; float: left;" id="related_events_list">
							<ul class="menu" style="margin-top: 15px">
								<?php
								if (is_array($related_events) && count($related_events)) {
									foreach ($related_events as $related_event) {
										?>
										<li class="community" id="related_event_<?php echo $related_event["event_id"]; ?>" style="margin-bottom: 5px; width: 450px; height: 1.5em;">
											<div style="width: 250px; position: relative; float:left; margin-left: 15px;">
												<?php echo $related_event["event_title"]; ?>
											</div>
											<div style="float: left;">
												<?php
													echo date(DEFAULT_DATE_FORMAT, $related_event["event_start"]);
												?>
											</div>
											<div  style="float: right;">
												<img style="position: relative;" src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeRelatedEvent('<?php echo $related_event["event_id"]; ?>');" class="list-cancel-image" />
											</div>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>
					</div>
					<?php
				}
				if ($related_events && $related_event_ids) {
					$added_events = array();
					$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr((isset($event["parent_id"]) && $event["parent_id"] ? $event["parent_id"] : $event["event_id"]))." AND `event_id` NOT IN (".$related_event_ids.")";
					$removed_events = $db->GetAll($query);
					$query = "SELECT * FROM `events` WHERE `parent_id` = ".$db->qstr((isset($event["parent_id"]) && $event["parent_id"] ? $event["parent_id"] : $event["event_id"]))." AND `event_id` IN (".$related_event_ids.")";
					$existing_events = $db->GetAll($query);
					foreach ($related_events as $related_event) {
						if (array_search($related_event, $existing_events) === false) {
							$added_events[] = $related_event;
						}
					}
					if (isset($removed_events) && $removed_events) {
						foreach ($removed_events as $removed_event) {
							$query = "UPDATE `events` SET `parent_id` = NULL WHERE `event_id` = ".$db->qstr($removed_event["event_id"]);
							if (!$db->Execute($query)) {
								application_log("error", "Unable to set parent_id of an event [".$removed_event["event_id"]."] to null to remove the relationship between it and the parent event. Database said: ".$db->ErrorMsg());
							}
						}
					}
					if (isset($added_events) && $added_events) {
						foreach ($added_events as $added_event) {
							$query = "UPDATE `events` SET `parent_id` = ".$db->qstr((isset($event["parent_id"]) && $event["parent_id"] ? $event["parent_id"] : $event["event_id"]))." WHERE `event_id` = ".$db->qstr($added_event["event_id"]);
							if (!$db->Execute($query)) {
								application_log("error", "Unable to set parent_id [".(isset($event["parent_id"]) && $event["parent_id"] ? $event["parent_id"] : $event["event_id"])."] of an event [".$added_event["event_id"]."] to add a relationship between it and the parent event. Database said: ".$db->ErrorMsg());
							}
						}
					}
				}
			}
		} else {
			echo "<div id=\"display-notice-box\" class=\"display-notice\">\n";
			echo "<ul><li>No valid <strong>Event</strong> was identified to fetch the child or sibling events from the system for.</li></ul>";
			echo "</div>\n";
		}
	} else {
		echo "<div id=\"display-notice-box\" class=\"display-notice\">\n";
		echo "<ul><li>There is currently no <strong>Course</strong> associated with this event. Please select one now to view a list of events which may be related to this one.</li></ul>";
		echo "</div>\n";
	}
}