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
 * The default file that is loaded when /admin/evaluations is accessed.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Howard Lu <yhlu@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/
if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluation", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	/**
	 * Update requested column to sort by.
	 * Valid: director, name
	 */
    if (isset($_GET["sb"])) {
		if (@in_array(trim($_GET["sb"]), array("type", "name", "date", "teacher", "director", "notices"))) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]	= trim($_GET["sb"]);
		}

		$_SERVER["QUERY_STRING"] = replace_query(array("sb" => false));
	} else {
		if (!isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"])) {
			$_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"] = "name";
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

	?>
	<h1>Manage Evaluations</h1>
	<?php
	/**
	 * Fetch all of the evaluations that apply to the current filter set.
	 */
	$scheduler_evaluations = eval_sche_fetch_filtered_evals();

	if ($ENTRADA_ACL->amIAllowed("evaluation", "create", false)) {
		?>
		<div style="float: right">
			<ul class="page-action">
				<li><a href="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add" class="strong-green">Add New Evaluation</a></li>
			</ul>
		</div>
		<div style="clear: both"></div>
		<?php
	}

	if (count($scheduler_evaluations["evaluations"])) {
		if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations?section=delete" method="post">
		<?php endif; ?>
		<table class="tableList" cellspacing="0" cellpadding="1" summary="List of Evaluations">
			<colgroup>
				<col class="modified" />
				<col class="title" />
				<col class="start" />
				<col class="finish" />
				<col class="EvaluatorsNum" />
				<col class="attachment" />
				<col class="attachment" />
			</colgroup>
			<thead>
				<tr>
					<td class="modified">&nbsp;</td>
					<td>Title</td>
					<td>Start</td>
					<td>Finish</td>
					<td>Evaluation Num</td>
					<td class="attachment">&nbsp;</td>
					<td class="attachment">&nbsp;</td>
				</tr>
			</thead>
			<?php if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
			<tfoot>
				<tr>
					<td></td>
					<td colspan="5" style="padding-top: 10px">
						<input type="submit" class="button" value="Delete Selected" />
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php
			foreach ($scheduler_evaluations["evaluations"] as $result) {
				$url = ENTRADA_URL."/admin/evaluations?section=progress&evaluation=".$result["evaluation_id"];
				
				echo "<tr id=\"evaluation-".$result["evaluation_id"]."\" class=\"evaluation\">\n";
				echo "	<td class=\"modified\"><input type=\"checkbox\" name=\"checked[]\" value=\"".$result["evaluation_id"]."\" /></td>\n";
				echo "	<td class=\"title\"><a href=\"".$url."\">".html_encode($result["evaluation_title"])."</a></td>\n";
				echo "	<td class=\"start\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, $result["evaluation_start"])."</a></td>\n";
				echo "	<td class=\"finish\"><a href=\"".$url."\">".date(DEFAULT_DATE_FORMAT, html_encode($result["evaluation_finish"]))."</a></td>\n";
				echo "	<td class=\"EvaluatorsNum\"><a href=\"".$url."\">".html_encode($result["evaluator_num"])."*".html_encode($result["target_num"])."</a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/admin/evaluations?section=edit&id=".$result["evaluation_id"]."\"><img src=\"".ENTRADA_URL."/images/action-edit.gif\" width=\"16\" height=\"16\" alt=\"Manage Evaluation Detail\" title=\"Manage Evaluation Detail\" border=\"0\" /></a></td>\n";
				echo "	<td class=\"attachment\"><a href=\"".ENTRADA_URL."/admin/evaluations?section=members&evaluation=".$result["evaluation_id"]."\"><img src=\"".ENTRADA_URL."/images/event-contents.gif\" width=\"16\" height=\"16\" alt=\"Manage Evaluation Content\" title=\"Manage Evaluation Content\" border=\"0\" /></a></td>\n";
				echo "</tr>\n";
			}
			?>
			</tbody>
		</table>
		<?php if ($ENTRADA_ACL->amIAllowed("evaluation", "delete", false)) : ?>
		</form>
		<?php
		endif;
	} else {
		$filters_applied = (((isset($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"])) && ($filters_total = @count($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["filters"]))) ? true : false);
		?>
		<div class="display-notice">
			<h3>No Matching Evaluations</h3>
			There are no evaluations scheduled
			<?php
			switch ($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["dtype"]) {
				case "day" :
					echo "that take place on <strong>".date(DEFAULT_DATE_FORMAT, $scheduler_evaluations["duration_start"])."</strong>";
				break;
				case "month" :
					echo "that take place during <strong>".date("F", $scheduler_evaluations["duration_start"])."</strong> of <strong>".date("Y", $scheduler_evaluations["duration_start"])."</strong>";
				break;
				case "year" :
					echo "that take place during <strong>".date("Y", $scheduler_evaluations["duration_start"])."</strong>";
				break;
				default :
				case "week" :
					echo "from <strong>".date(DEFAULT_DATE_FORMAT, $scheduler_evaluations["duration_start"])."</strong> to <strong>".date(DEFAULT_DATE_FORMAT, $scheduler_evaluations["duration_end"])."</strong>";
				break;
				default :
					continue;
				break;
			}
			echo (($filters_applied) ? " that also match the supplied &quot;Show Only&quot; restrictions" : "") ?>.
			<br /><br />
			If this is unexpected there are a few things that you can check:
			<ol>
				<li style="padding: 3px">Make sure that you are browsing the intended time period. For example, if you trying to browse <?php echo date("F", time()); ?> of <?php echo date("Y", time()); ?>, make sure that the results bar above says &quot;... takes place in <strong><?php echo date("F", time()); ?></strong> of <strong><?php echo date("Y", time()); ?></strong>&quot;.</li>
				<?php
				if ($filters_applied) {
					echo "<li style=\"padding: 3px\">You also have ".$filters_total." filter".(($filters_total != 1) ? "s" : "")." applied to the event list. you may wish to remove ".(($filters_total != 1) ? "one or more of these" : "it")." by clicking the link in the &quot;Showing Evaluations That Include&quot; box above.</li>";
				}
				?>
			</ol>
		</div>
		<?php
	}

	echo "<form action=\"\" method=\"get\">\n";
	echo "<input type=\"hidden\" id=\"dstamp\" name=\"dstamp\" value=\"".html_encode($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["dstamp"])."\" />\n";
	echo "</form>\n";

	/**
	 * Sidebar item that will provide another method for sorting, ordering, etc.
	 */
	$sidebar_html  = "Sort columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "date") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "date"))."\" title=\"Sort by Date &amp; Time\">by date &amp; time</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("sb" => "title"))."\" title=\"Sort by Event Title\">by evaluation title</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Order columns:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
	$sidebar_html .= "</ul>\n";
	$sidebar_html .= "Rows per page:\n";
	$sidebar_html .= "<ul class=\"menu\">\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
	$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
	$sidebar_html .= "</ul>\n";

	new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");

	$ONLOAD[] = "initList()";
}