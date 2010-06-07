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
 * This file is used when a learner is being assigned to an available apartment.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: assign.inc.php 1169 2010-05-01 14:18:49Z simpson $
*/

if (!defined("IN_REGIONALED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("regionaled", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/regionaled", "title" => "Assign Accommodations");
	?>
	<h1>Assign Accommodations</h1>
	<?php

	$event_id = 0;

	if (isset($_GET["id"]) && ($tmp_input = clean_input($_GET["id"], array("trim", "int")))) {
		$event_id = $tmp_input;
	}

	if ($event_id) {
		$query = "	SELECT *
					FROM `".CLERKSHIP_DATABASE."`.`events` AS a
					LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
					ON b.`event_id` = a.`event_id`
					LEFT JOIN `".CLERKSHIP_DATABASE."`.`apartment_schedule` AS c
					ON c.`event_id` = a.`event_id`
					LEFT JOIN `".CLERKSHIP_DATABASE."`.`regions` AS d
					ON d.`region_id` = a.`region_id`
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS e
					ON e.`id` = b.`etype_id`
					WHERE a.`event_id` = ".$db->qstr($event_id);
		$event_info = $db->GetRow($query);
		if ($event_info) {

			$apartment_ids = array();
			
			$query = "	SELECT `apartment_id`
						FROM `".CLERKSHIP_DATABASE."`.`apartments`
						WHERE `region_id` = ".$db->qstr($event_info["region_id"])."
						AND (`available_start` = '0' OR `available_start` <= ".$db->qstr(time()).")
						AND (`available_finish` = '0' OR `available_finish` > ".$db->qstr(time()).")";
			$apartments = $db->GetAll($query);
			if ($apartments) {
				foreach ($apartments as $apartment) {
					$apartment_ids[] = $apartment["apartment_id"];
				}
			}

			switch ($STEP) {
				case 3 :
					
				break;
				case 2 :
					$query = "	SELECT *
								FROM `".CLERKSHIP_DATABASE."`.`apartments`
								WHERE `apartment_id` = ".$db->qstr($apartment_id);
					$apartment_info = $db->GetRow($query);
					if ($apartment_info) {
						if($apartment_info["region_id"] != $event_info["region_id"]) {
							$ERROR++;
							$ERRORSTR[]	= "The selected apartment is not in the same region as the event the learner is scheduled for.";
						}
					}

					if ($ERROR) {
						$STEP = 1;
					}
				break;
				default :
					continue;
				break;
			}

			// Page Dipslay
			switch($STEP) {
				case 3 :
					$ONLOAD[] = "setTimeout('window.location=\'".ENTRADA_URL."/admin/regionaled\'', 5000)";

					$SUCCESS++;
					$SUCCESSSTR[] = "You have successfully assigned <strong>".html_encode($event_info["firstname"]." ".$event_info["lastname"])."</strong> to <strong>".html_encode($apartment_info["apartment_title"])."</strong> during the <strong>".html_encode($event_info["event_title"])."</strong> rotation.<br /><br />You will be automatically redirected back to the Regional Education dashboard in 5 seconds, or <a href=\"".ENTRADA_URL."/admin/regionaled\">click here</a> if you do not wish to wait.";

					echo display_success();
				break;
				case 2 :
					
				break;
				default :
					if($ERROR) {
						echo display_error($ERRORSTR);
					}

					if (count($apartment_ids)) {
						/**
						 * Check to ensure the availability still exists.
						 */
						$available_apartments = regionaled_apartment_availability($apartment_ids, $event_info["event_start"], $event_info["event_finish"]);
						if (is_array($available_apartments) && ($available_apartments["openings"] > 0)) {
							$total_apartments = count($available_apartments["apartments"]);
							echo "<div class=\"display-generic\">\n";
							echo "	There ".($available_apartments["openings"] != 1 ? "are" : "is")." currently <strong>".$available_apartments["openings"]." room".($available_apartments["openings"] != 1 ? "s" : "")."</strong> available in <strong>".$total_apartments." apartment".($total_apartments != 1 ? "s" : "")."</strong> in <strong>".get_region_name($event_info["region_id"])."</strong> from <strong>".date("Y-m-d", $event_info["event_start"])."</strong> to <strong>".date("Y-m-d", $event_info["event_finish"])."</strong>. Please select which accommodation you would like this learner to be assigned to.";
							echo "</div>";

							?>
							<form action="<?php echo ENTRADA_URL; ?>/admin/regionaled?section=assign" method="post">
								<input type="hidden" name="step" value="2" />
								<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Available Accommodations">
									<colgroup>
										<col class="modified" />
										<col class="title" />
										<col class="title" />
									</colgroup>
									<tbody>
									<?php
									foreach ($available_apartments["apartments"] as $apartment) {
										?>
										<tr>
											<td class="modified" style="vertical-align: top"><input type="radio" id="" name="apartment_id" value="" /></td>
											<td class="title" style="vertical-align: top"><?php echo html_encode($apartment["details"]["apartment_title"]); ?></td>
											<td class="title" style="vertical-align: top">
												<?php
												if ($apartment["occupants"] && count($apartment["occupants"])) {
													echo "<ul class=\"menu\">\n";
													foreach ($apartment["occupants"] as $result) {
														echo "<li class=\"community\">\n";
														echo	(($result["fullname"]) ? (($result["gender"]) ? ($result["gender"] == 1 ? "F: " : "M: ") : "").$result["fullname"] : $result["occupant_title"]);
														echo "	<div class=\"content-small\">Dates: ".date(DEFAULT_DATE_FORMAT, $result["inhabiting_start"])." until ".date(DEFAULT_DATE_FORMAT, $result["inhabiting_finish"])."</div>";
														echo "</li>";
													}
													echo "</ul>\n";
												} else {
													echo "No other occupants.";
												}
												?>
											</td>
										</tr>
										<?php
									}
									?>
									</tbody>
								</table>
							</form>
							<?php
						} else {
							$NOTICE++;
							$NOTICESTR[] = "Unfortunately there are <strong>no available apartments</strong> in <strong>".get_region_name($event_info["region_id"])."</strong> at this time.";

							echo display_notice();
						}
					} else {
						$NOTICE++;
						$NOTICESTR[] = "Unfortunately there are <strong>no active apartments</strong> in <strong>".get_region_name($event_info["region_id"])."</strong> at this time.";

						echo display_notice();
					}
				break;
			}
		} else {
			$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

			$ERROR++;
			$ERRORSTR[] = "The event id that was provided was not found. Please select a new event from the Regional Education dashboard.";

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer [".$event_id."] when attempting to add an accommodation.");
		}
	} else {
		application_log("notice", "Failed to provide an event identifer when attempting to add an accommodation.");

		header("Location: ".ENTRADA_URL."/admin/regionaled");
		exit;
	}
}