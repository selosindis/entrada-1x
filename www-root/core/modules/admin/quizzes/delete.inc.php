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
 * This file is used by quiz authors to disable a particular quiz.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_QUIZZES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("quiz", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
    $BREADCRUMB[] = array("title" => "Delete Quizzes");

    $delete_quizzes = array();
    $quiz_ids = array();
    ?>
    <h1>Delete Quizzes</h1>
    <?php
    /**
     * Check for multiple items being deleted (usually from the Quizzes index file).
     */
    if (isset($_POST["delete"]) && is_array($_POST["delete"]) && !empty($_POST["delete"])) {
        foreach ($_POST["delete"] as $quiz_id) {
            $quiz_id = (int) $quiz_id;
            if ($quiz_id) {
                $quiz_ids[] = $quiz_id;
            }
        }

        if ($quiz_ids) {
            $query = "	SELECT a.`quiz_id`, a.`quiz_title`, a.`updated_date`, CONCAT(b.`firstname`, ' ', b.`lastname`) AS author, COUNT(DISTINCT c.`qquestion_id`) AS `question_total`
                        FROM `quizzes` AS a
						JOIN `".AUTH_DATABASE."`.`user_data` AS b
						ON a.`created_by` = b.id
						LEFT JOIN `quiz_questions` AS c
						ON a.`quiz_id` = c.`quiz_id`
                        WHERE a.`quiz_id` IN (".implode(", ", $quiz_ids).")
                        AND a.`quiz_active` = 1
                        ORDER BY a.`quiz_title` ASC";
            $results = $db->GetAll($query);
            if ($results) {
                foreach ($results as $result) {
                    if ($ENTRADA_ACL->amIAllowed(new QuizResource($result["quiz_id"]), "update")) {
                        $delete_quizzes[$result["quiz_id"]] = $result;
                    }
                }
            }
        }
    }

    if (!empty($delete_quizzes)) {
        $total_quizzes = count($delete_quizzes);

        if (isset($_POST["confirmed"]) && $_POST["confirmed"]) {
            $quiz_ids = array_keys($delete_quizzes);

            if ($db->Execute("UPDATE `quizzes` SET `quiz_active` = 0 WHERE `quiz_id` IN (".implode(", ", $quiz_ids).")")) {
				$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

                add_success("You have successfully deleted ".($total_quizzes != 1 ? "these quizzes" : "this quiz").".<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

				echo display_success();

				application_log("success", "Successfully deleted quiz_ids [".implode(", ", $quiz_ids)."].");
            } else {
				$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

                add_error("We were unable to delete the selected quiz".($total_quizzes != 1 ? "zes" : "")." at this time, please try again later.<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

                echo display_error();

                application_log("error", "Unable to deactivate quiz_ids [".implode(", ", $quiz_ids)."]. Database said: ".$db->ErrorMsg());
            }
        } else {
            ?>
            <div class="alert alert-block alert-danger">
                <strong>Warning!</strong> Do you really wish to delete the quiz<?php echo ($total_quizzes != 1 ? "zes" : ""); ?> below? If you proceed with this action the selected quiz<?php echo ($total_quizzes != 1 ? "zes" : ""); ?> will no longer be available to learners.
            </div>

            <form action="<?php echo ENTRADA_RELATIVE; ?>/admin/<?php echo $MODULE; ?>?section=delete" method="post">
                <input type="hidden" name="confirmed" value="1" />
                <table class="table table-striped table-bordered" summary="List of Quizzes Pending Delete">
                    <thead>
                        <tr>
							<th width="5%">&nbsp;</th>
							<th width="30%">Quiz Title</th>
							<th width="25%">Author</th>
							<th width="15%">Questions</th>
							<th width="25%">Last Updated</th>
						</tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($delete_quizzes as $quiz) {
                            echo "<tr>\n";
                            echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"delete[]\" value=\"".(int) $quiz["quiz_id"]."\" checked=\"checked\" /></td>\n";
                            echo "	<td class=\"title\"><a href=\"".ENTRADA_RELATIVE."/admin/".$MODULE."?section=edit&amp;id=".(int) $quiz["quiz_id"]."\">".html_encode($quiz["quiz_title"])."</a></td>\n";
							echo "	<td class=\"author\"><a href=\"".ENTRADA_RELATIVE."/admin/".$MODULE."?section=edit&amp;id=".(int) $quiz["quiz_id"]."\">".html_encode($quiz["author"])."</a></td>\n";
							echo "	<td class=\"questions\"><a href=\"".ENTRADA_RELATIVE."/admin/".$MODULE."?section=edit&amp;id=".(int) $quiz["quiz_id"]."\">".$quiz["question_total"]."</a></td>\n";
                            echo "	<td class=\"updated\"><a href=\"".ENTRADA_RELATIVE."/admin/".$MODULE."?section=edit&amp;id=".(int) $quiz["quiz_id"]."\">".date("Y-m-d g:ia", $quiz["updated_date"])."</a></td>\n";
                            echo "</tr>\n";
                        }
                        ?>
                    </tbody>
                </table>
				<div class="row-fluid">
					<a href="<?php echo ENTRADA_RELATIVE."/admin/".$MODULE; ?>" class="btn">Cancel</a>
                    <input type="submit" class="btn btn-danger pull-right" value="Confirm Delete" />
				</div>
            </form>
            <?php
        }
    } elseif ($RECORD_ID) {
		$query = "SELECT a.*
                    FROM `quizzes` AS a
                    WHERE a.`quiz_id` = ".$db->qstr($RECORD_ID)."
                    AND a.`quiz_active` = '1'";
		$quiz_record = $db->GetRow($query);
		if ($quiz_record && $ENTRADA_ACL->amIAllowed(new QuizResource($quiz_record["quiz_id"]), "update")) {
			if ($db->AutoExecute("quizzes", array("quiz_active" => 0), "UPDATE", "`quiz_id` = ".$RECORD_ID)) {
				$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

                add_success("You have successfully deleted this quiz.<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

				echo display_success();

				application_log("success", "Successfully deleted quiz_id [".$RECORD_ID."].");
			} else {
				$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

                add_error("We were unable to delete this quiz at this time, please try again later.<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

                echo display_error();

                application_log("error", "Unable to deactivate quiz_id [".$RECORD_ID."]. Database said: ".$db->ErrorMsg());
            }
		} else {
			$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";

			add_error("In order to delete a quiz you must provide a quiz identifier.<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

			echo display_error();

			application_log("notice", "Failed to provide a valid quiz identifer [".$RECORD_ID."] when attempting to delete a quiz.");
		}
	} else {
		$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 5000)";
		add_error("In order to delete a quiz you must provide a quiz identifier.<br /><br />You will now be redirected back to the quiz index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".ENTRADA_URL."/admin/".$MODULE."\" style=\"font-weight: bold\">click here</a> to continue.");

		echo display_error();

		application_log("notice", "Failed to provide a quiz identifier when attempting to delete a quiz.");
	}
}