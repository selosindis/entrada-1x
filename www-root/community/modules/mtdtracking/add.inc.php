<?php

/**
 * This script adds an MTD event into the database.
 *
 * @author Don Zuiker <don.zuiker@queensu.ca>
 */
@set_include_path(implode(PATH_SEPARATOR, array(
					dirname(__FILE__) . "/../../../core/includes",
					get_include_path(),
				)));

require_once("functions.inc.php");

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
				  FROM  `mtd_residents`
				  WHERE `last_name` = " . $db->qstr($last_name) . "
				  AND `first_name` = " . $db->qstr($first_name);

	$resident = $db->GetRow($query);

	if (!$ERROR) {
		if ($resident) {
			$PROCESSED["resident_id"] = $resident["id"];
		} else {
			$ERROR++;
			$ERRORSTR[] = "Resident not found.";
		}
	}

	if (isset($_POST["mtdlocation_duration_order"])) {
		$location_ids = explode(",", trim($_POST["mtdlocation_duration_order"]));
		$mtdlocation_durations = $_POST["duration_segment"];

		if ((is_array($location_ids)) && (count($location_ids))) {
			$count = 0;
			$total_time = 0;
			foreach ($location_ids as $order => $mtdlocation_id) {
				if (($mtdlocation_id = clean_input($mtdlocation_id, array("trim", "int"))) && ($duration = clean_input($mtdlocation_durations, array("trim")))) {
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
			if ($total_time > 100) {
				$ERROR++;
				$ERRORSTR[] = "The total time spent cannot be greater than 100%.";
			}
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "At least one <strong>Location</strong> is required.";
	}


	$start_date = clean_input($_POST["start_date"], array("notags", "trim"));
	$start_date = new DateTime($start_date);
	$start_date = $db->qstr($start_date->format('Y-m-d'));

	$end_date = clean_input($_POST["end_date"], array("notags", "trim"));
	$end_date = new DateTime($end_date);
	$end_date = $db->qstr($end_date->format('Y-m-d'));

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

	$PROCESSED["service_id"] = validate_integer_field($_POST["service_id"]);
	if (!$PROCESSED["service_id"]) {
		$ERROR++;
		$ERRORSTR[] = "No service code found.";
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

?>