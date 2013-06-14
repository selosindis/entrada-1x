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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
    
    if (isset($_POST["assessment_id"]) && ((int) $_POST["assessment_id"])) {
        $assessment_id = (int) $_POST["assessment_id"];
    }
    
    if (isset($_POST["course_id"]) && ((int) $_POST["course_id"])) {
        $course_id = (int) $_POST["course_id"];
    }
    
    if (isset($_POST["import_type"]) && (clean_input($_POST["import_type"], "alpha"))) {
        $import_type = clean_input($_POST["import_type"], "alpha");
    } else {
        $import_type = "all";
    }
    
	$url = ENTRADA_URL."/admin/gradebook/assessments?section=grade&id=".$course_id."&assessment_id=".$assessment_id;
	
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "grade", "id" => $COURSE_ID, "step" => false)), "title" => "Grading Assessment");
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("section" => "import-quiz", "id" => $COURSE_ID, "step" => false)), "title" => "Importing Quiz Results");
	
	echo "<h1>Import Quiz results into Gradebook Assessment</h1>";
		
	/*
	 *  fetch the quiz attached to the assessment
	 */
	$query = "SELECT * FROM `assessment_attached_quizzes`  AS a
                JOIN `attached_quizzes` AS b
                ON a.`aquiz_id` = b.`aquiz_id`
				WHERE a.`assessment_id` = ".$db->qstr($assessment_id);
	
	if ($attached_quizzes = $db->GetAll($query)) {
        if ($attached_quizzes) {
            $quiz_list = array();
            $quiz_ids_string = "";
            $questions_list = array();
            $question_ids_strings = array();
            foreach ($attached_quizzes as $aquiz) {
                $question_ids_strings[$aquiz["aquiz_id"]] = "";
                $quiz_ids_string .= ($quiz_ids_string ? ", " : "").$db->qstr($aquiz["aquiz_id"]);
                $quiz_list[$aquiz["aquiz_id"]] = $aquiz;
                
                $query = "SELECT b.* FROM `assessment_quiz_questions` AS a
                            JOIN `quiz_questions` AS b
                            ON a.`qquestion_id` = b.`qquestion_id`
                            JOIN `attached_quizzes` AS c
                            ON a.`aquiz_id` = c.`aquiz_id`
                            WHERE a.`assessment_id` = ".$db->qstr($assessment_id)."
                            AND c.`aquiz_id` = ".$db->qstr($aquiz["aquiz_id"]);
                $questions = $db->GetAll($query);
                if ($questions) {
                    foreach ($questions as $question) {
                        $question_ids_strings[$aquiz["aquiz_id"]] .= ($question_ids_strings[$aquiz["aquiz_id"]] ? ", " : "").$db->qstr($question["qquestion_id"]);
                        $questions_list[$question["qquestion_id"]."-".$aquiz["aquiz_id"]] = $question;
                    }
                } else {
                    $query = "SELECT a.* FROM `quiz_questions` AS a
                                JOIN `attached_quizzes` AS b
                                ON a.`quiz_id` = b.`quiz_id`
                                WHERE b.`aquiz_id` = ".$db->qstr($aquiz["aquiz_id"]);
                    $questions = $db->GetAll($query);
                    if ($questions) {
                        foreach ($questions as $question) {
                            $question_ids_strings[$aquiz["aquiz_id"]] .= ($question_ids_strings[$aquiz["aquiz_id"]] ? ", " : "").$db->qstr($question["qquestion_id"]);
                            $questions_list[$question["qquestion_id"]."-".$aquiz["aquiz_id"]] = $question;
                        }
                    }
                }
            }

            /*
             *  fetch the proxy_ids for the assessment
             */
            $query = "	SELECT a.`assessment_id`, a.`name`, GROUP_CONCAT(b.`proxy_id` SEPARATOR ',') AS `group_members`, a.`grade_threshold`
                        FROM `assessments` AS a
                        LEFT JOIN `group_members` AS b
                        ON a.`cohort` = b.`group_id`
                        WHERE `assessment_id` = ".$db->qstr($assessment_id);	

            if ($assessment = $db->GetRow($query)) {
                /*
                 *  fetch the existing assessment grades
                 */
               $query = "	SELECT `proxy_id`, `grade_id`, `value` 
                            FROM `assessment_grades` 
                            WHERE `assessment_id` = ".$db->qstr($assessment_id)." 
                            AND `proxy_id` IN (".$assessment["group_members"].")";
                $grades = $db->GetAssoc($query);
                
                $audience_members = explode(",", $assessment["group_members"]);
                foreach ($audience_members as $proxy_id) {
                    switch ($import_type) {
                        case "all" :
                        default :
                            $query = "SELECT b.`qquestion_id`, a.`aquiz_id`, c.`response_correct` 
                                        FROM `quiz_progress` AS a
                                        JOIN `quiz_progress_responses` AS b
                                        ON a.`qprogress_id` = b.`qprogress_id`
                                        JOIN `quiz_question_responses` AS c
                                        ON b.`qqresponse_id` = c.`qqresponse_id`
                                        WHERE a.`proxy_id` = ".$db->qstr($proxy_id)."
                                        AND (\n";
                            $first = true;
                            foreach ($question_ids_strings as $aquiz_id => $question_ids_string) {
                                $query .= (!$first ? "OR " : "")."(a.`aquiz_id` = ".$db->qstr($aquiz_id)." AND b.`qquestion_id` IN (".$question_ids_string."))\n";
                                $first = false;
                            }
                            $query .= " )";
                        break;
                        case "first" :
                            $query = "SELECT b.`qquestion_id`, a.`aquiz_id`, c.`response_correct`, a.`updated_date`, MIN(a.`updated_date`)
                                        FROM `quiz_progress` AS a
                                        JOIN `quiz_progress_responses` AS b
                                        ON a.`qprogress_id` = b.`qprogress_id`
                                        JOIN `quiz_question_responses` AS c
                                        ON b.`qqresponse_id` = c.`qqresponse_id`
                                        WHERE a.`proxy_id` = ".$db->qstr($proxy_id)."
                                        AND (\n";
                            $first = true;
                            foreach ($question_ids_strings as $aquiz_id => $question_ids_string) {
                                $query .= (!$first ? "OR " : "")."(a.`aquiz_id` = ".$db->qstr($aquiz_id)." AND b.`qquestion_id` IN (".$question_ids_string."))\n";
                                $first = false;
                            }
                            $query .= " )
                                        GROUP BY b.`qquestion_id`
                                        HAVING a.`updated_date` = MIN(a.`updated_date`)";
                        break;
                        case "last" :
                            $query = "SELECT b.`qquestion_id`, a.`aquiz_id`, c.`response_correct`, a.`updated_date`, MAX(a.`updated_date`)
                                        FROM `quiz_progress` AS a
                                        JOIN `quiz_progress_responses` AS b
                                        ON a.`qprogress_id` = b.`qprogress_id`
                                        JOIN `quiz_question_responses` AS c
                                        ON b.`qqresponse_id` = c.`qqresponse_id`
                                        WHERE a.`proxy_id` = ".$db->qstr($proxy_id)."
                                        AND (\n";
                            $first = true;
                            foreach ($question_ids_strings as $aquiz_id => $question_ids_string) {
                                $query .= (!$first ? "OR " : "")."(a.`aquiz_id` = ".$db->qstr($aquiz_id)." AND b.`qquestion_id` IN (".$question_ids_string."))\n";
                                $first = false;
                            }
                            $query .= " )
                                        GROUP BY b.`qquestion_id`
                                        HAVING a.`updated_date` = MAX(a.`updated_date`)";
                        break;
                        case "best" :
                            $query = "SELECT b.`qquestion_id`, a.`aquiz_id`, c.`response_correct`, a.`quiz_score`, MAX(a.`quiz_score`)
                                        FROM `quiz_progress` AS a
                                        JOIN `quiz_progress_responses` AS b
                                        ON a.`qprogress_id` = b.`qprogress_id`
                                        JOIN `quiz_question_responses` AS c
                                        ON b.`qqresponse_id` = c.`qqresponse_id`
                                        WHERE a.`proxy_id` = ".$db->qstr($proxy_id)."
                                        AND (\n";
                            $first = true;
                            foreach ($question_ids_strings as $aquiz_id => $question_ids_string) {
                                $query .= (!$first ? "OR " : "")."(a.`aquiz_id` = ".$db->qstr($aquiz_id)." AND b.`qquestion_id` IN (".$question_ids_string."))\n";
                                $first = false;
                            }
                            $query .= " )
                                        GROUP BY b.`qquestion_id`
                                        HAVING a.`quiz_score` = MAX(a.`quiz_score`)";
                        break;
                    }
                    $responses = $db->GetAll($query);
                    if ($responses) {
                        $total_value = 0;
                        $scored_value = 0;
                        foreach ($responses as $response) {
                            $total_value += $questions_list[$response["qquestion_id"]."-".$response["aquiz_id"]]["question_points"];
                            if ($response["response_correct"]) {
                                $scored_value += $questions_list[$response["qquestion_id"]."-".$response["aquiz_id"]]["question_points"];
                            }
                        }
                        $PROCESSED["value"]     = ($scored_value / $total_value) * 100;
                        $PROCESSED["assessment_id"] = (int) $assessment_id;
                        $PROCESSED["proxy_id"]		= (int) $proxy_id;
                        
                        echo $proxy_id.":".$PROCESSED["value"]."%\n<br/>";
                        
                        if (!@count($questions_list) || $total_value) {
                            if ($PROCESSED["value"] < $assessment["grade_threshold"]) {
                                $PROCESSED["threshold_notified"] = 0;
                            }

                            if (isset($grades[$proxy_id]) && $grades[$proxy_id]) {
                                $PROCESSED["grade_id"] = $grades[$proxy_id]["grade_id"];
                                $db->AutoExecute("assessment_grades",$PROCESSED,"UPDATE","`grade_id`=".$db->qstr($PROCESSED["grade_id"]));
                            } else {
                                $db->AutoExecute("assessment_grades",$PROCESSED,"INSERT");
                            }
                        }

                        unset($PROCESSED);
                    }
                }
                if (!$ERROR) {
                    add_success("Successfully imported results from the attached quiz questions into <strong>".$assessment["name"]."</strong>.");
                }
            } else {
                add_error("No students have been found in the cohort assigned to this assessment [<strong>".$assessment["name"]."</strong>].");
            }
        } else {
            add_error("No quizzes were found to be associated with this assessment [<strong>".$assessment["name"]."</strong>].");
        }
		
	} else {
		add_error("The assessment ".$assessment["name"]." does not have a quiz attached, results can not be imported.");
	}
	
	if ($ERROR) {
        add_error("You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$assessment["name"]."</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
		echo display_error();
	}
	if ($SUCCESS) {
        add_success("You will now be redirected to the <strong>Grade Assessment</strong> page for <strong>".$assessment["name"]."</strong>. This will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue now.");
		echo display_success();
	}
	
	$ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";
	
}