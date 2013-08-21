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
 * This file displays the delete entry interface.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_VENUE"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
		header("Location: ".ENTRADA_URL);
		exit;
} elseif (!$ENTRADA_ACL->amIAllowed('venue', 'delete', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/office/venues?section=delete", "title" => "Delete Venues");
	
	echo "<h1>Delete Venues</h1>";
	
	if (isset($_POST["delete"])) {
		foreach ($_POST["delete"] as $venue_id) {
			if ($tmp_input = clean_input($venue_id, "numeric")) {
				$PROCESSED["delete"][] = $tmp_input;
				$venues[] = Models_Venue::fetchRow($tmp_input);
			}
		}
	}
	
	switch ($STEP) {
		case 2 :
			foreach ($venues as $venue) {
				$venue_data = $venue->toArray();
				$venue_data["is_active"] = 0;
				if ($venue->fromArray($venue_data)->update()) {
					add_statistic("venues", "delete", "venue_id", $venue->getID(), $ENTRADA_USER->getID());
					$venue_rooms = $venue->getRooms();
					if (is_array($venue_rooms)) {
						foreach ($venue_rooms as $room) {
							if (!$room->fromArray(array("is_active" => 0))->update()) {
								add_error("Failed to update venue room <strong>".$room->getName()."</strong>. An Administrator has been informed, please try again later. You will now be redirected to the venues index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/office/venues\"><strong>click here</strong></a> to continue.");
								application_log("Failed to delete venue room, DB said: ".$db->ErrorMsg());
							} else {
								add_statistic("venue_rooms", "delete", "vroom_id", $room->getID(), $ENTRADA_USER->getID());
							}
						}
					}
					if (!$ERROR) {
						add_success("Successfully deleted <strong>".$venue->getName()."</strong>. You will now be redirected to the venues index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/office/venues\"><strong>click here</strong></a> to continue.");
					}
				} else {
					add_error("Failed to delete <strong>".$venue->getName()."</strong>, an Administrator has been informed, please try again later. You will now be redirected to the venues index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/office/venues\"><strong>click here</strong></a> to continue.");
					application_log("Failed to delete venue room, DB said: ".$db->ErrorMsg());
				}
			}
		break;
	}
	
	switch ($STEP) {
		case 2 :
			if ($ERROR) {
				echo display_error();
			}
			if ($SUCCESS) {
				echo display_success();
			}
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/office/venues\\'', 5000)";
		break;
		case 1 :
		default :
		
			if (isset($venues) && is_array($venues)) { ?>
				<div class="alert alert-info">You have selected the following venues to be deleted. Please confirm below that you would like to delete them.</div>
				<form action="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=delete&step=2" method="POST" id="needs-assessment-list">
					<table class="table table-striped table-bordered" width="100%" cellpadding="0" cellspacing="0" border="0">
						<thead>
							<tr>
								<th width="5%"></th>
								<th>Name</th>
								<th>Address</th>
								<th>City</th>
								<th>Province</th>
								<th>Country</th>
								<th width="8%">Rooms</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($venues as $venue) { ?>
							<tr class="needs-assessment" data-id="<?php echo html_encode($venue->getID()); ?>">
								<td><input class="delete" type="checkbox" name="delete[<?php echo html_encode($venue->getID()); ?>]" value="<?php echo html_encode($venue->getID()); ?>" <?php echo html_encode((in_array($venue->getID(), $PROCESSED["delete"]) ? "checked=\"checked\"" : "")); ?> /></td>
								<td class="name"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode($venue->getName()); ?></a></td>
								<td class="address"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode($venue->getAddress()); ?></a></td>
								<td class="city"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode($venue->getCity()); ?></a></td>
								<td class="province"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode($venue->getProvince()); ?></a></td>
								<td class="country"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode($venue->getCountry()); ?></a></td>
								<td class="rooms"><a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues?section=edit&venue_id=<?php echo html_encode($venue->getID()); ?>"><?php echo html_encode(is_array($venue->getRooms()) ? count($venue->getRooms()) : "-"); ?></a></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<div class="row-fluid">
						<a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues" class="btn" role="button">Cancel</a>
						<input type="submit" class="btn btn-primary pull-right" value="Delete" />
					</div>
				</form>
			<?php } else { ?>
				<div class="alert alert-info">No venues have been selected to be deleted. Please <a href="<?php echo html_encode(ENTRADA_URL); ?>/admin/office/venues">click here</a> to return to the venue index.</div>
			<?php }

		break;
	}
}