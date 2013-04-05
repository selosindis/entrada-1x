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
 * This API file returns an HTML table of the possible audience information
 * based on the selected course.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */
if (!defined("IN_GRADEBOOK")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("gradebook", "update", false)) {
	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] do not have access to this module [".$MODULE."]");
} else {
    
    if (isset($_GET["ajax"]) && $_GET["ajax"]) {
        $ajax = true;
    } else {
        $ajax = false;
    }
    
    if ($ajax) {
        /**
         * Clears all open buffers so we can return a plain response for the Javascript.
         */
        ob_clear_open_buffers();
        $PROCESSED = array();
    }
    if ($ASSESSMENT_ID) {
        $query = "SELECT * FROM `assessments` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
        $assessment = $db->GetRow($query);
        if ($assessment) {
            if (isset($_GET["quiz_id"]) && ($tmp_input = clean_input($_GET["quiz_id"], "int"))) {
                $PROCESSED["quiz_id"] = $tmp_input;
            }
            if (isset($PROCESSED["quiz_id"]) && $PROCESSED["quiz_id"]) {
                echo "<input type=\"hidden\" id=\"quiz_id\" value=\"".$PROCESSED["quiz_id"]."\" />";
                $temp_ids = array();
                $QUESTIONS = array();
                $QUESTIONS_LIST = array();
                if ($STEP == 2) {
                    $question_ids_string = "";
                    if(isset($_POST["question_ids"]) && @count($_POST["question_ids"])) {
                        foreach($_POST["question_ids"] as $question_id) {
                            $question_id = (int) trim($question_id);
                            if($question_id) {
                                $temp_ids[] = $question_id;
                            }
                        }
                        $query = "SELECT * FROM `quiz_questions`
                                    WHERE `quiz_id` = ".$db->qstr($PROCESSED["quiz_id"])."
                                    AND `questiontype_id` = 1";
                        $quiz_questions = $db->GetAll($query);
                        if ($quiz_questions) {
                            foreach ($quiz_questions as $quiz_question) {
                                if (array_search($quiz_question["qquestion_id"], $temp_ids) !== false) {
                                    $QUESTIONS[$quiz_question["qquestion_id"]] = $quiz_question;
                                }
                                $QUESTIONS_LIST[$quiz_question["qquestion_id"]] = $quiz_question;
                                $question_ids_string .= ($question_ids_string ? ", " : "").$db->qstr($quiz_question["qquestion_id"]);
                            }
                            if (count($QUESTIONS)) {
                                $added_questions = 0;

                                $query = "SELECT * FROM `assessment_quiz_questions` 
                                            WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID)."
                                            AND `qquestion_id` IN (".$question_ids_string.")";
                                $existing_questions = $db->GetAll($query);

                                $query = "DELETE FROM `assessment_quiz_questions` 
                                            WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID)."
                                            AND `qquestion_id` IN (".$question_ids_string.")";
                                $db->Execute($query);

                                foreach ($QUESTIONS as $question) {
                                    if (!$db->AutoExecute("assessment_quiz_questions", array("assessment_id" => $ASSESSMENT_ID, "qquestion_id" => $question["qquestion_id"]), "INSERT")) {
                                        application_log("error", "Unable to insert a new assessment_quiz_question record while updating an assessment. Database said: ".$db->ErrorMsg());
                                    } else {
                                        $added_questions++;
                                    }
                                }

                                if ($added_questions) {
                                    echo "<input type=\"hidden\" id=\"new_questions_count\" value=\"".$added_questions."\" />";
                                    $SUCCESS++;
                                    $SUCCESSSTR[] = "You have successfully updated the attached <strong>Quiz Questions</strong> for <strong>".$assessment["name"]."</strong>. There are now <strong>".html_encode($added_questions)."</strong> attached questions.";
                                    echo display_success();
                                    exit;
                                } else {
                                    foreach ($existing_questions as $existing_question) {
                                        if (!$db->AutoExecute("assessment_quiz_questions", array("assessment_id" => $ASSESSMENT_ID, "qquestion_id" => $existing_question["qquestion_id"]), "INSERT")) {
                                            application_log("error", "Unable to re-insert an assessment_quiz_question record while rolling back updates to an assessment. Database said: ".$db->ErrorMsg());
                                        }
                                    }
                                    add_error("There was an error while trying to attach the selected <strong>Quiz Questions</strong> for this assessment.<br /><br />The system administrator was informed of this error; please try again later.");
                                    echo display_error();
                                }
                            } else {
                                $ERROR++;
                                $ERRORSTR[] = "You must select at least 1 valid question to associate with this assessment.";

                                application_log("notice", "Assessment quiz question api page accessed without providing any question id's to attach while on 'step' 2.");
                            }
                        }
                    } else {
                        $ERROR++;
                        $ERRORSTR[] = "You must select at least 1 question to associate with this assessment by checking the checkbox to the left the question.";

                        application_log("notice", "Assessment quiz question api page accessed without providing any question id's to attach while on 'step' 2.");
                    }
                }

                //Display Questions List
                if (!count($QUESTIONS)) {
                    $query = "SELECT a.*, b.`assessment_id` FROM `quiz_questions` AS a
                                LEFT JOIN `assessment_quiz_questions` AS b
                                ON a.`qquestion_id` = b.`qquestion_id`
                                AND b.`assessment_id` = ".$db->qstr($ASSESSMENT_ID)."
                                WHERE a.`quiz_id` = ".$db->qstr($PROCESSED["quiz_id"])."
                                AND a.`questiontype_id` = 1";
                    $quiz_questions = $db->GetAll($query);
                    if ($quiz_questions) {
                        foreach ($quiz_questions as $quiz_question) {
                            if (isset($quiz_question["assessment_id"]) && $quiz_question["assessment_id"]) {
                                $QUESTIONS[$quiz_question["qquestion_id"]] = $quiz_question;
                            }
                            $QUESTIONS_LIST[$quiz_question["qquestion_id"]] = $quiz_question;
                        }
                        if (!count($QUESTIONS)) {
                            $QUESTIONS = $QUESTIONS_LIST;
                        }
                    }
                }
                
                if ($QUESTIONS_LIST) {
                    ?>
                    <h4 class="row-fluid">Quiz Questions</h2>
                    <br />
                    <div class="quiz-questions row-fluid" id="quiz-content-questions-holder">
                        <ol class="questions" id="quiz-questions-list" style="padding-left: 20px;">
                            <?php
                            foreach ($QUESTIONS_LIST as $question) {
                                echo "<li id=\"question_".$question["qquestion_id"]."\" class=\"question\">";
                                echo "<input onclick=\"submitQuizQuestions(".$PROCESSED["quiz_id"].")\" type=\"checkbox\" value=\"".$question["qquestion_id"]."\" name=\"question_ids[]\"".(array_key_exists($question["qquestion_id"], $QUESTIONS) || !count($QUESTIONS) ? " checked=\"checked\"" : "")." style=\"position: absolute; margin-left: -40px;\" />";
                                echo "		".clean_input($question["question_text"], "trim");
                                echo "</li>\n";
                            }
                            ?>
                        </ol>
                    </div>
                    <?php
                } else {
                    add_error("No valid questions were found associated with this quiz.");
                    echo display_error();
                }
            }
        }
    }
    if ($ajax) {
        exit;
    }
}