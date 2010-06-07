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
 * @version $Id: index.inc.php 360 2009-02-12 20:36:32Z simpson $
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_QUIZZES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	
	/**
	 * Update requested column to sort by.
	 * Valid: date, teacher, title, phase
	 */
	if (isset($_GET["sb"])) {
		if (in_array(trim($_GET["sb"]), array("title", "questions", "status"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = trim($_GET["sb"]);
		}
	
		$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "status";
		}
	}
	
	/**
	 * Update requested order to sort by.
	 * Valid: asc, desc
	 */
	if (isset($_GET["so"])) {
		$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = ((strtolower($_GET["so"]) == "desc") ? "desc" : "asc");
	
		$_SERVER["QUERY_STRING"] = replace_query(array("so" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"] = "asc";
		}
	}
	
	/**
	 * Update requsted number of rows per page.
	 * Valid: any integer really.
	 */
	if ((isset($_GET["pp"])) && ((int) trim($_GET["pp"]))) {
		$integer = (int) trim($_GET["pp"]);
	
		if (($integer > 0) && ($integer <= 250)) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = $integer;
		}
	
		$_SERVER["QUERY_STRING"] = replace_query(array("pp" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] = DEFAULT_ROWS_PER_PAGE;
		}
	}

	/**
	 * Check if preferences need to be updated on the server at this point.
	 */
	preferences_update($MODULE, $PREFERENCES);

	/**
	 * Provide the queries with the columns to order by.
	 */
	switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) {
		case "questions" :
		default :
			$sort_by = "`question_total` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
		case "title" :
			$sort_by = "a.`quiz_title` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
		case "status" :
		default :
			$sort_by = "`quiz_status` ".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]);
		break;
	}

	/**
	 * Get the total number of results using the generated queries above and calculate the total number
	 * of pages that are available based on the results per page preferences.
	 */

	$query	= "	SELECT COUNT(*) AS `total_rows` FROM `quizzes`";
	$result = $db->GetRow($query);
	if ($result) {
		$total_rows	= $result["total_rows"];

		if ($total_rows <= $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) {
			$total_pages = 1;
		} elseif (($total_rows % $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == 0) {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
		} else {
			$total_pages = (int) ($total_rows / $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) + 1;
		}
	} else {
		$total_rows		= 0;
		$total_pages	= 1;
	}

	/**
	 * Check if pv variable is set and see if it's a valid page, other wise page 1 it is.
	 */
	if (isset($_GET["pv"])) {
		$page_current = (int) trim($_GET["pv"]);

		if (($page_current < 1) || ($page_current > $total_pages)) {
			$page_current = 1;
		}
	} else {
		$page_current = 1;
	}

	$page_previous	= (($page_current > 1) ? ($page_current - 1) : false);
	$page_next		= (($page_current < $total_pages) ? ($page_current + 1) : false);
	?>
	
	<h1><?php echo $MODULES[strtolower($MODULE)]["title"]; ?></h1>
	
	<div style="float: right; padding-bottom: 10px;">
		<ul class="page-action">
			<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add">Create New Quiz</a></li>
		</ul>
	</div>
	<div class="clear"></div>
	<?php
	$query	= "	SELECT a.*, COUNT(c.`quiz_id`) AS `question_total`, IF(a.`quiz_active` = '1', 'Active', 'Disabled') AS `quiz_status`
				FROM `quizzes` AS a
				LEFT JOIN `quiz_contacts` AS b
				ON a.`quiz_id` = b.`quiz_id`
				LEFT JOIN `quiz_questions` AS c
				ON a.`quiz_id` = c.`quiz_id`
				WHERE b.`proxy_id` = ".$db->qstr($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"])."
				GROUP BY a.`quiz_id`
				ORDER BY %s LIMIT %s, %s";

	/**
	 * Provides the first parameter of MySQLs LIMIT statement by calculating which row to start results from.
	 */
	$limit_parameter = (int) (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"] * $page_current) - $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);

	$query		= sprintf($query, $sort_by, $limit_parameter, $_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]);
	$results	= $db->GetAll($query);
	if ($results) {
		?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=enable" method="post">
		<table class="tableList" cellspacing="0" summary="List of Quizzes">
		<colgroup>
			<col class="modified" />
			<col class="title" />
			<col class="general" />
			<col class="general" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="title<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "title") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("title", "Quiz Title"); ?></td>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "status") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("status", "Status"); ?></td>
				<td class="general<?php echo (($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] == "questions") ? " sorted".strtoupper($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) : ""); ?>"><?php echo admin_order_link("questions", "Quiz Questions"); ?></td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td></td>
				<td style="padding-top: 10px" colspan="3">
					<input type="submit" class="button" value="Enable Selected" />
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			foreach ($results as $result) {
				echo "<tr id=\"quiz-".$result["quiz_id"]."\"".((!$result["quiz_active"]) ? " class=\"disabled\"" : "").">\n";
				echo "	<td".((!$result["quiz_active"]) ? " class=\"disabled\"" : "")."><input type=\"radio\" name=\"id\" value=\"".$result["quiz_id"]."\" /></td>\n";
				echo "	<td".((!$result["quiz_active"]) ? " class=\"disabled\"" : "")."><a href=\"".ENTRADA_URL."/admin/".$MODULE."?section=edit&amp;id=".$result["quiz_id"]."\">".html_encode($result["quiz_title"])."</a></td>\n";
				echo "	<td".((!$result["quiz_active"]) ? " class=\"disabled\"" : "").">".($result["quiz_status"])."</td>\n";
				echo "	<td".((!$result["quiz_active"]) ? " class=\"disabled\"" : "").">".html_encode($result["question_total"])."</td>\n";
				echo "</tr>\n";
			}
			?>
		</tbody>
		</table>
		</form>
		<?php
	} else {
		?>
		<div class="display-generic">
			The Manage Quizzes tool allows you to author and deliver quizzes online directly within <?php echo APPLICATION_NAME; ?>.
			<br /><br />
			Creating quizzes is easy; to begin simply click the <strong>Create Quiz</strong> link above and follow the on-screen instructions.
		</div>
		<?php
	}
}