<?php
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

if ((isset($_POST["method"])) && ($method = clean_input($_POST["method"], array("notags", "trim")))) {

	
	switch($method) {
		case "login" :
			require_once("Entrada/xoft/xoft.class.php");
			require_once("Entrada/authentication/authentication.class.php");
			
			
			// $credentials = md5('simpson'.'apple123');
			
			// $query = "SELECT * FROM `user_data` WHERE MD5(CONCAT(`username`, `password`)) = $credentails";
			
			
			$logged_in = false;
			if ((!defined("AUTH_ALLOW_CAS")) || (!AUTH_ALLOW_CAS) || (!$CAS_AUTHENTICATED)) {
				$username = clean_input($_POST["username"], "credentials");
				$password = clean_input($_POST["password"], "trim");

				// Check for locked-out-edness before doing anything else
				

				// Check for SESSION lockout also
				
			}

			// Only even try to authorized if not locked out
			
			$auth = new AuthSystem((((defined("AUTH_DEVELOPMENT")) && (AUTH_DEVELOPMENT != "")) ? AUTH_DEVELOPMENT : AUTH_PRODUCTION));
			$auth->setAppAuthentication(AUTH_APP_ID, AUTH_USERNAME, AUTH_PASSWORD);
			$auth->setEncryption(AUTH_ENCRYPTION_METHOD);
			$auth->setUserAuthentication($username, $password, AUTH_METHOD);
			$result = $auth->Authenticate(
				array(
					"id",
					"firstname",
					"lastname",
					"role",
					"group",
					"organisation_id"
				)
			);
			

			if ($ERROR == 0 && $result["STATUS"] == "success") {
				if (isset($USER_ACCESS_ID)) {
					if (!$db->Execute("UPDATE `".AUTH_DATABASE."`.`user_access` SET `login_attempts` = NULL WHERE `id` = ".(int) $USER_ACCESS_ID." AND `app_id` = ".$db->qstr(AUTH_APP_ID))) {
						application_log("error", "Unable to incrememnt the login attempt counter for user [".$username."]. Database said ".$db->ErrorMsg());
					}
				}

				$GUEST_ERROR = false;
				if ($result["GROUP"] == "guest") {
					$query = "	SELECT COUNT(*) AS total
								FROM `community_members`
								WHERE `proxy_id` = ".$db->qstr($result["ID"])."
								AND `member_active` = 1";
					$community_result	= $db->GetRow($query);
					if ((!$community_result) || ($community_result["total"] == 0)) {
						// This guest user doesn't belong to any communities, don't let them log in.
						$GUEST_ERROR = true;
					}
				}

				if (($result["ACCESS_STARTS"]) && ($result["ACCESS_STARTS"] > time())) {
					$ERROR++;
					$ERRORSTR[] = "Your access to this system does not start until ".date("r", $result["ACCESS_STARTS"]);

					application_log("error", "User[".$username."] tried to access account prior to activation date.");
				} elseif (($result["ACCESS_EXPIRES"]) && ($result["ACCESS_EXPIRES"] < time())) {
					$ERROR++;
					$ERRORSTR[] = "Your access to this system expired on ".date("r", $result["ACCESS_EXPIRES"]);

					application_log("error", "User[".$username."] tried to access account after expiration date.");
				} elseif ($GUEST_ERROR) {
					$ERROR++;
					$ERRORSTR[] = "To log in using guest credentials you must be a member of at least one community.";
					application_log("error", "Guest user[".$username."] tried to log in and isn't a member of any communities.");
				} else {
					if (function_exists("adodb_session_regenerate_id")) {
						adodb_session_regenerate_id();
					} else {
						session_regenerate_id();
					}

					application_log("access", "User[".$username."] successfully logged in.");
					
					$logged_in = true;
					$user_details = array();
					$user_details["authenticated"] = $logged_in;
					$user_details["id"] = $result["ID"];
					$user_details["firstname"] = $result["FIRSTNAME"];
					$user_details["lastname"] = $result["LASTNAME"];
					$user_details["role"] = $result["ROLE"];
					$user_details["group"] = $result["GROUP"];
					$user_details["organisation_id"] = $result["ORGANISATION_ID"];
					$_SESSION["details"] = array();
					$_SESSION["isAuthorized"] = true;
					$_SESSION["details"]["id"] = $result["ID"];
					$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"] = time();
					
					echo json_encode($user_details);
					/**
					 * Any custom session information that needs to be set on a per-group basis.
					 */
					switch ($_SESSION["details"]["group"]) {
						case "student" :
							if ((!isset($result["ROLE"])) || (!clean_input($result["ROLE"], "alphanumeric"))) {
								$_SESSION["details"]["grad_year"] = fetch_first_year();
							} else {
								$_SESSION["details"]["grad_year"] = $result["ROLE"];
							}
						break;
						case "medtech" :
							/**
							 * If you're in MEdTech, always assign a graduating year,
							 * because we normally see more than normal users.
							 */
							$_SESSION["details"]["grad_year"] = fetch_first_year();
						break;
						case "staff" :
						case "faculty" :
						default :
							continue;
						break;
					}

					$_SESSION["permissions"] = permissions_load();

					$auth->updateLastLogin();
				}


			}
			unset($result, $username, $password);
			//echo json_encode($method);
			break;
		
		case "agenda" :
			
			if ((isset($_POST["authenticated"]))) {
				$authenticated = $_POST["authenticated"];
			}
			if ((isset($_POST["id"])) && ((int) trim($_POST["id"]))) {
				$id = $_POST["id"];
			}
			if ((isset($_POST["organisation_id"])) && ((int) trim($_POST["organisation_id"]))) {
				$organisation_id = $_POST["organisation_id"];
			}
			if ((isset($_POST["firstname"])) && (trim($_POST["firstname"]))) {
				$firstname = $_POST["firstname"];
			}
			if ((isset($_POST["lastname"])) && (trim($_POST["lastname"]))) {
				$lastname = $_POST["lastname"];
			}
			if ((isset($_POST["role"])) && (trim($_POST["role"]))) {
				$role = $_POST["role"];
			}
			if ((isset($_POST["group"])) && (trim($_POST["group"]))) {
				$group = $_POST["group"];
			}

			$user_proxy_id = $id;
			$user_username = "";
			$user_firstname = $firstname;
			$user_lastname = $lastname;
			$user_email = "";
			$user_role = $role;
			$user_group = $group;
			$user_organisation_id = $organisation_id;

			$calendar_type = "json";



			/**
			 * Determine the type of calendar the user is requesting.
			 */
			if (substr($request_filename, -4) == ".ics") {
				$calendar_type = "ics";
			}
			if ($user_proxy_id) {
				$event_start = strtotime("-12 months 00:00:00");
				$event_finish = strtotime("+12 months 23:59:59");

				$learning_events = events_fetch_filtered_events(
						$user_proxy_id,
						$user_group,
						$user_role,
						$user_organisation_id,
						"date",
						"asc",
						"custom",
						$event_start,
						$event_finish,
						events_filters_defaults($user_proxy_id, $user_group, $user_role),
						true,
						1,
						1750);

				if ($ENTRADA_ACL->amIAllowed("clerkship", "read")) {
					$query = "	SELECT c.*
								FROM `".CLERKSHIP_DATABASE."`.`events` AS a
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`event_contacts` AS b
								ON b.`event_id` = a.`event_id`
								LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS c
								ON c.`rotation_id` = a.`rotation_id`
								WHERE a.`event_finish` >= ".$db->qstr(strtotime("00:00:00"))."
								AND (a.`event_status` = 'published' OR a.`event_status` = 'approval')
								AND b.`econtact_type` = 'student'
								AND b.`etype_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
								ORDER BY a.`event_start` ASC";
					$clerkship_schedule	= $db->GetRow($query);
					if (isset($clerkship_schedule) && $clerkship_schedule && $clerkship_schedule["rotation_id"] < MAX_ROTATION) {
						$course_id = $clerkship_schedule["course_id"];
						$course_ids = array();
						$query 	= "SELECT `course_id` FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations` 
								WHERE `course_id` <> ".$db->qstr($course_id)." 
								AND `course_id` <> 0";
						$course_ids_array = $db->GetAll($query);
						foreach ($course_ids_array as $id) {
								$course_ids[] = $id;
						}
						foreach ($learning_events["events"] as $key => $event) {
							if (array_search($event["course_id"], $course_ids) !== false) {
								unset($learning_events["events"][$key]);
							}
						}
					}
				}

				switch ($calendar_type) {
					case "ics" :
						add_statistic("calendar.api", "view", "type", "ics");

						require_once("Entrada/icalendar/class.ical.inc.php");

						$ical = new iCal("-//".html_encode($_SERVER["HTTP_HOST"])."//iCal ".APPLICATION_NAME." Calendar MIMEDIR//EN", 1, ENTRADA_ABSOLUTE."/calendars/", $user_username);

						if (!empty($learning_events["events"])) {
							foreach ($learning_events["events"] as $event) {
								$ical->addEvent(
									array(), // Organizer
									(int) $event["event_start"], // Start Time (timestamp; for an allday event the startdate has to start at YYYY-mm-dd 00:00:00)
									(int) $event["event_finish"], // End Time (write 'allday' for an allday event instead of a timestamp)
									(($event["event_location"]) ? $event["event_location"] : "To Be Announced"), // Location
									1, // Transparancy (0 = OPAQUE | 1 = TRANSPARENT)
									array(), // Array with Strings
									strip_tags($event["event_message"]), // Description
									strip_tags($event["event_title"]), // Title
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
									str_replace("http://", "https://", ENTRADA_URL)."/events?id=".(int) $event["event_id"], // optional URL for that event
									"en", // Language of the Strings
									md5((int) $event["event_id"])
								);
							}
						}

						$ical->outputFile();
					break;
					case "json" :
					default :
						$events = array();
						if (!empty($learning_events["events"])) {
							foreach ($learning_events["events"] as $drid => $event) {
								$cal_type = 1;
								$cal_updated = "";

								if ($event["audience_type"] == "proxy_id") {
									$cal_type = 3;
								}

								if (((int) $event["last_visited"]) && ((int) $event["last_visited"] < (int) $event["updated_date"])) {
									$cal_type = 2;

									$cal_updated = date(DEFAULT_DATE_FORMAT, $event["updated_date"]);
								}

								$events[] = array(
											"id" => $event["event_id"],
											"start_date"	=> date("o-m-d G:i", $event["event_start"]),
											"end_date" => date("o-m-d G:i", $event["event_finish"]),
											"text" => strip_tags($event["event_title"]),
											"details" => $event["event_description"]. "<br /><b>Event Duration: </b>". $event["event_duration"] . " minutes <br /><b>Location: </b>". ($event["event_location"] == "" ? "To be announced" : $event["event_location"]) ."",
											
								);
								
							}
						}

						echo json_encode($events);
					break;
				}
			}
			break;
		case "notices" :			
			if ((isset($_POST["authenticated"]))) {
				$authenticated = $_POST["authenticated"];
			}
			if ((isset($_POST["id"])) && ((int) trim($_POST["id"]))) {
				$id = $_POST["id"];
			}
			if ((isset($_POST["organisation_id"])) && ((int) trim($_POST["organisation_id"]))) {
				$organisation_id = $_POST["organisation_id"];
			}
			if ((isset($_POST["firstname"])) && (trim($_POST["firstname"]))) {
				$firstname = $_POST["firstname"];
			}
			if ((isset($_POST["lastname"])) && (trim($_POST["lastname"]))) {
				$lastname = $_POST["lastname"];
			}
			if ((isset($_POST["role"])) && (trim($_POST["role"]))) {
				$role = $_POST["role"];
			}
			if ((isset($_POST["group"])) && (trim($_POST["group"]))) {
				$group = $_POST["group"];
			}
			if ((isset($_POST["sub_method"])) && (trim($_POST["sub_method"]))) {
				$sub_method = $_POST["sub_method"];
			}
			if ((isset($_POST["notice_id"])) && (trim($_POST["notice_id"]))) {
				$notice_id = $_POST["notice_id"];
			}
			
			switch ($group) {
				case "alumni" :
					$corrected_role = "students";
				break;
				case "faculty" :
					$corrected_role = "faculty";
				break;
				case "medtech" :
					$corrected_role = "medtech";
				break;
				case "resident" :
					$corrected_role = "resident";
				break;
				case "staff" :
					$corrected_role = "staff";
				break;
				case "student" :
				default :
					$cohort = groups_get_cohort($id);
					$corrected_role = "students";
				break;
			}
			
			$query = "	SELECT a.*, b.`statistic_id`, MAX(b.`timestamp`) AS `last_read`
						FROM `notices` AS a
						LEFT JOIN `statistics` AS b
						ON b.`module` = 'notices'
						AND b.`proxy_id` = ".$db->qstr($id)."
						AND b.`action` = 'read'
						AND b.`action_field` = 'notice_id'
						AND b.`action_value` = a.`notice_id`
						LEFT JOIN `notice_audience` AS c 
						ON a.`notice_id` = c.`notice_id` 
						WHERE (
							c.`audience_type` = 'all:users'
							".($corrected_role == "medtech" ? "OR c.`audience_type` LIKE '%all%' OR c.`audience_type` = 'cohorts'" : "OR c.`audience_type` = 'all:".$corrected_role."'")."
							OR
							((
								c.`audience_type` = 'students' 
								OR c.`audience_type` = 'faculty' 
								OR c.`audience_type` = 'staff') 
								AND c.`audience_value` = ".$db->qstr($id)."
							) 
							OR ((
								c.`audience_type` = 'cohorts' 
								OR c.`audience_type` = 'course_list') 
								AND c.`audience_value` IN (
									SELECT `group_id` 
									FROM `group_members` 
									WHERE `proxy_id` = ".$db->qstr($id).")
							)
						) 
						AND (a.`organisation_id` IS NULL 
						OR a.`organisation_id` = ".$db->qstr($organisation_id).") 
						AND (a.`display_from`='0' 
						OR a.`display_from` <= '".time()."') 
						AND (a.`display_until`='0' 
						OR a.`display_until` >= '".time()."') 
						AND a.`organisation_id` = ".$db->qstr($organisation_id)."
						GROUP BY a.`notice_id`
						ORDER BY a.`updated_date` DESC, a.`display_until` ASC";
			
			$notices_to_display = array();
			$results = $db->GetAll($query);
			if ($results) {
				$rows = 0;
				switch ($sub_method) {
					case "fetch_notices" :
						foreach ($results as $result) {
							$result["default_date"][] = date(DEFAULT_DATE_FORMAT, $result["updated_date"]);
							//$result["last_read"] = date(DEFAULT_DATE_FORMAT, $result["last_read"]);
							$notices_to_display[] = $result;
						}
						echo json_encode($notices_to_display);
					break;
					case "count_notices" :
						foreach ($results as $result) {
							if ((!$result["statistic_id"]) || ($result["last_read"] <= $result["updated_date"])) {
								$rows ++;
							}
						}
						echo $rows;
					break;
					case "mark_read" :
						add_statistic("notices", "read", "notice_id", $notice_id);
						echo $rows;
					break;
						
				}
			}
		break;
	}
}
?>
