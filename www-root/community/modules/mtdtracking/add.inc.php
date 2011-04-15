<?php

/**
 * This script adds an MTD event into the database.
 *
 * @author Don Zuiker <don.zuiker@queensu.ca>
 */

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} else {
	$PROCESSED["creator_id"] = $_SESSION["details"]["id"];

	$PROCESSED["resident_name"] = clean_input($_POST["resident_name"], array("notags", "trim"));
	if (!$PROCESSED["resident_name"]) {
		$ERROR++;
		$ERRORSTR[] = "Invalid resident name entered.";
	}

	$full_name = explode(",", $PROCESSED["resident_name"]);
	$last_name = trim($full_name[0]);
	$first_name = trim($full_name[1]);

	$query = "SELECT *
				  FROM `" . AUTH_DATABASE . "`.`user_data_resident`
				  WHERE `last_name` = " . $db->qstr($last_name) . "
				  AND `first_name` = " . $db->qstr($first_name);

	$resident = $db->GetRow($query);

	if (!$ERROR) {
		if ($resident) {
			$PROCESSED["resident_id"] = $resident["proxy_id"];
		} else {
			$ERROR++;
			$ERRORSTR[] = "Resident not found.";
		}
	}

	if (isset($_POST["mtdlocation_duration_order"])) {
		$location_ids = explode(",", trim($_POST["mtdlocation_duration_order"]));
		$mtdlocation_durations = ((isset($_POST["duration_segment"]) && is_array($_POST["duration_segment"])) ? $_POST["duration_segment"] : array());

		if ((is_array($location_ids)) && (count($location_ids))) {
			$count = 0;
			$total_time = 0;
			foreach ($location_ids as $order => $mtdlocation_id) {
				if (($mtdlocation_id = clean_input($mtdlocation_id, array("trim", "int"))) && ($duration = clean_input($mtdlocation_durations[$order], array("trim")))) {
					$query = "SELECT `facility_name` FROM `mtd_facilities` WHERE `id` = " . $db->qstr($mtdlocation_id);
					$result = $db->GetRow($query);
					if ($result) {
						$LOCALE_DURATION["locale_durations"][$count] = array($mtdlocation_id, $duration);
						$total_time += $duration;
						$count++;
					} else {
						$ERROR++;
						$ERRORSTR[] = "One of the <strong>locations</strong> you specified was invalid.";
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "One of the <strong>locations</strong> you specified is invalid.";
				}
			}
			
			if ($total_time > 100 || $total_time == 0) {
				$ERROR++;
				$ERRORSTR[] = "The total time spent cannot be greater than 100% or equal to 0%.";
			}
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
	}


	$start_date = clean_input($_POST["start_date"], array("notags", "trim"));
	$start_date_time = new DateTime($start_date);
	$start_date = $db->qstr($start_date_time->format('Y-m-d'));

	$end_date = clean_input($_POST["end_date"], array("notags", "trim"));
	$end_date_time = new DateTime($end_date);
	$end_date = $db->qstr($end_date_time->format('Y-m-d'));

	if (is_null($start_date) && is_null($end_date) && $start_date == "" && $end_date == "") {
		$PROCESSED["start_date"] = 0;
		$PROCESSED["end_date"] = 0;
		$ERROR++;
		$ERRORSTR[] = "Start date and end date are required.";
	}

	if (validate_start_end_dates($start_date, $end_date)) {
		$PROCESSED["start_date"] = $start_date;
		$PROCESSED["end_date"] = $end_date;
	} else {
		$PROCESSED["start_date"] = 0;
		$PROCESSED["end_date"] = 0;
		$ERROR++;
		$ERRORSTR[] = "Start date cannot be after the end date.";
	}

	$PROCESSED["type_code"] = clean_input($_POST["type_code"], array("notags", "trim"));
	if (!$PROCESSED["type_code"]) {
		$ERROR++;
		$ERRORSTR[] = "Type is required.";
	}

	$PROCESSED["service_id"] = validate_integer_field($_POST["service_id"]);
	if (!$PROCESSED["service_id"]) {
		$ERROR++;
		$ERRORSTR[] = "No service code found.";
	}

	if (!$ERROR) {
		//Validate that there is no overlapp of dates for this resident
		$query = "SELECT *
				  FROM  `mtd_schedule`
				  WHERE `resident_id` = " . $db->qstr($resident["proxy_id"]);

		$results = $db->GetAll($query);
		if ($results) {
			foreach ($results as $result) {
				$temp_start_date = new DateTime($result["start_date"]);
				$temp_end_date = new DateTime($result["end_date"]);

				if ($start_date_time >= $temp_start_date && $start_date_time <= $temp_end_date) {
					$query = "SELECT service_description
							  FROM  `mtd_moh_service_codes`
							  WHERE `id` = " . $db->qstr($result["service_id"]);

				    $service_program = $db->GetOne($query);
					$ERROR++;
					$ERRORSTR[] = "The selected start date overlapps with an existing entry for the " . $service_program . " program.";
				}
				if ($end_date_time >= $temp_start_date && $end_date_time <= $temp_end_date) {
					$ERROR++;
					$ERRORSTR[] = "The selected end date overlapps with an existing entry for the " . $service_program . " program.";
				}
			}
		}
	}

	if (!$ERROR) {
		//Add this MTD to the schedule.
		if ($db->AutoExecute(DATABASE_NAME . ".mtd_schedule", $PROCESSED, "INSERT")) {
			$new_schedule_id = $db->Insert_Id();
			//echo "<div id=\"responseMsg\">New Schedule ID: " . $new_schedule_id . " Length: " . sizeof($LOCALE_DURATION["locale_durations"]) . "</div>";
			foreach ($LOCALE_DURATION["locale_durations"] as $locale) {
				$LOCALE_PROCESSED["schedule_id"] = $new_schedule_id;
				$LOCALE_PROCESSED["location_id"] = $locale[0];
				$LOCALE_PROCESSED["percent_time"] = $locale[1];
				$result = $db->AutoExecute(DATABASE_NAME . ".mtd_locale_duration", $LOCALE_PROCESSED, "INSERT");
				if (!$result) {
					$ERROR++;
					$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
				}
			}

			if (!$ERROR) {
				$SUCCESS++;
				$SUCCESSSTR[] = "<p>You have successfully entered a new MTD event.</p>";
			}

			application_log("success", "Successfully entered an MTD for CMPA No: " . $PROCESSED["cmpa_no"] . "by [" . $PROCESSED["userId"] . "].");
		} else {
			$ERROR++;
			$ERRORSTR[] = "Failed to add new MTD event." . $db->ErrorMsg();

			application_log("error", "Failed to enter an MTD for CMPA No: " . $PROCESSED["cmpa_no"] . "by [" . $PROCESSED["userId"] . "]. Database said: " . $db->ErrorMsg());
		}
	}

	if ($SUCCESS) {
		echo "<div id=\"responseMsg\">" . display_success() . "</div>";
	}
	if ($NOTICE) {
		echo "<div id=\"responseMsg\">" . display_notice() . "</div>";
	}
	if ($ERROR) {
		echo "<div id=\"responseMsg\">" . display_error() . "</div>";
	}
}

function validate_start_end_dates($start_date, $end_date) {
	if ($start_date > $end_date) {
		return false;
	}
	
	return true;
}
