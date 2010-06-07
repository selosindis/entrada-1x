<?php
/**
 * Online Course Resources [Pre-Clerkship]
 * @author Unit: Medical Education Technology Unit
 * @author Director: Dr. Benjamin Chen <bhc@post.queensu.ca>
 * @author Developer: Matt Simpson <simpson@post.queensu.ca>
 * @version 3.0
 * @copyright Copyright 2006 Queen's University, MEdTech Unit
 *
 * $Id: syllabus_gen.php 1116 2010-04-13 15:38:31Z jellis $
*/

@set_time_limit(0);
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

function syllabus_display_learning_event() {
	global $db, $result;

	if(((int) $result["release_date"]) && ($result["release_date"] > time())) {
		/**
		 * Event should not yet be included in the syllabi.
		 */
	} elseif(((int) $result["release_until"]) && ($result["release_until"] < time())) {
		/**
		 * Event should no longer be available in the syllabi.
		 */
	} else {
		$primary_contact		= array();
		$other_contacts			= array();
		$other_contacts_names	= array();
		
		$squery		= "
					SELECT a.`proxy_id`, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`email`
					FROM `event_contacts` AS a
					LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON b.`id` = a.`proxy_id`
					WHERE a.`event_id` = ".$db->qstr($result["event_id"])."
					AND b.`id` IS NOT NULL
					ORDER BY a.`contact_order` ASC";
		$sresults	= $db->GetAll($squery);
		if($sresults) {
			foreach($sresults as $key => $sresult) {
				if(!(int) $key) {
					$primary_contact		= array("proxy_id" => $sresult["proxy_id"], "fullname" => $sresult["fullname"], "email" => $sresult["email"]);
				} else {
					$other_contacts[]		= array("proxy_id" => $sresult["proxy_id"], "fullname" => $sresult["fullname"], "email" => $sresult["email"]);
					$other_contacts_names[]	= $sresult["fullname"];
				}
			}
		}
				
		$event_url = ENTRADA_URL."/events?id=".$result["event_id"];

		echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
		echo "<tr>\n";
		echo "	<td colspan=\"2\" width=\"100%\"><h2>".$result["event_title"]."</h2></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\" width=\"25%\">Event URL:</td>\n";
		echo "	<td valign=\"top\" width=\"75%\"><a href=\"".$event_url."\">".$event_url."</a></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Associated Faculty:</td>\n";
		echo "	<td valign=\"top\">";
					if(count($primary_contact)) {
						echo $primary_contact["fullname"]." &lt;<a href=\"mailto:".$primary_contact["email"]."\">".$primary_contact["email"]."</a>&gt;<br>\n";
					
						if(count($other_contacts)) {
							foreach($other_contacts as $other_contact) {
								echo $other_contact["fullname"]." &lt;<a href=\"mailto:".$other_contact["email"]."\">".$other_contact["email"]."</a>&gt;<br>\n";
							}
						}
					} else {
						echo "To be announced";	
					}
		echo "	</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Graduating Year:</td>\n";
		echo "	<td valign=\"top\">".$result["event_grad_year"]."</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Phase:</td>\n";
		echo "	<td valign=\"top\">".strtoupper($result["event_phase"])."</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Event Date/Time:</td>\n";
		echo "	<td valign=\"top\">".date(DEFAULT_DATE_FORMAT, $result["event_start"])."</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Event Duration:</td>\n";
		echo "	<td valign=\"top\">".(int) $result["event_duration"]." minutes</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td valign=\"top\">Event Location:</td>\n";
		echo "	<td valign=\"top\">".(($result["event_location"]) ? $result["event_location"] : "To be announced")."</td>\n";
		echo "</tr>\n";
		
		if(trim(strip_tags($result["event_goals"])) != "") {
			echo "<tr>\n";
			echo "	<td colspan=\"2\">&nbsp;</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\"><b>Event Goals:</b></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\">".strip_tags($result["event_goals"], "<p><a><strong><em><i><b><br><ul><ol><li>")."</td>\n";
			echo "</tr>\n";
		}
		
		if(trim(strip_tags($result["event_objectives"])) != "") {
			echo "<tr>\n";
			echo "	<td colspan=\"2\">&nbsp;</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\"><b>Event Objectives:</b></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\">".strip_tags($result["event_objectives"], "<p><a><strong><em><i><b><br><ul><ol><li>")."</td>\n";
			echo "</tr>\n";
		}
		if(trim(strip_tags($result["event_message"])) != "") {
			echo "<tr>\n";
			echo "	<td colspan=\"2\">&nbsp;</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\"><b>Teachers Message:</b></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "	<td valign=\"top\" colspan=\"2\">".strip_tags($result["event_message"], "<p><a><strong><em><i><b><br><ul><ol><li>")."</td>\n";
			echo "</tr>\n";
		}

		echo "</table>\n";
		echo "<br><br>\n";
	}
	return;
}

/**
 * 	List of valid phases that can be requested.
 */
$EVENT_PHASES = array("1", "2A", "2B", "2C", "2E", "3A", "3B", "3C", "3D");

/**
 * The grad year that is being requested.
 */
if((isset($_GET["grad"])) && ((int) trim($_GET["grad"]))) {
	$EVENT_GRAD_YEAR	= (int) trim($_GET["grad"]);
} else {
	$EVENT_GRAD_YEAR	= (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
}

/**
 * The phase that is being requested.
 */
if((isset($_GET["phase"])) && (in_array(trim($_GET["phase"]), $EVENT_PHASES))) {
	$EVENT_PHASE	= trim($_GET["phase"]);
} else {
	$EVENT_PHASE	= "1";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title>Class of <?php echo $EVENT_GRAD_YEAR; ?> / Phase <?php echo $EVENT_PHASE; ?> Syllabus</title>

	<meta name="author" content="<?php echo $AGENT_CONTACTS["general-contact"]["name"]; ?>, &lt;<?php echo $AGENT_CONTACTS["general-contact"]["email"]; ?>&gt;">
	<meta name="copyright" content="<?php echo COPYRIGHT_STRING; ?>">
	<meta name="docnumber" content="Generated: <?php echo date(DEFAULT_DATE_FORMAT, time()) ?>">
	<meta name="generator" content="Syllabus Generator">
	<meta name="keywords" content="Class of <?php echo $EVENT_GRAD_YEAR; ?>, Phase <?php echo $EVENT_PHASE; ?>, Syllabus, Undergraduate, Education">
	<meta name="subject" content="Class of <?php echo $EVENT_GRAD_YEAR; ?>, Phase <?php echo $EVENT_PHASE; ?> Syllabus">
</head>

<body>
<!-- PAGE BREAK -->

<h1>Curriculum Map</h1>
<center><img src="<?php echo ENTRADA_URL; ?>/images/queens_curriculum.png" width="696" height="1082"></center>
<!-- HEADER RIGHT "Generated: <?php echo date(DEFAULT_DATE_FORMAT, time()); ?>" -->
<!-- FOOTER LEFT "$CHAPTER" -->
<!-- FOOTER RIGHT "$PAGE / $PAGES" -->
<!-- PAGE BREAK -->
<?php
$query		= "
			SELECT a.*, b.`audience_value` AS `event_grad_year`, c.*
			FROM `events` AS a
			LEFT JOIN `event_audience` AS b
			ON b.`event_id` = a.`event_id`
			LEFT JOIN `courses` AS c
			ON c.`course_id` = a.`course_id`
			WHERE b.`audience_type` = 'grad_year'
			AND b.`audience_value` = ".$db->qstr($EVENT_GRAD_YEAR)."
			AND a.`event_phase` = '".$EVENT_PHASE."'
			AND c.`course_active` = '1'
			ORDER BY c.`course_name` ASC, a.`event_start` ASC";
$results	= $db->GetAll($query);
if($results) {
	$current_course	= 0;
	$course_id		= 0;

	foreach($results as $result) {
		$event_id		= $result["event_id"];
		
		if(trim($result["course_name"]) == "") {
			$course_name	= "Uncategorized Event";
			$course_id		= 0;
		} else {
			$course_name	= trim(strip_tags($result["course_name"]));
			$course_id		= (int) $result["course_id"];
		}

		if($course_id != $current_course) {
			echo "<!-- PAGE BREAK -->\n";
			echo "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
			echo "<tr>\n";
			echo "	<td>";
			echo "		<h1>".$course_name."</h1> ".(($result["course_num"]) ? $result["course_num"] : "")."\n";

			if(trim(strip_tags($result["course_description"])) != "") {
				echo "	<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
				echo "	<tr>\n";
				echo "		<td><b>Course Description:</b></td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "		<td>".strip_tags($result["course_description"], "<p><a><strong><em><i><b><br><ul><ol><li>")."</td>\n";
				echo "	</tr>\n";
				echo "	</table>\n";
				echo "	<br><br>\n";
			}

			if(trim(strip_tags($result["course_objectives"])) != "") {
				echo "	<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
				echo "	<tr>\n";
				echo "		<td><b>Course Objectives:</b></td>\n";
				echo "	</tr>\n";
				echo "	<tr>\n";
				echo "		<td>".strip_tags($result["course_objectives"], "<p><a><strong><em><i><b><br><ul><ol><li>")."</td>\n";
				echo "	</tr>\n";
				echo "	</table>\n";
				echo "	<br><br>\n";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
			echo "<!-- PAGE BREAK -->\n";
			echo "<!-- HEADER RIGHT \"Generated: ".date(DEFAULT_DATE_FORMAT, time())."\" -->\n";
			echo "<!-- FOOTER LEFT \"\$CHAPTER\" -->\n";
			echo "<!-- FOOTER RIGHT \"\$PAGE / \$PAGES\" -->\n";

			syllabus_display_learning_event();

			$current_course = $course_id;
		} else {
			syllabus_display_learning_event();
		}
	}
}
?>
</body>
</html>