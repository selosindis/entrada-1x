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
 * This file is used to author and share quizzes with other folks who have
 * administrative permissions in the system.
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
} elseif (!$ENTRADA_ACL->amIAllowed('quizresult', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	if ($RECORD_ID) {
		$query			= "	SELECT a.*, b.`course_id`, b.`event_title`, d.`audience_type`, d.`audience_value` AS `event_grad_year`, e.`quiz_title` AS `default_quiz_title`, e.`quiz_description` AS `default_quiz_description`, f.`quiztype_code`, g.`organisation_id`
							FROM `event_quizzes` AS a
							LEFT JOIN `events` AS b
							ON b.`event_id` = a.`event_id`
							LEFT JOIN `event_audience` AS d
							ON d.`event_id` = a.`event_id`
							LEFT JOIN `quizzes` AS e
							ON e.`quiz_id` = a.`quiz_id`
							LEFT JOIN `quizzes_lu_quiztypes` AS f
							ON f.`quiztype_id` = a.`quiztype_id`
							LEFT JOIN `courses` AS g
							ON g.`course_id` = b.`course_id`
							WHERE a.`equiz_id` = ".$db->qstr($RECORD_ID)."
							AND g.`course_active` = '1'";
		$quiz_record	= $db->GetRow($query);
		if ($quiz_record) {
			if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($quiz_record["event_id"], $quiz_record["course_id"], $quiz_record["organisation_id"]), "update")) {
				application_log("error", "Someone attempted to view the results of an equiz_id [".$RECORD_ID."] that they were not entitled to view.");

				header("Location: ".ENTRADA_URL."/admin/events?section=content&id=".$quiz_record["event_id"]);
				exit;
			} else {
				$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/livepipe/progressbar.js?release=".APPLICATION_VERSION."\"></script>";

				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?section=content&id=".$quiz_record["event_id"], "title" => limit_chars($quiz_record["event_title"], 32));
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=edit&id=".$quiz_record["quiz_id"], "title" => limit_chars($quiz_record["quiz_title"], 32));
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/".$MODULE."?section=results&id=".$RECORD_ID, "title" => "Quiz Results");

				if ($quiz_record["audience_type"] == "grad_year") {
					$event_grad_year = $quiz_record["event_grad_year"];
				} else {
					$event_grad_year = 0;
				}

				$calculation_targets			= array();
				$calculation_targets["all"]		= "all quiz respondents";
				$calculation_targets["student"]	= "all students";
				$fyear = (date("Y", time()) + ((date("m", time()) < 7) ?  3 : 4));
				for ($year = $fyear; $year >= ($fyear - 3); $year--) {
					$calculation_targets["student:".$year]	= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;class of ".$year;
				}
				if (($event_grad_year) && (!array_key_exists("student:".$event_grad_year, $calculation_targets))) {
					$calculation_targets["student:".$event_grad_year] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;class of ".$event_grad_year;
				}
				$calculation_targets["resident"]	= "all residents";
				$calculation_targets["faculty"]		= "all faculty";
				$calculation_targets["staff"]		= "all staff";

				/**
				 * Update calculation target.
				 * Valid: any key from the $calculation_targets array.
				 */
				if (isset($_GET["target"])) {
					if (array_key_exists(trim($_GET["target"]), $calculation_targets)) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["target"] = trim($_GET["target"]);
					}

					$_SERVER["QUERY_STRING"] = replace_query(array("target" => false));
				} else {
					if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["target"])) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["target"] = (($event_grad_year) ? "student:".$event_grad_year : "all");
					}
				}

				$pieces = explode(":", $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["target"]);
				$target_group	= false;
				$target_role	= false;
				if (isset($pieces[0])) {
					$target_group	= clean_input($pieces[0], "alphanumeric");
				}

				if (isset($pieces[1])) {
					$target_role	= clean_input($pieces[1], "alphanumeric");
				}

				/**
				 * Update calculation attempts.
				 * Valid: first, last, all
				 */
				if (isset($_GET["attempt"])) {
					if (in_array(trim($_GET["attempt"]), array("first", "last", "all"))) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"] = trim($_GET["attempt"]);
					}

					$_SERVER["QUERY_STRING"] = replace_query(array("attempt" => false));
				} else {
					if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"])) {
						$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"] = "all";
					}
				}

				echo "<div class=\"content-small\">";
				if ($quiz_record["course_id"]) {
					$curriculum_path = curriculum_hierarchy($quiz_record["course_id"]);

					if ((is_array($curriculum_path)) && (count($curriculum_path))) {
						echo implode(" &gt; ", $curriculum_path);
					}
				} else {
					echo "No Associated Course";
				}
				echo " &gt; ".html_encode($quiz_record["event_title"]);
				echo "</div>\n";
				echo "<h1 class=\"event-title\">".html_encode($quiz_record["quiz_title"])."</h1>\n";

				/**
				 * Check to make sure people have completed the quiz before trying to display
				 * results of the quiz.
				 */
				if ($quiz_record["accesses"] > 0) {
					$questions		= array();
					$respondents	= array();
					$attempts		= array();
					$total_attempts	= 0;

					$query		= "	SELECT a.*
									FROM `quiz_questions` AS a
									WHERE a.`quiz_id` = ".$db->qstr($quiz_record["quiz_id"])."
									ORDER BY a.`question_order` ASC";
					$results	= $db->GetAll($query);
					if ($results) {
						foreach ($results as $question) {
							$qquestion_id = $question["qquestion_id"];

							$questions[$qquestion_id]["question"]	= $question;
							$questions[$qquestion_id]["responses"]	= array();

							$query		= "	SELECT a.*
											FROM `quiz_question_responses` AS a
											WHERE a.`qquestion_id` = ".$db->qstr($question["qquestion_id"])."
											ORDER BY a.`response_order` ASC";
							$responses	= $db->GetAll($query);
							if ($responses) {
								foreach ($responses as $response) {
									$questions[$qquestion_id]["responses"][$response["qqresponse_id"]] = $response;
									$questions[$qquestion_id]["responses"][$response["qqresponse_id"]]["response_selected"] = 0;
								}
							}
						}
					}

					if (($target_group == "student") && ($target_role)) {
						$query			= "	SELECT a.`id` AS `proxy_id`, a.`number`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, b.`group`
											FROM `".AUTH_DATABASE."`.`user_data` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
											ON b.`user_id` = a.`id`
											WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
											AND b.`group` = 'student'
											".(($target_role) ? " AND b.`role` = ".$db->qstr($target_role) : "")."
											ORDER BY b.`group` ASC, b.`role` ASC, `fullname` ASC";
						$respondents	= $db->GetAll($query);
					} else {
						$query			= "	SELECT a.`proxy_id`, b.`number`, CONCAT_WS(', ', b.`lastname`, b.`firstname`) AS `fullname`, c.`group`
											FROM `event_quiz_progress` AS a
											LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
											ON b.`id` = a.`proxy_id`
											LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
											ON c.`user_id` = a.`proxy_id`
											WHERE a.`equiz_id` = ".$db->qstr($RECORD_ID)."
											AND a.`progress_value` = 'complete'
											".((($target_group != "all") && ($target_group)) ? " AND c.`group` = ".$db->qstr($target_group) : "")."
											".((($target_group != "all") && ($target_role)) ? " AND c.`role` = ".$db->qstr($target_role) : "")."
											GROUP BY a.`proxy_id`
											ORDER BY c.`group` ASC, c.`role` ASC, `fullname` ASC";
						$respondents	= $db->GetAll($query);
					}

					if ($respondents) {
						foreach ($respondents as $respondent) {
							$query		= "	SELECT a.*
											FROM `event_quiz_progress` AS a
											WHERE a.`equiz_id` = ".$db->qstr($RECORD_ID)."
											AND a.`proxy_id` = ".$db->qstr($respondent["proxy_id"])."
											AND a.`progress_value` = 'complete'";
							switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"]) {
								case "last" :
									$query .= "	ORDER BY a.`updated_date` DESC
												LIMIT 0, 1";
								break;
								case "all" :
									$query .= "	ORDER BY a.`updated_date` ASC";
								break;
								case "first" :
								default :
									$query .= "	ORDER BY a.`updated_date` ASC
												LIMIT 0, 1";
								break;
							}
							$results	= $db->GetAll($query);
							if ($results) {
								$total_attempts += count($results);

								foreach ($results as $result) {
									$attempts[$respondent["proxy_id"]][] = $result;

									$query		= "	SELECT a.*
													FROM `event_quiz_responses` AS a
													WHERE a.`eqprogress_id` = ".$db->qstr($result["eqprogress_id"]);
									$responses = $db->GetAll($query);
									if ($responses) {
										foreach ($responses as $response) {
											$questions[$response["qquestion_id"]]["responses"][$response["qqresponse_id"]]["response_selected"]++;
										}
									}
								}
							} else {
								$attempts[$respondent["proxy_id"]] = 0;
							}

						}
					}

					if ($total_attempts) {
						if ((isset($_GET["download"])) && (in_array($_GET["download"], array("csv"))) && (count($respondents))) {
							ob_start();
							echo '"Number","Fullname","Completed","Score","Out Of","Percent"', "\n";

							$quiz_value	= 0;
							foreach ($respondents as $respondent) {
								$quiz_score	= 0;
								$proxy_id	= $respondent["proxy_id"];

								if ((isset($attempts[$proxy_id])) && ($attempts[$proxy_id]) && (is_array($attempts[$proxy_id]))) {
									foreach ($attempts[$proxy_id] as $attempt) {
										$quiz_score	= $attempt["quiz_score"];
										$quiz_value = $attempt["quiz_value"];

										$cols	= array();
										$cols[]	= (($respondent["group"] == "student") ? $respondent["number"] : 0);
										$cols[]	= $respondent["fullname"];
										$cols[] = (((int) $attempt["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $attempt["updated_date"]) : "Unknown");
										$cols[]	= $quiz_score;
										$cols[]	= $quiz_value;
										$cols[]	= number_format(((round(($quiz_score / $quiz_value), 3)) * 100), 1)."%";
									}
								} else {
									$cols	= array();
									$cols[]	= $respondent["number"];
									$cols[]	= $respondent["fullname"];
									$cols[] = "Not Completed";
									$cols[]	= "0";
									$cols[]	= $quiz_value;
									$cols[]	= "0%";
								}

								echo '"'.implode('","', $cols).'"', "\n";
							}
							$contents = ob_get_contents();

							ob_clear_open_buffers();
							
							header("Pragma: public");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Content-Type: application/force-download");
							header("Content-Type: application/octet-stream");
							header("Content-Type: text/csv");
							header("Content-Disposition: attachment; filename=\"".date("Y-m-d")."_".useable_filename($quiz_record["event_title"]."_".$quiz_record["quiz_title"]).".csv\"");
							header("Content-Length: ".strlen($contents));
							header("Content-Transfer-Encoding: binary\n");

							echo $contents;
							exit;
						}
						?>
						<a name="question-breakdown-section"></a>
						<h2 title="Question Breakdown Section">Quiz Results by Question Breakdown</h2>
						<div id="question-breakdown-section">
							<div class="content-small">Based on <strong><?php echo $total_attempts; ?></strong> response<?php echo (($total_attempts != 1) ? "s" : ""); ?>.</div>
							<table class="quizResults">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 3%" />
								<col style="width: 43%" />
								<col style="width: 35%" />
								<col style="width: 8%" />
								<col style="width: 8%" />
							</colgroup>
							<tbody>
								<tr>
									<td class="borderless" colspan="4">&nbsp;</td>
									<td class="borderless left bold">Percent</td>
									<td class="borderless center bold">Count</td>
								</tr>

								<?php
								$response_count = 0;
								foreach ($questions as $qquestion_id => $question) {
									$response_count++;

									echo "<tr>\n";
									echo "	<td>".$response_count.")</td>\n";
									echo "	<td colspan=\"5\">".clean_input($question["question"]["question_text"], "allowedtags")."</td>";
									echo "</tr>";

									$response_correct = 0;

									foreach ($question["responses"] as $qqresponse_id => $response) {

										if ($response["response_correct"] == 1) {
											$response_correct += $response["response_selected"];
										}

										$percent = number_format(((round(($response["response_selected"] / $total_attempts), 3)) * 100), 1);

										echo "<tr>\n";
										echo "	<td>&nbsp;</td>\n";
										echo "	<td><img src=\"".ENTRADA_URL."/images/question-".((($response["response_correct"] == 1)) ? "correct" : "incorrect").".gif\" width=\"16\" height=\"16\" /></td>";
										echo "	<td>".clean_input($response["response_text"], (($response["response_is_html"] == 1) ? "allowedtags" : "encode"))."</td>";
										echo "	<td>\n";
										echo "		<div id=\"response-".$qqresponse_id."\" class=\"stats-container".(($response["response_correct"] == 1) ? " correct" : "")."\"></div>\n";
										echo "		<script type=\"text/javascript\">\n";
										echo "			new Control.ProgressBar('response-".$qqresponse_id."').setProgress('".(int) $percent."');\n";
										echo "		</script>\n";
										echo "	</td>\n";
										echo "	<td class=\"left\">".$percent."%</td>";
										echo "	<td class=\"center\">".$response["response_selected"]."</td>";
										echo "</tr>";
									}
									echo "<tr>\n";
									echo "	<td colspan=\"3\" class=\"borderless\">&nbsp;</td>\n";
									echo "	<td colspan=\"3\" class=\"borderless\" style=\"padding-bottom: 10px\">\n";
									echo "		<span class=\"content-small\">".number_format(((round(($response_correct / $total_attempts), 3)) * 100), 1)."% Responded Correctly</span>\n";
									echo "	</td>";
									echo "</tr>";
								}
								?>
							</tbody>
							</table>
						</div>

						<a name="quiz-respondent-section"></a>
						<h2 title="Quiz Respondent Section">Quiz Results by Respondent</h2>
						<div id="quiz-respondent-section">
							<table class="quizResults">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 13%" />
								<col style="width: 35%" />
								<col style="width: 23%" />
								<col style="width: 8%" />
								<col style="width: 2%" />
								<col style="width: 8%" />
								<col style="width: 8%" />
							</colgroup>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="6" style="padding-top: 10px">
										<button onclick="window.location='<?php echo ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("download" => "csv")); ?>'">Download CSV</button>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td class="borderless">&nbsp;</td>
									<td class="borderless bold">Number</td>
									<td class="borderless bold">Fullname</td>
									<td class="borderless bold">Completed</td>
									<td class="borderless right bold">Score</td>
									<td class="borderless center bold">/</td>
									<td class="borderless left bold">Out Of</td>
									<td class="borderless right bold">Percent</td>
								</tr>

								<?php
								$quiz_value	= 0;
								foreach ($respondents as $respondent) {
									$quiz_score	= 0;
									$proxy_id	= $respondent["proxy_id"];

									if ((isset($attempts[$proxy_id])) && ($attempts[$proxy_id]) && (is_array($attempts[$proxy_id]))) {
										foreach ($attempts[$proxy_id] as $attempt) {
											$quiz_score		= $attempt["quiz_score"];
											$quiz_value		= $attempt["quiz_value"];
											$quiz_percent	= number_format(((round(($quiz_score / $quiz_value), 3)) * 100), 1);

											echo "<tr>\n";
											echo "	<td><img src=\"".ENTRADA_URL."/images/question-".((($quiz_percent > 60)) ? "correct" : "incorrect").".gif\" width=\"16\" height=\"16\" /></td>";
											echo "	<td><a href=\"".ENTRADA_URL."/quizzes?section=results&amp;id=".$attempt["eqprogress_id"]."\">".html_encode((($respondent["group"] == "student") ? $respondent["number"] : 0))."</a></td>\n";
											echo "	<td><a href=\"".ENTRADA_URL."/quizzes?section=results&amp;id=".$attempt["eqprogress_id"]."\">".html_encode($respondent["fullname"])."</a></td>\n";
											echo "	<td><a href=\"".ENTRADA_URL."/quizzes?section=results&amp;id=".$attempt["eqprogress_id"]."\">".(((int) $attempt["updated_date"]) ? date(DEFAULT_DATE_FORMAT, $attempt["updated_date"]) : "Unknown")."</a></td>\n";
											echo "	<td class=\"right\">".$quiz_score."</td>\n";
											echo "	<td class=\"center\">/</td>\n";
											echo "	<td class=\"left\">".$quiz_value."</td>\n";
											echo "	<td class=\"right\">".$quiz_percent."%</td>\n";
											echo "</tr>";
										}
									} else {
										echo "<tr>\n";
										echo "	<td><img src=\"".ENTRADA_URL."/images/question-incorrect.gif\" width=\"16\" height=\"16\" /></td>";
										echo "	<td>".html_encode((($respondent["group"] == "student") ? $respondent["number"] : 0))."</td>\n";
										echo "	<td>".html_encode($respondent["fullname"])."</td>\n";
										echo "	<td>Not Completed</td>\n";
										echo "	<td class=\"right\">0</td>\n";
										echo "	<td class=\"center\">/</td>\n";
										echo "	<td class=\"left\">".$quiz_value."</td>\n";
										echo "	<td class=\"right\">0%</td>\n";
										echo "</tr>";
									}
								}
								?>
							</tbody>
							</table>
						</div>
						<?php
					} else {
						?>
						<div class="display-notice">
							<h3>No Completed Quizzes</h3>
							There have been no quizzes completed by
							<?php
							switch($target_group) {
								case "faculty" :
									echo "<strong>faculty members</strong>";
								break;
								case "staff" :
									echo "<strong>staff members</strong>";
								break;
								case "student" :
									echo "<strong>students</strong>".(($target_role) ? " in the <strong>class of ".$target_role."</strong>" : "");
								break;
								case "all" :
								default :
									echo "anyone";
								break;
							}
							?>
							at this point.
							<br /><br />
							Try changing the group that results are calculated for in the <strong>Result Calculation</strong> menu.
						</div>
						<?php
					}

					/**
					 * Sidebar item that will provide a method for choosing which results to display.
					 */
					$sidebar_html  = "Calculate results for:\n";
					$sidebar_html .= "<ul class=\"menu\">\n";
					if (is_array($calculation_targets)) {
						foreach ($calculation_targets as $key => $target_name) {
							$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["target"]) == $key) ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("target" => $key))."\" title=\"".trim(html_decode($target_name))."\">".$target_name."</a></li>\n";
						}
					}
					$sidebar_html .= "</ul>\n";
					$sidebar_html .= "Results based on:\n";
					$sidebar_html .= "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"]) == "first") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("attempt" => "first"))."\" title=\"The First Attempt\">only the first attempt</a></li>\n";
					$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"]) == "last") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("attempt" => "last"))."\" title=\"The Last Attempt\">only the last attempt</a></li>\n";
					$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempt"]) == "all") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("attempt" => "all"))."\" title=\"All Attempts\">all attempts</a></li>\n";
					$sidebar_html .= "</ul>\n";

					new_sidebar_item("Result Calculation", $sidebar_html, "sort-results", "open");

					/**
					 * Sidebar item that will provide the links to the different sections within this page.
					 */
					$sidebar_html  = "<ul class=\"menu\">\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#question-breakdown-section\" onclick=\"$('question-breakdown-section').scrollTo(); return false;\" title=\"Results by Question Breakdown\">Results by Question</a></li>\n";
					$sidebar_html .= "	<li class=\"link\"><a href=\"#quiz-respondent-section\" onclick=\"$('quiz-respondent-section').scrollTo(); return false;\" title=\"Results by Respondent\">Results by Respondent</a></li>\n";
					$sidebar_html .= "</ul>\n";

					new_sidebar_item("Page Anchors", $sidebar_html, "page-anchors", "open");

				} else {
					$NOTICE++;
					$NOTICESTR[] = "There have been no completed attempts of this quiz to date. Please check back again later.";

					echo display_notice();
				}
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to view the results of a quiz, you must provide a quiz identifier.";

			echo display_error();

			application_log("notice", "Failed to provide a valid quiz identifer [".$RECORD_ID."] when attempting to view quiz results.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to view the results of a quiz, you must provide a quiz identifier.";

		echo display_error();

		application_log("notice", "Failed to provide a quiz identifier to view results for.");
	}
}