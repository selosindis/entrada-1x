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

$request = explode("/", ((isset( $_GET["request"])) ? clean_input($_GET["request"], array("url", "lowercase", "nows")) : ""));

$user_proxy_id = 0;
$user_username = "";
$user_firstname = "";
$user_lastname = "";
$user_email = "";
$user_role = "";
$user_group = "";
$user_organisation_id = 0;

$calendar_type = "json";
$user_private_hash = "";

/**
 * Check if the request has multiple parts to it indicating the URL contains a private_hash,
 * which allows them to by-pass the authentication for this calendar, thus allowing them to
 * load a calendar into Google Calendar.
 *
 * http://demo.entrada-project.org/calendars/private-jd7ghr5ga5f7cc5bd4357ab6d707faaa/username.ics
 *
 */
if (is_array($request) && (count($request) == 2) && isset($request[0]) && (substr($request[0], 0, 8) == "private-") && ($tmp_input = str_ireplace("private-", "", $request[0]))) {
	$user_private_hash = $tmp_input;
	$request_filename = (isset($request[1]) ? $request[1] : "");
} else {
	$request_filename = (isset($request[0]) ? $request[0] : "");
}

/**
 * Determine the type of calendar the user is requesting.
 */
if (substr($request_filename, -4) == ".ics") {
	$calendar_type = "ics";
}

/**
 * Check if the user is already authenticated.
 */
if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	$user_proxy_id = $_SESSION["details"]["id"];
	$user_username = $_SESSION["details"]["username"];
	$user_firstname = $_SESSION["details"]["firstname"];
	$user_lastname = $_SESSION["details"]["lastname"];
	$user_email = $_SESSION["details"]["email"];
	$user_role = $_SESSION["details"]["role"];
	$user_group = $_SESSION["details"]["group"];
	$user_organisation_id = $_SESSION["details"]["organisation_id"];
	$user_grad_year = $_SESSION["details"]["grad_year"];
} else {
	/**
	 * If the are not already authenticated, check to see if they have provided
	 * a private hash in the URL.
	 */
	if ($user_private_hash) {
		/**
		 * @todo Add a setUserHashAuthentication() method to the authentication client and server so we can use the
		 * web-service instead of querying the data directly to authenticate a private-hash.
		 */
		$query = "	SELECT a.`id`, a.`username`, a.`firstname`, a.`lastname`, a.`email`, b.`role`, b.`group`, a.`organisation_id`
					FROM `".AUTH_DATABASE."`.`user_data` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
					ON b.`user_id` = a.`id`
					WHERE b.`private_hash` = ".$db->qstr($user_private_hash)."
					AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
					AND b.`account_active` = 'true'
					AND (b.`access_starts`='0' OR b.`access_starts` <= ".$db->qstr(time()).")
					AND (b.`access_expires`='0' OR b.`access_expires` >= ".$db->qstr(time()).")
					GROUP BY a.`id`";
		$result = $db->GetRow($query);
		if ($result) {
			$user_proxy_id = $result["id"];
			$user_username = $result["username"];
			$user_firstname = $result["firstname"];
			$user_lastname = $result["lastname"];
			$user_email = $result["email"];
			$user_role = $result["role"];
			$user_group = $result["group"];
			$user_organisation_id = $result["organisation_id"];

			switch ($user_group) {
				case "student" :
					if ((!isset($result["role"])) || (!(int) $result["role"])) {
						$user_grad_year = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
					} else {
						$user_grad_year = $user_role;
					}
				break;
				default :
					/**
					 * If you're not a student, always assign a graduating year,
					 * because having no events in the calendar causes it not
					 * to validate.
					 */
					$user_grad_year = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
				break;
			}
		} else {
			/**
			 * If the query above fails, redirect them back here but without the
			 * private hash which will trigger the HTTP Authentication.
			 */
			header("Location: ".ENTRADA_URL."/calendars/".$request_filename);
			exit;
		}
	} else {
		/**
		 * If they are not already authenticated, and they don't have a private
		 * hash in the URL, then send them through to HTTP authentication.
		 */
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
				$user_proxy_id = $result["ID"];
				$user_username = $result["USERNAME"];
				$user_firstname = $result["FIRSTNAME"];
				$user_lastname = $result["LASTNAME"];
				$user_email = $result["EMAIL"];
				$user_role = $result["ROLE"];
				$user_group = $result["GROUP"];
				$user_organisation_id = $result["ORGANISATION_ID"];

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
						 * If you're not a student, always assign a graduating year,
						 * because having no events in the calendar causes it not
						 * to validate.
						 */
						$user_grad_year = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
					break;
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