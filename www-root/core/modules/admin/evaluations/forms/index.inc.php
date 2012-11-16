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
 * This file displays the list of all evaluation forms available in the system.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVALUATIONS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluationform", "update", false)) {
	add_error("Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	?>
	<h1>Manage Evaluation Forms</h1>
	
	<ul class="page-action fright">
		<li><a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=add">Create New Evaluation Form</a></li>
	</ul>
	<div class="clear"></div>
	<?php
	require_once("Models/evaluation/Evaluation.class.php");
	$results = Evaluation::getAuthorEvaluationForms();
	if ($results) {
		$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/jquery/jquery.dataTables.min.js\"></script>";
		$HEAD[] = "<script type=\"text/javascript\">
		jQuery(document).ready(function() {
			jQuery('#evaluationforms').dataTable(
				{    
					'sPaginationType': 'full_numbers',
					'bInfo': false
				}
			);
		});
		</script>";
		?>
		<form action="<?php echo ENTRADA_URL; ?>/admin/evaluations/forms?section=delete" method="post">
		<table class="tableList" id="evaluationforms" cellspacing="0" summary="List of Evaluation Forms">
		<colgroup>
			<col class="modified" />
			<col class="general" />
			<col class="title" />
		</colgroup>
		<thead>
			<tr>
				<td class="modified">&nbsp;</td>
				<td class="general">Form Type</td>
				<td class="title">Evaluation Form Title</td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td></td>
				<td style="padding-top: 10px" colspan="2">
					<input type="submit" class="button" value="Disable Selected" />
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			foreach ($results as $result) {
				echo "<tr id=\"eform-".$result["eform_id"]."\">\n";
				echo "	<td><input type=\"checkbox\" name=\"delete[]\" value=\"".$result["eform_id"]."\" /></td>\n";
				echo "	<td>".html_encode($result["target_title"])."</td>\n";
				echo "	<td><a href=\"".ENTRADA_URL."/admin/evaluations/forms?section=edit&amp;id=".$result["eform_id"]."\">".html_encode($result["form_title"])."</a></td>\n";
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
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "type") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("sb" => "type"))."\" title=\"Sort by Form Type\">by form type</a></li>\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["sb"]) == "title") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("sb" => "title"))."\" title=\"Sort by Form Title\">by title</a></li>\n";
		$sidebar_html .= "</ul>\n";
		$sidebar_html .= "Order columns:\n";
		$sidebar_html .= "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "asc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("so" => "asc"))."\" title=\"Ascending Order\">in ascending order</a></li>\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["so"]) == "desc") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("so" => "desc"))."\" title=\"Descending Order\">in descending order</a></li>\n";
		$sidebar_html .= "</ul>\n";
		$sidebar_html .= "Rows per page:\n";
		$sidebar_html .= "<ul class=\"menu\">\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "5") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("pp" => "5"))."\" title=\"Display 5 Rows Per Page\">5 rows per page</a></li>\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "15") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("pp" => "15"))."\" title=\"Display 15 Rows Per Page\">15 rows per page</a></li>\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "25") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("pp" => "25"))."\" title=\"Display 25 Rows Per Page\">25 rows per page</a></li>\n";
		$sidebar_html .= "	<li class=\"".((strtolower($_SESSION[APPLICATION_IDENTIFIER][$MODULE]["pp"]) == "50") ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/evaluations/forms?".replace_query(array("pp" => "50"))."\" title=\"Display 50 Rows Per Page\">50 rows per page</a></li>\n";
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Sort Results", $sidebar_html, "sort-results", "open");
	} else {
		?>
		<div class="display-generic">
			The Manage Forms tool allows you to create and manage forms that can be electronically distributed to groups of people.
			<br /><br />
			Creating evaluation forms is easy; to begin simply click the <strong>Create New Evaluation Form</strong> link above and follow the on-screen instructions.
		</div>
		<?php
	}
}