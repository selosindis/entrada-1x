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
 * This file displays the list of all quizzes available to the particular
 * individual who is accessing this file.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_GRADEBOOK"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
    if ($ASSESSMENT_ID) {
        $query = "SELECT * FROM `assessments` WHERE `assessment_id` = ".$db->qstr($ASSESSMENT_ID);
        $assessment = $db->GetRow($query);
        if ($assessment) {
            $BREADCRUMB[] = array("url" => ENTRADA_URL . "/admin/gradebook/assessment?" . replace_query(array("section" => "attach-quiz", "step" => false)), "title" => "Attach Quiz");

            switch ($STEP) {
                case 2 :
                    if ((isset($_GET["quiz_id"])) && ($quiz_id = clean_input($_GET["quiz_id"], array("int")))) {
                        $query = "SELECT a.*
                                    FROM `quizzes` AS a
                                    LEFT JOIN `quiz_contacts` AS b
                                    ON a.`quiz_id` = b.`quiz_id`
                                    LEFT JOIN `attached_quizzes` AS c
                                    ON a.`quiz_id` = c.`quiz_id`
                                    AND c.`content_type` = 'assessment'
                                    AND c.`content_id` = ".$db->qstr($ASSESSMENT_ID)."
                                    WHERE b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
                                    AND a.`quiz_id` = ".$db->qstr($quiz_id)."
                                    AND c.`aquiz_id` IS NULL
                                    GROUP BY a.`quiz_id`";
                        $quiz = $db->GetRow($query);
                        if ($quiz) {
                            $PROCESSED["quiz_title"]    = $quiz["quiz_title"];
                            $PROCESSED["quiz_id"]       = $quiz_id;
                        } else {
                            $ERROR++;
                            $ERRORSTR[] = "The <strong>Quiz</strong> you selected does not exist or is not enabled.";
                        }
                    } else {
                        $ERROR++;
                        $ERRORSTR[] = "Please select a <strong>Quiz</strong> to attach to the assessment.";
                    }

					if (!$ERROR) {
                        $PROCESSED["content_id"]	= (int) $ASSESSMENT_ID;
                        $PROCESSED["content_type"]  = "assessment";
						$PROCESSED["updated_date"]	= time();
						$PROCESSED["updated_by"]	= $ENTRADA_USER->getID();                        
						/**
						* Adding this quiz to the selected assessment.
						*/
						if ($db->AutoExecute("attached_quizzes", $PROCESSED, "INSERT")) {
							$url = ENTRADA_URL."/admin/gradebook/assessments?section=edit&id=".$assessment["course_id"]."&assessment_id=".$ASSESSMENT_ID;
							$SUCCESS++;
							$SUCCESSSTR[]	= "You have successfully attached <strong>".html_encode($quiz["quiz_title"])."</strong> to <strong>".$assessment["name"]."</strong>.";

							application_log("success", "Quiz [".$PROCESSED["quiz_id"]."] was successfully attached to assessment [".$ASSESSMENT_ID."].");

						} else {
							$url = ENTRADA_URL."/admin/gradebook/assessments?section=edit&id=".$assessment["course_id"]."&assessment_id=".$ASSESSMENT_ID;
							$ERROR++;
							$ERRORSTR[] = "There was a problem attaching this quiz to <strong>".html_encode($assessment["name"])."</strong>. The system administrator was informed of this error; please try again later.";

							application_log("error", "There was an error attaching quiz [".$PROCESSED["quiz_id"]."] to assessment [".$ASSESSMENT_ID."]. Database said: ".$db->ErrorMsg());
						}
                        if ($SUCCESS) {
							$url = ENTRADA_URL."/admin/gradebook/assessments?section=edit&id=".$assessment["course_id"]."&assessment_id=".$ASSESSMENT_ID;
                            $ONLOAD[] = "setTimeout('window.location=\\'".$url."\\'', 5000)";

                            $SUCCESSSTR[(count($SUCCESSSTR) - 1)] .= "<br /><br />You will now be redirected back to the assessment edit page; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";
                        } elseif ($ERROR) {
                            $STEP = 1;
                        }
                    } else {
                        $STEP = 1;
                    }
                break;
                case 1 :
                default :
                    /**
                    * Update requested column to sort by.
                    * Valid: date, teacher, title, phase
                    */
                   if (isset($_GET["sb"])) {
                       if (in_array(trim($_GET["sb"]), array("title", "questions", "status"))) {
                           $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"] = trim($_GET["sb"]);
                       }

                       $_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
                   } else {
                       if (!isset($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"])) {
                           $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"] = "status";
                       }
                   }

                   /**
                    * Update requested order to sort by.
                    * Valid: asc, desc
                    */
                   if (isset($_GET["so"])) {
                       $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

                       $_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
                   } else {
                       if (!isset($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"])) {
                           $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"] = "asc";
                       }
                   }

                   /**
                    * Update requsted number of rows per page.
                    * Valid: any integer really.
                    */
                   if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
                       $integer = (int) trim($_GET["pp"]);

                       if (($integer > 0) && ($integer <= 250)) {
                           $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"] = $integer;
                       }

                       $_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
                   } else {
                       if (!isset($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"])) {
                           $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"] = DEFAULT_ROWS_PER_PAGE;
                       }
                   }

                   /**
                    * Check if preferences need to be updated on the server at this point.
                    */
                   preferences_update("assessment-quiz", $PREFERENCES);

                   /**
                    * Provide the queries with the columns to order by.
                    */
                   switch ($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"]) {
                       case "questions" :
                       default :
                           $sort_by = "`question_total` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]);
                       break;
                       case "title" :
                           $sort_by = "a.`quiz_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]);
                       break;
                       case "status" :
                           $sort_by = "`quiz_status` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]);
                       break;
                   }

                   /**
                    * Get the total number of results using the generated queries above and calculate the total number
                    * of pages that are available based on the results per page preferences.
                    */

                   $query	= "	SELECT COUNT(*) AS `total_rows`
                               FROM `quizzes` AS a
                               LEFT JOIN `quiz_contacts` AS b
                               ON a.`quiz_id` = b.`quiz_id`
                               LEFT JOIN `attached_quizzes` AS c
                               ON a.`quiz_id` = c.`quiz_id`
                               AND c.`content_type` = 'assessment'
                               AND c.`content_id` = ".$db->qstr($ASSESSMENT_ID)."
                               WHERE b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
                               AND c.`aquiz_id` IS NULL";
                   $result = $db->GetRow($query);
                   if ($result) {
                       $total_rows	= $result["total_rows"];

                       if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) {
                           $total_pages = 1;
                       } elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) == 0) {
                           $total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]);
                       } else {
                           $total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) + 1;
                       }
                   } else {
                       $total_rows = 0;
                       $total_pages = 1;
                   }

                   /**
                    * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
                    */
                   if (isset($_GET["pv"])) {
                       $page_current = (int) trim($_GET["pv"]);

                       if (($page_current < 1) || ($page_current > $total_pages)) {
                           $page_current = 1;
                       }
                   } elseif (isset($_POST["pv"])) {
                       $page_current = (int) trim($_POST["pv"]);

                       if (($page_current < 1) || ($page_current > $total_pages)) {
                           $page_current = 1;
                       }
                   } else {
                       $page_current = 1;
                   }

                   $page_previous = (($page_current > 1) ? ($page_current - 1) : false);
                   $page_next = (($page_current < $total_pages) ? ($page_current + 1) : false);
                break;
            }
            
            switch ($STEP) {
                case 2 :
                    if ($SUCCESS) {
                        echo display_success();
                    }

                    if ($NOTICE) {
                        echo display_notice();
                    }

                    if ($ERROR) {
                        echo display_error();
                    }
                break;
                case 1 :
                default :
                    ?>
                    <div class="alert alert-info">Please select the quiz you would like to attach to this assessment:</div>
                    
                    <?php
                    if($ERROR) {
                        echo display_error();
                    }

                    if($NOTICE) {
                        echo display_notice();
                    }
                    ?>

                    <div style="float: right; padding-bottom: 10px;">


                        <?php
                        if ($total_pages > 1) {
                            echo "<form action=\"".ENTRADA_URL."/admin/gradebook/assessments\" method=\"get\" id=\"pageSelector\">\n";
                            foreach ($_GET as $name => $value) {
                                if ($name !== "step" && $name !== "pv") {
                                    echo "<input type=\"hidden\" name=\"".html_encode($name)."\" value=\"".html_encode($value)."\" />";
                                }
                            }
                            echo "<span style=\"width: 20px; vertical-align: middle; margin-right: 3px; text-align: left\">\n";
                            if ($page_previous) {
                                echo "<a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pv" => $page_previous))."\"><img src=\"".ENTRADA_URL."/images/record-previous-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Back to page ".$page_previous.".\" title=\"Back to page ".$page_previous.".\" style=\"vertical-align: middle\" /></a>\n";
                            } else {
                                echo "<img src=\"".ENTRADA_URL."/images/record-previous-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
                            }
                            echo "</span>";
                            echo "<span style=\"vertical-align: middle\">\n";
                            echo "<select name=\"pv\" onchange=\"$('pageSelector').submit();\"".(($total_pages <= 1) ? " disabled=\"disabled\"" : "").">\n";
                            for($i = 1; $i <= $total_pages; $i++) {
                                echo "<option value=\"".$i."\"".(($i == $page_current) ? " selected=\"selected\"" : "").">".(($i == $page_current) ? " Viewing" : "Jump To")." Page ".$i."</option>\n";
                            }
                            echo "</select>\n";
                            echo "</span>\n";
                            echo "<span style=\"width: 20px; vertical-align: middle; margin-left: 3px; text-align: right\">\n";
                            if ($page_current < $total_pages) {
                                echo "<a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pv" => $page_next))."\"><img src=\"".ENTRADA_URL."/images/record-next-on.gif\" border=\"0\" width=\"11\" height=\"11\" alt=\"Forward to page ".$page_next.".\" title=\"Forward to page ".$page_next.".\" style=\"vertical-align: middle\" /></a>";
                            } else {
                                echo "<img src=\"".ENTRADA_URL."/images/record-next-off.gif\" width=\"11\" height=\"11\" alt=\"\" title=\"\" style=\"vertical-align: middle\" />";
                            }
                            echo "</span>\n";
                            echo "</form>\n";
                        }
                        ?>
                    </div>
                    <div class="clear"></div>
                    <?php
                    $query	= "	SELECT a.*, COUNT(c.`quiz_id`) AS `question_total`, IF(a.`quiz_active` = '1', 'Active', 'Disabled') AS `quiz_status`
                                FROM `quizzes` AS a
                                LEFT JOIN `quiz_contacts` AS b
                                ON a.`quiz_id` = b.`quiz_id`
                                LEFT JOIN `quiz_questions` AS c
                                ON a.`quiz_id` = c.`quiz_id`
                                AND c.`question_active` = 1
                                LEFT JOIN `attached_quizzes` AS d
                                ON a.`quiz_id` = d.`quiz_id`
                                AND d.`content_type` = 'assessment'
                                AND d.`content_id` = ".$db->qstr($ASSESSMENT_ID)."
                                WHERE b.`proxy_id` = ".$db->qstr($ENTRADA_USER->getActiveId())."
                                AND d.`aquiz_id` IS NULL
                                GROUP BY a.`quiz_id`
                                ORDER BY %s LIMIT %s, %s";

                    /**
                     * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
                     */
                    $limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]);

                    $query		= sprintf($query, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]);
                    $results	= $db->GetAll($query);
                    if ($results) {
                        ?>
                        <form action="<?php echo ENTRADA_URL; ?>/admin/gradebook/assessments?<?php echo replace_query(array("step" => 2)); ?>" method="post">
                        <table class="tableList" cellspacing="0" summary="List of Quizzes">
                        <colgroup>
                            <col class="modified" />
                            <col class="title" />
                            <col class="general" />
                        </colgroup>
                        <thead>
                            <tr>
                                <td class="modified">&nbsp;</td>
                                <td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]) : ""); ?>"><?php echo admin_order_link("title", "Quiz Title"); ?></td>
                                <td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"] == "questions") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]) : ""); ?>"><?php echo admin_order_link("questions", "Quiz Questions"); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($results as $result) {
                                echo "<tr id=\"quiz-".$result["quiz_id"]."\">\n";
                                echo "	<td>&nbsp;</td>\n";
                                echo "	<td><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("quiz_id" => $result["quiz_id"], "step" => 2))."\">".html_encode($result["quiz_title"])."</a></td>\n";
                                echo "	<td>".html_encode($result["question_total"])."</td>\n";
                                echo "</tr>\n";
                            }
                            ?>
                        </tbody>
                        </table>
                        </form>
                        <?php
                        /**
                         * Sidebar item that will provide another method for sorting, ordering, etc.
                         */
                        $sidebar_html  = "Sort columns:\n";
                        $sidebar_html .= "<ul class=\"menu\">\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"]) == "title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("sb" => "title"))."\" title=\"Sort by Title\">by title</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"]) == "status") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("sb" => "status"))."\" title=\"Sort by Phase\">by status</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["sb"]) == "questions") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("sb" => "questions"))."\" title=\"Sort by Teacher\">by questions</a></li>\n";
                        $sidebar_html .= "</ul>\n";
                        $sidebar_html .= "Order columns:\n";
                        $sidebar_html .= "<ul class=\"menu\">\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
                        $sidebar_html .= "</ul>\n";
                        $sidebar_html .= "Rows per page:\n";
                        $sidebar_html .= "<ul class=\"menu\">\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
                        $sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER]["assessment-quiz"]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/gradebook/assessments?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
                        $sidebar_html .= "</ul>\n";

                        new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");
                    } else {
                        ?>
                        <div class="display-generic">
                            There are currently no available quizzes in the system which you can attach to this assessment. To gain access to a quiz for attaching to an assessment, an author of the quiz must grant you access by adding you as an additional author for the quiz.
                            <br /><br />
                            Alternatively, you may <strong><a href="<?php echo ENTRADA_URL . "/admin/quizzes?section=add&assessment_id=".$ASSESSMENT_ID; ?>">Create a New Quiz</a></strong> now to attach.
                        </div>
                        <?php
                    }
                break;
            }
        } else {
            add_error("The <strong>Assessment</strong> you selected does not exist or is not enabled.");
            echo display_error();
        }
    } else {
        add_error("A valid <strong>Assessment Identifier</strong> is required to select quizzes for attaching.");
        echo display_error();
    }
}