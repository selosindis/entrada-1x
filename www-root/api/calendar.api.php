<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves a particular calendar in either JSON or ICS depending on the extension of the $_GET["request"];
 * http://www.yourschool.ca/calendars/username.json
 * http://www.yourschool.ca/calendars/username.ics
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2009 Queen's University. All Rights Reserved.
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

$user_proxy_id			= 0;
$user_username			= "";
$user_firstname			= "";
$user_lastname			= "";
$user_email				= "";
$user_role				= "";
$user_group				= "";
$user_organisation_id	= 0;

$calendar_type			= "json";

/**
 * Request information.
 */
if ((isset($_GET["request"])) && (substr(trim($_GET["request"]), -4) == ".ics" )) {
	$calendar_type = "ics";
}

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	$user_proxy_id			= $_SESSION["details"]["id"];
	$user_username			= $_SESSION["details"]["username"];
	$user_firstname			= $_SESSION["details"]["firstname"];
	$user_lastname			= $_SESSION["details"]["lastname"];
	$user_email				= $_SESSION["details"]["email"];
	$user_role				= $_SESSION["details"]["role"];
	$user_group				= $_SESSION["details"]["group"];
	$user_organisation_id	= $_SESSION["details"]["organisation_id"];
} else {
	if (!isset($_SERVER["PHP_AUTH_USER"])) {
		http_authenticate();
	} else {
		require_once("Entrada/authentication/authentication.class.php");

		$username = clean_input($_SERVER["PHP_AUTH_USER"], "credentials");
		$password = clean_input($_SERVER["PHP_AUTH_PW"], "trim");

		$auth = new AuthSystem((((defined("AUTH_DEVELOPMENT")) && (AUTH_DEVELOPMENT != "")) ? AUTH_DEVELOPMENT : AUTH_PRODUCTION));
		$auth->setAppAuthentication(AUTH_APP_ID, AUTH_USERNAME, AUTH_PASSWORD);
		$auth->setUserAuthentication($username, $password, AUTH_METHOD);
		$result = $auth->Authenticate(array("id", "username", "firstname", "lastname", "email", "role", "group", "organisation_id"));

		$ERROR = 0;
		if ($result["STATUS"] == "success") {
			if (($result["ACCESS_STARTS"]) && ($result["ACCESS_STARTS"] > time())) {
				$ERROR++;
				application_log("error", "User[".$username."] tried to access account prior to activation date.");
			} elseif (($result["ACCESS_EXPIRES"]) && ($result["ACCESS_EXPIRES"] < time())) {
				$ERROR++;
				application_log("error", "User[".$username."] tried to access account after expiration date.");
			} else {
				$user_proxy_id			= $result["ID"];
				$user_username			= $result["USERNAME"];
				$user_firstname			= $result["FIRSTNAME"];
				$user_lastname			= $result["LASTNAME"];
				$user_email				= $result["EMAIL"];
				$user_role				= $result["ROLE"];
				$user_group				= $result["GROUP"];
				$user_organisation_id	= $result["ORGANISATION_ID"];
				
			switch ($user_group) {
				case "student" :
					if ((!isset($result["ROLE"])) || (!(int) $result["ROLE"])) {
						$user_grad_year = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
					} else {
						$user_grad_year = $user_role;
					}
				break;
				default :
					/**
					 * If you're in MEdTech, always assign a graduating year,
					 * because we normally see more than normal users.
					 */
					$user_grad_year = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
					continue;
				break;
			}
			}
		} else {
			$ERROR++;
			application_log("access", $result["MESSAGE"]);
		}

		if($ERROR) {
			http_authenticate();
		}

		unset($username, $password);
	}
}

if ($user_proxy_id) {
	$event_start	= 0;
	$event_finish	= 0;

	if ((isset($_GET["start"])) && ($tmp_input = clean_input($_GET["start"], array("trim", "int")))) {
		$event_start = $tmp_input;
	}
	if ((isset($_GET["end"])) && ($tmp_input = clean_input($_GET["end"], array("trim", "int")))) {
		$event_finish = $tmp_input;
	}

	$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = $event_start;

	$query		= "	SELECT a.*, b.`audience_type`, MAX(c.`timestamp`) AS `last_visited`
					FROM `events` AS a
					LEFT JOIN `event_audience` AS b
					ON b.`event_id` = a.`event_id`
					LEFT JOIN `statistics` AS c
					ON c.`module` = 'events'
					AND c.`proxy_id` = ".$db->qstr($user_proxy_id)."
					AND c.`action` = 'view'
					AND c.`action_field` = 'event_id'
					AND c.`action_value` = a.`event_id`
					JOIN `courses` AS d
					ON a.`course_id` = d.`course_id`
					WHERE (".(($user_group == "student") ? " (b.`audience_type` = 'grad_year' AND b.`audience_value` = ".$db->qstr($user_role).") OR" : "")."
					".(($user_group == "medtech") ? " (b.`audience_type` = 'grad_year' AND b.`audience_value` = '".(int) $user_grad_year."') OR" : "")."
					(b.`audience_type` = 'proxy_id' AND b.`audience_value` = ".$db->qstr($user_proxy_id).")	OR (b.`audience_type` = 'organisation_id' AND b.`audience_value` = ".$db->qstr($user_organisation_id)."))
					".(($event_start) ? " AND a.`event_start` >= ".$event_start : "")."
					".(($event_finish) ? " AND a.`event_finish` <= ".$event_finish : "")."
					AND d.`organisation_id` = ".$db->qstr($user_organisation_id)."
					GROUP BY a.`event_id`
					ORDER BY a.`event_start` ASC, a.`event_id` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		switch ($calendar_type) {
			case "ics" :
				require_once("Entrada/icalendar/class.ical.inc.php");
				$ical = new iCal("-//".html_encode($_SERVER["HTTP_HOST"])."//iCal ".APPLICATION_NAME." Calendar MIMEDIR//EN", 1, ENTRADA_ABSOLUTE."/calendars/", $user_username);

				foreach ($results as $result) {
					$ical->addEvent(
						array(), // Organizer
						(int) $result["event_start"], // Start Time (timestamp; for an allday event the startdate has to start at YYYY-mm-dd 00:00:00)
						(int) $result["event_finish"], // End Time (write 'allday' for an allday event instead of a timestamp)
						(($result["event_location"]) ? $result["event_location"] : "To Be Announced"), // Location
						1, // Transparancy (0 = OPAQUE | 1 = TRANSPARENT)
						array(), // Array with Strings
						strip_tags($result["event_message"]), // Description
						strip_tags($result["event_title"]), // Title
						1, // Class (0 = PRIVATE | 1 = PUBLIC | 2 = CONFIDENTIAL)
						array(), // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
						5, // Priority = 0-9
						0, // frequency: 0 = once, secoundly - yearly = 1-7
						0, // recurrency end: ('' = forever | integer = number of times | timestring = explicit date)
						0, // Interval for frequency (every 2,3,4 weeks...)
						array(), // Array with the number of the days the event accures (example: array(0,1,5) = Sunday, Monday, Friday
						1, // Startday of the Week ( 0 = Sunday - 6 = Saturday)
						"", // exeption dates: Array with timestamps of dates that should not be includes in the recurring event
						0,  // Sets the time in minutes an alarm appears before the event in the programm. no alarm if empty string or 0
						1, // Status of the event (0 = TENTATIVE, 1 = CONFIRMED, 2 = CANCELLED)
						str_replace("http://", "https://", ENTRADA_URL)."/events?id=".(int) $result["event_id"], // optional URL for that event
						"en", // Language of the Strings
						md5((int) $result["event_id"])
					);
				}

				$ical->outputFile();
			break;
			case "json" :
			default :
				$events = array();
				foreach ($results as $result) {
					$cal_type		= 1;
					$cal_updated	= "";

					if ($result["audience_type"] == "proxy_id") {
						$cal_type = 3;
					}

					if (((int) $result["last_visited"]) && ((int) $result["last_visited"] < (int) $result["updated_date"])) {
						$cal_type = 2;

						$cal_updated = date(DEFAULT_DATE_FORMAT, $result["updated_date"]);
					}

					$events[] = array (
								"id" => $result["event_id"],
								"start"	=> date("c", $result["event_start"]),
								"end" => date("c", $result["event_finish"]),
								"title" => strip_tags($result["event_title"]),
								"loc" => strip_tags($result["event_location"]),
								"type" => $cal_type,
								"updated" => $cal_updated
					);
				}

				echo json_encode($events);
			break;
		}
	}
}
?>