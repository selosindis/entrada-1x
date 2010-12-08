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
 * This file is used to review the progress of evaluations.
 *
 * @author Organisation: University of Calgary
 * @author Unit: School of Medicine
 * @author Developer:  Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
if((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
}

if ($MAILING_LISTS["active"]) {
	require_once("Entrada/mail-list/mail-list.class.php");
}

$EVALUATION_ID		= 0;
$PREFERENCES		= preferences_load($MODULE);
$PROXY_IDS			= array();

/**
 * 
 */
if((isset($_GET["evaluation"])) && ((int) trim($_GET["evaluation"]))) {
	$EVALUATION_ID	= (int) trim($_GET["evaluation"]);
} elseif((isset($_POST["evaluation_id"])) && ((int) trim($_POST["evaluation_id"]))) {
	$EVALUATION_ID	= (int) trim($_POST["evaluation_id"]);
} elseif((isset($_POST["evaluation"])) && ((int) trim($_POST["evaluation"]))) {
	$EVALUATION_ID	= (int) trim($_POST["evaluation"]);
}


if((isset($_GET["type"])) && ($tmp_action_type = clean_input(trim($_GET["type"]), "alphanumeric"))) {
	$ACTION_TYPE	= $tmp_action_type;
} elseif((isset($_POST["type"])) && ($tmp_action_type = clean_input(trim($_POST["type"]), "alphanumeric"))) {
	$ACTION_TYPE	= $tmp_action_type;
}
unset($tmp_action_type);


/**
 * Ensure that the selected evaluation is in the system.
 */
if($EVALUATION_ID) {
	$query				= "SELECT * FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
	$evaluation_details	= $db->GetRow($query);
	if($evaluation_details) {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
		$HEAD[]	= "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
		$HEAD[]	= "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
                $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/eventtypes_list.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";
                $HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/AutoCompleteList.js?release=".html_encode(APPLICATION_VERSION)."\"></script>";

		$HEAD[]	= "<link href=\"".ENTRADA_URL."/css/tree.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
		$HEAD[]	= "<style type=\"text/css\">.dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";

		$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $EVALUATION_ID)), "title" => "Show Progress");
		
                // Display Content
                switch($STEP) {
                        case 3 :

                                break;
                        case 2 :
                                $ONLOAD[]		= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$EVALUATION_ID."\\'', 5000)";

                                if($SUCCESS) {
                                        echo display_success();
                                }
                                if($NOTICE) {
                                        echo display_notice();
                                }
                                break;
                        case 1 :
                        default :
                        /**
                         * Update requested sort column.
                         * Valid: date, title
                         */
                                if(isset($_GET["sb"])) {
                                        if(@in_array(trim($_GET["sb"]), array("date", "name", "type"))) {
                                                $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = trim($_GET["sb"]);
                                        }

                                        $_SERVER["QUERY_STRING"]	= replace_query(array("sb" => false));
                                } else {
                                        if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
                                                $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "date";
                                        }
                                }

                                /**
                                 * Update requested order to sort by.
                                 * Valid: asc, desc
                                 */
                                if(isset($_GET["so"])) {
                                        $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");

                                        $_SERVER["QUERY_STRING"]	= replace_query(array("so" => false));
                                } else {
                                        if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
                                                $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
                                        }
                                }

                                /**
                                 * Update requsted number of rows per page.
                                 * Valid: any integer really.
                                 */
                                if((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
                                        $integer = (int) trim($_GET["pp"]);

                                        if(($integer > 0) && ($integer <= 250)) {
                                                $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
                                        }

                                        $_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
                                } else {
                                        if(!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
                                                $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = 15;
                                        }
                                }

                                if($NOTICE) {
                                        echo display_notice();
                                }
                                if($ERROR) {
                                        echo display_error();
                                }

                                    if ($ENTRADA_ACL->amIAllowed(new EventResource($evaluation_details["evaluation_id"]), 'update')) {
                                    }
                                            echo "<div class=\"no-printing\">\n";
                                            echo "	<div style=\"float: right; margin-top: 8px\">\n";
                                            echo "		<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\"><img src=\"".ENTRADA_URL."/images/event-details.gif\" width=\"16\" height=\"16\" alt=\"Edit evaluation details\" title=\"Edit evaluation details\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\" style=\"font-size: 10px; margin-right: 8px\">Edit evaluation details</a>\n";
                                            echo "	</div>\n";
                                            echo "</div>\n";

                                    echo "<h1 class=\"evaluation-title\">".html_encode($evaluation_details["evaluation_title"])."</h1>\n";
                                ?>
        <div class="tab-pane" id="progress_div">
		<h2 style="margin-top: 0px">Progress</h2>
                            <?php
                            /**
                             * Get the total number of results using the generated queries above and calculate the total number
                             * of pages that are available based on the results per page preferences.
                             */
                            $query	= "SELECT COUNT(*) AS `total_rows` FROM `evaluation_progress` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
                            $result	= $db->GetRow($query);
                            if($result) {
                                    $TOTAL_ROWS	= $result["total_rows"];

                                    if($TOTAL_ROWS <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
                                            $TOTAL_PAGES = 1;
                                    } elseif (($TOTAL_ROWS % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
                                            $TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
                                    } else {
                                            $TOTAL_PAGES = (int) ($TOTAL_ROWS / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
                                    }

                                    if(isset($_GET["mpv"])) {
                                            $PAGE_CURRENT = (int) trim($_GET["mpv"]);

                                            if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
                                                    $PAGE_CURRENT = 1;
                                            }
                                    } else {
                                            $PAGE_CURRENT = 1;
                                    }

                                    if($TOTAL_PAGES > 1) {
                                            $member_pagination = new Pagination($PAGE_CURRENT, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"], $TOTAL_ROWS, ENTRADA_URL."/admin/evaluations", replace_query(), "mpv");
                                    } else {
                                            $member_pagination = false;
                                    }
                            } else {
                                    $TOTAL_ROWS		= 0;
                                    $TOTAL_PAGES	= 1;
                            }
                            if (!isset($PAGE_CURRENT) || !$PAGE_CURRENT) {
                                    if(isset($_GET["mpv"])) {
                                            $PAGE_CURRENT = (int) trim($_GET["mpv"]);

                                            if(($PAGE_CURRENT < 1) || ($PAGE_CURRENT > $TOTAL_PAGES)) {
                                                    $PAGE_CURRENT = 1;
                                            }
                                    } else {
                                            $PAGE_CURRENT = 1;
                                    }
                            }

                            $PAGE_PREVIOUS	= (($PAGE_CURRENT > 1) ? ($PAGE_CURRENT - 1) : false);
                            $PAGE_NEXT		= (($PAGE_CURRENT < $TOTAL_PAGES) ? ($PAGE_CURRENT + 1) : false);

                            /**
                             * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
                             */
                            $limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $PAGE_CURRENT) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);


			    $query		= "
						SELECT * from
						(
						    (
						    SELECT a.updated_date, a.progress_value, concat(concat(b.`firstname`,' '), b.`lastname`) as evaluator_name, concat(concat(e.`firstname`,' '), e.`lastname`) as target_name
						    FROM `entrada`.`evaluation_progress` as a
						    LEFT JOIN `evaluation_targets` as d
						    on d.`etarget_id` = a.`etarget_id`
                                                    LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                                    ON a.`proxy_id` = b.`id`
                                                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
                                                    ON c.`user_id` = b.`id`
                                                    AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
						    LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS e
                                                    ON d.`target_value` = e.`id`
                                                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS f
                                                    ON f.`user_id` = e.`id`
                                                    AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
							WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
							AND d.`target_id` in ('2','3','6','7','8')
						    )
							UNION
						    (
						    SELECT a.updated_date, a.progress_value, concat(concat(b.`firstname`,' '), b.`lastname`) as evaluator_name, e.`course_name` as target_name
						    FROM `entrada`.`evaluation_progress` as a
						    LEFT JOIN `evaluation_targets` as d
						    on d.`etarget_id` = a.`etarget_id`
                                                    LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                                    ON a.`proxy_id` = b.`id`
                                                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS c
                                                    ON c.`user_id` = b.`id`
                                                    AND c.`app_id` IN (".AUTH_APP_IDS_STRING.")
						    LEFT JOIN `courses` AS e
						    ON d.`target_value` = e.`course_id`
							WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID)."
							AND d.`target_id` = '1'
						    )
						) as t_ppl
						LIMIT %s, %s
                                                ";
			    
                            $query		= sprintf($query, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
                            $results	= $db->GetAll($query);
                            //echo "______log______"."query: ".$query."<br>";
                            //echo "______log______"."total_rows: ".$results["total_rows"]."<br>";

                            if($results) {
                                    if(($TOTAL_PAGES > 1) && ($member_pagination)) {
                                            echo "<div id=\"pagination-links\">\n";
                                            echo "Pages: ".$member_pagination->GetPageLinks();
                                            echo "</div>\n";
                                    }
                                    ?>
                                    <table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Members">
                                    <colgroup>
                                            <col class="target" />
                                            <col class="evaluator" />
                                            <col class="progress" />
                                    </colgroup>
                                    <thead>
                                            <tr>
                                                    <td class="target">Target</td>
                                                    <td class="evaluator">Evaluator</td>
                                                    <td class="progress">Progress</td>

                                            </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($results as $result) {
                                            echo "<tr>\n";
                                            echo "	<td>".$result["target_name"]."</td>\n";
                                            echo "	<td>".$result["evaluator_name"]."</td>\n";
                                            echo "	<td>".$result["progress_value"]."</td>\n";
                                            echo "</tr>\n";
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot>
                                        <tr><td>&nbsp;</td></tr>
                                    </tfoot>
                                    </table>
                                    <?php
                            } else {
                                    echo display_notice(array("No evaluators in this evaluation has started at this time."));
                            }
                            ?>
        </div>
<br /><br />
					<?php
					break;
			}
	} else {
		application_log("error", "User tried to manage progress of a evaluation id [".$EVALUATION_ID."] that does not exist or is not active in the system.");

		$ERROR++;
		$ERRORSTR[] = "The evaluation you are trying to manage either does not exist in the system or has been deactived by an administrator.<br /><br />If you feel you are receiving this message in error, please contact the MEdTech Unit (page feedback on left) and we will investigate. The MEdTech Unit has automatically been informed that this error has taken place.";

		echo display_error();
	}
} else {
	application_log("error", "User tried to manage members a evaluation without providing a evaluation_id.");

	header("Location: ".ENTRADA_URL."/admin/evaluations");
	exit;
}
?>