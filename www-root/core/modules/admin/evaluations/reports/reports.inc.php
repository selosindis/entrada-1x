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
 * This file is used to add events to the entrada.events table.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "read", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/", "title" => "Evaluation Reports");

	$QUESTION_COMMENTS = false;  // Show question comments at the end of the question
	$END_COMMENTS = true;		// Show question comments at the end of the report
	
	/**
	 * Collect the evaluation(s) to be reported.
	 */
	if(isset($_GET["evaluation"]))  {
		$EVALUATIONS[] =  trim($_GET["evaluation"]);
	} elseif((!isset($_POST["checked"])) || (!is_array($_POST["checked"])) || (!@count($_POST["checked"]))) {
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	} else {
		foreach($_POST["checked"] as $evaluation) {
			$evaluation = trim($evaluation);
			if($evaluation) {
				$EVALUATIONS[] = $evaluation;
			}
		}
		if(!@count($EVALUATIONS)) {
			$ERROR++;
			$ERRORSTR[] = "There were no valid evaluation identifiers to report. Please ensure that you access this section through the event index.";
			echo display_error();
		}
	}
	/**
	 * Produce a report for each evaluation
	 */
	foreach($EVALUATIONS as $evaluation){
        list($evaluator, $target) = explode(":",$evaluation);
		$STUDENTS = $evaluator=="s";

		/**
		 * Get generic form and evaluation information for selected target
		 */
		$report = $db->GetRow("	SELECT t.`evaluation_id` `evaluation`, t.`target_value` `target`, f.`eform_id` form_id, f.`form_title`, f.`form_description`,
								e.`evaluation_title`, e.`evaluation_description`, e.`evaluation_start`, e.`evaluation_finish`, e.`min_submittable`, e.`max_submittable`, e.`release_date`, e.`release_until`,
								CONCAT(UPPER(SUBSTRING(`target_shortname`, 1, 1)), LOWER(SUBSTRING(`target_shortname` FROM 2))) as `type` FROM `evaluation_targets` t
								INNER JOIN `evaluations` e ON t.`evaluation_id` = e.`evaluation_id`
								LEFT JOIN `evaluation_forms` f ON e.`eform_id` = f.`eform_id`
							  	INNER JOIN `evaluations_lu_targets` lt ON t.`target_id` = lt.`target_id`
								WHERE t.`etarget_id` = ".$db->qstr($target));

		switch($report["type"]) {
			case "Course" :
				$type = $db->GetRow("	SELECT `course_name` `name`, `course_code` `code` FROM `courses` 
							WHERE `course_id` = ".$db->qstr($report["target"]));
				$title = ($STUDENTS?"Students ":"")."Course Evaluation ";
			break;
			case "Teacher" :
				$type = $db->GetRow("	SELECT CONCAT(`lastname`,', ',`firstname`) `name`, `id` `code` FROM `".AUTH_DATABASE."`.`user_data`
							WHERE `id` = ".$db->qstr($report["target"]));
				$title = ($STUDENTS?"Students ":"")."Teacher Evaluation ";
			break;
			default:
				$title = ($STUDENTS?"Students ":"")."Evaluation ";
			break;
		}

		echo	"<div class=\"no-printing\">";
		echo	"<table width=\"100%\" summary=\"Evaluation Reports\">";
		echo	"	<colgroup>
						<col style=\"width: 18%\" />
						<col style=\"width: 42%\" />
						<col style=\"width: 12%\" />
						<col style=\"width: 28%\" />
					</colgroup>";
		echo 	"	<tr><td colspan=\"4\"><h2>$title - $report[evaluation_title]</h2></td></tr>\n";
		echo	"	<tr><td colspan=\"2\"><h2 style=\"border:none; margin:0 0 0 0\">$type[name]</h2></td>";
		if ($report["type"] == "Course") {
			echo "<td><h3>Course code:</h3></td><td>[$type[code]]</td></tr>";
		} else {
			echo "<td colspan=\"2\" /></tr>";
		}
		echo	"	<tr><td><h3> Evaluation period:</h3></td><td>".date("M jS", $report["evaluation_start"])."  -  ".date("M jS Y", $report["evaluation_finish"])."</td>";
		echo	"		<td><h3> Released:</h3></td><td>".date("M jS Y", $report["release_date"])."</td></tr>";

		/**
		 * Get number of evaluators: individual student(s) and whole class
		 */
		$query = "	SELECT COUNT(DISTINCT(`evaluator`)) FROM
					(
						SELECT ev.`evaluator_value` `evaluator`
						FROM `evaluation_evaluators` ev
						WHERE ev.`evaluator_type` = 'proxy_id'
						AND ev.`evaluation_id` = ".$db->qstr($report["evaluation"])."
						UNION
						SELECT a.`user_id` `evaluator`
						FROM `".AUTH_DATABASE."`.`user_access` a , `evaluation_evaluators` ev
						WHERE ev.`evaluator_type` = 'grad_year' AND ev.`evaluator_value` = a.`role`
						AND ev.`evaluation_id` = ".$db->qstr($report["evaluation"])."
					) t";
		$evaluators	= $db->GetOne($query);

		if ($STUDENTS) {		
			$query = "	SELECT COUNT(DISTINCT(a.`user_id`)) `total`,  a.`role` `year`
						FROM `".AUTH_DATABASE."`.`user_access` a, `evaluation_evaluators` ev
						WHERE ev.`evaluator_type` = 'grad_year' AND ev.`evaluator_value` = a.`role`
						AND ev.`evaluation_id` = ".$db->qstr($report["evaluation"]);
			$class	= $db->GetRow($query);	
		}
		
		$updated = $db->GetOne("SELECT MAX(`updated_date`) FROM `evaluation_progress`
								WHERE `etarget_id` = ".$db->qstr($target)." AND `progress_value` <> 'cancelled'");
				
		$cancelled = $db->GetOne("	SELECT COUNT(`eprogress_id`) FROM `evaluation_progress`
									WHERE `etarget_id` = ".$db->qstr($target)." AND `progress_value` = 'cancelled'");
				
		$progress = $db->GetOne("	SELECT COUNT(`eprogress_id`) FROM `evaluation_progress`
									WHERE `etarget_id` = ".$db->qstr($target)." AND `progress_value` = 'inprogress'");
				
		$completed = $db->GetOne("	SELECT COUNT(`eprogress_id`) FROM `evaluation_progress`
									WHERE `etarget_id` = ".$db->qstr($target)." AND `progress_value` = 'complete'");

		echo	"<tr><td><h3>Evaluators:</h3></td>";

		/**
		 * Calculate number of evaluators, the class, and extra indviduals not in the class.
		 */
		if ($STUDENTS && ($class["total"]>0)) {
			$indies = $evaluators-$class["total"];
			echo "	<td>Class of $class[year] (#$class[total])".($indies?" plus $indies individual".($indies>1?"s":""):"")."</td>";
		} else {
			echo "	<td>$evaluators</td>";
		}
		echo	"<td><h3>Updated:</h3></td><td>".date("M jS", $updated)."</td></tr>";
		echo	"<tr><td><h3> Progress:</h3></td><td colspan=\"3\">".($completed?"$completed - Completed ":"").($progress?"$progress - In progress ":"").($cancelled?"$cancelled - Cancelled ":"")."</td></tr>";
		echo	"<tr><td /><td colspan=\"2\"><hr></td><td /></tr>";

		/**
		 * Get the questions for this form
		 */
		$query = "	SELECT q.`efquestion_id` `id`, t.`questiontype_title`, q.`question_text`
					FROM `evaluation_form_questions` q
					INNER JOIN `evaluations_lu_questiontypes` t ON q.`questiontype_id` = t.`questiontype_id`
					WHERE `eform_id` = ".$db->qstr($report["form_id"])."
					ORDER BY q.`question_order`";
		$questions = $db->GetAll($query);

		/**
		 * Get response statistics for each question
		 */ 
		$number = 0;		
		$comments = array();
		
		foreach($questions as $question){
			$number++;
			echo	"<tr><td  colspan=\"4\">&nbsp;</td></tr>";
			echo	"<tr><td><h3>Question: $number</h3></td><td  colspan=\"2\"><h3>$question[question_text]</h3></td><td>$question[questiontype_title]</td></tr>";

			/**
			 * Get all evaluator responses for each question
			 */
			$query = "	SELECT r.`efresponse_id`, r.`comment`
						FROM `evaluation_responses` r
						INNER JOIN `evaluation_progress` p ON r.`eprogress_id` = p.`eprogress_id`
						WHERE p.`progress_value` <> 'cancelled'
						AND p.`etarget_id` = ".$db->qstr($target)."
						AND r.`eform_id` = ".$db->qstr($report["form_id"])."
						AND r.`efquestion_id` = ".$db->qstr($question["id"]);
			$results	= $db->GetAll($query);

			if ($results) {
				echo	"<tr><td  colspan=\"4\"><table width=\"80%\" summary=\"Question Statistics\">";
				echo	"	<tr><td style=\"width: 34%\" />";
				echo	"		<th align=\"left\" style=\"width: 22%\">Frequency</td>";
				echo	"		<th align=\"left\" style=\"width: 22%\">Percent</td>";
				echo	"		<th align=\"left\" style=\"width: 22%\">Cumulative %</td></tr>";
			
				/**
				 * Get the available responses for each question and build the response profile array
				 */
				$query = "	SELECT `efresponse_id` `id`, `response_order` `order` ,`response_text` `text`,  `response_is_html` `html`, `minimum_passing_level` `mpl`, 0 `freq`, 0 `percent`, 0 `cumul`
							FROM `evaluation_form_responses`
							WHERE `efquestion_id` = ".$db->qstr($question["id"])."
							ORDER BY `response_order`";
				$responses = $db->GetAll($query);

				$answers = count($responses);
				/**
				 * Build profile array
				 */
				$profile = array();
				$index = array();
				foreach ($responses as $response) {
					$i = array_shift($response);
					$profile[$i] = $response;
					$index[] = $i;
				}	
				$total = count($results);					
				$percent = 0;

				/**
				 * Tally repsonses for each question
				 */
				foreach ($results as $result) {
					$profile[$result["efresponse_id"]]["freq"]++;
					if (($QUESTION_COMMENTS||$END_COMMENTS) && $result["comment"] && strlen($result["comment"])) {
						$comments[$number][] = $result["comment"];
					}
				}

				for ($i = 0; $i < $answers; $i++) {
					$profile[$index[$i]]["percent"] = $profile[$index[$i]]["freq"] / $total * 100;
					$percent += $profile[$index[$i]]["percent"];
					$profile[$index[$i]]["cumul"] = $percent;
					echo	"	<tr><td>".$profile[$index[$i]]["text"]."</td><td>".$profile[$index[$i]]["freq"]."</td><td>".round($profile[$index[$i]]["percent"],2)."</td><td>".round($profile[$index[$i]]["cumul"], 2)."</td>";
				}
				echo	"		<tr><td /><td>--------</td><td>--------</td><td /></tr>";
				echo	"		<tr><td>Total:</td><td>$total</td><td>100.0</td><td /></tr>";
				echo	"	</table>";
				
				/**
				 * Use this code to Show all comments at the end of each question
				 */
				if ($QUESTION_COMMENTS && isset($comments[$number]) && count($comments[$number])) {	// Show all comments at end of question
					echo	"<tr><td  colspan=\"4\">&nbsp;</td></tr>";
					for ($i=0; $i<count($comments[$number]); $i++) {
						if ($i) {
							echo "<tr><td />";
						} else {
							echo "<tr><td>Comments:</td>";
						}
						echo "	<td colspan=\"3\">".($i+1).") ".$comments[$number][$i]."</td></tr>";
					}
					echo	"	<tr><td  colspan=\"4\">&nbsp;</td></tr>";					
				}
			} else {	// No responses for this question
				echo	"<tr><td /><td colspan=\"3\"><hr></td></tr>";  	
			}
		}
		/**
		 * Use this code to Show comments for all questions at the end of the report
		 */
		if ($END_COMMENTS && count($comments)) {
			echo	"<tr><td  colspan=\"4\">&nbsp;</td></tr>";
			echo	"<tr><td colspan=\"2\"><h3>Question Comments:</h3></td><td colspan=\"2\" /></tr>";
			for ($i=1; $i<=$number; $i++) {
				if (isset($comments[$i]) && count($comments[$i])) {	
					for ($j=0; $j<count($comments[$i]); $j++) {
						if ($j) {
							echo "<tr><td />";
						} else {
							echo "<tr><td>Question $i:</td>";
						}
						echo "<td colspan=\"3\">".($j+1).") ".$comments[$i][$j]."</td></tr>";
					}
				}
			}
			echo	"	<tr><td  colspan=\"4\">&nbsp;</td></tr>";					
		}
		echo	"<tr><td /><td colspan=\"3\" align=\"right\">Form: $report[form_title] -- $report[form_description]</td></tr>";
		echo	"</table>";
		echo	"<hr><p />";
		echo	"</div>";		
	}
}
?>