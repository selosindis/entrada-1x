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
		$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $EVALUATION_ID)), "title" => "Show Progress");
		
        /**
         * Update requested sort column.
         * Valid: date, title
         */
/*
        if((isset($_GET["attempts"]) && $_GET["attempts"] == 'true') || !isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempts"])) {
           $view_individual_attempts = true;
           $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempts"] = true;
        } elseif ((isset($_GET["attempts"]) && $_GET["attempts"] == 'false')) {
*/
           $view_individual_attempts = false;
           $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempts"] = false;
//        }
        
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
            echo "<div class=\"no-printing\">\n";
            echo "	<div style=\"float: right; margin-top: 8px\">\n";
            echo "		<a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\"><img src=\"".ENTRADA_URL."/images/event-details.gif\" width=\"16\" height=\"16\" alt=\"Edit details\" title=\"Edit evaluation details\" border=\"0\" style=\"vertical-align: middle\" /></a> <a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("section" => "edit", "id" => $evaluation_details["evaluation_id"]))."\" style=\"font-size: 10px; margin-right: 8px\">Edit details</a>\n";
            echo "	</div>\n";
            echo "</div>\n";
        }
        echo "<h1 class=\"evaluation-title\">".html_encode($evaluation_details["evaluation_title"])."</h1>\n";
        ?>
        <div class="tab-pane" id="progress_div">
			<h2 style="margin-top: 0px">Progress</h2>
            <?php
            if (!$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["attempts"]) {
				$query = "	SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, b.`id` AS `proxy_id`
	            			FROM `evaluation_evaluators` AS a
                			LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS ba
                			ON a.`evaluator_type` = 'grad_year'
                			AND ba.`app_id` = ".$db->qstr(AUTH_APP_ID)."
                			AND a.`evaluator_value` = ba.`role`
	            			JOIN `".AUTH_DATABASE."`.`user_data` AS b
	            			ON ((
		            			a.`evaluator_type` = 'proxy_id'
		            			AND a.`evaluator_value` = b.`id`
							) OR (
								ba.`user_id` = b.`id`
							))
	            			WHERE a.`evaluation_id` = ".$db->qstr($EVALUATION_ID);
	            $evalation_evaluators = $db->GetAll($query);
	        	if ($evalation_evaluators) {
	                ?>
	                <table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Members">
	                <colgroup>
                        <col class="modified" />
                        <col class="target" />
                        <col class="date-small" />
                        <col class="date-small" />
                        <col class="date" />
	                </colgroup>
	                <thead>
                        <tr>
                            <td class="modified">&nbsp;</td>
                            <td class="target">Evaluator</td>
                            <td class="date-small">Current Attempt</td>
                            <td class="date-small">Attempts Completed</td>
                            <td class="date">Last Attempted</td>
                        </tr>
	                </thead>
	                <tbody>
	                <?php
	                foreach($evalation_evaluators as $evalation_evaluator) {
                		$query = "	SELECT * FROM `evaluation_progress`
                					WHERE `evaluation_id` = ".$evalation_evaluator["evaluation_id"]."
		                			AND `proxy_id` = ".$evalation_evaluator["proxy_id"]."
		                			AND `progress_value` <> 'cancelled'
                					ORDER BY `progress_value` ASC";
                		$evaluator_progress_records = $db->GetAll($query);
                		$count = 0;
                		$inprogress = false;
                		$last_completed = 0;
                		foreach ($evaluator_progress_records as &$evaluator_progress) {
                			if ($evaluator_progress["progress_value"] == "complete") {
                				$count++;
                			} else {
                				$inprogress = true;
                			}
            				if ($last_completed < $evaluator_progress["updated_date"]) {
            					$last_completed = $evaluator_progress["updated_date"];
            				}
                		}
                        echo "<tr>\n";
                        echo "	<td>&nbsp;</td>\n";
                        echo "	<td>".$evalation_evaluator["fullname"]."</td>\n";
                        echo "	<td>".($inprogress ? "In Progress" : ($last_completed ? "Completed" : "Not Started"))."</td>\n";
                        echo "	<td>".$count." / ".$evaluation_details["max_submittable"]."</td>\n";
                        echo "	<td>".(isset($last_completed) && $last_completed ? date(DEFAULT_DATE_FORMAT, $last_completed) : "Not Started")."</td>\n";
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
	                    echo display_notice(array("No evaluators have completed this evaluation at this time."));
	            }
            } else {
	            /**
	             * Get the total number of results using the generated queries above and calculate the total number
	             * of pages that are available based on the results per page preferences.
	             */
	            $query = "	SELECT a.*, CONCAT_WS(' ', b.`firstname`, b.`lastname`) AS `fullname`, c.`progress_value`, c.`updated_date`, d.`target_value`, e.`target_shortname`, b.`id` AS `proxy_id`
	            			FROM `evaluation_progress` AS c
                			JOIN `evaluation_evaluators` AS a
                			ON a.`evaluation_id` = c.`evaluation_id`
                			LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS ba
                			ON a.`evaluator_type` = 'grad_year'
                			AND ba.`app_id` = ".$db->qstr(AUTH_APP_ID)."
                			AND a.`evaluator_value` = ba.`role`
	            			JOIN `".AUTH_DATABASE."`.`user_data` AS b
	            			ON ((
		            			a.`evaluator_type` = 'proxy_id'
		            			AND a.`evaluator_value` = b.`id`
	                			AND c.`proxy_id` = a.`evaluator_value`
							) OR (
								ba.`user_id` = b.`id`
	                			AND c.`proxy_id` = b.`id`
							))
                			AND `progress_value` <> 'cancelled'
                			JOIN `evaluation_targets` AS d
                			ON d.`etarget_id` = c.`etarget_id`
                			JOIN `evaluations_lu_targets` AS e
                			ON d.`target_id` = e.`target_id`
	            			WHERE c.`evaluation_id` = ".$db->qstr($EVALUATION_ID);
	            $evalation_evaluators = $db->GetAll($query);
	        	if ($evalation_evaluators) {
	                ?>
	                <table class="tableList" style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Evaluation Members">
	                <colgroup>
                        <col class="modified" />
                        <col class="title" />
                        <col class="title" />
                        <col class="date-small" />
                        <col class="date" />
	                </colgroup>
	                <thead>
                        <tr>
                            <td class="modified">&nbsp;</td>
                            <td class="title">Evaluator</td>
                            <td class="title">Evaluation Target</td>
                            <td class="date-small">Attempt Progress</td>
                            <td class="date">Last Updated</td>
                        </tr>
	                </thead>
	                <tbody>
	                <?php
	                foreach($evalation_evaluators as $evalation_evaluator) {
	                	$target_name = fetch_evaluation_target_title($evalation_evaluator);
                        echo "<tr>\n";
                        echo "	<td>&nbsp;</td>\n";
                        echo "	<td>".$evalation_evaluator["fullname"]."</td>\n";
                        echo "	<td>".$target_name."</td>\n";
                        echo "	<td>".($evalation_evaluator["progress_value"] == "inprogress" ? "In Progress" : "Completed")."</td>\n";
                        echo "	<td>".date(DEFAULT_DATE_FORMAT, $evalation_evaluator["updated_date"])."</td>\n";
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
	                    echo display_notice(array("No evaluators have completed this evaluation at this time."));
	            }
            }
            ?>
		</div>
		<br /><br />
		<?php
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